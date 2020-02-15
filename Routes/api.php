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
Route::group([
    'prefix' => 'fcm/v1/fcm_token/admin',
    'middleware' => ['auth.api_admin'],
], function ($api) {

    Route::post('store', 'FCMController@store');

});

Route::group([
    'prefix' => 'fcm/v1/fcm_token/manager',
    'middleware' => ['auth.api_manager'],
], function ($api) {

    Route::post('store', 'FCMController@store');

});

Route::group([
    'prefix' => 'fcm/v1/fcm_token/coach',
    'middleware' => ['auth.api_coach'],
], function ($api) {

    Route::post('store', 'FCMController@store');

});

Route::group([
    'prefix' => 'fcm/v1/fcm_token/player',
    'middleware' => ['auth.api_player'],
], function ($api) {

    Route::post('store', 'FCMController@store');

});
