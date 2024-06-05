<?php

namespace App\Http\Resources\V1;

use App\Models\Plan;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Plan $this */

        return [
            'id'            => $this->id,
            'icon'          => $this->icon,
            'title'         => $this->title,
            'custom'        => $this->custom,
            'description'   => $this->description,
            'ranges'        => PlanRangeResource::collection($this->ranges),
            'options'       => OptionResource::collection($this->options),
        ];
    }
}
