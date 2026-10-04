<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function apiResponseData($data = null, $message = null, $code = 200)
    {
        $array = [
            'status' =>  1,
            'message' => $message,
            'data'=>$data,
        ];
        return response($array, $code);
    }


    public function apiResponseMessage($status,$message = null,$code = 200)
    {
        $array = [
            'status' =>  $status,
            'message' => $message,
            'data'=>null,
        ];

        return response($array, $code);
    }
}
