<?php

namespace FluentCartPro\App\Services\Translations;

class Translations
{
    public static function getTranslations()
    {
        $translations = [
            'You can replace this module from %s/resources/admin/Components/Dashboard.vue%s' => __('You can replace this module from %s/resources/admin/Components/Dashboard.vue%s', 'webmakerr-cart-pro'),
            'A New Era of Commerce With WordPress' => __('A New Era of Commerce With WordPress', 'webmakerr-cart-pro'),
            'Webmakerr requires the Core Plugin to be installed first. Let\'s get you set up in just one click.' => __('Webmakerr requires the Core Plugin to be installed first. Let\'s get you set up in just one click.', 'webmakerr-cart-pro'),
            'Quick & Easy Setup' => __('Quick & Easy Setup', 'webmakerr-cart-pro'),
            'Go from Setup to first sale in minutes. Configuration is straightforward with the Onboarding Wizard.' => __('Go from Setup to first sale in minutes. Configuration is straightforward with the Onboarding Wizard.', 'webmakerr-cart-pro'),
            'Clean, Modern Interface' => __('Clean, Modern Interface', 'webmakerr-cart-pro'),
            'Manage your store with a clean dashboard that gives you everything you need in an intuitive workflow.' => __('Manage your store with a clean dashboard that gives you everything you need in an intuitive workflow.', 'webmakerr-cart-pro'),
            'Robust & Reliable Performance' => __('Robust & Reliable Performance', 'webmakerr-cart-pro'),
            'Built for lightning-fast processing, your store will perform under any stress without breaking your server' => __('Built for lightning-fast processing, your store will perform under any stress without breaking your server', 'webmakerr-cart-pro'),
            'cancel' => __('cancel', 'webmakerr-cart-pro'),
            'confirm' => __('confirm', 'webmakerr-cart-pro'),
            'Are you sure to delete this?' => __('Are you sure to delete this?', 'webmakerr-cart-pro'),
        ];

        return webmakerr_apply_filters("webmakerr_cart_pro/admin_translations", $translations);
    }
}