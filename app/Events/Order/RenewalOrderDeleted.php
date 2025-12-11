<?php

namespace Webmakerr\App\Events\Order;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\Order;

class RenewalOrderDeleted extends EventDispatcher
{
    public string $hook = 'fluent_cart/renewal_order_deleted';

    protected array $listeners = [
        // Listeners\UpdateStock::class,
        Listeners\Order\RenewalOrderDeleted::class
    ];

    /**
     * @var $order Order
     */
    public Order $order;

    /**
     * @var $customer Customer|null
     */

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->order->load('customer','shipping_address','billing_address');
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'customer' => $this->order->customer ?? [],
        ];
    }

    public function getActivityEventModel()
    {
        return $this->order;
    }
}
