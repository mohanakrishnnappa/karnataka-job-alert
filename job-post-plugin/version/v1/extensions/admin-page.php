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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                <h2 style="margin-top: 0;">🌐 Blog Post Display</h2>
                <hr style="margin: 10px 0 20px 0;">
                <p>There is No Settings for Blog Post Display</p>
            </div>

            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0;">⁉️ How to use this?</h2>
                <hr style="margin: 10px 0 20px 0;">
                <p>Here is the shortcode to use this <code>[gmpipo_blog categories="tech,business"]</code></p>
            </div>
        </div>
    </div>
    
    <?php
}
?>