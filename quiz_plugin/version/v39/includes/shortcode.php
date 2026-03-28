<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('wz_quiz', function($atts){

    global $wpdb;

    $post_id = intval($atts['id']);

    $quiz = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wz_quizzes WHERE post_id = %d",
            $post_id
        )
    );

    if(!$quiz) return "Quiz not found";

    $questions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wz_questions WHERE quiz_id = %d ORDER BY order_index ASC",
            $quiz->id
        )
    );

    ob_start();
?>

<div class="wzq-wrapper" 
     data-total="<?php echo count($questions); ?>" 
     data-random="<?php echo $quiz->random_order; ?>"
     data-timer="<?php echo isset($quiz->time_limit) ? intval($quiz->time_limit) : 0; ?>"
     data-quiz="<?php echo $quiz->id; ?>">

    <div class="wzq-start-screen">

        <div class="wzq-q-header">
            <div class="wzq-q-meta">
                <span class="wzq-status-label answered">Quiz Ready</span>
            </div>

            <h2 class="wzq-question-text">
                <?php echo get_the_title($post_id); ?>
            </h2>
        </div>

        <!-- ✅ QUIZ DETAILS -->
        <div class="wzq-start-meta">
            <div class="wzq-meta-item">
                📊 Questions: <strong><?php echo count($questions); ?></strong>
            </div>

            <div class="wzq-meta-item">
                ⏱ Time Limit: 
                <strong>
                    <?php 
                        $time = intval($quiz->time_limit);

                        if ($time === 0) {
                            echo "Unlimited";
                        } else {

                            $h = floor($time / 3600);
                            $m = floor(($time % 3600) / 60);
                            $s = $time % 60;

                            $parts = [];

                            if ($h > 0) {
                                $parts[] = $h . " hr" . ($h > 1 ? "s" : "");
                            }

                            if ($m > 0) {
                                $parts[] = $m . " min" . ($m > 1 ? "s" : "");
                            }

                            if ($s > 0) {
                                $parts[] = $s . " sec";
                            }

                            echo implode(" ", $parts);
                        }
                    ?>
                </strong>
            </div>
        </div>

        <div class="wzq-options">
            <button class="wzq-start-btn">▶ Start Quiz</button>
        </div>

    </div>

    <div class="wzq-card" style="display:none;">
        <div class="wzq-warning">
            <span class="wzq-warning-text"></span>
            <button class="wzq-warning-close">&times;</button>
        </div>
        <div class="wzq-header">
            <div class="wzq-header-top">
                <div class="wzq-progress-text">
                    Question <span id="wzq-current">1</span> / <?php echo count($questions); ?>
                </div>

                <div class="wzq-timer" style="display:none;">
                    ⏱ <span id="wzq-time">00:00</span>
                </div>
            </div>

            <div class="wzq-bar">
                <div class="wzq-bar-fill"></div>
            </div>
        </div>

        <?php foreach($questions as $i => $q): ?>

        <div class="wzq-question <?php echo $i==0 ? 'active' : ''; ?>" 
            data-index="<?php echo $i; ?>"
            data-id="<?php echo $q->id; ?>">

            <h3 class="wzq-question-text">
                <span class="wzq-q-number">Q<?php echo $i+1; ?>.</span>
                <?php echo $q->question; ?>
            </h3>

            <div class="wzq-options">

                <?php foreach(['a','b','c','d'] as $opt): ?>
                    <button class="wzq-option"
                        data-correct="<?php echo $q->correct; ?>"
                        data-opt="<?php echo $opt; ?>">
                        <span><?php echo strtoupper($opt); ?>)</span>
                        <?php echo $q->{'option_'.$opt}; ?>
                    </button>
                <?php endforeach; ?>

            </div>

            <div class="wzq-explanation">
                <span class="wzq-expl-title">Explanation:</span>
                <?php echo $q->explanation; ?>
            </div>

        </div>

        <?php endforeach; ?>

        <div class="wzq-nav">
            <button class="wzq-prev" disabled>Previous</button>
            <button class="wzq-next">Next</button>
        </div>

    </div>

    <div class="wzq-result" style="display:none;">
        <h3 class="wzq-score"></h3>

        <button class="wzq-restart">Restart Quiz</button>
    </div>

    <div id="wzq-report-modal" class="wzq-modal">
        <div class="wzq-modal-content">
            <h3>🚩 Report Question</h3>

            <!-- ✅ Dropdown -->
            <select id="wzq-report-reason">
                <option value="">Select issue</option>
                <option value="Wrong Answer">Wrong Answer</option>
                <option value="Typo">Typo / Spelling mistake</option>
                <option value="Duplicate">Duplicate Question</option>
                <option value="Outdated">Outdated Information</option>
                <option value="Other">Other</option>
            </select>

            <!-- ✅ Optional text -->
            <textarea id="wzq-report-text" placeholder="Add more details (optional)"></textarea>

            <div id="wzq-report-msg" class="wzq-report-msg"></div>

            <div class="wzq-modal-actions">
                <button id="wzq-report-cancel">Cancel</button>
                <button id="wzq-report-submit">Submit</button>
            </div>
        </div>
    </div>

    <!-- ✅ TOAST -->
    <div id="wzq-toast"></div>

</div>

<?php
return ob_get_clean();
});


add_filter('the_content', function($content){
    if(get_post_type() == 'wz_quiz'){
        return do_shortcode('[wz_quiz id="'.get_the_ID().'"]');
    }
    return $content;
});