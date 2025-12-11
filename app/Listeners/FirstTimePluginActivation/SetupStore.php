<?php

namespace Webmakerr\App\Listeners\FirstTimePluginActivation;

use Webmakerr\App\Events\FirstTimePluginActivation;

class SetupStore
{
    public static function handle(FirstTimePluginActivation $event)
    {
        $event->storeSettings->set('store_name', '');
        $event->storeSettings->set('store_logo', '');
    }
}
