<?php

namespace Webmakerr\App\Hooks\Handlers\ShortCodes\Checkout;


use Webmakerr\App\Helpers\CartHelper;
use Webmakerr\App\Hooks\Handlers\ShortCodes\ShortCode;
use Webmakerr\App\Services\Renderer\CheckoutRenderer;


class CheckoutShippingMethodsShortCode extends ShortCode
{
    protected static string $shortCodeName = 'fluent_cart_checkout_shipping_methods';


    public function render($viewData = null)
    {
        $cart = CartHelper::getCart();
        (new CheckoutRenderer($cart))->getFragment('shipping_methods');
    }
}
