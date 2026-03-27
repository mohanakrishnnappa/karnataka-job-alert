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
    register_setting(
        'wzjob_author_settings_group',
        'wzjob_author_options',
        ['sanitize_callback' => 'wzjob_author_sanitize_options']
    );
}

function wzjob_author_sanitize_options($input) {
    $clean = [];
    $clean['name']      = sanitize_text_field($input['name'] ?? '');
    $clean['email']     = sanitize_email($input['email'] ?? '');
    $clean['image']     = esc_url_raw($input['image'] ?? '');
    $clean['bio']       = wp_kses_post($input['bio'] ?? '');
    $clean['website']   = esc_url_raw($input['website'] ?? '');
    $clean['facebook']  = esc_url_raw($input['facebook'] ?? '');
    $clean['instagram'] = esc_url_raw($input['instagram'] ?? '');
    $clean['youtube']   = esc_url_raw($input['youtube'] ?? '');
    $clean['whatsapp']  = esc_url_raw($input['whatsapp'] ?? '');
    $clean['verified']  = isset($input['verified']) ? '1' : '';
    return $clean;
}

/* ================================
   SETTINGS PAGE HTML
================================ */

function wzjob_author_settings_page_html() {

    if (!current_user_can('manage_options')) {
        return;
    }

    $opts = get_option('wzjob_author_options', []);
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

                        <tr>
                            <th>Verified Author</th>
                            <td>
                                <input type="checkbox" name="wzjob_author_options[verified]" value="1"
                                <?php checked($opts['verified'] ?? '', '1'); ?>>
                                Mark as verified author
                            </td>
                        </tr>

                    </table>

                    <?php submit_button(); ?>
                </form>
            </div>

            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0;">⁉️ Where is it Stored?</h2>
                <hr style="margin: 10px 0 20px 0;">
                <p>It is stored in <code>wp_options</code></p>
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
    // Fixed: corrected hook name to match parent slug 'webzeeto-job'
    if ($hook === 'webzeeto-job_page_wzjob-author-profile-display') {
        wp_enqueue_media();
    }
}

/* ================================
   FRONTEND AUTHOR BOX
================================ */

add_filter('the_content', 'wzjob_author_display_box');

function wzjob_author_display_box($content) {

    if (
        (!is_single() && !is_front_page())
        || is_home()
        || !in_the_loop()
        || !is_main_query()
    ) {
        return $content;
    }

    $opts = get_option('wzjob_author_options', []);
    if (empty($opts['name'])) {
        return $content;
    }

    // Profile image
    $image = '';
    if (!empty($opts['image'])) {
        $image = '<div class="wzjob_author-avatar-wrapper">
                    <img src="' . esc_url($opts['image']) . '" class="wzjob_author-avatar" alt="' . esc_attr($opts['name']) . '">
                  </div>';
    }

    // Email
    $email_html = '';
    if (!empty($opts['email'])) {
        $email_html = '<div class="wzjob_author-email">Email: ' . esc_html($opts['email']) . '</div>';
    }

    // Fixed: $verified_badge defined BEFORE $box string
    $verified_badge = '';
    if (!empty($opts['verified'])) {
        $verified_badge = '<span class="wzjob-verified-badge" title="Verified Author">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="12" height="12">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </span>';
    }

    // Author name — conditionally wrap in <a> only if website exists
    $author_name_html = !empty($opts['website'])
        ? '<a href="' . esc_url($opts['website']) . '" rel="author" target="_blank">' . esc_html($opts['name']) . '</a>'
        : '<span>' . esc_html($opts['name']) . '</span>';

    // Social links
    $social = '';
    if (!empty($opts['website'])) {
        $social .= '<a href="' . esc_url($opts['website']) . '" target="_blank" class="wzjob_author-social-btn wzjob_author-web"><span class="dashicons dashicons-admin-site"></span> Website</a>';
    }
    if (!empty($opts['facebook'])) {
        $social .= '<a href="' . esc_url($opts['facebook']) . '" target="_blank" class="wzjob_author-social-btn wzjob_author-fb"><span class="dashicons dashicons-facebook"></span> Facebook</a>';
    }
    if (!empty($opts['instagram'])) {
        $social .= '<a href="' . esc_url($opts['instagram']) . '" target="_blank" class="wzjob_author-social-btn wzjob_author-ig"><span class="dashicons dashicons-instagram"></span> Instagram</a>';
    }
    if (!empty($opts['youtube'])) {
        $social .= '<a href="' . esc_url($opts['youtube']) . '" target="_blank" class="wzjob_author-social-btn wzjob_author-yt"><span class="dashicons dashicons-video-alt3"></span> YouTube</a>';
    }
    if (!empty($opts['whatsapp'])) {
        $social .= '<a href="' . esc_url($opts['whatsapp']) . '" target="_blank" class="wzjob_author-social-btn wzjob_author-wa"><span class="dashicons dashicons-whatsapp"></span> WhatsApp</a>';
    }

    // Bio
    $bio_content = '';
    if (!empty($opts['bio'])) {
        $bio_content = wp_kses_post($opts['bio']);
    }

    $box = '
    <div class="wzjob_author-wrapper">
        <div class="wzjob_author-box">
            <div class="wzjob_author-top-label">Written by</div>
            <div class="wzjob_author-header">
                ' . $image . '
                <div class="wzjob_author-title-wrap">
                    <div class="wzjob_author-title">
                        <strong class="wzjob_author-name">
                            ' . $author_name_html . '
                        </strong>
                        ' . $verified_badge . '
                    </div>
                    ' . $email_html . '
                </div>
            </div>
            <hr class="wzjob_author-hr">
            <div class="wzjob_author-bio">' . $bio_content . '</div>
            <hr class="wzjob_author-hr">
            <div class="wzjob_author-social">' . $social . '</div>
        </div>
    </div>';

    return $content . $box;
}

