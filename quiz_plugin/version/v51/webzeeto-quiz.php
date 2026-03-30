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
require_once WZQ_PATH . 'includes/certificate.php';
require_once WZQ_PATH . 'includes/shortcode.php';

register_activation_hook(__FILE__, 'wzq_create_tables');

add_action('wp_enqueue_scripts', function() {

    wp_enqueue_style('wzq-css', WZQ_URL . 'assets/css/quiz.css');

    // ✅ QUIZ PAGE
    if (is_singular('wz_quiz')) {

        wp_enqueue_script('wzq-js', WZQ_URL . 'assets/js/quiz.js', [], null, true);

        wp_enqueue_script(
            'quiz-report',
            WZQ_URL . 'assets/js/quiz-report.js',
            ['wzq-js'],
            null,
            true
        );

        wp_enqueue_script(
            'wzq-cert',
            WZQ_URL . 'assets/js/certificate.js',
            ['wzq-js'],
            null,
            true
        );

        wp_localize_script('wzq-js', 'wzq_ajax', [
            'url' => admin_url('admin-ajax.php'),
            'sounds' => [
                'correct' => WZQ_URL . 'assets/sounds/correct.mp3',
                'wrong'   => WZQ_URL . 'assets/sounds/wrong.mp3',
                'finish'  => WZQ_URL . 'assets/sounds/finish.mp3'
            ]
        ]);

        wp_localize_script('wzq-cert', 'wzq_cert', [
            'site_url' => home_url()
        ]);
    }

    // ✅ ARCHIVE PAGE (🔥 FIX HERE)
    if (is_post_type_archive('wz_quiz')) {

        wp_enqueue_script('wzq-archive', WZQ_URL . 'assets/js/archive.js', [], null, true);

        wp_localize_script('wzq-archive', 'wzq_ajax', [
            'url' => admin_url('admin-ajax.php')
        ]);
    }

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

// Template filter buttons (AJAX)
add_action('wp_ajax_wzq_filter_quiz', 'wzq_filter_quiz');
add_action('wp_ajax_nopriv_wzq_filter_quiz', 'wzq_filter_quiz');

function wzq_filter_quiz() {

    $cat = sanitize_text_field($_POST['cat']);

    $tax_query = [];

    if (!empty($cat)) {
        $tax_query[] = [
            'taxonomy' => 'wz_quiz_category',
            'field' => 'slug',
            'terms' => $cat
        ];
    }

    $query = new WP_Query([
        'post_type' => 'wz_quiz',
        'posts_per_page' => -1,
        'tax_query' => $tax_query
    ]);

    if ($query->have_posts()) :

        while ($query->have_posts()) : $query->the_post();

            echo "<div class='wzq-card'>";
            echo "<h3>" . get_the_title() . "</h3>";
            echo "<a class='wzq-btn' href='" . get_permalink() . "'>Start Quiz</a>";
            echo "</div>";

        endwhile;

    else:
        echo "<p>No quizzes found</p>";
    endif;

    wp_die();
}