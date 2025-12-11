<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Foundation\Policy;

class FilePolicy extends Policy
{

    public function verifyRequest(Request $request)
    {
        return true;
        return current_user_can('manage_options');
    }


}
