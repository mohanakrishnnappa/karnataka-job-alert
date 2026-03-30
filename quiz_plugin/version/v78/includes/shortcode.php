<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('wz_quiz', function($atts){

    $post_id = intval($atts['id']);

    $quiz = wzq_get_quiz_by_post( $post_id );

    if ( ! $quiz ) return 'Quiz not found';

    global $wpdb;

    $total_questions = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM " . WZQ_TABLE_QUESTIONS . " WHERE quiz_id = %d",
            $quiz->id
        )
    );

    ob_start();
?>

<div class="wzq-wrapper" 
     data-total="<?php echo $total_questions; ?>"
     data-random="<?php echo $quiz->random_order; ?>"
     data-timer="<?php echo isset($quiz->time_limit) ? intval($quiz->time_limit) : 0; ?>"
     data-quiz="<?php echo $quiz->id; ?>"
     data-btn-text="<?php echo esc_attr($quiz->custom_btn_text); ?>"
     data-btn-link="<?php echo esc_url($quiz->custom_btn_link); ?>">

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
                📊 Questions: <strong><?php echo $total_questions; ?></strong>
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
                    Question <span id="wzq-current">1</span> / <?php echo $total_questions; ?>
                </div>

                <div class="wzq-timer" style="display:none;">
                    ⏱ <span id="wzq-time">00:00</span>
                </div>
            </div>

            <div class="wzq-bar">
                <div class="wzq-bar-fill"></div>
            </div>
        </div>

        <div id="wzq-question-container"></div>

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

<?php wzq_certificate_ui(); ?>

<?php
return ob_get_clean();
});


add_filter('the_content', function($content){
    if(get_post_type() == 'wz_quiz'){
        return do_shortcode('[wz_quiz id="'.get_the_ID().'"]');
    }
    return $content;
});

// ✅ GET QUESTION IDS
add_action('wp_ajax_wzq_get_question_ids', 'wzq_get_question_ids');
add_action('wp_ajax_nopriv_wzq_get_question_ids', 'wzq_get_question_ids');

function wzq_get_question_ids() {

    global $wpdb;

    $quiz_id = intval($_POST['quiz_id']);

    if (!$quiz_id) {
        echo json_encode([]);
        wp_die();
    }

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT id FROM " . WZQ_TABLE_QUESTIONS . " WHERE quiz_id = %d ORDER BY order_index ASC",
            $quiz_id
        )
    );

    echo json_encode($ids);
    wp_die();
}

// ✅ LOAD QUESTIONS (CHUNK)
add_action('wp_ajax_wzq_load_questions', 'wzq_load_questions');
add_action('wp_ajax_nopriv_wzq_load_questions', 'wzq_load_questions');

function wzq_load_questions() {

    global $wpdb;

    $ids = isset($_POST['ids']) ? (array) $_POST['ids'] : [];

    if (empty($ids) || !is_array($ids)) {
        echo json_encode([]);
        wp_die();
    }

    // sanitize
    $ids = array_map('intval', $ids);

    $ids_str = implode(',', $ids);

    $query = "
        SELECT id, question, option_a, option_b, option_c, option_d, correct, explanation
        FROM " . WZQ_TABLE_QUESTIONS . "
        WHERE id IN ($ids_str)
        ORDER BY FIELD(id, $ids_str)
    ";

    $results = $wpdb->get_results(
        $wpdb->prepare($query, ...$ids),
        ARRAY_A
    );

    // 🔥 FIX: return proper keys expected by JS
    $formatted = array_map(function($q){
        return [
            'question' => $q['question'],
            'option_a' => $q['option_a'],
            'option_b' => $q['option_b'],
            'option_c' => $q['option_c'],
            'option_d' => $q['option_d'],
            'correct' => $q['correct'],
            'explanation' => $q['explanation'],
        ];
    }, $results);

    echo json_encode($formatted);
    wp_die();
}