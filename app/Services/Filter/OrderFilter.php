<?php

namespace Webmakerr\App\Services\Filter;

use Webmakerr\Api\ModuleSettings;
use Webmakerr\App\App;
use Webmakerr\App\Helpers\AddressHelper;
use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Models\Order;
use Webmakerr\Framework\Database\Orm\Builder;
use Webmakerr\Framework\Support\Arr;
use FluentCartPro\App\Modules\Licensing\Models\License;

class OrderFilter extends BaseFilter
{

    public function applySimpleFilter()
    {
        $isApplied = $this->applySimpleOperatorFilter();
        if ($isApplied) {
            return;
        }

        foreach (['payment_statuses', 'order_statuses', 'shipping_statuses'] as $key => $status) {
            $this->query->when(Arr::get($this->args, $status), function ($query) use ($status) {
                return $query->whereIn($status, $status);
            });
        }


        if (!empty($this->search)) {
            $search = $this->search;

            $this->query->orWhere('invoice_no', 'LIKE', "%{$search}%")
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery
                        ->where('email', 'LIKE', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
        }
    }

    public function tabsMap(): array
    {
        return [
            'on-hold'            => 'status',
            'paid'               => 'payment_status',
            //'unpaid'       => 'payment_status',
            'completed'          => 'status',
            'processing'         => 'status',
            'renewal'            => 'type',
            'subscription'       => 'type',
            'onetime'            => 'type',
            'refunded'           => 'payment_status',
            'partially_refunded' => 'payment_status',
            'upgraded_to'        => 'upgraded_to',
            'upgraded_from'      => 'upgraded_from'
        ];
    }

    public function getModel(): string
    {
        return Order::class;
    }

    public static function getFilterName(): string
    {
        return 'orders';
    }

    public static function parseableKeys(): array
    {
        return array_merge(
            parent::parseableKeys(),
            ['payment_statuses', 'order_statuses', 'shipping_statuses']
        );
    }


    public function applyActiveViewFilter()
    {

        $tabsMap = $this->tabsMap();

        //Apply Active Tab view
        $this->query = $this->query->when($this->activeView, function (Builder $query, $activeView) use ($tabsMap) {

            if ($activeView === 'upgraded_to') {
                return $query
                    ->whereRaw("JSON_EXTRACT(config, '$.upgraded_to') IS NOT NULL")
                    ->whereRaw("JSON_EXTRACT(config, '$.upgraded_to') != 0");
            } else if ($activeView === 'upgraded_from') {
                return $query
                    ->whereRaw("JSON_EXTRACT(config, '$.upgraded_from') IS NOT NULL")
                    ->whereRaw("JSON_EXTRACT(config, '$.upgraded_from') != 0");
            } else {
                return $query->where(
                    $tabsMap[$activeView],
                    $activeView
                );
            }


        });
    }

    public static function getSearchableFields(): array
    {
        $fields = [
            'id'      => [
                'column'      => 'id',
                'description' => __('Order Id', 'webmakerr-cart'),
                'type'        => 'numeric',
                'examples'    => [
                    'id = 1',
                    'id > 5',
                    'id :: 1-10'
                ]
            ],
            'status'  => [
                'column'      => 'status',
                'description' => __('Search by order status e.g., completed, processing, on-hold, canceled, failed', 'webmakerr-cart'),
                'type'        => 'string',
                'examples'    => [
                    'status = completed',
                ]
            ],
            'invoice' => [
                'column'      => 'status',
                'description' => __('Invoice Number', 'webmakerr-cart'),
                'type'        => 'string'
            ],

            'payment'    => [
                'column'      => 'payment_status',
                'description' => __('Search by payment status e.g., paid, pending, partially_paid, refunded, partially_refunded', 'webmakerr-cart'),
                'type'        => 'string',
                'examples'    => [
                    'payment = paid',
                    'payment = partially_paid',
                    'payment = partially_refunded',
                ]
            ],
            'payment_by' => [
                'column'      => 'payment_method',
                'description' => __('Search by payment method e.g., stripe, PayPal, offline_payment', 'webmakerr-cart'),
                'type'        => 'string',
                'examples'    => [
                    'payment_by = stripe',
                    'payment_by = paypal',
                ]
            ],

            'customer' => [
                'description' => __('Search by customer name or email', 'webmakerr-cart'),
                'note'        => __("only supports '=' operator", 'webmakerr-cart'),
                'type'        => 'custom',
                'callback'    => function ($query, $search) {
                    $query->whereHas('customer', function ($query) use ($search) {
                        $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
                },
                'examples'    => [
                    'customer = jhon',
                ]
            ],
        ];


        if (class_exists(License::class)) {
            $fields['license'] = [
                'description' => __('Search by license key', 'webmakerr-cart'),
                'note'        => __("only supports '=' operator", 'webmakerr-cart'),
                'type'        => 'custom',
                'callback'    => function ($query, $search) {
                    $query->whereHas('licenses', function ($query) use ($search) {
                        $query->where('license_key', 'like', '%' . $search . '%');
                    });
                },
                'examples'    => [
                    'license = ff-78d47b3fed89bda25cdc5b60d0298d60',
                ]
            ];
        }

        return $fields;
    }

    public static function advanceFilterOptions(): array
    {
        $filters = [
            'order'        => [
                'label'    => __('Order Property', 'webmakerr-cart'),
                'value'    => 'order',
                'children' => [
                    [
                        'label'           => __('By Order Items', 'webmakerr-cart'),
                        'value'           => 'order_items',
                        'column'          => 'object_id',
                        'filter_type'     => 'relation',
                        'relation'        => 'order_items',
                        'remote_data_key' => 'product_variations',
                        'type'            => 'remote_tree_select',
                        'limit'           => 10,
                    ],
                    [
                        'label'       => __('Order Status', 'webmakerr-cart'),
                        'value'       => 'status',
                        'type'        => 'selections',
                        'options'     => [
                            'completed'  => __('Completed', 'webmakerr-cart'),
                            'processing' => __('Processing', 'webmakerr-cart'),
                            'on-hold'    => __('On Hold', 'webmakerr-cart'),
                            'canceled'   => __('Canceled', 'webmakerr-cart')
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
                    [
                        'label'       => __('Payment Status', 'webmakerr-cart'),
                        'value'       => 'payment_status',
                        'type'        => 'selections',
                        'options'     => [
                            'paid'               => __('Paid', 'webmakerr-cart'),
                            'pending'            => __('Pending', 'webmakerr-cart'),
                            'partially_paid'     => __('Partially Paid', 'webmakerr-cart'),
                            'refunded'           => __('Refunded', 'webmakerr-cart'),
                            'partially_refunded' => __('Partially Refunded', 'webmakerr-cart'),
                            //'authorized'         => __('Authorized', 'webmakerr-cart')
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
//                    [
//                        'label'       => __('Shipping Status', 'webmakerr-cart'),
//                        'value'       => 'shipping_status',
//                        'type'        => 'selections',
//                        'options'     => [
//                            'fulfilled'   => __('Fulfilled', 'webmakerr-cart'),
//                            'unfulfilled' => __('Unfulfilled', 'webmakerr-cart'),
//                            'on_hold'     => __('On Hold', 'webmakerr-cart')
//                        ],
//                        'is_multiple' => true,
//                        'is_only_in'  => true
//                    ],
                    [
                        'label'       => __('Order Type', 'webmakerr-cart'),
                        'value'       => 'type',
                        'type'        => 'selections',
                        'options'     => [
                            'payment'      => __('Single Payment', 'webmakerr-cart'),
                            'subscription' => __('Subscription', 'webmakerr-cart'),
                            'renewal'      => __('Renewal', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
                    [
                        'label'       => __('Payment Method', 'webmakerr-cart'),
                        'value'       => 'payment_method',
                        'type'        => 'selections',
                        'column'      => 'payment_method',
                        'relation'    => 'transactions',
                        'filter_type' => 'relation',
                        'options'     => [
                            'stripe'          => __('Stripe', 'webmakerr-cart'),
                            'paypal'          => __('PayPal', 'webmakerr-cart'),
                            'offline_payment' => __('Cash on Delivery', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
                    [
                        'label' => __('Order Amount', 'webmakerr-cart'),
                        'value' => 'total_amount',
                        'type'  => 'numeric',
                    ],
                    [
                        'label'       => __('Order Date', 'webmakerr-cart'),
                        'value'       => 'created_at',
                        'type'        => 'dates',
                        'filter_type' => 'date',
                    ],
                ],
            ],
            'customer'     => [
                'label' => __('Customer Property', 'webmakerr-cart'),
                'value' => 'customer',

                'children' => [
                    [
                        'label'       => __('Customer Name', 'webmakerr-cart'),
                        'value'       => 'customer_full_name',
                        'type'        => 'text',
                        'filter_type' => 'custom',
                        'operators'   => [
                            'like_all'    => __('Contains', 'webmakerr-cart'),
                            'starts_with' => __('Starts With', 'webmakerr-cart'),
                            'ends_with'   => __('Ends With', 'webmakerr-cart'),
                            'not_like'    => __('Not Contains', 'webmakerr-cart'),
                        ],
                        'callback'    => function ($query, $data) {
                            $query->whereHas('customer', function ($query) use ($data) {
                                $query->searchByFullName($data);
                            });
                        }
                    ],

                    [
                        'label'       => __('Customer Email', 'webmakerr-cart'),
                        'value'       => 'customer_email',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'email',
                        'relation'    => 'customer',
                    ]
                ],
            ],
            'transactions' => [
                'label' => __('Transactions Property', 'webmakerr-cart'),
                'value' => 'transactions',

                'children' => [
                    [
                        'label'       => __('Transaction Id', 'webmakerr-cart'),
                        'value'       => 'transaction_id',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'vendor_charge_id',
                        'relation'    => 'transactions',
                    ],
                    [
                        'label'       => __('Transaction Status', 'webmakerr-cart'),
                        'value'       => 'transaction_status',
                        'type'        => 'selections',
                        'filter_type' => 'relation',
                        'column'      => 'status',
                        'relation'    => 'transactions',
                        'options'     => [
                            Status::TRANSACTION_SUCCEEDED => __('Succeeded', 'webmakerr-cart'),
                            Status::TRANSACTION_PENDING   => __('Pending', 'webmakerr-cart'),
                            Status::TRANSACTION_REFUNDED  => __('Refunded', 'webmakerr-cart'),
                            Status::TRANSACTION_FAILED    => __('Failed', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
                    [
                        'label'       => __('Transaction Type', 'webmakerr-cart'),
                        'value'       => 'transaction_type',
                        'type'        => 'selections',
                        'filter_type' => 'relation',
                        'column'      => 'transaction_type',
                        'relation'    => 'transactions',
                        'options'     => [
                            Status::TRANSACTION_TYPE_CHARGE  => __('Charge', 'webmakerr-cart'),
                            Status::TRANSACTION_TYPE_REFUND  => __('Refunded', 'webmakerr-cart'),
                            Status::TRANSACTION_TYPE_DISPUTE => __('Dispute', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
                    [
                        'label'       => __('Card last 4', 'webmakerr-cart'),
                        'value'       => 'transaction_card_last',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'card_last_4',
                        'relation'    => 'transactions',
                    ],
                    [
                        'label'       => __('Card Brand', 'webmakerr-cart'),
                        'value'       => 'transaction_card_brand',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'card_brand',
                        'relation'    => 'transactions',
                    ],
                    [
                        'label'       => __('Payer email', 'webmakerr-cart'),
                        'value'       => 'payer_email',
                        'type'        => 'text',
                        'filter_type' => 'custom',
                        'operators'   => [
                            'equals'      => __('Equals', 'webmakerr-cart'),
                            'contains'    => __('Contains', 'webmakerr-cart'),
                            'starts_with' => __('Starts With', 'webmakerr-cart'),
                            'ends_with'   => __('Ends With', 'webmakerr-cart'),
                            'not_like'    => __('Not Contains', 'webmakerr-cart')
                        ],
                        'callback'    => function ($query, $data) {
                            $query->whereHas('transactions', function ($query) use ($data) {
                                $query->searchByPayerEmail($data);
                            });
                        },
                        'examples'    => [
                            'payer_email = jhon@example.com',
                        ]
                    ]
                ]
            ]
        ];

        if (ModuleSettings::isActive('license') && App::isProActive()) {
            $filters['license'] = [
                'label'    => __('License Property', 'webmakerr-cart'),
                'value'    => 'license',
                'children' => [
                    [
                        'label'       => __('License key', 'webmakerr-cart'),
                        'value'       => 'license_key',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'license_key',
                        'relation'    => 'licenses',
                    ],
                    [
                        'label'       => __('License Status', 'webmakerr-cart'),
                        'value'       => 'license_status',
                        'type'        => 'selections',
                        'filter_type' => 'relation',
                        'column'      => 'status',
                        'relation'    => 'licenses',
                        'options'     => [
                            Status::LICENSE_ACTIVE   => __('Active', 'webmakerr-cart'),
                            Status::LICENSE_DISABLED => __('Disabled', 'webmakerr-cart'),
                            Status::LICENSE_EXPIRED  => __('Expired', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ]
                ],
            ];
        }

        $utmFilters = [
            'utm_campaign' => __('Utm Campaign', 'webmakerr-cart'),
            'utm_term'     => __('Utm Term', 'webmakerr-cart'),
            'utm_source'   => __('Utm Source', 'webmakerr-cart'),
            'utm_medium'   => __('Utm Medium', 'webmakerr-cart'),
            'utm_content'  => __('Utm Content', 'webmakerr-cart'),
            'utm_id'       => __('Utm Id', 'webmakerr-cart'),
            'refer_url'    => __('Refer Url', 'webmakerr-cart'),
        ];

        $utmChildren = [];
        foreach ($utmFilters as $key => $label) {
            $utmChildren[] = [
                'label'       => $label,
                'value'       => $key,
                'type'        => 'text',
                'filter_type' => 'relation',
                'column'      => $key,
                'relation'    => 'orderOperation',
            ];
        }

        $filters['utm'] = [
            'label' => __('Utm Property', 'webmakerr-cart'),
            'value' => 'utm',

            'children' => $utmChildren
        ];
        return LabelFilter::advanceFilterOptionsForOther($filters);
    }


    public function centColumns(): array
    {
        return ['subtotal', 'shipping_total', 'total_amount', 'total_paid', 'total_refund'];
    }
}