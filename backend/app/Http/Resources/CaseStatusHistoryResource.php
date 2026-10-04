<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class CaseStatusHistoryResource extends JsonResource
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
            'current_status'=>new CaseStatusResource($this->current_status),
            'old_status'=>new CaseStatusResource($this->old_status),
            'created_at' =>  DateHelper::getInstance()->customDateFormatWithTime($this->created_at) ,
        ];
    }
}
