<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\Api\CurrencySettings;
use Webmakerr\Api\Invokable\DummyProduct;
use Webmakerr\Api\StoreSettings;
use Webmakerr\App\CPT\Pages;
use Webmakerr\App\Helpers\Helper as HelperService;
use Webmakerr\App\Http\Requests\CreatePageRequest;
use Webmakerr\Framework\Foundation\Async;
use Webmakerr\Framework\Http\Request\Request;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Support\Str;

class OnboardingController extends Controller
{
    public function index(Request $request): \WP_REST_Response
    {
        $defaultSettings = (new StoreSettings())->toArray();

        $pages = new Pages();

        foreach ($pages->getGeneratablePage(true) as $pageName => $page) {

            if (empty(Arr::get($defaultSettings, "{$pageName}_page_id"))) {
                Arr::set(
                    $defaultSettings,
                    "{$pageName}_page_id",
                    Arr::get($page, 'page_id')
                );
            }

        }

        return $this->response->sendSuccess([
            'pages'            => Pages::getPages('', true),
            'currencies'       => CurrencySettings::getFormattedCurrencies(),
            'default_settings' => $defaultSettings
        ]);
    }

    public function createPages(Request $request): \WP_REST_Response
    {
        $excluded = [];
        $pages = new Pages();
        $storeSettings = new StoreSettings();

        foreach ($pages->getGeneratablePage() as $pageName => $page) {
            $pageId = $storeSettings->get("{$pageName}_page_id");
            if (!empty($pageId) && !Pages::isPage($pageId)) {
                $excluded[] = $pageName;
            }
        }

        $pages->createPages($excluded);

        return $this->index($request);
    }

    public function createPage(CreatePageRequest $request): \WP_REST_Response
    {
        $content = sanitize_text_field($request->get('content'));
        $pageKey = $content;
        $content = Str::of($content)->replaceFirst('_page_id', '')->toString();
        $saveSettings = filter_var($request->get('save_settings'), FILTER_VALIDATE_BOOLEAN);

        $generateablePages = (new Pages())->getGeneratablePage();
        $pageData = Arr::get($generateablePages, $content, null);

        if (!empty($pageData)) {
            $page = [
                'post_type'    => 'page',
                'post_title'   => sanitize_text_field($request->get('page_name')),
                'post_content' => $pageData['content'],
                'post_status'  => 'publish'
            ];

            $pageId = (string)wp_insert_post($page);

            if ($saveSettings) {
                (new StoreSettings())->save([
                    $pageKey => $pageId
                ]);

                flush_rewrite_rules(true);
                delete_option('rewrite_rules');
//                if ($content === 'customer_profile') {
//                    flush_rewrite_rules(true);
//                    delete_option('rewrite_rules');
//                }
            }

            return $this->response->sendSuccess([
                'page_id'   => $pageId,
                'page_name' => $pageData['title'],
                'link'      => get_page_link($pageId)
            ]);
        }

        return $this->response->sendError([
            'message' => __('Unable to create page', 'webmakerr-cart')
        ]);
    }

    public function saveSettings(Request $request)
    {
        $settings = array_merge((new StoreSettings())->toArray(), $request->all());

        $savedStoreSettings = (new StoreSettings())->save(
            Arr::except($settings, 'category')
        );

        if ($category = $request->get('category')) {
            Async::call(DummyProduct::class, ['category' => $category]);
        }

        if ($savedStoreSettings) {
            return $this->response->sendSuccess([
                'message' => __('Store has been updated successfully', 'webmakerr-cart')
            ]);
        }

        return $this->response->sendError([
            'errors' => __('Failed to update!', 'webmakerr-cart')
        ], 400);
    }
}