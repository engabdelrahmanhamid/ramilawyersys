<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\TaskHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class NotificationResource extends JsonResource
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
            'model_type' =>  $this->model_type ,
            'title' =>  $this->title ,
            'description' =>  $this->description ,
            'is_read' => (int)$this->is_read ,
            'admin'=>new AdminResource($this->admin),
            'date'=>DateHelper::getInstance()->customDateFormatWithTime($this->created_at)
        ];
    }
}

