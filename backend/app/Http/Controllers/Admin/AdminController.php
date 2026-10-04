<?php

namespace App\Http\Controllers\Admin;

use App\Core\AppResult;
use App\Http\Collections\AdminCollection;
use App\Http\Collections\PermissionCollection;
use App\Http\Resources\AdminResource;
use App\Repos\AdminPermissionRepo;
use App\Repos\AdminRepo;
use App\Repos\PermissionRepo;
use App\Validations\AdminValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Crypt;

class AdminController extends Controller
{
    use \App\Traits\ApiResponseTrait;
    private $adminRepo;
    private $adminValidation;
    private $adminPermissionRepo;
    private $permissionRepo;

    /**
     * @param AdminRepo $adminRepo
     * @param AdminValidation $adminValidation
     * @param AdminPermissionRepo $adminPermissionRepo
     */
    public function __construct(AdminRepo $adminRepo
        , AdminValidation $adminValidation
        , AdminPermissionRepo $adminPermissionRepo
        ,PermissionRepo $permissionRepo)
    {
        $this->adminRepo=$adminRepo;
        $this->adminValidation=$adminValidation;
        $this->adminPermissionRepo=$adminPermissionRepo;
        $this->permissionRepo=$permissionRepo;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $admins = $this->adminRepo->get($request);
        return $this->apiResponseData(new AdminCollection($admins));
    }

    /**
     * @param Request $request
     * @param PermissionRepo $permissionRepo
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get_permissions(Request $request,PermissionRepo $permissionRepo){
        $permissions=$permissionRepo->get($request);
        return $this->apiResponseData(new PermissionCollection($permissions));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $request['admin_logs']=1;
        $response = $this->adminRepo->getAdminById($request->admin_id);
        return $this->apiResponseData(new AdminResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request['adminPermissions']=json_decode($request->adminPermissions,true);
        App::setLocale($request->header('lang'));
        $validateAdmin = $this->adminValidation->validate($request,null);
        if($validateAdmin->operationType==ERROR)
            return $this->apiResponseMessage(0,$validateAdmin->error,200);
        $permissions = $this->permissionRepo->getAll();
        $admin = $this->adminRepo->create($request);
        if ($request->super == 1) {
            $this->adminPermissionRepo->assignAllPermissions($permissions,$admin);
        } else if (isset($request->adminPermissions)) {
            $this->adminPermissionRepo->createArray($request->adminPermissions, $admin);
        }
        return $this->apiResponseData(new AdminResource($admin));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request['adminPermissions']=json_decode($request->adminPermissions,true);
        App::setLocale($request->header('lang'));
        $response = $this->adminRepo->getAdminById($request->admin_id);
        $admin=$response->data;
        $validateAdmin = $this->adminValidation->validate($request);
        if($validateAdmin->operationType==ERROR)
            return $this->apiResponseMessage(0,$validateAdmin->error,200);
        $data = $this->adminRepo->update($request,$admin);
        if(isset($request->adminPermissions))
            $this->adminPermissionRepo->createArray($request->adminPermissions,$admin);
        $admin->tokens->each(function ($token) {
            $token->delete();
        });
        return $this->apiResponseData(new AdminResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->adminRepo->getAdminById($request->admin_id);
        $admin=$response->data;
        if($admin->id == 1)
            return $this->apiResponseMessage(0,__("responseMessage.you_can_not_delete_the_owner"));
        $this->adminRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }
}
