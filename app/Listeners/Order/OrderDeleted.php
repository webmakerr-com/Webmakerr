<?php

namespace Webmakerr\App\Listeners\Order;

use Webmakerr\App\Models\Customer;

class OrderDeleted
{
    public static function handle(\Webmakerr\App\Events\Order\OrderDeleted $event)
    {
        if ($event->order->customer_id) {
            $customer = Customer::query()->where('id', $event->order->customer_id)->first();
            if (!empty($customer)) {
                $customer->recountStat();
            }
        }
    }

}