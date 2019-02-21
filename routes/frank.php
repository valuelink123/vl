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

Route::get('/cb/list', 'CtgController@cblist');
Route::post('/cb/list', 'CtgController@cblist');
Route::post('/cb/batchassigntask', 'CtgController@cbbatchAssignTask');
Route::get('/cb/list/process', 'CtgController@cbprocess');
Route::post('/cb/list/process', 'CtgController@cbprocess');

Route::get('/bg/list', 'CtgController@bglist');
Route::post('/bg/list', 'CtgController@bglist');
Route::post('/bg/batchassigntask', 'CtgController@bgbatchAssignTask');
Route::get('/bg/list/process', 'CtgController@bgprocess');
Route::post('/bg/list/process', 'CtgController@bgprocess');








