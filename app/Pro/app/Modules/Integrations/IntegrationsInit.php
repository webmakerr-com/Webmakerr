<?php

namespace FluentCartPro\App\Modules\Integrations;

use FluentCartPro\App\Modules\Integrations\LMS\LearnDashLMSConnect;
use FluentCartPro\App\Modules\Integrations\LMS\LifterLMSConnect;

class IntegrationsInit
{
    public function register()
    {
        webmakerr_add_action('webmakerr_cart/init', function () {
            (new WebhookConnect())->register();
            (new LifterLMSConnect())->register();
            (new LearnDashLMSConnect())->register();
        });
    }


}
