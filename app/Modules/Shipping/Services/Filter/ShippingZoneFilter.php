<?php

namespace Webmakerr\App\Modules\Shipping\Services\Filter;

use Webmakerr\App\Models\ShippingZone;
use Webmakerr\App\Services\Filter\BaseFilter;
use Webmakerr\Framework\Database\Orm\Builder;
use Webmakerr\Framework\Support\Arr;

class ShippingZoneFilter extends BaseFilter
{
    public function applySimpleFilter()
    {
        if (!empty($this->search)) {
            $this->query->whereLike('name', $this->search);
        }
    }

    public function getModel(): string
    {
        return ShippingZone::class;
    }

    public static function getFilterName(): string
    {
        return 'shipping_zones';
    }

    protected function defaultSorting(): array
    {
        return [
            'column'    => 'order',
            'direction' => 'ASC'
        ];
    }

    public static function getAdvanceFilterOptions(): ?array
    {
        return [
            'search' => [
                'type'  => 'text',
                'label' => __('Search', 'fluent-cart')
            ]
        ];
    }

    public function applyActiveViewFilter()
    {
        // TODO: Implement applyActiveViewFilter() method.
    }

    public function tabsMap(): array
    {
        return [
            'publish'          => 'post_status',
            'draft'            => 'post_status',
            'physical'         => 'fulfillment_type',
            'digital'          => 'fulfillment_type',
            'subscribable'     => 'has_subscription',
            'not_subscribable' => 'has_subscription',
        ];
    }
}