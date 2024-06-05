<?php

namespace App\Models;

use App\Http\Resources\V1\OptionResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_range_id',
        'zoho_deal_id',
        'invoice_id',
        'status',
        'name',
        'email',
        'phone',
        'country',
        'language',
        'industry',
        'position',
        'job_title',
        'company_name',
    ];

    protected $table = 'orders';

    public const STATUS_CREATED     = 'created';
    public const STATUS_PROCESSING  = 'processing';
    public const STATUS_APPROVED    = 'approved';
    public const STATUS_DECLINED    = 'declined';
    public const STATUS_EXPIRED     = 'expired';
    public const STATUS_REVERSED    = 'reversed';
    public const PAYMENT_CUSTOM     = 'payment_custom';
    public const PAYMENT_SYSTEM_ID_DEFAULT = 2;


    protected $attributes = [
        'status' => self::STATUS_CREATED,
    ];


    /**
     * @return string[]
     */
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
     * @return string[]
     */
    public static function orderFailed(): array
    {
        return [
            self::STATUS_PROCESSING,
            self::STATUS_DECLINED,
            self::STATUS_EXPIRED,
        ];
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->invoice_id = substr(microtime(true)*10000,3);
        });
    }

    /**
     * @return array
     */
    public function getMerchantData():array
    {
        return ['order_id' => $this->id];
    }

    /**
     * @return BelongsTo
     */
    public function planRange(): BelongsTo
    {
        return $this->belongsTo(PlanRange::class, 'plan_range_id');
    }

    public static function changeStatus(int $invoiceId, string $orderStatus)
    {
        $order = self::invoice($invoiceId)->first();
        if($order){
            $order->status = $orderStatus;
            return $order;
        }
        return false;
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeApproved($query): void
    {
        $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * @param $query
     * @param $invoiceId
     * @return void
     */
    public function scopeInvoice($query, $invoiceId): void
    {
        $query->where('invoice_id', $invoiceId);
    }

    /**
     * @return float|int
     */
    public function getAmountAttribute(): float|int
    {
        return $this->planRange->amount;
    }

    /**
     * @param $email
     * @return array|null
     */
    public static function onBoardingPlan($email): array|null
    {
        $order = self::where([
            'email'  => $email,
            'status' => self::STATUS_APPROVED
        ])->first();

        $planRange = $order?->planRange;

        if ($planRange) {
            $planRange = [
                'id'               => $planRange->id,
                'title'            => $planRange->plan->title,
                'description'      => $planRange->plan->description,
                'currency_id'      => $planRange->currency_id,
                'amount'           => $planRange->amount,
                'amount_per_month' => $planRange->getAmountPerMonth(),
                'discount'         => $planRange->discount,
                'regular_mode'     => $planRange->regilar_mode,
                'available'        => $planRange->plan->available,
                'options'          => OptionResource::collection($planRange->plan->options)
            ];
        }

        return $planRange;
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }
}
