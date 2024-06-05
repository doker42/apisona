<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends AbstractModel
{
    use HasFactory, SoftDeletes;

    public const ENTERPRISE_ID = 3;

    protected $table = 'plans';

    protected $fillable = [
        'slug',
        'icon',
        'title',
        'custom',
        'available',
        'parent_id',
        'account_id',
        'currency_id',
        'description',
    ];

    /**
     * @return BelongsToMany
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'plan_option')->withPivot('value');
    }


    /**
     * @return HasMany
     */
    public function planOptions(): hasMany
    {
        return $this->hasMany(PlanOption::class);
    }

    /**
     * @return HasMany
     */
    public function ranges(): HasMany
    {
        return $this->hasMany(PlanRange::class)->where('trial', false);
    }

    /**
     * @return HasMany
     */
    public function personalRanges(): HasMany
    {
        return $this->hasMany(PlanRange::class);
    }

    /**
     * @param $option_name
     * @return null|string
     */
    public function optionValue($option_name): null|string
    {
        $options = $this->options;

        $option = $options->filter(function ($value, $key) use ($option_name) {
            return  $value->name == $option_name;
        })->first();

        return $option?->pivot->value;
    }

    /**
     * @param  Builder  $query
     * @return mixed
     */
    public function scopeAvailable(Builder $query)
    {
        return $this->where('available', true);
    }

    /**
     * @param  Builder  $query
     * @return mixed
     */
    public function scopeNotPersonal(Builder $query)
    {
        return $this->whereNull('parent_id');
    }

    public static function personalCreate(Account $account, Plan $parentPlan)
    {
        return self::create([
            'slug'        => $parentPlan->slug,
            'icon'        => $parentPlan->icon,
            'title'       => $parentPlan->title,
            'custom'      => $parentPlan->custom,
            'available'   => $parentPlan->available,
            'parent_id'   => $parentPlan->id,
            'account_id'  => $account->id,
            'currency_id' => $parentPlan->currency_id,
            'description' => $parentPlan->description,
        ]);
    }
}
