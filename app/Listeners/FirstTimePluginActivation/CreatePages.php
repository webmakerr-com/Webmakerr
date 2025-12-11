<?php

namespace Webmakerr\App\Listeners\FirstTimePluginActivation;

use Webmakerr\App\CPT\Pages;
use Webmakerr\App\Events\FirstTimePluginActivation;
use Webmakerr\Framework\Support\Str;

class CreatePages
{

    public static function handle(FirstTimePluginActivation $event)
    {
        static::createPages();
    }


    public static function createPages()
    {
        (new Pages())->createPages();
    }


}