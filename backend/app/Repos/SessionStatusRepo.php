<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\CaseStatus;
use App\Models\SessionStatus;
use Validator, Auth, Artisan, Hash, File, Crypt;

class SessionStatusRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $data = SessionStatus::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        $data = $data->paginate($limit);
        return $data;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getSessionStatusById($id)
    {
        $sessionStatus = SessionStatus::findOrfail($id);
        return AppResult::success($sessionStatus);
    }

    /**
     * @param $payload
     * @return SessionStatus
     */
    public function create($payload)
    {
        $sessionStatus = new SessionStatus();
        $sessionStatus->name = $payload->name;
        $sessionStatus->save();
        return $sessionStatus;
    }

    /**
     * @param $payload
     * @param $sessionStatus
     * @return mixed
     */
    public function update($payload, $sessionStatus)
    {
        $sessionStatus->name = $payload->name;
        $sessionStatus->save();
        return $sessionStatus;
    }

    /**
     * @param $sessionStatus
     * @return void
     */
    public function delete($sessionStatus)
    {
        $sessionStatus->delete();
    }


}
