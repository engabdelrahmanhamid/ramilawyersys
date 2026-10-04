<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Helpers\NumberHelper;
use App\Models\Admin;
use App\Models\Models\City;
use App\Models\User;
use Validator,Auth,Artisan,Hash,File,Crypt;

class AdminRepo{
    use \App\Traits\ApiResponseTrait;

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $admins=Admin::orderBy('id','desc')
            ->where('id', '!=', 17);
        $limit=$filter->limit ? $filter->limit : 10;
        if($filter->search_text) {
            $admins = $admins->where(function($q)use($filter){
                $q->where('phone', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhere('name', 'LIKE', '%' . $filter->search_text . '%')
                    ->orWhere('email', 'LIKE', '%' . $filter->search_text . '%');
            });

        }
        if($filter->branch_id)
            $admins=$admins->where('branch_id',$filter->branch_id);
        $admins=$admins->paginate($limit);
        return $admins;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getAdminById($id)
    {
        $admin=Admin::findOrfail($id);
        return AppResult::success($admin);
    }

    /**
     * @param $email
     * @return AppResult
     */
    public function getAdminByEmail($email){
        $admin=Admin::where('email',$email)->first();
        if(is_null($admin))
            return AppResult::error('email not found');
        return AppResult::success($admin);
    }

    /**
     * @param $payload
     * @return Admin
     */
    public function create($payload)
    {
        $admin=new Admin();
        $admin->name=$payload->name;
        $admin->email=$payload->email;
        $admin->phone=$payload->phone;
        $admin->password=Hash::make($payload->password);
        $admin->type_id=$payload->type_id;
        $admin->job_id=$payload->job_id;
        $admin->branch_id=$payload->branch_id;
        $admin->super=$payload->super;
        if($payload->image)
            $admin->image=ImageHelper::getInstance()->saveImage('Admin',$payload->image);
        $admin->save();
        return $admin;
    }

    /**
     * @param $payload
     * @param $admin
     * @return mixed
     */
    public function update($payload,$admin)
    {
        if($payload->name)
            $admin->name=$payload->name;
        if(isset($payload->super))
            $admin->super=$payload->super;


        if($payload->phone)
            $admin->phone=$payload->phone;
        if($payload->email)
            $admin->email=$payload->email;
        if($payload->password)
            $admin->password=Hash::make($payload->password);
        if($payload->type_id)
            $admin->type_id=$payload->type_id;
        if($payload->job_id)
            $admin->job_id=$payload->job_id;
        if(isset($payload->branch_id))
            $admin->branch_id=$payload->branch_id;
        if($payload->image) {
            ImageHelper::getInstance()->deleteFile('Admin',$admin->image);
            $admin->image=ImageHelper::getInstance()->saveImage('Admin',$payload->image);
        }
        $admin->save();
        return $admin;
    }

    /**
     * @param $admin
     */
    public function generatePasswordCode($admin){
        $admin->code = NumberHelper::getInstance()->generateCode();
        $admin->save();
    }

    /**
     * @param $admin
     * @param $newPassword
     */
    public function changePassword($admin,$newPassword){
        $admin->password=Hash::make($newPassword);
        $admin->save();
    }

    /**
     * @param $admin
     */
    public function delete($admin)
    {
        $admin->delete();
    }


}
