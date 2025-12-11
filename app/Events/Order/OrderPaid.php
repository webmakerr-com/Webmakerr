<?php

namespace Webmakerr\App\Events\Order;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Models\OrderTransaction;

class OrderPaid extends EventDispatcher
{

    public string $hook = 'fluent_cart/order_paid';
    protected array $listeners = [
        Listeners\Order\OrderPaid::class,
    ];

    /**
     * @var $order Order
     */
    public Order $order;

    /**
     * @var $customer Customer|null
     */
    public ?Customer $customer;

    /**
     * @var $transaction OrderTransaction|null
     */
    public ?OrderTransaction $transaction;

    public function __construct(Order $order, $customer = null, $transaction = null)
    {
        $this->order = $order;
        $this->order->load('customer', 'order_items', 'shipping_address', 'billing_address');
        $this->customer = $customer;
        $this->transaction = $transaction;
    }

    public function toArray(): array
    {
        return [
            'order'       => $this->order,
            'customer'    => $this->customer ?? null,
            'transaction' => $this->transaction ?? null
        ];
    }

    public function getActivityEventModel()
    {
        return $this->order;
    }

    public function beforeDispatch()
    {
        if (!$this->order->receipt_number) {
            $this->order = $this->order->generateReceiptNumber();
        }
    }

    public function afterDispatch()
    {
        /**
         * 3rd party devs: Please do not use this hook to add any action that
         * use: fluent_cart/order_paid_done
         */
        $id = as_enqueue_async_action('fluent_cart/order_paid_ansyc_private_handle', [
            [
                'order_id' => $this->order->id
            ]
        ], 'fluent-cart');

        if ($id) {
            $this->order->updateMeta('action_scheduler_id', $id);
        }

    }

}
