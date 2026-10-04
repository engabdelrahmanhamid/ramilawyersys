<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\CaseFileResource;
use App\Repos\CaseFileRepo;
use App\Validations\CaseFileValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class CaseFileController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $caseFileRepo;
    private $caseFileValidation;


    public function __construct(CaseFileRepo $caseFileRepo , CaseFileValidation $caseFileValidation)
    {
        $this->caseFileRepo = $caseFileRepo;
        $this->caseFileValidation = $caseFileValidation;

    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request['create']=1;
        $validateCaseFile = $this->caseFileValidation->validate($request);
        if($validateCaseFile->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCaseFile->error,200);
        }
        $data = $this->caseFileRepo->create($request);
        return $this->apiResponseData(new CaseFileResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->caseFileRepo->getCaseFileById($request->file_id);
        $caseFile=$response->data;
        $validateCaseFile = $this->caseFileValidation->validate($request);
        if($validateCaseFile->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCaseFile->error,200);
        }
        $data = $this->caseFileRepo->update($request,$caseFile);
        return $this->apiResponseData(new CaseFileResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $response = $this->caseFileRepo->getCaseFileById($request->file_id);
        $this->caseFileRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
