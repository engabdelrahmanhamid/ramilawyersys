<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DateHelper;
use App\Http\Collections\CaseCollection;
use App\Http\Collections\ClientCollection;
use App\Http\Collections\SessionCollection;
use App\Http\Resources\CaseResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\SessionResource;
use App\Repos\CaseRepo;
use App\Repos\ClientRepo;
use App\Repos\LogRepo;
use App\Repos\SessionRepo;
use App\Validations\CaseValidation;
use App\Validations\ClientValidation;
use App\Validations\SessionValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Alkoumi\LaravelHijriDate\Hijri;

use Validator, Auth, Artisan, Hash, File, Mail;

class SessionController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $sessionRepo;
    private $sessionValidation;


    public function __construct(SessionRepo $sessionRepo , SessionValidation $sessionValidation)
    {
        $this->sessionRepo = $sessionRepo;
        $this->sessionValidation = $sessionValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $admin = Auth::user();
        if($admin->super != 1) {
            $request['branch_id'] = $admin->branch_id;
        }
        $sessions = $this->sessionRepo->get($request);
        return $this->apiResponseData(new SessionCollection($sessions));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->sessionRepo->getSessionById($request->session_id);
        $request['logs']=1;
        return $this->apiResponseData(new SessionResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validateSession = $this->sessionValidation->validate($request);
        if($validateSession->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateSession->error,200);
        }
        $data = $this->sessionRepo->create($request);
        LogRepo::create($data,'session','add');
        return $this->apiResponseData(new SessionResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function convertToHigri(Request $request)
    {
        $gregorianDate = $request->gregorian_date;
        $hijriDate = Hijri::ShortDate($gregorianDate);
        return $this->apiResponseData($hijriDate);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->sessionRepo->getSessionById($request->session_id);
        $session=$response->data;
        $validateSession = $this->sessionValidation->validate($request);
        if($validateSession->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateSession->error,200);
        }
        $data = $this->sessionRepo->update($request,$session);
        LogRepo::create($data,'session','update');
        return $this->apiResponseData(new SessionResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->sessionRepo->getSessionById($request->session_id);
        $this->sessionRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
