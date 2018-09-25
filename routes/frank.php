<?php
/**
 * Created by PhpStorm.
 */

Route::get('/kms/brandline', 'BrandLineController@index');
Route::post('/kms/brandline/get', 'BrandLineController@get');

Route::get('/kms/videolist', 'VideoListController@index');
Route::post('/kms/videolist/get', 'VideoListController@get');
Route::get('/kms/videolist/import', 'VideoListController@import');
Route::post('/kms/videolist/import', 'VideoListController@create');

Route::get('/kms/usermanual', 'UserManualController@index');
Route::post('/kms/usermanual/get', 'UserManualController@get');
Route::get('/kms/usermanual/import', 'UserManualController@import');
Route::post('/kms/usermanual/import', 'UserManualController@create');

Route::get('/kms/partslist', 'PartsListController@index');
Route::post('/kms/partslist/get', 'PartsListController@get');
