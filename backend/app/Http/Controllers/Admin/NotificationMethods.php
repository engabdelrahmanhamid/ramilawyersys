<?php
namespace App\Http\Controllers\Admin;
use App\Http\Resources\OrderResource;
use App\Models\Notification;
use App\Models\Order;
use App\Repos\NotificationRepo;
use Mail;
use App\Models\User;
use App\Models\Notifcation;

define('API_ACCESS_KEY', env('FCM_LEGACY_KEY', ''));

class NotificationMethods
{
    /**
     * @param $user
     * @param $title
     * @param $desc
     * @param $redirect_id
     * @param $admin
     * @param $userOrsitter
     * @param $type
     * @return Exeption|bool|\Exception|string
     */
    public static function senNotificationToSingleUser($admin, $title, $desc,$mode_id,$model_type)
    {


        $url = 'https://fcm.googleapis.com/fcm/send';
        $msg = array(
            'body'  => $desc,
            'title'     => $title,
            'vibrate'   => 1,
            'sound'     => 1,
            'click_action'=>(string)$mode_id,
            'status'=>1,
            'redirect_id'=>$mode_id,
        );
        $fields = array(
            'to' => $admin->fire_base,
            'data' => $msg,
            'notification' => $msg,
        );
        $headers = array(
            'Authorization: key='.API_ACCESS_KEY,
            'Content-type: Application/json'
        );
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
        } catch(Exeption $e){
            return $e ;
        }
        NotificationRepo::create($title,$desc,$mode_id,$model_type,$admin->id);
        return $result ;
    }


    /**
     * @param $title
     * @param $desc
     * @param $user_id
     * @param $redirect_id
     * @param $admin
     * @param $userOrSitter
     * @return Notifcation
     */
    public static function saveNotification($title,$desc,$user_id,$redirect_id,$admin,$userOrSitter)
    {
        $notification=new Notification();
        $notification->title=$title;
        $notification->desc=$desc;
        $notification->user_id=$user_id;
        $notification->redirect_id=$redirect_id;
        $notification->admin=$admin;
        $notification->userOrSitter=$userOrSitter;
        $notification->save();
        return $notification;
    }
}
