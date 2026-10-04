<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Job;
use App\Models\Jop;
use Validator,Auth,Artisan,Hash,File,Crypt;

class JobRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $jobs=Job::orderBy('id','desc');
        $limit=$filter->limit ? $filter->limit : 10;
        $jobs=$jobs->paginate($limit);
        return $jobs;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getjobById($id)
    {
        $job=Job::findOrfail($id);
        return AppResult::success($job);
    }

    /**
     * @param $payload
     * @return Job
     */
    public function create($payload)
    {
        $job=new Job();
        $job->name=$payload->name;
        $job->save();
        return $job;
    }

    /**
     * @param $payload
     * @param $job
     * @return mixed
     */
    public function update($payload,$job)
    {
        if (isset($payload->name))
            $job->name=$payload->name;
        $job->save();
        return $job;
    }

    /**
     * @param $job
     * @return void
     */
    public function delete($job)
    {
        $job->delete();
    }


}
