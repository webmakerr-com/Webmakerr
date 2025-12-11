<?php

namespace Webmakerr\App\Modules\PaymentMethods\Core;

use Webmakerr\App\Services\Payments\PaymentInstance;

interface PaymentGatewayInterface
{
    public function has(string $feature): bool;
    public function meta(): array;
    public function makePaymentFromPaymentInstance(PaymentInstance $paymentInstance);
    public function handleIPN();
    public function getOrderInfo(array $data);
    public function fields();
}
