<?php

namespace Webmakerr\App\Models\Query;

use Webmakerr\Framework\Database\Orm\Builder;
use Webmakerr\Framework\Support\Arr;

class Sort
{
    static function make(): Sort
    {
        return new static();
    }


    public function apply(Builder $query, array $sortCriteria)
    {
        if (empty($sortCriteria)) {
            return;
        }

        foreach ($sortCriteria as $key => $criteria) {
            $key = Arr::get($criteria, 'key');
            if (empty($key)) {
                continue;
            }
            $order = Arr::get($criteria, 'order') === 'descending' ? 'desc' : 'asc';
            $query->orderBy($key, $order);
        }
    }
}