<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use App\Http\Collections\ClientCollection;
use App\Models\Client;
use App\Repos\ClientRepo;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;


class CampanClientResource extends JsonResource
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
            'status' =>  new StatusResource($this) ,
            'clients' =>  new ClientCollection($this->clients()->paginate(1000)) ,
        ];
    }
}
