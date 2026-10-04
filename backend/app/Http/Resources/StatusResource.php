<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class StatusResource extends JsonResource
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
            'color' =>  $this->color ,
            'sort' => (int)$this->sort,
            'type'=>$this->types ? TypeResource::collection($this->types) : null,
        ];
    }
}
