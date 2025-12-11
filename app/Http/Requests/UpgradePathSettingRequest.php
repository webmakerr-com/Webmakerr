<?php

namespace Webmakerr\App\Http\Requests;

use Webmakerr\Framework\Foundation\RequestGuard;

class UpgradePathSettingRequest extends RequestGuard
{

    public function rules(): array
    {
        return [
            'from_variant'    => 'required|exists:fct_product_variations,id',
            'to_variants'     => 'required|array',
            'discount_amount' => 'numeric',
            'to_variants.*'   => 'required|exists:fct_product_variations,id',
        ];
    }

    /**
     *
     * @return array
     */
    public function messages()
    {
        return [
            'from_variant.required'   => __('From is required.', 'webmakerr-cart'),
            'to_variants.required'  => __('Variation is required.', 'webmakerr-cart'),
            'to_variants.*.required'  => __('Variation is required.', 'webmakerr-cart'),
            'discount_amount.numeric' => __('Discount amount is should be a number.', 'webmakerr-cart'),
        ];
    }


    /**
     *
     * @return array
     */
    public function sanitize()
    {
        return [
            'title'       => 'sanitize_text_field',
            'description' => 'sanitize_text_field',
            'slug'        => 'sanitize_text_field'
        ];
    }
}