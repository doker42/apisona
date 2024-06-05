<?php

namespace App\Payments;

use Cloudipsp\Checkout;
use Cloudipsp\Configuration;
use Cloudipsp\Exception\ApiException;
use Cloudipsp\Payment;
use Cloudipsp\Response\Response;
use Cloudipsp\Result\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FondyPayment extends AbstractPayment
{
    public string $systemName = 'fondy';

    public $verificationClearHistory = false;

    protected function initBootstrap(): void
    {
        if (isset($this->merchant->merchant_id)) {
            Configuration::setMerchantId($this->merchant->merchant_id);
            Configuration::setSecretKey($this->merchant->decrypted_secret_key);
            Configuration::setApiVersion($this->merchant->api_version);
        }
    }

    /**
     * @return bool
     */
    public function isResultValid(): bool
    {
        return true;
    }

    /**
     * @param  array  $parameters
     * @return array
     */
    public function getCheckoutData(array $parameters = []): array
    {

        $language = $parameters['language'] ?? config('app.locale');
        $merchantData =  array_merge($this->getMerchantData(), $parameters ?? []);
        $rectoken = isset($parameters['rectoken']) ? ['rectoken' => $parameters['rectoken']] : [];
        return array_merge([
            'currency' => $this->getCurrencyCode(),
            'amount' => $this->getAmount() * self::UNIT_AMOUNT_MULTIPLIER,
            'order_id' => $this->getOrderId(),
            'order_desc' => $this->getOrderDescription(),
            'response_url' => $this->generateResponseUrl($this?->paymentSystem->response_url),
            'server_callback_url' => $this->generateResponseUrl($this?->paymentSystem->callback_url),
            'lang' => $language,
            'delayed' => 'Y',
            'required_rectoken' => 'Y',
            'merchant_data' => $merchantData
        ], $rectoken);
    }

    /**
     * @param $data
     * @return array|false|string
     */
    public function resultData(Request|array $data): bool|array|string
    {
        $result = new Result($data->all());
        if ($result->isValid()) {
            return $this->result = self::parseResultData($result->getData());
        }
        return false;
    }

    public function parseResultData(array $data): array
    {
        $data['order_time'] = Carbon::parse($data['order_time'] ?? null);
        $data['rectoken_lifetime'] = Carbon::parse($data['rectoken_lifetime'] ?? null);
        $data['merchant_data'] = json_decode($data['merchant_data'] ?? null, true);

        self::saveLog($data);
        return $data;
    }



    /**
     * @param  array  $merchantData
     * @return array
     * @throws ApiException
     */
    public function getVerificationUrl(array $merchantData = []): array
    {

        $overrideData = [
            'verification_type' => 'amount',
            'verification' => 'Y',
            'amount' => self::VERIFICATION_AMOUNT * self::UNIT_AMOUNT_MULTIPLIER,
            'currency' => 'USD',
        ];

        return $this->checkout = self::setCheckout($merchantData, $overrideData);

    }

    /**
     * @return array|mixed|void
     */
    public function setCheckout(array $merchantData = [], array $overrideData = [])
    {
        try {
            $checkoutData = $this->getCheckoutData($merchantData);

            if(!empty($overrideData)){
                $checkoutData = array_merge($checkoutData, $overrideData);
            }

            if ($checkoutData) {
                $this->checkout = Checkout::url($checkoutData)->getData();
                return $this->checkout;
            }
        } catch (\Exception $e) {
            $this->setError($e);
            return false;
        }
    }

    /**
     * @param  array  $merchantData
     * @return array|Response|false
     */
    public function makeRecurringPayment(array $merchantData = [])
    {

        try {
            $merchantData = array_merge($merchantData, ['rectoken' => $this->getToken()]);
            $checkoutData = $this->getCheckoutData($merchantData);
            $this->result = Payment::recurring($checkoutData);
            $this->result = self::parseResultData($this->result->getData());
            self::saveLog($this->result);
            $this->isRecurringPaymentSuccess = true;
            return $this->result;
        } catch (\Exception $e) {
            $this->setError($e);
            return false;
        }
    }


    public function getCheckoutUrl()
    {
        return $this->checkout['checkout_url'] ?? null;
    }

    public function getCheckoutId()
    {
        return null;
    }

    public function getEventPaymentIntendId(): ?string
    {
        return null;
    }

    public function getWebhookEventType(): ?string
    {
        return null;
    }
}