<?php

namespace Webmakerr\App\Listeners\Subscription;


use Webmakerr\Api\StoreSettings;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Services\AuthService;
use Webmakerr\Framework\Support\Arr;

class SubscriptionRenewed
{
    public static function handle(\Webmakerr\App\Events\Subscription\SubscriptionRenewed $event)
    {
        if ($event->customer) {
            $event->customer->recountStat();
        }
    }

}
