<?php

namespace Webmakerr\App\Modules\Subscriptions\Http\Handlers;

use Webmakerr\App\Vite;
use Webmakerr\Framework\Support\Arr;

class AdminMenuHandler
{
    public function register()
    {
        webmakerr_add_action('webmakerr_cart/loading_app', function () {
            Vite::enqueueScript('fluent_cart_subscriptions', 'admin/Modules/Subscriptions/subscription.js');
        });

        webmakerr_add_filter('webmakerr_cart/global_admin_menu_items', [$this, 'addSubscriptionAdminMenu'], 10, 2);
    }

    public function addSubscriptionAdminMenu ($items, $args)
    {
        $baseUrl = Arr::get($args, 'base_url');

        $items['subscriptions'] = [
            'label' => __('Subscriptions', 'fluent-cart'),
            'link'  => $baseUrl . 'subscriptions',
            'permission' => ['subscriptions/view']
        ];

        return $items;
    }

}

