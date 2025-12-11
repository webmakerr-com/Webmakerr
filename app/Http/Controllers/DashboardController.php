<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\Api\StoreSettings;
use Webmakerr\App\CPT\Pages;
use Webmakerr\App\Hooks\Handlers\GlobalPaymentHandler;
use Webmakerr\App\Models\Product;
use Webmakerr\App\Services\Widgets\DashboardWidget;
use Webmakerr\Framework\Support\Arr;

class DashboardController extends Controller
{
    public function getOnboardingData()
    {
        $completed = 0;
        $baseUrl = webmakerr_apply_filters('webmakerr_cart/admin_base_url', admin_url('admin.php?page=webmakerr#/'), []);
        $steps = [
            'page_setup'   => [
                'title'     => __('Setup Pages', 'webmakerr-cart'),
                'text'      => __("Customers to find what they're looking for by organising.", 'webmakerr-cart'),
                'icon'      => 'Cart',
                'completed' => false,
                'url'       => $baseUrl . "settings/store-settings/pages_setup"
            ],
            'store_info'   => [
                'title'     => __('Add Details to Store', 'webmakerr-cart'),
                'text'      => __('Store details such as addresses, company info etc.', 'webmakerr-cart'),
                'icon'      => 'StoreIcon',
                'completed' => false,
                'url'       => $baseUrl . "settings/store-settings/"
            ],
            'product_info' => [
                'title'     => __('Add Your First Product', 'webmakerr-cart'),
                'text'      => __('Share your brand story and build trust with customers.', 'webmakerr-cart'),
                'icon'      => 'ShoppingCartIcon',
                'completed' => false,
                'url'       => $baseUrl . "products"
            ],


            'setup_payments' => [
                'title'     => __('Setup Payment Methods', 'webmakerr-cart'),
                'text'      => __("Choose from fast & secure online and offline payment.", 'webmakerr-cart'),
                'icon'      => 'PaymentIcon',
                'completed' => true,
                'url'       => $baseUrl . "settings/payments"
            ],
        ];

        $settings = (new StoreSettings)->get();

        if ($this->isStoreInfoProvided($settings)) {
            $completed++;
            $steps['store_info']['completed'] = true;
        }

        if ($this->isProductInfoProvided()) {
            $completed++;
            $steps['product_info']['completed'] = true;
        }

        if ($this->isAllPageSetUpDone($settings)) {
            $completed++;
            $steps['page_setup']['completed'] = true;
        }

        if (!$this->isAnyPaymentModuleEnabled()) {
            $steps['setup_payments']['completed'] = false;
        } else {
            $completed++;
        }


        return $this->response->json([
            'data' => [
                'steps'     => $steps,
                'completed' => $completed
            ]
        ]);
    }

    protected function isStoreInfoProvided(array $settings): bool
    {
        $storeName = Arr::get($settings, 'store_name');
        $storeLogo = Arr::get($settings, 'store_logo');
        return !(
            //$storeName === 'Fluent Cart Shop' ||
            empty($storeName) ||
            empty($storeLogo)
        );
    }

    protected function isProductInfoProvided(): bool
    {
        return Product::query()->count() > 0;
    }


    private function isAllPageSetUpDone(array $settings): bool
    {
        $pages = (new Pages())->getGeneratablePage();
        foreach ($pages as $pageKey => $page) {
            $pageKey = "{$pageKey}_page_id";
            if (empty(Arr::get($settings, $pageKey))) {
                return false;
            }
        }
        return true;
    }

    private function isAnyPaymentModuleEnabled(): bool
    {
        $gateways = (new GlobalPaymentHandler())->getAll();
        foreach ($gateways as $gateway) {
            if ($gateway['status']) {
                return true;
            }
        }
        return false;
    }

    public function getDashboardStats(): \WP_REST_Response
    {
        return $this->sendSuccess([
            'stats' => DashboardWidget::widgets()
        ]);
    }
}