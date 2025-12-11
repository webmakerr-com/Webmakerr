<?php

namespace Webmakerr\App\Modules\Integrations;

use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Vite;


class AddOnModule
{
    /**
     * Show the add-ons list.
     */
    public static function showAddOns(): array
    {
        $addOns = webmakerr_apply_filters('webmakerr_cart/integration/order_integrations', []);

        return [
            'addOns' => $addOns
        ];
    }

    public function updateAddOnsStatus($request): array
    {
        $addons = wp_unslash(Arr::get($request, 'addons'));
        fluent_cart_update_option('fluent_cart_global_integrations', $addons);

        return [
            'message' => __('Status successfully updated', 'fluent-cart')
        ];
    }


    public static function getPremiumAddOns(): array
    {
        return [];
    }

    public static function getFluentCrm(): array
    {
        return [
            'fluent-crm' => [
                'title'        => __('Fluent CRM', 'fluent-cart'),
                'description'  => __('Connect FluentCRM with FluentCart and subscribe a contact when a form is submitted', 'fluent-cart'),
                'logo'         => Vite::getAssetUrl('images/integrations/fluentcrm.svg'),
                'enabled'      => 'yes',
                'purchase_url' => 'https://wordpress.org/plugins/fluent-crm/',
                'category'     => 'crm',
                'btnTxt'       => __('Install & Activate', 'fluent-cart')
            ]
        ];
    }

    // public static function isModuleEnabled($module = 'slack')
    // {
    //     $globalModules = fct_get_option('fluent_cart_global_integrations');
    //     return $globalModules && isset($globalModules[$module]) && $globalModules[$module] == 'yes';
    // }

    public static function getFluentSupport()
    {
        return [];
    }
}
