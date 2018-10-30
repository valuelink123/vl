<?php
/**
 * Frank Routes
 *
 * URI Prefix: None
 * Controller Namespace: App\Http\Controllers\Frank
 *
 */

use Illuminate\Support\Facades\Route;

Route::get('/ctg/list', 'CtgController@list');
Route::post('/ctg/list', 'CtgController@list');
Route::get('/ctg/process', 'CtgController@process');
Route::post('/ctg/process', 'CtgController@process');
Route::post('/ctg/import', 'CtgController@import');








