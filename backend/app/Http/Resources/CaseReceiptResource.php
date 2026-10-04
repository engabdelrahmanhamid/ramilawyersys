<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Models\PaymentMethod;
use App\Repos\CaseRepo;
use Illuminate\Http\Resources\Json\JsonResource;


class CaseReceiptResource extends JsonResource
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
            'case_id' => $this->case_id,
            'amount' => PriceHelper::getInstance()->priceFormat($this->amount),
            'tax_amount' => PriceHelper::getInstance()->priceFormat($this->tax_amount),
            'total_amount' => PriceHelper::getInstance()->priceFormat($this->total_amount),
            'paid_amount' => PriceHelper::getInstance()->priceFormat($this->paid_amount),
            'unpaid_amount' => PriceHelper::getInstance()->priceFormat($this->unpaid_amount),
            'date' =>  $this->date ,
            'paid_to' =>  $this->paid_to ,
            'payment_status' =>  (int) $this->payment_status ,
            'payment_method' =>  new PaymentMethodResource($this->payment_method) ,
            'case' =>  new CaseResource($this->UserCase) ,

        ];
    }
}
