<?php

namespace Webmakerr\App\Helpers;

use Webmakerr\Api\CurrencySettings;
use FLuentCart\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}


class CurrenciesHelper
{
    /**
     * https://support.stripe.com/questions/which-currencies-does-stripe-support
     */
    public static function getCurrencies($code = null)
    {
        $currencies = webmakerr_apply_filters('webmakerr_cart/accepted_currencies', array(
            'AED' => __('United Arab Emirates Dirham', 'webmakerr-cart'),
            'AFN' => __('Afghan Afghani', 'webmakerr-cart'),
            'ALL' => __('Albanian Lek', 'webmakerr-cart'),
            'AMD' => __('Armenian Dram', 'webmakerr-cart'),
            'ANG' => __('Netherlands Antillean Gulden', 'webmakerr-cart'),
            'AOA' => __('Angolan Kwanza', 'webmakerr-cart'),
            'ARS' => __('Argentine Peso', 'webmakerr-cart'), // non amex
            'AUD' => __('Australian Dollar', 'webmakerr-cart'),
            'AWG' => __('Aruban Florin', 'webmakerr-cart'),
            'AZN' => __('Azerbaijani Manat', 'webmakerr-cart'),
            'BAM' => __('Bosnia & Herzegovina Convertible Mark', 'webmakerr-cart'),
            'BBD' => __('Barbadian Dollar', 'webmakerr-cart'),
            'BDT' => __('Bangladeshi Taka', 'webmakerr-cart'),
            'BIF' => __('Burundian Franc', 'webmakerr-cart'),
            'BGN' => __('Bulgarian Lev', 'webmakerr-cart'),
            'BMD' => __('Bermudian Dollar', 'webmakerr-cart'),
            'BND' => __('Brunei Dollar', 'webmakerr-cart'),
            'BOB' => __('Bolivian Boliviano', 'webmakerr-cart'),
            'BRL' => __('Brazilian Real', 'webmakerr-cart'),
            'BSD' => __('Bahamian Dollar', 'webmakerr-cart'),
            'BWP' => __('Botswana Pula', 'webmakerr-cart'),
            'BYN' => __('Belarusian Ruble', 'webmakerr-cart'),
            'BZD' => __('Belize Dollar', 'webmakerr-cart'),
            'CAD' => __('Canadian Dollar', 'webmakerr-cart'),
            'CDF' => __('Congolese Franc', 'webmakerr-cart'),
            'CHF' => __('Swiss Franc', 'webmakerr-cart'),
            'CLP' => __('Chilean Peso', 'webmakerr-cart'),
            'CNY' => __('Chinese Renminbi Yuan', 'webmakerr-cart'),
            'COP' => __('Colombian Peso', 'webmakerr-cart'),
            'CRC' => __('Costa Rican Colón', 'webmakerr-cart'),
            'CVE' => __('Cape Verdean Escudo', 'webmakerr-cart'),
            'CZK' => __('Czech Koruna', 'webmakerr-cart'),
            'DJF' => __('Djiboutian Franc', 'webmakerr-cart'),
            'DKK' => __('Danish Krone', 'webmakerr-cart'),
            'DOP' => __('Dominican Peso', 'webmakerr-cart'),
            'DZD' => __('Algerian Dinar', 'webmakerr-cart'),
            'EGP' => __('Egyptian Pound', 'webmakerr-cart'),
            'ETB' => __('Ethiopian Birr', 'webmakerr-cart'),
            'EUR' => __('Euro', 'webmakerr-cart'),
            'FJD' => __('Fijian Dollar', 'webmakerr-cart'),
            'FKP' => __('Falkland Islands Pound', 'webmakerr-cart'),
            'GBP' => __('British Pound', 'webmakerr-cart'),
            'GEL' => __('Georgian Lari', 'webmakerr-cart'),
            'GIP' => __('Gibraltar Pound', 'webmakerr-cart'),
            'GMD' => __('Gambian Dalasi', 'webmakerr-cart'),
            'GNF' => __('Guinean Franc', 'webmakerr-cart'),
            'GTQ' => __('Guatemalan Quetzal', 'webmakerr-cart'),
            'GYD' => __('Guyanese Dollar', 'webmakerr-cart'),
            'HKD' => __('Hong Kong Dollar', 'webmakerr-cart'),
            'HNL' => __('Honduran Lempira', 'webmakerr-cart'),
            'HRK' => __('Croatian Kuna', 'webmakerr-cart'),
            'HTG' => __('Haitian Gourde', 'webmakerr-cart'),
            'HUF' => __('Hungarian Forint', 'webmakerr-cart'),
            'IDR' => __('Indonesian Rupiah', 'webmakerr-cart'),
            'ILS' => __('Israeli New Sheqel', 'webmakerr-cart'),
            'INR' => __('Indian Rupee', 'webmakerr-cart'),
            'IRR'  => __('Iranian Rial', 'webmakerr-cart'),
            'ISK' => __('Icelandic Króna', 'webmakerr-cart'),
            'JMD' => __('Jamaican Dollar', 'webmakerr-cart'),
            'JPY' => __('Japanese Yen', 'webmakerr-cart'),
            'KES' => __('Kenyan Shilling', 'webmakerr-cart'),
            'KGS' => __('Kyrgyzstani Som', 'webmakerr-cart'),
            'KHR' => __('Cambodian Riel', 'webmakerr-cart'),
            'KMF' => __('Comorian Franc', 'webmakerr-cart'),
            'KRW' => __('South Korean Won', 'webmakerr-cart'),
            'KYD' => __('Cayman Islands Dollar', 'webmakerr-cart'),
            'KZT' => __('Kazakhstani Tenge', 'webmakerr-cart'),
            'LAK' => __('Lao Kip', 'webmakerr-cart'),
            'LBP' => __('Lebanese Pound', 'webmakerr-cart'),
            'LKR' => 'Sri Lankan Rupee',
            'LRD' => __('Liberian Dollar', 'webmakerr-cart'),
            'LSL' => __('Lesotho Loti', 'webmakerr-cart'),
            'MAD' => __('Moroccan Dirham', 'webmakerr-cart'),
            'MDL' => __('Moldovan Leu', 'webmakerr-cart'),
            'MGA' => __('Malagasy Ariary', 'webmakerr-cart'),
            'MKD' => __('Macedonian Denar', 'webmakerr-cart'),
            'MMK' => __('Myanmar Kyat', 'webmakerr-cart'),
            'MNT' => __('Mongolian Tögrög', 'webmakerr-cart'),
            'MOP' => __('Macanese Pataca', 'webmakerr-cart'),
            'MRO' => __('Mauritanian Ouguiya', 'webmakerr-cart'),
            'MUR' => __('Mauritian Rupee', 'webmakerr-cart'),
            'MVR' => __('Maldivian Rufiyaa', 'webmakerr-cart'),
            'MWK' => __('Malawian Kwacha', 'webmakerr-cart'),
            'MXN' => __('Mexican Peso', 'webmakerr-cart'),
            'MYR' => __('Malaysian Ringgit', 'webmakerr-cart'),
            'MZN' => __('Mozambican Metical', 'webmakerr-cart'),
            'NAD' => __('Namibian Dollar', 'webmakerr-cart'),
            'NGN' => __('Nigerian Naira', 'webmakerr-cart'),
            'NIO' => __('Nicaraguan Córdoba', 'webmakerr-cart'),
            'NOK' => __('Norwegian Krone', 'webmakerr-cart'),
            'NPR' => __('Nepalese Rupee', 'webmakerr-cart'),
            'NZD' => __('New Zealand Dollar', 'webmakerr-cart'),
            'PAB' => __('Panamanian Balboa', 'webmakerr-cart'),
            'PEN' => __('Peruvian Nuevo Sol', 'webmakerr-cart'),
            'PGK' => __('Papua New Guinean Kina', 'webmakerr-cart'),
            'PHP' => __('Philippine Peso', 'webmakerr-cart'),
            'PKR' => __('Pakistani Rupee', 'webmakerr-cart'),
            'PLN' => __('Polish Złoty', 'webmakerr-cart'),
            'PYG' => __('Paraguayan Guaraní', 'webmakerr-cart'),
            'QAR' => __('Qatari Riyal', 'webmakerr-cart'),
            'RON' => __('Romanian Leu', 'webmakerr-cart'),
            'RSD' => __('Serbian Dinar', 'webmakerr-cart'),
            'RUB' => __('Russian Ruble', 'webmakerr-cart'),
            'RWF' => __('Rwandan Franc', 'webmakerr-cart'),
            'SAR' => __('Saudi Riyal', 'webmakerr-cart'),
            'SBD' => __('Solomon Islands Dollar', 'webmakerr-cart'),
            'SCR' => __('Seychellois Rupee', 'webmakerr-cart'),
            'SEK' => __('Swedish Krona', 'webmakerr-cart'),
            'SGD' => __('Singapore Dollar', 'webmakerr-cart'),
            'SHP' => __('Saint Helenian Pound', 'webmakerr-cart'),
            'SLL' => __('Sierra Leonean Leone', 'webmakerr-cart'),
            'SOS' => __('Somali Shilling', 'webmakerr-cart'),
            'SRD' => __('Surinamese Dollar', 'webmakerr-cart'),
            'STD' => __('São Tomé and Príncipe Dobra', 'webmakerr-cart'),
            'SVC' => __('Salvadoran Colón', 'webmakerr-cart'),
            'SZL' => __('Swazi Lilangeni', 'webmakerr-cart'),
            'THB' => __('Thai Baht', 'webmakerr-cart'),
            'TJS' => __('Tajikistani Somoni', 'webmakerr-cart'),
            'TOP' => __('Tongan Paʻanga', 'webmakerr-cart'),
            'TRY' => __('Turkish Lira', 'webmakerr-cart'),
            'TTD' => __('Trinidad and Tobago Dollar', 'webmakerr-cart'),
            'TWD' => __('New Taiwan Dollar', 'webmakerr-cart'),
            'TZS' => __('Tanzanian Shilling', 'webmakerr-cart'),
            'UAH' => __('Ukrainian Hryvnia', 'webmakerr-cart'),
            'UGX' => __('Ugandan Shilling', 'webmakerr-cart'),
            'USD' => __('United States Dollar', 'webmakerr-cart'),
            'UYU' => __('Uruguayan Peso', 'webmakerr-cart'),
            'UZS' => __('Uzbekistani Som', 'webmakerr-cart'),
            'VND' => __('Vietnamese Đồng', 'webmakerr-cart'),
            'VUV' => __('Vanuatu Vatu', 'webmakerr-cart'),
            'WST' => __('Samoan Tala', 'webmakerr-cart'),
            'XAF' => __('Central African Cfa Franc', 'webmakerr-cart'),
            'XCD' => __('East Caribbean Dollar', 'webmakerr-cart'),
            'XOF' => __('West African Cfa Franc', 'webmakerr-cart'),
            'XPF' => __('Cfp Franc', 'webmakerr-cart'),
            'YER' => __('Yemeni Rial', 'webmakerr-cart'),
            'ZAR' => __('South African Rand', 'webmakerr-cart'),
            'ZMW' => __('Zambian Kwacha', 'webmakerr-cart'),
        ), []);

        if ($code) {
            return isset($currencies[$code]) ? $currencies[$code] : '';
        }

        return $currencies;
    }

