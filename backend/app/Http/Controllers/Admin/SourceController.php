<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\SourceCollection;
use App\Http\Resources\SourceResource;
use App\Repos\SourceRepo;
use App\Validations\SourceValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;
class SourceController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $sourceRepo;
    private $sourceValidation;

    public function __construct(SourceRepo $sourceRepo , SourceValidation $sourceValidation)
    {
        $this->sourceRepo = $sourceRepo;
        $this->sourceValidation = $sourceValidation;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $sources = $this->sourceRepo->get($request);
        return $this->apiResponseData(new SourceCollection($sources));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->sourceRepo->getSourceById($request->source_id);
        return $this->apiResponseData(new SourceResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateSource = $this->sourceValidation->validate($request);
        if($validateSource->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateSource->error,200);
        }
        $data = $this->sourceRepo->create($request);
        return $this->apiResponseData(new SourceResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->sourceRepo->getSourceById($request->source_id);
        $source=$response->data;
        $validateSource = $this->sourceValidation->validate($request);
        if($validateSource->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateSource->error,200);
        }
        $data = $this->sourceRepo->update($request,$source);
        return $this->apiResponseData(new SourceResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->sourceRepo->getSourceById($request->source_id);
        $this->sourceRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
