<?php

namespace Webmakerr\Api;

use Webmakerr\App\Modules\PaymentMethods\Core\AbstractPaymentGateway;
use Webmakerr\App\Modules\PaymentMethods\Core\GatewayManager;
use Webmakerr\Framework\Support\Arr;

class FluentCartGeneralApi
{
    /**
     * @var FluentCartGeneralApi
     */
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function addCustomerDashboardEndpoint($slug, $args = [])
    {
        if (!$slug) {
            throw new \Exception(esc_html__('The endpoint slug cannot be empty.', 'webmakerr-cart'));
        }

        $reserved = ['dashboard', 'purchase-history', 'subscriptions', 'licenses', 'downloads', 'profile'];

        if (in_array($slug, $reserved)) {
            throw new \Exception(
                sprintf(
                    /* translators: %s: The reserved endpoint slug. */
                    esc_html__(
                        'The endpoint slug "%s" is reserved and cannot be used.',
                        'webmakerr-cart'
                    ),
                    esc_html($slug)
                )
            );

        }

        if (!isset($args['render_callback']) && !isset($args['page_id'])) {
            throw new \Exception(esc_html__('You must provide either a render callback or a page ID for the endpoint.', 'webmakerr-cart'));
        }

        webmakerr_add_filter('webmakerr_cart/global_customer_menu_items', function ($items) use ($slug, $args) {
            // Add this new item just before the 'profile' item
            $profileKey = array_search('profile', array_keys($items));
            if ($profileKey !== false) {
                $items = array_slice($items, 0, $profileKey, true) +
                    [$slug => [
                        'label'     => Arr::get($args, 'title'),
                        'css_class' => 'fct-menu-item-' . $slug,
                        'link'      => \Webmakerr\App\Services\URL::getCustomerDashboardUrl($slug)
                    ]] +
                    array_slice($items, $profileKey, null, true);
            } else {
                // If 'profile' is not found, just append it at the end
                if (!isset($items[$slug])) {
                    $items[$slug] = [
                        'label'     => Arr::get($args, 'title'),
                        'css_class' => 'fct-menu-item-' . $slug,
                        'link'      => \Webmakerr\App\Services\URL::getCustomerDashboardUrl($slug)
                    ];
                }
            }

            return $items;
        });

        webmakerr_add_filter('webmakerr_cart/customer_portal/custom_endpoints', function ($endPoints) use ($slug, $args) {

            if (isset($args['render_callback'])) {
                $endPoints[$slug] = [
                    'render_callback' => $args['render_callback'],
                ];
            } else if (isset($args['page_id'])) {
                $endPoints[$slug] = [
                    'page_id' => $args['page_id'],
                ];
            }

            return $endPoints;
        });
    }

    public function registerCustomPaymentMethod($name, $paymentGatewayInstance)
    {
        if(! $paymentGatewayInstance instanceof AbstractPaymentGateway) {
            throw new \Exception(
                sprintf(
                    /* translators: %s: The name of the invalid payment gateway class. */
                    esc_html__(
                        'The payment gateway class "%s" is not valid. It must extend AbstractPaymentGateway.',
                        'webmakerr-cart'
                    ),
                    esc_html($paymentGatewayClass)
                )
            );

        }
        
        (GatewayManager::getInstance())->register($name, $paymentGatewayInstance);
    }

}