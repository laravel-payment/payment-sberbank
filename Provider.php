<?php


namespace LaravelPayment\PaymentSberbank;

use LaravelPayment\Manager\Payment\ProviderAbstract;
use LaravelPayment\Manager\Support\RequestClient;

class Provider extends ProviderAbstract
{

    const API_URI = 'https://securepayments.sberbank.ru';
    const API_URI_TEST = 'https://3dsec.sberbank.ru';

    const CURRENCY_EUR = 978;
    const CURRENCY_RUB = 643;
    const CURRENCY_UAH = 980;
    const CURRENCY_USD = 840;

    public function boot()
    {
        if (!empty($this->config['token'])) {
            $data = [
                'token' => $this->config['token'],
            ];
        } else {
            $this->checkServiceConfig($this->config, ['username', 'password']);

            $data = [
                'userName' => $this->config['username'],
                'password' => $this->config['password'],
            ];
        }

        $this->client
            ->setGlobalData($data)
            ->setDataType(RequestClient::DATA_TYPE_JSON);

    }


    /**
     * Redirect to payment form
     *
     * @return \Illuminate\Http\Response
     */
    public function process()
    {
        // TODO: Implement process() method.
    }

    public function callback()
    {
        // TODO: Implement callback() method.
    }

    public function success()
    {
        // TODO: Implement success() method.
    }

    public function fail()
    {
        // TODO: Implement fail() method.
    }


}
