<?php

namespace Webmakerr\App\Modules\Templating\Bricks\Elements;

use Bricks\Element;
use Webmakerr\App\Modules\Data\ProductDataSetup;
use Webmakerr\App\Services\Renderer\ProductRenderer;
use Webmakerr\Framework\Support\Arr;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProductStock extends Element
{
    public $category = 'fluent_cart_product';
    public $name = 'fct-product-stock';
    public $icon = 'ti-package';

    public function get_label()
    {
        return esc_html__('Product stock', 'webmakerr-cart');
    }

    public function set_control_groups()
    {
        $this->control_groups['inStock'] = [
            'title' => esc_html__('In stock', 'webmakerr-cart'),
            'tab'   => 'content',
        ];

//
//        $this->control_groups['lowStock'] = [
//            'title' => esc_html__('Low stock', 'webmakerr-cart') . '/' . esc_html__('On backorder', 'webmakerr-cart'),
//            'tab'   => 'content',
//        ];

        $this->control_groups['outOfStock'] = [
            'title' => esc_html__('Out of stock', 'webmakerr-cart'),
            'tab'   => 'content',
        ];
    }

    public function set_controls()
    {
        // In Stock

        $this->controls['inStockText'] = [
            'tab'            => 'content',
            'group'          => 'inStock',
            'type'           => 'text',
            'hasDynamicData' => 'text',
            'placeholder'    => esc_html__('Custom text', 'webmakerr-cart'),
        ];

        $this->controls['inStockTypography'] = [
            'tab'   => 'content',
            'group' => 'inStock',
            'label' => esc_html__('Typography', 'webmakerr-cart'),
            'type'  => 'typography',
            'css'   => [
                [
                    'property' => 'font',
                    'selector' => '.in-stock',
                ],
            ],
        ];

        $this->controls['inStockBackgroundColor'] = [
            'tab'   => 'style',
            'group' => 'inStock',
            'label' => esc_html__('Background color', 'webmakerr-cart'),
            'type'  => 'color',
            'css'   => [
                [
                    'property' => 'background-color',
                    'selector' => '.in-stock',
                ]
            ],
        ];

        // Low Stock
        if (false) {
            $this->controls['lowStockText'] = [
                'tab'            => 'content',
                'group'          => 'lowStock',
                'type'           => 'text',
                'hasDynamicData' => 'text',
                'placeholder'    => esc_html__('Custom text', 'webmakerr-cart'),
            ];

            $this->controls['lowStockTypography'] = [
                'tab'   => 'content',
                'group' => 'lowStock',
                'label' => esc_html__('Typography', 'webmakerr-cart'),
                'type'  => 'typography',
                'css'   => [
                    [
                        'property' => 'font',
                        'selector' => '.low-stock, .available-on-backorder',
                    ],
                ],
            ];

            $this->controls['lowStockBackgroundColor'] = [
                'tab'   => 'style',
                'group' => 'lowStock',
                'label' => esc_html__('Background color', 'webmakerr-cart'),
                'type'  => 'color',
                'css'   => [
                    [
                        'property' => 'background-color',
                        'selector' => '.low-stock, .available-on-backorder',
                    ]
                ],
            ];
        }

        // Out of Stock
        $this->controls['outOfStockText'] = [
            'tab'            => 'content',
            'group'          => 'outOfStock',
            'type'           => 'text',
            'hasDynamicData' => 'text',
            'placeholder'    => esc_html__('Custom text', 'webmakerr-cart'),
        ];

        $this->controls['outOfStockTypography'] = [
            'tab'   => 'content',
            'group' => 'outOfStock',
            'label' => esc_html__('Typography', 'webmakerr-cart'),
            'type'  => 'typography',
            'css'   => [
                [
                    'property' => 'font',
                    'selector' => '.out-of-stock',
                ],
            ],
        ];

        $this->controls['outOfStockBackgroundColor'] = [
            'tab'   => 'style',
            'group' => 'outOfStock',
            'label' => esc_html__('Background color', 'webmakerr-cart'),
            'type'  => 'color',
            'css'   => [
                [
                    'property' => 'background-color',
                    'selector' => '.out-of-stock',
                ]
            ],
        ];
    }

    public function render()
    {

        $product = ProductDataSetup::getProductModel($this->post_id);

        if (empty($product)) {
            return $this->render_element_placeholder(
                [
                    'title'       => esc_html__('For better preview select content to show.', 'webmakerr-cart'),
                    'description' => esc_html__('Go to: Settings > Template Settings > Populate Content', 'webmakerr-cart'),
                ]
            );
        }

        webmakerr_add_filter('webmakerr_cart/product_stock_availability', [$this, 'simulateAvailability'], 10, 2);

        $productRender = new ProductRenderer($product);

        ob_start();
        $productRender->renderStockAvailability();
        $stock_html = ob_get_clean();
        remove_filter('fluent_cart/product_stock_availability', [$this, 'simulateAvailability'], 10, 2);

        if (!$stock_html) {
            return $this->render_element_placeholder(
                [
                    'title' => esc_html__('Stock management not enabled for this product.', 'webmakerr-cart'),
                ]
            );
        }

        echo "<div {$this->render_attributes( '_root' )}>" . wp_kses_post($stock_html) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_attributes() handles escaping
    }

    public function simulateAvailability($availability, $data)
    {
        $settings = $this->settings;
        $is_manage_stock = Arr::get($availability, 'manage_stock');
        if (!$is_manage_stock) {
            return $availability;
        }

        // Get stock via helper function if we don't manage stock on product level (to enable low stock for example)
        // $stock_quantity = Arr::get($productDetail, 'available_quantity');

        // Set availability text based on user input
        switch ($availability['class']) {
            case 'in-stock':
                $availability['availability'] = !empty($settings['inStockText']) ? $settings['inStockText'] : $availability['availability'];
                break;

            case 'available-on-backorder':
            case 'low-stock':
                $availability['availability'] = !empty($settings['lowStockText']) ? $settings['lowStockText'] : $availability['availability'];
                break;

            case 'out-of-stock':
                $availability['availability'] = !empty($settings['outOfStockText']) ? $settings['outOfStockText'] : $availability['availability'];
                break;
        }

        return $availability;
    }
}