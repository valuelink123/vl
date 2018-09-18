<?php
/**
 * Created by PhpStorm.
 */

Route::get('/kms/brandline', 'BrandLineController@index');
Route::post('/kms/brandline/get', 'BrandLineController@get');