    /**
     * Get the available locales that Stripe can use
     *
     * @return array
     */
    public static function getLocales()
    {
        return array(
            ''     => 'English (en) (default)',
            'auto' => 'Auto-detect locale',
            'zh'   => 'Simplified Chinese (zh)',
            'da'   => 'Danish (da)',
            'nl'   => 'Dutch (nl)',
            'fi'   => 'Finnish (fi)',
            'fr'   => 'French (fr)',
            'de'   => 'German (de)',
            'it'   => 'Italian (it)',
            'ja'   => 'Japanese (ja)',
            'no'   => 'Norwegian (no)',
            'es'   => 'Spanish (es)',
            'sv'   => 'Swedish (sv)',
        );
    }

    public static function getCurrencySigns()
    {
        return webmakerr_apply_filters('webmakerr_cart/global_currency_symbols', [
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => 'Afl.',
			'AZN' => '&#8380;',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BYN' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'kr.',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x20be;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x62f;.&#x639;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => '&#8376;',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x62f;.&#x644;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRU' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => 'N&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#1088;&#1089;&#1076;',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STN' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'E',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VES' => 'Bs.',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'CFA',
			'XCD' => '&#36;',
			'XOF' => 'CFA',
			'XPF' => 'XPF',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		], []);
    }

