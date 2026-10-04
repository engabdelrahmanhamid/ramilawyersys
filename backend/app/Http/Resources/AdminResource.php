<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class AdminResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'fire_base' => $this->fire_base,
            'super' => (int)$this->super,
            'logged_time' => DateHelper::getInstance()->customDateFormatWithTime($this->logged_time),
            'image' => ImageHelper::getInstance()->getImageUrl('Admin',$this->image),
            'type'=>new TypeResource($this->type),
            'job'=>new JopResource($this->job),
            'branch'=>new BranchResource($this->branch),
            'permissions' => $this->permissions->pluck('id'),
            'token' => $this->my_token,
            'logs'=>$request->admin_logs ? LogResource::collection($this->logs) : null,
            'files'=>AdminFileResource::collection($this->files),
        ];
    }
}
