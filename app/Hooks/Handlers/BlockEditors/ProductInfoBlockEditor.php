<?php

namespace Webmakerr\App\Hooks\Handlers\BlockEditors;

use Webmakerr\App\Hooks\Handlers\ShortCodes\ProductCardShortCode;
use Webmakerr\App\Services\Renderer\ProductRenderer;
use Webmakerr\App\Services\Translations\TransStrings;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Models\Product;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\App;
use Webmakerr\Api\StoreSettings;
use Webmakerr\App\Modules\Templating\AssetLoader;

class ProductInfoBlockEditor extends BlockEditor
{
    protected static string $editorName = 'product-info';

    protected function getScripts(): array
    {
        return [
            [
                'source'       => 'admin/BlockEditor/ProductInfo/ProductInfoBlockEditor.jsx',
                'dependencies' => ['wp-blocks', 'wp-components']
            ]
        ];
    }

    protected function getStyles(): array
    {
        return [
            'admin/BlockEditor/ProductInfo/style/product-info-block-editor.scss'
        ];
    }

    protected function localizeData(): array
    {
        return [
            $this->getLocalizationKey()     => [
                'slug'              => $this->slugPrefix,
                'name'              => static::getEditorName(),
                'title'             => __('Product Info', 'webmakerr-cart'),
                'description'       => __('This block will display the product information.', 'webmakerr-cart'),
                'placeholder_image' => Vite::getAssetUrl('images/placeholder.svg'),
            ],
            'fluent_cart_block_translation' => TransStrings::blockStrings(),
        ];
    }


    public function render(array $shortCodeAttribute, $block = null)
    {
        AssetLoader::loadSingleProductAssets();

        $queryType = Arr::get($shortCodeAttribute, 'query_type', 'default');
        $product = null;
        if ($queryType === 'default') {
            $product = fluent_cart_get_current_product();
            $product && setup_postdata($product->ID);
        } else {
            $productId = Arr::get($shortCodeAttribute, 'product_id', false);
            if ($productId) {
                $product = Product::query()->with(['variants'])->find($productId);
                if ($product) {
                    setup_postdata($product->ID);
                }
            }
        }

        if (!$product) {
            return '';
        }

        $innerBlocksContent = '';
        if ($block instanceof \WP_Block && !empty($block->inner_blocks)) {
            $innerBlocksContent .= '<div class="product-info-block-wrapper">';
            foreach ($block->inner_blocks as $inner_block) {
                if (isset($inner_block->parsed_block)) {
                    $innerBlocksContent .= '<div class="product-info-child-block">';
                    $innerBlocksContent .= $inner_block->render();
                    $innerBlocksContent .= '</div>';
                }
            }
            $innerBlocksContent .= '</div>';
        }

        if ($queryType === 'custom') {
            wp_reset_postdata();
        }


        return $innerBlocksContent;
    }


}