<?php

namespace FluentCart\App\Modules\PaymentMethods\AddonGateways;

use FluentCart\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;
use FluentCart\App\Services\Payments\PaymentInstance;
use FluentCart\App\Vite;
use FluentCart\Framework\Support\Arr;

class PaystackAddon extends AbstractPaymentGateway
{
    public array $supportedFeatures = [];

    private $addonSlug = 'paystack-for-fluent-cart';
    private $addonFile = 'paystack-for-fluent-cart/paystack-for-fluent-cart.php';

    public function __construct()
    {
        parent::__construct(new AddonGatewaySettings('paystack'));
    }

    public function meta(): array
    {
        return [
            'title' => 'Paystack',
            'route' => 'paystack',
            'slug' => 'paystack',
            'description' => 'Pay securely with Paystack - Cards, Bank Transfer, USSD, and Mobile Money',
            'logo' => Vite::getAssetUrl("images/payment-methods/paystack-logo.svg"),
            'icon' => Vite::getAssetUrl("images/payment-methods/paystack-logo.svg"),
            'brand_color' => '#0fa958',
            'status' => false,
            'requires_pro' => true,
            'is_addon' => false
        ];
    }

    public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance)
    {
        // This will not be called since the gateway is not active
        return null;
    }

    public function handleIPN()
    {
        // This will not be called since the gateway is not active
    }

    public function getOrderInfo(array $data)
    {
        // This will not be called since the gateway is not active
        return null;
    }

    public function fields()
    {
        // This will show the Upgrade to Pro message
        return [
        ];
    }
}
