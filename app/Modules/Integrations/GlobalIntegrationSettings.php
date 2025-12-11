<?php

namespace Webmakerr\App\Modules\Integrations;

use Webmakerr\App\Helpers\EditorShortCodeHelper;
use Webmakerr\App\Models\Meta;
use Webmakerr\App\Models\ProductMeta;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Services\PluginInstaller\BackgroundInstaller;

// Load necessary WordPress files for filesystem and plugin installation
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';

class GlobalIntegrationSettings
{
    public function getGlobalSettingsData($request)
    {
        $settingsKey = sanitize_text_field(Arr::get($request, 'settings_key'));
        $settings = webmakerr_apply_filters('webmakerr_cart/integration/global_integration_settings_' . $settingsKey, [], []);
        $fieldSettings = webmakerr_apply_filters('webmakerr_cart/integration/global_integration_fields_' . $settingsKey, [], []);

        if (!$fieldSettings) {
            wp_send_json([
                'settings'     => $settings,
                'settings_key' => $settingsKey,
                'message'      => __('Sorry! No integration failed found with: ', 'webmakerr-cart') . $settingsKey
            ], 422);
        }

        if (empty($fieldSettings['save_button_text'])) {
            $fieldSettings['save_button_text'] = __('Save Settings', 'webmakerr-cart');
        }

        if (empty($fieldSettings['valid_message'])) {
            $fieldSettings['valid_message'] = __('Your API Key is valid', 'webmakerr-cart');
        }

        if (empty($fieldSettings['invalid_message'])) {
            $fieldSettings['invalid_message'] = __('Your API Key is not valid', 'webmakerr-cart');
        }

        wp_send_json([
            'integration' => $settings,
            'settings'    => $fieldSettings
        ], 200);
    }

    public function authenticateCredentials($request)
    {
        $settingsKey = sanitize_text_field(Arr::get($request, 'settings_key'));
        $integration = wp_unslash(Arr::get($request, 'integration'));
        webmakerr_do_action('webmakerr_cart/integration/authenticate_global_credentials_' . $settingsKey, [
            'settings_key' => $settingsKey,
            'integration'  => $integration
        ]);
    }

    public function saveGlobalSettingsData($request)
    {
        $settingsKey = sanitize_text_field(Arr::get($request, 'settings_key'));
        $integration = wp_unslash(Arr::get($request, 'integration'));
        webmakerr_do_action('webmakerr_cart/integration/save_global_integration_settings_' . $settingsKey, [
            'settings_key' => $settingsKey,
            'integration'  => $integration
        ]);

        // Someone should catch that above action and send response
        wp_send_json([
            'message' => __('Sorry, no Integration found. Please make sure that latest version of installed', 'webmakerr-cart')
        ], 422);
    }

    // Get All the feeds for global integrations
    // This is used in the global integration settings page
    // It returns all the feeds that are registered with the global scope
    public function getFeeds()
    {

        $availableIntegrations = webmakerr_apply_filters('webmakerr_cart/integration/order_integrations', [], []);

        $availableIntegrations = array_filter($availableIntegrations, function ($integration) {
            return in_array('global', Arr::get($integration, 'scopes', [])) && $integration['enabled'];
        });

        $formattedFeeds = [];
        if ($availableIntegrations) {
            $feeds = Meta::whereIn('meta_key', array_keys($availableIntegrations))
                ->where('object_type', 'order_integration')
                ->orderBy('id', 'ASC')
                ->get();

            foreach ($feeds as $feed) {
                $data = $feed->meta_value;
                $formattedFeeds[] = [
                    'id'       => $feed->id,
                    'name'     => Arr::get($data, 'name'),
                    'enabled'  => $data['enabled'],
                    'provider' => $feed->meta_key,
                    'feed'     => $data,
                    'scope'    => 'product',
                ];
            }
        }

        return [
            'feeds'                  => $formattedFeeds,
            'available_integrations' => $availableIntegrations,
            'all_module_config_url'  => admin_url('admin.php?page=webmakerr#/integrations')
        ];
    }

    public function getNotificationFeeds()
    {
        $notificationKeys = webmakerr_apply_filters('webmakerr_cart/integration/global_notification_types', [], []);
        if ($notificationKeys) {
            $feeds = Meta::whereIn('meta_key', $notificationKeys)
                ->orderBy('id', 'DESC')
                ->get();
        } else {
            $feeds = [];
        }
        return $this->formatFeedsData($feeds);
    }

