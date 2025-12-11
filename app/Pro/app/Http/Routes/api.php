<?php

/**
 * @var $router WPFluentMicro\Framework\Http\Router\Router
 */

use FluentCartPro\App\Http\Controllers\ProductController;
use FluentCartPro\App\Http\Controllers\RoleController;
use FluentCartPro\App\Modules\Licensing\Http\Controllers\LicenseController;

//Roles and permissions routes
$router->prefix('roles')
    ->withPolicy('Webmakerr\App\Http\Policies\AdminPolicy')->group(function ($router) {
        $router->get('/managers', [RoleController::class, 'managers']);
        $router->get('/user-list', [RoleController::class, 'userList']);
        $router->get('/', [RoleController::class, 'index']);
        $router->post('/', [RoleController::class, 'create']);
        $router->get('/{key}', [RoleController::class, 'find']);
        $router->post('/{key}', [RoleController::class, 'update']);
        $router->delete('/{key}', [RoleController::class, 'delete']);
    });

//FluentCart pluginManager routes
$router->prefix('settings/license')
    ->withPolicy('Webmakerr\App\Http\Policies\LicensePolicy')
    ->group(function ($router) {
        $router->get('/', [LicenseController::class, 'getLicenseDetails']);
        $router->post('/', [LicenseController::class, 'activateLicense']);
        $router->delete('/', [LicenseController::class, 'deactivateLicense']);
    });

$router->prefix('products')
    ->withPolicy('Webmakerr\App\Http\Policies\ProductPolicy')
    ->group(function ($router) {
        // manage Inventory
        $router->put('/{postId}/update-inventory/{variantId}', [ProductController::class, 'updateInventory'])->meta([
            'permissions' => 'products/edit'
        ]);
        $router->put('/{postId}/update-manage-stock', [ProductController::class, 'updateManageStock'])->meta([
            'permissions' => 'products/edit'
        ]);
    });
