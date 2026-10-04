<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class SettingResource extends JsonResource
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
            'project_name' =>  $this->project_name ,
            'logo' =>  ImageHelper::getInstance()->getImageUrl('Setting',$this->logo) ,
            'start_of_case_receipt' =>  (int)$this->start_of_case_receipt ,
            'increase_amount_case_receipt' =>  (int)$this->increase_amount_case_receipt ,
            'start_of_service_receipt' =>  (int)$this->start_of_service_receipt ,
            'increase_amount_service_receipt' =>  (int)$this->increase_amount_service_receipt ,
        ];
    }
}
