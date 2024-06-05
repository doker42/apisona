<?php

namespace App\Payments;

use App\Models\Card;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentSystem;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class AbstractPayment
{
    private ?object $client;

    public $verificationClearHistory = false;
    public $result;
    public $event = null;
    public $checkout;
    public int $id;


    public bool $isRecurringPaymentSuccess = false;

    public string $systemName = 'default';

    protected ?Merchant $merchant;
    protected ?PaymentSystem $paymentSystem;
    protected ?Card $card;

    private Order $order;
    protected Subscription $subscription;

    protected ?string $mode = null;


    public const STATUS_CREATED = 'created';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVERSED = 'reversed';

    public const LOG_TYPE_ERROR = 'error';
    public const LOG_TYPE_DEBUG = 'debug';
    public const LOG_TYPE_INFO  = 'info';

    public const PAYMENT_TYPE_BOARDING = 'boarding';
    public const PAYMENT_TYPE_SUBSCRIPTION = 'subscription';


    public const MODE_VERIFICATION = 'verification';
    public const MODE_UPGRADE = 'upgrade';


    public const VERIFICATION_AMOUNT = 1;
    public const UNIT_AMOUNT_MULTIPLIER = 100;

    public int $amount = 0;
    public int $currencyId = 1;

    public static function statuses(): array
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_PROCESSING,
            self::STATUS_APPROVED,
            self::STATUS_DECLINED,
            self::STATUS_EXPIRED,
            self::STATUS_REVERSED,
        ];
    }

    /**
     * @param Merchant|null $merchant
     * @param PaymentSystem|null $paymentSystem
     * @param Card|null $card
     */
    public function __construct(?Merchant $merchant = null, ?PaymentSystem $paymentSystem = null, ?Card $card = null)
    {
        $this->id = $paymentSystem->id ?? 0;
        $this->card = $card;
        $this->merchant = $merchant;
        $this->paymentSystem = $paymentSystem;

        $this->initConfiguration();
    }

    abstract protected function initBootstrap(): void;
    abstract public function setCheckout(array $merchantData = []);
    abstract public function getCheckoutUrl();
    abstract public function getCheckoutId();
    abstract public function makeRecurringPayment(array $merchantData = []);
    abstract public function getVerificationUrl(array $merchantData = []);
    abstract public function resultData(Request|array $data): bool|array|string;
    abstract public function parseResultData(array $data):array;

    /**
     * @return bool
     */
    public function getVerificationClearedHistory(): bool
    {
        return $this->verificationClearHistory;
    }

    /**
     * @return void
     */
    protected function initConfiguration(): void
    {
        $this->setDefaultPaymentSystem();
        $this->setDefaultMerchant();
        $this->initBootstrap();
    }


    public function isResultValid(): bool
    {
        return is_array($this->result);
    }

    /**
     * @param  string  $mode
     * @return void
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return void
     */
    private function setDefaultMerchant(): void
    {
        if(is_null($this->merchant)){
            $this->merchant = Merchant::active()->where('payment_system_id', $this->paymentSystem->id)->first();
        }
    }

    /**
     * @return Merchant|null
     */
    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    /**
     * @return PaymentSystem|null
     */
    public function getPaymentSystem(): ?PaymentSystem
    {
        return $this->paymentSystem;
    }

    /**
     * @return void
     */
    private function setDefaultPaymentSystem(): void
    {
        if (is_null($this->paymentSystem)) {
            $ps = PaymentSystem::where('slug', $this->systemName)->active()->first();

            if ($ps) {
                $this->paymentSystem = $ps;
            } else {
                $query = PaymentSystem::default()->active();
                if (!is_null($this->merchant)) {
                    $query = $query->where('id', $this->merchant->payment_system_id);
                }
                $this->paymentSystem = $query->first();
            }
        }
    }

    /**
     * @param Order $order
     * @return void
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * @param Subscription $subscription
     * @return void
     */
    public function setSubscription(Subscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    /**
     * @param Card $card
     * @return void
     */
    public function setCard(Card $card): void
    {
        $this->card = $card;
    }


    /**
     * @param array $parameters
     * @return array
     */
    public function getCheckoutData(array $parameters = []): array
    {
        $language = config('app.locale');
        $merchantData = $this->getMerchantData();
        if (array_key_exists('merchant_data', $parameters)) {
            $merchantData = array_merge($merchantData, $parameters['merchant_data']);
            unset($parameters['merchant_data']);
        }

        if (array_key_exists('language', $parameters)) {
            $language = $parameters['language'];
            $merchantData['language'] = $language;
            unset($parameters['language']);
        }

        return array_merge([
            'currency'            => $this->getCurrencyCode(),
            'amount'              => $this->getAmount() * 100,
            'order_id'            => $this->getOrderId(),
            'order_desc'          => $this->getOrderDescription(),
            'response_url'        => $this->generateResponseUrl($this?->paymentSystem->response_url),
            'server_callback_url' => $this->generateResponseUrl($this?->paymentSystem->callback_url),
            'lang'                => $language,
            'delayed'             => 'Y',
            'required_rectoken'   => 'Y',
            'merchant_data'       => $merchantData
        ], $parameters);
    }

    /**
     * @return Order|Subscription|null
     */
    protected function getEntity(): Order|Subscription|null
    {
        $entity = null;
        if(isset($this->order)){
            $entity = $this->order;
        }
        if(isset($this->subscription)){
            $entity = $this->subscription;
        }

        return $entity;

    }

    /**
     * @return mixed|null
     */
    protected function getOrderId(): mixed
    {
        $entity = $this->getEntity();
        return $entity?->invoice_id;
    }

    /**
     * @return array
     */
    protected function getMerchantData(): array
    {
        $entity = $this->getEntity();
        return $entity?->getMerchantData() ?? [];
    }

    /**
     * @return string|null
     */
    protected function getOrderDescription()
    {
        $entity = $this->getEntity();
        $title = $entity?->planRange->plan->title;
        if ($entity?->planRange->isTrial()) {
            $title = $title.' '.__('(trial)');
        }
        return $title;
    }

    /**
     * @return string|null
     */
    public function getAmount(): ?string
    {
        $entity = $this->getEntity();
        return $this->amount > 0 ? $this->amount : $entity?->amount;
    }

    /**
     * @return string|null
     */
    protected function getCurrencyCode(): ?string
    {
       $entity = $this->getEntity();
        if($entity){
            return strtoupper($entity->planRange->currency->code);
        }

        return null;
    }

    /**
     * @param  string  $url
     * @param  string|null  $status
     * @param  int|null  $paymentId
     * @return string
     */
    protected function generateResponseUrl(string $url, ?string $status = null, ?int $paymentId = null): string
    {
        return env('API_URL').'/'.$url.'/'.$this->merchant->id;
    }

    /**
     * @param $data
     * @param string $type
     * @return void
     */
    public static function saveLog($data, string $type = self::LOG_TYPE_INFO): void
    {
        $channelName = 'gcloud';

        if(env('APP_ENV') === 'local'){
            $channelName = 'payments';
        }

        $logChannel = Log::channel($channelName);

        switch ($type) {
            case self::LOG_TYPE_ERROR:
                $logChannel->error($data);
                break;
            case self::LOG_TYPE_DEBUG:
                $logChannel->info($data);
                break;
            case self::LOG_TYPE_INFO:
            default:
                $logChannel->info($data);
        }
    }

    /**
     * @return string
     */
    public function getResultLanguage(): string
    {
        return $this->result['merchant_data']['language'] ?? 'en';
    }

    public function getResultOrderStatus(): string
    {
        return $this->result['order_status'];
    }

    public function getResultTransactionType(): string
    {
        return $this->result['tran_type'] ?? self::STATUS_DECLINED;
    }

    public function getResultOrderId(): ?string
    {
        if (isset($this->result['merchant_data']['order_id'])) {
            return $this->result['merchant_data']['order_id'] ?? null;
        }
        return null;
    }

    public function getResultPaymentId(): ?string
    {
        return $this->result['merchant_data']['payment_id'] ?? null;
    }

    public function getResultSubscriptionId()
    {
        return $this->result['merchant_data']['subscription_id'] ?? null;
    }

    public function getResultProjectId()
    {
        return $this->result['merchant_data']['project_id'] ?? null;
    }

    public function getPaymentId(): ?string
    {
        return $this->result['payment_id'] ?? null;
    }

    public function getResultCardData(): array
    {
        return [
            'token_properties'  => [
                'token' => $this->result['rectoken'],
            ],
            'card_type'         => strtolower($this->result['card_type']),
            'masked_card'       => $this->result['masked_card'],
            'rectoken_lifetime' => $this->result['rectoken_lifetime'],
            'payment_system_id' => $this->id,
        ];
    }

    public function getWebhookData(Request $request){
        return false;
    }

    public function getWebhookEvent()
    {
        return $this->event;
    }

    /**
     * @return mixed
     */
    public function getToken(): mixed
    {
        return $this->card ? $this->card->token_properties['token'] : $this->subscription->card->token_properties['token'];
    }

    /**
     * @return bool
     */
    public function isPaymentSuccess(): bool
    {
        return $this->isRecurringPaymentSuccess;
    }

    /**
     * @param  \Exception  $e
     * @return void
     */
    public function setError(\Exception $e): void
    {
        $error = [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ];
        self::saveLog($error, self::LOG_TYPE_ERROR);
    }

    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

}