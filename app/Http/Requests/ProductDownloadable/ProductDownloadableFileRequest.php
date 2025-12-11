<?php

namespace Webmakerr\App\Http\Requests\ProductDownloadable;

use Webmakerr\Framework\Foundation\RequestGuard;

class ProductDownloadableFileRequest extends RequestGuard
{

    /**
     * @return array
     */
    public function rules()
    {

        return [
            //'product_variation_id' => 'required|array|min:1',
            'title' => 'required|sanitizeText|maxLength:100',
            'type' => 'required|sanitizeText|maxLength:100',
            'driver' => 'required|sanitizeText|maxLength:100',
            'file_name' => 'required|sanitizeText|maxLength:100',
            'settings.download_limit' => 'nullable|numeric',
            'settings.download_expiry' => 'nullable|numeric',
        ];
    }


    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => esc_html__('Title is required.', 'webmakerr-cart'),
            'type.required' => esc_html__('Type is required.', 'webmakerr-cart'),
            'driver.required' => esc_html__('Driver is required.', 'webmakerr-cart'),
            'file_name.required' => esc_html__('File Name is required.', 'webmakerr-cart'),
            'file_path.required' => esc_html__('File Path is required.', 'webmakerr-cart'),
            'file_url.required' => esc_html__('File URL is required.', 'webmakerr-cart'),
        ];
    }


    /**
     * @return array
     */
    public function sanitize()
    {

        return [
            'id' => 'intval',
            //'post_id' => 'intval',
            'product_variation_id' => 'sanitize_text_field',
            //'download_identifier' => 'sanitize_text_field',
            'title' => 'sanitize_text_field',
            'type' => 'sanitize_text_field',
            'driver' => 'sanitize_text_field',
            'file_name' => 'sanitize_text_field',
            'file_path' => 'sanitize_text_field',
            'file_url' => 'sanitize_text_field',
            'settings' => function ($value) {
                return $value;
            },
            'serial' => 'intval',
            //'created_at' => 'sanitize_text_field',
            //'updated_at' => 'sanitize_text_field',
        ];

    }
}