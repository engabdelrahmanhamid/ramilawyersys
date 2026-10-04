<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class ServiceResource extends JsonResource
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
            'details' =>  $this->details ,
            'amount' => PriceHelper::getInstance()->priceFormat($this->amount),
            'tax' => (int)$this->tax,
            'tax_amount' => PriceHelper::getInstance()->priceFormat($this->tax_amount),
            'total_amount' => PriceHelper::getInstance()->priceFormat($this->total_amount),
            'deposit' => PriceHelper::getInstance()->priceFormat($this->deposit),
            'start_date' =>  DateHelper::getInstance()->customDateFormat($this->start_date) ,
            'end_date' =>  DateHelper::getInstance()->customDateFormat($this->end_date) ,
            'payment_type' =>  (int) $this->payment_type ,
            'payment_status' =>  (int) $this->payment_status ,
            'status' =>  (int) $this->status ,
            'payment_method'=>new PaymentMethodResource($this->payment_method),
            'service_type'=>new ServiceTypeResource($this->service_type),
            'branch'=>new BranchResource($this->branch),
            'client'=>new ClientResource($this->client),
            'admin'=>new AdminResource($this->admin),
            'logs'=>$request->logs ?  LogResource::collection($this->logs) :null,
        ];
    }
}
