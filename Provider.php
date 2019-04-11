<?php


namespace LaravelPayment\PaymentSberbank;

use LaravelPayment\Manager\Exceptions\Payment\AuthException;
use LaravelPayment\Manager\Exceptions\Payment\CurrencyUnknownException;
use LaravelPayment\Manager\Exceptions\Payment\InvalidArgumentException;
use LaravelPayment\Manager\Exceptions\Payment\OrderNumberException;
use LaravelPayment\Manager\Exceptions\Payment\UnknownException;
use LaravelPayment\Manager\Models\Payment;
use LaravelPayment\Manager\Payment\Results\CallbackResult;
use LaravelPayment\Manager\Payment\Results\ProcessResult;
use LaravelPayment\Manager\Payment\Results\StatusResult;
use LaravelPayment\Manager\Payment\WithoutCallbackProviderAbstract;
use LaravelPayment\Manager\Support\RequestClient;

class Provider extends WithoutCallbackProviderAbstract
{

    const API_URI = 'https://securepayments.sberbank.ru';
    const API_URI_TEST = 'https://3dsec.sberbank.ru';

    const CURRENCY_EUR = 978;
    const CURRENCY_RUB = 643;
    const CURRENCY_UAH = 980;
    const CURRENCY_USD = 840;

    const ORDER_STATUS_REGISTER = 0;
    const ORDER_STATUS_PRE_AUTH_SUM = 1;
    const ORDER_STATUS_SUCCESS = 2;
    const ORDER_STATUS_CANCEL = 3;
    const ORDER_STATUS_REFUND = 4;
    const ORDER_STATUS_3D_SEC = 5;
    const ORDER_STATUS_DECLINED = 6;

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
            ->setDataType(RequestClient::DATA_TYPE_JSON)
            ->setBaseURL($this->testMode ? self::API_URI_TEST : self::API_URI);

    }

    public function callback($data): CallbackResult
    {

    }

    public function process($orderNumber, $currency, $amount): ProcessResult
    {
        $response = $this->client->request('/payment/rest/register.do', RequestClient::METHOD_POST, [
            'orderNumber' => 'testm' . $orderNumber,
            'amount'      => $amount * 100,
            'returnUrl'   => $this->getRouteCheck(),
            'failUrl'     => $this->getRouteCheck(),
            'currency'    => self::CURRENCY_RUB,
            'language'    => $this->config['language'] ?? 'ru',
        ]);

        if (!empty($response['errorCode'])) {
            switch ($response['errorCode']) {
                case 1:
                    throw new OrderNumberException($response['errorMessage']);
                case 3:
                    throw new CurrencyUnknownException($response['errorMessage']);
                case 4:
                    throw new InvalidArgumentException($response['errorMessage']);
                case 5:
                    throw new AuthException($response['errorMessage']);
                case 7:
                case 13:
                case 14:
                default:
                    throw new UnknownException($response['errorMessage']);
            }
        }

        $result = new ProcessResult();
        $result->providerOrderId = $response['orderId'];
        $result->redirectUrl = $response['formUrl'];

        return $result;
    }

    public function status($data): StatusResult
    {
        $response = $this->client->request('/payment/rest/getOrderStatusExtended.do', RequestClient::METHOD_POST, [
            'orderId' => $data['orderId'],
        ]);

        $result = new StatusResult();
        $result->providerOrderId = $data['orderId'];
        $result->response = $response;

        switch ($response['orderStatus']) {
            case self::ORDER_STATUS_REGISTER:
                $result->status = Payment::STATUS_NEW;
                break;
            case self::ORDER_STATUS_PRE_AUTH_SUM:
                $result->status = Payment::STATUS_PRE_AUTH_SUM;
                break;
            case self::ORDER_STATUS_SUCCESS:
                $result->status = Payment::STATUS_SUCCESS;
                break;
            case self::ORDER_STATUS_CANCEL:
                $result->status = Payment::STATUS_CANCEL;
                break;
            case self::ORDER_STATUS_REFUND:
                $result->status = Payment::STATUS_REFUND;
                break;
            case self::ORDER_STATUS_3D_SEC:
                $result->status = Payment::STATUS_PROCESS;
                break;
            case self::ORDER_STATUS_DECLINED:
                $result->status = Payment::STATUS_DECLINE;
                break;
        }

        return $result;
    }


}
