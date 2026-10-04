<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\ClientStatus;
use Validator,Auth,Artisan,Hash,File,Crypt;

class ClientStatusRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $ClientStatuses=ClientStatus::orderBy('sort','asc');
        $limit=$filter->limit ? $filter->limit : 10;
        if($filter->type_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('type_id', $filter->type_id);
            });
        }
        if($filter->status_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('status_id', $filter->status_id);
            });
        }
        if($filter->branch_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('branch_id', $filter->branch_id);
            });
        }
        if($filter->admin_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('admin_id', $filter->admin_id);
            });
        }
        if($filter->country_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('country_id', $filter->country_id);
            });
        }
        if($filter->city_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('city_id', $filter->city_id);
            });
        }
        if($filter->district_id) {
            $ClientStatuses = $ClientStatuses->whereHas('clients', function ($q) use ($filter) {
                $q->where('district_id', $filter->district_id);
            });
        }
        $ClientStatuses=$ClientStatuses->paginate($limit);
        return $ClientStatuses;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getClientStatusById($id)
    {
        $ClientStatus=ClientStatus::findOrfail($id);
        return AppResult::success($ClientStatus);
    }

    /**
     * @param $payload
     * @return ClientStatus
     */
    public function create($payload)
    {
        $clientStatus=new ClientStatus();
        $clientStatus->name=$payload->name;
        $clientStatus->sort=$payload->sort;
        $clientStatus->color=$payload->color;
        $clientStatus->save();
        return $clientStatus;
    }

    /**
     * @param $payload
     * @param $clientStatus
     * @return mixed
     */
    public function update($payload,$clientStatus)
    {
        $clientStatus->name=$payload->name;
        $clientStatus->sort=$payload->sort;
        $clientStatus->color=$payload->color;
        $clientStatus->save();
        return $clientStatus;
    }



    /**
     * @param $ClientStatus
     * @return void
     */
    public function delete($ClientStatus)
    {
        $ClientStatus->delete();
    }


}
