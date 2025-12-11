<?php

namespace Webmakerr\App\Listeners\Order;

use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\Subscription;
use Webmakerr\App\Modules\Subscriptions\Services\SubscriptionService;

class RenewalOrderDeleted
{
    public static function handle(\Webmakerr\App\Events\Order\RenewalOrderDeleted $event)
    {
        if ($event->order->customer_id) {
            $customer = Customer::query()->where('id', $event->order->customer_id)->first();
            if (!empty($customer)) {
                $customer->recountStat();
            }
        }

        $subscriptionModel = Subscription::query()->where('parent_order_id', $event->order->parent_id)->first();

        if ($subscriptionModel) {
            SubscriptionService::syncSubscriptionStates($subscriptionModel, []);
        }

    }

}
