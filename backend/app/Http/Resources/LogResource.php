<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class LogResource extends JsonResource
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
            'model_name' =>  $this->model_name ,
            'description' =>  $this->description ,
            'operation' =>  $this->operation ,
            'admin_name' => $this->admin? $this->admin->name : null ,
            'admin_id' =>  $this->admin_id ,
            'created_at' =>  DateHelper::getInstance()->customDateFormatWithTime($this->created_at) ,
            'replayes'=>new ReplayResource($this->replayes),
        ];
    }
}
