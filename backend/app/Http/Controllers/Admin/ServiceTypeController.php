<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CountryCollection;
use App\Http\Collections\PaymentMethodCollection;
use App\Http\Collections\ServiceTypeCollection;
use App\Http\Collections\SessionCollection;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ServiceTypeResource;
use App\Models\PaymentMethod;
use App\Repos\CountryRepo;
use App\Repos\PaymentMethodRepo;
use App\Repos\ServiceTypeRepo;
use App\Validations\CountryValidation;
use App\Validations\PaymentMethodValidation;
use App\Validations\ServiceTypeValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class ServiceTypeController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $serviceTypeRepo;
    private $serviceTypeValidation;


    public function __construct(ServiceTypeRepo $serviceTypeRepo , ServiceTypeValidation $serviceTypeValidation)
    {
        $this->serviceTypeRepo = $serviceTypeRepo;
        $this->serviceTypeValidation = $serviceTypeValidation;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $service_types = $this->serviceTypeRepo->get($request);
        return $this->apiResponseData(new ServiceTypeCollection($service_types));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->serviceTypeRepo->getServiceTypeById($request->service_type_id);
        return $this->apiResponseData(new ServiceTypeResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateServiceType = $this->serviceTypeValidation->validate($request);
        if($validateServiceType->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateServiceType->error,200);
        }
        $data = $this->serviceTypeRepo->create($request);
        return $this->apiResponseData(new ServiceTypeResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->serviceTypeRepo->getServiceTypeById($request->service_type_id);
        $service_type=$response->data;
        $validateServiceType = $this->serviceTypeValidation->validate($request);
        if($validateServiceType->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateServiceType->error,200);
        }
        $data = $this->serviceTypeRepo->update($request,$service_type);
        return $this->apiResponseData(new ServiceTypeResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->serviceTypeRepo->getServiceTypeById($request->service_type_id);
        $this->serviceTypeRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
