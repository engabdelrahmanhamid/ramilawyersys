<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Resources\Json\JsonResource;


class ClientResource extends JsonResource
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
            'phone' =>  $this->phone ,
            'secondary_phone' =>  $this->secondary_phone ,
            'email' =>  $this->email ,
            'address' =>  $this->address ,
            'id_number' =>  $this->id_number ,
            'tax_number' =>  $this->tax_number ,
            'commercial_register' =>  $this->commercial_register ,
            'case_type' =>  $this->case_type ,
            'image' =>  ImageHelper::getInstance()->getImageUrl('Client',$this->image) ,
            'branch'=>new BranchResource($this->branch),
            'country'=>new CountryResource($this->country),
            'city'=>new CityResource($this->city),
            'district'=>new DistrictResource($this->district),
            'type'=>new TypeResource($this->type),
            'device'=>new DeviceResource($this->device),
            'source'=>new SourceResource($this->source),
            'status'=>new StatusResource($this->status),
            'status_updated_at'=>DateHelper::getInstance()->customDateFormatWithTime($this->status_updated_at),
            'admin'=>new AdminResource($this->admin),
            'files'=>ClientFileResource::collection($this->files),
            'logs'=>$request->client_logs ?  LogResource::collection($this->logs) :null,
        ];
    }
    // public function toArray($request)
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'phone' => $this->phone,
    //         'secondary_phone' => $this->secondary_phone,
    //         'email' => $this->email,
    //         'address' => $this->address,
    //         'id_number' => $this->id_number,
    //         'tax_number' => $this->tax_number,
    //         'commercial_register' => $this->commercial_register,
    //         'case_type' => $this->case_type,
    //         'image' => ImageHelper::getInstance()->getImageUrl('Client', $this->image),
    
    //         'branch' => $this->branch ? [
    //             'id' => $this->branch->id,
    //             'name' => $this->branch->name,
    //             'address' => $this->branch->address ?? null,
    //         ] : null,
    
    //         'country' => $this->country ? [
    //             'id' => $this->country->id,
    //             'name' => $this->country->name,
    //         ] : null,
    
    //         'city' => $this->city ? [
    //             'id' => $this->city->id,
    //             'name' => $this->city->name,
    //             'country_id' => $this->city->country_id ?? null,
    //         ] : null,
    
    //         'district' => $this->district ? [
    //             'id' => $this->district->id,
    //             'name' => $this->district->name,
    //             'city_id' => $this->district->city_id ?? null,
    //         ] : null,
    
    //         'type' => $this->type ? [
    //             'id' => $this->type->id,
    //             'name' => $this->type->name,
    //         ] : null,
    
    //         'device' => $this->device ? [
    //             'id' => $this->device->id,
    //             'name' => $this->device->name,
    //         ] : null,
    
    //         'source' => $this->source ? [
    //             'id' => $this->source->id,
    //             'name' => $this->source->name,
    //         ] : null,
    
    //         'status' => $this->status ? [
    //             'id' => $this->status->id,
    //             'name' => $this->status->name,
    //         ] : null,
    
    //         'status_updated_at' => DateHelper::getInstance()->customDateFormatWithTime($this->status_updated_at),
    
            // 'admin' => $this->admin ? [
            //     'id' => $this->admin->id,
            //     'name' => $this->admin->name,
            // ] : null,
    
    //         'files' => ClientFileResource::collection($this->files),
    
    //         'logs' => $request->client_logs ? LogResource::collection($this->logs) : null,
    //     ];
    // }
}
