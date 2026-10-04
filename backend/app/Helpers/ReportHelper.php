<?php

namespace App\Helpers;

class ReportHelper
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function hourly()
    {
        return [
            1 => ['start' => '00:00:00', 'end' => '03:59:59', 'title' => '00:00-03:59'],
            2 => ['start' => '04:00:00', 'end' => '07:59:59', 'title' => '04:00-07:59'],
            3 => ['start' => '08:00:00', 'end' => '11:59:59', 'title' => '08:00-11:59'],
            4 => ['start' => '12:00:00', 'end' => '15:59:59', 'title' => '12:00-15:59'],
            5 => ['start' => '16:00:00', 'end' => '19:59:59', 'title' => '16:00-19:59'],
            6 => ['start' => '20:00:00', 'end' => '23:59:59', 'title' => '20:00-23:59'],
        ];
    }

    public function monthly(){
        return [
            1=>['start'=>1,'end'=>5,'title'=>'1-5'],
            2=>['start'=>6,'end'=>10,'title'=>'6-10'],
            3=>['start'=>11,'end'=>15,'title'=>'11-15'],
            4=>['start'=>16,'end'=>20,'title'=>'16-20'],
            5=>['start'=>21,'end'=>25,'title'=>'21->25'],
            6=>['start'=>26,'end'=>31,'title'=>'26-31']
        ];
    }

    public function yearly(){
        return [
            1=>['title'=>'يناير'],
            2=>['title'=>'فبراير'],
            3=>['title'=>'مارس'],
            4=>['title'=>'ابريل'],
            5=>['title'=>'مايو'],
            6=>['title'=>'يونيو'],
            7=>['title'=>'يوليو'],
            8=>['title'=>'اغسطس'],
            9=>['title'=>'سبتمبر'],
            10=>['title'=>'اكتوبر'],
            11=>['title'=>'نوفمبر'],
            12=>['title'=>'ديسمبر'],
        ];
    }

    public static function dispose()
    {
        self::$instance = null;
    }
}

