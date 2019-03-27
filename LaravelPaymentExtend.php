<?php


namespace LaravelPayment\PaymentSberbank;


use LaravelPayment\Manager\Events\PaymentServiceBooted;

class LaravelPaymentExtend
{
    public function handle(PaymentServiceBooted $paymentBooted)
    {
        $paymentBooted->extendService('sberbank', __NAMESPACE__ . '\Provider');
    }
}
