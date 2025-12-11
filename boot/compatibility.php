<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * Compatibility layer to keep legacy FluentCart integrations working
 * after the namespace and hook prefix migration to Webmakerr.
 */

$constantAliases = [
    'WEBMAKERR_PLUGIN_PATH'          => 'FLUENTCART_PLUGIN_PATH',
    'WEBMAKERR_URL'                  => 'FLUENTCART_URL',
    'WEBMAKERR_VERSION'              => 'FLUENTCART_VERSION',
    'WEBMAKERR_DB_VERSION'           => 'FLUENTCART_DB_VERSION',
    'WEBMAKERR_LEGACY_UPLOAD_DIR'    => 'FLUENTCART_UPLOAD_DIR',
    'WEBMAKERR_UPLOAD_DIR'           => 'FLUENTCART_UPLOAD_DIR',
    'WEBMAKERR_DIR_FILE'             => 'FLUENT_CART_DIR_FILE',
    'WEBMAKERR_MIN_PRO_VERSION'      => 'FLUENTCART_MIN_PRO_VERSION',
    'WEBMAKERR_PRO_PLUGIN_BASENAME'  => 'FLUENTCART_PRO_PLUGIN_BASENAME',
    'WEBMAKERR_PRO_PLUGIN_VERSION'   => 'FLUENTCART_PRO_PLUGIN_VERSION',
    'WEBMAKERR_PRO_VERSION'          => 'FLUENTCART_PRO_VERSION',
    'WEBMAKERR_PRO_PLUGIN_FILE_PATH' => 'FLUENTCART_PRO_PLUGIN_FILE_PATH',
    'WEBMAKERR_PRO_PLUGIN_URL'       => 'FLUENTCART_PRO_PLUGIN_URL',
    'WEBMAKERR_PRO_PLUGIN_DIR'       => 'FLUENTCART_PRO_PLUGIN_DIR',
    'WEBMAKERR_DEV_MODE'             => 'FLUENT_CART_DEV_MODE',
    'WEBMAKERR_CART_ENCRYPTION_KEY'  => 'FLUENT_CART_ENCRYPTION_KEY',
    'WEBMAKERR_PRESERVER_DEV_META'   => 'FLUENTCART_PRESERVER_DEV_META'
];

foreach ($constantAliases as $new => $legacy) {
    if (defined($legacy) && !defined($new)) {
        define($new, constant($legacy));
    }

    if (defined($new) && !defined($legacy)) {
        define($legacy, constant($new));
    }
}

// Bridge legacy FluentCart classes to the new Webmakerr namespace.
spl_autoload_register(static function ($class) {
    $legacyPrefixes = [
        'FluentCart\\' => 'Webmakerr\\'
    ];

    foreach ($legacyPrefixes as $legacy => $replacement) {
        if (strpos($class, $legacy) !== 0) {
            continue;
        }

        $targetClass = $replacement . substr($class, strlen($legacy));

        if (
            (
                class_exists($targetClass) ||
                interface_exists($targetClass) ||
                trait_exists($targetClass)
            ) && !class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)
        ) {
            class_alias($targetClass, $class);
        }

        break;
    }
}, true, true);

// Alias Webmakerr-prefixed classes back to their legacy implementations when needed.
spl_autoload_register(static function ($class) {
    $legacyPrefix = 'FluentCart\\';
    $newPrefix = 'Webmakerr\\';

    if (strpos($class, $newPrefix) !== 0) {
        return;
    }

    $legacyClass = $legacyPrefix . substr($class, strlen($newPrefix));

    if (
        (
            class_exists($legacyClass) ||
            interface_exists($legacyClass) ||
            trait_exists($legacyClass)
        ) && !class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)
    ) {
        class_alias($legacyClass, $class);
    }
}, true, true);

/**
 * Map hook prefixes between legacy (fluentcart/fluent_cart) and the new webmakerr ones.
 *
 * @param string $tag
 * @return string
 */
function webmakerr_hook_alias(string $tag): string
{
    $mappings = [
        'webmakerr_cart' => 'fluent_cart',
        'webmakerr'      => 'fluentcart'
    ];

    foreach ($mappings as $new => $legacy) {
        if (strpos($tag, $new) === 0) {
            return $legacy . substr($tag, strlen($new));
        }

        if (strpos($tag, $legacy) === 0) {
            return $new . substr($tag, strlen($legacy));
        }
    }

    return $tag;
}

/**
 * Trigger both the new and legacy action hooks.
 *
 * @param string $tag
 * @param mixed  ...$args
 * @return void
 */
function webmakerr_do_action(string $tag, ...$args): void
{
    do_action($tag, ...$args);

    $alias = webmakerr_hook_alias($tag);
    if ($alias !== $tag) {
        do_action($alias, ...$args);
    }
}

/**
 * Trigger both the new and legacy action hooks while preserving reference arrays.
 *
 * @param string $tag
 * @param array  $args
 * @return void
 */
function webmakerr_do_action_ref_array(string $tag, array $args): void
{
    do_action_ref_array($tag, $args);

    $alias = webmakerr_hook_alias($tag);
    if ($alias !== $tag) {
        do_action_ref_array($alias, $args);
    }
}

/**
 * Apply filters for both the new and legacy hook names.
 *
 * @param string $tag
 * @param mixed  $value
 * @param mixed  ...$args
 * @return mixed
 */
function webmakerr_apply_filters(string $tag, $value, ...$args)
{
    $value = apply_filters($tag, $value, ...$args);

    $alias = webmakerr_hook_alias($tag);
    if ($alias !== $tag) {
        $value = apply_filters($alias, $value, ...$args);
    }

    return $value;
}

/**
 * Register actions for both the new and legacy hook names.
 *
 * @param string   $tag
 * @param callable $callback
 * @param int      $priority
 * @param int      $accepted_args
 * @return void
 */
function webmakerr_add_action(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
    add_action($tag, $callback, $priority, $accepted_args);

    $alias = webmakerr_hook_alias($tag);
    if ($alias !== $tag) {
        add_action($alias, $callback, $priority, $accepted_args);
    }
}

/**
 * Register filters for both the new and legacy hook names.
 *
 * @param string   $tag
 * @param callable $callback
 * @param int      $priority
 * @param int      $accepted_args
 * @return void
 */
function webmakerr_add_filter(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
    add_filter($tag, $callback, $priority, $accepted_args);

    $alias = webmakerr_hook_alias($tag);
    if ($alias !== $tag) {
        add_filter($alias, $callback, $priority, $accepted_args);
    }
}
