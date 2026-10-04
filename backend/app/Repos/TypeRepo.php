<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Type;
use Validator,Auth,Artisan,Hash,File,Crypt;

class TypeRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $types=Type::orderBy('id','desc')->where('model',$filter->model);
        $limit=$filter->limit ? $filter->limit : 10;
        if ($filter->search_text) {
            $types->where(function ($query) use ($filter) {
                $query->where('name', 'LIKE', '%' . $filter->search_text . '%');
            });
        }
        $types=$types->paginate($limit);
        return $types;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getTypeById($id)
    {
        $type=Type::where('id',$id)->firstOrFail();
        return AppResult::success($type);
    }

    /**
     * @param $payload
     * @return Type
     */
    public function create($payload)
    {
        $type=new Type();
        $type->name=$payload->name;
        $type->model=$payload->model;
        $type->save();
        return $type;
    }

    /**
     * @param $payload
     * @param $type
     * @return mixed
     */
    public function update($payload,$type)
    {
        if (isset($payload->name))
            $type->name=$payload->name;
        $type->save();
        return $type;
    }

    /**
     * @param $type
     * @return void
     */
    public function delete($type)
    {
        $type->delete();
    }


}
