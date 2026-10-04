<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class SessionResource extends JsonResource
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
            'link' =>  $this->link ,
            'type' =>  (int)$this->type ,
            'hijri_date' =>  $this->hijri_date ,
            'gregorian_date' =>  $this->gregorian_date ,
            'session_time' =>  $this->session_time ,
            'session_reminder_date' =>  $this->session_reminder_date ,
            'session_requirements' =>  $this->session_requirements ,
            'destination' =>  $this->destination ,
            'session_notes' =>  $this->session_notes ,
            'status'=>new SessionStatusResource($this->status),
            'admin'=>new AdminResource($this->admin),
            'client'=>new ClientResource($this->client),
            'case'=>new CaseResource($this->userCase)
        ];
    }
}
