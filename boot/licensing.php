<?php

use Webmakerr\FluentLicensing;
use Webmakerr\LicenseSettings;

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('webmakerr_get_license_status')) {
    function webmakerr_get_license_status($remoteFetch = false)
    {
        $defaultStatus = [
            'license_key'     => '',
            'status'          => 'unregistered',
            'variation_id'    => '',
            'variation_title' => '',
            'expires'         => '',
            'activation_hash' => ''
        ];

        try {
            if (!class_exists('\\Webmakerr\\FluentLicensing')) {
                require_once WEBMAKERR_PLUGIN_PATH . 'updater/FluentLicensing.php';
            }

            $licensing = \Webmakerr\FluentLicensing::getInstance();
            $status = $licensing->getStatus($remoteFetch);

            if (is_array($status)) {
                return wp_parse_args($status, $defaultStatus);
            }
        } catch (\Throwable $exception) {
            // We will fallback to saved data when the licensing instance is not available yet.
        }

        $savedStatus = get_option('__webmakerr_sl_info', []);

        return wp_parse_args($savedStatus, $defaultStatus);
    }
}

if (!function_exists('webmakerr_is_license_active')) {
    function webmakerr_is_license_active()
    {
        $licenseStatus = webmakerr_get_license_status();

        return isset($licenseStatus['status']) && $licenseStatus['status'] === 'valid';
    }
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
