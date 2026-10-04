<?php

namespace App\Helpers;

class PriceHelper
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

    /**
     * @param $price
     * @return string
     */
    public function priceFormat($price){
        return number_format((float)$price, 2, '.', '');
    }

    /**
     * @param $totalPrice
     * @param $discount
     * @param $discount_type
     * @return float|int
     */
    function calDiscountPrice($totalPrice, $discount, $discount_type)
    {
        $discount_value = $discount;
        if ($discount_type == 2) {
            $discount_value = ($totalPrice * $discount / 100);
        }
        return $discount_value;
    }

    public function getTax(){
        $user = auth('api')->user();
        if($user)
            return $user->getAccountInfo()->tax;
        return 15;
    }

    public static function dispose()
    {
        self::$instance = null;
    }
}
