<?php

namespace Webmakerr\App\Listeners\Order;

use Webmakerr\App\Models\Customer;

class OrderCreated
{
    public static function handle(\Webmakerr\App\Events\Order\OrderCreated $event)
    {
        if ($event->order->customer_id) {
            $customer = Customer::query()->where('id', $event->order->customer_id)->first();
            (!empty($customer)) ? $customer->recountStat() : '';
        }
    }

}