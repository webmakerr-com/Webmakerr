<?php

namespace Webmakerr\App\Hooks\Handlers\BlockEditors;


use Webmakerr\App\Services\Translations\TransStrings;
use Webmakerr\App\Hooks\Handlers\ShortCodes\Checkout\CheckoutPageHandler;
use Webmakerr\Framework\Support\Arr;


class CheckoutBlockEditor extends BlockEditor
{
    protected static string $editorName = 'checkout';

    protected function getScripts(): array
    {
        return [
            [
                'source'       => 'admin/BlockEditor/Checkout/CheckoutBlockEditor.jsx',
                'dependencies' => ['wp-blocks', 'wp-components']
            ]
        ];
    }

    protected function getStyles(): array
    {
        return ['admin/BlockEditor/Checkout/style/checkout-block-editor.scss'];
    }

    protected function localizeData(): array
    {

        return [
            $this->getLocalizationKey()     => [
                'slug'        => $this->slugPrefix,
                'name'        => static::getEditorName(),
                'title'       => __('Checkout Page', 'webmakerr-cart'),
                'description' => __('Render the Webmakerr checkout experience within the block editor.', 'webmakerr-cart')
            ],
            'fluent_cart_block_translation' => TransStrings::blockStrings()
        ];
    }

    public function render(array $shortCodeAttribute, $block = null): string
    {
        $attributes = $block ? $block->attributes : [];

        $cssClasses = [
            Arr::get($attributes, 'className', '')
        ];

        $align = Arr::get($attributes, 'align', '');
        if ($align) {
            $cssClasses[] = 'fct_has_align align' . $align;
        }

        webmakerr_add_filter('webmakerr_cart/checkout_page_css_classes', function ($classes) use ($cssClasses) {
            if(!is_array($cssClasses)) {
                $cssClasses = [];
            }

            if (empty($cssClasses)) {
                return $classes;
            }
            foreach ($cssClasses as $class) {
                $classes[] = $class;
            }
            return $classes;
        });

        return '[fluent_cart_checkout]';
    }

    /**
     * Returns the default `addressModal`
     *
     * @return array
     */
    public static function getDefaultAddressModal(): array
    {
        return [
            'billingAddress'   => __('Billing Address', 'webmakerr-cart'),
            'shippingAddress'  => __('Shipping Address', 'webmakerr-cart'),
            'openButtonText'   => __('Change', 'webmakerr-cart'),
            'addButtonText'    => __('Add Address', 'webmakerr-cart'),
            'applyButtonText'  => __('Apply', 'webmakerr-cart'),
            'submitButtonText' => __('Submit', 'webmakerr-cart'),
            'cancelButtonText' => __('Cancel', 'webmakerr-cart')
        ];
    }

    /**
     * Returns the default `SippingMethods`
     *
     * @return array
     */
    public static function getDefaultSippingMethods(): array
    {
        return [
            'heading' => __('Shipping Method', 'webmakerr-cart')
        ];
    }

    /**
     * Returns the default `PaymentMethods`
     *
     * @return array
     */
    public static function getDefaultPaymentMethods(): array
    {
        return [
            'heading' => __('Payment', 'webmakerr-cart')
        ];
    }

    /**
     * Returns the default `orderSummary`
     *
     * @return array
     */
    public static function getDefaultOrderSummary(): array
    {
        return [
            'toggleButtonText' => __('View Items', 'webmakerr-cart'),
            'removeButtonText' => __('Remove', 'webmakerr-cart'),
            'totalText'        => __('Total', 'webmakerr-cart'),
            'heading'          => __('Summary', 'webmakerr-cart'),
            'maxVisibleItems'  => 2,
            'showRemoveButton' => true,
            'coupons'          => self::getDefaultCoupons()
        ];
    }

    /**
     * Returns the default `coupons`
     *
     * @return array
     */
    public static function getDefaultCoupons(): array
    {
        return [
            'iconVisibility' => true,
            'placeholder'    => __('Apply Here', 'webmakerr-cart'),
            'applyButton'    => __('Apply', 'webmakerr-cart'),
            'label'          => __('Have a Coupon?', 'webmakerr-cart'),
            'collapsible'    => true
        ];
    }

    /**
     * Returns the default `submitButton`
     *
     * @return array
     */
    public static function getDefaultSubmitButton(): array
    {
        return [
            'text'      => __('Place Order', 'webmakerr-cart'),
            'alignment' => 'left',
            'size'      => 'large',
            'full'      => true
        ];
    }

    /**
     * Returns the default `AllowCreateAccount`
     *
     * @return array
     */
    public static function getDefaultAllowCreateAccount(): array
    {
        return [
            'label'    => __('Create my user account', 'webmakerr-cart'),
            'infoText' => __('By checking this box, you agree to create an account with us to manage your subscription and order details. This is mandatory for subscription-based purchases.', 'webmakerr-cart')
        ];
    }

    /**
     * Safely encode JSON strings if needed
     *
     * @param mixed $value The value to process
     * @return mixed The processed value (encoded if it was an array or object)
     */
    protected function maybeEncodeJson($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        return $value;
    }

}