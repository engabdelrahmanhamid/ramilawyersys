<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CityCollection;
use App\Http\Collections\DistrictCollection;
use App\Http\Resources\CityResource;
use App\Http\Resources\DistrictResource;
use App\Repos\CityRepo;
use App\Repos\DistrictRepo;
use App\Validations\CityValidation;
use App\Validations\DistrictValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class DistrictController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $districtRepo;
    private $districtValidation;

    /**
     * DistrictController constructor.
     * @param DistrictRepo $districtRepo
     * @param DistrictValidation $districtValidation
     */
    public function __construct(DistrictRepo $districtRepo , DistrictValidation $districtValidation)
    {
        $this->districtRepo = $districtRepo;
        $this->districtValidation = $districtValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $districts = $this->districtRepo->get($request);
        return $this->apiResponseData(new DistrictCollection($districts));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->districtRepo->getDistrictById($request->district_id);
        return $this->apiResponseData(new DistrictResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateDistrict = $this->districtValidation->validate($request);
        if($validateDistrict->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateDistrict->error,200);
        }
        $data = $this->districtRepo->create($request);
        return $this->apiResponseData(new DistrictResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->districtRepo->getDistrictById($request->district_id);
        $district=$response->data;
        $validateDistrict = $this->districtValidation->validate($request);
        if($validateDistrict->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateDistrict->error,200);
        }
        $data = $this->districtRepo->update($request,$district);
        return $this->apiResponseData(new DistrictResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->districtRepo->getDistrictById($request->district_id);
        $this->districtRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
