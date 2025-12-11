<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Foundation\Policy;

class PublicPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param \Webmakerr\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return true;
    }

    /**
     * Check user permission for any method
     * @param \Webmakerr\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function create(Request $request)
    {
        return true;
    }
}
