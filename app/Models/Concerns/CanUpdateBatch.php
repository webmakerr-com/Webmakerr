<?php

namespace Webmakerr\App\Models\Concerns;

use Webmakerr\App\Models\BatchQuery\Batch;
use Webmakerr\Framework\Database\Orm\Builder;

trait CanUpdateBatch
{
    public static function scopeBatchUpdate(Builder $query, $values, $index = null)
    {
        $model = new static();
        $index = $index ?? $model->getKeyName();
        return (new Batch())->update($model, $values,$index);
    }
}