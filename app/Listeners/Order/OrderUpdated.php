<?php

namespace Webmakerr\App\Listeners\Order;
use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Models\Customer;
use Webmakerr\App\Models\OrderTransaction;

class OrderUpdated
{
    public static function handle(\Webmakerr\App\Events\Order\OrderUpdated $event)
    {
        static::updateTransaction($event->oldOrder, $event->order);
    }

    public static function updateTransaction($oldOrder, $newOrder)
    {
        if ($oldOrder->total_amount == $newOrder->total_amount) {
            return;
        }

        $transaction = OrderTransaction::query()->where('order_id', $newOrder->id)
            ->where('transaction_type', Status::TRANSACTION_TYPE_CHARGE)
            ->where('status', 'pending')->first();

        if ($transaction) {
            $transaction->total = $newOrder->total_amount;
            $transaction->save();
        }
    }

}