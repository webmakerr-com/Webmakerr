<?php

defined('ABSPATH') or die;

if (defined('WEBMAKERR_LEGACY_LOADER')) {
    return;
}

define('WEBMAKERR_LEGACY_LOADER', __FILE__);

if (defined('WEBMAKERR_PLUGIN_FILE')) {
    return;
}

require __DIR__ . '/webmakerr.php';
