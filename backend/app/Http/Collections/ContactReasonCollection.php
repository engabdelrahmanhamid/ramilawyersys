<?php

namespace App\Http\Collections;

use App\Http\Resources\ClientResource;
use App\Http\Resources\ContactReasonResource;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactReasonCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) 
    {
        return [

            'data' =>ContactReasonResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => (int)$this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'is_pagination' => $this->lastPage() <= $this->currentPage() ? false : true,

            ],


        ];
    }
}
