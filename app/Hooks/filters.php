<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

use Webmakerr\Framework\Database\Orm\Builder;
use Webmakerr\Framework\Foundation\Application;

use Webmakerr\App\Services\Payments\SubscriptionHelper;
use Webmakerr\Framework\Support\Arr;

/**
 * All registered filter's handlers should be in app\Hooks\Handlers,
 * addFilter is similar to add_filter and addCustomFlter is just a
 * wrapper over add_filter which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomFilter('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_filter('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 *
 * @var Application $app
 */


add_filter('block_categories_all', function ($categories) {
    $categories[] = array(
        'slug'  => 'fluent-cart',
        'title' => __('Webmakerr', 'webmakerr-cart'),
    );

    $categories[] = array(
        'slug'  => 'fluent-cart-buttons',
        'title' => __('Webmakerr Buttons', 'webmakerr-cart'),
    );

    return $categories;
});

webmakerr_add_filter('webmakerr_cart/dummy_product_info', function ($info) {
    $infos = [
        'mens-shoes' => [
            'title'    => __("Men’s Shoes", 'webmakerr-cart'),
            'count'    => "0",
            'category' => 'mens-shoes',
            'icon'     => 'RunningShoe'
        ],

        'menswear' => [
            'title'    => __("Menswear", 'webmakerr-cart'),
            'count'    => "0",
            'category' => 'menswear',
            'icon'     => 'Cloth'
        ],
//        'clothing' => [
//            'title' => __("Clothing's", 'webmakerr-cart'),
//            'count' => "0",
//            'category' => 'clothing'
//        ],
//        'food' => [
//            'title' => __("Food", 'webmakerr-cart'),
//            'count' => "0",
//            'category' => 'food'
//        ],
//        'electronics' => [
//            'title' => __('Electronics', 'webmakerr-cart'),
//            'count' => "0",
//            'category' => 'electronics'
//        ]
    ];

    foreach ($infos as $key => $info) {
        $filePath = WEBMAKERR_PLUGIN_PATH . 'dummies' . DIRECTORY_SEPARATOR . $key . '.json';
        if (file_exists($filePath)) {
            try {
                $json = file_get_contents($filePath);
                $products = json_decode($json, true);
                $infos[$key]['count'] = count($products);
            } catch (\Exception $exception) {

            }
        }

    }

    return $infos;
});