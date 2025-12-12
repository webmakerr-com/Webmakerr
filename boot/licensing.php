<?php

use Webmakerr\FluentLicensing;
use Webmakerr\LicenseSettings;

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    if (!class_exists('\\Webmakerr\\FluentLicensing')) {
        require_once WEBMAKERR_PLUGIN_PATH . 'updater/FluentLicensing.php';
    }

    if (!class_exists('\\Webmakerr\\LicenseSettings')) {
        require_once WEBMAKERR_PLUGIN_PATH . 'updater/LicenseSettings.php';
    }

    $licensing = (new FluentLicensing())->register([
        'version'           => defined('FLUENTCART_VERSION') ? FLUENTCART_VERSION : '1.0.0',
        'item_id'           => 112,
        'basename'          => plugin_basename(WEBMAKERR_PLUGIN_FILE),
        'api_url'           => 'https://webmakerr.com/',
        'plugin_title'      => 'Webmakerr',
        'purchase_url'      => 'https://webmakerr.com/',
        'activate_url'      => admin_url('admin.php?page=webmakerr-manage-license'),
        'show_check_update' => true,
    ]);

    $licenseMessage = $licensing->getLicenseMessages();
    if ($licenseMessage) {
        add_action('admin_notices', function () use ($licenseMessage) {
            $class = 'notice notice-error fluent-cart-notice';
            $message = $licenseMessage['message'];
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
        });
    }

    (new LicenseSettings())
        ->register($licensing)
        ->setConfig([
            'menu_title'   => __('Webmakerr License', 'webmakerr'),
            'page_title'   => __('Webmakerr License', 'webmakerr'),
            'title'        => __('Webmakerr License', 'webmakerr'),
            'license_key'  => __('License Key', 'webmakerr'),
            'purchase_url' => 'https://webmakerr.com/',
            'account_url'  => 'https://webmakerr.com/account',
            'plugin_name'  => 'Webmakerr',
        ])
        ->addPage([
            'type'        => 'submenu',
            'parent_slug' => 'webmakerr',
        ]);
});
