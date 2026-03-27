<?php

function wzq_create_tables() {
    global $wpdb;

    $charset = $wpdb->get_charset_collate();

    $quiz_table = $wpdb->prefix . 'wz_quizzes';
    $question_table = $wpdb->prefix . 'wz_questions';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql1 = "CREATE TABLE $quiz_table (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT,
        time_limit INT,
        random_order TINYINT,
        ad_after INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    $sql2 = "CREATE TABLE $question_table (
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

    dbDelta($sql1);
    dbDelta($sql2);
}