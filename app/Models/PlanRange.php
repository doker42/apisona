<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PlanRange extends AbstractModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trial',
        'amount',
        'weight',
        'plan_id',
        'discount',
        'parent_id',
        'currency_id',
        'regular_mode',
        'crm_product_id',
    ];

    protected $table = 'plan_ranges';

    public const REGULAR_MODE_YEARLY    = 'yearly';
    public const REGULAR_MODE_MONTHLY   = 'monthly';
    public const REGULAR_MODE_QUARTERLY = 'quarterly';

    public const ENTERPRISE_YEARLY_ID   = 5;


    public const TRIAL_DAYS = 30;
    /**
     * @return array
     */
    public static function regularModes(): array
    {
        return self::getConstants('REGULAR_MODE');
    }

    /**
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasOne
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * @return HigherOrderBuilderProxy|mixed
     */
    public function getOriginalAmount(): mixed
    {
        return $this->getOriginalPlanRange()->amount;
    }

    public function getOriginalCrmProductId(): mixed
    {
        return $this->getOriginalPlanRange()->crm_product_id;
    }


    public static function getCrmProductId(int $plan_range_id)
    {
        $plan_range = self::find($plan_range_id);

        return $plan_range?->getOriginalCrmProductId();
    }


    /**
     * @return int
     */
    public function getOriginalId(): int
    {
        if ($this->isTrial()) {
            return $this->getOriginalPlanRange()->id;
        }
        return $this->parent_id ?? $this->id;
    }

    public function getOriginalPlanRange()
    {
        if ($this->isTrial()) {
            return self::query()
                ->where('plan_id', '=', function ($query) {
                    $query->select('plan_id')
                        ->from('plan_ranges')
                        ->where('id', '=', $this->parent_id);
                })
                ->where('regular_mode', '=', $this->regular_mode)
                ->where('trial', '=', false)
                ->first();
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isTrial(): bool
    {
        return $this->trial;
    }

    /**
     * @return string
     */
    public function getPlanTitle(): string
    {
        return $this->plan->title;
    }

    /**
     * @return float
     */
    public function getAmountPerMonth(): float
    {
        return match ($this->regular_mode) {
            self::REGULAR_MODE_YEARLY => $this->getOriginalAmount() / 12,
            self::REGULAR_MODE_QUARTERLY => $this->getOriginalAmount() / 3,
            self::REGULAR_MODE_MONTHLY => $this->getOriginalAmount(),
        };
    }

    /**
     * @param  int  $planRangeId
     * @return static|null
     */
    public static function getTrialPlanRange(int $planRangeId): ?self
    {
        $plan = self::where('id', '=', $planRangeId)->first();
        if ($plan) {
            return self::where('plan_id', $plan->plan_id)
                ->where('regular_mode', $plan->regular_mode)
                ->where('trial', true)
                ->first();
        }

        return $plan;
    }

    public static function personalCreate(Plan $personalPlan, PlanRange $parentPlanRange): self
    {
        return self::create([
            'plan_id'        => $personalPlan->id,
            'parent_id'      => $parentPlanRange->id,
            'currency_id'    => $parentPlanRange->currency_id,
            'weight'         => $parentPlanRange->weight,
            'trial'          => $parentPlanRange->trial,
            'amount'         => $parentPlanRange->amount,
            'discount'       => $parentPlanRange->discount,
            'regular_mode'   => $parentPlanRange->regular_mode,
            'crm_product_id' => $parentPlanRange->crm_product_id,
        ]);
    }

    /**
     * @return string
     */
    public  function getExpires(): string
    {

        if($this->isTrial()){
            return Carbon::now()->addDays(self::TRIAL_DAYS)->endOfDay();
        }

        return match($this->regular_mode) {
            self::REGULAR_MODE_MONTHLY   => Carbon::now()->addMonth()->endOfDay(),
            self::REGULAR_MODE_QUARTERLY => Carbon::now()->addQuarter()->endOfDay(),
            self::REGULAR_MODE_YEARLY    => Carbon::now()->addYear()->endOfDay(),
        };
    }

}
