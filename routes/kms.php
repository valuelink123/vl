<?php
/**
 * KMS routes
 *
 * URI Prefix: /kms
 * Controller Namespace: App\Http\Controllers\Frank
 *
 */

use Illuminate\Support\Facades\Route;

Route::get('/productguide', 'BrandLineController@index');
Route::post('/productguide/get', 'BrandLineController@get');
Route::post('/email-detail-right-bar-data', 'BrandLineController@getEmailDetailRightBar');

Route::get('/videolist', 'VideoListController@index');
Route::post('/videolist/get', 'VideoListController@get');
Route::get('/videolist/import', 'VideoListController@import');
Route::post('/videolist/import', 'VideoListController@create');

Route::get('/usermanual', 'UserManualController@index');
Route::post('/usermanual/get', 'UserManualController@get');
Route::get('/usermanual/import', 'UserManualController@import');
Route::post('/usermanual/import', 'UserManualController@create');//添加数据
Route::get('/usermanual/edit', 'UserManualController@edit');//编辑usermanual操作
Route::post('/usermanual/update', 'UserManualController@update');//更新usermanual操作

Route::get('/partslist', 'PartsListController@index');
Route::post('/partslist/get', 'PartsListController@get');
Route::get('/partslist/export', 'PartsListController@export');
Route::post('/partslist/subitems', 'PartsListController@getSubItemList');
Route::post('/partslist/updateStatus', 'PartsListController@updateStatus');//partslist列表页面设置账号机是否有效
Route::post('/stocklist', 'PartsListController@getStockList');

Route::get('/notice', 'NoticeCenterController@index');
Route::post('/notice/get', 'NoticeCenterController@get');
Route::get('/notice/create', 'NoticeCenterController@edit');
Route::post('/notice/create', 'NoticeCenterController@create');

Route::get('/learn', 'LearnCenterController@index');
Route::post('/learn/get', 'LearnCenterController@get');
Route::get('/learn/create', 'LearnCenterController@edit');
Route::post('/learn/create', 'LearnCenterController@create');








