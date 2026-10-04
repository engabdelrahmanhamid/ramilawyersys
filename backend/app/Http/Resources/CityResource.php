<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' =>  $this->name ,
            'country_id' =>  (int)$this->country_id ,
            'districts'=>DistrictResource::collection($this->districts)
        ];
    }
}
