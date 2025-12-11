<?php

namespace Webmakerr\App\Services\ShortCodeParser\Parsers;

use Webmakerr\Api\CurrencySettings;
use Webmakerr\Api\StoreSettings;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\App\Models\Order;
use Webmakerr\App\Services\DateTime\DateTime;
use Webmakerr\App\Services\Payments\PaymentReceipt;
use Webmakerr\Framework\Support\Arr;
use Webmakerr\Framework\Support\Str;

class TransactionParser extends BaseParser
{
    private $transaction;

    public function __construct($data)
    {
        $this->transaction = Arr::get($data, 'transaction');
        parent::__construct($data);
    }


    protected array $centColumns = [
        'total'
    ];

    public function parse($accessor = '', $code = '', $transformer = null): ?string
    {

        if (empty($this->transaction)) {
            return $code;
        }
        if (in_array($accessor, $this->centColumns)) {
            $amount = Arr::get($this->transaction, $accessor);
            if (!is_numeric($amount)) {
                return $amount;
            }
            return CurrencySettings::getPriceHtml(
                $amount,
                Arr::get($this->transaction, 'currency')
            );
        }
        return $this->get($accessor, $code);
    }

    public function getRefundAmount()
    {
        $refundAmount = Arr::get($this->transaction, 'total', 0);
        return CurrencySettings::getPriceHtml(
            $refundAmount,
            Arr::get($this->transaction, 'currency')
        );
    }

}


