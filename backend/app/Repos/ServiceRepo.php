<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Models\ProductImage;
use App\Models\Service;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ServiceRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $services = Service::orderBy('id', 'desc')->filter($filter);
        $limit = $filter->limit ? $filter->limit : 10;
        if($filter->export)
            return $services=$services->get();
        $services = $services->paginate($limit);
        return $services;
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getReport($filter){
        return Service::orderBy('id', 'desc')->filter($filter);
    }

    /**
     * @param $column
     * @param $data
     * @return mixed
     */
    public function getSumArray($column,$data){
        return $data->sum($column);
    }

    /**
     * @param $id
     * @return AppResult
     */
    public function getServiceById($id)
    {
        $service = Service::findOrfail($id);
        return AppResult::success($service);
    }

    /**
     * @param $payload
     * @return Service
     */
    public function create($payload)
    {
        $admin = Auth::user();
        $service = new Service();
        $service->name = $payload->name;
        $service->details = $payload->details;
        $service->amount = $payload->amount;
        $service->tax = $payload->tax;
        $service->tax_amount = $this->calculateTaxPrice($payload->amount , $payload->tax);
        $service->total_amount = $service->amount + $service->tax_amount;
        $service->start_date = $payload->start_date;
        $service->end_date = $payload->end_date;
        $service->payment_type = $payload->payment_type;
        $service->status = $payload->status;

        $service->service_type_id = $payload->service_type_id;
        $service->payment_method_id = $payload->payment_method_id;
        $service->client_id = $payload->client_id;
        $service->branch_id = $payload->branch_id;
        $service->admin_id = $payload->admin_id;
        $service->createdBy_id = $admin->id;
        $service->save();
        return $service;
    }

    /**
     * @param $payload
     * @param $service
     * @return mixed
     */
    public function update($payload, $service)
    {
        if (isset($payload->name))
            $service->name = $payload->name;
        if (isset($payload->details))
            $service->details = $payload->details;
        if (isset($payload->amount))
            $service->amount = $payload->amount;
        if(isset($payload->amount) || isset($payload->tax)){
            $service->tax_amount = $this->calculateTaxPrice($service->amount , $service->tax);
            $service->total_amount =$service->amount + $service->tax_amount;
        }
        if (isset($payload->start_date))
            $service->start_date = $payload->start_date;
        if (isset($payload->end_date))
            $service->end_date = $payload->end_date;
        if (isset($payload->payment_type))
            $service->payment_type = $payload->payment_type;
        if (isset($payload->status))
            $service->status = $payload->status;
        if (isset($payload->service_type_id))
            $service->service_type_id = $payload->service_type_id;
        if (isset($payload->payment_method_id))
            $service->payment_method_id = $payload->payment_method_id;
        if (isset($payload->client_id))
            $service->client_id = $payload->client_id;
        if (isset($payload->branch_id))
            $service->branch_id = $payload->branch_id;
        if (isset($payload->admin_id))
            $service->admin_id = $payload->admin_id;
        $service->save();
        return $service;
    }

    /**
     * @param $amount
     * @param $tax
     * @return float|int
     */
    private function calculateTaxPrice($amount , $tax)
    {
        return PriceHelper::getInstance()->calDiscountPrice($amount , $tax , 2);
    }

    /**
     * @param $service
     * @return void
     */
    public function delete($service)
    {
        $service->delete();
    }


    /**
     * @param $service
     * @return void
     */
    public function changePaymentStatus($service)
    {
        $service->payment_status = $service->deposit == $service->total_amount ? 1 : 0;
        $service->save();
    }

    /**
     * @param $service
     * @param $payload
     * @return mixed
     */
    public function change_status($service, $payload)
    {
        $service->status = $payload['status'];
        $service->save();
        return $service;
    }




    /**
     * @param $service
     * @param $payload
     * @return void
     */
    public function changeDeposit($service,$payload)
    {
        $service->deposit += $payload->pay_amount;
        $service->save();
    }




}
