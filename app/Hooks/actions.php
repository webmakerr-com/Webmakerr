<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all WordPress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app Webmakerr\Framework\Foundation\Application
 */

use Webmakerr\App\App;
use Webmakerr\App\Services\FileSystem\Drivers\Local\LocalDriver;
use Webmakerr\Framework\Support\Arr;

(new \Webmakerr\App\CPT\FluentProducts)->register();
(new \Webmakerr\App\Services\Email\EmailNotificationMailer)->register();
(new \Webmakerr\App\Hooks\Handlers\CPTHandler)->register();
(new \Webmakerr\App\Hooks\Handlers\MenuHandler)->register();
(new \Webmakerr\App\Hooks\Handlers\AdminMenuBarHandler)->register();
(new \Webmakerr\App\Hooks\Handlers\FluentCartHandler)->register();

(new \Webmakerr\App\Hooks\Handlers\ShortCodes\ShopAppHandler)->register();
(new \Webmakerr\App\Hooks\Handlers\ExportHandler)->register();

(new Webmakerr\App\Hooks\Handlers\CustomCheckout\CustomCheckout())->register();

// Tax Module Init
(new \Webmakerr\App\Modules\Tax\TaxModule())->register();

// Register Pro Gateways Promo
(new \Webmakerr\App\Hooks\Handlers\PromoGatewaysHandler())->register();

// Register Addon Gateways
(new \Webmakerr\App\Hooks\Handlers\AddonGatewaysHandler())->register();


// Web Checkout
(new \Webmakerr\App\Hooks\Cart\WebCheckoutHandler())->register();


\Webmakerr\App\Hooks\Handlers\BlockEditors\ShopApp\ShopAppBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\ShortCodes\SearchBarShortCode::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\SearchBarBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\PricingTableBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\CustomerProfileBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\ShortCodes\CustomerProfileHandler::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\CheckoutBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\ShortCodes\CartShortcode::register();
\Webmakerr\App\Hooks\Handlers\ShortCodes\PricingTableShortCode::register();
\Webmakerr\App\Hooks\Handlers\ShortCodes\Checkout\CheckoutPageHandler::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\ProductCardBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\ProductGalleryBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\ProductInfoBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\BlockEditors\BuySectionBlockEditor::register();
\Webmakerr\App\Hooks\Handlers\ShortCodes\ProductCardShortCode::register();

$registerShortcodeAlias = function ($alias, $originalShortcode) {
    add_shortcode($alias, function ($shortcodeAttributes, $content = null, $block = null) use ($originalShortcode) {
        $attributeString = '';

        foreach ((array) $shortcodeAttributes as $attributeKey => $attributeValue) {
            if (is_bool($attributeValue)) {
                $attributeValue = $attributeValue ? 'true' : 'false';
            } elseif (is_array($attributeValue)) {
                $attributeValue = wp_json_encode($attributeValue);
            }

            $attributeString .= sprintf(' %s="%s"', esc_attr($attributeKey), esc_attr($attributeValue));
        }

        return do_shortcode('[' . $originalShortcode . $attributeString . ']');
    });
};

$registerShortcodeAlias('webmakerr_products', 'fluent_cart_products');
$registerShortcodeAlias('webmakerr_customer_profile', 'fluent_cart_customer_profile');
$registerShortcodeAlias('webmakerr_cart', 'fluent_cart_cart');
$registerShortcodeAlias('webmakerr_receipt', 'fluent_cart_receipt');
$registerShortcodeAlias('webmakerr_checkout', 'fluent_cart_checkout');
$registerShortcodeAlias('webmakerr_toggle_button', 'fcart-cart-toggle-button');

if (\Webmakerr\Api\ModuleSettings::isActive('stock_management')) {
    \Webmakerr\App\Hooks\Handlers\BlockEditors\StockBlock::register();
}

(new \Webmakerr\App\Hooks\Cart\CartLoader)->register();
(new \Webmakerr\App\Hooks\Handlers\GlobalPaymentHandler)->register();

