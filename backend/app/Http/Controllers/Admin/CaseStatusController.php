<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CaseStatusCollection;
use App\Http\Resources\CaseStatusResource;
use App\Repos\CaseStatusRepo;
use App\Validations\CaseStatusValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class CaseStatusController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $caseStatusRepo;
    private $caseStatusValidation;


    public function __construct(CaseStatusRepo $caseStatusRepo , CaseStatusValidation $caseStatusValidation)
    {
        $this->caseStatusRepo = $caseStatusRepo;
        $this->caseStatusValidation = $caseStatusValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $cities = $this->caseStatusRepo->get($request);
        return $this->apiResponseData(new CaseStatusCollection($cities));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->caseStatusRepo->getCaseStatusById($request->case_status_id);
        return $this->apiResponseData(new CaseStatusResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validateCaseStatus = $this->caseStatusValidation->validate($request);
        if($validateCaseStatus->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCaseStatus->error,200);
        }
        $data = $this->caseStatusRepo->create($request);
        return $this->apiResponseData(new CaseStatusResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->caseStatusRepo->getCaseStatusById($request->case_status_id);
        $CaseStatus=$response->data;
        $validateCaseStatus = $this->caseStatusValidation->validate($request);
        if($validateCaseStatus->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCaseStatus->error,200);
        }
        $data = $this->caseStatusRepo->update($request,$CaseStatus);
        return $this->apiResponseData(new CaseStatusResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->caseStatusRepo->getCaseStatusById($request->case_status_id);
        $this->caseStatusRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
