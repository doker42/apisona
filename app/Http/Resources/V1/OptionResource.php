<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        $value = $this->pivot->value;
        if($value === false){
            $value = 0;
        }

        if($value === true){
            $value = 1;
        }

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'value'      => $value,
        ];
    }
}
