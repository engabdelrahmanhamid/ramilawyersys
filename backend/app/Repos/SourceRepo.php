<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Source;
use Validator,Auth,Artisan,Hash,File,Crypt;

class SourceRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $sources=Source::orderBy('id','desc');
        $limit=$filter->limit ? $filter->limit : 10;
        $sources=$sources->paginate($limit);
        return $sources;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getSourceById($id)
    {
        $source=Source::where('id',$id)->firstOrFail();
        return AppResult::success($source);
    }

    /**
     * @param $payload
     * @return Source
     */
    public function create($payload)
    {
        $source=new Source();
        $source->name=$payload->name;
        $source->save();
        return $source;
    }

    /**
     * @param $payload
     * @param $source
     * @return mixed
     */
    public function update($payload,$source)
    {
        if (isset($payload->name))
            $source->name=$payload->name;
        $source->save();
        return $source;
    }

    /**
     * @param $source
     */
    public function delete($source)
    {
        $source->delete();
    }


}
