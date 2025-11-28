<?php

namespace FluentCart\App\Services\Renderer;

use FluentCart\Api\ModuleSettings;
use FluentCart\Api\Resource\ProductResource;
use FluentCart\Api\StoreSettings;
use FluentCart\App\Helpers\Helper;
use FluentCart\App\Models\Product;
use FluentCart\App\Models\ProductVariation;
use FluentCart\App\Vite;
use FluentCart\Framework\Support\Arr;
use FluentCart\App\App;
use FluentCart\Framework\Support\Collection;

class ProductRenderer
{
    protected $product;
    protected $variants;
    protected $defaultVariant = null;
    protected $hasOnetime = false;
    protected $hasSubscription = false;
    protected $viewType = '';
    protected $columnType = '';
    protected $defaultVariationId = '';
    protected $paymentTypes = [];
    protected $variantsByPaymentTypes = [];
    protected $activeTab = 'onetime';
    protected $images = [];
    protected $defaultImageUrl = null;
    protected $defaultImageAlt = null;
    protected $featuredVideo = [];
    protected $renderCustomSections = true;
    protected $viewersCount = 0;
    protected $addedToCartCount = 0;

    public function __construct(Product $product, $config = [])
    {
        $this->product = $product;
        $this->variants = $product->variants;
        $this->viewType = $config['view_type'] ?? 'both';
        $this->columnType = $config['column_type'] ?? 'masonry';
        $defaultVariationId = $config['default_variation_id'] ?? '';
        $this->renderCustomSections = $config['render_custom_sections'] ?? true;
        $this->featuredVideo = ProductResource::formatFeaturedVideo(
            get_post_meta($product->ID, '_fct_featured_video', true)
        );
        $this->viewersCount = random_int(3, 21);
        $this->addedToCartCount = random_int(15, 29);

        if (!$defaultVariationId) {
            $variationIds = $product->variants->pluck('id')->toArray();
            $defaultVariationId = $product->detail->default_variation_id;

            if (!$defaultVariationId || !in_array($defaultVariationId, $variationIds)) {
                $defaultVariationId = Arr::get($variationIds, '0');
            }

            $this->defaultVariationId = $defaultVariationId;
        }

        foreach ($this->product->variants as $variant) {
            if ($variant->id == $this->defaultVariationId) {
                $this->defaultVariant = $variant;
            }
            $paymentType = Arr::get($variant->other_info, 'payment_type');
            if ($paymentType === 'onetime') {
                $this->hasOnetime = true;
            } else if ($paymentType === 'subscription') {
                $this->hasSubscription = true;
            }
        }

        $this->buildProductGroups();
    }

    public function buildProductGroups()
    {
        $groupKey = 'repeat_interval';
        $otherInfo = (array)Arr::get($this->product->detail, 'other_info');
        $groupBy = Arr::get($otherInfo, 'group_pricing_by', 'repeat_interval'); //repeat_interval,payment_type,none

        if ($groupBy !== 'none') {
            if ($groupBy === 'payment_type') {
                $groupKey = 'payment_type';
            }

            $paymentTypes = [];

            if ($groupBy === 'repeat_interval') {
                foreach ($this->variants as $key => $variant) {
                    $paymentType = 'onetime';
                    $type = Arr::get($variant, 'payment_type');
                    if ($type === 'subscription') {
                        $isInstallment = Arr::get($variant, 'other_info.installment', 'no');
                        if ($isInstallment === 'yes' && App::isProActive()) {
                            $paymentType = 'installment';
                        } else {
                            $paymentType = Arr::get($variant, 'other_info.repeat_interval', 'onetime');
                        }
                    }

                    $paymentTypes[] = $paymentType;

                    if (!isset($this->variantsByPaymentTypes[$paymentType])) {
                        $this->variantsByPaymentTypes[$paymentType] = [];
                    }

                    $this->variantsByPaymentTypes[$paymentType][] = $variant;

                    if ($this->defaultVariationId == $variant['id']) {
                        $this->activeTab = $paymentType;
                    }
                }
            } else {
                foreach ($this->variants as $key => $variant) {
                    $paymentType = 'onetime';
                    $type = Arr::get($variant, 'payment_type');
                    if ($type === 'subscription') {
                        $isInstallment = Arr::get($variant, 'other_info.installment');
                        if ($isInstallment === 'yes' && App::isProActive()) {
                            $paymentType = 'installment';
                        } else {
                            $paymentType = 'subscription';
                        }
                    }
                    $paymentTypes[] = $paymentType;

                    if (!isset($this->variantsByPaymentTypes[$paymentType])) {
                        $this->variantsByPaymentTypes[$paymentType] = [];
                    }

                    $this->variantsByPaymentTypes[$paymentType][] = $variant;

                    if ($this->defaultVariationId == $variant['id']) {
                        $this->activeTab = $paymentType;
                    }
                }
            }

            $paymentTypes = array_unique($paymentTypes);

            $intervalOptions = Helper::getAvailableSubscriptionIntervalOptions();

            $groupLanguageMap = [
                'onetime'      => __('One Time', 'fluent-cart'),
                'subscription' => __('Subscription', 'fluent-cart'),
                'installment'  => __('Installment', 'fluent-cart'),
            ];

            foreach ($intervalOptions as $interval) {
                $groupLanguageMap[$interval['value']] = $interval['label'];
            }

            foreach ($paymentTypes as $paymentType) {
                $this->paymentTypes[$paymentType ?: 'onetime'] = Arr::get($groupLanguageMap, $paymentType ?: 'onetime');
            }
        }
    }

