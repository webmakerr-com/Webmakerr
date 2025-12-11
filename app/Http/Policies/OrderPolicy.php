<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;

class OrderPolicy extends Policy
{
    public function verifyRequest(Request $request)
    {
        return $this->hasRoutePermissions();
    }
}
