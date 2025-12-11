<?php
namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\App;
use Webmakerr\App\Services\TemplateService;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Support\Arr;

class TemplateController extends Controller
{
    public function getPrintTemplates(Request $request): \WP_REST_Response
    {
        $availableTemplates = [
            'invoice_template' => __('Invoice Template', 'webmakerr-cart'),
            'packing_slip'     => __('Packing Slip Template', 'webmakerr-cart'),
            'delivery_slip'    => __('Delivery Slip Template', 'webmakerr-cart'),
            'shipping_slip'    => __('Shipping Slip Template', 'webmakerr-cart'),
            'dispatch_slip'    => __('Dispatch Slip Template', 'webmakerr-cart'),
        ];
        $templates = [];
        foreach ($availableTemplates as $path => $template) {
            $content = fluent_cart_get_option($path, '');
            if (empty($content)) {
                $content = TemplateService::getInvoicePackingTemplateByPathName($path);
            }
            $templates[] = [
                'key' => $path,
                'title' => $template,
                'content' => $content
            ];
        }

        return $this->sendSuccess([
            'templates' => $templates
        ]);
    }

    public function savePrintTemplates(Request $request): \WP_REST_Response
    {
        $templates = Arr::get($request->all(), 'templates', []);
        foreach ($templates as $template) {
            $key     = sanitize_key(Arr::get($template, 'key'));
            $content = wp_kses_post(Arr::get($template, 'content'));
            fluent_cart_update_option($key, $content, '', 'template');
        }
        return $this->sendSuccess([
            'message' => __('Template saved successfully', 'webmakerr-cart')
        ]);
    }

}