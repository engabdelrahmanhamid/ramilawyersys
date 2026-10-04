<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CaseStatusCollection;
use App\Http\Collections\SessionStatusCollection;
use App\Http\Resources\CaseStatusResource;
use App\Http\Resources\SessionStatusResource;
use App\Repos\CaseStatusRepo;
use App\Repos\SessionStatusRepo;
use App\Validations\CaseStatusValidation;
use App\Validations\SessionStatusValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class SessionStatusController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $sessionStatusRepo;
    private $sessionStatusValidation;


    public function __construct(SessionStatusRepo $sessionStatusRepo , SessionStatusValidation $sessionStatusValidation)
    {
        $this->sessionStatusRepo = $sessionStatusRepo;
        $this->sessionStatusValidation = $sessionStatusValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $data = $this->sessionStatusRepo->get($request);
        return $this->apiResponseData(new SessionStatusCollection($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->sessionStatusRepo->getSessionStatusById($request->session_status_id);
        return $this->apiResponseData(new SessionStatusResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validateSessionStatus = $this->sessionStatusValidation->validate($request);
        if($validateSessionStatus->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateSessionStatus->error,200);
        }
        $data = $this->sessionStatusRepo->create($request);
        return $this->apiResponseData(new SessionStatusResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->sessionStatusRepo->getSessionStatusById($request->session_status_id);
        $sessionStatus=$response->data;
        $validateSessionStatus = $this->sessionStatusValidation->validate($request);
        if($validateSessionStatus->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateSessionStatus->error,200);
        }
        $data = $this->sessionStatusRepo->update($request,$sessionStatus);
        return $this->apiResponseData(new SessionStatusResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->sessionStatusRepo->getSessionStatusById($request->session_status_id);
        $this->sessionStatusRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
