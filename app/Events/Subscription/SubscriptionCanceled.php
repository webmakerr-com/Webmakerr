<?php


namespace Webmakerr\App\Events\Subscription;

use Webmakerr\App\Events\EventDispatcher;
use Webmakerr\App\Listeners;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Models\Subscription;


class SubscriptionCanceled extends EventDispatcher
{
    public string $hook = 'fluent_cart/subscription_canceled';
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

    public string $reason;

    public function __construct($subscription, $order = null, $customer = null, $reason = '')
    {
        $this->subscription = $subscription;
        $this->order = $order;
        $this->customer = $customer;
        $this->reason = $reason;
    }


    public function toArray(): array
    {
        return [
            'subscription' => $this->subscription,
            'order'        => $this->order,
            'customer'     => $this->customer ?? [],
            'reason'       => $this->reason ?: __('canceled on customer request', 'webmakerr-cart'),
        ];
    }

    public function getActivityEventModel(): Subscription
    {
        return $this->subscription;
    }

    public function shouldCreateActivity(): bool
    {
        return true;
    }

}