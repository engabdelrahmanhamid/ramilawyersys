<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class AppointmentResource extends JsonResource
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
            'date' =>  $this->date ,
            'status' =>  (int) $this->status ,
            'client_type' =>  (int) $this->client_type ,
            'reason' =>   $this->reason ,
            'client'=>new ClientResource($this->client),
            'admin'=>new AdminResource($this->admin),
        ];
    }
}
