<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_system_id',
        'secret_key',
        'merchant_id',
        'api_version',
        'status'
    ];

    protected $table = 'merchants';

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
     * @param string $key
     * @return string
     */
    public static function cryptSecretKey(string $key): string
    {
        return Crypt::encrypt($key);
    }

    /**
     * @param string $hash
     * @return string
     */
    public static function decryptSecretKey(string $hash): string
    {
        return Crypt::decrypt($hash);
    }

    /**
     * @return string
     */
    public function getDecryptedSecretKeyAttribute(): string
    {
        return self::decryptSecretKey($this->secret_key);
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeActive($query): void
    {
        $query->where(self::getTableName() . '.status', self::STATUS_ACTIVE);
    }

    public function paymentSystem(): BelongsTo
    {
        return $this->belongsTo(PaymentSystem::class);
    }

}
