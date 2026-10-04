<?php

namespace App\Http\Controllers\Admin;

use App\Http\Collections\AppointmentCollection;
use App\Http\Collections\CityCollection;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\CityResource;
use App\Repos\AppointmentRepo;
use App\Repos\CityRepo;
use App\Validations\AppointmentValidation;
use App\Validations\CityValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Validator, Auth, Artisan, Hash, File, Mail;

class AppointmentController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    private $appointmentRepo;
    private $appointmentValidation;


    public function __construct(AppointmentRepo $appointmentRepo , AppointmentValidation $appointmentValidation)
    {
        $this->appointmentRepo = $appointmentRepo;
        $this->appointmentValidation = $appointmentValidation;

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $appointments = $this->appointmentRepo->get($request);
        return $this->apiResponseData(new AppointmentCollection($appointments));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function single(Request $request)
    {
        $response = $this->appointmentRepo->getAppointmentById($request->appointment_id);
        return $this->apiResponseData(new AppointmentResource($response->data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        App::setLocale($request->header('lang'));
        $validateAppointment = $this->appointmentValidation->validate($request);
        if($validateAppointment->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateAppointment->error,200);
        }
        $data = $this->appointmentRepo->create($request);
        return $this->apiResponseData(new AppointmentResource($data));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->appointmentRepo->getAppointmentById($request->appointment_id);
        $appointment=$response->data;
        $validateAppointment = $this->appointmentValidation->validate($request);
        if($validateAppointment->operationType==ERROR){
            return $this->apiResponseMessage(0,$validateAppointment->error,200);
        }
        $data = $this->appointmentRepo->update($request,$appointment);
        return $this->apiResponseData(new AppointmentResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function is_attend(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->appointmentRepo->getAppointmentById($request->appointment_id);
        $appointment=$response->data;
        $data = $this->appointmentRepo->is_attend($request,$appointment);
        return $this->apiResponseData(new AppointmentResource($data));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        App::setLocale($request->header('lang'));
        $response = $this->appointmentRepo->getAppointmentById($request->appointment_id);
        $this->appointmentRepo->delete($response->data);
        return $this->apiResponseMessage(1,'deleted successfully');
    }

}
