<?php

namespace Webmakerr\App\Services\Filter;

use Webmakerr\App\Models\Label;
use Webmakerr\App\Models\Order;
use Webmakerr\Framework\Database\Orm\Builder;
use Webmakerr\Framework\Support\Arr;

class LabelFilter extends BaseFilter
{

    public function applySimpleFilter()
    {
        $this->query = $this->query->when($this->search, function ($query, $search) {
            return $query->where('value', 'LIKE', "%{$search}%");
        });
    }

    public function tabsMap(): array
    {
        return [];
    }

    public function getModel(): string
    {
        return Label::class;
    }

    public static function getFilterName(): string
    {
        return 'label';
    }


    public function applyActiveViewFilter()
    {

    }

    public static function getSelectFilterOptions(array $args): array
    {
        return static::make($args)->get()->pluck('value', 'id')->toArray();
    }

    public static function advanceFilterOptions()
    {
        return null;
    }

    public static function advanceFilterOptionsForOther($otherFilters = []): array
    {
        return array_merge(
            $otherFilters,
            [
                'labels' => [
                    'label'    => __('Labels', 'fluent-cart'),
                    'value'    => 'labels',
                    'children' => [
                        [
                            'label'           => __('Label Name', 'fluent-cart'),
                            'value'           => 'customer_email',
                            'type'            => 'selections',
                            'filter_type'     => 'relation',
                            'column'          => 'label_id',
                            'relation'        => 'labels',
                            'remote'          => true,
                            'remote_data_key' => 'labels',
                            'is_multiple' => true
                        ]
                    ]
                ]
            ]
        );
    }
}