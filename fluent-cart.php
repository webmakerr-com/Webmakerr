<?php

defined('ABSPATH') or die;

if (defined('WEBMAKERR_PLUGIN_FILE')) {
    return;
}

if (!defined('WEBMAKERR_LEGACY_LOADER')) {
    define('WEBMAKERR_LEGACY_LOADER', __FILE__);
}

require __DIR__ . '/webmakerr.php';