    public function formatFeedsData($feeds): array
    {
        $formattedFeeds = [];
        foreach ($feeds as $feed) {
            $formattedFeeds[] = $this->formatFeedData($feed);
        }
        return $formattedFeeds;
    }

    public function formatFeedData($feed)
    {
        $data = !is_string($feed->meta_value) ? $feed->meta_value : json_decode($feed->meta_value, true);

        $feedData = [
            'id'       => $feed['id'],
            'name'     => Arr::get($data, 'name'),
            'enabled'  => $data['enabled'],
            'provider' => $feed->meta_key,
            'feed'     => $data
        ];

        return webmakerr_apply_filters('webmakerr_cart/integration/global_notification_feed_' . $feed->meta_key, $feedData, []);
    }

    // public function updateNotificationStatus($request)
    // {
    //     $integrations = Arr::get($request, 'addons');

    //     fct_update_option('fluent_cart_global_integrations', $integrations);

    //     return [
    //         'message' => __('Integration successfully updated', 'webmakerr-cart')
    //     ];
    // }

    public function getIntegrationSettings($args, $feedType = 'global')
    {
        $integrationName = Arr::get($args, 'integration_name');
        $integrationId = intval(Arr::get($args, 'integration_id'));

        $settings = [
            'conditionals' => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all'
            ],
            'enabled'      => 'yes',
            'list_id'      => '',
            'list_name'    => '',
            'name'         => '',
            'merge_fields' => (object)[],
        ];

        $mergeFields = false;
        if ($integrationId) {
            if ($feedType === 'product') {
                $feed = ProductMeta::query()->where('id', $integrationId)->first();
            } else {
                $feed = Meta::query()->where('id', $integrationId)->first();
            }

            if (!$feed) {
                return new \WP_Error(
                    'integration_not_found',
                    __('Integration not found', 'webmakerr-cart')
                );
            }

            $feedValue = $feed->meta_value ?? [];
            $settings = wp_parse_args($feedValue, webmakerr_apply_filters('webmakerr_cart/integration/get_integration_defaults_' . $integrationName, [], []));
        } else {
            $settings = webmakerr_apply_filters('webmakerr_cart/integration/get_integration_defaults_' . $integrationName, $settings, []);
        }

        $settingsFields = webmakerr_apply_filters('webmakerr_cart/integration/get_integration_settings_fields_' . $integrationName, [], $settings);
        $shortCodes = EditorShortCodeHelper::getShortCodes();