add_action('wp_enqueue_scripts', function () {
    if (is_single()) {
        wp_enqueue_style('dashicons');
    }
});

add_action('wp_head', function () {
    if (!is_single() && !is_front_page()) return;
    ?>
    <style>
    .wzjob_author-box{margin-top:40px;padding:30px;background:#fff;border-radius:16px;box-shadow:0 8px 25px #00000014}
    .wzjob_author-header{display:flex;align-items:center;gap:20px}
    .wzjob_author-avatar-wrapper{width:90px;height:90px;border-radius:50%;overflow:hidden;border:3px solid #1dae41;flex-shrink:0}
    .wzjob_author-avatar{width:100%;height:100%;object-fit:cover;display:block;padding:5px;border-radius:100%}
    .wzjob_author-title-wrap{display:flex;flex-direction:column}
    .wzjob_author-top-label{font-size:14px;font-weight:600!important;margin-bottom:12px;color:#1dae41;background-color:#4da3ff;color:white;padding:4px;border-radius:100px;text-align:center;width:120px;margin:0 auto 20px;}
    .wzjob_author-title{display:flex;align-items:center;font-size:22px}
    .wzjob_author-verified{width:18px;height:18px;}
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
    .wzjob-verified-badge {display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:#4da3ff;border-radius:50%;margin-left:6px;position:relative;top:1px;animation:badge-pop 0.3s ease;}
    @media(max-width:600px){.wzjob_author-header{flex-direction:column;text-align:center}}
    </style>
    <?php
});

add_action('wp_head', function () {

    if (!is_single() && !is_front_page()) return;

    $opts = get_option('wzjob_author_options', []);
    if (empty($opts['name'])) return;

    $author_schema = [
        "@context" => "https://schema.org",
        "@type"    => "Person",
        "name"     => $opts['name'],
        "email"    => $opts['email'] ?? '',
        "url"      => $opts['website'] ?? '',
        "image"    => $opts['image'] ?? '',
        "jobTitle" => "Content Publisher",
        "worksFor" => [
            "@type" => "Organization",
            "name"  => get_bloginfo('name'),
            "url"   => home_url()
        ],
        "sameAs" => array_values(array_filter([
            $opts['facebook'] ?? '',
            $opts['instagram'] ?? '',
            $opts['youtube'] ?? '',
            $opts['website'] ?? ''
        ])),
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($author_schema) . '</script>';
});

add_action('wp_head', function () {

    if (!is_single()) return;

    global $post;
    $opts = get_option('wzjob_author_options', []);
    if (empty($opts['name'])) return;

    $article_schema = [
        "@context" => "https://schema.org",
        "@type"    => "Article",
        "headline" => get_the_title($post),
        "datePublished" => get_the_date('c', $post),
        "dateModified"  => get_the_modified_date('c', $post),
        "author" => [
            "@type" => "Person",
            "name"  => $opts['name'],
            "url"   => $opts['website'] ?? '',
            "sameAs" => array_values(array_filter([
                $opts['facebook'] ?? '',
                $opts['instagram'] ?? '',
                $opts['youtube'] ?? ''
            ])),
        ],
        "publisher" => [
            "@type" => "Organization",
            "name"  => get_bloginfo('name'),
            "url"   => home_url(),
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($article_schema) . '</script>';
});