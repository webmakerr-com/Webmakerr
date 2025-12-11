<?php

namespace Webmakerr\App\Hooks\Handlers;

use Webmakerr\App\Modules\StorageDrivers\Local\Local;
use Webmakerr\App\Modules\StorageDrivers\S3\S3;
use Webmakerr\Api\StorageDrivers;

class GlobalStorageHandler
{
    public function register()
    {
        webmakerr_add_action('webmakerr_loaded', [$this, 'init']);
    }

    public function init()
    {
        add_action('init', function () {
            (new Local())->init();
            (new S3())->init();
        });
        //This hook will allow others to register their storage driver with ours
        webmakerr_do_action('webmakerr_cart/register_storage_drivers');

    }

    public function getSettings($driver)
    {
        $storageDrivers = new StorageDrivers();
        return $storageDrivers->getSettings($driver);
    }

    public function getAll()
    {
        $storageDrivers = new StorageDrivers();
        return $storageDrivers->getAll();
    }

    public function getStatus($driver)
    {
        $storageDrivers = new StorageDrivers();
        return $storageDrivers->getStatus($driver);
    }

    public function getAllActive()
    {
        $storageDrivers = new StorageDrivers();
        return $storageDrivers->getActive();
    }
    
}
