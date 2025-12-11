<?php

namespace Webmakerr\App\Http\Controllers;

use Webmakerr\App\App;
use Webmakerr\App\Helpers\AddressHelper;
use Webmakerr\App\Services\Localization\LocalizationManager;
use Webmakerr\Framework\Http\Request\Request;

class AddressInfoController extends Controller
{
    public function countriesOption(): \WP_REST_Response
    {
        $countries = LocalizationManager::getInstance()->countriesOptions();
        return $this->sendSuccess([
            'data' => $countries
        ]);
    }

    public function getCountryInfo(Request $request): \WP_REST_Response
    {
        $timezone = sanitize_text_field($request->get('timezone'));

        if ($timezone) {
            $countryCode = LocalizationManager::guessCountryFromTimezone($timezone);
        } else {
            $countryCode = sanitize_text_field($request->get('country_code'));
        }

        $states = LocalizationManager::getInstance()->statesOptions($countryCode);
        $addressLocale = LocalizationManager::getInstance()->addressLocales($countryCode);

        return $this->sendSuccess([
            'country_code'   => $countryCode,
            'country_name'   => AddressHelper::getCountryNameByCode($countryCode),
            'states'         => $states,
            'address_locale' => $addressLocale
        ]);
    }
}
