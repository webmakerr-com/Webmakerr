<?php

namespace Webmakerr\Database;

use Webmakerr\App\App;
use Webmakerr\Database\Seeder\AttributeSeeder;
use Webmakerr\Database\Seeder\CouponSeeder;
use Webmakerr\Database\Seeder\CustomerAddressSeeder;
use Webmakerr\Database\Seeder\OrderAddressSeeder;
use Webmakerr\Database\Seeder\OrderOperationSeeder;
use Webmakerr\Database\Seeder\ProductSeeder;
use Webmakerr\Database\Seeder\CustomerSeeder;
use Webmakerr\Database\Seeder\OrderSeeder;
use Webmakerr\Database\Seeder\AppliedCouponsSeeder;
use Webmakerr\Database\Seeder\OrderMetaSeeder;
use Webmakerr\Database\Seeder\SubscriptionSeeder;
use Webmakerr\Database\Seeder\TaxSeeder;

class DBSeeder
{
    public static function run($count = 10, $entity = null, $checkDev = true, $assoc_args = [])
    {
        $seeders = [
            'customer'         => CustomerSeeder::class,
            'customer_address' => CustomerAddressSeeder::class,
            'coupon'           => CouponSeeder::class,
            'product'          => ProductSeeder::class,
            'order'            => OrderSeeder::class,
            'order_operation'  => OrderOperationSeeder::class,
            'order_address'    => OrderAddressSeeder::class,
            'subscription'     => SubscriptionSeeder::class,
            'tax'              => TaxSeeder::class,
        ];


        if (empty($entity)) {
            foreach ($seeders as $value) {
                /**
                 * @var CustomerSeeder|ProductSeeder|OrderSeeder|OrderOperationSeeder|OrderAddressSeeder|CouponSeeder|SubscriptionSeeder $value
                 */
                $value::seed($count, $assoc_args);
            }
        } else {
            if ($entity === 'order') {
                /**
                 * @var OrderSeeder $seeders ['order']
                 */
                $seeders['order']::seed($count, $assoc_args);
                $seeders['order_operation']::seed($count, $assoc_args);
            } elseif (isset($seeders[$entity])) {
                /**
                 * @var CustomerSeeder|ProductSeeder|OrderAddressSeeder $seeders ['order']
                 */
                $seeders[$entity]::seed($count, $assoc_args);
            }
        }

    }
}
