<?php

namespace Webmakerr\App\Services;

use Webmakerr\App\Modules\Integrations\FluentPlugins\FluentCommunityConnect;
use Webmakerr\App\Modules\Integrations\FluentPlugins\FluentCRMConnect;
use Webmakerr\App\Modules\Integrations\FluentPlugins\FluentCRMDeepIntegration;
use Webmakerr\App\Modules\Integrations\FluentPlugins\FluentSupportWidget;

class Integration
{
    public function register(): void
    {
        add_action('init', function () {
            $this->init();
            (new \Webmakerr\App\Listeners\IntegrationEventListener())->registerHooks();
        }, 2);
    }

    private function init()
    {
        (new FluentCRMConnect)->register();
        (new FluentCommunityConnect())->register();

        if (defined('FLUENTCRM')) {
            (new FluentCRMDeepIntegration())->init();
        }

        if(defined('FLUENT_SUPPORT_VERSION')) {
            (new FluentSupportWidget())->register();
        }
    }

}
