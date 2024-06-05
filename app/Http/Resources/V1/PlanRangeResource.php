<?php

namespace App\Http\Resources\V1;

use App\Models\PlanRange;
use App\Models\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanRangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        $use = false;
        /** @var PlanRange $this */
        if (user()) {
            $subscription = $this
                ->subscriptions()
                ->account(user()->account->id)
                ->current()
                ->first();

            if ($subscription) {
                $use = true;
            }
        }

        $expired = false;

        if($use && $subscription?->isExpired()){
            $expired = true;
        }

        return [
            'id'               => $this->id,
            'use'              => $use,
            'amount'           => $this->amount,
            'weight'           => $this->weight,
            'expired'          => $expired,
            'plan_id'          => $this->plan_id,
            'discount'         => $this->discount,
            'currency'         => $this->currency,
            'regular_mode'     => $this->regular_mode,
            'amount_per_month' => $this->getAmountPerMonth(),
        ];
    }
}
