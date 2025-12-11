<?php

namespace Webmakerr\App\Events\Order;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Order;

class OrderUpdated extends EventDispatcher
{
    public string $hook = 'fluent_cart/order_updated';

    protected array $listeners = [
//        Listeners\UpdateStock::class,
        Listeners\Order\OrderUpdated::class
    ];

    /**
     * @var $order Order
     */
    public Order $order;
    public Order $oldOrder;

    public function __construct(Order $order, Order $oldOrder)
    {
        $this->order = $order;
        $this->order->load('customer', 'shipping_address', 'billing_address');
        $this->oldOrder = $oldOrder;
        $this->oldOrder->load('customer', 'shipping_address', 'billing_address');
    }

    public function toArray(): array
    {
        return [
            'order'     => $this->order,
            'old_order' => $this->oldOrder
        ];
    }

    public function getActivityEventModel()
    {
        return $this->order;
    }
}
