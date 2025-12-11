<?php

namespace Webmakerr\App\Services\FileSystem;

use Webmakerr\Api\Resource\ProductDownloadResource;
use Webmakerr\App\App;
use Webmakerr\App\Helpers\CustomerHelper;
use Webmakerr\App\Http\Controllers\FrontendControllers\CustomerProfileController;
use Webmakerr\App\Models\ProductDownload;
use Webmakerr\App\Services\URL;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Http\URL as BaseUrl;

class DownloadService
{
    public function register()
    {
        webmakerr_add_action('webmakerr_cart/before_download_check_permission_and_store_log', function ($params) {
            $this->checkPermissionBeforeDownloadPermissionAndStoreLog($params);
        }, 10, 2);

        webmakerr_add_action('webmakerr_cart/download_file', function ($params) {
            $this->downloadFile($params);
        });

        //todo: use when web router removes
        //commented out as downloadFile method dosent exsits in CustomerProfileController class
//        add_action('init', function () {
//            if (!isset($_REQUEST['fluent_cart_download'])) {
//                return;
//            }
//            $request = App::getInstance('request');
//            $request->params = $request->all();
//            $this->registerDownloadRoutes($request);
//        });
    }

    public function registerDownloadRoutes($request)
    {
        (new CustomerProfileController())->downloadFile($request);
    }

    private function checkPermissionBeforeDownloadPermissionAndStoreLog($params)
    {
        $customerHelper = new CustomerHelper();
        $customerHelper->checkDownloadPermissionAndStoreLog($params);
    }

    private function downloadFile($params)
    {
        $driver = Arr::get($params, 'driver');
        $file = Arr::get($params, 'file');
        $productDownload = ProductDownloadResource::find(Arr::get($params, 'download_id'));
        $bucket = Arr::get($productDownload, 'settings.bucket', '');
        (new FileManager($driver))->downloadFile($file,$file, $bucket);
    }

    public static function downloadFileFromId($downloadId)
    {
        $productDownload = ProductDownload::query()->where('download_identifier', $downloadId)->first();
        if (empty($productDownload)) {
            return __('File not found', 'webmakerr-cart');
        }
        $driver = Arr::get($productDownload, 'driver');
        $file = Arr::get($productDownload, 'file_path');
        $bucket = Arr::get($productDownload, 'settings.bucket', '');
        $downloadName = Arr::get($productDownload, 'file_name');

        try{
            $fileManager = new FileManager($driver);
        }catch (\Exception $e){
            return new \WP_Error('fluent_cart_file_manager_error', $e->getMessage());
        }
        return $fileManager->downloadFile($file,$downloadName, $bucket);
    }

    public static function getDownloadableUrlFromDownload($productDownload): string
    {
        $driver = Arr::get($productDownload, 'driver');
        $file = Arr::get($productDownload, 'file_path');
        $fileName = Arr::get($productDownload, 'file_name');
        $bucket = Arr::get($productDownload, 'settings.bucket', '');
        return (new FileManager($driver))->getSignedDownloadUrl($file, $bucket, $productDownload);
    }

    public function getDownloadableUrl($params): string
    {
        $productDownload = ProductDownloadResource::find(Arr::get($params, 'download_id'));
        $identifier = Arr::get($productDownload, 'download_identifier', '');

        return (new BaseUrl())->sign(
            URL::getFrontEndUrl('download-by-id/'),
            [
                'download_identifier' => $identifier,
            ]
        );
    }


    public function getDownloadablePath($params)
    {
        $driver = Arr::get($params, 'driver');
        $file = Arr::get($params, 'file');
        $productDownload = ProductDownloadResource::find(Arr::get($params, 'download_id'));
        $bucket = Arr::get($productDownload, 'settings.bucket', '');
        return (new FileManager($driver))->getFilePath($file, $bucket);
    }

}