<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'default',
        'callback_url',
        'response_url',
        'allowed_callback_ip',

    ];

    protected $table = 'payment_systems';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_NOT_ACTIVE = 'not_active';

    /**
     * @return string[]
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_NOT_ACTIVE
        ];
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeActive($query): void
    {
        $query->where(self::getTableName() . '.status', self::STATUS_ACTIVE);
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeDefault($query): void
    {
        $query->where(self::getTableName() . '.default', true);
    }


    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }

}
