<?php

namespace Webmakerr\App\Hooks\Handlers;

use Webmakerr\App\Modules\PaymentMethods\ProGateways\PaddlePromo;
use Webmakerr\App\Modules\PaymentMethods\ProGateways\MolliePromo;
use Webmakerr\App\Modules\PaymentMethods\Core\GatewayManager;

class PromoGatewaysHandler
{
    public function register()
    {
        webmakerr_add_action('webmakerr_cart/register_payment_methods', [$this, 'registerPromoGateways'], 20);
    }
    
    public function registerPromoGateways()
    {
        $isProActive = defined('WEBMAKERR_PRO_PLUGIN_VERSION');   

        foreach ([
            'paddle' => PaddlePromo::class,
            'mollie' => MolliePromo::class,
        ] as $slug => $promoClass) {
            $isGatewayRegistered = GatewayManager::has($slug);
            if (!$isGatewayRegistered && !$isProActive) {
                $gateway = GatewayManager::getInstance();
                $gateway->register($slug, new $promoClass());
            }
        }
        
    }
    
}
