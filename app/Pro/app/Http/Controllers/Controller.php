<?php

namespace FluentCartPro\App\Http\Controllers;

use FluentCartPro\App\Core\App;
use Webmakerr\App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{
    public function __construct()
    {
        parent::__construct(App::getInstance());
    }
}
