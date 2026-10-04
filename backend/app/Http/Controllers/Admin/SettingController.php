<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Repos\SettingRepo;
use Artisan;
use Auth;
use File;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Mail;
use Validator;

class SettingController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $settingRepo;

    public function __construct(SettingRepo $settingRepo)
    {
        $this->settingRepo = $settingRepo;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get()
    {
        $response = $this->settingRepo->getSetting();
        $setting = $response->data;
        return $this->apiResponseData(new SettingResource($setting));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->settingRepo->getSetting();
        $setting=$response->data;
        $data = $this->settingRepo->update($request,$setting);
        return $this->apiResponseData(new SettingResource($data));
    }
}
