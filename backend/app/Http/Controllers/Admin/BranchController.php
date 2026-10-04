<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\BranchCollection;
use App\Http\Resources\BranchResource;
use App\Repos\BranchRepo;
use App\Validations\BranchValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class BranchController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $branchRepo;
    private $branchValidation;


    public function __construct(BranchRepo $branchRepo , BranchValidation $branchValidation)
    {
        $this->branchRepo = $branchRepo;
        $this->branchValidation = $branchValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $admin = Auth::user();
        if ($admin->super != 1) {
            $branch = $admin->branch;
            return $branch ? $this->apiResponseData(new BranchResource($branch)) :
                $this->apiResponseData([]);
        }
        $branches = $this->branchRepo->get($request);
        return $this->apiResponseData(new BranchCollection($branches));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->branchRepo->getBranchById($request->branch_id);
        return $this->apiResponseData(new BranchResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateBranch = $this->branchValidation->validate($request);
        if($validateBranch->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateBranch->error,200);
        }
        $data = $this->branchRepo->create($request);
        return $this->apiResponseData(new BranchResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $response = $this->branchRepo->getBranchById($request->branch_id);
        $Branch=$response->data;
        $validateBranch = $this->branchValidation->validate($request);
        if($validateBranch->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateBranch->error,200);
        }
        $data = $this->branchRepo->update($request,$Branch);
        return $this->apiResponseData(new BranchResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->branchRepo->getBranchById($request->branch_id);
        $this->branchRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