        return [
            'settings'        => $settings,
            'settings_fields' => $settingsFields,
            'shortcodes'      => $shortCodes,
            'inputs'          => EditorShortCodeHelper::checkoutInputs(),
            'merge_fields'    => $settings['merge_fields'] ?? $mergeFields
        ];

    }

    public function prepareFeedData($data, $integrationId, $integrationName, $feedType = 'order_integration')
    {

        if (!$integrationName) {
            wp_send_json([
                'message' => __('Validation Failed', 'webmakerr-cart'),
                'errors'  => [
                    'name' => [
                        __('Feed name is required', 'webmakerr-cart')
                    ]
                ]
            ], 422);
        }

        $errors = $this->validate($data, $integrationName);

        if (!empty($errors)) {

            return new \WP_Error(
                'validation_error',
                __('Validation Failed, Please fill up the required fields.', 'webmakerr-cart'),
                $errors
            );
        }

        if (Arr::get($data, 'data_type') == 'stringify') {
            $integration = \json_decode(Arr::get($data, 'integration'), true);
        } else {
            $integration = wp_unslash(Arr::get($data, 'integration'));
        }

        $integration = webmakerr_apply_filters('webmakerr_cart/integration/save_integration_values_' . $integrationName, $integration, [
            'id'        => $integrationId,
            'provider'  => $integrationName,
            'feed_type' => $feedType,
            'data'      => $data
        ]);

        return [
            //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_key'    => sanitize_text_field($integrationName),
            //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            'meta_value'  => $integration,
            'object_type' => $feedType
        ];
    }

    public function validate($data, $integrationName)
    {
        if (empty($data['integration'])) {
            return [
                'message' => __('Validation Failed, No valid data found.', 'webmakerr-cart'),
                'errors'  => [
                    'name' => [__('Feed data is required', 'webmakerr-cart')]
                ]
            ];
        }

        $data = json_decode($data['integration'], true);
        $settingsFields = webmakerr_apply_filters('webmakerr_cart/integration/get_integration_settings_fields_' . $integrationName, [], []);
        $fields = Arr::get($settingsFields, 'fields');
        return $this->validateFields($fields, $data);
    }

    public function validateFields(array $fields, array $data): array
    {
        $errors = [];
        foreach ($fields as $field) {
            $key = Arr::get($field, 'key');
            if (empty($field['required'])) {
                continue;
            }
            if (!array_key_exists($key, $data) || (is_array($data[$key]) ? empty($data[$key]) : trim((string)$data[$key]) === '')) {
                $label = $field['label'] ?? $key;
                $errors[$key] = "$label is required.";
            }
        }

        return $errors;
    }


    public function saveIntegrationSettings($reuestData)
    {
        $integrationId = Arr::get($reuestData, 'integration_id');
        $integrationMeta = null;
        if ($integrationId) {
            $integrationMeta = Meta::query()->where('id', $integrationId)
                ->where('object_type', 'order_integration')
                ->first();

            if (!$integrationMeta) {
                return new \WP_Error(
                    'integration_not_found',
                    __('Integration not found', 'webmakerr-cart')
                );
            }
        }

        $provider = Arr::get($reuestData, 'integration_name');

        $integrationFeed = json_decode(Arr::get($reuestData, 'integration'), true);

        $integrationFeedData = IntegrationHelper::validateAndFormatIntegrationFeedSettings($integrationFeed, [
            'provider'       => $provider,
            'scope'          => 'global',
            'integration_id' => $integrationId
        ]);


        if (is_wp_error($integrationFeedData)) {
            return $integrationFeedData;
        }


        if ($integrationMeta) {
            $integrationMeta->meta_value = $integrationFeedData;
            $integrationMeta->save();
        } else {
            $integrationMeta = Meta::create([
                //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_key'    => $provider,
                //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                'meta_value'  => $integrationFeedData,
                'object_type' => 'order_integration'
            ]);
        }

        return [
            'message'          => __('Integration successfully saved', 'webmakerr-cart'),
            'integration_id'   => $integrationId,
            'integration_name' => $provider,
            'created'          => $integrationMeta->wasRecentlyCreated
        ];
    }

    public function deleteIntegrationFeed($request)
    {
        $integrationId = intval(Arr::get($request, 'integration_id'));

        Meta::where('id', $integrationId)->delete();

        return [
            'message' => __('Selected integration feed successfully deleted', 'webmakerr-cart')
        ];
    }

    public function getIntegrationList($request)
    {
        $integrationName = Arr::get($request, 'integration_name');
        $list = [];
        $listId = Arr::get($request, 'list_id');
        $merge_fields = webmakerr_apply_filters('webmakerr_cart/integration/get_integration_merge_fields_' . $integrationName, $list, $listId);
        return [
            'merge_fields' => $merge_fields
        ];
    }

    public function chainedData($requestData)
    {
        webmakerr_do_action('webmakerr_cart/integration/chained_' . Arr::get($requestData, 'route'), [
            'data' => $requestData
        ]);
    }

    public function installPlugin($pluginSlug)
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!current_user_can('install_plugins')) {
            return new \WP_Error('permission_denied', __('You do not have permission to install plugins.', 'webmakerr-cart'));
        }

        if ($pluginSlug === 'fluentcrm') {
            $pluginSlug = 'fluent-crm';
        }

        $listedPlugins = webmakerr_apply_filters('webmakerr_cart/installable_repo_plugins', [
            'fluent-crm',
            'fluent-smtp',
            'fluent-community',
            'fluent-security',
            'fluentform',
            'fluent-support'
        ]);

        if (empty($pluginSlug) || !in_array($pluginSlug, $listedPlugins)) {
            return new \WP_Error('invalid_plugin', __('Invalid plugin selected for installation.', 'webmakerr-cart'));
        }

        $result = (new BackgroundInstaller())->installPlugin($pluginSlug);

        if (is_wp_error($result)) {
            return $result;
        }

        return [
            'message' => __('The selected plugin has been installed. Please refresh the page.', 'webmakerr-cart')
        ];

    }

    public function changeStatus($request): array
    {
        $feedId = intval(Arr::get($request, 'notification_id'));
        $status = Arr::get($request, 'status');

        $feedQuery = Meta::query()->where('id', $feedId);
        $feed = $feedQuery->first();
        $feedData = $feed['meta_value'];

        if (!$feed) {
            return [
                'message' => __('Feed not found', 'webmakerr-cart')
            ];
        }

        $feedData['enabled'] = $status === 'yes' ? 'yes' : 'no';

        $feedQuery->update([
            //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            'meta_value' => $feedData
        ]);
        return [
            'message' => __('Feed status updated', 'webmakerr-cart')
        ];

    }

}