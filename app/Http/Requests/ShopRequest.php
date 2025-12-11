<?php

namespace Webmakerr\App\Http\Requests;

use Webmakerr\Framework\Foundation\RequestGuard;

class ShopRequest extends RequestGuard
{

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }


    /**
     * @return array
     */
    public function messages()
    {
        return [];
    }


    /**
     * @return array
     */
    public function sanitize()
    {
        return [];
    }
}