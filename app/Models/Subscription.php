<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'plan_range_id',
        'currency_id',
        'payment_system_id',
        'card_id',
        'amount',
        'status',
        'regular_mode',
        'expires',
        'payment_tries',
        'renew',
    ];

    protected $casts = [
        'expires' => 'date'
    ];

    public const REGULAR_MODE_MONTHLY = 'monthly';
    public const REGULAR_MODE_QUARTERLY = 'quarterly';
    public const REGULAR_MODE_YEARLY = 'yearly';

    public const STATUS_CURRENT = 'current';
    public const STATUS_UPGRADE = 'upgrade';

    public const MAX_PAYMENT_TRIES = 3;

    public const OVERDUE_DAYS = 14;

    protected $table = 'subscriptions';

    public function account(): object
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return array
     */
    public static function statuses(): array
    {
        return self::getConstants('STATUS_');
    }
    /**
     * @return BelongsTo
     */
    public function planRange(): BelongsTo
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

    /**
     * @param $query
     * @return void
     */
    public function scopeExpires($query): void
    {
        $query->whereDate('expires', '<=', Carbon::now());
    }

    public function scopeAccount($query, $accountId): void
    {
        $query->where('account_id', $accountId);
    }

    /**
     * @param $query
     * @param  bool  $value
     * @return void
     */
    public function scopeRenew($query,bool $value): void
    {
        $query->where('renew', $value);
    }

    /**
     * @return float|int
     */
    public function getInvoiceIdAttribute(): float|int
    {
        return microtime(true)*1000000;
    }

    /**
     * @return array
     */
    public function getMerchantData():array
    {
        return ['subscription_id' => $this->id];
    }
    /**
     * @param Order $order
     * @param User $user
     * @param Card $card
     * @param $result
     * @return mixed
     */
    public static function createOnBoarding(Order $order, User $user, Card $card, $result)
    {
        $planRange = PlanRange::find($order->plan_range_id);

        return self::create([
            'account_id'        => $user->account->id,
            'plan_range_id'     => $planRange->id,
            'currency_id'       => $planRange->currency_id,
            'card_id'           => $card->id,
            'payment_system_id' => $card->payment_system_id,
            'amount'            => $planRange->amount,
            'regular_mode'      => $planRange->regular_mode,
            'amount_per_month'  => $planRange->getAmountPerMonth(),
            'expires'           => $planRange->getExpires()
        ]);
    }

    /**
     * @return bool
     */
    public function isToBlock(): bool
    {
        return $this->payment_tries >= self::MAX_PAYMENT_TRIES;
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeCurrent($query): void
    {
        $query->where('status', self::STATUS_CURRENT);
            //->where('deleted_at', NULL);
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeUpgrade($query): void
    {
        $query->where('status', self::STATUS_UPGRADE);
    }

    /**
     * @param  Subscription  $currentSubscription
     * @param  PlanRange  $upgradableSubscription
     * @return array
     */
    public static function calculateReimbursement(self $currentSubscription, PlanRange $upgradableSubscription): array
    {
        $discountCurrent = 0;

        /** @var Carbon $expiresDate */
        $expiresDate = $currentSubscription->expires->clone();
        $expiresDate->endOfDay();

        $dateFrom = $expiresDate->clone();
        switch ($currentSubscription->regular_mode) {
            case self::REGULAR_MODE_MONTHLY:
                $dateFrom->subMonth();
                break;
            case self::REGULAR_MODE_QUARTERLY:
                $dateFrom->subQuarter();
                break;
            case self::REGULAR_MODE_YEARLY:
                $dateFrom->subYear();
                break;
        }

        $dateNow = Carbon::now()->endOfDay();

        $type = 'upgrade';

        if($currentSubscription->plan_range_id === $upgradableSubscription->id){
            $type = 'continue_plan';
        }

        if ($dateNow->gte($expiresDate)) {
            $resultPrice = $upgradableSubscription->amount;
        } else {
            $daysLeftToExpired = $dateNow->diffInDays($expiresDate);

            $daysInPlan = $expiresDate->endOfDay()->diffInDays($dateFrom);
            $priceForDay = $currentSubscription->planRange->amount / $daysInPlan;
            $newPrice = $upgradableSubscription->amount;
            $discountCurrent = $priceForDay * $daysLeftToExpired;
            $resultPrice = round($newPrice - $discountCurrent, 2);
        }
        $newExpires = self::getNewExpirationDate($upgradableSubscription);
        return [
            'type'             => $type,
            'new_expires'      => $newExpires,
            'result_price'     => $resultPrice,
            'discount_current' => round($discountCurrent, 2),
        ];
    }

    /**
     * @param  PlanRange  $upgradableSubscription
     * @return Carbon
     */
    public static function getNewExpirationDate(PlanRange $upgradableSubscription): Carbon
    {
        $newExpirationDate = Carbon::now()->endOfDay();

        switch ($upgradableSubscription->regular_mode) {
            case self::REGULAR_MODE_MONTHLY:
                $newExpirationDate->addMonth();
                break;
            case self::REGULAR_MODE_QUARTERLY:
                $newExpirationDate->addQuarter();
                break;
            case self::REGULAR_MODE_YEARLY:
                $newExpirationDate->addYear();
                break;
        }
        return  $newExpirationDate;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires->lte(Carbon::now());
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->expires >= Carbon::now();
    }

    /**
     * @param $expirationDate
     * @return void
     */
    public function setExpiries($expirationDate): void
    {
        switch ($this->regular_mode) {
            case self::REGULAR_MODE_MONTHLY:
                $this->expires = $expirationDate->addMonth();
                break;
            case self::REGULAR_MODE_YEARLY:
                $this->expires = $expirationDate->addYear();
                break;
            case self::REGULAR_MODE_QUARTERLY:
                $this->expires = $expirationDate->addQuarter();
                break;
        }
    }

}