    public static function getCurrencySign($currency = 'USD')
    {
        $currency = strtoupper($currency);
        $symbols = static::getCurrencySigns();
        return $symbols[$currency] ?? '';
    }

    public static function getCurrencyWithSign($currencies = [])
    {
        $currencyWithSign = [];

        foreach ($currencies as $currency) {
            $currencyWithSign[$currency] = self::getCurrencySign($currency);
        }

        return $currencyWithSign;
    }

    public static function zeroDecimalCurrencies()
    {
        return webmakerr_apply_filters('webmakerr_cart/zero_decimal_currencies', array(
            'BIF' => esc_html__('Burundian Franc', 'webmakerr-cart'),
            'CLP' => esc_html__('Chilean Peso', 'webmakerr-cart'),
            'DJF' => esc_html__('Djiboutian Franc', 'webmakerr-cart'),
            'GNF' => esc_html__('Guinean Franc', 'webmakerr-cart'),
            'JPY' => esc_html__('Japanese Yen', 'webmakerr-cart'),
            'KMF' => esc_html__('Comorian Franc', 'webmakerr-cart'),
            'KRW' => esc_html__('South Korean Won', 'webmakerr-cart'),
            'MGA' => esc_html__('Malagasy Ariary', 'webmakerr-cart'),
            'PYG' => esc_html__('Paraguayan Guaraní', 'webmakerr-cart'),
            'RWF' => esc_html__('Rwandan Franc', 'webmakerr-cart'),
            'VND' => esc_html__('Vietnamese Dong', 'webmakerr-cart'),
            'VUV' => esc_html__('Vanuatu Vatu', 'webmakerr-cart'),
            'XAF' => esc_html__('Central African Cfa Franc', 'webmakerr-cart'),
            'XOF' => esc_html__('West African Cfa Franc', 'webmakerr-cart'),
            'XPF' => esc_html__('Cfp Franc', 'webmakerr-cart'),
        ), []);
    }

    public static function isZeroDecimal($currencyCode): bool
    {
        $currencyCode = strtoupper($currencyCode);
        $zeroDecimals = self::zeroDecimalCurrencies();
        return isset($zeroDecimals[$currencyCode]);
    }

    public static function updateCurrencyArray($oldCurrencies = [], $amount = 0, $currency = ''): array
    {
        if (!Arr::exists($oldCurrencies, $currency)) {
            $oldCurrencies[$currency] = [
                'currency' => $currency,
                'total'    => 0,
                'count'    => 0,
                'sign'     => '$'
            ];
        }

        $oldCurrencies[$currency]['total'] += $amount;
        $oldCurrencies[$currency]['count']++;
        $oldCurrencies[$currency]['sign'] = static::getCurrencySign($currency);

        return $oldCurrencies;
    }

    public static function centsToDecimal($cents, $currency = null)
    {

        if (!$currency) {
            $currency = CurrencySettings::get('currency');
        }

        if (self::isZeroDecimal($currency)) {
            return $cents;
        }

        return number_format($cents / 100, 2, '.', '');
    }
}