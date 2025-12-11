<?php

namespace Webmakerr\Api\Invokable;

use Webmakerr\App\Services\Async\ImageAttachService;
use Webmakerr\Framework\Support\Arr;

class ImageAttach
{
    public function __invoke($app, $params)
    {
        $method = Arr::get($params, 'method');
        $data = Arr::get($params, 'data');
        $images = Arr::get($params, 'images');

        if ($method === 'attachImageToVariant') {
            ImageAttachService::attachImageToVariant($data, $images);
        } else if ($method === 'attachImageToProduct') {
            ImageAttachService::attachImageToProduct($data, $images);
        }
    }
}