<?php

namespace Webmakerr\App\Events\Order;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Models\OrderTransaction;
use Webmakerr\Framework\Support\Str;
use ReflectionClass;

class OrderCreated extends EventDispatcher
{
    public string $hook = 'fluent_cart/order_created';
    protected array $listeners = [
        Listeners\Order\OrderCreated::class,
//        Listeners\UpdateStock::class,
    ];

    /**
     * @var $order Order
     */
    public Order $order;

    /**
     * @var $prevOrder Order|null
     */
    public ?Order $prevOrder;

    /**
     * @var $customer Customer|null
     */
    public ?Customer $customer;

    /**
     * @var $transaction OrderTransaction|null
     */
    public ?OrderTransaction $transaction;

    public function __construct($order, $prevOrder = null, $customer = null, $transaction = null)
    {
        $this->order = $order;
        $this->prevOrder = $prevOrder;
        $this->order->load('customer','shipping_address','billing_address');
        $this->customer = $customer;
        $this->transaction = $transaction;
    }


    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'prev_order' => $this->prevOrder,
            'customer' => $this->customer ?? [],
            'transaction' => $this->transaction ?? []
        ];
    }

    public function getActivityEventModel()
    {
        return $this->order;
    }

}
