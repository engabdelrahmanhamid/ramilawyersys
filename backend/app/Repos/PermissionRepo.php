<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Permission;
use Validator,Auth,Artisan,Hash,File,Crypt;

class PermissionRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $permissions=Permission::orderBy('id','desc');
        $limit=$filter->limit ? $filter->limit : 20;
        $permissions=$permissions->paginate($limit);
        return $permissions;
    }


    /**
     * @return Permission[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Permission::all();
    }


}
