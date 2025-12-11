<?php

namespace FluentCart\App\Services\Pro;

use FluentCart\App\Services\Permission\PermissionManager;

class ProFeatureManager
{
    private static $instance;

    private bool $proStatus = false;
    private bool $statusEvaluated = false;

    public static function instance(): ProFeatureManager
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function boot($app): void
    {
        add_action('plugins_loaded', function () use ($app) {
            $this->evaluateStatus();
            do_action('fluent_cart/pro_bootstrap', $app, $this);

            if ($this->isProActive()) {
                do_action('fluent_cart/pro_loaded', $app, $this);
            }
        }, 9);

        add_action('admin_notices', function () {
            if ($this->shouldShowLockNotices()) {
                $this->renderLockedNotice();
            }
        });

        add_filter('fluent_cart/admin_notices', function ($notices) {
            if ($this->shouldShowLockNotices()) {
                $notices[] = '<div>' . esc_html($this->getLockedMessage()) . '</div>';
            }

            return $this->maybeAddVersionNotice($notices);
        });
    }

    public function evaluateStatus(): void
    {
        if ($this->statusEvaluated) {
            return;
        }

        $status = (bool) apply_filters('fluent_cart/is_pro_active', false);

        if (!$status && (defined('FLUENTCART_PRO_PLUGIN_VERSION') || defined('FLUENTCART_PRO_VERSION'))) {
            $status = true;
        }

        if (!$status && defined('FLUENTCART_PRO_PLUGIN_BASENAME')) {
            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if (function_exists('is_plugin_active') && is_plugin_active(FLUENTCART_PRO_PLUGIN_BASENAME)) {
                $status = true;
            }
        }

        $this->proStatus = $status;
        $this->statusEvaluated = true;
    }

    public function isProActive(): bool
    {
        $this->evaluateStatus();
        return $this->proStatus;
    }

    public function getLockedMessage(string $feature = ''): string
    {
        $message = __('Pro feature locked. Install and activate Webmakerr Pro to unlock all premium features.', 'fluent-cart');

        if ($feature) {
            $message = sprintf(
                __('Pro feature locked: %s. Install and activate Webmakerr Pro to continue.', 'fluent-cart'),
                $feature
            );
        }

        return $message;
    }

    public function lockedError(string $feature = ''): \WP_Error
    {
        return new \WP_Error('fluent_cart_pro_required', $this->getLockedMessage($feature));
    }

    public function dispatch(string $featureKey, $payload, callable $fallback)
    {
        if (!$this->isProActive()) {
            return $fallback();
        }

        $handler = apply_filters('fluent_cart/pro_feature_handler', null, $featureKey, $payload, $this);
        if (is_callable($handler)) {
            return $handler($payload, $this);
        }

        return $fallback();
    }

    public function ensureClassAvailable(string $className): bool
    {
        return class_exists($className);
    }

    public function maybeAddVersionNotice(array $notices): array
    {
        if (!$this->isProActive()) {
            return $notices;
        }

        if (!defined('FLUENTCART_MIN_PRO_VERSION') || !defined('FLUENTCART_PRO_PLUGIN_VERSION')) {
            return $notices;
        }

        if (!PermissionManager::userCan('is_super_admin')) {
            return $notices;
        }

        if (version_compare(FLUENTCART_MIN_PRO_VERSION, FLUENTCART_PRO_PLUGIN_VERSION, '>')) {
            $updateUrl = admin_url('plugins.php?s=fluent-cart&plugin_status=all&fluent-cart-pro-check-update=' . time());
            $notices[] = '<div>' . sprintf(
                    __('Webmakerr Pro needs to be updated to at least version %s. %sUpdate now%s', 'fluent-cart'),
                    FLUENTCART_MIN_PRO_VERSION,
                    '<a href="' . esc_url($updateUrl) . '">',
                    '</a>'
                ) . '</div>';
        }

        return $notices;
    }

    private function shouldShowLockNotices(): bool
    {
        return !$this->isProActive() && PermissionManager::userCan('is_super_admin');
    }

    private function renderLockedNotice(): void
    {
        ?>
        <div class="notice notice-warning fluent-cart-notice">
            <p><?php echo esc_html($this->getLockedMessage()); ?></p>
        </div>
        <?php
    }
}
