<?php

namespace Webmakerr\Api\Invokable;

use Webmakerr\Framework\Support\Arr;

class DummyProduct
{
    public function __invoke($app, $params)
    {
        \Webmakerr\App\Services\Async\DummyProductService::createAll(Arr::get($params, 'category'));
    }
}