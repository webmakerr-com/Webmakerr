<?php

namespace Webmakerr\App\Events;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Models\ProductVariation;

class PlanChanged extends EventDispatcher
{

    public string $hook = 'fluent_cart/plan_changed';
    protected array $listeners = [];

    public Order $order;
    public ProductVariation $newVariation;
    public ProductVariation $oldVariation;
    public array $otherInfo = [];


    public function __construct(Order $order, ProductVariation $newVariation, ProductVariation $oldVariation, $otherInfo = null)
    {
        $this->order = $order;
        $this->newVariation = $newVariation;
        $this->oldVariation = $oldVariation;
        $this->otherInfo = is_array($otherInfo) ? $otherInfo : [];

        // Load necessary relationships
        $this->order->load('customer', 'shipping_address', 'billing_address');
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order->toArray(),
            'customer' => $this->order->customer ? $this->order->customer->toArray() : [],
            'new_variation' => $this->newVariation->toArray(),
            'old_variation' => $this->oldVariation->toArray(),
            'other_info' => $this->otherInfo
        ];
    }

    public function getActivityEventModel()
    {
        return null;
    }

    public function shouldCreateActivity(): bool
    {
        return false;
    }
}