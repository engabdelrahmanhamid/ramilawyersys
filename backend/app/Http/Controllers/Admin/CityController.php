<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CityCollection;
use App\Http\Resources\CityResource;
use App\Repos\CityRepo;
use App\Validations\CityValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class CityController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $cityRepo;
    private $cityValidation;


    public function __construct(CityRepo $cityRepo , CityValidation $cityValidation)
    {
        $this->cityRepo = $cityRepo;
        $this->cityValidation = $cityValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $cities = $this->cityRepo->get($request);
        return $this->apiResponseData(new CityCollection($cities));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->cityRepo->getCityById($request->city_id);
        return $this->apiResponseData(new CityResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateCity = $this->cityValidation->validate($request);
        if($validateCity->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCity->error,200);
        }
        $data = $this->cityRepo->create($request);
        return $this->apiResponseData(new CityResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->cityRepo->getCityById($request->city_id);
        $City=$response->data;
        $validateCity = $this->cityValidation->validate($request);
        if($validateCity->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCity->error,200);
        }
        $data = $this->cityRepo->update($request,$City);
        return $this->apiResponseData(new CityResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->cityRepo->getCityById($request->city_id);
        $this->cityRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
