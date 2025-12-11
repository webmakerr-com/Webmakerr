<?php

namespace Webmakerr\App\Http\Controllers;


use Webmakerr\App\Models\Order;
use Webmakerr\Framework\Http\Request\Request;

class NotesController extends Controller
{
    public function attach(Request $request)
    {
        $order = Order::query()->findOrFail(
            sanitize_text_field($request->get('order_id'))
        );

        $order->note = sanitize_text_field($request->get('note'));


        if ($order->update()) {
            return $this->sendSuccess([
                'message' => __('Order Note Updated successfully.', 'fluent-cart'),
            ]);
        }
        return $this->sendError([
            'message' => __('Failed to update order note.', 'fluent-cart'),
        ]);
    }
}
