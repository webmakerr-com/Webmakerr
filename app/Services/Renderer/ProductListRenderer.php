<?php

namespace Webmakerr\App\Services\Renderer;

use Webmakerr\Framework\Support\Arr;

class ProductListRenderer
{
    protected $products = [];

    protected $listTitle = null;

    protected $wrapperClass = null;

    protected $cursor = null;

    protected $cardConfig = [];

    public function __construct($products, $listTitle = null, $wrapperClass = null)
    {
        $this->products = $products;
        $this->listTitle = $listTitle;
        $this->wrapperClass = $wrapperClass;

        if (strpos((string)$wrapperClass, 'fct-similar-product-list-container') !== false) {
            $this->cardConfig['force_view_offer'] = true;
            $this->cardConfig['show_excerpt'] = false;
        }

        if($products instanceof \Webmakerr\Framework\Pagination\CursorPaginator){
            $this->cursor = wp_parse_args(wp_parse_url($products->nextPageUrl(), PHP_URL_QUERY));
            $this->cursor = Arr::get($this->cursor, 'cursor', '');
        }

    }

    public function render()
    {
        $isSimilarProductList = strpos((string)$this->wrapperClass, 'fct-similar-product-list-container') !== false;
        $productCount = is_countable($this->products) ? count($this->products) : 0;
        $enableSlider = $isSimilarProductList && $productCount > 4;
        ?>
        <section class="fct-product-list-container <?php echo esc_attr($this->wrapperClass); ?>" aria-label="<?php echo esc_attr($this->listTitle ?: __('Product List', 'fluent-cart')); ?>">
            <?php $this->renderTitle(); ?>
            <?php if ($isSimilarProductList): ?>
                <div class="fct-product-list-slider-wrapper <?php echo $enableSlider ? 'has-slider' : ''; ?>" data-fct-similar-slider-wrapper>
                    <?php if ($enableSlider): ?>
                        <button type="button" class="fct-similar-nav-button prev" data-fct-similar-slider-prev aria-label="<?php esc_attr_e('View previous related products', 'fluent-cart'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    <?php endif; ?>

                    <div
                        class="fct-product-list"
                        role="list"
                        aria-live="polite"
                        aria-busy="false"
                    >
                        <?php $this->renderProductList(); ?>
                    </div>

                    <?php if ($enableSlider): ?>
                        <button type="button" class="fct-similar-nav-button next" data-fct-similar-slider-next aria-label="<?php esc_attr_e('View more related products', 'fluent-cart'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div
                    class="fct-product-list"
                    role="list"
                    aria-live="polite"
                    aria-busy="false"
                >
                    <?php $this->renderProductList(); ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }

    public function renderProductList()
    {

        foreach ($this->products as $index => $product) {
            $config = $this->cardConfig;
            if($index == 0 && $this->cursor){
                $config['cursor'] = $this->cursor;
            }
            ?>
            <div
                class="fct-product-list-item"
                role="listitem"
            >
                <?php (new ProductCardRender($product, $config))->render(); ?>
            </div>

            <?php
        }
    }

    public function renderTitle() {

        if(!empty($this->listTitle)) : ?>
            <h4 class="fct-product-list-heading">
                <?php echo esc_html($this->listTitle); ?>
            </h4>
        <?php endif;

    }

}
