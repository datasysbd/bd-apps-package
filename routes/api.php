<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});




Route::post('send', 'SMSController@smsReceive');
Route::post('send_sms', 'SMSController@smsSend');

Route::post('ussd', 'SMSController@ussdReceive');

Route::post('reg', 'SMSController@addSubscriberPass');
Route::post('checkStatus', 'SMSController@checkStatus');
Route::get('cron_sms_send', 'SMSController@cronSmsSend');
Route::get('sub_check', 'SMSController@checkSubscriptionCodeOfSubscriber');
Route::post('submit_otp', 'SMSController@submitOtp');
Route::get('msg_check', 'SMSController@checkMessageDataOtp');
Route::post('test', 'SMSController@testApi');

//shakiba
Route::get('checkSubscriptionStatus', 'SMSController@checkSubscriptionStatus');
Route::get('checkLastOtp', 'SMSController@getLastOtp');

//smnadim21
Route::post('mirror', 'SMSController@mirror');

