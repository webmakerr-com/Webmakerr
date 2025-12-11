<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;

class CouponPolicy extends Policy
{
    public function verifyRequest(Request $request): bool
    {
        return $this->hasRoutePermissions();
    }
}