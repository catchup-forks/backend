<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::group(['namespace' => 'Auth'], function() {

    Route::group(['prefix' => 'auth'], function() {
        // Authentication routes...
        Route::post('login', 'AuthController@postLogin');

        // Registration routes...
        Route::post('register', 'AuthController@postRegister');

    });

    Route::group(['prefix' => 'password'], function() {
        // Password reset link request routes...
        Route::post('email', 'PasswordController@postEmail');

        // Password reset routes...
        Route::post('reset', 'PasswordController@postReset');
    });
});

Route::resource('auth.employees', 'EmployeesController');
Route::resource('auth.groups', 'GroupsController');