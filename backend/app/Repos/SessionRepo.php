<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Models\Appointment;
use App\Models\Session;
use App\Models\Task;
use App\Models\UserCase;
use Validator, Auth, Artisan, Hash, File, Crypt;

class SessionRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $sessions = Session::orderBy('id', 'desc')->filter($filter);
        if($filter->export)
            return $sessions=$sessions->get();
        if (isset($filter->no_pagination) && $filter->no_pagination == true) {
            return $sessions->get();
        } else {
            $limit = $filter->limit ? $filter->limit : 10;
            return $sessions->paginate($limit);
        }
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getForExport($filter)
    {
        $sessions = Session::orderBy('id', 'desc')->filter($filter);
        return $sessions=$sessions->get();

    }

//    /**
//     * @param $sessions
//     * @param $sort
//     * @return mixed
//     */
//    private function sort($sessions, $sort)
//    {
//        $statusValues = [4];
//        foreach ($statusValues as $statusValue) {
//            $orderByRaw[] = "IF(status_id = $statusValue, 0,1)";
//        }
//        if($sort==1)
//            $sessions = $sessions->orderByRaw('IF(status_id IN (4), 0,1), id DESC')
//                ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, gregorian_date, NOW()))');
//        return $sessions;
//    }



    public function getReport($filter){
        return Session::orderBy('id', 'desc')->filter($filter);
    }


    /**
     * @param $filter
     * @return mixed
     */
    public function getOnlySessions($filter)
    {
        $data = Session::orderBy('id', 'desc')->filter($filter);
        $data = $data->where('type', 1);
        $limit = $filter->limit ? $filter->limit : 10;
//        if ($filter->branch_id) {
//            $data = $data->whereHas('userCase', function ($query) use ($filter) {
//                $query->where('branch_id', $filter->branch_id);
//            });
//        }
        if($filter->client_id)
            $data=$data->where('client_id',$filter->client_id);
        return $data->paginate($limit);
    }

    /**
     * @param $filter
     * @return mixed
     */
    public function getOnlyRevisions($filter)
    {
        $data = Session::orderBy('id', 'desc')->filter($filter);
        $data = $data->where('type', 2);
        $limit = $filter->limit ? $filter->limit : 10;
//        if ($filter->branch_id) {
//            $data = $data->whereHas('userCase', function ($query) use ($filter) {
//                $query->where('branch_id', $filter->branch_id);
//            });
//        }
        if($filter->client_id)
            $data=$data->where('client_id',$filter->client_id);
        return $data->paginate($limit);
    }



    /**
     * @param $id
     * @return AppResult
     */
    public function getSessionById($id)
    {
        $session = Session::findOrfail($id);
        return AppResult::success($session);
    }

    /**
     * @param $payload
     * @return Session
     */
    public function create($payload)
    {
        $session = new Session();
        $session->type = $payload->type;
        $session->link = $payload->link;
        $session->gregorian_date = $payload->gregorian_date;
        $session->hijri_date = $payload->hijri_date;
        $session->session_time = $payload->session_time;
        $session->session_reminder_date = $payload->session_reminder_date;
        $session->session_requirements = $payload->session_requirements;
        $session->destination = $payload->destination;
        $session->session_notes = $payload->session_notes;
        $session->status_id = $payload->status_id;
        $session->admin_id = $payload->admin_id;
        $session->client_id = $payload->client_id;
        $session->case_id = $payload->case_id;
        $session->save();
        return $session;
    }


    /**
     * @param $payload
     * @param $session
     * @return mixed
     */
    public function update($payload, $session)
    {
        if(isset($payload->link))
            $session->link = $payload->link;
        if(isset($payload->type))
            $session->type = $payload->type;
        if (isset($payload->gregorian_date)) {
            $session->gregorian_date = $payload->gregorian_date;
            $session->hijri_date = DateHelper::getInstance()->gregorianToHijri($payload->gregorian_date);
        }
        if(isset($payload->session_time))
            $session->session_time = $payload->session_time;
        if(isset($payload->session_reminder_date))
            $session->session_reminder_date = $payload->session_reminder_date;
        if(isset($payload->session_requirements))
            $session->session_requirements = $payload->session_requirements;
        if(isset($payload->session_notes))
            $session->session_notes = $payload->session_notes;
        if(isset($payload->status_id))
            $session->status_id = $payload->status_id;
        if(isset($payload->admin_id))
            $session->admin_id = $payload->admin_id;
        if(isset($payload->client_id))
            $session->client_id = $payload->client_id;
        if(isset($payload->case_id))
            $session->case_id = $payload->case_id;
        $session->save();
        return $session;
    }

    /**
     * @param $session
     * @return void
     */
    public function delete($session)
    {
        $session->delete();
    }




}
