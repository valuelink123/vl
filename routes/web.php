<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::resource('account', 'AccountController');
Route::resource('template', 'TemplateController');
Route::resource('asin', 'AsinController');
Route::resource('user', 'UserController');
Route::get('/total', 'UserController@total')->name('total');
Route::get('/etotal', 'UserController@etotal')->name('etotal');
Route::get('/tran', 'TranController@index')->name('tran');
Route::get('/price', 'PriceController@index')->name('price');
Route::Post('/price/get', 'PriceController@get')->name('getPrice');
Route::Post('/price/getStockAge', 'PriceController@getStockAge')->name('getStockAge');
Route::resource('rule', 'RuleController');
Route::resource('role', 'RoleController');
Route::resource('auto', 'AutoController');
Route::get('/profile', 'UserController@profile')->name('profile');
Route::Post('/profile', 'UserController@profile')->name('profileUpdate');
Route::Post('/inbox/getCategoryJson', 'InboxController@getCategoryJson')->name('getCategoryJson');
Route::get('/inbox/getCategoryJson', 'InboxController@getCategoryJson')->name('getCategoryJson');
Route::resource('inbox', 'InboxController');
Route::get('/inbox/filter/{type}', 'InboxController@index')->name('filterInbox');
Route::Post('/inbox/get', 'InboxController@get')->name('getInbox');
Route::Post('/inbox/getItemGroup', 'InboxController@getItemGroup')->name('getItemGroup');
Route::Post('/inbox/getItem', 'InboxController@getItem')->name('getItem');
Route::Post('/asin/get', 'AsinController@get')->name('getAsin');
Route::Post('/inbox/change', 'InboxController@change')->name('changeInbox');
Route::resource('send', 'SendController');
Route::Post('/send/get', 'SendController@get')->name('getSendbox');
Route::get('/send/deletefile/{filename}', 'SendController@deletefile')->name('deleteFile');
Route::resource('review', 'ReviewController');
Route::resource('star', 'StarController');
Route::resource('phone', 'PhoneController');
Route::Post('/phone/get', 'PhoneController@get')->name('getPhone');
Route::get('/phoneExport', 'PhoneController@export')->name('exportPhone');//call_message功能的导出功能
Route::Post('/star/get', 'StarController@get')->name('getStar');
Route::Post('/review/get', 'ReviewController@get')->name('getReview');
Route::Post('/review/upload', 'ReviewController@upload')->name('uploadReview');
Route::get('/template/ajax/get', 'TemplateController@get')->name('getTemplate');
Route::Post('/saporder/get', 'InboxController@getrfcorder')->name('getRfcOrder');
Route::get('/invoice/get/{id}', 'InboxController@getpdfinvoice')->name('getPdfInvoice');
Route::resource('qa', 'QaiController');
Route::Post('/qa/get', 'QaiController@get')->name('getQa');
Route::get('/laravel-u-editor-server/server', 'CustomuploadController@server')->name('upload');
Route::resource('question', 'QaController');
Route::resource('category', 'CategoryController');
Route::resource('group', 'GroupController');
Route::resource('sellertab', 'SellertabController');
Route::resource('rs', 'RsController');
Route::resource('seller', 'SellerController');
Route::get('/seller/{asin}/{marketplaceid}', 'SellerController@show')->name('viewAsin');
Route::Post('/ratingdetails', 'SellerController@getrating')->name('getRating');
Route::resource('exception', 'ExceptionController');
Route::Post('/exception/get', 'ExceptionController@get')->name('getException');
Route::Post('/exception/getorder', 'ExceptionController@getrfcorder')->name('getExceptionOrder');
Route::Post('/exception/getrepeatorder', 'ExceptionController@getRepeatOrder')->name('getRepeatOrder');
Route::get('/exceptionexport', 'ExceptionController@export')->name('exportException');
Route::get('/reviewexport', 'ReviewController@export')->name('exportReview');
Route::get('/dreportexport', 'SkuController@export')->name('exportDreport');

Route::get('/asinexport', 'AsinController@export')->name('exportAsin');
Route::get('/fees', 'FeesController@index')->name('fees');
Route::Post('/fees/getads', 'FeesController@getads')->name('getads');
Route::Post('/fees/getcpc', 'FeesController@getcpc')->name('getcpc');
Route::Post('/fees/getdeal', 'FeesController@getdeal')->name('getdeal');
Route::Post('/fees/getcoupon', 'FeesController@getcoupon')->name('getcoupon');
Route::Post('/fees/getservice', 'FeesController@getservice')->name('getservice');
Route::resource('mcforder', 'McforderController');
Route::resource('autoprice', 'AutopriceController');
Route::resource('couponkunnr', 'CouponkunnrController');
Route::Post('/couponkunnr/get', 'CouponkunnrController@get')->name('getkunnrs');
Route::Post('/couponkunnr/upload', 'CouponkunnrController@upload')->name('uploadkunnr');
Route::Post('/mcforder/get', 'McforderController@get')->name('getMcforder');
Route::resource('rsgproducts', 'RsgproductsController');
Route::Post('/rsgproducts/get', 'RsgproductsController@get')->name('getrsgproducts');
Route::get('/rsgrequests/process', 'RsgrequestsController@process');
Route::Post('/rsgrequests/process', 'RsgrequestsController@process');
Route::get('/rsgrequests/export', 'RsgrequestsController@export');
Route::Post('/rsgrequests/export', 'RsgrequestsController@export');
Route::resource('rsgrequests', 'RsgrequestsController');
Route::Post('/rsgrequests/get', 'RsgrequestsController@get')->name('getrsgrequests');
Route::resource('salesp', 'SalespController');
Route::resource('rr', 'RrController');
Route::Post('/salesp/get', 'SalespController@get')->name('getSalesp');
Route::resource('proline', 'ProlineController');
Route::Post('/proline/get', 'ProlineController@get')->name('getproline');
Route::get('/skus', 'SkuController@index');
Route::Post('/skus', 'SkuController@update');

Route::get('/nonctg', 'NonctgController@index');//non-ctg功能
Route::Post('/nonctg/get', 'NonctgController@get')->name('getnonctg');//non-ctg功能，ajax请求数据
Route::Post('/nonctg/batchAssignTask', 'NonctgController@batchAssignTask')->name('batchAssignTaskNonctg');//non-ctg功能的分配功能
Route::get('/nonctg/process', 'NonctgController@process');//non-ctg功能的修改页面
Route::post('/nonctg/process', 'NonctgController@process');//non-ctg功能的修改页面
Route::get('/nonctg/export', 'NonctgController@export');//non-ctg功能的下载功能

Route::get('/sendcs/{id}', 'SendController@changeStatus')->name('changeStatus');

Route::Post('/qa/getSonProductByProduct', 'QaiController@getSonProductByProduct');//add Qa页面的For Product联动，根据组别得到子组别的键值对

//crm模块
Route::match(['post','get'],'/crm/export', 'CrmController@export')->name('exportCrm');//导出功能
Route::get('/crm', 'CrmController@index');
Route::get('/crm/show', 'CrmController@show');
Route::get('/crm/edit', 'CrmController@edit');
Route::match(['post','get'],'/crm/update', 'CrmController@update');
Route::match(['post','get'],'/crm/get', 'CrmController@get')->name('getCrm');
Route::match(['post','get'],'/crm/create', 'CrmController@create');
Route::Post('/crm/import', 'CrmController@import');
Route::get('/crm/download', 'CrmController@download');
Route::match(['post','get'],'/crm/batchAssignTask', 'CrmController@batchAssignTask');
