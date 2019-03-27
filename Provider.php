<?php


namespace LaravelPayment\PaymentSberbank;


use LaravelPayment\Manager\Contracts\Payment\Provider as ProviderContract;

class Provider implements ProviderContract
{

    /**
     * Redirect to payment form
     *
     * @return \Illuminate\Http\Response
     */
    public function process()
    {
        // TODO: Implement process() method.
    }

    public function result()
    {
        // TODO: Implement result() method.
    }
}
