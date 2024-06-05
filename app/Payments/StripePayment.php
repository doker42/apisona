<?php

namespace App\Payments;

use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePayment extends AbstractPayment
{

    public ?string $customerId = null;

    public $verificationClearHistory = true;

    public const STATUS_MAPPER = [
        'complete'  => parent::STATUS_APPROVED,
        'open'      => parent::STATUS_PROCESSING,
        'expired'   => parent::STATUS_EXPIRED,
    ];

    public const SUPPORTED_LOCALES = [
        "auto", "bg", "cs", "da", "de", "el", "en", "es", "et", "fi", "fr", "he", "hr", "hu", "id", "it", "ja", "ko",
        "lt", "lv", "ms", "mt", "nb", "nl", "pl", "pt", "ro", "ru", "sk", "sl", "sv", "th", "tr", "vi", "zh"
    ];

    public string $systemName = 'stripe';
    protected function initBootstrap(): void
    {
       $this->client = new StripeClient($this->merchant->decrypted_secret_key);
    }

    /**
     * @param  Request|array  $data
     * @return bool|array|string
     * @throws ApiErrorException
     */
    public function resultData(Request|array $data): bool|array|string
    {
        if(is_array($data) && array_key_exists('session_id', $data)){
            $sessionId = $data['session_id'];
        }else{
            $sessionId = Payment::where('id', Crypt::decrypt($data->query('payment_id')))->withTrashed()->value('session_id');
        }

        $data = $this->client->checkout->sessions->retrieve($sessionId);

        if($data){
            $this->customerId = $data->customer;
            return $this->result = $data->toArray();
        }

        return false;
    }

    public function retrieveSession($sessionId): array
    {
        return  $this->client->checkout->sessions->retrieve($sessionId)->toArray();
    }

    public function parseResultData(array $data): array
    {
        return $data;
    }

    /**
     * @param  array  $merchantData
     * @return array|false|mixed|null
     */
    public function getVerificationUrl(array $merchantData = []): mixed
    {
        return $this->checkout = self::setCheckout($merchantData, [],true);
    }

    private function getLocale($language): string
    {
        $locale = self::SUPPORTED_LOCALES[0];
        if(in_array($language, self::SUPPORTED_LOCALES)){
            $locale = $language;
        }
        return $locale;
    }

    public function getVerificationData(array $parameters = []): array
    {
        $language = $this->getLocale($parameters['language'] ?? config('app.locale'));
        $merchantData = array_merge($this->getMerchantData(), $parameters ?? []);

        return [
            'mode' => 'setup',
            'payment_method_types' => ['card'],
            'customer' => $parameters['customer'] ?? null,
            'locale' => $language,
            'success_url' => self::generateResponseUrl($this->paymentSystem->response_url, self::STATUS_APPROVED, $parameters['payment_id'] ?? null),
            'cancel_url' => self::generateResponseUrl($this->paymentSystem->response_url, self::STATUS_DECLINED, $parameters['payment_id'] ?? null),
            'metadata' => $merchantData,
        ];
    }

    /**
     * @param  array  $parameters
     * @return array
     */
    public function getCheckoutData(array $parameters = []): array
    {
        $language = $this->getLocale($parameters['language'] ?? config('app.locale'));
        $merchantData = array_merge($this->getMerchantData(), $parameters ?? []);

        return [
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $this->getCurrencyCode(),
                        'product_data' => [
                            'name' => $this->getOrderDescription(),
                        ],
                        'unit_amount' => $this->getAmount() * self::UNIT_AMOUNT_MULTIPLIER,
                    ],
                    'quantity' => 1,
                ]
            ],
            'customer_creation' => 'always',
            'payment_intent_data' => [
                'setup_future_usage' => 'off_session',
            ],
            'mode' => 'payment',
            'customer_email' => $merchantData['email'] ?? null,
            'locale' => $language,
            'success_url' => self::generateResponseUrl($this->paymentSystem->response_url, self::STATUS_APPROVED, $parameters['payment_id'] ?? null),
            'cancel_url' => self::generateResponseUrl($this->paymentSystem->response_url, self::STATUS_DECLINED, $parameters['payment_id'] ?? null),
            'metadata' => $merchantData,
        ];
    }

    /**
     * @param  array  $merchantData
     * @param  array  $overrideData
     * @param  bool  $addCard
     * @return bool|Session
     */
    public function setCheckout(array $merchantData = [], array $overrideData = [], bool $addCard = false): bool|Session
    {
        try {
            if($addCard){
                $checkoutData = $this->getVerificationData($merchantData);
            }else{
                $checkoutData = $this->getCheckoutData($merchantData);
            }


            if(!empty($overrideData)){
                $checkoutData = array_merge($checkoutData, $overrideData);
            }
            if ($checkoutData) {
                $this->checkout = $this->client->checkout->sessions->create($checkoutData);
                return $this->checkout;
            }
        } catch (Exception $e) {
            $this->setError($e);
            return false;
        }
        return false;
    }

    /**
     * @param  array  $merchantData
     * @return array|false|PaymentIntent
     */
    public function makeRecurringPayment(array $merchantData = []): PaymentIntent|bool|array
    {
        try {
            $this->result =  $this->client->paymentIntents->create([
                'amount'         => $this->getAmount() * self::UNIT_AMOUNT_MULTIPLIER, // Amount in cents
                'confirm'        => true,
                'currency'       => $this->getCurrencyCode(),
                'metadata'       => $merchantData,
                'customer'       => $this->card->token_properties['customer'],
                'off_session'    => true,
                'payment_method' => $this->card->token_properties['token'],
            ]);

            $this->result = $this->result->toArray();
            $this->result['payment_intent'] = $this->result['id'];

            $this->isRecurringPaymentSuccess = $this->result['status'] == 'succeeded';

            return $this->result;

        } catch (Exception $e) {
            $this->setError($e);
            return false;
        }
    }

    protected function generateResponseUrl(string $url, ?string $status = null, ?int $paymentId = null): string
    {
        return env('API_URL').'/'.$url.'/'.$this->merchant->id.'?'.http_build_query([
                'status' => $status,
                'payment_id' => Crypt::encrypt($paymentId),
            ]);
    }

    public function getResult(){
        return $this->checkout->toArray() ?? null;
    }

    public function getCheckoutUrl()
    {
        return $this->checkout->url ?? null;
    }

    public function getCheckoutId()
    {
        return $this->checkout->id ?? null;
    }
    public function getResultAll(): string
    {
        return $this->result;
    }
    public function getResultLanguage(): string
    {
        return $this->result['metadata']['language'] ?? 'en';
    }

    public function getResultOrderId(): ?string
    {
        return $this->result['metadata']['order_id'] ?? null;
    }

    public function getResultPaymentId(): ?string
    {
        return $this->result['metadata']['payment_id'] ?? null;
    }

    public function getResultOrderStatus(): string
    {
        return self::STATUS_MAPPER[$this->result['status']];
    }

    /**
     * @return string|null
     */
    public function getPaymentId(): ?string
    {
        return $this->result['payment_intent'] ?? null;
    }

    public function getEventPaymentIntendId(): string
    {
        return $this->event->data->object->payment_intent;
    }

    public function getResultSubscriptionId()
    {
        if (isset($this->result['metadata']['subscription_id'])) {
            return $this->result['metadata']['subscription_id'];
        }
        return null;
    }

    public function getResultProjectId()
    {
        return $this->result['metadata']['project_id'] ?? null;
    }

    public function getResultCardData(): array
    {
        $paymentMethods = $this->client->paymentMethods->all([
            'customer' => $this->customerId,
        ]);

        $paymentCard = $paymentMethods->first();

        return [
            'token_properties' => [
                'customer'  => $this->customerId,
                'token'     => $paymentCard->id,
            ],
            'card_type'         => strtolower($paymentCard->card->brand),
            'masked_card'       => '000000XXXXXX'.$paymentCard->card->last4,
            'rectoken_lifetime' => Carbon::createFromDate($paymentCard->card->exp_year, $paymentCard->card->exp_month, 1),
            'payment_system_id' => $this->id,
        ];
    }

    /**
     * @param  Request  $request
     * @return bool|Event
     */
    public function getWebhookData(Request $request): bool|Event
    {
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $payload = $request->getContent();
        self::saveLog($request->all());
        try {

            return $this->event = Webhook::constructEvent(
                $payload, $sigHeader, env('WEB_HOOK_SECURITY_KEY')
            );

        } catch (\UnexpectedValueException|SignatureVerificationException $e) {
            $error = [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ];
            self::saveLog($error,self::LOG_TYPE_ERROR);
            return false;
        }
    }

    public function getWebhookEvent()
    {
        return $this->event;
    }

    public function getWebhookEventType():string
    {
        return $this->event->type;
    }
}