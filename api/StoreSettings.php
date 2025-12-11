<?php

namespace Webmakerr\Api;

use Webmakerr\App\CPT\Pages;
use Webmakerr\App\Helpers\AddressHelper;
use Webmakerr\App\Helpers\CurrenciesHelper;
use Webmakerr\App\Services\OrderService;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Support\ArrayableInterface;
use Webmakerr\Framework\Support\Str;

class StoreSettings implements ArrayableInterface
{
    /**
     * @var string
     *
     * Store settings option name
     */
    protected string $optionKey = 'fluent_cart_store_settings';

    /**
     * @var array key value pair
     *
     * Store settings parsed from fields
     */
    protected array $storeSettings;

    protected static $cachedStoreSettings = null;

    public function __construct()
    {
        $defaultSettings = $this->getDefaultSettings();
        $storeSettings = get_option($this->optionKey, []);
        $settings = wp_parse_args($storeSettings, $defaultSettings);
        $this->storeSettings = $settings;
    }

    protected function getDefaultSettings(): array
    {
        $defaultSettings = [
            'store_name'                           => get_bloginfo('name'),
            'note_for_user_account_creation'       => __('An user account will be created', 'webmakerr-cart'),
            'checkout_button_text'                 => __('Checkout', 'webmakerr-cart'),
            'view_cart_button_text'                => __('View Cart', 'webmakerr-cart'),
            'cart_button_text'                     => __('Add To Cart', 'webmakerr-cart'),
            'popup_button_text'                    => __('View Product', 'webmakerr-cart'),
            'out_of_stock_button_text'             => __('Out of stock', 'webmakerr-cart'),
            'currency_position'                    => 'before',
            // 'thousand_separator'                   => 'comma',
            'decimal_separator'                    => 'dot',
            'checkout_method_style'                => 'logo',
            'require_logged_in'                    => 'no',
            'show_cart_icon_in_nav'                => 'no',
            'show_cart_icon_in_body'               => 'yes',
            'additional_address_field'             => 'yes',
            'hide_coupon_field'                    => 'no',
            'user_account_creation_mode'           => 'all',
            'checkout_page_id'                     => '',
            'custom_payment_page_id'               => '',
            'registration_page_id'                 => '',
            'login_page_id'                        => '',
            'cart_page_id'                         => '',
            'receipt_page_id'                      => '',
            'shop_page_id'                         => '',
            'customer_profile_page_id'             => '',
            'customer_profile_page_slug'           => '',
            'currency'                             => 'USD',
            'store_address1'                       => '',
            'store_address2'                       => '',
            'store_city'                           => '',
            'store_country'                        => '',
            'store_postcode'                       => '',
            'store_state'                          => '',
            'show_relevant_product_in_single_page' => 'yes',
            'show_relevant_product_in_modal'       => '',
            'order_mode'                           => 'test',
            'variation_view'                       => 'both',
            'variation_columns'                    => 'masonry',
            'modules_settings'                     => [],
            'min_receipt_number'                   => '1',
            'inv_prefix'                           => 'INV-'
        ];

        return webmakerr_apply_filters('webmakerr_cart/store_settings/values', $defaultSettings, []);
    }

