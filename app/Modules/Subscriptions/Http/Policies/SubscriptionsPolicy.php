<?php

namespace Webmakerr\App\Modules\Subscriptions\Http\Policies;

use Webmakerr\Framework\Foundation\Policy;
use Webmakerr\Framework\Http\Request\Request;

class SubscriptionsPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param \Webmakerr\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return true;
        return current_user_can('manage_options');
    }

    /**
     * Check user permission for any method
     * @param \Webmakerr\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function create(Request $request)
    {
        return current_user_can('manage_options');
    }
}
