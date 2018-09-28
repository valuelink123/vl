<?php
/**
 * KMS routes
 *
 * URI Prefix: /kms
 * Controller Namespace: App\Http\Controllers\Frank
 *
 */

use Illuminate\Support\Facades\Route;

Route::get('/brandline', 'BrandLineController@index');
Route::post('/brandline/get', 'BrandLineController@get');

Route::get('/videolist', 'VideoListController@index');
Route::post('/videolist/get', 'VideoListController@get');
Route::get('/videolist/import', 'VideoListController@import');
Route::post('/videolist/import', 'VideoListController@create');

Route::get('/usermanual', 'UserManualController@index');
Route::post('/usermanual/get', 'UserManualController@get');
Route::get('/usermanual/import', 'UserManualController@import');
Route::post('/usermanual/import', 'UserManualController@create');

Route::get('/partslist', 'PartsListController@index');
Route::post('/partslist/get', 'PartsListController@get');












