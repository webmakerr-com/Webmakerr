<?php

use FluentCartPro\App\Core\Application;
use Webmakerr\App\Services\Permission\PermissionManager;
use FluentCartPro\App\Services\PluginManager\FluentLicensing;
use FluentCartPro\App\Modules\PaymentMethods\PaddleGateway\Paddle;
use FluentCartPro\App\Modules\PaymentMethods\MollieGateway\Mollie;

return function ($file) {
    webmakerr_add_action('webmakerr_loaded', function ($app) use ($file) {
        new Application($app, $file);

        (new \FluentCartPro\App\Modules\Licensing\Licensing())->register($app);
        (new \FluentCartPro\App\Modules\StockManagement\StockManagement())->register($app);
        (new \FluentCartPro\App\Modules\Promotional\PromotionalInit())->register($app);

        $fluentLicensing = new FluentLicensing;

        $fluentLicensing->register([
            'item_id'      => 21480,
            'basename'     => plugin_basename(WEBMAKERR_PRO_PLUGIN_FILE_PATH),
            'version'      => WEBMAKERR_PRO_PLUGIN_VERSION,
            'api_url'      => 'https://fluentcart.com/',
            'activate_url' => admin_url('admin.php?page=webmakerr#/settings/licensing'),
            'plugin_title' => 'Webmakerr',
        ]);

        $licenseNotice = $fluentLicensing->getLicenseNotice();

        if ($licenseNotice) {
            add_action('admin_notices', function () use ($licenseNotice) {
                $class = 'notice notice-error fluent-cart-notice';
                $message = $licenseNotice['message'];
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
            });

            webmakerr_add_filter('webmakerr_cart/admin_notices', function ($notices) use ($licenseNotice) {
                if (!$licenseNotice || !PermissionManager::userCan('is_super_admin')) {
                    return;
                }

                if (!empty($licenseNotice['message'])) {
                    $notices[] = '<div>' . $licenseNotice['message'] . '</div>';
                }

                return $notices;
            });
        }

        webmakerr_add_action('webmakerr_cart/init', function ($app) {
            Paddle::register();
            Mollie::register();
        });


        // Integrations Modile Initialization
        (new FluentCartPro\App\Modules\Integrations\IntegrationsInit())->register();

    });

    add_action('plugins_loaded', function () {
        if (!defined('WEBMAKERR_VERSION')) {
            (new \FluentCartPro\App\Services\Onboarding\CoreDependencyHandler())->register();
        } else {
            webmakerr_add_filter('webmakerr_cart/admin_notices', function ($notices) {
                if (WEBMAKERR_MIN_CORE_VERSION !== WEBMAKERR_VERSION && version_compare(WEBMAKERR_MIN_CORE_VERSION, WEBMAKERR_VERSION, '>')) {
                    if (!PermissionManager::userCan('is_super_admin')) {
                        return $notices;
                    }

                    $updateUrl = admin_url('plugins.php?s=fluent-cart&plugin_status=all&fluent-cart-check-update=' . time());

                    $notices[] = '<div>FluentCart Base Plugin needs to be updated to the latest version. <a href="' . esc_url($updateUrl) . '">Click here to update</a></div>';
                }
                return $notices;
            });
        }
    });
};
