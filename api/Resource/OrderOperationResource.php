<?php

namespace Webmakerr\Api\Resource;

use Webmakerr\App\Models\OrderOperation;
use Webmakerr\Framework\Database\Orm\Builder;

class OrderOperationResource extends BaseResourceApi
{
    public static function getQuery(): Builder
    {
        return OrderOperation::query();
    }


    public static function get(array $params = [])
    {

    }

    public static function find($id, $params = [])
    {

    }


    public static function create($data, $params = [])
    {
        return static::getQuery()->create($data);
    }

    public static function update($data, $id, $params = [])
    {

    }

    public static function delete($id, $params = [])
    {

    }

    public static function view(int $id): array
    {
       return [];
    }
}