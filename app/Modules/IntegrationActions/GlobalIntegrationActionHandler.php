<?php

namespace Webmakerr\App\Modules\IntegrationActions;

class GlobalIntegrationActionHandler
{
    public function register()
    {
        webmakerr_add_action('webmakerr_loaded', [$this, 'init']);
    }

    public function init()
    {
        add_action('init', function () {
            webmakerr_do_action('webmakerr_cart/register_integration_action');
        });

    }

    public static function getAll()
    {
        return webmakerr_apply_filters('webmakerr_cart/integration/get_global_integration_actions', []);
    }
}