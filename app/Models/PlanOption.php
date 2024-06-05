<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PlanOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'option_id',
        'value'
    ];

    protected $table = 'plan_option';

    /**
     * @return BelongsTo
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    /**
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public static function personalCreate(Plan $parentPlan, Plan $personalPlan)
    {
        $options = $parentPlan->options;
        $plan_options = [];

        foreach ($options as $option) {
            $plan_options[] = [
                'plan_id'    => (int)$personalPlan->id,
                'option_id'  => (int)$option->pivot->option_id,
                'value'      => (int)$option->pivot->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ];
        }
        DB::table('plan_option')->insert($plan_options);
    }
}
