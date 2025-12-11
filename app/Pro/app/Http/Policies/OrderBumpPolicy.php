<?php

namespace FluentCartPro\App\Http\Policies;
use Webmakerr\App\Http\Policies\Policy;
use Webmakerr\Framework\Http\Request\Request;


class OrderBumpPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request): bool
    {
        return $this->hasRoutePermissions();
    }
}
