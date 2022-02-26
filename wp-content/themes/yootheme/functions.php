<?php

/**
 * Boostrap theme.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions
 */
$app = require 'bootstrap.php';
$app->load(__DIR__ . '/{vendor/yootheme/{platform-wordpress,theme{,-analytics,-cookie,-highlight,-settings,-wordpress*},styler,builder{,-source*,-templates,-newsletter,-wordpress*}}/bootstrap.php,config.php}');

/**
 * Fire the wp_body_open action.
 *
 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
 */
if (!function_exists('wp_body_open')) {
    function wp_body_open() {
        do_action('wp_body_open');
    }
}

/**
 * Try to remove `wp-content/install.php` which wasn't removed during demo installation.
 */
add_action('admin_notices', function () {

    if (!is_file($file = WP_CONTENT_DIR . '/install.php')) {
        return;
    }

    $contents = @file_get_contents($file, false, null, 0, 500) ?: '';

    if (strpos($contents, 'shutdown') && !@unlink($file)) {
        printf(
            '<div class="notice notice-warning"><h2>%s</h2><p>%s</p></div>',
            'Action required: Critical vulnerability in your installation',
            'YOOtheme Pro was unable to remove the <code>wp-content/install.php</code> file. This file was used during the installation of the YOOtheme Pro demo package. It can potentially be used to reset the database. Please delete the file manually.'
        );
    }

});
