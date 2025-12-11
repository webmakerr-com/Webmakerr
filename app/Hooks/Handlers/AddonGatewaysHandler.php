<?php

namespace Webmakerr\App\Hooks\Handlers;

use Webmakerr\App\Modules\PaymentMethods\AddonGateways\PaystackAddon;
use Webmakerr\App\Modules\PaymentMethods\Core\GatewayManager;

class AddonGatewaysHandler
{
    public function register()
    {
        webmakerr_add_action('webmakerr_cart/register_payment_methods', [$this, 'registerPromoGateways'], 20);
    }
    
    public function registerPromoGateways()
    {
        foreach ([
            'paystack' => PaystackAddon::class,
        ] as $slug => $addonClass) {
            $isGatewayRegistered = GatewayManager::has($slug);
            if (!$isGatewayRegistered) {
                $gateway = GatewayManager::getInstance();
                $gateway->register($slug, new $addonClass());
            }
        }
        
    }
    
}
