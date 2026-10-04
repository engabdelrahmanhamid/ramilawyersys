<?php

namespace App\Repos;

use App\Models\AdminPermission;
use Validator,Auth,Artisan,Hash,File,Crypt;

class AdminPermissionRepo{
    use \App\Traits\ApiResponseTrait;

    /**
     * @param $payload
     * @param $admin
     * @return void
     */
    public function createArray($payload, $admin)
    {
        AdminPermission::where('admin_id',$admin->id)->delete();
        foreach ($payload as $row) {
            $this->create($row, $admin->id);
        }
    }

    /**
     * @param $row
     * @param $user_id
     * @return void
     */
    public function create($row,$admin_id)
    {
        $adminPermission=AdminPermission::where('admin_id',$admin_id)->where('permission_id',$row['permission_id'])->first();
        if(is_null($adminPermission))
            $adminPermission=new AdminPermission();
        $adminPermission->admin_id=$admin_id;
        $adminPermission->permission_id=$row['permission_id'];
        $adminPermission->save();
    }

    /**
     * @param $permissions
     * @param $admin
     * @return void
     */
    public function assignAllPermissions($permissions, $admin)
    {
        AdminPermission::where('admin_id',$admin->id)->delete();
        foreach ($permissions as $permission) {
                $adminPermission = new AdminPermission();
                $adminPermission->admin_id = $admin->id;
                $adminPermission->permission_id = $permission->id;
                $adminPermission->save();
            }
    }
}