    /**
     * @return array
     *
     * Get all store settings fields
     * @hook to use webmakerr_apply_filters("webmakerr_cart/store_setting_fields", $fields)
     */
    public function fields($params = []): array
    {

        $pages = Pages::getPages('');
        $previewLinks = [
            'shop_page_id'             => $this->getShopPage(),
            'customer_profile_page_id' => $this->getCustomerProfilePage(),
            'cart_page_id'             => $this->getCartPage(),
            'checkout_page_id'         => $this->getCheckoutPage(),
            'receipt_page_id'          => $this->getReceiptPage()
        ];


        $fields = [
            'setting_tabs' => [
                'type'            => 'section',
                'disable_nesting' => true,
                'default_tab'     => 'store_setup',
                'hide_tab_switch' => true,
                'schema'          => [
                    'store_setup'          => [
                        'title'           => __('Store Setup', 'webmakerr-cart'),
                        'type'            => 'section',
                        'disable_nesting' => true,
                        'columns'         => [
                            'default' => 1,
                            'md'      => 1
                        ],
                        'schema'          => [
                            'name_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'      => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Store Name', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __('Enter the public name of your online store.', 'webmakerr-cart') . '</div>'
                                    ],
                                    "store_name" => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => '',
                                        "type"         => "input",
                                        "value"        => "",
                                    ],
                                ]
                            ],

                            'hr' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],


                            'logo_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'      => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Store Logo', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Upload your brand's logo. Recommended width: 512 pixels minimum.", 'webmakerr-cart') . '</div>'
                                    ],
                                    "store_logo" => [
                                        "label"    => false,
                                        "type"     => "media",
                                        "value"    => "",
                                        'multiple' => false,
                                        // if `condition_type` not specified then all condition type will be `and`

                                        // 'conditions' => [
                                        //     [
                                        //         'key' => 'store_name',
                                        //         'operator' => '==',
                                        //         'value' => [
                                        //             'accessor' => 'order_mode'
                                        //         ]
                                        //     ],
                                        // ],

                                    ],
                                ]
                            ],

                            'hr2' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'mode_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'      => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Store Mode', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select your store's operating mode: `Test` for setup, `Live` for real transactions.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'order_mode' => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => '',
                                        "type"         => "radio",
                                        "options"      => [
                                            [
                                                "label" => __('Live', 'webmakerr-cart'),
                                                "value" => 'live',
                                            ],
                                            [
                                                "label" => __('Test', 'webmakerr-cart'),
                                                "value" => 'test',
                                            ],
                                        ],
                                        "value"        => "live"
                                    ],
                                ]
                            ],

                            'settings_hr' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'address_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'              => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Store Address', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Provide your physical business address details.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'address_input_grid' => [
                                        'wrapperClass'    => 'fct-compact-form',
                                        'type'            => 'grid',
                                        'disable_nesting' => true,
                                        'class'           => 'col-span-2',
                                        'schema'          => [
                                            'store_address_component' => [
                                                'type'         => 'component',
                                                'component'    => 'StoreSettings/AddressComponent',
                                                'wrapperClass' => 'col-span-full'
                                            ],
                                            'store_address1'          => [
                                                'type' => 'hidden',
                                            ],
                                            'store_address2'          => [
                                                'type' => 'hidden',
                                            ],
                                            'store_city'              => [
                                                'type' => 'hidden',
                                            ],
                                            'store_postcode'          => [
                                                'type' => 'hidden',
                                            ],
                                            'store_country'           => [
                                                'type' => 'hidden',
                                            ],
                                            'store_state'             => [
                                                'type' => 'hidden',
                                            ],
                                        ]
                                    ],
                                ]
                            ],


                            'settings_hr_2' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'currency_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'    => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Checkout Currency', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the primary currency for your store.", 'webmakerr-cart') . '</div>'
                                    ],
                                    "currency" => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => '',
                                        "type"         => "select",
                                        'filterable'   => true,
                                        "options"      => CurrencySettings::getFormattedCurrencies(),
                                        "value"        => "USD"
                                    ],
                                ]
                            ],

                            'hr3' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'decimal_separator_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'             => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Number Format', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the character used to separate thousands or decimals in prices.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'decimal_separator' => [
                                        'wrapperClass' => 'col-span-2 flex items-start flex-col',
                                        "label"        => '',
                                        "type"         => "radio",
                                        "options"      => [
                                            [
                                                "label" => __('Comma & Dot (eg 10,000.00)', 'webmakerr-cart'),
                                                "value" => 'dot'
                                            ],
                                            [
                                                "label" => __('Dot & Comma (eg 10.000,00)', 'webmakerr-cart'),
                                                "value" => 'comma'
                                            ],
                                        ],
                                        "value"        => "dot"
                                    ],
                                ]
                            ],

                            'hr4' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'currency_position_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'             => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Currency Formatting', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select how the currency should be formatted.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'currency_position' => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => '',
                                        "type"         => "select",
                                        "options"      => [
                                            [
                                                "label" => __('Symbol before (eg: $100)', 'webmakerr-cart'),
                                                "value" => 'before',
                                            ],
                                            [
                                                "label" => __('Symbol after (eg: 100$)', 'webmakerr-cart'),
                                                "value" => 'after',
                                            ],
                                            [
                                                "label" => __('ISO before (eg: USD 100)', 'webmakerr-cart'),
                                                "value" => 'iso_before',
                                            ],
                                            [
                                                "label" => __('ISO after (eg: 100 USD)', 'webmakerr-cart'),
                                                "value" => 'iso_after',
                                            ],
                                            [
                                                "label" => __('Symbol & ISO (eg: $100 USD)', 'webmakerr-cart'),
                                                "value" => 'symbool_before_iso',
                                            ],
                                            [
                                                "label" => __('ISO & Symbol (eg: USD 100$)', 'webmakerr-cart'),
                                                "value" => 'symbool_after_iso',
                                            ],
                                            [
                                                "label" => __('ISO-Symbol (eg: USD $100)', 'webmakerr-cart'),
                                                "value" => 'symbool_and_iso',
                                            ],
                                        ],
                                        "value"        => "before"
                                    ],
                                ]
                            ],

                            'hr5' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'checkout_style_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'                 => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Payment View', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select how payment options are visually presented on checkout.", 'webmakerr-cart') . '</div>'
                                    ],
                                    "checkout_method_style" => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "type"         => "component",
                                        'component'    => 'PaymentView',
                                        'label'        => false,
                                        "options"      => [
                                            [
                                                "label" => '',
                                                "value" => 'logo',
                                            ],
                                            [
                                                "label" => __('Label Selector', 'webmakerr-cart'),
                                                "value" => 'radio',
                                            ],
                                        ],
                                        "value"        => "logo"
                                    ],
                                ]
                            ],
                        ],
                    ],