\Webmakerr\App\Http\Routes\WebRoutes::register();

(new \Webmakerr\App\Modules\IntegrationActions\GlobalIntegrationActionHandler())->register();
(new \Webmakerr\App\Hooks\Handlers\GlobalStorageHandler)->register();


//Register Page Handlers
(new \Webmakerr\App\Hooks\Handlers\ShortCodes\ReceiptHandler)->register();

(new \Webmakerr\App\Modules\Coupon\CouponHandler)->register();

\Webmakerr\App\Services\Theme\AdminTheme::applyTheme();
\Webmakerr\App\Services\Theme\FrontendTheme::applyTheme();

(new \Webmakerr\App\CPT\Pages)->handlePageDelete();
(new \Webmakerr\App\Services\FileSystem\DownloadService)->register();

(new \Webmakerr\App\Hooks\Handlers\UserHandler())->register();

\Webmakerr\App\Http\Routes\AjaxRoute::register();


webmakerr_add_action('webmakerr_cart/order_paid_ansyc_private_handle', function ($data) {
    $orderId = \Webmakerr\Framework\Support\Arr::get($data, 'order_id');

    if (!$orderId) {
        return;
    }

    $order = \Webmakerr\App\Models\Order::find($orderId);
    if (!$order || $order->payment_status !== 'paid' || !$order->getMeta('action_scheduler_id')) {
        return;
    }

    $order->deleteMeta('action_scheduler_id');

    $transaction = \Webmakerr\App\Models\OrderTransaction::query()
        ->where('order_id', $order->id)
        ->where('status', \Webmakerr\App\Helpers\Status::TRANSACTION_SUCCEEDED)
        ->orderBy('id', 'DESC')
        ->first();

    $eventData = [
        'order'       => $order,
        'transaction' => $transaction,
        'customer'    => $order->customer
    ];

    if ($order->type === 'subscription' || $order->type === 'renewal') {
        $subscription = \Webmakerr\App\Models\Subscription::query()->where('parent_order_id', $order->id)->first();
        if ($subscription) {
            $eventData['subscription'] = $subscription;
        }
    }

    webmakerr_do_action('webmakerr_cart/order_paid_done', $eventData);

}, 1, 1);


//
//$app->addAction('fluent_cart/orders_filter_customer', function ($query, $filters) {
//    return (new \Webmakerr\App\Models\Order)->buildCustomerFilterQuery($query, $filters);
//}, 10, 2);

// require the CLI
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('fluent_cart', '\Webmakerr\App\Hooks\CLI\Commands');

}

\Webmakerr\App\Modules\Subscriptions\SubscriptionModule::register();
\Webmakerr\App\Modules\Shipping\ShippingModule::register();


$app->ready(function () use ($app) {
    \Webmakerr\App\Models\Connection\ConnectionManager::connect($this->app);
});


// Add to your theme's functions.php or a custom plugin
// For Elementor preview URL


(new \Webmakerr\App\Services\Integration)->register();

$app->addAction('fluent_cart/integration/schedule_feed', function ($queueId) use ($app) {
    (new \Webmakerr\App\Modules\Integrations\GlobalNotificationHandler())->processIntegrationAction($queueId);
});

// Schedulers
(new \Webmakerr\App\Hooks\Scheduler\AutoSchedules\FiveMinuteScheduler())->register();
(new \Webmakerr\App\Hooks\Scheduler\AutoSchedules\HourlyScheduler())->register();
(new \Webmakerr\App\Hooks\Scheduler\AutoSchedules\DailyScheduler())->register();


/**
 * Theme Hooks
 */

add_action('init', [\Webmakerr\App\Modules\Templating\TemplateLoader::class, 'init']);
add_action('after_setup_theme', function () {
    \Webmakerr\App\Modules\Templating\TemplateLoader::registerBlockParts();
    (new \Webmakerr\App\Modules\Templating\Bricks\BricksLoader())->register();
});

add_action('init', function () {
});

/**
 * Development Hooks
 */
