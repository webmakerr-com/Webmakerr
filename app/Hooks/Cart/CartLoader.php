<?php

namespace Webmakerr\App\Hooks\Cart;

use Webmakerr\Api\CurrencySettings;
use Webmakerr\Api\Resource\FrontendResource\CartResource;
use Webmakerr\Api\StoreSettings;
use Webmakerr\App\App;
use Webmakerr\App\Helpers\CartHelper;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\Helpers\UtmHelper;
use Webmakerr\App\Modules\Templating\AssetLoader;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Services\Renderer\CartRenderer;
use Webmakerr\App\Services\Renderer\CartDrawerRenderer;

class CartLoader
{
    public function register()
    {
        add_action('wp_footer', [$this, 'init']);
    }

    public function init(): void
    {
        static $loadedOnce = false;

        if ($loadedOnce) {
            return;
        }

        $enableNavFloatingButton = webmakerr_apply_filters('webmakerr_cart/buttons/enable_floating_cart_button', true, []);

        if (!$enableNavFloatingButton) {
            return;
        }

        $loadedOnce = true;


        $this->registerDependency();

        if (self::shouldHideCartDrawer()) {
            return;
        }

        $cart = CartHelper::getCart(null, false);
        $itemCount = 0;

        if ($cart) {
            $itemCount = count($cart->cart_data ?? []);
        }

        $cartItems = Arr::get(CartResource::getStatus(), 'cart_data', []);

        if(empty($cartItems)){
            return;
        }

        (new CartDrawerRenderer($cartItems, [
            'item_count' => $itemCount
        ]))->render();
    }

    public function registerDependency(): void
    {
        AssetLoader::loadCartAssets();
    }

    public function enqueueStyle()
    {
        $app = fluentCart();
        $slug = $app->config->get('app.slug');

        Vite::enqueueStyle(
            $slug . '-fluentcart-drawer',
            'public/cart-drawer/cart-drawer.scss',
        );
    }

    public static function shouldHideCartDrawer()
    {
        global $post;
        $currentPageId = $post->ID ?? null;
        $storeSettings = new StoreSettings();
        $cartPageId = $storeSettings->getCheckoutPageId();
        $receiptPageId = $storeSettings->getReceiptPageId();

        if (!CartHelper::doingInstantCheckout() && $cartPageId != $currentPageId && $receiptPageId != $currentPageId) {
            return false;
        }
        return true;
    }
}
