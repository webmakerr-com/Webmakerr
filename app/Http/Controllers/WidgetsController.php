<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\Api\Resource\OrderResource;
use Webmakerr\App\Services\Permission\PermissionManager;
use Webmakerr\Framework\Http\Request\Request;

class WidgetsController extends Controller
{
    public function __invoke(Request $request): \WP_REST_Response
    {
        if (!PermissionManager::hasAnyPermission(['customers/view', 'orders/view'])) {
            return $this->sendError([
                'message' => __('You do not have permission to access this resource', 'webmakerr-cart')
            ]);
        }

        $filter = $request->get('filter') ?? '';
        $filter = str_replace('fluent_cart_', '', $filter);
        $data = $request->get('data');

        return $this->sendSuccess([
            'widgets' => webmakerr_apply_filters('webmakerr_cart/widgets/' . $filter, [], $data)
        ]);
    }
}