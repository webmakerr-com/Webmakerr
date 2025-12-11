<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

use FluentCart\Framework\Foundation\Application;
use FluentCart\App\Hooks\Handlers\ActivationHandler;
use FluentCart\App\Hooks\Handlers\DeactivationHandler;
use FluentCart\App\Services\Pro\ProFeatureManager;

return function ($file) {

    // $errorHandler = __DIR__ . "/error_handler.php";

    // if (0 !== error_reporting() && file_exists($errorHandler)) {
    //     require_once $errorHandler;
    // }

    $app = new Application($file);
    $proFeatures = ProFeatureManager::instance();
    $proFeatures->boot($app);

    register_activation_hook($file, function ($networkwide = false) use ($app) {
        ($app->make(ActivationHandler::class))->handle($networkwide);
    });

    register_deactivation_hook($file, function () use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    require_once(FLUENTCART_PLUGIN_PATH . 'boot/action_scheduler_loader.php');

    add_action('plugins_loaded', function () use ($app, $proFeatures) {
        do_action('fluentcart_loaded', $app);

        (new FluentCart\App\Modules\Data\ProductDataSetup())->boot();

        add_action('init', function () use ($app) {
            do_action('fluent_cart/init', $app);

            if (defined('DATABASE_TYPE') && DATABASE_TYPE === 'sqlite') {
                add_action('admin_notices', function () {
                    ?>
                    <div class="notice notice-warning">
                        <p><?php echo 'Webmakerr requires MySQL/MariaDB database Engine. Looks like you are using sqlite database. Webmakerr will not work on this site.'; ?></p>
                    </div>
                    <?php
                });
            }
        });

    });


    add_action('wp_insert_site', function ($new_site) use ($app) {
        if (is_plugin_active_for_network('fluent-cart/fluent-cart.php')) {
            switch_to_blog($new_site->blog_id);
            ($app->make(ActivationHandler::class))->handle(false);
            restore_current_blog();
        }
    });

    return $app;
};
