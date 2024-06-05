<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    protected $table = 'accounts';

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }


    /**
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): object|null
    {
        $subscriptions = $this->subscriptions;

        return count($subscriptions) ? $subscriptions->first() : null;
    }

    /**
     * @return HasOne
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status','=',Subscription::STATUS_CURRENT);
    }

    /**
     * @param string $option
     * @param bool $value
     * @return bool|string
     */
    public function hasOption(string $option, bool $value = false): bool|string
    {
        $option_value = $this->subscriptions()?->current()->first()->planRange?->plan?->optionValue($option);

        $option_value = $option_value === '-1'  ? Option::OPTION_UNLIM_VALUE : $option_value;

        return $value
            ? $option_value                 // if $value string Option->value return ? $value === $option_value
            : (bool)$option_value;
    }


    /**
     * @param  string|null  $role
     * @return Collection|false
     */
    public function getAllUsersByRole(?string $role = null): bool|Collection
    {
        $projects = $this->projects()->with([
            'projectUsers.roles' => function ($q) use ($role) {
                if ($role) {
                    $q->where('name', $role);
                }
            }
        ])->get();

        if ($projects) {
            $userIds = $projects
                ->pluck('projectUsers')
                ->flatten()
                ->filter(function ($value, $key) {
                    return $value->roles->isNotEmpty();
                })
                ->unique('user_id')
                ->pluck('user_id')
                ->toArray();

            return User::whereIn('id', $userIds)->get();
        }

        return false;
    }

}
