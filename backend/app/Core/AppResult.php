<?php


namespace App\Core;

define('SUCCESS' , 1);
define('ERROR' , 0);

class AppResult
{
    public $operationType = null ;
    public $data = null ;
    public $error = null ;

    private function __construct($operationType , $data , $error){
        $this->operationType = $operationType ;
        $this->data = $data;
        $this->error = $error;
    }

    public static function success($data){
        return new self(SUCCESS,$data,null);
    }

    public static function error($error){
        return new self(ERROR,null,$error);
    }

}
