<?php

namespace Webmakerr\App\Modules\ProductIntegration;

use Webmakerr\App\Models\ProductMeta;
use Webmakerr\App\Modules\Integrations\GlobalIntegrationSettings;
use Webmakerr\App\Modules\Integrations\GlobalNotificationHandler;
use Webmakerr\Framework\Support\Arr;

class ProductIntegrationHandler
{
    public function handle($order, $customer, $targetHook, $group): void
    {
        if (empty($order->order_items)) {
            return;
        }

        $productIds = $order->order_items->pluck('post_id');
        $productFeeds = $this->getProductsFeeds($productIds);

        $formattedFeeds = (new GlobalIntegrationSettings())->formatFeedsData($productFeeds);

        (new GlobalNotificationHandler())->triggerNotification(
            $formattedFeeds,
            $order,
            $customer,
            $targetHook,
            $group,
            'product_integration'
        );
    }

    public function getProductsFeeds($productIds)
    {
        return ProductMeta::query()->where('object_type', 'product_integration')
            ->whereIn('object_id', $productIds)
            ->get();
    }

}
