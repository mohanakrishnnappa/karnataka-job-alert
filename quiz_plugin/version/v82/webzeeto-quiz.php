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
            'site_url' => home_url(),
            'site_host' => parse_url(home_url(), PHP_URL_HOST),
            'signature' => WZQ_URL . 'assets/img/signature-v3.png',
            'logo' => WZQ_URL . 'assets/img/certificate-logo-v1.png',
            'certifiedseal' => WZQ_URL . 'assets/img/certified-seal-v1.png',
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

// ─── Reusable quiz card renderer ─────────────────────────────────────────────
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

            echo wzq_render_quiz_card( get_the_ID(), get_the_title(), get_permalink() );

        endwhile;

    else:
        echo "<p>No quizzes found</p>";
    endif;

    wp_die();
}

function wzq_render_quiz_card($post_id) {

    $quiz = wzq_get_quiz_by_post($post_id);

    // ✅ DIRECT READ (NO QUERY)
    $question_count = $quiz->total_questions ?? 0;

    // ⏱ Time format
    $time_label = "Unlimited";

    if (!empty($quiz->time_limit)) {
        $m = floor($quiz->time_limit / 60);
        $s = $quiz->time_limit % 60;

        if ($m > 0) {
            $time_label = $m . " min";
        } elseif ($s > 0) {
            $time_label = $s . " sec";
        }
    }

    ob_start();
    ?>

    <div class="wzq-card">
        <h2><?php echo esc_html(get_the_title($post_id)); ?></h2>

        <div class="wzq-meta">
            <span>📊 <?php echo esc_html($question_count); ?> Questions</span>
            <span>⏱ <?php echo esc_html($time_label); ?></span>
        </div>

        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="wzq-btn">
            ▶ Start Quiz
        </a>
    </div>

    <?php
    return ob_get_clean();
}