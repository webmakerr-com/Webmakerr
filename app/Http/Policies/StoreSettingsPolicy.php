<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;

class StoreSettingsPolicy extends Policy
{
    public function verifyRequest(Request $request)
    {
        webmakerr_do_action('webmakerr_cart/policy/store_settings_request', [
            'request' => $request
        ]);

        return $this->hasRoutePermissions();
    }
}
