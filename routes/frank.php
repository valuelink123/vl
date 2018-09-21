<?php
/**
 * Created by PhpStorm.
 */

Route::get('/kms/brandline', 'BrandLineController@index');
Route::post('/kms/brandline/get', 'BrandLineController@get');

Route::get('/kms/videolist', 'VideoListController@index');
Route::post('/kms/videolist/get', 'VideoListController@get');
Route::get('/kms/videolist/new', 'VideoListController@create');
