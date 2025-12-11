<?php

namespace Webmakerr\App\Helpers;

use Webmakerr\Api\ModuleSettings;
use Webmakerr\App\App;

class EditorShortCodeHelper
{
    public static function getGeneralShortCodes(): array
    {
        return [
            'title'      => __('General', 'webmakerr-cart'),
            'key'        => 'wp',
            'shortcodes' => [
                '{{wp.admin_email}}'    => __('Admin Email', 'webmakerr-cart'),
                '{{wp.site_url}}'       => __('Site URL', 'webmakerr-cart'),
                '{{wp.site_title}}'     => __('Site Title', 'webmakerr-cart'),
                '{{user.ID}}'           => __('User ID', 'webmakerr-cart'),
                '{{user.display_name}}' => __('User Display Name', 'webmakerr-cart'),
                '{{user.first_name}}'   => __('User First Name', 'webmakerr-cart'),
                '{{user.last_name}}'    => __('User Last Name', 'webmakerr-cart'),
                '{{user.user_email}}'   => __('User Email', 'webmakerr-cart'),
                '{{user.user_login}}'   => __('User Username', 'webmakerr-cart')
            ],
        ];
    }

    public static function getSettingsShortCodes(): array
    {
        return [
            'title'      => __('Settings', 'webmakerr-cart'),
            'key'        => 'settings',
            'shortcodes' => [
                '{{settings.store_name}}'     => __('Store Name', 'webmakerr-cart'),
                '{{settings.store_logo}}'     => __('Store Logo', 'webmakerr-cart'),
                '{{settings.store_address}}'  => __('Store Address Line 1', 'webmakerr-cart'),
                '{{settings.store_address2}}' => __('Store Address Line 2', 'webmakerr-cart'),
                '{{settings.store_country}}'  => __('Store Country', 'webmakerr-cart'),
                '{{settings.store_state}}'    => __('Store State', 'webmakerr-cart'),
                '{{settings.store_city}}'     => __('Store City', 'webmakerr-cart'),
                '{{settings.store_postcode}}' => __('Store Postcode', 'webmakerr-cart'),
            ],
            'group'      => 'settings'
        ];
    }


