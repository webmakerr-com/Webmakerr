<?php

namespace Webmakerr\App\Services\Filter;

use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Services\DateTime\DateTime;
use FluentCartPro\App\Modules\Licensing\Models\License;
use FluentCartPro\App\Modules\Promotional\Models\OrderPromotion;


class OrderBumpFilter extends BaseFilter
{

    public function applySimpleFilter()
    {

        $isApplied = $this->applySimpleOperatorFilter();
        if ($isApplied) {
            return;
        }

        $this->query->when($this->search, function ($query, $search) {
            // If search is an array, implode it
            $search = is_array($search) ? implode(' ', $search) : $search;

            // If search is empty or null, return the query
            if (empty($search)) {
                return $query;
            }

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });

            return $query;
        });
    }

    public function tabsMap(): array
    {
        return [
            'active'   => __('Active', 'webmakerr-cart'),
            'inactive' => __('Inactive', 'webmakerr-cart'),
        ];
    }

    public function getModel(): string
    {
        return OrderPromotion::class;
    }

    public static function getFilterName(): string
    {
        return 'order_bump';
    }


    public function applyActiveViewFilter()
    {

        $this->query->when($this->activeView, function ($query, $activeView) {
            $validStatuses = [
                'active',
                'draft'
            ];

            if (in_array($activeView, $validStatuses)) {
                if ($activeView == 'expired') {
                    $query->where('expiration_date', '<', DateTime::gmtNow());
                } else if ($activeView == 'active') {
                    $query->where(function ($query) {
                        $query->where('expiration_date', '>', DateTime::gmtNow())
                            ->orWhereNull('expiration_date');
                    })
                        ->where('status', 'active');
                } else {
                    $query->where('status', $activeView);
                }
            } else if ($activeView == 'inactive') {
                $query->where('status', 'active')
                    ->whereDoesntHave('activations');
            }

            return $query;
        });
    }

    public static function getSearchableFields(): array
    {
        return [
            'key' => [
                'column'      => 'title',
                'description' => 'title',
                'type'        => 'string'
            ]
        ];
    }

    public static function advanceFilterOptions(): array
    {
        $filters = [
            'product'  => [
                'label'    => __('Products', 'webmakerr-cart'),
                'value'    => 'product',
                'children' => [
                    [
                        'label'           => __('By Products', 'webmakerr-cart'),
                        'value'           => 'product',
                        'column'          => 'variation_id',
                        'filter_type'     => 'relation',
                        'relation'        => 'productVariant',
                        'remote_data_key' => 'product_variations',
                        'type'            => 'remote_tree_select',
                        'limit'           => 10,
                    ],
                ],
            ],
            'customer' => [
                'label'    => __('Customer Property', 'webmakerr-cart'),
                'value'    => 'customer',
                'children' => [
                    [
                        'label'       => __('Customer first name', 'webmakerr-cart'),
                        'value'       => 'customer_first_name',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'first_name',
                        'relation'    => 'customer',
                    ],
                    [
                        'label'       => __('Customer last name', 'webmakerr-cart'),
                        'value'       => 'customer_last_name',
                        'type'        => 'text',
                        'filter_type' => 'relation',
                        'column'      => 'last_name',
                        'relation'    => 'customer',
                    ]
                ],
            ],
            'license'  => [
                'label'    => __('License Property', 'webmakerr-cart'),
                'value'    => 'license',
                'children' => [
                    [
                        'label'       => __('License key', 'webmakerr-cart'),
                        'value'       => 'license_key',
                        'type'        => 'text',
                        'filter_type' => 'column',
                        'column'      => 'license_key',
                    ],
                    [
                        'label'       => __('Status', 'webmakerr-cart'),
                        'value'       => 'status',
                        'type'        => 'selections',
                        'filter_type' => 'column',
                        'column'      => 'status',
                        'options'     => [
                            Status::LICENSE_ACTIVE   => __('Active', 'webmakerr-cart'),
                            Status::LICENSE_DISABLED => __('Disabled', 'webmakerr-cart'),
                            Status::LICENSE_EXPIRED  => __('Expired', 'webmakerr-cart'),
                        ],
                        'is_multiple' => true,
                        'is_only_in'  => true
                    ],
                    [
                        'label'       => __('Activation Count', 'webmakerr-cart'),
                        'value'       => 'activation_count',
                        'type'        => 'numeric',
                        'filter_type' => 'column',
                        'column'      => 'activation_count',
                    ],
                    [
                        'label'       => __('Expiration Date', 'webmakerr-cart'),
                        'value'       => 'expiration_date',
                        'type'        => 'dates',
                        'filter_type' => 'date',
                        'column'      => 'expiration_date',
                    ]
                ],
            ],
        ];
        return LabelFilter::advanceFilterOptionsForOther($filters);
    }

}