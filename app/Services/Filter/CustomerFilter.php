<?php

namespace Webmakerr\App\Services\Filter;

use Webmakerr\App\Helpers\AddressHelper;
use Webmakerr\App\Models\Activity;
use Webmakerr\App\Models\Customer;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Support\Str;

class CustomerFilter extends BaseFilter
{

    public function applySimpleFilter()
    {
        $isApplied = $this->applySimpleOperatorFilter();
        if ($isApplied) {
            return;
        }
        $this->query->when($this->search, function ($query, $search) {

            return $query
                ->where(function ($query) use ($search) {
                    $search = trim($search);
                    $query
                        ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                        ->when(is_numeric($search), function ($query) use ($search) {
                            $query->orWhere('id', 'LIKE', "%{$search}%");
                        })
                        ->when(!str_contains($search, ' '), function ($query) use ($search) {
                            $query->orWhere('email', 'LIKE', "%{$search}%");
                        });
                });
        });
    }


    public function tabsMap(): array
    {
        return [

        ];
    }

    public function getModel(): string
    {
        return Customer::class;
    }

    public static function getFilterName(): string
    {
        return 'customers';
    }


    public function applyActiveViewFilter()
    {

    }

    public static function getSearchableFields(): array
    {
        return [
            'id' => [
                'column'      => 'ID',
                'description' => 'Customer ID',
                'type'        => 'numeric'
            ]
        ];
    }

    public static function advanceFilterOptions(): array
    {
        $filters = [
            'order'    => [
                'label'    => __('Order Property', 'webmakerr-cart'),
                'value'    => 'order',
                'children' => [
                    [
                        'label'           => __('By Order Items', 'webmakerr-cart'),
                        'value'           => 'order_items',
                        'column'          => 'object_id',
                        'filter_type'     => 'relation',
                        'relation'        => 'orders.order_items',
                        'remote_data_key' => 'product_variations',
                        'type'            => 'remote_tree_select',
                        'limit'           => 10,
                    ],
                    [
                        'label' => __('Purchases', 'webmakerr-cart'),
                        'value' => 'purchase_count',
                        'type'  => 'numeric',
                    ],
                    [
                        'label'       => __('First Purchase Date', 'webmakerr-cart'),
                        'value'       => 'first_purchase_date',
                        'type'        => 'dates',
                        'filter_type' => 'date',
                    ],
                    [
                        'label'       => __('Last Purchase Date', 'webmakerr-cart'),
                        'value'       => 'last_purchase_date',
                        'type'        => 'dates',
                        'filter_type' => 'date',
                    ]
                ],
            ],
            'customer' => [
                'label'    => __('Customer Property', 'webmakerr-cart'),
                'value'    => 'customer',
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
                            $query->searchByFullName($data);
                        }
                    ],
                    [
                        'label'       => __('Customer Email', 'webmakerr-cart'),
                        'value'       => 'email',
                        'type'        => 'text',
                        'filter_type' => 'column',
                        'column'      => 'email',
                    ],
                    [
                        'label'       => __('Customer LTV', 'webmakerr-cart'),
                        'value'       => 'ltv',
                        'type'        => 'numeric',
                        'filter_type' => 'column',
                        'column'      => 'ltv'
                    ]
                ],
            ]
        ];
        return LabelFilter::advanceFilterOptionsForOther($filters);
    }

    public function dateColumns(): array
    {
        return array_merge(parent::dateColumns(), ['first_purchase_date', 'last_purchase_date']);
    }

    public function centColumns(): array
    {
        return ['ltv'];
    }
}