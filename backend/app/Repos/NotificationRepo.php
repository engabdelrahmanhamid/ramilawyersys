<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Models\Notification;
use Validator,Auth,Artisan,Hash,File,Crypt;

class NotificationRepo{

    /**
     * @param $filter
     * @return mixed
     */
    public function get($filter)
    {
        $data=Notification::orderBy('id','desc');
        if($filter->admin_id)
            $data=$data->where('admin_id',$filter->admin_id);
        $limit=$filter->limit ? $filter->limit : 2;
        $data=$data->paginate($limit);
        return $data;
    }


    /**
     * @param $id
     * @return AppResult
     */
    public function getNotificationById($id)
    {
        $notification = Notification::findOrfail($id);
        return AppResult::success($notification);
    }

    /**
     * @param $title
     * @param $description
     * @param $model_id
     * @param $model_type
     * @param $admin_id
     * @return Notification
     */
    public static function create($title,$description,$model_id,$model_type,$admin_id)
    {
        $notification=new Notification();
        $notification->title=$title;
        $notification->description=$description;
        $notification->model_id=$model_id;
        $notification->model_type=$model_type;
        $notification->admin_id=$admin_id;
        $notification->save();
        return $notification;
    }

    /**
     * @param $notification
     * @return mixed
     */
    public function read($notification)
    {
        $notification->is_read=1;
        $notification->save();
        return $notification;
    }


}
