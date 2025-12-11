<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\Services\PluginInstaller\BackgroundInstaller;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Http\Request\Request;

class AddonsController extends Controller
{

    public function getAddons(Request $request)
    {
        $addons = [
            'fluent-crm'       => [
                'installable' => 'fluent-crm',
                'enabled'     => defined('FLUENTCRM'),
                'title'       => __('FluentCRM', 'webmakerr-cart'),
                'logo'        => Vite::getAssetUrl('images/integrations/fluentcrm.svg'),
                'categories'   => [
                    'crm',
                    'core',
                    'marketing',
                ],
                'description' => __('The most powerful and user-friendly email marketing automation plugin for WordPress. Send newsletters, marketing emails, customer onboard emails, Automations with FluentCart + FluentCRM integration.', 'webmakerr-cart' ),
            ],
            'fluent-smtp'      => [
                'installable' => 'fluent-smtp',
                'logo'        => Vite::getAssetUrl('images/integrations/fluent-smtp.svg'),
                'enabled'     => defined('FLUENTMAIL_PLUGIN_FILE'),
                'title'       => __('FluentSMTP', 'webmakerr-cart'),
                'categories'   => [
                    'core',
                    'marketing',
                ],
                'description' => __('A free and open-source WordPress SMTP plugin to send emails via multiple SMTP providers. Ensure email deliverability for your WooCommerce and FluentCart emails with FluentSMTP.', 'webmakerr-cart'),
            ],
            'fluent-community' => [
                'installable' => 'fluent-community',
                'logo'        => Vite::getAssetUrl('images/integrations/fluent-community.svg'),
                'enabled'     => defined('FLUENT_COMMUNITY_PLUGIN_VERSION'),
                'title'       => __('FluentCommunity', 'webmakerr-cart'),
                'categories'   => [
                    'core',
                    'community',
                    'lms'
                ],
                'description' => __('Build a community, membership site, or online forum with FluentCart + FluentCommunity integration. Engage your customers and users right from your WordPress dashboard.', 'webmakerr-cart'),
            ],
            'fluent-security'  => [
                'installable' => 'fluent-security',
                'logo'        => Vite::getAssetUrl('images/integrations/fluent-auth.svg'),
                'enabled'     => defined('FLUENT_AUTH_VERSION'),
                'title'       => __('FluentAuth', 'webmakerr-cart'),
                'categories'   => [
                    'core'
                ],
                'description' => __('Customize WordPress emails, customized login & signup forms with enhanced security and social logins. Enhance your site security with FluentCart + FluentSecurity integration.', 'webmakerr-cart'),
            ],
            'fluentform'       => [
                'installable' => 'fluentform',
                'logo'        => Vite::getAssetUrl('images/integrations/fluent-form.svg'),
                'enabled'     => defined('FLUENTFORM'),
                'title'       => __('Fluent Forms', 'webmakerr-cart'),
                'categories'   => [
                    'core',
                    'marketing'
                ],
                'description' => __('Create advanced forms and surveys with an easy-to-use drag & drop form builder.', 'webmakerr-cart'),
            ],
            'fluent-support'   => [
                'installable' => 'fluent-support',
                'logo'        => Vite::getAssetUrl('images/integrations/fluent-support.svg'),
                'enabled'     => defined('FLUENT_SUPPORT_VERSION'),
                'title'       => __('FluentSupport', 'webmakerr-cart'),
                'categories'   => [
                    'core',
                    'marketing'
                ],
                'description' => __('A powerful helpdesk and customer support plugin for WordPress. Manage customer support tickets directly from your WordPress dashboard with FluentCart + FluentSupport integration.', 'webmakerr-cart'),
            ]
        ];

        $addons = webmakerr_apply_filters('webmakerr_cart/integration/addons', $addons);

        return [
            'addons' => $addons
        ];
    }

    public function installAndActivate(Request $request)
    {
        $addon = $request->getSafe('addon', 'sanitize_text_field');

        $listedPlugins = webmakerr_apply_filters('webmakerr_cart/installable_repo_plugins', [
            'fluent-crm',
            'fluent-smtp',
            'fluent-community',
            'fluent-security',
            'fluentform',
            'fluent-support'
        ]);

        if (!$addon || !in_array($addon, $listedPlugins)) {
            return $this->sendError([
                'message' => __('This addon cannot be installed at this time', 'webmakerr-cart')
            ]);
        }

        $result = (new BackgroundInstaller())->installPlugin($addon);

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message()
            ]);
        }

        return $this->sendSuccess([
            'message'  => __('Addon installation started successfully.', 'webmakerr-cart'),
            'redirect' => admin_url('admin.php?page=webmakerr#/integrations')
        ]);
    }

}