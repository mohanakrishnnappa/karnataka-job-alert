<?php

if (!defined('ABSPATH')) {
    exit;
}

/* ================================
   ADD SUBMENU UNDER YOUR PLUGIN
================================ */

add_action('admin_menu', 'wzjob_author_add_settings_page', 16);

function wzjob_author_add_settings_page() {
    add_submenu_page(
        'webzeeto-job',
        'Author Profile',
        'Author Profile',
        'manage_options',
        'wzjob-author-profile-display',
        'wzjob_author_settings_page_html'
    );
}

add_action('admin_init', 'wzjob_author_register_settings');
function wzjob_author_register_settings() {
    register_setting('wzjob_author_settings_group', 'wzjob_author_options');
}

/* ================================
   SETTINGS PAGE HTML
================================ */

function wzjob_author_settings_page_html() {

    if (!current_user_can('manage_options')) {
        return;
    }

    $opts = get_option('wzjob_author_options');
    ?>
    <div class="wrap">
        <h1><strong>Author Profile Display</strong></h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                <h2 style="margin-top: 0;">✍🏼 Author Profile</h2>
                <hr style="margin: 10px 0 20px 0;">

                <form method="post" action="options.php" style="margin-top:20px;">
                    <?php settings_fields('wzjob_author_settings_group'); ?>

                    <table class="form-table">

                        <tr>
                            <th>Name</th>
                            <td><input type="text" name="wzjob_author_options[name]" value="<?php echo esc_attr($opts['name'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                        <tr>
                            <th>Email</th>
                            <td><input type="email" name="wzjob_author_options[email]" value="<?php echo esc_attr($opts['email'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                        <tr>
                            <th>Profile Image</th>
                            <td>
                                <input type="text" id="wzjob_author_image" name="wzjob_author_options[image]" value="<?php echo esc_attr($opts['image'] ?? ''); ?>" class="regular-text">
                                <button class="button" id="wzjob_author_upload_btn">Upload</button>
                                <div style="margin-top:10px;">
                                    <img id="wzjob_author_preview" src="<?php echo esc_url($opts['image'] ?? ''); ?>" style="max-width:120px;border-radius:50%;">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>Biography (HTML Supported)</th>
                            <td>
                                <?php
                                wp_editor(
                                    $opts['bio'] ?? '',
                                    'wzjob_author_bio_editor',
                                    [
                                        'textarea_name' => 'wzjob_author_options[bio]',
                                        'textarea_rows' => 8,
                                        'media_buttons' => false,
                                        'teeny'         => false,
                                    ]
                                );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Website</th>
                            <td><input type="text" name="wzjob_author_options[website]" value="<?php echo esc_attr($opts['website'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                        <tr>
                            <th>Facebook</th>
                            <td><input type="text" name="wzjob_author_options[facebook]" value="<?php echo esc_attr($opts['facebook'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                        <tr>
                            <th>Instagram</th>
                            <td><input type="text" name="wzjob_author_options[instagram]" value="<?php echo esc_attr($opts['instagram'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                        <tr>
                            <th>YouTube</th>
                            <td><input type="text" name="wzjob_author_options[youtube]" value="<?php echo esc_attr($opts['youtube'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                        <tr>
                            <th>WhatsApp</th>
                            <td><input type="text" name="wzjob_author_options[whatsapp]" value="<?php echo esc_attr($opts['whatsapp'] ?? ''); ?>" class="regular-text"></td>
                        </tr>

                    </table>

                    <?php submit_button(); ?>
                </form>
            </div>

            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0;">⁉️ Where is was Stored?</h2>
                <hr style="margin: 10px 0 20px 0;">
                <p>It was storing in <code>wp_options</code></p>
                <p><code>wzjob_author_options</code> with user entered details</p>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        var mediaUploader;
        $('#wzjob_author_upload_btn').click(function(e){
            e.preventDefault();
            if(mediaUploader){
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media({
                title: 'Select Image',
                button: { text: 'Use Image' },
                multiple: false
            });
            mediaUploader.on('select', function(){
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#wzjob_author_image').val(attachment.url);
                $('#wzjob_author_preview').attr('src', attachment.url);
            });
            mediaUploader.open();
        });
    });
    </script>
    <?php
}

add_action('admin_enqueue_scripts', 'wzjob_author_media_scripts');
function wzjob_author_media_scripts($hook) {
    if ($hook === 'wzjob-ipo-admin_page_wzjob-author-profile-display') {
        wp_enqueue_media();
    }
}

/* ================================
   FRONTEND AUTHOR BOX
================================ */

