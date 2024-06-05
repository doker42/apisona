<?php

namespace App\Models;

use App\Payments\AbstractPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends AbstractModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'plan_range_id',
        'currency_id',
        'type',
        'merchant_id',
        'card_id',
        'subscription_id',
        'payment_system_id',
        'session_id',
        'zoho_deal_id',
        'payment_id',
        'created_at',
        'amount',
        'status',
    ];

    protected $table = 'payments';


    public const STATUS_CREATED     = 'created';
    public const STATUS_PROCESSING  = 'processing';
    public const STATUS_APPROVED    = 'approved';
    public const STATUS_DECLINED    = 'declined';
    public const STATUS_EXPIRED     = 'expired';
    public const STATUS_REVERSED    = 'reversed';


    public const TYPE_EXPIRE       = 'expire';
    public const TYPE_ORDER        = 'order';
    public const TYPE_SITE_ORDER   = 'site_order';
    public const TYPE_UPGRADE      = 'upgrade';
    public const TYPE_VERIFICATION = 'verification';

    /**
     * @return array
     */
    public function types(): array
    {
        return self::getConstants('TYPE_');
    }

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

    public function account(): object
    {
        return $this->belongsTo(Account::class);
    }

    public function planRange(): object
    {
        return $this->belongsTo(PlanRange::class, 'plan_range_id');
    }

    public function currency(): object
    {
        return $this->belongsTo(Currency::class);
    }

    public function card(): object
    {
        return $this->belongsTo(Card::class);
    }

    public function subscription(): object
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @param  string  $type
     * @param  AbstractPayment  $paymentSystem
     * @param  Subscription|null  $subscription
     * @param  Card|null  $card
     * @param  string|null  $status
     * @param  Order|null  $order
     * @return mixed
     */
    public static function createPayment(
        string $type,
        AbstractPayment $paymentSystem,
        ?Subscription $subscription = null,
        ?Card $card = null,
        ?string $status = null,
        ?Order $order = null,
    ): mixed
    {
        if(is_null($status)){
            $status =  $order ? $order->status : Payment::STATUS_CREATED;
        }

        return self::create([
            'type'              => $type,
            'status'            => $status,
            'amount'            => $paymentSystem->getAmount(),
            'card_id'           => $card?->id,
            'payment_id'        => $paymentSystem->getResultPaymentId() ?? null,
            'account_id'        => $subscription?->account->id,
            'merchant_id'       => $paymentSystem->getMerchant()?->id,
            'currency_id'       => $subscription?->currency_id ?? $order?->planRange->currency_id ?? $paymentSystem->getCurrencyId(),
            'zoho_deal_id'      => $order?->zoho_deal_id,
            'plan_range_id'     => $subscription?->plan_range_id ?? $order?->planRange->id ?? 0,
            'subscription_id'   => $subscription?->id,
            'payment_system_id' => $paymentSystem->getPaymentSystem()?->id,
        ]);
    }

    /**
     * @param  Order  $order
     * @param  string  $paymentId
     * @param  int|null  $cardId
     * @param  int|null  $subscriptionId
     * @param  int|null  $accountId
     * @return $this
     */
    public function updateOnBoardingPayment(Order $order , string $paymentId, ?int $cardId = null, ?int $subscriptionId = null, ?int $accountId = null): static
    {
        $updateData = [
            'status'            => $order->status,
            'zoho_deal_id'      => $order->zoho_deal_id,
        ];

        if($paymentId){
            $updateData['payment_id'] = $paymentId;
        }

        if($cardId){
            $updateData['card_id'] = $cardId;
        }

        if($subscriptionId){
            $updateData['subscription_id'] = $subscriptionId;
        }

        if($accountId){
            $updateData['account_id'] = $accountId;
        }

        $this->update($updateData);

        return $this;
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }


    public function paymentSystem()
    {
        return $this->belongsTo(PaymentSystem::class);
    }

}
