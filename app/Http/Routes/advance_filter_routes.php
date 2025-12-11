<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

/**
 * @var $router Router
 */

use Webmakerr\App\Http\Controllers\AdvanceFilter\AdvanceFilterController;
use Webmakerr\App\Http\Controllers\VariantController;
use Webmakerr\Framework\Http\Router;

$router->prefix('advance_filter')
    ->group(function (Router $router) {
        $router->get('/get-filter-options', [AdvanceFilterController::class, 'getFilterOption']);
    });

$router->get('forms/search_options', [AdvanceFilterController::class, 'getSearchOptions'])->withPolicy('AdminPolicy')->meta([
    'permissions' => 'super_admin'
]);