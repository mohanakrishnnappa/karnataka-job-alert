<?php

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

    <div class="wzq-card">
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