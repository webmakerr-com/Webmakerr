<?php

namespace Webmakerr\App\Listeners\FirstTimePluginActivation;

use Webmakerr\App\Events\FirstTimePluginActivation;

class ManagePayments
{
    public static function handle(FirstTimePluginActivation $event)
    {
        $event->storeSettings->set('currency', 'USD');
    }
}