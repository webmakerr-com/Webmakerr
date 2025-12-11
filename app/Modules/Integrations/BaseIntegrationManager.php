<?php

namespace Webmakerr\App\Modules\Integrations;

use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Services\ShortCodeParser\ShortcodeTemplateBuilder;
use Webmakerr\Framework\Support\Arr;

abstract class BaseIntegrationManager
{
    protected $title = '';

    protected $description = '';

    protected $integrationKey = '';

    protected $priority = 10;

    protected $runOnBackgroundForProduct = true;

    protected $runOnBackgroundForGlobal = true;

    public $logo = '';

    public $hasGlobalMenu = false;

    public $category = 'crm';

    public $disableGlobalSettings = false;

    public $installable = '';

    public $scopes = ['global', 'product'];

    public $integrationId = null;

    public function __construct($title, $integrationKey, $priority = 10)
    {
        $this->title = $title;
        $this->integrationKey = $integrationKey;
        $this->priority = $priority;
    }

    final public function register()
    {
        webmakerr_add_filter('webmakerr_cart/integration/order_integrations', function ($addons) {
            $addons[$this->integrationKey] = $this->getInfo();
            return $addons;
        }, $this->priority, 1);

        $isConfigured = $this->isConfigured();

        if ($isConfigured) {
            webmakerr_add_filter('webmakerr_cart/integration/get_integration_defaults_' . $this->integrationKey, array($this, 'getIntegrationDefaults'), 10, 2);
            webmakerr_add_filter('webmakerr_cart/integration/get_integration_settings_fields_' . $this->integrationKey, array($this, 'getSettingsFields'), 10, 2);
            webmakerr_add_filter('webmakerr_cart/integration/integration_saving_data_' . $this->integrationKey, [$this, 'validateFeedData'], 10, 2);
            webmakerr_add_action('webmakerr_cart/integration/run/' . $this->integrationKey, function ($eventData) {
                $order = Arr::get($eventData, 'order', null);
                if (!$order) {
                    return;
                }
                $this->processAction($order, $eventData);
            }, $this->priority, 1);
        }

    }

    public function getInfo()
    {
        return [
            'priority'                => $this->priority,
            'title'                   => $this->title,
            'description'             => $this->description,
            'category'                => $this->category,
            'disable_global_settings' => $this->disableGlobalSettings,
            'config_url'              => $this->disableGlobalSettings ? '' : admin_url('admin.php?page=webmakerr#/integrations/' . $this->integrationKey),
            'logo'                    => $this->logo,
            'enabled'                 => $this->isConfigured(),
            'scopes'                  => $this->scopes,
            'installable'             => $this->installable,
            'delay_on_product_action' => $this->runOnBackgroundForProduct,
            'delay_on_global_action'  => $this->runOnBackgroundForGlobal,
        ];
    }

    abstract function processAction($order, $eventData);

    public function actionFields(): array
    {
        return Status::eventTriggers();
    }

    public function notify($feed, $order, $customer)
    {
        // Each integration have to implement this notify method
        return;
    }

    abstract public function getIntegrationDefaults($settings);

    abstract public function getSettingsFields($settings, $args = []);

    public function validateFeedData($data, $args)
    {
        return $data;
    }

    public function isConfigured()
    {
        $globalStatus = $this->getApiSettings();
        return $globalStatus && !!Arr::get($globalStatus, 'status');
    }

    public function getApiSettings()
    {
        $optionValue = fluent_cart_get_option('_integration_api_' . $this->integrationKey);

        $settings = is_array($optionValue) ? $optionValue : json_decode((string)$optionValue, true);

        if (!$settings || empty($settings['status'])) {
            $settings = [
                'apiKey' => '',
                'status' => false
            ];
        }

        return $settings;
    }

    protected function getSelectedTagIds($data, $inputData, $simpleKey = 'tag_ids', $routingId = 'tag_ids_selection_type', $routersKey = 'tag_routers')
    {
        $routing = Arr::get($data, $routingId, 'simple');
        if (!$routing || $routing == 'simple') {
            return Arr::get($data, $simpleKey, []);
        }

        $routers = Arr::get($data, $routersKey);
        if (empty($routers)) {
            return [];
        }

        return $this->evaluateRoutings($routers, $inputData);
    }

    protected function evaluateRoutings($routings, $inputData)
    {
        $validInputs = [];
        foreach ($routings as $routing) {
            $inputValue = Arr::get($routing, 'input_value');
            if (!$inputValue) {
                continue;
            }
            $condition = [
                'conditionals' => [
                    'status'     => true,
                    'is_test'    => true,
                    'type'       => 'any',
                    'conditions' => [
                        $routing
                    ]
                ]
            ];

            if (\Webmakerr\App\Services\ConditionAssesor::evaluate($condition, $inputData)) {
                $validInputs[] = $inputValue;
            }
        }

        return $validInputs;
    }

    public function parseSmartCode($text, Order $order)
    {
        return (string)ShortcodeTemplateBuilder::make($text, [
            'order'    => $order,
            'customer' => $order->customer ? $order->customer : []
        ]);
    }
}
