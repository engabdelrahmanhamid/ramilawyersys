<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\JobCollection;
use App\Http\Resources\JopResource;
use App\Repos\JobRepo;
use App\Validations\JobValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;
class JobController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $jobRepo;
    private $jobValidation;


    public function __construct(JobRepo $jobRepo , JobValidation $jobValidation)
    {
        $this->jobRepo = $jobRepo;
        $this->jobValidation = $jobValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $jobs = $this->jobRepo->get($request);
        return $this->apiResponseData(new JobCollection($jobs));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->jobRepo->getjobById($request->job_id);
        return $this->apiResponseData(new JopResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateJob = $this->jobValidation->validate($request);
        if($validateJob->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateJob->error,200);
        }
        $data = $this->jobRepo->create($request);
        return $this->apiResponseData(new JopResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->jobRepo->getjobById($request->job_id);
        $job=$response->data;
        $validateJob = $this->jobValidation->validate($request);
        if($validateJob->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateJob->error,200);
        }
        $data = $this->jobRepo->update($request,$job);
        return $this->apiResponseData(new JopResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->jobRepo->getjobById($request->job_id);
        $this->jobRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
