<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\PaymentMethod;
use App\Models\ServiceType;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ServiceTypeRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $service_types = ServiceType::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $service_types = $service_types->paginate($limit);
        return $service_types;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getServiceTypeById($id)
    {
        $service_type = ServiceType::findOrfail($id);
        return AppResult::success($service_type);
    }

    /**
     * @param $payload
     * @return ServiceType
     */
    public function create($payload)
    {
        $service_type = new ServiceType();
        $service_type->name = $payload->name;
        $service_type->save();
        return $service_type;
    }

    /**
     * @param $payload
     * @param $service_type
     * @return mixed
     */
    public function update($payload, $service_type)
    {
        $service_type->name = $payload->name;
        $service_type->save();
        return $service_type;
    }

    /**
     * @param $service_type
     * @return void
     */
    public function delete($service_type)
    {
        $service_type->delete();
    }


}
