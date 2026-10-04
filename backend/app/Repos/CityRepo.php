<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\City;
use Validator, Auth, Artisan, Hash, File, Crypt;

class CityRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $cities = City::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        if($filter->country_id)
            $cities=$cities->where('country_id',$filter->country_id);
        $cities = $cities->paginate($limit);
        return $cities;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getCityById($id)
    {
        $City = City::findOrfail($id);
        return AppResult::success($City);
    }

    /**
     * @param $payload
     * @return City
     */
    public function create($payload)
    {
        $City = new City();
        $City->name = $payload->name;
        $City->country_id = $payload->country_id;
        $City->save();
        return $City;
    }

    /**
     * @param $payload
     * @param $City
     * @return mixed
     */
    public function update($payload, $City)
    {
        $City->name = $payload->name;
        $City->country_id = $payload->country_id;
        $City->save();
        return $City;
    }

    /**
     * @param $City
     * @return void
     */
    public function delete($City)
    {
        $City->delete();
    }


}
