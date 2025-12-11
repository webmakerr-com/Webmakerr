<?php

namespace Webmakerr\App\Hooks\Handlers\ShortCodes\Checkout;

use Webmakerr\Api\Resource\CustomerResource;
use Webmakerr\App\App;
use Webmakerr\App\Helpers\AddressHelper;
use Webmakerr\App\Models\Cart;
use Webmakerr\App\Modules\Templating\AssetLoader;
use Webmakerr\App\Services\Renderer\CheckoutRenderer;
use Webmakerr\App\Services\URL;
use Webmakerr\Api\StoreSettings;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\Models\Customer;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Helpers\CartHelper;
use Webmakerr\App\Modules\Tax\TaxModule;
use Webmakerr\App\Services\CheckoutService;
use Webmakerr\App\Services\TemplateService;
use Webmakerr\App\Helpers\CartCheckoutHelper;
use Webmakerr\Api\Resource\CustomerAddressResource;
use Webmakerr\App\Hooks\Handlers\ShortCodes\ShortCode;
use Webmakerr\App\Services\Localization\LocalizationManager;
use Webmakerr\App\Hooks\Handlers\BlockEditors\CheckoutBlockEditor;
use Webmakerr\App\Services\Renderer\CartRenderer;

class CheckoutPageHandler extends ShortCode
{
    const SHORT_CODE = 'fluent_cart_checkout';
    protected static string $shortCodeName = 'fluent_cart_checkout';

    public static function register()
    {
        parent::register();
    }

    public function render(?array $viewData = null)
    {
        $cart = CartHelper::getCart();

        if (!$cart || empty($cart->cart_data)) {
            ob_start();
            (new CartRenderer())->renderEmpty();
            
            return ob_get_clean();
        }

        // Push the shipping and billing address from id
        $checkoutData = $cart->checkout_data;
        $formData = Arr::get($checkoutData, 'form_data', []);

        $currentCustomer = CustomerResource::getCurrentCustomer();

        if ($currentCustomer && empty($formData['billing_country']) && empty($formData['billing_address_id'])) {
            // this is a new cart. So we should fill the address id if any
            $primaryBillingAddress = $currentCustomer->primary_billing_address;
            if ($primaryBillingAddress) {
                $formData['billing_address_id'] = $primaryBillingAddress->id;
            }

            if ($cart->isShipToDifferent()) {
                $primaryShippingAddress = $currentCustomer->primary_shipping_address;
                if ($primaryShippingAddress) {
                    $formData['shipping_address_id'] = $primaryShippingAddress->id;
                }
            }
        }

        $formData = AddressHelper::maybePushAddressDataForCheckout($formData, 'billing');
        if ($cart->isShipToDifferent()) {
            $formData = AddressHelper::maybePushAddressDataForCheckout($formData, 'shipping');
        }

        if(empty($formData['billing_country'])) {
            $formData['billing_country'] = AddressHelper::getDefaultBillingCountryForCheckout();
        }
        
        $checkoutData['form_data'] = $formData;
        $cart->checkout_data = $checkoutData;
        $cart->save();

        webmakerr_do_action('webmakerr_cart/cart/cart_data_items_updated', [
            'cart'       => $cart,
            'scope'      => 'loading',
            'scope_data' => ''
        ]);


        AssetLoader::loadCheckoutAssets($cart);

        ob_start();
        (new CheckoutRenderer($cart))->render();
        return ob_get_clean();
    }
}
