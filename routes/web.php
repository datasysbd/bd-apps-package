<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});

// Route::get('/home', function () {
//     return redirect('/');
// });
Route::get('/ip/get', 'PublicController@ip_get')->name('ip_get');
Route::get('/ip/test', 'PublicController@ip_test')->name('ip_test');
Route::get('/ip/post', 'PublicController@ip_post')->name('ip_post');


Auth::routes();
Route::get('/download', 'SubscriberController@subscribe')->name('subscribe');

Route::group(['middleware' => 'auth'], function () {

    Route::post('/lang', 'HomeController@lang')->name('lang');
    Route::get('/home', 'HomeController@index')->name('dashboard');

    Route::get('/messages', 'MessageController@index')->name('messages');
    Route::post('/messages/dataload', 'MessageController@datatable')->name('messages_load');
    Route::get('/messages/message/{messages?}', 'MessageController@message')->name('messages');
    Route::post('/messages/message/update', 'MessageController@message_update')->name('messages_update');

    Route::get('/subscriptiondatas', 'SubscriptionDataController@index')->name('SubscriptionDatas');
    Route::post('/subscriptiondatas/dataload', 'SubscriptionDataController@datatable')->name('SubscriptionDatas_load');
    Route::get('/subscriptiondatas/subscriptiondata/{subscriptiondata?}', 'SubscriptionDataController@subscriptionData')->name('subscriptionDatas');
    Route::post('/subscriptiondatas/subscriptiondata/update', 'SubscriptionDataController@subscriptionData_update')->name('subscriptionDatas_update');

    // Route::get('/dashboard', function(){})->name('dashboard');
    // Route::get('/profile', function(){})->name('profile');
    // Route::get('/logout', function(){})->name('logout');
});
