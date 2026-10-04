<?php

namespace App\Http\Controllers\Admin;
use App\Http\Collections\TypeCollection;
use App\Http\Resources\TypeResource;
use App\Repos\TypeRepo;
use App\Validations\TypeValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;
class TypeController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $typeRepo;
    private $typeValidation;


    public function __construct(TypeRepo $typeRepo , TypeValidation $typeValidation)
    {
        $this->typeRepo = $typeRepo;
        $this->typeValidation = $typeValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $types = $this->typeRepo->get($request);
        return $this->apiResponseData(new TypeCollection($types));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->typeRepo->getTypeById($request->type_id);
        return $this->apiResponseData(new TypeResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateType = $this->typeValidation->validate($request);
        if($validateType->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateType->error,200);
        }
        $data = $this->typeRepo->create($request);
        return $this->apiResponseData(new TypeResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->typeRepo->getTypeById($request->type_id);
        $type=$response->data;
        $validateType = $this->typeValidation->validate($request);
        if($validateType->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateType->error,200);
        }
        $data = $this->typeRepo->update($request,$type);
        return $this->apiResponseData(new TypeResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->typeRepo->getTypeById($request->type_id);
        if(in_array($request->type_id,[21,14,15]))
            return $this->apiResponseMessage(0,'لا يمكن مسح النوع');
        $this->typeRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
