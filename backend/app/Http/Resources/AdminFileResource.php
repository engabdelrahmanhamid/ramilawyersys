<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class AdminFileResource extends JsonResource
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
            'date' =>  $this->date ,
            'file' =>  ImageHelper::getInstance()->getImageUrl('AdminFile',$this->file) ,
        ];
    }
}
