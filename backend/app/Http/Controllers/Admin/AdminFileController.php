<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\AdminFileResource;
use App\Http\Resources\CaseFileResource;
use App\Repos\AdminFileRepo;
use App\Repos\CaseFileRepo;
use App\Validations\AdminFileValidation;
use App\Validations\CaseFileValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class AdminFileController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $adminFileRepo;
    private $adminFileValidation;


    public function __construct(AdminFileRepo $adminFileRepo , AdminFileValidation $adminFileValidation)
    {
        $this->adminFileRepo = $adminFileRepo;
        $this->adminFileValidation = $adminFileValidation;

    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request['create']=1;
        $validateAdminFile = $this->adminFileValidation->validate($request);
        if($validateAdminFile->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateAdminFile->error,200);
        }
        $data = $this->adminFileRepo->create($request);
        return $this->apiResponseData(new AdminFileResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->adminFileRepo->getAdminFileById($request->file_id);
        $adminFile=$response->data;
        $validateAdminFile = $this->adminFileValidation->validate($request);
        if($validateAdminFile->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateAdminFile->error,200);
        }
        $data = $this->adminFileRepo->update($request,$adminFile);
        return $this->apiResponseData(new AdminFileResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $response = $this->adminFileRepo->getAdminFileById($request->file_id);
        $this->adminFileRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
