<?php

/*
Plugin Name: Webmakerr
Description: Webmakerr makes eCommerce, bookings, and service selling fast and effortless.
Version: 1.3.1
Author: Webmakerr Team
Author URI: https://webmakerr.com/about-us
Plugin URI: https://webmakerr.com
License: GPLv2 or later
Text Domain: webmakerr
Domain Path: /language
*/

defined('ABSPATH') or die;

if (!defined('WEBMAKERR_PLUGIN_FILE')) {
    define('WEBMAKERR_PLUGIN_FILE', __FILE__);
    define('WEBMAKERR_PLUGIN_PATH', plugin_dir_path(__FILE__));
    define('WEBMAKERR_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('FLUENTCART_PLUGIN_PATH')) {
    define('FLUENTCART_VERSION', '1.3.1');
    define('FLUENTCART_DB_VERSION', '1.0.31');
    define('FLUENTCART_PLUGIN_PATH', WEBMAKERR_PLUGIN_PATH);
    define('FLUENTCART_URL', WEBMAKERR_PLUGIN_URL);
    define('FLUENTCART_PLUGIN_FILE_PATH', WEBMAKERR_PLUGIN_FILE);
    define('FLUENTCART_UPLOAD_DIR', 'fluent_cart');
    define('FLUENT_CART_DIR_FILE', WEBMAKERR_PLUGIN_FILE);
    define('FLUENTCART_MIN_PRO_VERSION', '1.3.1');
    define('FLUENTCART_PRO_PLUGIN_BASENAME', 'fluentcart-pro/fluentcart-pro.php');
}

require __DIR__ . '/boot/licensing.php';

if (is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_init', function () {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (get_transient('webmakerr_plugin_entry_notice_shown')) {
            return;
        }

        set_transient('webmakerr_plugin_entry_notice_shown', true, DAY_IN_SECONDS);

        add_action('admin_notices', function () {
            $entry_file = plugin_basename(WEBMAKERR_PLUGIN_FILE);

            echo '<div class="notice notice-info"><p>' . esc_html(sprintf(__('Webmakerr is loading from: %s', 'webmakerr'), $entry_file)) . '</p></div>';
        });
    });
}

register_activation_hook(WEBMAKERR_PLUGIN_FILE, function () {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $conflicting_plugin = 'fluent-cart/fluent-cart.php';

    if (is_plugin_active($conflicting_plugin)) {
        // Prevent activation when FluentCart is already active.
        set_transient('webmakerr_fluentcart_conflict_notice', true, 30);
        deactivate_plugins(plugin_basename(WEBMAKERR_PLUGIN_FILE));

        return;
    }

    update_option('fluent_cart_do_activation_redirect', true);
});

add_action('activated_plugin', function ($plugin) {
    if ($plugin !== 'fluent-cart/fluent-cart.php') {
        return;
    }

    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    if (is_plugin_active(plugin_basename(WEBMAKERR_PLUGIN_FILE))) {
        // Deactivate this plugin when FluentCart gets activated.
        set_transient('webmakerr_fluentcart_conflict_notice', true, 30);
        deactivate_plugins(plugin_basename(WEBMAKERR_PLUGIN_FILE));
    }
});

add_action('admin_notices', function () {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    $notice_flag = get_transient('webmakerr_fluentcart_conflict_notice');

    if (!$notice_flag) {
        return;
    }

    delete_transient('webmakerr_fluentcart_conflict_notice');

    echo '<div class="notice notice-error"><p>' . esc_html__('This plugin cannot be used together with FluentCart. Please deactivate FluentCart first.', 'webmakerr-cart') . '</p></div>';
});

return (function ($_) {
    return $_(WEBMAKERR_PLUGIN_FILE);
})(
    require __DIR__ . '/boot/app.php',
    require __DIR__ . '/vendor/autoload.php',
    //require __DIR__ . '/dev/build-scoped/vendor/scoper-autoload.php'
);
