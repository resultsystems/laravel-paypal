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

$prefix = Config::get('paypal.route_prefix', '/paypal/');

Route::post($prefix.'ipn', "ResultSystems\Paypal\Http\Controllers\PaypalController@ipn");
//Route::get($prefix.'ipn', "ResultSystems\Paypal\Http\Controllers\PaypalController@ipn");

