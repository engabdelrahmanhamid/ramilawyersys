<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\City;
use App\Models\District;
use Validator, Auth, Artisan, Hash, File, Crypt;

class DistrictRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $districts = District::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        if($filter->city_id)
            $districts=$districts->where('city_id',$filter->city_id);
        $districts = $districts->paginate($limit);
        return $districts;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getDistrictById($id)
    {
        $district = District::findOrfail($id);
        return AppResult::success($district);
    }

    /**
     * @param $payload
     * @return District
     */
    public function create($payload)
    {
        $district = new District();
        $district->name = $payload->name;
        $district->city_id = $payload->city_id;
        $district->save();
        return $district;
    }

    /**
     * @param $payload
     * @param $district
     * @return mixed
     */
    public function update($payload, $district)
    {
        $district->name = $payload->name;
        $district->city_id = $payload->city_id;
        $district->save();
        return $district;
    }

    /**
     * @param $district
     */
    public function delete($district)
    {
        $district->delete();
    }


}
