<?php

namespace Webmakerr\App\Helpers;

use Webmakerr\Api\ModuleSettings;
use Webmakerr\App\App;
use Webmakerr\App\Http\Controllers\ProductController;
use Webmakerr\App\Models\Product;
use Webmakerr\App\Services\Filter\OrderFilter;
use Webmakerr\App\Services\URL;

class AdminHelper
{
    public static function getProductMenu($product, $echo = false, $activeMenu = '')
    {
        if (!$product instanceof Product) {
            $product = Product::query()->find($product);
        }

        $productId = $product->ID;

        $baseUrl = webmakerr_apply_filters('webmakerr_cart/admin_base_url', admin_url('admin.php?page=webmakerr#/'), []);

        $menuItems = webmakerr_apply_filters('webmakerr_cart/product_admin_items', [
            'product_edit'          => [
                'label' => __('Edit Product', 'webmakerr-cart'),
                'link'  => $baseUrl . 'products/' . $productId
            ],
            'product_upgrade_paths' => [
                'label' => __('Upgrade Paths', 'webmakerr-cart'),
                'link'  => $baseUrl . 'products/' . $productId . '/upgrade-paths'
            ],
            'product_integrations' => [
                'label' => __('Integrations', 'webmakerr-cart'),
                'link'  => $baseUrl . 'products/' . $productId . '/integrations'
            ],
            // 'product_pricing' => [
            //     'label' => __('Pricing', 'webmakerr-cart'),
            //     'link' => $baseUrl . 'products/' . $productId . '/pricing'
            // ],
//            'product_integrations' => [
//                'label' => __('Integrations', 'webmakerr-cart'),
//                'link' => $baseUrl . 'products/' . $productId . '/integrations'
//            ]
        ], [
            'product_id' => $productId,
            'base_url' => $baseUrl
        ]);

        $request = App::request()->all();
        if (isset($request['action']) && $request['action'] == 'edit') {
            $menuItems['product_details'] = [
                'label' => __('Edit Pricing', 'webmakerr-cart'),
                'link'  => admin_url('admin.php?page=webmakerr#/products/' . $productId)
            ];
        }


        $productName = $product->post_title;

        $data = [
            'menu_items'   => $menuItems,
            'active'       => $activeMenu,
            'products_url' => $baseUrl . 'products',
            'product_name' => $productName,
            'status'       => $product->post_status,
            'product_id'   => $productId
        ];

        if ($echo) {
            App::make('view')->render('admin.admin_product_menu', $data);
        } else {
            return (string)App::make('view')->make('admin.admin_product_menu', $data);
        }
    }

    public static function getAdminMenu($echo = false, $activeNav = '')
    {
        $baseUrl = webmakerr_apply_filters('webmakerr_cart/admin_base_url', admin_url('admin.php?page=webmakerr#/'), []);
        $menuItems = webmakerr_apply_filters('webmakerr_cart/global_admin_menu_items', [
            'dashboard'    => [
                'label' => __('Dashboard', 'webmakerr-cart'),
                'link'  => $baseUrl
            ],
            'orders'       => [
                'label' => __('Orders', 'webmakerr-cart'),
                'link'  => $baseUrl . 'orders',
                'permission' => ['orders/view']
            ],
            'customers'    => [
                'label' => __('Customers', 'webmakerr-cart'),
                'link'  => $baseUrl . 'customers',
                'permission' => ["customers/view", "customers/manage"]
            ],
            'products'     => [
                'label' => __('Products', 'webmakerr-cart'),
                'link'  => $baseUrl . 'products',
                'permission' => ['products/view']
            ],
//            'integrations' => [
//                'label' => __('Integrations', 'webmakerr-cart'),
//                'link'  => $baseUrl . 'integrations'
//            ],
            'reports'      => [
                'label' => __('Reports', 'webmakerr-cart'),
                'link'  => $baseUrl . 'reports/overview',
                'permission' => ['reports/view']
            ],
            // 'attributes' => [
            //     'label' => __('Attributes', 'webmakerr-cart'),
            //     'link' => $baseUrl . 'attributes'
            // ],
        ], ['base_url' => $baseUrl]);

//        $menuItems['settings'] = [
//            'label' => __('Settings', 'webmakerr-cart'),
//            'link'  => $baseUrl . 'settings',
//        ];

        $menuItems['more'] = [
            'label'    => __('More', 'webmakerr-cart'),
            'link'     => '#',
            'children' => []
        ];
        if (App::isProActive() && ModuleSettings::isActive('order_bump')) {
            $menuItems['more']['children']['order_bump'] = [
                'label' => __('Order Bump', 'webmakerr-cart'),
                'link'  => $baseUrl . 'order_bump',
            ];
        }
        $menuItems['more']['children']['coupons'] = [
            'label' => __('Coupons', 'webmakerr-cart'),
            'link'  => $baseUrl . 'coupons',
        ];
        $menuItems['more']['children']['logs'] = [
            'label' => __('Logs', 'webmakerr-cart'),
            'link'  => $baseUrl . 'logs',
        ];
        $menuItems['more']['children']['taxes'] = [
            'label' => __('Taxes', 'webmakerr-cart'),
            'link'  => $baseUrl . 'taxes',
        ];


        if ($echo) {
            App::make('view')->render('admin.admin_menu', [
                'menu_items' => $menuItems,
                'active'     => $activeNav
            ]);
        } else {
             return App::make('view')->make('admin.admin_menu', [
                'menu_items' => $menuItems,
                'active'     => $activeNav
            ]);
        }
    }

    public static function pushGlobalAdminAssets()
    {
        $app = App::getInstance();

        $assets = $app['url.assets'];

        $slug = $app->config->get('app.slug');

        wp_enqueue_style(
            $slug . '_global_admin_app', $assets . 'admin/global_admin.css',
            [],
            WEBMAKERR_VERSION,
        );
    }


}






