<?php
/*
Plugin Name: Webzeeto Quiz
Description: Advanced MCQ Quiz Plugin
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

define('WZQ_PATH', plugin_dir_path(__FILE__));
define('WZQ_URL', plugin_dir_url(__FILE__));

require_once WZQ_PATH . 'includes/db.php';
require_once WZQ_PATH . 'includes/cpt.php';
require_once WZQ_PATH . 'includes/admin.php';
require_once WZQ_PATH . 'includes/shortcode.php';

register_activation_hook(__FILE__, 'wzq_create_tables');

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('wzq-css', WZQ_URL . 'assets/css/quiz.css');
    wp_enqueue_script('wzq-js', WZQ_URL . 'assets/js/quiz.js', [], false, true);
});

wp_localize_script('wzq-js', 'wzq_ajax', [
    'url' => admin_url('admin-ajax.php')
]);