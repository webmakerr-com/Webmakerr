<?php

namespace Webmakerr\App\Modules\Shipping\Http\Controllers;

use Webmakerr\App\Http\Controllers\Controller;
use Webmakerr\App\Models\ShippingClass;
use Webmakerr\App\Modules\Shipping\Http\Requests\ShippingClassRequest;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\App\Modules\Shipping\Services\Filter\ShippingClassFilter;

class ShippingClassController extends Controller
{
    public function index(Request $request)
    {
        return $this->sendSuccess([
            'shipping_classes' => ShippingClassFilter::fromRequest($request)->paginate()
        ]);
    }

    public function store(ShippingClassRequest $request)
    {
        $data = $request->getSafe($request->sanitize());

        $shippingClass = ShippingClass::create($data);

        return $this->sendSuccess([
            'shipping_class' => $shippingClass,
            'message' => __('Shipping class has been created successfully', 'fluent-cart')
        ]);
    }

    public function show($id)
    {
        $shippingClass = ShippingClass::findOrFail($id);

        return $this->sendSuccess([
            'shipping_class' => $shippingClass
        ]);
    }

    public function update(ShippingClassRequest $request, $id)
    {
        $data = $request->getSafe($request->sanitize());

        $shippingClass = ShippingClass::findOrFail($id);
        $shippingClass->update($data);

        return $this->sendSuccess([
            'shipping_class' => $shippingClass,
            'message' => __('Shipping class has been updated successfully', 'fluent-cart')
        ]);
    }

    public function destroy($id)
    {
        $shippingClass = ShippingClass::findOrFail($id);
        $shippingClass->delete();

        return $this->sendSuccess([
            'message' => __('Shipping class has been deleted successfully', 'fluent-cart')
        ]);
    }
}
