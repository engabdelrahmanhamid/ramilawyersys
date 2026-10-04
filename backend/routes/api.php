<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use Illuminate\Http\Request;



/** Start Auth Route **/

Route::middleware('auth:api')->group(function () {
    /** auth private */
    Route::prefix('auth')->group(function()
    {
        Route::post('/change_password', 'AuthController@change_password');
        Route::post('/edit_profile', 'AuthController@edit_profile');
        Route::get('/my_info', 'AuthController@my_info');
        Route::post('/logout', 'AuthController@logout');
        Route::post('/reset_password', 'AuthController@reset_password');
        Route::post('/change_password', 'AuthController@change_password');
        Route::post('/check_active_code', 'AuthController@check_active_code');
        Route::post('/edit_profile', 'AuthController@edit_profile');
        Route::post('/change_lang', 'AuthController@change_lang');
    });

});
/** End Auth Route **/

/** Auth_general */

Route::prefix('auth')->group(function()
{
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');
    Route::post('/forget_password', 'AuthController@forget_password');
    Route::post('/check_password_code', 'AuthController@check_password_code');
    Route::post('/resend_code', 'AuthController@resend_code');
});

