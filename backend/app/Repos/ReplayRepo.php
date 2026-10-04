<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\City;
use App\Models\Replay;
use Validator, Auth, Artisan, Hash, File, Crypt;

class ReplayRepo
{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $data = Replay::orderBy('id', 'desc');
        $limit = $filter->limit ? $filter->limit : 10;
        if($filter->log_id)
            $data=$data->where('log_id',$filter->log_id);
        if($filter->receiver_id)
            $data=$data->where('receiver_id',$filter->receiver_id);
        $data = $data->paginate($limit);
        return $data;
    }

    /**
     * @param $id
     * @return AppResult
     */
    public function getReplayById($id)
    {
        $replay = Replay::findOrfail($id);
        return AppResult::success($replay);
    }
    /**
     * @param $payload
     * @return Replay
     */
    public function create($payload)
    {
        $admin = Auth::user();
        $replay = new Replay();
        $replay->message = $payload->message;
        $replay->log_id = $payload->log_id;
        $replay->receiver_id = $payload->receiver_id;
        $replay->sender_id = $admin->id;
        $replay->save();
        return $replay;
    }

    /**
     * @param $payload
     * @param $replay
     * @return mixed
     */
    public function update($payload, $replay)
    {
        $replay->message = $payload->message;
        $replay->receiver_id = $payload->receiver_id;
        $replay->save();
        return $replay;
    }

    /**
     * @param $replay
     * @return void
     */
    public function delete($replay)
    {
        $replay->delete();
    }




}
