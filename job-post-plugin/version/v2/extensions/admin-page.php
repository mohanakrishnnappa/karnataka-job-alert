<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Add admin menu
add_action('admin_menu', 'graymarket_admin_menu');

function graymarket_admin_menu() {
    add_menu_page(
        'WebZeeto Job',
        'WebZeeto Job',
        'manage_options',
        'webzeeto-job',
        'webzeeto_job_page',
        'dashicons-money-alt',
        3
    );
}

// Information page HTML
function webzeeto_job_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    
    <div class="wrap">
        <h1><strong>WebZeeto Job</strong></h1>
    </div>
    
    <?php
}
?>