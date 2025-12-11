<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\Api\Resource\FrontendResource\CartResource;
use Webmakerr\App\Helpers\CartCheckoutHelper;
use Webmakerr\App\Http\Requests\CartRequest;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Support\Arr;

class CartController extends Controller
{

    /**
     *
     * @param Request $request
     * @return array
     */
    public function getStatus(Request $request)
    {
        return CartResource::getStatus();
    }


    /**
     *
     * @param Request $request
     */
    public function updateCart(CartRequest $request)
    {

        $response = CartResource::update($request->get('data'), '');

        if (is_wp_error($response)) {
            return $this->response->sendError([
                'message' => $response->get_error_messages()[0]
            ]);
        }
        return $this->response->sendSuccess($response);
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function addToCart(CartRequest $request)
    {
        $itemId = $request->get('item_id');
        $itemId = intval($itemId);
        $qty = $request->get('quantity', 1);

        $isCreated = CartResource::create(['id' => $itemId, 'quantity' => $qty]);

        if (is_wp_error($isCreated)) {
            return $isCreated;
        }
        return $this->response->sendSuccess($isCreated);
    }
}
