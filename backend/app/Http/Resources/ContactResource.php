<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class ContactResource extends JsonResource
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
            'date' =>  DateHelper::getInstance()->customDateFormatWithTime($this->date) ,
            'method' =>  (int)$this->method ,
            'description' =>  $this->description ,
            'contact_reason'=>new ContactReasonResource($this->contact_reason),
            'type'=>new TypeResource($this->type),
            'client'=>new ClientResource($this->client),
            'admin'=>new AdminResource($this->admin),
        ];
    }
}
