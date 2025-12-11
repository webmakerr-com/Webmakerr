<?php

namespace Webmakerr\App\Modules\Shipping\Http\Handlers;

use Webmakerr\App\Vite;

class ScriptHandler
{
    public function register()
    {
        webmakerr_add_action('webmakerr_cart/loading_app', function () {
            Vite::enqueueScript('fluent_cart_shipping', 'admin/Modules/Shipping/shipping.js');
        });
    }
}
