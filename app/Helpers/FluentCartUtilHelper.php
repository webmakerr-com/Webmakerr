<?php

namespace Webmakerr\App\Helpers;

class FluentCartUtilHelper
{

    public function getCheckoutUrl()
    {
        return site_url('/checkout');
    }
}
