<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Appointment;
use App\Models\City;
use App\Models\Replay;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class AppointmentRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $data = Appointment::orderBy('id', 'desc')->filter($filter);;
        $limit = $filter->limit ? $filter->limit : 10;
        if($filter->export)
            return $data=$data->get();
        $data = $data->paginate($limit);
        return $data;
    }


    /**
     * @param $filter
     * @return mixed
     */
    public function get_old_clients($filter)
    {
        $data = Appointment::orderBy('id', 'desc')->filter($filter);
        $data = $data->where('client_type',2);
        $limit = $filter->limit ? $filter->limit : 10;
        return $data->paginate($limit);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function get_new_clients($filter)
    {
        $data = Appointment::orderBy('id', 'desc')->filter($filter);
        $data = $data->where('client_type',1);
        $limit = $filter->limit ? $filter->limit : 10;
        return $data->paginate($limit);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getAll($filter){
        return Appointment::orderBy('id', 'desc')->filter($filter);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getAllAttended($filter){
        return Appointment::orderBy('id', 'desc')->filter($filter)->where('status' , 2);
    }
    /**
     * @param $allAppointments
     * @param $allClients
     * @return float|int
     */
    public function getAppointmentPercentage($allAppointments, $allClients)
    {
        return $allClients > 0 ? ($allAppointments / $allClients) * 100 : 0;
    }
    /**
     * @param $allAppointments
     * @param $attendedAppointments
     * @return float|int
     */
    public function getAttendedPercentage($allAppointments , $attendedAppointments)
    {
        return $allAppointments > 0 ? ($attendedAppointments / $allAppointments) * 100 : 0;
    }



    /**
     * @param $id
     * @return AppResult
     */
    public function getAppointmentById($id)
    {
        $appointment = Appointment::findOrfail($id);
        return AppResult::success($appointment);
    }

    /**
     * @param $payload
     * @return Appointment
     */
    public function create($payload)
    {
        $admin = Auth::user();
        $appointment = new Appointment();
        $appointment->date = $payload->date;
        $appointment->status = $payload->status;
        $appointment->client_type = $payload->client_type;
        $appointment->reason = $payload->reason;
        $appointment->client_id = $payload->client_id;
        $appointment->createdBy_id = $admin->id;
        $appointment->save();
        return $appointment;
    }

    /**
     * @param $payload
     * @param $appointment
     * @return mixed
     */
    public function update($payload, $appointment)
    {
        if (isset($payload->date))
            $appointment->date = $payload->date;
        if (isset($payload->status))
            $appointment->status = $payload->status;
        if (isset($payload->client_type))
            $appointment->client_type = $payload->client_type;
        if (isset($payload->reason))
            $appointment->reason = $payload->reason;
        if (isset($payload->client_id))
            $appointment->client_id = $payload->client_id;
        $appointment->save();
        return $appointment;
    }

    /**
     * @param $payload
     * @param $appointment
     * @return mixed
     */
    public function is_attend($payload, $appointment)
    {
        $appointment->status = $payload->status;
        $appointment->reason = $payload->reason;
        $appointment->save();
        return $appointment;
    }

    /** hostinger.com
     * @param $appointment
     * @return void
     */
    public function delete($appointment)
    {
        $appointment->delete();
    }

}