add_filter('the_content', 'wzjob_author_display_box');
function wzjob_author_display_box($content) {

    if (!is_single() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $opts = get_option('wzjob_author_options');
    if (empty($opts['name'])) {
        return $content;
    }

    $image = '';
    if (!empty($opts['image'])) {
        $image = '<div class="wzjob_author-avatar-wrapper">
                    <img src="'.esc_url($opts['image']).'" class="wzjob_author-avatar" alt="'.esc_attr($opts['name']).'">
                  </div>';
    }

    $email_html = '';
    if (!empty($opts['email'])) {
        $email_html = '<div class="wzjob_author-email">Email: '.esc_html($opts['email']).'</div>';
    }

    $social = '';

    if (!empty($opts['website'])) {
        $social .= '<a href="'.esc_url($opts['website']).'" target="_blank" class="wzjob_author-social-btn wzjob_author-web"><span class="dashicons dashicons-admin-site"></span> Website</a>';
    }

    if (!empty($opts['facebook'])) {
        $social .= '<a href="'.esc_url($opts['facebook']).'" target="_blank" class="wzjob_author-social-btn wzjob_author-fb"><span class="dashicons dashicons-facebook"></span> Facebook</a>';
    }

    if (!empty($opts['instagram'])) {
        $social .= '<a href="'.esc_url($opts['instagram']).'" target="_blank" class="wzjob_author-social-btn wzjob_author-ig"><span class="dashicons dashicons-instagram"></span> Instagram</a>';
    }

    if (!empty($opts['youtube'])) {
        $social .= '<a href="'.esc_url($opts['youtube']).'" target="_blank" class="wzjob_author-social-btn wzjob_author-yt"><span class="dashicons dashicons-video-alt3"></span> YouTube</a>';
    }

    if (!empty($opts['whatsapp'])) {
        $social .= '<a href="'.esc_url($opts['whatsapp']).'" target="_blank" class="wzjob_author-social-btn wzjob_author-wa"><span class="dashicons dashicons-whatsapp"></span> WhatsApp</a>';
    }

    $bio_content = '';
    if (!empty($opts['bio'])) {
        $bio_content = wp_kses_post($opts['bio']);
    }

    $box = '
    <div class="wzjob_author-wrapper">
        <div class="wzjob_author-box">
            <div class="wzjob_author-top-label">Written by</div>
            <div class="wzjob_author-header">
                '.$image.'
                <div class="wzjob_author-title-wrap">
                    <div class="wzjob_author-title">
                        <strong class="wzjob_author-name">'.esc_html($opts['name']).'</strong>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e4/Twitter_Verified_Badge.svg/512px-Twitter_Verified_Badge.svg.png" class="wzjob_author-verified" alt="Verified">
                    </div>
                    '.$email_html.'
                </div>
            </div>
            <hr class="wzjob_author-hr">
            <div class="wzjob_author-bio">'.$bio_content.'</div>
            <hr class="wzjob_author-hr">
            <div class="wzjob_author-social">'.$social.'</div>
        </div>

    </div>';

    return $content . $box;
}

add_action('wp_enqueue_scripts', function(){
    if (is_single()) {
        wp_enqueue_style('dashicons');
    }
});

add_action('wp_head', function(){
    if (!is_single()) return;
    ?>
    <style>
    .wzjob_author-box{margin-top:40px;padding:30px;background:#fff;border-radius:16px;box-shadow:0 8px 25px #00000014}
    .wzjob_author-header{display:flex;align-items:center;gap:20px}
    .wzjob_author-avatar-wrapper{width:90px;height:90px;border-radius:50%;overflow:hidden;border:3px solid #1dae41;flex-shrink:0}
    .wzjob_author-avatar{width:100%;height:100%;object-fit:cover;display:block;padding:5px;border-radius:100%}
    .wzjob_author-title-wrap{display:flex;flex-direction:column}
    .wzjob_author-top-label{font-size:14px;font-weight:600!important;margin-bottom:12px;color:#1dae41; background-color: #4da3ff;color: white;padding: 4px;border-radius: 100px;text-align: center;width: 120px;margin: 0 auto 20px;}
    .wzjob_author-title{display:flex;align-items:center;gap:6px;font-size:22px;}
    .wzjob_author-verified{width:18px;height:18px;}
    .wzjob_author-title{display:flex;align-items:center;gap:8px;font-size:22px}
    .wzjob_author-label{background:#4da3ff;color:#fff;padding:2px 8px;border-radius:10px;font-size:12px;font-weight:bold}
    .wzjob_author-email{margin-top:10px;background:#f1f1f1;padding:6px 12px;border-radius:8px;font-size:13px;text-align:center;font-weight:500}
    .wzjob_author-hr{margin:20px 0;border:0;border-top:1px solid #e5e5e5}
    .wzjob_author-bio{text-align:justify;line-height:1.7}
    .wzjob_author-social{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
    .wzjob_author-social-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;color:#fff;text-decoration:none;font-size:14px;border:2px solid transparent;transition:all .2s ease}
    .wzjob_author-web{background:#000;border-color:#000}
    .wzjob_author-fb{background:#1877f2;border-color:#1877f2}
    .wzjob_author-ig{background:#e1306c;border-color:#e1306c}
    .wzjob_author-yt{background:#ff0000;border-color:#ff0000}
    .wzjob_author-wa{background:#25d366;border-color:#25d366}
    .wzjob_author-social-btn:hover{background:#fff;color:#000}
    @media(max-width:600px){.wzjob_author-header{flex-direction:column;text-align:center}}
    </style>
    <?php
});