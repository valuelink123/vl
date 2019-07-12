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
Route::post('/ctg/batchassigntask', 'CtgController@batchAssignTask');
Route::get('/ctg/list/process', 'CtgController@process');
Route::post('/ctg/list/process', 'CtgController@process');
Route::get('/ctg/export', 'CtgController@export');
Route::post('/ctg/export', 'CtgController@export');

Route::get('/ctg/create', 'CtgController@create');//添加ctg数据
Route::post('/ctg/store', 'CtgController@store');//添加ctg数据