//                    'button_setup'           => [
//                        'title'           => __('Button Setup', 'webmakerr-cart'),
//                        'type'            => 'tab-pane',
//                        'disable_nesting' => true,
//                        'schema'          => [
//                            'button_setup_settings' => [
//                                'title'           => __('Button Setup', 'webmakerr-cart'),
//                                'type'            => 'section',
//                                'disable_nesting' => true,
//                                'columns'         => [
//                                    'default' => 1,
//                                    'md'      => 2
//                                ],
//                                'schema'          => [
//                                    "checkout_button_text" => [
//                                        "label" => __('Checkout Button Text', 'webmakerr-cart'),
//                                        "type"  => "input",
//                                        "value" => __('Buy now', 'webmakerr-cart')
//                                    ],
//                                    "cart_button_text"     => [
//                                        "label" => __('Cart Button Text', 'webmakerr-cart'),
//                                        "type"  => "input",
//                                        "value" => __('Add to cart', 'webmakerr-cart')
//                                    ],
//
//                                    "popup_button_text"        => [
//                                        "label" => __('Popup Button Text', 'webmakerr-cart'),
//                                        "type"  => "input",
//                                        "value" => __('View Product', 'webmakerr-cart')
//                                    ],
//                                    "out_of_stock_button_text" => [
//                                        "label" => __('Out of Stock Button Text', 'webmakerr-cart'),
//                                        "type"  => "input",
//                                        "value" => __('Out of stock', 'webmakerr-cart')
//                                    ],
//                                    "view_cart_button_text"    => [
//                                        "label" => __('View Cart Button Text', 'webmakerr-cart'),
//                                        "type"  => "input",
//                                        "value" => __('View Cart', 'webmakerr-cart')
//                                    ],
//
//                                    "note_for_user_account_creation" => [
//                                        "label" => __('Note for User Account Creation,', 'webmakerr-cart'),
//                                        "type"  => "input",
//                                        "value" => __('You have subscription product in your cart, an account will be created', 'webmakerr-cart')
//                                    ],
//                                ],
//                            ]
//                        ]
//                    ],
                    'pages_setup'          => [
                        'title'           => __('Pages Setup', 'webmakerr-cart'),
                        'type'            => 'section',
                        'disable_nesting' => true,
                        'columns'         => [
                            'default' => 1,
                            'md'      => 1
                        ],
                        'schema'          => [

                            'shop_page_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3,
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'        => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Select Shop Page', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the page that showcases all of your products.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'shop_page_id' => [
                                        'wrapperClass' => 'col-span-2',
                                        'page_title'   => __('Shop', 'webmakerr-cart'),
                                        'type'         => 'component',
                                        'component'    => 'StoreSettings/PageSelector',
                                        'page_key'     => 'shop_page_id',
                                        'preview_link' => $previewLinks['shop_page_id'],
                                        'options'      => $pages,
                                        'hide_note'    => true,
                                        'value'        => '',
                                        'note'         => \Webmakerr\App\Helpers\Helper::getShortcodeInstructionString(
                                            '[webmakerr_products]',
                                            __('Products', 'webmakerr-cart')
                                        ),
                                    ],
                                ]
                            ],

                            'hr1' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'customer_profile_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'                    => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Select Customer Profile Page', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the page where customers will manage their profile, orders, and downloads.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'customer_profile_page_id' => [
                                        'wrapperClass' => 'col-span-2',
                                        'label'        => false,
                                        'page_title'   => __('Account', 'webmakerr-cart'),
                                        'type'         => 'component',
                                        'component'    => 'StoreSettings/PageSelector',
                                        'page_key'     => 'customer_profile_page_id',
                                        'preview_link' => $previewLinks['customer_profile_page_id'],
                                        'hide_note'    => true,
                                        'options'      => $pages,
                                        'value'        => '',
                                        'note'         => \Webmakerr\App\Helpers\Helper::getShortcodeInstructionString(
                                            '[webmakerr_customer_profile]',
                                            __('Account', 'webmakerr-cart')
                                        ),
                                    ],
                                ]
                            ],

                            'hr2' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'cart_page_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'        => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Select Cart Page', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the page that will display customer's current shopping cart.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'cart_page_id' => [
                                        'wrapperClass' => 'col-span-2',
                                        'label'        => false,
                                        'page_title'   => __('Cart', 'webmakerr-cart'),
                                        'type'         => 'component',
                                        'component'    => 'StoreSettings/PageSelector',
                                        'page_key'     => 'cart_page_id',
                                        'preview_link' => $previewLinks['cart_page_id'],
                                        'hide_note'    => true,
                                        'options'      => $pages,
                                        'value'        => '',
                                        'note'         => \Webmakerr\App\Helpers\Helper::getShortcodeInstructionString(
                                            '[webmakerr_cart]',
                                            __('Cart', 'webmakerr-cart')
                                        ),
                                    ],
                                ]
                            ],

                            'hr3' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'receipt_page_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'           => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Select Receipt Page', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the page that will display order summary after a successful purchase.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'receipt_page_id' => [
                                        'wrapperClass' => 'col-span-2',
                                        'label'        => false,
                                        'page_title'   => __('Receipt', 'webmakerr-cart'),
                                        'type'         => 'component',
                                        'component'    => 'StoreSettings/PageSelector',
                                        'page_key'     => 'receipt_page_id',
                                        'preview_link' => $previewLinks['receipt_page_id'],
                                        'hide_note'    => true,
                                        'options'      => $pages,
                                        'value'        => '',
                                        'note'         => \Webmakerr\App\Helpers\Helper::getShortcodeInstructionString(
                                            '[webmakerr_receipt]',
                                            __('Receipt', 'webmakerr-cart')
                                        ),
                                    ],
                                ]
                            ],

                            'hr4' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'checkout_page_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'            => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Select Checkout Page', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Select the page where customers will finalize their purchase.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'checkout_page_id' => [
                                        'wrapperClass' => 'col-span-2',
                                        'label'        => false,
                                        'page_title'   => __('Checkout', 'webmakerr-cart'),
                                        'type'         => 'component',
                                        'component'    => 'StoreSettings/PageSelector',
                                        'page_key'     => 'checkout_page_id',
                                        'preview_link' => $previewLinks['checkout_page_id'],
                                        'hide_note'    => true,
                                        'options'      => $pages,
                                        'value'        => '',
                                        'note'         => \Webmakerr\App\Helpers\Helper::getShortcodeInstructionString(
                                            '[webmakerr_checkout]',
                                            __('Checkout', 'webmakerr-cart')
                                        ),
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'single_product_setup' => [
                        'title'           => __('Product Page', 'webmakerr-cart'),
                        'type'            => 'section',
                        'disable_nesting' => true,
                        'columns'         => [
                            'default' => 1,
                            'md'      => 1
                        ],
                        'schema'          => [
                            'product_settings_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'  => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Single Product Setup', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Control the display of relevant information.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'fields' => [
                                        'type'            => 'grid',
                                        'columns'         => [
                                            'default' => 1,
                                            'md'      => 1
                                        ],
                                        'disable_nesting' => true,
                                        'schema'          => [
                                            "show_relevant_product_in_single_page" => [
                                                "label" => __('Show Relevant In Single Page', 'webmakerr-cart'),
                                                "type"  => "checkbox",
                                                "value" => "yes"
                                            ],
                                            "show_relevant_product_in_modal"       => [
                                                "label" => __('Show Relevant In Product Modal', 'webmakerr-cart'),
                                                "type"  => "checkbox",
                                                "value" => "no"
                                            ],
                                        ]
                                    ]

                                ]
                            ],
                            'hr'                    => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'image_zoom_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'  => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Image Zooming', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Enable Image zoom in single product.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'fields' => [
                                        'type'            => 'grid',
                                        'columns'         => [
                                            'default' => 1,
                                            'md'      => 1
                                        ],
                                        'disable_nesting' => true,
                                        'schema'          => [
                                            "enable_image_zoom_in_single_product" => [
                                                "label" => __('Enable Zoom in Single Product', 'webmakerr-cart'),
                                                "type"  => "checkbox",
                                                "value" => "no"
                                            ],
                                            "enable_image_zoom_in_modal"          => [
                                                "label" => __('Enable Zoom in Modal', 'webmakerr-cart'),
                                                "type"  => "checkbox",
                                                "value" => "no"
                                            ],
                                        ]
                                    ]

                                ]
                            ],

                            'hr_image_zoom' => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'variation_grid'        => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'          => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Variation View', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Defines how product variations are visually presented to customers.", 'webmakerr-cart') . '</div>'
                                    ],
                                    "variation_view" => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => false,
                                        "type"         => "select",
                                        "options"      => [
                                            [
                                                "label" => __('Image', 'webmakerr-cart'),
                                                "value" => 'image',
                                            ],
                                            [
                                                "label" => __('Text', 'webmakerr-cart'),
                                                "value" => 'text',
                                            ],
                                            [
                                                "label" => __('Image with Text', 'webmakerr-cart'),
                                                "value" => 'both',
                                            ],
                                        ],
                                        "value"        => ""
                                    ],

                                ]
                            ],
                            'hr2'                   => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],
                            'variation_column_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'             => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Variation Columns', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Set the column layout for how product variations are displayed within product sections.", 'webmakerr-cart') . '</div>'
                                    ],
                                    "variation_columns" => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => false,
                                        "type"         => "select",
                                        "options"      => [
                                            [
                                                "label" => __('One Column', 'webmakerr-cart'),
                                                "value" => 'one',
                                            ],
                                            [
                                                "label" => __('Two Columns', 'webmakerr-cart'),
                                                "value" => 'two',
                                            ],
                                            [
                                                "label" => __('Three Columns', 'webmakerr-cart'),
                                                "value" => 'three',
                                            ],
                                            [
                                                "label" => __('Four Columns', 'webmakerr-cart'),
                                                "value" => 'four',
                                            ],
                                            [
                                                "label" => __('Masonry', 'webmakerr-cart'),
                                                "value" => 'masonry',
                                            ],
                                        ],
                                        "value"        => ""
                                    ],

                                ]
                            ],

                            'hr3'               => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],
                            'product_slug_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'        => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Product Slug', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Set Product Slug.", 'webmakerr-cart') . '</div>'
                                    ],
                                    "product_slug" => [
                                        'wrapperClass' => 'col-span-2 flex items-center',
                                        "label"        => false,
                                        "type"         => "input",
                                        "value"        => ""
                                    ],

                                ]
                            ],
                        ],
                    ],
                    'cart_and_checkout'    => [
                        'title'           => __('Cart & checkout', 'webmakerr-cart'),
                        'type'            => 'section',
                        'disable_nesting' => true,
                        'columns'         => [
                            'default' => 1,
                            'md'      => 1
                        ],
                        'schema'          => [
                            "show_cart_icon_in_body" => [
                                "label" => __('Cart Icon In Body', 'webmakerr-cart'),
                                "type"  => "checkbox",
                                "value" => "yes",
                                'note'  => sprintf(
                                /* translators: %1$s: Display instruction text, %2$s: "Use this" text, %3$s: Copy to clipboard tooltip, %4$s: CSS class name, %5$s: CSS class description text */
                                    "<div class='pl-6'><p>%1\$s</p><p>%2\$s <code class='copyable-content' title='%3\$s'>%4\$s</code> %5\$s</p></div>",
                                    __('Display the cart icon in the body of the website', 'webmakerr-cart'),
                                    __('Use this', 'webmakerr-cart'),
                                    __('Copy to clipboard', 'webmakerr-cart'),
                                    '[webmakerr_toggle_button]',
                                    __('CSS class to display the cart link/icon anywhere else.', 'webmakerr-cart')
                                )
                            ],
                            'hr2'                    => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],
                            "require_logged_in"      => [
                                "label"        => __('Require user to be logged in for checkout (Coming soon)', 'webmakerr-cart'),
                                "type"         => "checkbox",
                                "value"        => "no",
                                'note'         => "<div class='pl-6'>" . __('Enforce that customers must be logged into their account to complete a purchase.', 'webmakerr-cart') . "</div>",
                                'wrapperClass' => 'disabled'
                            ],
                            'hr3'                    => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],

                            'user_account_creation_mode_grid' => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'                   => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('User Account Creation Mode', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("User account will be created regardless of the mode if the order has a subscription.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'user_account_input_grid' => [
                                        'type'            => 'grid',
                                        'disable_nesting' => true,
                                        'class'           => 'col-span-2',
                                        'schema'          => [
                                            'user_account_creation_mode' => [
                                                'wrapperClass' => 'col-span-2 flex items-center',
                                                "label"        => '',
                                                "type"         => "radio",
                                                "options"      => [
                                                    [
                                                        "label"        => __('Create User Account Automatically after payment
', 'webmakerr-cart'),
                                                        "value"        => 'all',
                                                        'note'         => "<div class='my-[2px] form-note'>" . __('User account will be created automatically after payment.', 'webmakerr-cart') . "</div>",
                                                        'option_class' => 'mb-2.5'
                                                    ],
                                                    [
                                                        "label"        => __('Give checkbox to create account on checkout page', 'webmakerr-cart'),
                                                        "value"        => 'user_choice',
                                                        'note'         => "<div class='my-[2px] form-note'>" . __('User will have an option to create an account during checkout.', 'webmakerr-cart') . "</div>",
                                                        'option_class' => 'mb-2.5'
                                                    ],
                                                    [
                                                        "label" => __('No need to create account for onetime purchases', 'webmakerr-cart'),
                                                        "value" => 'only_subscription',
                                                        'note'  => "<div class='my-[2px] form-note'>" . __('User account will not be created for onetime purchases.', 'webmakerr-cart') . "</div>",
                                                    ],
                                                ],
                                                "value"        => "all"
                                            ],
                                        ]
                                    ],
                                ]
                            ],


