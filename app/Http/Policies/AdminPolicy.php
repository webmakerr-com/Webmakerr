<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;

class AdminPolicy extends Policy
{
    public function verifyRequest(Request $request): bool
    {
        return $this->userCan('is_super_admin');
    }
}
