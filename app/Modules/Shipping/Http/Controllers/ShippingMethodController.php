<?php

namespace Webmakerr\App\Modules\Shipping\Http\Controllers;

use Webmakerr\App\Http\Controllers\Controller;
use Webmakerr\App\Models\ShippingMethod;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\App\Modules\Shipping\Http\Requests\ShippingMethodRequest;

class ShippingMethodController extends Controller
{

    public function store(ShippingMethodRequest $request): \WP_REST_Response
    {
        $data = $request->getSafe($request->sanitize());

        ShippingMethod::create($data);

        return $this->sendSuccess([
            'message' => __('Shipping method has been created successfully', 'fluent-cart')
        ]);
    }

    public function update(ShippingMethodRequest $request): \WP_REST_Response
    {
        $data = $request->getSafe($request->sanitize());
        $methodId = Arr::get($data, 'method_id');

        $method = ShippingMethod::findOrFail($methodId);

        $method->update($data);

        return $this->sendSuccess([
            'message' => __('Shipping method has been updated successfully', 'fluent-cart')
        ]);
    }

    public function destroy($method_id)
    {
        $method = ShippingMethod::findOrFail($method_id);
        $method->delete();

        return $this->sendSuccess([
            'message' => __('Shipping method has been deleted successfully', 'fluent-cart')
        ]);
    }

}
