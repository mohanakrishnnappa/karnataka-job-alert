<?php
/**
 * Plugin Name: Job Plugin by WebZeeto
 * Description: Essential plugin for Webzeeto Job Portal, providing core functionalities and extensions.
 * Version: 1.0.0
 * Author: Mohana Krishnnappa
 */

if (!defined('ABSPATH')) {
    exit;
} // Security: prevent direct access

define( 'WZJOB_PATH', plugin_dir_path( __FILE__ ) );
define( 'WZJOB_URL',  plugin_dir_url( __FILE__ ) );

// ── Core (load first — others depend on these) ──────────────────────────────
require_once WZJOB_PATH . 'extensions/admin-page.php';

require_once WZJOB_PATH . 'extensions/blog-post-display.php';
require_once WZJOB_PATH . 'extensions/author-profile.php';