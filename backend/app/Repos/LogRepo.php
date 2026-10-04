<?php

namespace App\Repos;
use App\Models\Log;
use Validator, Auth, Artisan, Hash, File, Crypt;

class LogRepo
{

    /**
     * @param $data
     * @param $model_name
     * @param $operation
     */
    public static function create($data,$model_name,$operation)
    {
        $log = new Log();
        $log->model_name = $model_name;
        $log->model_id = $data->id;
        $log->description = Self::getDescription($data,$model_name,$operation);
        $log->operation = $operation;
        $log->admin_id = Auth::user()->id;
        $log->save();
    }


    /**
     * @param $data
     * @param $model_name
     * @param $operation
     * @return void
     */
    public static function logStatusUpdate($data, $model_name, $operation)
    {
        $log = new Log();
        $log->model_name = $model_name;
        $log->model_id = $data->id;
        $log->description = Self::getDescriptionOfStatus($data, $model_name, $operation);
        $log->operation = $operation;
        $log->admin_id = Auth::id();
        $log->save();
    }

    /**
     * @param $operation
     * @return void
     */
    public static function logAuthAction($user,$operation)
    {
        $log = new Log();
        $log->model_name = 'admin';
        $log->model_id = $user->id;
        $log->description = Self::getDescriptionOfAuthAction($user,$operation);
        $log->operation = $operation;
        $log->admin_id = $user->id;
        $log->save();
    }




    /**
     * @param $data
     * @param $model_name
     * @param $operation
     * @return string
     */
    private static function getDescription($data,$model_name,$operation){
        $text= ' قام  ' .Auth::user()->name;
        $text.=' ب '.Self::getNameOfOperation($operation);
        $text.=' ' .Self::getNameOfModel($model_name).' '.$data->name;
        return $text;
    }

    /**
     * @param $data
     * @param $model_name
     * @param $operation
     * @return string
     */
    private static function getDescriptionOfStatus($data, $model_name, $operation)
    {
        $statusName = '';

        if ($model_name === 'case') {
            $statusName = $data->status->name ?? 'undefined status';
        } elseif ($model_name === 'client') {
            $statusName = $data->status->name ?? 'undefined status';
        }

        $text = ' قام  ' . Auth::user()->name;
        $text .= ' ب ' . Self::getNameOfOperation($operation);
        $text .= ' حالة ' . Self::getNameOfModel($model_name) . ' ' . $data->name;
        $text .= ' إلى ' . $statusName;
        return $text;
    }

    /**
     * @param $operation
     * @return string
     */
    private static function getDescriptionOfAuthAction($user,$operation)
    {
        $text = ' قام ' . $user->name;
        $text .= ' ب ' . Self::getNameOfOperation($operation);

        return $text;
    }

    /**
     * @param $operation
     * @return string
     */
    private static function getNameOfOperation($operation){
        $array=[
            'add'=>'اضافة',
            'update'=>'تعديل',
            'delete'=>'حذف',
            'update_status'=>'تعديل حالة' ,
            'login'=>'تسجيل الدخول',
            'logout' => 'تسجيل الخروج'
        ];
        return $array[$operation];
    }

    /**
     * @param $model_name
     * @return string
     */
    private static function getNameOfModel($model_name){
        $array=['client'=>'العميل' , 'case'=>'القضيه' , 'session'=>'الجلسه' , 'service'=>'الخدمه' , 'task'=> 'مهمه'];
        return $array[$model_name];
    }

}
