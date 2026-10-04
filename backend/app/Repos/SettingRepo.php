<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Models\Appointment;
use App\Models\City;
use App\Models\Information;
use App\Models\Replay;
use App\Models\Setting;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class SettingRepo
{
    /**
     * @return AppResult
     */
    public function getSetting()
    {
        $setting=Setting::first();
        return AppResult::success($setting);
    }

    /**
     * @return mixed
     */
    public function getFirstSetting()
    {
        return Setting::first();
    }

    /**
     * @return void
     */
    public function current_case_number(){
        $setting=Setting::first();
        $setting->current_case_number+= $setting->increase_amount_case_receipt ;
        $setting->save();
    }

    /**
     * @return void
     */
    public function current_service_number(){
        $setting=Setting::first();
        $setting->current_service_number+= $setting->increase_amount_service_receipt ;
        $setting->save();
    }


    /**@api
     * @param $payload
     * @param $setting
     * @return mixed
     */
    public function update($payload, $setting)
    {
        if (isset($payload->project_name))
            $setting->project_name = $payload->project_name;
        if (isset($payload->start_of_case_receipt))
            $setting->start_of_case_receipt = $payload->start_of_case_receipt;
        if (isset($payload->increase_amount_case_receipt))
            $setting->increase_amount_case_receipt = $payload->increase_amount_case_receipt;
        if (isset($payload->start_of_service_receipt))
            $setting->start_of_service_receipt = $payload->start_of_service_receipt;
        if (isset($payload->increase_amount_service_receipt))
            $setting->increase_amount_service_receipt = $payload->increase_amount_service_receipt;
        if($payload->logo){
            ImageHelper::getInstance()->deleteFile('Setting',$setting->logo);
            $setting->logo = ImageHelper::getInstance()->saveImage('Setting',$payload->logo);
        }
        $setting->save();
        return $setting;
    }

}
