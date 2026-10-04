<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\TaskHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class TaskResource extends JsonResource
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
            'model' =>  TaskHelper::getInstance()->getRelationModel($this) ,
            'end_date' =>  $this->end_date ,
            'time_remaining'=>DateHelper::getInstance()->convertToTime($this->end_date),
            'description' =>  $this->description ,
            'status' =>  (int)$this->status,
            'is_overdue' => $this->isOverdue(),
            'comment' =>  $this->comment,
            'task_type'=>new TypeResource($this->task_type),
            'created_by'=>new AdminResource($this->created_by),
            'admin'=>new AdminResource($this->admin),
            'client_id'=>new ClientResource($this->client_),
            'logs'=>$request->logs ?  LogResource::collection($this->logs) :null,
        ];
    }
}

