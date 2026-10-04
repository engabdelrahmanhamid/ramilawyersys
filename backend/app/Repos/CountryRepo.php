<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Country;
use Validator, Auth, Artisan, Hash, File, Crypt;

class CountryRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $countries = Country::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $countries = $countries->paginate($limit);
        return $countries;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getCountryById($id)
    {
        $Country = Country::findOrfail($id);
        return AppResult::success($Country);
    }

    /**
     * @param $payload
     * @return Country
     */
    public function create($payload)
    {
        $Country = new Country();
        $Country->name = $payload->name;
        $Country->save();
        return $Country;
    }

    /**
     * @param $payload
     * @param $Country
     * @return mixed
     */
    public function update($payload, $Country)
    {
        $Country->name = $payload->name;
        $Country->save();
        return $Country;
    }

    /**
     * @param $Country
     * @return void
     */
    public function delete($Country)
    {
        $Country->delete();
    }


}
