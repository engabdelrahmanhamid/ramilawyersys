<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Branch;
use Validator, Auth, Artisan, Hash, File, Crypt;

class BranchRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $branches = Branch::orderBy('id','desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $branches = $branches->paginate($limit);
        return $branches;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getBranchById($id)
    {
        $Branch = Branch::findOrfail($id);
        return AppResult::success($Branch);
    }

    /**
     * @param $payload
     * @return Branch
     */
    public function create($payload)
    {
        $Branch = new Branch();
        $Branch->name = $payload->name;
        $Branch->address = $payload->address;
        $Branch->save();
        return $Branch;
    }

    /**
     * @param $payload
     * @param $Branch
     * @return mixed
     */
    public function update($payload, $Branch)
    {
        $Branch->name = $payload->name;
        $Branch->address = $payload->address;
        $Branch->save();
        return $Branch;
    }

    /**
     * @param $Branch
     * @return void
     */
    public function delete($Branch)
    {
        $Branch->delete();
    }


}
