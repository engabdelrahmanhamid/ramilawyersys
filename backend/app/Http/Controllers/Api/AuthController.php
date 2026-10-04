<?php

namespace App\Http\Controllers\Api;

use App\Helpers\NumberHelper;
use App\Helpers\SmsHelper;
use App\Repos\UserAddRepo;
use App\Repos\UserRepo;
use App\Validations\UserValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator,Auth,Artisan,Hash,File,Crypt;
use App\Http\Resources\UserResource;
use App\Models\User;

class AuthController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    use \App\Traits\ApiResponseTrait;
    private $userRepo;
    private $userValidation;

    public function __construct(UserRepo $userRepo,UserValidation $userValidation)
    {
        $this->userRepo=$userRepo;
        $this->userValidation=$userValidation;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login(Request $request){
        App::setLocale($request->header('lang'));
        $response=$this->userRepo->getUserByEmail($request->email);
        if($response->operationType==ERROR)
            return $this->apiResponseMessage(0,$response->error);
        $user=$response->data;
        $password = Hash::check($request->password, $user->password);
        if ($password == false)
            return $this->apiResponseMessage(0, __('validationMessage.passwordNotCorrect'), 200);
        $this->putTokenInUser($user);
        return $this->apiResponseData(new UserResource($user));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function register(Request $request){
        App::setLocale($request->header('lang'));
        $validationUser=$this->userValidation->validate($request);
        if($validationUser->operationType==ERROR)
            return $this->apiResponseMessage(0,$validationUser->error,400);
        $user=$this->userRepo->create($request);
        $this->putTokenInUser($user);
        //TODO::verfay email
        return $this->apiResponseData(new UserResource($user));
    }

    /***
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit_profile(Request $request){
        App::setLocale($request->header('lang'));
        $user=Auth::user();
        $request['user_id']=$user->id;
        $validationUser=$this->userValidation->validate($request);
        if($validationUser->operationType==ERROR)
            return $this->apiResponseMessage(0,$validationUser->error,400);
        $user=$this->userRepo->update($request,$user);
        $this->putTokenInUser($user);
        return $this->apiResponseData(new UserResource($user));

    }
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function my_info(){
        return $this->apiResponseData(new UserResource(Auth::user()));
    }

    /***
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function change_lang(Request $request){
        $user=Auth::user();
        $user=$this->userRepo->update($request,$user);
        return $this->apiResponseData(new UserResource($user),'success');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function check_active_code(Request $request){
        App::setLocale($request->header('lang'));
        $user=Auth::user();
        if($request->code != $user->active_code)
            return $this->apiResponseMessage(0, __('validationMessage.codeNotCorrect'), 200);
        $user->active_code=null;
        $user->status=1;
        $user->save();
        return $this->apiResponseData(new UserResource($user),'success');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resend_code(Request $request){
        App::setLocale($request->header('lang'));
        $response=$this->userRepo->getUserByEmail($request->email);
        if($response->operationType==ERROR)
            return $this->apiResponseMessage(0,$response->error);
        $user=$response->data;
        $user->active_code=NumberHelper::getInstance()->generateCode();
        $user->save();
        return $this->apiResponseData(new UserResource($user),__('validationMessage.codeSend'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function forget_password(Request $request){
        App::setLocale($request->header('lang'));
        $response=$this->userRepo->getUserByEmail($request->email);
        if($response->operationType==ERROR)
            return $this->apiResponseMessage(0,$response->error);
        $user=$response->data;
        $user->password_code=NumberHelper::getInstance()->generateCode();
        $user->save();
        return $this->apiResponseData(new UserResource($user),__('validationMessage.codeSend'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function check_password_code(Request $request){
        App::setLocale($request->header('lang'));
        $response=$this->userRepo->getUserByEmail($request->email);
        if($response->operationType==ERROR)
            return $this->apiResponseMessage(0,$response->error);
        $user=$response->data;
        if($request->code != $user->password_code)
            return $this->apiResponseMessage(0, __('validationMessage.codeNotCorrect'), 200);
        $user->password_code=null;
        $user->save();
        $this->putTokenInUser($user);
        return $this->apiResponseData(new UserResource($user),'success');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function reset_password(Request $request){
        $user=Auth::user();
        $this->userRepo->changePassword($user,$request->password);
        return $this->apiResponseData(new UserResource($user),'success');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        App::setLocale($request->header('lang'));
        $user->firebase = null;
        $user->save();
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return $this->apiResponseMessage(1, __('validationMessage.logout'), 200);
    }

    /**
     * @param $user
     * @return mixed
     */
    private function putTokenInUser($user){
        return  $user['user_token'] = $user->createToken('TutsForWeb')->accessToken;
    }

}
