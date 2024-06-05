<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    public const TYPE_MASTERCARD = 'MASTERCARD';
    public const TYPE_VISA = 'VISA';

    protected $fillable = [
        'account_id',
        'user_id',
        'order_id',
        'payment_system_id',
        'card_pan',
        'card_type',
        'rectoken_lifetime',
        'token_properties',
        'default',
    ];

    protected $table = 'cards';

    protected $dates = ['rectoken_lifetime'];

    protected $casts = [
        'token_properties' => 'json',
    ];

    public function account(): object
    {
        return $this->belongsTo(Account::class);
    }

    public function order(): object
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentSystem(): object
    {
        return $this->belongsTo(PaymentSystem::class);
    }

    public function subscriptions(): object
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeRectokenIsLive($query){
        $query->whereDate('rectoken_lifetime', '>', Carbon::now());
    }

    /**
     * @return bool
     */
    public function isRecTokenLive(): bool
    {
        return $this->rectoken_lifetime > Carbon::now()
            &&  in_array($this->card_type, [self::TYPE_MASTERCARD, self::TYPE_VISA]);
    }

    /**
     * @param $query
     * @return void
     */
    public function scopeDefault($query): void
    {
        $query->where('default', true);
    }

    /**
     * @return string[]
     */
    public function getCardPanChunkAttribute(): array
    {
        return explode('XXXXXX', $this->card_pan);
    }

    /**
     * @param  Order  $order
     * @param  User  $user
     * @param  Account  $account
     * @param  array  $result
     * @return Card
     */
    public static function createOnBoarding(Order $order, User $user, Account $account, array $result): Card
    {
        return self::create([
            'user_id'           => $user->id,
            'account_id'        => $account->id,
            'order_id'          => $order->id,
            'card_pan'          => $result['masked_card'],
            'token_properties'  => $result['token_properties'],
            'card_type'         => strtolower($result['card_type']),
            'default'           => true,
            'rectoken_lifetime' => $result['rectoken_lifetime'],
            'payment_system_id' => $result['payment_system_id'],
        ]);
    }

    /**
     * @param  Account  $account
     * @param  array  $cardData
     * @param  int  $paymentSystemId
     * @param  bool  $default
     * @return Card
     */
    public static function createOrUpdate(Account $account, array $cardData, int $paymentSystemId, bool $default = true): Card
    {
        $card = self::where([
            'user_id'    => $account->user->id,
            'account_id' => $account->id,
            'card_pan'   => $cardData['masked_card'],
        ])->first();

        if ($card) {
            if ($default) {
                $card->default = true;
            }
            $card->rectoken_lifetime = $cardData['rectoken_lifetime'];
        } else {
            $card = self::create([
                'user_id'           => $account->user->id,
                'card_pan'          => $cardData['masked_card'],
                'token_properties'  => $cardData['token_properties'],
                'card_type'         => strtolower($cardData['card_type']),
                'account_id'        => $account->id,
                'rectoken_lifetime' => $cardData['rectoken_lifetime'],
                'payment_system_id' => $paymentSystemId,
                'default'           => $default,
            ]);
        }
        if ($default) {
            Card::where('user_id', $card->user_id)
                ->whereNotIn('id', [$card->id])
                ->update(['default' => false]);
        }

        return $card;
    }

    /**
     * @return mixed|null
     */
    public function getCustomerAttribute(): mixed
    {
        return $this->token_properties['customer'] ?? null;
    }

}
