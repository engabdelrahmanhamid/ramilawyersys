<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class CaseResource extends JsonResource
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
            'name' =>  $this->name ,
            'address' =>  $this->address ,
            'number' =>  $this->number ,
            'hijri_date' =>  $this->hijri_date ,
            'gregorian_date' =>  $this->gregorian_date ,
            'desc' =>  $this->desc ,
            'court_name' =>  $this->court_name ,
            'court_city' =>  $this->court_city ,
            'court_circle' =>  $this->court_circle ,
            'court_degree' =>  $this->court_degree ,
            'opponent_name' =>  $this->opponent_name ,
            'client_characteristic' =>  (int) $this->client_characteristic ,
            'payment_type' =>  (int) $this->payment_type ,
            'amount' => PriceHelper::getInstance()->priceFormat($this->amount),
            'tax' => (int)$this->tax,
            'tax_amount' => PriceHelper::getInstance()->priceFormat($this->tax_amount),
            'total_amount' => PriceHelper::getInstance()->priceFormat($this->total_amount),
            'deposit' => PriceHelper::getInstance()->priceFormat($this->deposit),
            'payment_status' =>  (int) $this->payment_status ,
            'payment_method'=>new PaymentMethodResource($this->payment_method),
            'case_type'=>new TypeResource($this->case_type),
            'opponent_type'=>new TypeResource($this->opponent_type),
            'status'=>new CaseStatusResource($this->status),
            'branch'=>new BranchResource($this->branch),
            'client'=>new ClientResource($this->client),
            'admin'=>new AdminResource($this->admin),
            'files'=>CaseFileResource::collection($this->files),
            'notes'=>NoteResource::collection($this->notes),
            'statuses_history'=>CaseStatusHistoryResource::collection($this->statuses_history),
            'logs'=>$request->case_logs ?  LogResource::collection($this->logs) :null,
        ];
    }
}
