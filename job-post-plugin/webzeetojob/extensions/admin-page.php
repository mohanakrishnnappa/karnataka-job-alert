<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Add admin menu
add_action('admin_menu', 'graymarket_admin_menu');

function graymarket_admin_menu() {
    add_menu_page(
        'Job Plugin',
        'Job Plugin',
        'manage_options',
        'webzeeto-job',
        'webzeeto_job_page',
        'dashicons-yes-alt',
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
        <h1><strong>Job Plugin by WebZeeto</strong></h1>
        <p>👉 You now have:
            1) Person ✅
            2) Article ✅
            3) Breadcrumb ✅
            4) WebSite ✅
        </p>
    </div>
    
    <?php
}
?>