    static function collectShortcodeFromNestedTextFields($array, $prefix = '')
    {
        $text_fields = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (isset($value['type']) && $value['type'] === 'text' && isset($value['label'])) {
                    $label = isset($value['label']) && $value['label']
                        ? $value['label']
                        : ucwords(str_replace('_', ' ', $key));

                    $text_fields['{{' . $prefix . '.' . $key . '}}'] = $label;
                } elseif (isset($value['schema'])) {
                    $text_fields = array_merge($text_fields, static::collectShortcodeFromNestedTextFields($value['schema'], $prefix));
                }
            }
        }

        return $text_fields;
    }

    /**
     * Make shortCodes from array
     * @array  array [ key => [ ... arguments] ]
     * @parentKey string will add to the key of array label
     * @type $type string 'keyPair', will only return a key and label (string) array
     * @return array array of shortCodes Exm: [ {{my.shortcode}} => 'My Shortcode' ]
     */
    public static function makeShortCodes(array $array, $parentKey = '', $type = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $parentKey ? $parentKey . '.' . $key : $key;
            if (isset($value['label'])) {
                $title = $value['label'] . ' (' . $parentKey . ')';
                $code = '{{' . $newKey . '}}';
                if ($type === 'keyPair') {
                    $result[$code] = $title;
                } else {
                    $value['attributes'] = [
                        'code' => '{{' . $newKey . '}}',
                        'type' => $value['data-type'],
                        'name' => $value['label'] . ' (' . $parentKey . ')'
                    ];
                    $result[$newKey] = $value;
                }
            }

        }

        return $result;
    }

    public static function checkoutInputs(): array
    {
        $cartCheckoutHelper = CartCheckoutHelper::make();

        return array_merge(
            static::makeShortCodes($cartCheckoutHelper->getBillingAddressFields(), 'billing'),
            static::makeShortCodes($cartCheckoutHelper->getShippingAddressFields(), 'shipping')
        );
    }

    public static function conditionalInputs()
    {

    }

    public static function getCustomerShortCodesForOrder(): array
    {
        $cartCheckoutHelper = CartCheckoutHelper::make();
        $billingFields = $cartCheckoutHelper->getBillingAddressFields();

        $shippingFields = $cartCheckoutHelper->getShippingAddressFields();

        // $shortCodes = array_merge(
        //     static::collectShortcodeFromNestedTextFields($billingFields, 'billing'),
        //     static::collectShortcodeFromNestedTextFields($shippingFields, 'shipping')
        // );

        $shortCodes = array_merge(
            static::collectShortcodeFromNestedTextFields($billingFields, 'order.billing'),
            [
                '{{order.billing.city}}'      => __('City', 'webmakerr-cart'),
                '{{order.billing.state}}'     => __('State', 'webmakerr-cart'),
                '{{order.billing.postcode}}'  => __('Postcode', 'webmakerr-cart'),
                '{{order.billing.country}}'   => __('Country', 'webmakerr-cart'),
                '{{order.billing.address_1}}' => __('Address Line 1', 'webmakerr-cart'),
                '{{order.billing.address_2}}' => __('Address Line 2', 'webmakerr-cart'),
            ],
            static::collectShortcodeFromNestedTextFields($shippingFields, 'order.shipping'),
            [
                '{{order.shipping.city}}'      => __('City', 'webmakerr-cart'),
                '{{order.shipping.state}}'     => __('State', 'webmakerr-cart'),
                '{{order.shipping.postcode}}'  => __('Postcode', 'webmakerr-cart'),
                '{{order.shipping.country}}'   => __('Country', 'webmakerr-cart'),
                '{{order.shipping.address_1}}' => __('Address Line 1', 'webmakerr-cart'),
                '{{order.shipping.address_2}}' => __('Address Line 2', 'webmakerr-cart'),
            ]
        );


        return [
            'key' => 'customer',
            'title'      => 'Customer',
            'shortcodes' => $shortCodes
        ];
    }

    public static function getPaymentShortCodes(): array
    {
        $orderProperties = [

        ];

        return [
            'key' => 'payment',
            'title'      => 'Payments',
            'shortcodes' => $orderProperties,
        ];
    }

    public static function getTransactionShortCodes(): array
    {
        $orderProperties = [
            '{{transaction.total}}'          => __('Total Amount', 'webmakerr-cart'),
            '{{transaction.refund_amount}}'  => __('Refund Amount', 'webmakerr-cart'),
            '{{transaction.payment_method}}' => __('Payment Method', 'webmakerr-cart'),
            '{{transaction.card_last_4}}'    => __('Card Last 4', 'webmakerr-cart'),
            '{{transaction.card_brand}}'     => __('Card Brand', 'webmakerr-cart'),
            '{{transaction.status}}'         => __('Status', 'webmakerr-cart'),
            '{{transaction.currency}}'       => __('Currency', 'webmakerr-cart'),
        ];

        return [
            'title'      => 'transaction',
            'key'        => 'settings',
            'shortcodes' => $orderProperties,
        ];
    }

    public static function getShortCodes(): array
    {
        $groups = [
            static::getCustomerShortCodesForOrder(),
            static::getOrderShortCodes(),
            static::getGeneralShortCodes(),
            static::getSettingsShortCodes(),
        ];

        $groups = webmakerr_apply_filters('webmakerr_cart/confirmation_shortcodes', $groups, []);

        $data = [
            'data' => $groups
        ];

        return $data;
    }

    public static function getOrderShortCodes(): array
    {
        return [
            'title'      => __('Order', 'webmakerr-cart'),
            'key'        => 'order',
            'shortcodes' => [
                '{{order.id}}'                      => __('Order ID', 'webmakerr-cart'),
                '{{order.customer_dashboard_link}}' => __('Customer Dashboard Link', 'webmakerr-cart'),
                '{{order.status}}'                  => __('Order Status', 'webmakerr-cart'),
                '{{order.parent_id}}'               => __('Order Parent Id', 'webmakerr-cart'),
                '{{order.invoice_no}}'              => __('Order Number', 'webmakerr-cart'),
                '{{order.fulfillment_type}}'        => __('Order Fulfillment Type', 'webmakerr-cart'),
                '{{order.type}}'                    => __('Order Type', 'webmakerr-cart'),
                '{{order.customer_id}}'             => __('Order Customer Id', 'webmakerr-cart'),
                '{{order.payment_method}}'          => __('Order Payment Method', 'webmakerr-cart'),
                '{{order.payment_method_title}}'    => __('Order Payment Method Title', 'webmakerr-cart'),
                '{{order.payment_status}}'          => __('Order Payment Status', 'webmakerr-cart'),
                '{{order.currency}}'                => __('Order Currency', 'webmakerr-cart'),
                '{{order.subtotal}}'                => __('Order Subtotal', 'webmakerr-cart'),
                '{{order.discount_tax}}'            => __('Order Discount Tax', 'webmakerr-cart'),
                '{{order.discount_total}}'          => __('Order Discount Total', 'webmakerr-cart'),
                '{{order.shipping_tax}}'            => __('Order Shipping Tax', 'webmakerr-cart'),
                '{{order.shipping_total}}'          => __('Order Shipping Total', 'webmakerr-cart'),
                '{{order.tax_total}}'               => __('Order Tax Total', 'webmakerr-cart'),
                '{{order.total_amount}}'            => __('Order Total Amount', 'webmakerr-cart'),
                '{{order.total_paid}}'              => __('Order Total Paid', 'webmakerr-cart'),
                '{{order.rate}}'                    => __('Order Rate', 'webmakerr-cart'),
                '{{order.note}}'                    => __('Order Note', 'webmakerr-cart'),
                '{{order.ip_address}}'              => __('Order Ip Address', 'webmakerr-cart'),
                '{{order.completed_at}}'            => __('Order Completed At', 'webmakerr-cart'),
                '{{order.refunded_at}}'             => __('Order Refunded At', 'webmakerr-cart'),
                '{{order.uuid}}'                    => __('Order UUID', 'webmakerr-cart'),
                '{{order.payment_receipt}}'         => __('Payment Receipt', 'webmakerr-cart'),
                '{{order.payment_summary}}'         => __('Payment Summary', 'webmakerr-cart'),
                '{{order.downloads}}'               => __('Order Downloads', 'webmakerr-cart'),
                '{{order.created_at}}'              => __('Order Create Date', 'webmakerr-cart'),
            ],
        ];
    }

    public static function getEmailNotificationShortcodes(): array
    {

        $shortCodes = [
            'order'       => static::getOrderShortCodes(),
            'general'     => static::getGeneralShortCodes(),
            'customer'    => static::getCustomerShortCodesForOrder(),
            'transaction' => static::getTransactionShortCodes(),
            'settings'    => static::getSettingsShortCodes()
        ];
        return webmakerr_apply_filters('webmakerr_cart/editor_shortcodes', $shortCodes);
    }


    public static function getEmailSettingsShortcodes(): array
    {
        return [
            static::getGeneralShortCodes(),
            static::getSettingsShortCodes()
        ];
    }

    public static function getButtons()
    {
        $url = esc_url(site_url());
        return [
            'View Order' => '<a href="' . $url . '/wp-admin/admin.php?page=webmakerr#/orders/{{order.id}}/view" style="background-color: green; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Order</a>'
        ];
    }
}