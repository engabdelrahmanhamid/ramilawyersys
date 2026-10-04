<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EmailHelper;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Models\City;
use App\Models\District;
use App\Models\Service;
use App\Models\Task;
use App\Models\UserCase;
use App\Repos\AdminRepo;
use App\Repos\LogRepo;
use App\Validations\AdminValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\Fluent\Concerns\Has;
use Validator, Auth, Artisan, Hash, File, Mail;

class AuthController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $adminRepo;
    private $adminValidation;
    public function __construct(AdminRepo $adminRepo,AdminValidation $adminValidation)
    {
        $this->adminRepo=$adminRepo;
        $this->adminValidation=$adminValidation;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        if (Auth::guard('Admin')->attempt($credentials)) {
            $user = Auth::guard('Admin')->user();
            $token = $user->createToken('Admin')->accessToken;
            if ($request->has('fire_base')) {
                $user->fire_base = $request->input('fire_base');
                $user->save();
            }
            $user->logged_time = now();
            $user->save();
            $user['my_token'] = $token;
            LogRepo::logAuthAction($user,'login');

            return $this->apiResponseData(new AdminResource($user), 'login success', 200);

        }
        return $this->apiResponseMessage(0, 'invalid email or password', 200);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function my_info()
    {
        $user = Auth::user();
        return $this->apiResponseData(new AdminResource($user), 'success', 200);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit_profile(Request $request)
    {
        $admin = Auth::user();
        if (!$admin) {
            return $this->apiResponseMessage(0, 'User not authenticated', 401);
        }
        $request['admin_id'] = $admin->id;
        $validateAdmin = $this->adminValidation->validate($request,null);
        if($validateAdmin->operationType==ERROR)
            return $this->apiResponseMessage(0,$validateAdmin->error,400);
        $admin=$this->adminRepo->update($request,$admin);
        return $this->apiResponseData(new AdminResource($admin), 'updated successfully', 200);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::user()->tokens->each(function ($token, $key) {
            $token->delete();
            LogRepo::logAuthAction(Auth::user(),'logout');
        });
        return $this->apiResponseMessage(1, 'logout successfully', 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */

    public function forget_password(Request $request)
    {
        $response=$this->adminRepo->getAdminByEmail($request->email);
        if($response->operationType==ERROR)
            return $this->apiResponseMessage(0,$response->error);
        $admin=$response->data;
        $this->adminRepo->generatePasswordCode($admin);
        EmailHelper::getInstance()->forgetPasswordEmail($admin);
        return $this->apiResponseData(new AdminResource($admin), 'code send to your email', 200);
    }

    /****
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function reset_password(Request $request)
    {
        $admin = Auth::user();
        $lang = $request->header('lang');
        if (!$request->password) {
            $msg = $lang == 'en' ? 'please enter new password' : 'من فضلك ادخل كلمة السر الجديدة';
            return $this->apiResponseMessage(0, $msg, 200);
        }
        $this->adminRepo->changePassword($admin,$request->password);
        $msg = $lang == 'en' ? 'password updated successfully' : 'تم تغيير كلمة السر بنجاح';
        return $this->apiResponseMessage(1, $msg, 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function check_password_code(Request $request)
    {
        $lang = $request->header('lang');
        $response=$this->adminRepo->getAdminByEmail($request->email);
        if($response->operationType==ERROR)
            return $this->apiResponseMessage(0,$response->error);
        $admin=$response->data;
        if ($request->code != $admin->code) {
            $msg = $lang == 'en' ? 'code not correct' : 'الكود غير صحيح';
            return $this->apiResponseMessage(0, $msg, 200);
        }
        $admin->code = null;
        $admin->save();
        $token = $admin->createToken('TutsForWeb')->accessToken;
        $admin['my_token'] = $token;
        $msg = $lang == 'en' ? 'code correct' : 'الكود صحيح';
        return $this->apiResponseData(new AdminResource($admin), $msg, 200);
    }

    /***
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function change_password(Request $request)
    {
        $lang = $request->header('lang');
        $admin = Auth::user();
        if (!$request->newPassword) {
            $msg = $lang == 'ar' ? 'يجب ادخال كلمة السر الجديدة' : 'new password is required';
            return $this->apiResponseMessage(0, $msg, 200);
        }
        $password = Hash::check($request->oldPassword, $admin->password);
        if ($password == true) {
            $this->adminRepo->changePassword($admin,$request->newPassword);
            $msg = $lang == 'ar' ? 'تم تغيير كلمة السر بنجاح' : 'password changed successfully';
            return $this->apiResponseMessage(1, $msg, 200);

        } else {
            $msg = $lang == 'ar' ? 'كلمة السر القديمة غير صحيحة' : 'invalid old password';
            return $this->apiResponseMessage(0, $msg, 401);
        }
    }

}
