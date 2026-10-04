<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\CountryCollection;
use App\Http\Resources\CountryResource;
use App\Repos\CountryRepo;
use App\Validations\CountryValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class CountryController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $countryRepo;
    private $countryValidation;


    public function __construct(CountryRepo $countryRepo , CountryValidation $countryValidation)
    {
        $this->countryRepo = $countryRepo;
        $this->countryValidation = $countryValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $Countrys = $this->countryRepo->get($request);
        return $this->apiResponseData(new CountryCollection($Countrys));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->countryRepo->getCountryById($request->country_id);
        return $this->apiResponseData(new CountryResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateCountry = $this->countryValidation->validate($request);
        if($validateCountry->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCountry->error,200);
        }
        $data = $this->countryRepo->create($request);
        return $this->apiResponseData(new CountryResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->countryRepo->getCountryById($request->country_id);
        $Country=$response->data;
        $validateCountry = $this->countryValidation->validate($request);
        if($validateCountry->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateCountry->error,200);
        }
        $data = $this->countryRepo->update($request,$Country);
        return $this->apiResponseData(new CountryResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->countryRepo->getCountryById($request->country_id);
        $this->countryRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
