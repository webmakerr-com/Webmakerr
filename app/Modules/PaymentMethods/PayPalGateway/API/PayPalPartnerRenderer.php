<?php

namespace Webmakerr\App\Modules\PaymentMethods\PayPalGateway\API;

use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\Services\FrontendView;
use Webmakerr\App\Vite;
use Webmakerr\Framework\Foundation\App;
use Webmakerr\Framework\Support\Arr;

class PayPalPartnerRenderer
{

    protected $mode;

    public function __construct($mode)
    {
        if (!$mode) {
            echo '<div class="fct_message fct_message_error">' . esc_html__('Invalid PayPal payment mode. Please try configuring paypal payment gateway again expected mode test or live !', 'webmakerr-cart') . '</div>';
            die();
        }

        $this->mode = $mode;
    }

    public function render($data)
    {
        $this->template($data);
        die();
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function scriptJs(): string
    {
        if ($this->mode == 'test') {
            return 'https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
        }
        return 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
    }

    public function template($data)
    {
        $mode = Arr::get($data, 'mode', false);
        if (!$mode) {
            return;
        }
        $paypalPartner = new PayPalPartner($mode);


        try {
            $redirectUrl = $paypalPartner->sellerOnboarding();

            if (is_wp_error($redirectUrl)) {
                FrontendView::renderNotFoundPage(
                    __('Error', 'webmakerr-cart'),
                    __('Error', 'webmakerr-cart'),
                    __('Please setup your store country and currency first. ', 'webmakerr-cart') . '<a href="' . admin_url('admin.php?page=webmakerr#/settings/store-settings/') . '">' . __('Settings', 'webmakerr-cart') . '</a>',

                    __('Go Back to the Store', 'webmakerr-cart'),
                    null,
                    admin_url('admin.php?page=webmakerr#/settings/payments')
                );
                die();
            }

        } catch (\Exception $exception) {
            FrontendView::renderNotFoundPage(
                __('Unable To connect with PayPal.', 'webmakerr-cart'),
                __('Please try again in a moment.', 'webmakerr-cart'), '',
                __('Go Back to the Store', 'webmakerr-cart'),
                null,
                admin_url('admin.php?page=webmakerr#/settings/payments')
            );
            die();

        }

        $logo = Vite::getAssetUrl('images/logo/logo-full-dark.svg');
        $baseUrl = admin_url('admin.php?page=webmakerr#/');


        App::make('view')->render('paypal.authenticate', [
            'url'       => $redirectUrl,
            'scriptJs'  => $this->scriptJs(),
            'mode'      => $mode,
            'rest'      => Helper::getRestInfo(),
            'logo'      => $logo,
            'admin_url' => $baseUrl
        ]);
    }
}