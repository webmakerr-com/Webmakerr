<?php

namespace Webmakerr\App\Modules\PaymentMethods\Core;

use Webmakerr\App\App;
use Webmakerr\App\Models\Meta;
use Webmakerr\Framework\Support\Arr;

abstract class BaseGatewaySettings
{
    public static array $allSettings = [];
    private static bool $settingsLoaded = false;

    public $settings;
    public $methodHandler;

    public function __construct()
    {
        if (!self::$settingsLoaded) {
            self::$allSettings = Meta::query()
                ->whereLike('meta_key', 'fluent_cart_payment_settings\\_%',)
                ->get()
                ->pluck('meta_value', 'meta_key')
                //->keyBy('meta_key')
                ->toArray();

            self::$settingsLoaded = true;
        }

        try {
            $settings = Arr::get(self::$allSettings, $this->methodHandler, []);
        } catch (\Exception $e) {

        }

        if (!is_array($settings)) {
            $settings = [];
        }

        $this->settings = wp_parse_args($settings, static::getDefaults());
    }

    abstract public function get($key = '');
    abstract public function getMode();
    abstract public function isActive();

    public function getCachedSettings()
    {
        return Arr::get(self::$allSettings, $this->methodHandler);
    }
}

