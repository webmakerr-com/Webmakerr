<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\Services\Renderer\CheckoutFieldsSchema;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Support\Arr;

class CheckoutFieldsController extends Controller
{

    public function getFields()
    {
        return [
            'fields'   => CheckoutFieldsSchema::getFieldsSchemaConfig(),
            'settings' => CheckoutFieldsSchema::getFieldsSettings(),
        ];
    }

    public function saveFields(Request $request)
    {
        $settings = $request->get('settings', []);
        $prevSettings = CheckoutFieldsSchema::getFieldsSettings();

        $settings = Arr::only($settings, array_keys($prevSettings));

        fluent_cart_update_option('_fc_checkout_fields', $settings);

        return [
            'message' => __('Checkout fields has been updated successfully.', 'webmakerr-cart'),
        ];
    }

}