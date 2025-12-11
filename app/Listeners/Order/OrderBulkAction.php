<?php

namespace Webmakerr\App\Listeners\Order;

use Webmakerr\App\Helpers\CustomerHelper;

class OrderBulkAction
{
    public static function handle(\Webmakerr\App\Events\Order\OrderBulkAction $event)
    {
        if ($event->customerIds) {
            (new CustomerHelper)->calculateCustomerStats($event->customerIds);
        }
    }
}