    public function render()
    {
        ?>
        <!-- Mobile gallery + price badge + perks + payments icons -->
        <style>
            /* MAIN IMAGE — no crop */
            .fct-product-gallery-thumb{padding:0; position:relative;}
            .fct-product-gallery-thumb img{
                display:block;width:100%;height:auto;object-fit:contain;
            }

            .fct-product-trust-badges{position:absolute;top:12px;left:12px;display:flex;flex-direction:column;gap:8px;z-index:3;align-items:flex-start;}
            .fct-product-trust-badge{display:flex;align-items:center;gap:8px;background:#f8f9e5;border:1px solid #000;color:#111827;font-weight:500;padding:2px 12px;border-radius:20px;box-shadow:0 1px 2px rgba(0,0,0,0.03);}
            .fct-product-trust-badge--added-to-cart{background:#d50b0b;border:2px solid #fff;color:#fff;}
            .fct-product-trust-badge__icon{width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;color:#111827;}
            .fct-product-trust-badge--added-to-cart .fct-product-trust-badge__icon{color:#fff;}
            .fct-product-trust-badge__text{font-size:12px;line-height:1.4;}

            /* Thumbs default */
            .fct-gallery-thumb-controls{display:flex;flex-wrap:wrap;gap:10px;}
            .fct-gallery-thumb-control-button{padding:0;border:none;background:transparent;cursor:pointer;}
            .fct-gallery-control-thumb{display:block;width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;}

            /* Force thumbs BELOW main image on mobile, very small size */
            @media (max-width: 767px){
                .fct-product-gallery-wrapper.thumb-pos-left{
                    display:flex !important;flex-direction:column !important;gap:12px !important;
                }
                .fct-product-gallery-wrapper.thumb-pos-left .fct-product-gallery-thumb{
                    order:1 !important;
                }
                .fct-product-gallery-wrapper.thumb-pos-left .fct-gallery-thumb-controls{
                    order:2 !important;display:flex !important;flex-direction:row !important;
                    width:100% !important;gap:8px !important;overflow-x:auto !important;flex-wrap:nowrap !important;
                    -webkit-overflow-scrolling:touch;
                }
                .fct-gallery-thumb-controls .fct-gallery-thumb-control-button{
                    flex:0 0 auto !important;
                }
                .fct-gallery-thumb-controls .fct-gallery-control-thumb{
                    width:44px !important;height:44px !important;border-radius:5px !important;
                }
            }

            /* Prev/Next (kept for JS; optional) */
            .fct-gallery-nav{position:absolute;top:50%;transform:translateY(-50%);background:#fff;border:1px solid #e5e7eb;border-radius:9999px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:.9}
            .fct-gallery-nav-prev{left:8px}.fct-gallery-nav-next{right:8px}

            /* PRICE + SAVINGS right side */
            .fct-price-display{display:flex;align-items:center;gap:12px;}
            .fct-price-range{display:flex;align-items:flex-end;gap:8px;}
            .fct-price-badge{
                margin-left:auto;white-space:nowrap;
                background:#E6F6FF;color:#0B6BCB;border:1px solid #B6E1FF;
                font-weight:600;padding:.25rem .5rem;border-radius:.375rem;font-size:.875rem;
            }

            /* PERKS (inline, two items) */
            .fct-product-perks{display:flex;align-items:center;gap:16px;flex-wrap:nowrap;overflow-x:auto;margin-top:10px;}
            .fct-product-perk{display:inline-flex;align-items:center;gap:8px;white-space:nowrap;}
            .fct-product-perk__icon{width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;color:#0B6BCB;background:#F1F5F9;border-radius:9999px;}

            /* ASSURANCES */
            .fct-product-assurances{display:grid;gap:16px;}
            @media (min-width:768px){.fct-product-assurances{grid-template-columns:repeat(3,minmax(0,1fr));}}
            .fct-product-assurance-item{display:flex;gap:12px;align-items:flex-start;padding:14px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;}
            .fct-product-assurance-icon{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:9999px;background:#f1f5f9;color:#0B6BCB;flex:0 0 36px;}
            .fct-product-assurance-copy .title{margin:0;font-weight:600;line-height:1.25;}
            .fct-product-assurance-copy .subtitle{margin:.125rem 0 0;color:#64748b;line-height:1.35;font-size:.925rem;}

            /* REMOVE legacy "Guaranteed Safe Checkout" */
            .fct-payment-security{display:none !important;}

            /* PAYMENT ICONS — no gaps, single line; smaller on mobile */
            .fct-payment-icons{display:flex;align-items:center;gap:1px;flex-wrap:nowrap;overflow-x:auto;white-space:nowrap;margin-top:10px;}
            .fct-payment-icon{display:inline-flex;margin:0;line-height:0;}
            .fct-payment-icons img{display:block;height:28px;width:auto;}
            @media (max-width:767px){
                .fct-payment-icons{flex-wrap:nowrap;overflow-x:auto;}
                .fct-payment-icons img{height:22px;}
            }
        </style>

        <div class="fct-single-product-page" data-fluent-cart-single-product-page>
            <div class="fct-single-product-page-row">
                <?php $this->renderGallery(); ?>
                <div class="fct-product-summary">
                    <div class="fct-product-summary-card">
                        <div class="fct-product-summary-header">
                            <?php $this->renderTitle(); ?>
                            <div class="fct-product-meta">
                                <?php $this->renderStockAvailability(); ?>
                            </div>
                        </div>
                        <?php
                        $this->renderExcerpt();
                        $this->renderPrices();
                        $this->renderBuySection();
                        ?>
                    </div>
                </div>
            </div>
            <div class="fct-product-assurances-section">
                <?php $this->renderAssurances(); ?>
            </div>
            <?php if ($this->renderCustomSections) { $this->renderCustomSections(); } ?>
            <?php $this->renderReviewsSection(); ?>
        </div>
        <?php
    }

    public function renderCustomSectionsOnly()
    {
        if (!$this->renderCustomSections) {
            return;
        }
        $this->renderCustomSections();
    }

    public function renderBuySection($atts = [])
    {
        $otherInfo = (array)Arr::get($this->product->detail, 'other_info');
        $groupBy = Arr::get($otherInfo, 'group_pricing_by', 'repeat_interval'); //repeat_interval,payment_type,none

        echo '<div aria-labelledby="fct-product-summary-title" data-fluent-cart-product-pricing-section data-product-id="' . esc_attr($this->product->ID) . '" class="fct_buy_section">';

        if (count($this->paymentTypes) === 1 || $groupBy === 'none') {
            $this->renderVariants(Arr::get($atts, 'variation_atts', []));
        } else {
            $this->renderTab(Arr::get($atts, 'variation_atts', []));
        }

        $this->renderItemPrice();
        $this->renderQuantity();
        $this->renderPerksRow(); // keep under quantity
        echo '<div class="fct-product-buttons-wrap">';
        $this->renderPurchaseButtons(Arr::get($atts, 'button_atts', []));
        echo '</div>';
        echo '</div>';
    }

    public function renderGalleryThumb()
    {
        $thumbnails = [];

        $featuredMedia = $this->product->thumbnail ?? Vite::getAssetUrl('images/placeholder.svg');
        if (!$featuredMedia) {
            $featuredMedia = [];
        }

        $galleryImage = get_post_meta($this->product->ID, 'fluent-products-gallery-image', true);

        if (!empty($galleryImage)) {
            $thumbnails[0] = [
                'media' => $galleryImage,
            ];
        }

        foreach ($this->variants as $variant) {
            if (!empty($variant['media']['meta_value'])) {
                $thumbnails[$variant['id']] = [
                    'media' => $variant['media']['meta_value'],
                ];
            } else {
                $this->defaultImageUrl = $featuredMedia;
                $this->defaultImageAlt = Arr::get($variant, 'variation_title', '');
            }
        }

        $images = empty($thumbnails) ? [] : $thumbnails;

        $this->images = $images;

        if (!empty($images)) {
            $variationId = $this->defaultVariationId;
            $imageId = $variationId;

            if (isset($images[$imageId])) {
                $imageMetaValue = $images[$imageId];
                $this->defaultImageUrl = Arr::get($imageMetaValue, 'media.0.url', '');
                $this->defaultImageAlt = Arr::get($imageMetaValue, 'media.0.title', '');
            }
        }

        $videoPlayerHtml = '';
        $featuredVideoUrl = Arr::get($this->featuredVideo, 'url', '');
        $featuredVideoType = Arr::get($this->featuredVideo, 'type', '');
        $featuredVideoTitle = Arr::get($this->featuredVideo, 'title', $this->product->post_title);
        $previewImage = $this->defaultImageUrl ?: $featuredMedia;
        if ($this->hasFeaturedVideo()) {
            $videoPlayerHtml = base64_encode($this->getVideoPlayerHtml());
        }

        ?>
        <div class="fct-product-gallery-thumb" role="region"
             aria-label="<?php echo esc_attr($this->product->post_title . ' gallery'); ?>">
            <?php $this->renderTrustBadges(); ?>
            <?php if ($this->hasFeaturedVideo()) { ?>
                <div
                    class="fct-product-featured-video"
                    data-fluent-cart-product-video
                    data-video-embed="<?php echo esc_attr($videoPlayerHtml); ?>"
                    data-video-loaded="false"
                    data-video-url="<?php echo esc_url($featuredVideoUrl); ?>"
                    data-video-type="<?php echo esc_attr($featuredVideoType); ?>"
                    data-video-title="<?php echo esc_attr($featuredVideoTitle); ?>"
                >
                    <button
                        type="button"
                        class="fct-product-featured-video__preview"
                        data-fluent-cart-product-video-preview
                        aria-label="<?php esc_attr_e('Play featured video preview', 'fluent-cart'); ?>"
                    >
                        <img
                            src="<?php echo esc_url($previewImage); ?>"
                            alt="<?php echo esc_attr($featuredVideoTitle); ?>"
                            class="fct-product-featured-video__preview-image"
                            loading="lazy"
                        />
                        <span class="fct-product-featured-video__play" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none">
                                <path d="M9 7L17 12L9 17V7Z" fill="currentColor"/>
                            </svg>
                        </span>
                    </button>
                </div>
            <?php } ?>
            <img
                src="<?php echo esc_url($this->defaultImageUrl ?? '') ?>"
                alt="<?php echo esc_attr($this->defaultImageAlt) ?>"
                data-fluent-cart-single-product-page-product-thumbnail
                data-default-image-url="<?php echo esc_url($featuredMedia) ?>"
                class="<?php echo $this->hasFeaturedVideo() ? 'is-hidden' : ''; ?>"
            />
            <button
                type="button"
                class="fct-gallery-nav fct-gallery-nav-prev"
                data-gallery-nav="prev"
                aria-label="<?php esc_attr_e('Previous image', 'fluent-cart'); ?>"
            >
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button
                type="button"
                class="fct-gallery-nav fct-gallery-nav-next"
                data-gallery-nav="next"
                aria-label="<?php esc_attr_e('Next image', 'fluent-cart'); ?>"
            >
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <?php
    }

    protected function renderTrustBadges()
    {
        $addedToCartCount = intval($this->addedToCartCount);
        $addedToCartTemplate = __('%d people have added this item to cart (24H)', 'fluent-cart');
        $viewersCount = intval($this->viewersCount);
        $viewersTemplate = __('%d people are viewing this item', 'fluent-cart');

        ?>
        <div class="fct-product-trust-badges" aria-label="<?php echo esc_attr__('Product activity info', 'fluent-cart'); ?>">
            <span class="fct-product-trust-badge">
                <span class="fct-product-trust-badge__icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.66699 10C1.66699 10 4.58366 3.33331 10.0003 3.33331C15.417 3.33331 18.3337 10 18.3337 10C18.3337 10 15.417 16.6666 10.0003 16.6666C4.58366 16.6666 1.66699 10 1.66699 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span
                    class="fct-product-trust-badge__text"
                    data-fluent-cart-viewers-text
                    data-template="<?php echo esc_attr($viewersTemplate); ?>"
                    data-count="<?php echo esc_attr($viewersCount); ?>"
                >
                    <?php printf(esc_html__('%d people are viewing this item', 'fluent-cart'), $viewersCount); ?>
                </span>
            </span>
            <span class="fct-product-trust-badge fct-product-trust-badge--added-to-cart" data-fluent-cart-added-to-cart-badge>
                <span class="fct-product-trust-badge__icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33301 4.16669H4.69134C5.09032 4.16669 5.28981 4.16669 5.45423 4.23867C5.59839 4.30136 5.72435 4.40347 5.81707 4.533C5.92026 4.67737 5.96967 4.87425 6.06849 5.26802L6.33301 6.33335" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M6.33301 6.33331H16.1507C16.5933 6.33331 16.8147 6.33331 16.9602 6.44766C17.0889 6.54897 17.1635 6.70102 17.1626 6.86186C17.1616 7.04343 17.0182 7.24645 16.7312 7.6525L14.7273 10.502C14.5935 10.6946 14.5265 10.7913 14.4336 10.8619C14.3505 10.9243 14.2556 10.9709 14.1534 11C14.0387 11.0333 13.9167 11.0333 13.6727 11.0333H8.03065C7.31715 11.0333 6.96039 11.0333 6.71908 10.8655C6.50664 10.7188 6.37766 10.4872 6.36336 10.2342C6.34765 9.94647 6.54356 9.62563 6.93539 8.98395L7.77823 7.59274C7.92343 7.35309 7.99601 7.23327 8.096 7.15246C8.18501 7.0808 8.28915 7.02783 8.40009 6.99757C8.52423 6.96352 8.66039 6.96352 8.93273 6.96352" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M8.33301 14.1667C8.33301 14.6269 7.95993 15 7.49967 15C7.03942 15 6.66634 14.6269 6.66634 14.1667C6.66634 13.7064 7.03942 13.3333 7.49967 13.3333C7.95993 13.3333 8.33301 13.7064 8.33301 14.1667Z" fill="currentColor" />
                        <path d="M15 14.1667C15 14.6269 14.6269 15 14.1667 15C13.7064 15 13.3333 14.6269 13.3333 14.1667C13.3333 13.7064 13.7064 13.3333 14.1667 13.3333C14.6269 13.3333 15 13.7064 15 14.1667Z" fill="currentColor" />
                    </svg>
                </span>
                <span
                    class="fct-product-trust-badge__text"
                    data-fluent-cart-added-to-cart-text
                    data-template="<?php echo esc_attr($addedToCartTemplate); ?>"
                    data-count="<?php echo esc_attr($addedToCartCount); ?>"
                >
                    <?php echo esc_html(sprintf($addedToCartTemplate, $addedToCartCount)); ?>
                </span>
            </span>
            <script>
                (function() {
                    var viewerElement = document.querySelector('[data-fluent-cart-viewers-text]');

                    if (viewerElement) {
                        var viewerTemplate = viewerElement.getAttribute('data-template');
                        var viewerInitialCount = parseInt(viewerElement.getAttribute('data-count'), 10);

                        if (viewerTemplate && !isNaN(viewerInitialCount)) {
                            var viewerMinimum = Math.max(0, viewerInitialCount - 5);
                            var viewerMaximum = viewerInitialCount + 5;

                            var updateViewerCount = function() {
                                var nextViewerCount = Math.floor(Math.random() * (viewerMaximum - viewerMinimum + 1)) + viewerMinimum;
                                viewerElement.textContent = viewerTemplate.replace('%d', nextViewerCount);
                            };

                            setInterval(updateViewerCount, 3000);
                        }
                    }
                })();
            </script>
        </div>
        <?php
    }

    public function renderGalleryThumbControls()
    {
        ?>
        <div class="fct-gallery-thumb-controls" data-fluent-cart-single-product-page-product-thumbnail-controls>
            <?php if ($this->hasFeaturedVideo()) {
                $this->renderGalleryVideoControl();
            } ?>
            <?php $this->renderGalleryThumbControl(); ?>
        </div>
        <?php
    }

    public function renderGalleryVideoControl()
    {
        $videoUrl = Arr::get($this->featuredVideo, 'url', '');
        if (!$videoUrl) {
            return;
        }

        ?>
        <button
            type="button"
            class="fct-gallery-thumb-control-button active"
            data-fluent-cart-thumb-control-button
            data-media-type="video"
            data-url="<?php echo esc_url($videoUrl); ?>"
            data-variation-id="0"
            aria-label="<?php echo esc_attr__('View product video', 'fluent-cart'); ?>"
            aria-pressed="true"
        >
            <span class="fct-gallery-thumb-control-button__label">
                <?php esc_html_e('Video', 'fluent-cart'); ?>
            </span>
        </button>
        <?php
    }

    public function renderGalleryThumbControl()
    {
        foreach ($this->images as $imageId => $image) {
            if (empty($image['media']) || !is_array($image['media'])) {
                continue;
            }

            foreach ($image['media'] as $item) {
                if (empty(Arr::get($item, 'url', ''))) {
                    continue;
                }

                $this->renderGalleryThumbControlButton($item, $imageId);
            }
        }
    }

    public function renderGalleryThumbControlButton($item, $imageId)
    {
        $isHidden = '';
        $itemUrl = Arr::get($item, 'url', '');
        $itemTitle = Arr::get($item, 'title', '');
        $isSelected = $imageId == $this->defaultVariationId ? 'true' : 'false';
        ?>
        <button
            type="button"
            class="fct-gallery-thumb-control-button <?php echo esc_attr($isHidden); ?>"
            data-fluent-cart-thumb-control-button
            data-url="<?php echo esc_url($itemUrl); ?>"
            data-variation-id="<?php echo esc_attr($imageId); ?>"
            aria-label="<?php echo esc_attr(sprintf(__('View %s image', 'fluent-cart'), $itemTitle)); ?>"
            aria-pressed="<?php echo esc_attr($isSelected); ?>"
        >
            <img
                class="fct-gallery-control-thumb"
                data-fluent-cart-single-product-page-product-thumbnail-controls-thumb
                src="<?php echo esc_url($itemUrl); ?>"
                alt="<?php echo esc_attr($itemTitle); ?>"
            />
        </button>
        <?php
    }

    protected function hasFeaturedVideo()
    {
        return !empty(Arr::get($this->featuredVideo, 'url'));
    }

    protected function getVideoPlayerHtml()
    {
        if (!$this->hasFeaturedVideo()) {
            return '';
        }

        $videoUrl = Arr::get($this->featuredVideo, 'url', '');
        $title = Arr::get($this->featuredVideo, 'title', $this->product->post_title);

        $embedHtml = wp_oembed_get($videoUrl);

        if ($embedHtml) {
            return $this->formatEmbedHtml($embedHtml);
        }

        return sprintf(
            '<video class="fct-product-featured-video__embed" controls preload="metadata" playsinline src="%1$s" title="%2$s" style="width:100%%;height:100%%;border-radius:4px;"></video>',
            esc_url($videoUrl),
            esc_attr($title)
        );
    }

    protected function formatEmbedHtml($embedHtml)
    {
        if (!$embedHtml) {
            return '';
        }

        if (stripos($embedHtml, '<iframe') !== false) {
            if (!preg_match('/<iframe[^>]*\bloading=/i', $embedHtml)) {
                $embedHtml = preg_replace('/<iframe\b/i', '<iframe loading="lazy"', $embedHtml, 1);
            }

            if (!preg_match('/<iframe[^>]*\bclass=/i', $embedHtml)) {
                $embedHtml = preg_replace('/<iframe\b/i', '<iframe class="fct-product-featured-video__embed"', $embedHtml, 1);
            } else {
                $embedHtml = preg_replace('/<iframe([^>]*)class="([^"]*)"/i', '<iframe$1class="$2 fct-product-featured-video__embed"', $embedHtml, 1);
            }
        }

        return $embedHtml;
    }

    protected function getCustomSections(): array
    {
        $otherInfo = (array)Arr::get($this->product->detail, 'other_info', []);
        $sections = Arr::get($otherInfo, 'custom_sections', []);

        if (!is_array($sections)) {
            return [];
        }

        $normalized = [];

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $title = Arr::get($section, 'title', '');
            $description = Arr::get($section, 'description', '');
            $mediaType = Arr::get($section, 'media_type', 'image');
            $image = Arr::get($section, 'image', []);
            $video = Arr::get($section, 'video', []);

            if (!$title && !$description && empty($image) && empty($video)) {
                continue;
            }

            $normalized[] = [
                'title'       => $title,
                'description' => $description,
                'media_type'  => $mediaType ?: 'image',
                'image'       => $image,
                'video'       => $video
            ];

            if (count($normalized) >= 4) {
                break;
            }
        }

        return $normalized;
    }

    protected function getSectionImageUrl($image): string
    {
        if (empty($image)) {
            return '';
        }

        $url = Arr::get($image, 'url', '');

        if (!$url && Arr::get($image, 'id')) {
            $url = wp_get_attachment_image_url(Arr::get($image, 'id'), 'full');
        }

        return $url ?: '';
    }

    protected function renderCustomSections()
    {
        $sections = $this->getCustomSections();

        if (empty($sections)) {
            return;
        }

        echo '<div class="fct-product-custom-sections">';

        foreach ($sections as $index => $section) {
            $isReversed = $index % 2 === 1 ? 'is-reverse' : '';
            echo '<div class="fct-product-custom-section ' . esc_attr($isReversed) . '">';

            echo '<div class="fct-product-section-media">';
            $this->renderSectionMedia($section);
            echo '</div>';

            echo '<div class="fct-product-section-copy">';
            if (!empty($section['title'])) {
                echo '<h3 class="fct-product-section-heading">' . esc_html($section['title']) . '</h3>';
            }
            if (!empty($section['description'])) {
                echo '<p class="fct-product-section-description">' . wp_kses_post($section['description']) . '</p>';
            }
            $this->renderCustomSectionAddToCart();
            echo '</div>';

            echo '</div>';
        }

        echo '</div>';
    }

    protected function renderSectionMedia($section)
    {
        $mediaType = Arr::get($section, 'media_type', 'image');

        if ($mediaType === 'video') {
            echo '<div class="fct-product-section-video">';
            $this->renderSectionVideo(Arr::get($section, 'video', []), Arr::get($section, 'title', ''));
            echo '</div>';
            return;
        }

        $image = Arr::get($section, 'image', []);
        $imageUrl = $this->getSectionImageUrl($image);

        if (!$imageUrl) {
            return;
        }

        $alt = Arr::get($image, 'title', Arr::get($section, 'title', $this->product->post_title));

        echo '<div class="fct-product-section-image">';
        echo '<img src="' . esc_url($imageUrl) . '" alt="' . esc_attr($alt) . '" loading="lazy" />';
        echo '</div>';
    }

    protected function renderSectionVideo($video, $title = '')
    {
        $videoUrl = Arr::get($video, 'url', '');

        if (!$videoUrl) {
            return;
        }

        $type = Arr::get($video, 'type');
        $videoTitle = $title ?: Arr::get($video, 'title', $this->product->post_title);

        if ($type === 'file' || preg_match('/\.(mp4|mov|webm|ogg)(\?.*)?$/i', $videoUrl)) {
            echo sprintf(
                '<video class="fct-product-section-video" controls preload="metadata" playsinline src="%1$s" title="%2$s"></video>',
                esc_url($videoUrl),
                esc_attr($videoTitle)
            );
            return;
        }

        $embedHtml = wp_oembed_get($videoUrl);

        if ($embedHtml) {
            echo $this->formatEmbedHtml($embedHtml); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }

        echo '<a class="fct-product-section-video-link" href="' . esc_url($videoUrl) . '" target="_blank" rel="noopener">' . esc_html($videoUrl) . '</a>';
    }

    protected function renderCustomSectionAddToCart()
    {
        if ($this->isAddToCartOutOfStock()) {
            $this->renderOutOfStockMessage();
            return;
        }

        if (!$this->hasOnetime) {
            return;
        }

        $config = $this->getAddToCartButtonConfig();

        if (empty($config)) {
            return;
        }

        echo '<div class="fct-product-section-actions">';
        $this->renderAddToCartButton($config['cartAttributes'], $config['addToCartText']);
        echo '</div>';
    }

    protected function renderReviewsSection()
    {
        $reviews = $this->getReviews();

        if (empty($reviews)) {
            return;
        }

        echo '<div class="fct-product-reviews" id="fct-product-reviews">';
        echo '<div class="fct-product-reviews__header">';
        echo '<h2 class="fct-product-reviews__title">' . esc_html__('Reviews', 'fluent-cart') . '</h2>';
        echo '<p class="fct-product-reviews__subtitle">' . esc_html__('What customers are saying', 'fluent-cart') . '</p>';
        echo '</div>';
        echo '<div class="fct-product-reviews__list">';

        foreach ($reviews as $review) {
            $initials = $this->getInitials(Arr::get($review, 'name', ''));
            $rating = (float)Arr::get($review, 'rating', 0);
            $ratingText = number_format($rating, 1);
            $countryCode = Arr::get($review, 'country', '');
            $countryName = Arr::get($review, 'country_name', '');
            $flag = $this->getFlagEmoji($countryCode);
            $flagIcon = $this->getFlagIconUrl($countryCode);
            $date = $this->formatReviewDate(Arr::get($review, 'date', ''));

            echo '<div class="fct-product-review-card">';
            echo '<div class="fct-product-review-card__header">';
            echo '<div class="fct-reviewer">';
            echo '<div class="fct-reviewer__avatar" aria-hidden="true">' . esc_html($initials ?: '–') . '</div>';
            echo '<div class="fct-reviewer__meta">';
            echo '<p class="fct-reviewer__name">' . esc_html(Arr::get($review, 'name', '')) . '</p>';
            echo '<div class="fct-reviewer__detail">';

            if ($flagIcon) {
                echo '<span class="fct-reviewer__flag" aria-hidden="true">';
                echo '<img src="' . esc_url($flagIcon) . '" alt="" loading="lazy">';
                echo '</span>';
            } else if ($flag) {
                echo '<span class="fct-reviewer__flag" aria-hidden="true">' . esc_html($flag) . '</span>';
            }

            if ($countryName) {
                echo '<span class="screen-reader-text">' . esc_html($countryName) . '</span>';
            }

            if ($date) {
                echo '<span class="fct-reviewer__date">' . esc_html($date) . '</span>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';

            echo '<div class="fct-review-rating" aria-label="' . esc_attr(sprintf(__('Rated %s out of 5', 'fluent-cart'), $ratingText)) . '">';
            echo '<span class="fct-review-rating__label">' . esc_html__('Rating', 'fluent-cart') . '</span>';
            echo '<span class="fct-review-rating__value">' . esc_html($ratingText) . '</span>';
            echo '<span class="fct-review-rating__stars" aria-hidden="true">';
            echo '<span class="fct-review-rating__stars-active" style="width:' . esc_attr($this->getStarFillWidth($rating)) . '%;">★★★★★</span>';
            echo '<span class="fct-review-rating__stars-base">★★★★★</span>';
            echo '</span>';
            echo '</div>';
            echo '</div>';

            $text = Arr::get($review, 'text', '');
            if ($text) {
                echo '<div class="fct-product-review-card__body">' . wpautop(wp_kses_post($text)) . '</div>';
            }

            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    protected function getReviews(): array
    {
        $reviews = Arr::get($this->product->detail, 'other_info.reviews', []);

        if (!is_array($reviews)) {
            return [];
        }

        $normalized = [];

        foreach ($reviews as $review) {
            if (!is_array($review)) {
                continue;
            }

            $name = trim((string)Arr::get($review, 'name', ''));
            $text = trim((string)Arr::get($review, 'text', ''));
            $rating = (float)Arr::get($review, 'rating', 0);
            $rawCountry = trim((string)Arr::get($review, 'country', ''));
            $countryCode = $this->normalizeCountryCode($rawCountry);

            if (!$name && !$text && !$rating) {
                continue;
            }

            $normalized[] = [
                'name'         => $name,
                'text'         => $text,
                'rating'       => max(0, min(5, round($rating, 1))),
                'country'      => $countryCode,
                'country_name' => $rawCountry ?: $countryCode,
                'date'         => Arr::get($review, 'date', ''),
            ];

            if (count($normalized) >= 15) {
                break;
            }
        }

        return $normalized;
    }

    protected function getInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';

        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (!$part) {
                    continue;
                }

                $initials .= mb_strtoupper(mb_substr($part, 0, 1));

                if (mb_strlen($initials) >= 2) {
                    break;
                }
            }
        }

        return $initials;
    }

    protected function normalizeCountryCode(?string $country): string
    {
        $code = strtoupper(substr((string)$country, 0, 2));

        if (!preg_match('/^[A-Z]{2}$/', $code)) {
            return '';
        }

        return $code;
    }

    protected function getFlagIconUrl(?string $country): string
    {
        $code = $this->normalizeCountryCode($country);

        if (!$code) {
            return '';
        }

        return 'https://flagcdn.com/24x18/' . strtolower($code) . '.png';
    }

    protected function getFlagEmoji(?string $country): string
    {
        $code = $this->normalizeCountryCode($country);

        if (!$code) {
            return '';
        }
        $offset = 127397;
        $first = $offset + ord($code[0]);
        $second = $offset + ord($code[1]);

        return mb_convert_encoding('&#' . $first . ';', 'UTF-8', 'HTML-ENTITIES')
            . mb_convert_encoding('&#' . $second . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    protected function formatReviewDate($date): string
    {
        if (!$date) {
            return '';
        }

        $timestamp = strtotime((string)$date);

        if (!$timestamp) {
            return sanitize_text_field((string)$date);
        }

        return date_i18n(get_option('date_format'), $timestamp);
    }

    protected function getStarFillWidth(float $rating): float
    {
        $clamped = max(0, min($rating, 5));

        return ($clamped / 5) * 100;
    }

    public function renderGallery($args = [])
    {
        $defaults = [
            'thumbnail_mode' => 'all', // horizontal, vertical
            'thumb_position' => 'left' // bottom, left, right, top
        ];

        $atts = wp_parse_args($args, $defaults);

        $thumbnailMode = $atts['thumbnail_mode'];

        $wrapperAtts = [
            'class'                                    => 'fct-product-gallery-wrapper ' . 'thumb-pos-' . $atts['thumb_position'] . ' thumb-mode-' . $thumbnailMode,
            'data-fct-product-gallery'                 => '',
            'data-fluent-cart-product-gallery-wrapper' => '',
            'data-thumbnail-mode'                      => $thumbnailMode,
            'data-product-id'                          => $this->product->ID,
        ];

        ?>
        <div <?php RenderHelper::renderAtts($wrapperAtts); ?>>
            <?php $this->renderGalleryThumb(); ?>
            <?php $this->renderGalleryThumbControls(); ?>
        </div>
        <?php
    }

    public function renderTitle()
    {
        ?>
        <div class="fct-product-title">
            <h1 id="fct-product-summary-title"><?php echo esc_html($this->product->post_title); ?></h1>
        </div>
        <?php
    }

    public function renderStockAvailability($wrapper_attributes = '')
    {
        if (!ModuleSettings::isActive('stock_management')) {
            return '';
        }

        $stockAvailability = $this->product->detail->getStockAvailability();

        if (!Arr::get($stockAvailability, 'manage_stock')) {
            return '';
        }

        $stockLabel = $stockAvailability['availability'];

        $hasInStock = $this->product->variants()
            ->where('stock_status', Helper::IN_STOCK)->exists();

        if (!$hasInStock) {
            $stockLabel = __('Out of stock', 'fluent-cart');
        }

        $statusClass = $stockAvailability['class'] ?? '';

        echo sprintf(
            '<div class="fct-product-stock %1$s" role="status" aria-live="polite">
                <div %2$s>
                    <span class="fct-stock-status fct_status_badge_%1$s" data-fluent-cart-product-stock>
                        %3$s
                    </span>
                </div>
            </div>',
            esc_attr($statusClass),
            $wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_html($stockLabel)
        );
    }

    public function renderExcerpt()
    {
        $excerpt = $this->product->post_excerpt;
        if (!$excerpt) {
            return;
        }
        ?>
        <div class="fct-product-excerpt" aria-labelledby="fct-product-summary-title">
            <p><?php echo wp_kses_post($excerpt); ?></p>
        </div>
        <?php

    }

    public function renderPrices()
    {
        if ($this->product->detail->variation_type === 'simple') {
            $first_price  = $this->product->variants()->first();
            $itemPrice    = $first_price ? $first_price->item_price : 0;
            $comparePrice = $first_price ? $first_price->compare_price : 0;
            if ($comparePrice <= $itemPrice) {
                $comparePrice = 0;
            }
            $savingsPercent = $comparePrice ? round((($comparePrice - $itemPrice) / $comparePrice) * 100) : 0;
            $savedAmount    = $comparePrice ? $comparePrice - $itemPrice : 0;

            do_action('fluent_cart/product/single/before_price_block', [
                'product'       => $this->product,
                'current_price' => $itemPrice,
                'scope'         => 'price_range'
            ]);

            $aria_label = $comparePrice
                ? sprintf(__('Original Price: %1$s, Price: %2$s', 'fluent-cart'), Helper::toDecimal($comparePrice), Helper::toDecimal($itemPrice))
                : sprintf(__('Price: %1$s', 'fluent-cart'), Helper::toDecimal($itemPrice));

            ?>
            <div class="fct-price-display" data-fluent-cart-price-display>
                <div class="fct-price-range fct-product-prices" role="term" aria-label="<?php echo esc_attr($aria_label); ?>">
                    <span class="fct-compare-price <?php echo $comparePrice ? '' : 'is-hidden'; ?>" data-fluent-cart-compare-price>
                        <del aria-label="<?php echo esc_attr(__('Original price', 'fluent-cart')); ?>"><?php echo esc_html(Helper::toDecimal($comparePrice)); ?></del>
                    </span>
                    <span class="fct-item-price" data-fluent-cart-item-price aria-label="<?php echo esc_attr(__('Current price', 'fluent-cart')); ?>">
                        <?php echo esc_html(Helper::toDecimal($itemPrice)); ?>
                        <?php do_action('fluent_cart/product/after_price', [
                            'product'       => $this->product,
                            'current_price' => $itemPrice,
                            'scope'         => 'price_range'
                        ]); ?>
                    </span>
                </div>
                <span class="fct-price-badge <?php echo $savingsPercent ? '' : 'is-hidden'; ?>" data-fluent-cart-savings-badge>
                    <?php echo esc_html(sprintf(__('You save %1$s%% (%2$s)', 'fluent-cart'), $savingsPercent, Helper::toDecimal($savedAmount))); ?>
                </span>
            </div>
            <?php
            do_action('fluent_cart/product/single/after_price_block', [
                'product'       => $this->product,
                'current_price' => $itemPrice,
                'scope'         => 'price_range'
            ]);
            return;
        }

        $defaultPrice = $this->defaultVariant ? $this->defaultVariant->item_price : $this->product->detail->min_price;
        $comparePrice = $this->defaultVariant ? $this->defaultVariant->compare_price : 0;

        if ($comparePrice <= $defaultPrice) {
            $comparePrice = 0;
        }
        $savingsPercent = $comparePrice ? round((($comparePrice - $defaultPrice) / $comparePrice) * 100) : 0;
        $savedAmount    = $comparePrice ? $comparePrice - $defaultPrice : 0;

        do_action('fluent_cart/product/single/before_price_range_block', [
            'product'       => $this->product,
            'current_price' => $defaultPrice,
            'scope'         => 'price_range'
        ]);

        $aria_label = $comparePrice
            ? sprintf(__('Original Price: %1$s, Price: %2$s', 'fluent-cart'), Helper::toDecimal($comparePrice), Helper::toDecimal($defaultPrice))
            : sprintf(__('Price: %1$s', 'fluent-cart'), Helper::toDecimal($defaultPrice));

        ?>
        <div class="fct-price-display" data-fluent-cart-price-display>
            <div class="fct-price-range fct-product-prices" role="term" aria-label="<?php echo esc_attr($aria_label); ?>">
                <span class="fct-compare-price <?php echo $comparePrice ? '' : 'is-hidden'; ?>" data-fluent-cart-compare-price>
                    <del aria-label="<?php echo esc_attr(__('Original price', 'fluent-cart')); ?>"><?php echo esc_html(Helper::toDecimal($comparePrice)); ?></del>
                </span>
                <span class="fct-item-price" data-fluent-cart-item-price aria-label="<?php echo esc_attr(__('Current price', 'fluent-cart')); ?>">
                    <?php echo esc_html(Helper::toDecimal($defaultPrice)); ?>
                    <?php do_action('fluent_cart/product/after_price', [
                        'product'       => $this->product,
                        'current_price' => $defaultPrice,
                        'scope'         => 'price_range'
                    ]); ?>
                </span>
            </div>
            <span class="fct-price-badge <?php echo $savingsPercent ? '' : 'is-hidden'; ?>" data-fluent-cart-savings-badge>
                <?php echo esc_html(sprintf(__('You save %1$s%% (%2$s)', 'fluent-cart'), $savingsPercent, Helper::toDecimal($savedAmount))); ?>
            </span>
        </div>
        <?php
        do_action('fluent_cart/product/single/after_price_range_block', [
            'product'       => $this->product,
            'current_price' => $defaultPrice,
            'scope'         => 'price_range'
        ]);
    }

    public function renderVariants($atts = [])
    {
        if ($this->product->detail->variation_type === 'simple') {
            return;
        }

        $variants = $this->product->variants;
        if (!$variants || $variants->isEmpty()) {
            return;
        }

        $variants = $variants->sortBy('serial_index')->values();

        $classes = array_filter([
            'fct-product-variants',
            'column-type-' . $this->columnType,
            Arr::get($atts, 'wrapper_class', ''),
        ]);

        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" role="radiogroup"
             aria-label="<?php esc_attr_e('Product Variants', 'fluent-cart'); ?>">
            <?php foreach ($variants as $variant) {
                do_action('fluent_cart/product/single/before_variant_item', [
                    'product' => $this->product,
                    'variant' => $variant,
                    'scope'   => 'product_variant_item'
                ]);
                $this->renderVariationItem($variant, $this->defaultVariationId);
                do_action('fluent_cart/product/single/after_variant_item', [
                    'product' => $this->product,
                    'variant' => $variant,
                    'scope'   => 'product_variant_item'
                ]);
            } ?>
        </div>
        <?php
    }

    public function renderItemPrice()
    {
        if ($this->product->detail->variation_type === 'simple' && !$this->hasSubscription) {
            return;
        }

        do_action('fluent_cart/product/single/before_price_block', [
            'product'       => $this->product,
            'current_price' => $this->defaultVariant ? $this->defaultVariant->item_price : 0,
            'scope'         => 'product_variant_price'
        ]);
        ?>
        <?php if ($this->viewType !== 'text' || $this->columnType !== 'one'): ?>
            <?php foreach ($this->product->variants as $variant): ?>
                <div
                    class="fct-product-item-price fluent-cart-product-variation-content <?php echo $this->defaultVariant->id != $variant->id ? ' is-hidden' : '' ?>"
                    data-fluent-cart-product-item-price
                    data-variation-id="<?php echo esc_attr($variant->id); ?>"
                >
                    <?php if ($this->defaultVariant && !$this->hasSubscription) {
                        if ($variant->compare_price): ?>
                            <span class="fct-compare-price">
                                <del><?php echo esc_html(Helper::toDecimal($variant->compare_price)); ?></del>
                            </span>
                        <?php endif;

                        echo wp_kses_post(apply_filters('fluent_cart/single_product/variation_price', esc_html(Helper::toDecimal($variant->item_price)), [
                            'product' => $this->product,
                            'variant' => $variant,
                            'scope'   => 'product_variant_price'
                        ]));
                        do_action('fluent_cart/product/after_price', [
                            'product'       => $this->product,
                            'current_price' => $variant->item_price,
                            'scope'         => 'product_variant_price'
                        ]);
                    } ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($this->hasSubscription && $this->viewType !== 'text' && $this->columnType !== 'one'): ?>
            <?php foreach ($this->product->variants as $variant): ?>
                <?php
                $paymentType = Arr::get($variant->other_info, 'payment_type', 'onetime');
                $atts = [
                    'class'                                 => 'fct-product-payment-type fluent-cart-product-variation-content ' . ($paymentType !== 'subscription' || $this->defaultVariant->id != $variant->id ? ' is-hidden' : ''),
                    'data-fluent-cart-product-payment-type' => '',
                    'data-variation-id'                     => $variant->id
                ];
                ?>
                <div <?php $this->renderAttributes($atts); ?>>
                    <?php if ($variant->compare_price): ?>
                        <span class="fct-compare-price">
                            <del><?php echo esc_html(Helper::toDecimal($variant->compare_price)); ?></del>
                        </span>
                    <?php endif; ?>

                    <?php
                    if ($paymentType === 'onetime') {
                        echo esc_html(Helper::toDecimal($variant->item_price));
                    } else {
                        echo wp_kses_post(apply_filters('fluent_cart/single_product/variation_price', esc_html($variant->getSubscriptionTermsText(true)), [
                            'product' => $this->product,
                            'variant' => $variant,
                            'scope'   => 'product_variant_price'
                        ]));
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        <?php endif;

        do_action('fluent_cart/product/single/after_price_block', [
            'product'       => $this->product,
            'current_price' => $this->defaultVariant ? $this->defaultVariant->item_price : 0,
            'scope'         => 'product_variant_price'
        ]);
    }

    public function renderQuantity()
    {
        $soldIndividually = $this->product->soldIndividually();

        if (!$this->hasOnetime || $soldIndividually) {
            return;
        }

        $attributes = [
            'data-fluent-cart-product-quantity-container' => '',
            'data-cart-id'                                => $this->defaultVariant ? $this->defaultVariant->id : '',
            'data-variation-type'                         => $this->product->detail->variation_type,
            'data-payment-type'                           => 'onetime',
            'class'                                       => 'fct-product-quantity-container'
        ];

        $defaultVariantData = $this->getDefaultVariantData();

        if ($this->hasSubscription && Arr::get($defaultVariantData, 'payment_type') !== 'onetime') {
            $attributes['class'] .= ' is-hidden';
        }

        do_action('fluent_cart/product/single/before_quantity_block', [
            'product' => $this->product,
            'scope'   => 'product_quantity_block'
        ]);
        ?>
        <div <?php $this->renderAttributes($attributes); ?>>
            <label for="fct-product-qty-input" class="quantity-title">
                <?php esc_html_e('Quantity', 'fluent-cart'); ?>
            </label>

            <div class="fct-product-quantity">
                <button class="fct-quantity-decrease-button"
                        data-fluent-cart-product-qty-decrease-button
                        title="<?php esc_html_e('Decrease Quantity', 'fluent-cart'); ?>"
                        aria-label="<?php esc_attr_e('Decrease Quantity', 'fluent-cart'); ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="2" viewBox="0 0 14 2" fill="none">
                        <path d="M12.3333 1L1.66659 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round"></path>
                    </svg>
                </button>

                <input
                    id="fct-product-qty-input"
                    min="1"
                    <?php echo $soldIndividually ? 'max="1"' : ''; ?>
                    class="fct-quantity-input"
                    data-fluent-cart-single-product-page-product-quantity-input
                    type="text"
                    placeholder="<?php esc_attr_e('Quantity', 'fluent-cart'); ?>"
                    value="1"
                    aria-label="<?php esc_attr_e('Product quantity', 'fluent-cart'); ?>"
                />

                <button class="fct-quantity-increase-button"
                        data-fluent-cart-product-qty-increase-button
                        title="<?php esc_attr_e('Increase Quantity', 'fluent-cart'); ?>"
                        aria-label="<?php esc_attr_e('Increase Quantity', 'fluent-cart'); ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M6.99996 1.66666L6.99996 12.3333M12.3333 6.99999L1.66663 6.99999" stroke="currentColor"
                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
            </div>
            <div class="fct-qty-inline-stock">
                <?php $this->renderStockAvailability('class="fct-inline-stock"'); ?>
            </div>
        </div>
        <?php
        do_action('fluent_cart/product/single/after_quantity_block', [
            'product' => $this->product,
            'scope'   => 'product_quantity_block'
        ]);
    }

    protected function isAddToCartOutOfStock(): bool
    {
        if (!ModuleSettings::isActive('stock_management')) {
            return false;
        }

        if ($this->product->detail->variation_type === 'simple' && $this->defaultVariant) {
            return $this->product->detail->manage_stock && $this->defaultVariant->stock_status !== Helper::IN_STOCK;
        }

        return false;
    }

    protected function renderOutOfStockMessage()
    {
        echo '<span aria-disabled="true">' . esc_html__('Out of stock', 'fluent-cart') . '</span>';
    }

    protected function getAddToCartButtonConfig($atts = []): array
    {
        $defaults = [
            'add_to_cart_text' => __('Add To Cart', 'fluent-cart'),
        ];

        $atts = wp_parse_args($atts, $defaults);

        $cartAttributes = [
            'data-fluent-cart-add-to-cart-button' => '',
            'data-cart-id'                        => $this->defaultVariant ? $this->defaultVariant->id : '',
            'data-product-id'                     => $this->product->ID,
            'class'                               => 'fluent-cart-add-to-cart-button fct-dominant-add-to-cart',
            'data-variation-type'                 => $this->product->detail->variation_type,
        ];

        $addToCartText = apply_filters('fluent_cart/product/add_to_cart_text', $atts['add_to_cart_text'], [
            'product' => $this->product
        ]);

        return [
            'cartAttributes' => $cartAttributes,
            'addToCartText'  => $addToCartText
        ];
    }

    protected function renderAddToCartButton(array $cartAttributes, string $addToCartText)
    {
        ?>
        <button <?php $this->renderAttributes($cartAttributes); ?> aria-label="<?php echo esc_attr($addToCartText); ?>">
            <span class="fct-add-to-cart-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 6H21L19 14H9L7 4H3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="10" cy="19" r="1.2" fill="currentColor" />
                    <circle cx="18" cy="19" r="1.2" fill="currentColor" />
                </svg>
            </span>
            <span class="text">
                <?php echo wp_kses_post($addToCartText); ?>
            </span>
            <span class="fluent-cart-loader" role="status">
                <svg aria-hidden="true"
                     width="20"
                     height="20"
                     class="w-5 h-5 text-gray-200 animate-spin fill-blue-600"
                     viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                        fill="currentColor"/>
                    <path
                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.10071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                        fill="currentFill"/>
                </svg>
            </span>
        </button>
        <?php
    }

    public function renderPurchaseButtons($atts = [])
    {
        if ($this->isAddToCartOutOfStock()) {
            $this->renderOutOfStockMessage();
            return;
        }

        $defaults = [
            'buy_now_text'     => __('Buy Now', 'fluent-cart'),
            'add_to_cart_text' => __('Add To Cart', 'fluent-cart'),
        ];

        $atts = wp_parse_args($atts, $defaults);

        $buyNowAttributes = [
            'data-fluent-cart-direct-checkout-button' => '',
            'data-variation-type'                     => $this->product->detail->variation_type,
            'class'                                   => 'fluent-cart-direct-checkout-button',
            'data-stock-availability'                 => 'in-stock',
            'data-quantity'                           => '1',
            'href'                                    => site_url('?fluent-cart=instant_checkout&item_id=') . ($this->defaultVariant ? $this->defaultVariant->id : '') . '&quantity=1',
            'data-cart-id'                            => $this->defaultVariant ? $this->defaultVariant->id : '',
            'data-url'                                => site_url('?fluent-cart=instant_checkout&item_id='),
        ];

        $cartButtonConfig = $this->getAddToCartButtonConfig($atts);

        $buyButtonText = apply_filters('fluent_cart/product/buy_now_button_text', $atts['buy_now_text'], [
            'product' => $this->product
        ]);
        ?>
        <a <?php $this->renderAttributes($buyNowAttributes); ?> aria-label="<?php echo esc_attr($buyButtonText); ?>">
            <?php echo wp_kses_post($buyButtonText); ?>
        </a>
        <?php if ($this->hasOnetime || $this->hasSubscription): ?>
            <?php $this->renderAddToCartButton($cartButtonConfig['cartAttributes'], $cartButtonConfig['addToCartText']); ?>
            <?php $this->renderPaymentIcons(); ?>
        <?php endif; ?>
        <?php
    }

    protected function renderPaymentIcons()
    {
        $icons = ['f1.png', 'f2.png', 'f3.png', 'f4.png', 'f5.png'];
        ?>
        <div class="fct-payment-icons" aria-label="<?php esc_attr_e('Accepted payment methods', 'fluent-cart'); ?>">
            <?php foreach ($icons as $icon): ?>
                <span class="fct-payment-icon" aria-hidden="true">
                    <img
                        src="<?php echo esc_url(Vite::getAssetUrl('images/' . $icon)); ?>"
                        alt="<?php esc_attr_e('Payment method', 'fluent-cart'); ?>"
                        width="64.98"
                        height="43.82"
                    >
                </span>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public static function renderNoProductFound()
    {
        ?>
        <div class="fluent-cart-shop-no-result-found" data-fluent-cart-shop-no-result-found role="status"
             aria-live="polite">
            <p class="has-text-align-center has-large-font-size m-0">
                <?php echo esc_html__('No Product Found!', 'fluent-cart'); ?>
            </p>

            <p class="has-text-align-center">
                <?php echo esc_html__('You can try clearing any filters.', 'fluent-cart'); ?>
            </p>
        </div>
        <?php
    }

    protected function renderAssurances()
    {
        ?>
        <div class="fct-product-assurances"
             aria-label="<?php echo esc_attr__('Purchase assurances', 'fluent-cart'); ?>">
            <div class="fct-product-assurance-item">
                <div class="fct-product-assurance-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.00008 12.0001L4.66675 8.66675" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.00008 12.0001L16.0001 4.00008" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.99997 18.3333C14.6023 18.3333 18.3333 14.6023 18.3333 9.99996C18.3333 5.39759 14.6023 1.66663 9.99997 1.66663C5.39759 1.66663 1.66663 5.39759 1.66663 9.99996C1.66663 14.6023 5.39759 18.3333 9.99997 18.3333Z"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="fct-product-assurance-copy">
                    <p class="title"><?php esc_html_e('Fast & Reliable Delivery', 'fluent-cart'); ?></p>
                    <p class="subtitle"><?php esc_html_e('Your service or assets are delivered quickly', 'fluent-cart'); ?></p>
                </div>
            </div>

            <div class="fct-product-assurance-item">
                <div class="fct-product-assurance-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3.33337C6.31811 3.33337 3.33331 6.31816 3.33331 10C3.33331 13.6819 6.31811 16.6667 10 16.6667C13.6819 16.6667 16.6666 13.6819 16.6666 10C16.6666 6.31816 13.6819 3.33337 10 3.33337Z"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 6.66663V10L12.5 11.6666" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="fct-product-assurance-copy">
                    <p class="title"><?php esc_html_e('Hassle-Free Revisions', 'fluent-cart'); ?></p>
                    <p class="subtitle"><?php esc_html_e('Need adjustments? We fix it without extra complexity', 'fluent-cart'); ?></p>
                </div>
            </div>

            <div class="fct-product-assurance-item">
                <div class="fct-product-assurance-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.33331 9.16667H8.34165" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round"/>
                        <path d="M11.6667 9.16667H11.675" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round"/>
                        <path d="M7.5 11.6667C8.03544 12.2018 8.75347 12.5 9.5 12.5C10.2465 12.5 10.9646 12.2018 11.5 11.6667"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 1.66663C5.39764 1.66663 1.66669 5.39759 1.66669 9.99996C1.66669 14.6023 5.39764 18.3333 10 18.3333C14.6024 18.3333 18.3334 14.6023 18.3334 9.99996C18.3334 5.39759 14.6024 1.66663 10 1.66663Z"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 1.66663C8.15818 3.65538 7.15265 6.25526 7.22228 8.93027C7.29191 11.6053 8.43043 14.1412 10.4167 16.0416"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="fct-product-assurance-copy">
                    <p class="title"><?php esc_html_e('Buyer Protection', 'fluent-cart'); ?></p>
                    <p class="subtitle"><?php esc_html_e('You’re fully protected from order to delivery — guaranteed.', 'fluent-cart'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    protected function renderPerksRow()
    {
        ?>
        <div class="fct-product-perks" role="list">
            <div class="fct-product-perk" role="listitem">
                <span class="fct-product-perk__icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 7.5H15.5V16.5H3V7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.5 10H19L21 12V16.5H15.5V10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="title"><?php esc_html_e('Instant Delivery', 'fluent-cart'); ?></span>
            </div>
            <div class="fct-product-perk" role="listitem">
                <span class="fct-product-perk__icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.75 7.5H6.5C5.119 7.5 4 8.619 4 10V18.5C4 19.881 5.119 21 6.5 21H17.5C18.881 21 20 19.881 20 18.5V10C20 8.619 18.881 7.5 17.5 7.5H17.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 8C8 5.791 9.791 4 12 4C14.209 4 16 5.791 16 8V9C16 11.209 14.209 13 12 13C9.791 13 8 11.209 8 9V8Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </span>
                <span class="title"><?php esc_html_e('100% Money-Back Guarantee', 'fluent-cart'); ?></span>
            </div>
        </div>
        <?php
    }

    protected function renderVariationItem(ProductVariation $variant, $defaultId = '', $extraClasses = [])
    {
        $availableStocks = $variant->available;
        if (!$variant->manage_stock) {
            $availableStocks = 'unlimited';
        }

        $rawItemPrice      = $variant->item_price;
        $rawComparePrice   = $variant->compare_price;
        $formattedItemPrice = Helper::toDecimal($rawItemPrice);

        if ($rawComparePrice <= $rawItemPrice) {
            $rawComparePrice = 0;
        }

        $formattedComparePrice = $rawComparePrice ? Helper::toDecimal($rawComparePrice) : '';
        $savedAmount           = $rawComparePrice ? $rawComparePrice - $rawItemPrice : 0;
        $savingsPercent        = $rawComparePrice ? round((($rawComparePrice - $rawItemPrice) / $rawComparePrice) * 100) : 0;
        $savingsText           = $savingsPercent
            ? sprintf(__('You save %1$s%% (%2$s)', 'fluent-cart'), $savingsPercent, Helper::toDecimal($savedAmount))
            : '';

        $paymentType = Arr::get($variant->other_info, 'payment_type');

        $itemClasses = [
            'fct-product-variant-item',
            'fct_price_type_' . $paymentType,
            'fct_variation_view_type_' . $this->viewType,
        ];

        if ($variant->media_id) {
            $itemClasses[] = 'fct-item-has-image';
        }

        if ($variant->id == $defaultId) {
            $itemClasses[] = 'selected';
        }

        $priceSuffix = apply_filters('fluent_cart/product/price_suffix_atts', '', [
            'product' => $this->product,
            'variant' => $variant,
            'scope'   => 'variant_item'
        ]);

        $renderingAttributes = [
            'data-fluent-cart-product-variant' => '',
            'data-cart-id'                     => $variant->id,
            'data-item-stock'                  => $variant->stock_status,
            'data-default-variation-id'        => $defaultId,
            'data-payment-type'                => $paymentType,
            'data-available-stock'             => $availableStocks,
            'data-item-price'                  => $formattedItemPrice,
            'data-item-price-raw'              => $rawItemPrice,
            'data-compare-price'               => $formattedComparePrice,
            'data-compare-price-raw'           => $rawComparePrice,
            'data-price-suffix'                => $priceSuffix,
            'data-savings-percent'             => $savingsPercent,
            'data-saved-amount'                => $savedAmount ? Helper::toDecimal($savedAmount) : '',
            'data-savings-text'                => $savingsText,
            'data-stock-management'            => ModuleSettings::isActive('stock_management') ? 'yes' : 'no',
        ];

        if ($paymentType === 'subscription') {
            $renderingAttributes['data-subscription-terms'] = $variant->getSubscriptionTermsText(true);
            $repeatInterval = Arr::get($variant->other_info, 'repeat_interval', '');
            $hasInstallment = Arr::get($variant->other_info, 'has_installment') === 'yes';

            $itemClasses[] = 'fct_sub_interval_' . $repeatInterval;
            if ($hasInstallment) {
                $itemClasses[] = 'fct_sub_has_installment';
            }
        }

        if ($extraClasses) {
            $itemClasses = array_merge($itemClasses, $extraClasses);
        }

        $itemClasses = array_filter($itemClasses);
        $renderingAttributes['class'] = implode(' ', $itemClasses);

        $itemPrice = $rawItemPrice;
        $comparePrice = $rawComparePrice;

        ?>
        <div
            <?php $this->renderAttributes($renderingAttributes); ?>
            role="radio"
            tabindex="0"
            aria-checked="<?php echo $variant->id == $defaultId ? 'true' : 'false'; ?>"
            aria-label="<?php echo esc_attr($variant->variation_title); ?>"
        >
            <?php if ($this->viewType === 'image'): ?>
                <?php $this->renderTooltip($variant); ?>
            <?php endif; ?>

            <div class="variant-content">
                <?php
                if ($this->viewType === 'both' || $this->viewType === 'image') {
                    $this->renderVariantImage($variant);
                }
                if ($this->viewType === 'both' || $this->viewType === 'text') {
                    echo '<div class="fct-product-variant-title" aria-label="' . esc_attr(__('Variant title', 'fluent-cart')) . '">' . esc_html($variant->variation_title) . '</div>';
                }
                ?>
            </div>

            <?php if ($this->viewType === 'text' && $paymentType === 'subscription' && $this->columnType === 'one'): ?>
                <?php $this->renderSubscriptionInfo($variant); ?>
            <?php endif; ?>

            <?php if ($this->viewType === 'text' && $this->columnType === 'one'): ?>
                <div class="fct-product-variant-price">
                    <?php if ($comparePrice): ?>
                        <div class="fct-product-variant-compare-price">
                            <del aria-label="<?php echo esc_attr(__('Original price', 'fluent-cart')); ?>">
                                <span><?php echo esc_html(Helper::toDecimal($comparePrice)); ?></span></del>
                        </div>
                    <?php endif; ?>
                    <div class="fct-product-variant-item-price"
                         aria-label="<?php echo esc_attr(__('Current price', 'fluent-cart')); ?>">
                        <span><?php echo esc_html(Helper::toDecimal($itemPrice)); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function renderTooltip($variant)
    {
        ?>
        <div class="fct-product-variant-tooltip" role="tooltip" id="tooltip-<?php echo esc_attr($variant->id); ?>">
            <?php echo esc_html($variant->variation_title); ?>
        </div>
        <?php
    }

    protected function renderVariantImage($variant)
    {
        $image = $variant->thumbnail;
        if (!$image) {
            $image = Vite::getAssetUrl('images/placeholder.svg');
        }
        ?>
        <div class="fct-product-variant-image">
            <img role="img" alt="<?php echo esc_attr($variant->variation_title); ?>"
                 src="<?php echo esc_url($image); ?>"/>
        </div>
        <?php
    }

    protected function renderSubscriptionInfo($variant)
    {
        $info = $variant->getSubscriptionTermsText(true);

        if (!$info) {
            return '';
        }

        ?>
        <div class="fct-product-variant-payment-type" aria-live="polite">
            <div class="additional-info">
                <span><?php echo esc_html($info); ?></span>
            </div>
        </div>
        <?php
    }

    protected function renderAttributes($atts = [])
    {
        foreach ($atts as $attr => $value) {
            if ($value !== '') {
                echo esc_attr($attr) . '="' . esc_attr((string)$value) . '" ';
            } else {
                echo esc_attr($attr) . ' ';
            }
        }
    }

    protected function renderTab($atts = [])
    {
        ?>
        <div class="fct-product-tab" data-fluent-cart-product-tab>
            <?php $this->renderTabNav(); ?>

            <div class="fct-product-tab-content" data-tab-contents>
                <?php $this->renderTabPane($atts); ?>
            </div>
        </div>
        <?php

    }

    protected function renderTabNav()
    {
        ?>

        <div class="fct-product-tab-nav" role="tablist">
            <div class="tab-active-bar" data-tab-active-bar></div>
            <?php
            foreach ($this->paymentTypes as $typeKey => $typeLabel) : ?>
                <div
                    class="fct-product-tab-nav-item <?php echo esc_attr($this->activeTab === $typeKey ? 'active' : ''); ?>"
                    data-tab="<?php echo esc_attr($typeKey); ?>"
                    role="tab"
                    tabindex="0"
                    aria-selected="<?php echo $this->activeTab === $typeKey ? 'true' : 'false'; ?>"
                    aria-controls="<?php echo esc_attr($typeKey); ?>"
                >
                    <?php echo esc_html($typeLabel); ?>
                </div>
            <?php endforeach;
            ?>
        </div>

        <?php
    }

    protected function renderTabPane($atts = [])
    {
        $variantsClasses = [
            'fct-product-variants',
            'column-type-' . $this->columnType,
            Arr::get($atts, 'wrapper_class', ''),
        ];

        foreach ($this->variantsByPaymentTypes as $variantKey => $variants): ?>
            <div
                data-tab-content
                id="<?php echo esc_attr($variantKey); ?>"
                class="fct-product-tab-pane <?php echo esc_attr($this->activeTab === $variantKey ? 'active' : ''); ?>"
                role="tabpanel"
                aria-labelledby="<?php echo esc_attr($variantKey); ?>"
            >
                <div class="<?php echo esc_attr(implode(' ', $variantsClasses)); ?>">
                    <?php
                    $variants = (new Collection($variants))->sortBy('serial_index')->values();

                    foreach ($variants as $variant) {
                        do_action('fluent_cart/product/single/before_variant_item', [
                            'product' => $this->product,
                            'variant' => $variant,
                            'scope'   => 'product_variant_item'
                        ]);

                        $this->renderVariationItem($variant, $this->defaultVariationId);

                        do_action('fluent_cart/product/single/after_variant_item', [
                            'product' => $this->product,
                            'variant' => $variant,
                            'scope'   => 'product_variant_item'
                        ]);
                    }
                    ?>
                </div>

            </div>
        <?php endforeach; ?>

        <?php
    }

    protected function getDefaultVariantData()
    {
        if (empty($this->variants) || !$this->defaultVariationId) {
            return null;
        }

        foreach ($this->variants as $variant) {
            if ($variant['id'] == $this->defaultVariationId) {
                return $variant;
            }
        }

        return null;
    }
}
