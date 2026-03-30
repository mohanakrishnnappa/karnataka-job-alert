<?php

if (!defined('ABSPATH')) {
    exit;
}

// ─── Table name constants (single source of truth) ───────────────────────────
define('WZQ_TABLE_QUIZZES',   $GLOBALS['wpdb']->prefix . 'wz_quizzes');
define('WZQ_TABLE_QUESTIONS', $GLOBALS['wpdb']->prefix . 'wz_questions');
define('WZQ_TABLE_REPORTS',   $GLOBALS['wpdb']->prefix . 'wz_reports');

// ─── Reusable DB helpers ──────────────────────────────────────────────────────

function wzq_get_quiz_by_post( $post_id ) {
    global $wpdb;
    return $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM " . WZQ_TABLE_QUIZZES . " WHERE post_id = %d", $post_id )
    );
}

function wzq_get_questions( $quiz_id ) {
    global $wpdb;
    return $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM " . WZQ_TABLE_QUESTIONS . " WHERE quiz_id = %d ORDER BY order_index ASC", $quiz_id )
    );
}

// ─────────────────────────────────────────────────────────────────────────────

function wzq_create_tables() {
    global $wpdb;

    $charset = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql1 = "CREATE TABLE " . WZQ_TABLE_QUIZZES . " (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT,
        time_limit INT,
        random_order TINYINT,
        ad_after INT,
        custom_btn_text VARCHAR(255),
        custom_btn_link TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    $sql2 = "CREATE TABLE " . WZQ_TABLE_QUESTIONS . " (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        quiz_id BIGINT,
        question TEXT,
        option_a TEXT,
        option_b TEXT,
        option_c TEXT,
        option_d TEXT,
        correct CHAR(1),
        explanation TEXT,
        order_index INT
    ) $charset;";

    $sql3 = "CREATE TABLE " . WZQ_TABLE_REPORTS . " (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        quiz_id BIGINT,
        question_id BIGINT,
        question_text TEXT,
        issue TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}


add_action('wp_ajax_wzq_get_ids', 'wzq_get_ids');
add_action('wp_ajax_nopriv_wzq_get_ids', 'wzq_get_ids');

function wzq_get_ids() {

    $quiz_id = intval($_POST['quiz_id']);

    global $wpdb;

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT id FROM " . WZQ_TABLE_QUESTIONS . " WHERE quiz_id = %d",
            $quiz_id
        )
    );

    wp_send_json($ids);
}

add_action('wp_ajax_wzq_get_questions', 'wzq_get_questions_ajax');
add_action('wp_ajax_nopriv_wzq_get_questions', 'wzq_get_questions_ajax');

function wzq_get_questions_ajax() {

    global $wpdb;

    $ids = $_POST['ids'];

    if (empty($ids)) wp_send_json([]);

    $ids = array_map('intval', $ids);
    $ids_str = implode(',', $ids);

    $questions = $wpdb->get_results("
        SELECT id, question, option_a, option_b, option_c, option_d, correct, explanation
        FROM " . WZQ_TABLE_QUESTIONS . "
        WHERE id IN ($ids_str)
        ORDER BY FIELD(id, $ids_str)
    ");

    wp_send_json($questions);
}