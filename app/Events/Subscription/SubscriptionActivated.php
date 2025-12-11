<?php


namespace Webmakerr\App\Events\Subscription;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Models\Subscription;


class SubscriptionActivated extends EventDispatcher
{
    public string $hook = 'fluent_cart/subscription_activated';
    protected array $listeners = [

    ];

    /**
     * @var $subscription Subscription
     */
    public Subscription $subscription;

    /**
     * @var $customer Customer|null
     */
    public ?Customer $customer;

    /**
     * @var $order Order|null
     */
    public ?Order $order;

    public array $meta = [];

    public function __construct($subscription, $order = null, $customer = null, $meta = [])
    {
        $this->subscription = $subscription;
        $this->order = $order;
        $this->customer = $customer;
        $this->meta = $meta;
    }


    public function toArray(): array
    {
        return [
            'subscription' => $this->subscription,
            'order' => $this->order,
            'customer' => $this->customer ?? [],
            'meta' => $this->meta
        ];
    }

    public function getActivityEventModel()
    {
        return $this->subscription;
    }

}