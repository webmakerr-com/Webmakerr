<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;

class CustomerPolicy extends Policy
{
    public function verifyRequest(Request $request): bool
    {
        return $this->hasRoutePermissions();
    }
}