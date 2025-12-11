<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
// Add to your bindings.php file
$app->singleton(\Webmakerr\App\Services\Localization\LocalizationManager::class, function($app) {
    return \Webmakerr\App\Services\Localization\LocalizationManager::getInstance();
});
$app->alias(\Webmakerr\App\Services\Localization\LocalizationManager::class, 'localization');