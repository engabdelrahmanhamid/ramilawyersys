<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ClientFileResource;
use App\Repos\ClientFileRepo;
use App\Validations\ClientFileValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ClientFileController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $ClientFileRepo;
    private $ClientFileValidation;


    public function __construct(ClientFileRepo $ClientFileRepo , ClientFileValidation $ClientFileValidation)
    {
        $this->ClientFileRepo = $ClientFileRepo;
        $this->ClientFileValidation = $ClientFileValidation;

    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request['create']=1;
        $validateClientFile = $this->ClientFileValidation->validate($request);
        if($validateClientFile->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateClientFile->error,200);
        }
        $data = $this->ClientFileRepo->create($request);
        return $this->apiResponseData(new ClientFileResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->ClientFileRepo->getClientFileById($request->file_id);
        $ClientFile=$response->data;
        $validateClientFile = $this->ClientFileValidation->validate($request);
        if($validateClientFile->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateClientFile->error,200);
        }
        $data = $this->ClientFileRepo->update($request,$ClientFile);
        return $this->apiResponseData(new ClientFileResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $response = $this->ClientFileRepo->getClientFileById($request->file_id);
        $this->ClientFileRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
