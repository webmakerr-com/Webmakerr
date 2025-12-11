<?php

namespace Webmakerr\App\Modules\Subscriptions\Services\Filter;

use Webmakerr\Api\ModuleSettings;
use Webmakerr\Api\PaymentMethods;
use Webmakerr\App\App;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Models\Subscription;
use Webmakerr\App\Services\Filter\BaseFilter;
use Webmakerr\App\Services\Filter\LabelFilter;
use Webmakerr\Framework\Database\Orm\Builder;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Support\Collection;
use Webmakerr\Framework\Support\Str;

class SubscriptionFilter extends BaseFilter
{
    public function applySimpleFilter()
    {
        $isApplied = $this->applySimpleOperatorFilter();
        if ($isApplied) {
            return;
        }

        $this->query->when($this->search, function ($query, $search) {
            return $query->where(function ($query) use ($search) {
                if (in_array(strtolower($search), ['canceled', 'cancelled'])) {
                    $query->orWhere('status', 'canceled');
                } else {
                    $query->orWhere('status', $search);
                }

                if (Str::of($search)->contains('#')) {
                    $search = Str::of($search)->remove('#')->toString();
                    $query->orWhere('id', 'like', '%' . $search . '%');
                } else if (Str::of($search)->contains('@')) {
                    $query->orWhereHas('customer', function ($query) use ($search) {
                        $query->where('email', 'like', '%' . $search . '%');
                    });
                } else {
                    // Search in other columns
                    $columns = ['parent_order_id', 'item_name', 'vendor_subscription_id',
                        'vendor_customer_id', 'vendor_plan_id', 'current_payment_method',
                        'billing_interval', 'bill_count'];

                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        });
    }

    public function tabsMap(): array
    {
        return [
            Status::SUBSCRIPTION_ACTIVE   => 'status',
            Status::SUBSCRIPTION_PENDING  => 'status',
            Status::SUBSCRIPTION_INTENDED => 'status',
            Status::SUBSCRIPTION_PAUSED   => 'status',
            Status::SUBSCRIPTION_TRIALING => 'status',
            Status::SUBSCRIPTION_CANCELED => 'status',
            Status::SUBSCRIPTION_FAILING  => 'status',
            Status::SUBSCRIPTION_EXPIRING => 'status',
            Status::SUBSCRIPTION_EXPIRED  => 'status',
        ];
    }

    public function getModel(): string
    {
        return Subscription::class;
    }

    public static function getFilterName(): string
    {
        return 'subscriptions';
    }

    public function applyActiveViewFilter()
    {
        $tabsMap = $this->tabsMap();
        //Apply Active Tab view
        $this->query = $this->query->when($this->activeView, function (Builder $query, $activeView) use ($tabsMap) {
            $query->where(function ($query) use ($tabsMap, $activeView) {
                if ('canceled' === $activeView && $tabsMap[$activeView] === 'status') {
                    // active(Collection paused) is also considered as canceled
                    $query->whereIn('status', [Status::SUBSCRIPTION_CANCELED]);
                } else {
                    $query->where($tabsMap[$activeView], $activeView);
                }
            });
        });
    }

    public static function getSearchableFields(): array
    {
        return [
            'id' => [
                'column'      => 'ID',
                'description' => __('Subscription ID', 'webmakerr-cart'),
                'type'        => 'numeric'
            ]
        ];
    }

    public static function advanceFilterOptions(): array
    {
        $activePaymentMethods = PaymentMethods::getActiveMeta();
        $paymentMethodOptions = Collection::make($activePaymentMethods)->pluck('title', 'slug')->toArray();
        $paymentMethodOptions = array_merge(
            $paymentMethodOptions,
            [
                'stripe' => __('Stripe', 'webmakerr-cart'),
                'paypal' => __('PayPal', 'webmakerr-cart')
            ]
        );
        $filters = [
            'subscription' => [
                'label'    => __('Subscription', 'webmakerr-cart'),
                'value'    => 'subscription',
                'children' => [
                    [
                        'label'       => __('Subscription ID', 'webmakerr-cart'),
                        'value'       => 'vendor_subscription_id',
                        'type'        => 'text',
                        'filter_type' => 'column',
                        'column'      => 'vendor_subscription_id',
                    ],
                    [
                        'label'       => __('Status', 'webmakerr-cart'),
                        'value'       => 'status',
                        'type'        => 'selections',
                        'options'     => Status::getSubscriptionStatuses(),
                        'is_multiple' => true,
                    ],
                    [
                        'label'           => __('Order Items', 'webmakerr-cart'),
                        'value'           => 'variation',
                        'type'            => 'remote_tree_select',
                        'column'          => 'id',
                        'filter_type'     => 'relation',
                        'relation'        => 'variation',
                        'remote_data_key' => 'product_variations',
                        'limit'           => 10,
                    ],
                    [
                        'label'       => __('Billing Interval', 'webmakerr-cart'),
                        'value'       => 'billing_interval',
                        'type'        => 'selections',
                        'options'     => [
                            'yearly'      => __('Yearly', 'webmakerr-cart'),
                            'half_yearly' => __('Half Yearly', 'webmakerr-cart'),
                            'quarterly'   => __('Quarterly', 'webmakerr-cart'),
                            'monthly'     => __('Monthly', 'webmakerr-cart'),
                            'weekly'      => __('Weekly', 'webmakerr-cart'),
                            'daily'       => __('Daily', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                    ],
                    [
                        'label'       => __('Created At', 'webmakerr-cart'),
                        'value'       => 'created_at',
                        'type'        => 'dates',
                        'filter_type' => 'date',
                    ],
                    [
                        'label'       => __('Next Billing Date', 'webmakerr-cart'),
                        'value'       => 'next_billing_date',
                        'type'        => 'dates',
                        'filter_type' => 'date',
                    ],
                    [
                        'label' => __('Bill Count', 'webmakerr-cart'),
                        'value' => 'bill_count',
                        'type'  => 'numeric',
                    ],
                    [
                        'label'       => __('Status', 'webmakerr-cart'),
                        'value'       => 'status',
                        'type'        => 'selections',
                        'options'     => Status::getSubscriptionStatuses(),
                        'is_multiple' => true,
                    ],
                ],
            ],
            'transaction'  => [
                'label'    => __('Transaction Property', 'webmakerr-cart'),
                'value'    => 'transaction',
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
                        'label'       => __('Payment method', 'webmakerr-cart'),
                        'value'       => 'current_payment_method',
                        'type'        => 'selections',
                        'options'     => $paymentMethodOptions,
                        'is_multiple' => true,
                    ],
                ],
            ],
            'product'      => [
                'label'    => __('Products', 'webmakerr-cart'),
                'value'    => 'product',
                'children' => [
                    [
                        'label'           => __('By Products', 'webmakerr-cart'),
                        'value'           => 'product',
                        'type'            => 'remote_tree_select',
                        'column'          => 'variation_id',
                        'filter_type'     => 'relation',
                        'relation'        => 'variation',
                        'remote_data_key' => 'product_variations',
                        'limit'           => 10,
                    ],
                ],
            ],
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
                        'relation'    => 'license',
                    ],

                    [
                        'label'       => __('License Status', 'webmakerr-cart'),
                        'value'       => 'license_status',
                        'type'        => 'selections',
                        'filter_type' => 'relation',
                        'column'      => 'status',
                        'relation'    => 'license',
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
        return LabelFilter::advanceFilterOptionsForOther($filters);
    }
}