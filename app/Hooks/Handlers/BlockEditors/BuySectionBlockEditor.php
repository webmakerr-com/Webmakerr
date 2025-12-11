<?php

namespace Webmakerr\App\Hooks\Handlers\BlockEditors;

use Webmakerr\App\Modules\Templating\AssetLoader;
use Webmakerr\App\Services\Renderer\ProductRenderer;
use Webmakerr\App\Services\Translations\TransStrings;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Models\Product;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\App;
use Webmakerr\Api\StoreSettings;

class BuySectionBlockEditor extends BlockEditor
{
    protected static string $editorName = 'buy-section';

    protected function getScripts(): array
    {
        return [
            [
                'source'       => 'admin/BlockEditor/BuySection/BuySectionBlockEditor.jsx',
                'dependencies' => ['wp-blocks', 'wp-components']
            ]
        ];
    }

    protected function getStyles(): array
    {
        return [
            'admin/BlockEditor/BuySection/style/buy-section-block-editor.scss'
        ];
    }

    protected function localizeData(): array
    {
        return [
            $this->getLocalizationKey()     => [
                'slug'              => $this->slugPrefix,
                'name'              => static::getEditorName(),
                'title'             => __('Buy Section', 'webmakerr-cart'),
                'description'       => __('Present the Webmakerr buy section so customers can purchase fast.', 'webmakerr-cart'),
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

        ob_start();
        (new ProductRenderer($product))->renderBuySection();
        return ob_get_clean();
    }
}