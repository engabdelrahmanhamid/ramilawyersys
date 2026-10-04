<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class ReplayResource extends JsonResource
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
            'message' =>  $this->message,
            'log' =>  new LogResource($this->log),
        ];
    }
}
