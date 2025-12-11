<?php

namespace Webmakerr\App\Hooks\Handlers\BlockEditors;

use Webmakerr\App\Hooks\Handlers\ShortCodes\ProductCardShortCode;
use Webmakerr\App\Modules\Templating\AssetLoader;
use Webmakerr\App\Services\Renderer\ProductRenderer;
use Webmakerr\App\Services\Translations\TransStrings;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Models\Product;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\App;
use Webmakerr\Api\StoreSettings;

class ProductGalleryBlockEditor extends BlockEditor
{
    protected static string $editorName = 'product-gallery';

    protected function getScripts(): array
    {
        return [
            [
                'source'       => 'admin/BlockEditor/ProductGallery/ProductGalleryBlockEditor.jsx',
                'dependencies' => ['wp-blocks', 'wp-components']
            ]
        ];
    }

    protected function getStyles(): array
    {
        return [
            'admin/BlockEditor/ProductGallery/style/product-gallery-block-editor.scss'
        ];
    }

    protected function localizeData(): array
    {
        return [
            $this->getLocalizationKey()     => [
                'slug'              => $this->slugPrefix,
                'name'              => static::getEditorName(),
                'title'             => __('Product Gallery', 'webmakerr-cart'),
                'description'       => __('Show the Webmakerr product gallery with your chosen layout.', 'webmakerr-cart'),
                'placeholder_image' => Vite::getAssetUrl('images/placeholder.svg'),
            ],
            'fluent_cart_block_translation' => TransStrings::blockStrings(),
        ];
    }

    public function render(array $shortCodeAttribute, $block = null)
    {
        AssetLoader::loadSingleProductAssets();

        $product = null;
        $insideProductInfo = Arr::get($shortCodeAttribute, 'inside_product_info', 'no');
        $queryType = Arr::get($shortCodeAttribute, 'query_type', 'default');
        $enableImageZoom = Arr::get($shortCodeAttribute, 'enableImageZoom', 'yes');
        
        if ($insideProductInfo === 'yes' || $queryType === 'default') {
            $product = fluent_cart_get_current_product();

        } else {
            $productId = Arr::get($shortCodeAttribute, 'product_id', false);
            if ($productId) {
                $product = Product::query()->with(['variants'])->find($productId);
            }
        }

        if (!$product) {
            return '';
        }
        // import xzoom
//        Vite::enqueueStaticScript(
//            'fluentcart-zoom-js',
//            'public/lib/xzoom/xzoom.js',
//            []
//        );
//        Vite::enqueueStaticStyle(
//            'fluentcart-zoom-css',
//            'public/lib/xzoom/xzoom.css',
//        );
//
//        wp_enqueue_style(
//            'fluentcart-single-product',
//            Vite::getAssetUrl('public/single-product/single-product.scss'),
//            [],
//            ''
//        );
//
//
//        Vite::enqueueStyle(
//            'fluentcart-add-to-cart-btn-css',
//            'public/buttons/add-to-cart/style/style.scss'
//        );
//        Vite::enqueueStyle(
//            'fluentcart-direct-checkout-btn-css',
//            'public/buttons/direct-checkout/style/style.scss'
//        );

        $thumbnailMode = 'all';
        ob_start();
        (new ProductRenderer($product))->renderGallery([
            'mode' => 'all'
        ]);
        return ob_get_clean();
    }


}