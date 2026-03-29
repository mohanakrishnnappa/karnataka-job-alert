<?php
/*
Plugin Name: Webzeeto Quiz
Description: Advanced MCQ Quiz Plugin
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit;
}

define('WZQ_PATH', plugin_dir_path(__FILE__));
define('WZQ_URL', plugin_dir_url(__FILE__));

require_once WZQ_PATH . 'includes/db.php';
require_once WZQ_PATH . 'includes/cpt.php';
require_once WZQ_PATH . 'includes/admin.php';
require_once WZQ_PATH . 'includes/admin-qpreport.php';
require_once WZQ_PATH . 'includes/shortcode.php';

register_activation_hook(__FILE__, 'wzq_create_tables');

add_action('wp_enqueue_scripts', function() {

    wp_enqueue_style('wzq-css', WZQ_URL . 'assets/css/quiz.css');

    // ✅ Quiz main script
    wp_enqueue_script('wzq-js', WZQ_URL . 'assets/js/quiz.js', [], null, true);

    // ✅ Report script (depends on quiz.js)
    wp_enqueue_script(
        'quiz-report',
        WZQ_URL . 'assets/js/quiz-report.js',
        ['wzq-js'], // ✅ correct dependency
        null,
        true
    );

    // ✅ AJAX and Sound Tracks
    wp_localize_script('wzq-js', 'wzq_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'sounds' => [
            'correct' => WZQ_URL . 'assets/sounds/correct.mp3',
            'wrong'   => WZQ_URL . 'assets/sounds/wrong.mp3',
            'finish'  => WZQ_URL . 'assets/sounds/finish.mp3'
        ]
    ]);
});

add_filter('template_include', function($template) {

    // ✅ Check if it's quiz archive (/quiz/)
    if (is_post_type_archive('wz_quiz')) {

        // 1️⃣ Allow theme override (future-proof)
        $theme_template = locate_template('archive-wz_quiz.php');

        if ($theme_template) {
            return $theme_template;
        }

        // 2️⃣ Load your custom plugin template (archive-cpt.php)
        return WZQ_PATH . 'templates/archive-cpt.php';
    }

    return $template;
});