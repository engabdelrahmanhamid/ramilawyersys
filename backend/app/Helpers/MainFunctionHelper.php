<?php

/**
 * @return string
 */
function get_baseUrl()
{
    return url('/');
}

/**
 * @return mixed
 */
function get_user_lang()
{
    return Auth::user()->lang;
}

/**
 * @param $price
 * @return string
 */

function priceFormat($price){
    return number_format((float)$price, 2, '.', '');
}

/**
 * @param $date
 * @return false|string
 */
function CustomDateFormat($date){
    return date('m/d/Y', strtotime($date));
}