<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\DeviceCollection;
use App\Http\Resources\DeviceResource;
use App\Repos\DeviceRepo;
use App\Validations\DeviceValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;
class DeviceController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $deviceRepo;
    private $deviceValidation;


    public function __construct(DeviceRepo $deviceRepo , DeviceValidation $deviceValidation)
    {
        $this->deviceRepo = $deviceRepo;
        $this->deviceValidation = $deviceValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $devices = $this->deviceRepo->get($request);
        return $this->apiResponseData(new DeviceCollection($devices));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->deviceRepo->getDeviceById($request->device_id);
        return $this->apiResponseData(new DeviceResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateDevice = $this->deviceValidation->validate($request);
        if($validateDevice->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateDevice->error,200);
        }
        $data = $this->deviceRepo->create($request);
        return $this->apiResponseData(new DeviceResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->deviceRepo->getDeviceById($request->device_id);
        $device=$response->data;
        $validateDevice = $this->deviceValidation->validate($request);
        if($validateDevice->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateDevice->error,200);
        }
        $data = $this->deviceRepo->update($request,$device);
        return $this->apiResponseData(new DeviceResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->deviceRepo->getDeviceById($request->device_id);
        $this->deviceRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