//                            "allow_checkout_signup"    => [
//                                "label" => __('Allow customers to create account during one-time checkout', 'webmakerr-cart'),
//                                "type"  => "checkbox",
//                                "value" => "no",
//                                'note'  => "<div class='pl-6'>" . __('Provide an option for customers to create a user account during a single, non-recurring checkout process.', 'webmakerr-cart') . "</div>"
//                            ],
//                    "force_ssl" => [
//                        "label" => __('Force page to SSL (https)', 'webmakerr-cart'),
//                        "type" => "checkbox",
//                        "value" => "no"
//                    ],
                            'hr5'                             => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],
                            "hide_coupon_field"               => [
                                "label" => __('Hide coupon field on checkout', 'webmakerr-cart'),
                                "type"  => "checkbox",
                                "value" => "no",
                                'note'  => "<div class='pl-6'>" . __('Hide the coupon code input field on the checkout page.', 'webmakerr-cart') . "</div>"
                            ],
                            'hr6'                             => [
                                'type'  => 'html',
                                'value' => '<hr class="settings-divider">'
                            ],
                            'order_invoice_grid'              => [
                                'type'            => 'grid',
                                'columns'         => [
                                    'default' => 1,
                                    'md'      => 3
                                ],
                                'disable_nesting' => true,
                                'schema'          => [
                                    'label'              => [
                                        'type'  => 'html',
                                        'value' => '<span class="setting-label">' . __('Receipt Settings', 'webmakerr-cart') . '</span>
                                                            <div class="form-note">' . __("Setup your receipt.", 'webmakerr-cart') . '</div>'
                                    ],
                                    'invoice_input_grid' => [
                                        'type'            => 'grid',
                                        'disable_nesting' => true,
                                        'class'           => 'col-span-2',
                                        'schema'          => [
                                            "min_receipt_number" => [
                                                'wrapperClass' => 'col-span-2 flex flex-col',
                                                "label"        => __('Minimum Receipt Number', 'webmakerr-cart'),
                                                "type"         => "input",
                                                "value"        => "",
                                                'note'         => sprintf(
                                                    "<div class=''>%s</div>",
                                                    sprintf(
                                                    /* translators: %s is the next receipt number */
                                                        __('Next Receipt Number: %s', 'webmakerr-cart'),
                                                        OrderService::getNextReceiptNumber()
                                                    )
                                                ),

                                            ],
                                            "inv_prefix"         => [
                                                'wrapperClass' => 'col-span-2 flex flex-col',
                                                "label"        => __('Receipt Prefix', 'webmakerr-cart'),
                                                "type"         => "input",
                                                "value"        => "",
                                            ],
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
        ];


        return webmakerr_apply_filters("webmakerr_cart/store_settings/fields", $fields, []);
    }

    public function isModuleTabEnabled(): bool
    {
        $fields = $this->fields();
        return isset($fields['setting_tabs']['schema']['modules_tab']);
    }

    /**
     * @param string|null $key like stripe or paypal
     * All store settings if key is not provided
     * @return mixed
     *
     */

    public function get($key = null, $default = null)
    {
        if (empty($key)) {
            return $this->storeSettings;
        }

        if (is_array($key)) {
            $data = Arr::only($this->storeSettings, $key);
        } else {
            $data = Arr::get($this->storeSettings, $key);
        }

        return empty($data) ? $default : $data;
    }

    public function moduleSettings(): array
    {
        $settings = $this->get('modules_settings', []);
        return Arr::wrap($settings);
    }

    /**
     * @param array $settings like Stripe or PayPal settings array
     * Save store settings
     * @return array
     *
     */
    public function save(array $settings)
    {
        $prevSettings = get_option($this->optionKey, []);
        $prevSettings = wp_parse_args($prevSettings, $this->getDefaultSettings());

        $settings = wp_parse_args($settings, $prevSettings);

        $customerProfilePageId = Arr::get($settings, 'customer_profile_page_id', 0);
        if ($customerProfilePageId) {
            $settings['customer_profile_page_slug'] = $this->getCustomerDashboardPageSlug($customerProfilePageId, false);
        }

        update_option($this->optionKey, $settings, true);
        $this->storeSettings = $settings;

        $isSlugChanged = Arr::get($prevSettings, 'product_slug') !== Arr::get($settings, 'product_slug');
        $isAccountPageChanged = Arr::get($prevSettings, 'customer_profile_page_slug') !== Arr::get($settings, 'customer_profile_page_slug');

        if ($isSlugChanged || $isAccountPageChanged) {
            flush_rewrite_rules();
            delete_option('rewrite_rules');
        }

        return $this->storeSettings;
    }

    public function set(string $key, $value)
    {
        return $this->save([
            $key => $value
        ]);
    }

    public function getCurrency()
    {
        return $this->get('currency', 'USD');
    }

    public function getCurrencySymbol()
    {
        $currency = $this->getCurrency();
        return CurrenciesHelper::getCurrencySign($currency);
    }

    public function isCheckoutPage(): bool
    {
        global $post;
        $pageId = $this->getCheckoutPageId();
        return intval($pageId) === intval($post->ID);
    }

    public function getCheckoutPageId()
    {
        return Arr::get($this->storeSettings, 'checkout_page_id');
    }

    public function getPagesSettings(): array
    {
        return Arr::only(
            $this->storeSettings,
            [
                'checkout_page_id',
                'custom_payment_page_id',
                'registration_page_id',
                'login_page_id',
                'cart_page_id',
                'receipt_page_id',
                'shop_page_id',
                'customer_profile_page_id',
                'customer_profile_page_slug',
            ]
        );
    }

    public function getCheckoutPage(): string
    {
        if ($pageID = $this->getCheckoutPageId()) {
            return $this->getPageLink($pageID);
        }

        return '';
    }


    public function getCustomerProfilePageId()
    {
        return Arr::get($this->storeSettings, 'customer_profile_page_id');
    }

    public function getCustomerProfilePage(): string
    {
        if ($pageId = $this->getCustomerProfilePageId()) {
            return $this->getPageLink($pageId);
        }
        return '';
    }


    public function getCartPageId()
    {
        return Arr::get($this->storeSettings, 'cart_page_id');
    }

    public function getCartPage(): string
    {
        if ($pageId = $this->getCartPageId()) {
            if (Pages::isPage($pageId)) {
                return $this->getPageLink($pageId);
            }
        }
        return '';
    }

    public function getShopPageId()
    {
        return Arr::get($this->storeSettings, 'shop_page_id');
    }

    public function getShopPage(): string
    {
        if ($pageId = $this->getShopPageId()) {
            if (Pages::isPage($pageId)) {
                return $this->getPageLink($pageId);
            }
        }

        return '';
    }

    public function getReceiptPageId()
    {
        return Arr::get($this->storeSettings, 'receipt_page_id');
    }

    public function getReceiptPage(): string
    {
        if ($pageId = $this->getReceiptPageId()) {
            if (Pages::isPage($pageId)) {
                return $this->getPageLink($pageId);
            }
        }
        return '';
    }


    public function getBuyButtonText(): string
    {
        return esc_html(Arr::get($this->storeSettings, 'checkout_button_text', __('Buy now', 'webmakerr-cart')));
    }

    public function getViewCartButtonText(): string
    {
        return esc_html(Arr::get($this->storeSettings, 'view_cart_button_text', __('View Cart', 'webmakerr-cart')));
    }

    /**
     * @return string
     *
     * Get cart button text
     */
    public function getCartButtonText(): string
    {
        return esc_html(Arr::get($this->storeSettings, 'cart_button_text', __('Add to cart', 'webmakerr-cart')));
    }

    public function toArray(): array
    {
        return $this->storeSettings;
    }

    /**
     * @return string
     *
     * Get the base address1 for the store.
     */
    public function getBaseAddressLine1()
    {
        return Arr::get($this->storeSettings, 'store_address1');
    }

    public function getFormattedFullAddress()
    {
        $addressParts = [
            trim(Arr::get($this->storeSettings, 'store_address1') ?? ''),
            trim(Arr::get($this->storeSettings, 'store_address2') ?? ''),
            trim(Arr::get($this->storeSettings, 'store_city') ?? ''),
            trim(AddressHelper::getStateNameByCode(
                Arr::get($this->storeSettings, 'store_state'),
                Arr::get($this->storeSettings, 'store_country')
            )),
            trim(Arr::get($this->storeSettings, 'store_postcode') ?? ''),
            trim(AddressHelper::getCountryNameByCode(Arr::get($this->storeSettings, 'store_country')))
        ];

        // Filter out empty or null parts
        $addressParts = array_filter($addressParts, function ($part) {
            return $part !== '';
        });

        // Join parts with comma and space
        $addressString = implode(', ', $addressParts);
        return $addressString;
    }

    /**
     * @return string
     *
     * Get the base address2 for the store.
     */
    public function getBaseAddressLine2()
    {
        return Arr::get($this->storeSettings, 'store_address2');
    }

    /**
     * @return string
     *
     * Get the base country for the store.
     */
    public function getBaseCountry()
    {
        return Arr::get($this->storeSettings, 'store_country');
    }

    /**
     * @return string
     *
     * Get the base state for the store.
     */
    public function getBaseState()
    {
        return Arr::get($this->storeSettings, 'store_state');
    }

    /**
     * @return string
     *
     * Get the base city for the store.
     */
    public function getBaseCity()
    {
        return Arr::get($this->storeSettings, 'store_city');
    }

    /**
     * @return string
     *
     * Get the base postcode for the store.
     */
    public function getBasePostcode()
    {
        return Arr::get($this->storeSettings, 'store_postcode');
    }

    public function getInvoicePrefix()
    {
        // @todo: make this dynamic from settings
        return $this->get('inv_prefix', '');
    }

    public function getInvoiceSuffix(): string
    {
        return '';
    }

    public function getCustomerDashboardPageSlug($pageId = null, $cached = true)
    {
        $slug = Arr::get($this->storeSettings, 'customer_profile_page_slug', '');

        if ($cached && $slug) {
            return $slug;
        }

        if (!$pageId) {
            $pageId = Arr::get($this->storeSettings, 'customer_profile_page_id');
        }

        if (!$pageId) {
            return $slug;
        }

        $url = get_page_link($pageId);
        $url = rtrim($url, '/');

        if (!$url) {
            return $slug;
        }

        return trim(str_replace(home_url('/'), '', $url), '/');
    }

    /**
     * Get theme colors as CSS variable string.
     *
     * @return string
     */
    public function getThemeColors(): string
    {
        $themeSetup = $this->get('theme_setup');
        $colors = '';

        if (!empty($themeSetup) && is_array($themeSetup)) {
            foreach ($themeSetup as $key => $value) {
                if (!empty($value)) {
                    $colors .= "$key: $value;";
                }
            }
        }

        return $colors;
    }

    public function getPageLink($pageId = null): string
    {
        $link = get_page_link($pageId);

        if (!empty($link)) {
            return Str::endsWith($link, '/') ? $link : $link . '/';
        }
        return '';
    }
}