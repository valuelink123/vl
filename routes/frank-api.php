<?php
/**
 * Frank Api Routes
 *
 * URI Prefix: None
 * Controller Namespace: App\Http\Controllers\Frank
 *
 */

use Illuminate\Support\Facades\Route;

Route::post('/ctg/import', 'CtgController@import');
Route::post('/b1g1/import', 'CtgController@b1g1import');
Route::post('/cashback/import', 'CtgController@cashbackimport');








