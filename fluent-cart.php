<?php

defined('ABSPATH') or die;

/*
Plugin Name: Webmakerr Cart
Description: Webmakerr Cart makes eCommerce, bookings, and service selling fast and effortless.
Version: 1.3.1
Author: Webmakerr
Author URI: https://webmakerr.com
Plugin URI: https://webmakerr.com
License: GPLv2 or later
Text Domain: webmakerr-cart
Domain Path: /language
*/

if (!defined('WEBMAKERR_PLUGIN_PATH')) {
    $legacyUploadDir = defined('FLUENTCART_UPLOAD_DIR') ? FLUENTCART_UPLOAD_DIR : 'fluent_cart';

    define('WEBMAKERR_VERSION', defined('FLUENTCART_VERSION') ? FLUENTCART_VERSION : '1.3.1');
    define('WEBMAKERR_DB_VERSION', defined('FLUENTCART_DB_VERSION') ? FLUENTCART_DB_VERSION : '1.0.31');
    define('WEBMAKERR_PLUGIN_PATH', defined('FLUENTCART_PLUGIN_PATH') ? FLUENTCART_PLUGIN_PATH : plugin_dir_path(__FILE__));
    define('WEBMAKERR_URL', defined('FLUENTCART_URL') ? FLUENTCART_URL : plugin_dir_url(__FILE__));
    define('WEBMAKERR_PLUGIN_FILE_PATH', defined('FLUENTCART_PLUGIN_FILE_PATH') ? FLUENTCART_PLUGIN_FILE_PATH : __FILE__);
    define('WEBMAKERR_UPLOAD_DIR', 'webmakerr_cart');
    define('WEBMAKERR_LEGACY_UPLOAD_DIR', $legacyUploadDir);
    define('WEBMAKERR_DIR_FILE', defined('FLUENT_CART_DIR_FILE') ? FLUENT_CART_DIR_FILE : __FILE__);
    define('WEBMAKERR_MIN_PRO_VERSION', defined('FLUENTCART_MIN_PRO_VERSION') ? FLUENTCART_MIN_PRO_VERSION : '1.3.1');
    define('WEBMAKERR_PRO_PLUGIN_BASENAME', defined('FLUENTCART_PRO_PLUGIN_BASENAME') ? FLUENTCART_PRO_PLUGIN_BASENAME : 'webmakerr-cart-pro/webmakerr-cart-pro.php');
}

// Provide legacy constants for backwards compatibility with FluentCart extensions.
defined('FLUENTCART_VERSION') || define('FLUENTCART_VERSION', WEBMAKERR_VERSION);
defined('FLUENTCART_DB_VERSION') || define('FLUENTCART_DB_VERSION', WEBMAKERR_DB_VERSION);
defined('FLUENTCART_PLUGIN_PATH') || define('FLUENTCART_PLUGIN_PATH', WEBMAKERR_PLUGIN_PATH);
defined('FLUENTCART_URL') || define('FLUENTCART_URL', WEBMAKERR_URL);
defined('FLUENTCART_PLUGIN_FILE_PATH') || define('FLUENTCART_PLUGIN_FILE_PATH', WEBMAKERR_PLUGIN_FILE_PATH);
defined('FLUENTCART_UPLOAD_DIR') || define('FLUENTCART_UPLOAD_DIR', WEBMAKERR_LEGACY_UPLOAD_DIR);
defined('FLUENT_CART_DIR_FILE') || define('FLUENT_CART_DIR_FILE', WEBMAKERR_DIR_FILE);
defined('FLUENTCART_MIN_PRO_VERSION') || define('FLUENTCART_MIN_PRO_VERSION', WEBMAKERR_MIN_PRO_VERSION);
defined('FLUENTCART_PRO_PLUGIN_BASENAME') || define('FLUENTCART_PRO_PLUGIN_BASENAME', WEBMAKERR_PRO_PLUGIN_BASENAME);

register_activation_hook(__FILE__, function () {
    update_option('fluent_cart_do_activation_redirect', true);
});

$bootstrap = require __DIR__ . '/boot/app.php';

require __DIR__ . '/vendor/autoload.php';
// require __DIR__ . '/dev/build-scoped/vendor/scoper-autoload.php'

return $bootstrap(__FILE__);
