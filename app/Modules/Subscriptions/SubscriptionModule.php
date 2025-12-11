<?php

namespace Webmakerr\App\Modules\Subscriptions;

use Webmakerr\App\App;
use Webmakerr\App\Modules\Subscriptions\Services\Filter\SubscriptionFilter;

class SubscriptionModule
{
    public static function register()
    {
        $self = new static();
        App::getInstance()->addAction('fluentcart_loaded', [$self, 'init']);
    }

    public function init($app)
    {
        $app->router->group(function ($router) {
            require_once __DIR__ . '/Http/subscriptions-api.php';
        });

        (new \Webmakerr\App\Modules\Subscriptions\Http\Handlers\AdminMenuHandler())->register();

        webmakerr_add_filter('webmakerr_cart/admin_filter_options', function ($filterOptions, $args) {
            $filterOptions['subscription_filter_options'] = SubscriptionFilter::getTableFilterOptions();
            return $filterOptions;
        }, 10, 2);

    }

}
