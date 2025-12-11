<?php

namespace Webmakerr\App\Services\Widgets;

use Webmakerr\Framework\Support\Arr;

abstract class BaseWidget
{
    abstract public function widgetName(): string;

    abstract public function widgetData(): array;

    public static function widgets(): array
    {
        $instance = new static();
        $stats = webmakerr_apply_filters('webmakerr_cart/' . $instance->widgetName(), $instance->widgetData());
        return Arr::wrap($stats);
    }
}