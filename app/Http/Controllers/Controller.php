<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\Models\User;
use Webmakerr\App\Services\RoleManager;
use Webmakerr\Framework\Http\Controller as BaseController;

abstract class Controller extends BaseController
{
    /**
     *
     * @param string $permission
     * @return false|string|void
     */
    public function redirectOnUnauthorized(string $permission)
    {
        return true;
    }

    public function getUser()
    {
        return Helper::getCurrentUser();
    }

    public function entityNotFoundError($message, $buttonText = null, $route = '/'): \WP_REST_Response
    {
        return $this->sendError([
            'data' => [
                'message'    => $message,
                'buttonText' => $buttonText ?? __('Back to Dashboard', 'webmakerr-cart'),
                'route'      => $route,
            ],
            'code' => 'fluent_cart_entity_not_found',
        ], 404);
    }

}