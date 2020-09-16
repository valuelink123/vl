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
Route::get('/homeexport', 'HomeController@export');
Route::get('/home/asins', 'HomeController@asins');
Route::Post('/home/getasins', 'HomeController@getasins');
Route::get('/service', 'ServiceController@index')->name('service');
Route::get('/service/fastSearch', 'ServiceController@fastSearch')->name('fastSearch');
Route::resource('account', 'AccountController');
Route::resource('template', 'TemplateController');
Route::resource('asin', 'AsinController');
Route::resource('user', 'UserController');
Route::get('/total', 'UserController@total')->name('total');
Route::get('/etotal', 'UserController@etotal')->name('etotal');
Route::get('/tran', 'TranController@index')->name('tran');
Route::get('/price', 'PriceController@index')->name('price');
Route::get('/reservedProducts', 'PriceController@reservedProducts')->name('reservedProducts');
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
Route::Post('/inbox/fromService', 'InboxController@fromService')->name('inboxFromService');
Route::get('/inbox/filter/{type}', 'InboxController@index')->name('filterInbox');
Route::Post('/inbox/get', 'InboxController@get')->name('getInbox');
Route::Post('/inbox/getItemGroup', 'InboxController@getItemGroup')->name('getItemGroup');
Route::Post('/inbox/getItem', 'InboxController@getItem')->name('getItem');
Route::Post('/asin/get', 'AsinController@get')->name('getAsin');
Route::Post('/inbox/change', 'InboxController@change')->name('changeInbox');
Route::Post('/inbox/getRsgTaskData', 'InboxController@getRsgTaskData')->name('getRsgTaskData');
Route::resource('send', 'SendController');
Route::Post('/send/get', 'SendController@get')->name('getSendbox');
Route::get('/send/deletefile/{filename}', 'SendController@deletefile')->name('deleteFile');
Route::resource('review', 'ReviewController');
Route::post('/reviewUpdateContentCN', 'ReviewController@updateContentCN');
Route::resource('star', 'StarController');
Route::resource('phone', 'PhoneController');
Route::Post('/phone/get', 'PhoneController@get')->name('getPhone');
Route::get('/phoneExport', 'PhoneController@export')->name('exportPhone');//call_message功能的导出功能
Route::Post('/star/get', 'StarController@get')->name('getStar');
Route::Post('/star/detail', 'StarController@detail')->name('getStarDetail');
Route::get('/star/show/{asin}/{domain}', 'StarController@show')->name('showStar');
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
Route::Post('/exception/fromService', 'ExceptionController@fromService')->name('exceptionFromService');
Route::Post('/exception/get', 'ExceptionController@get')->name('getException');
Route::get('/exception/111/111', 'ExceptionController@get')->name('getException');
Route::Post('/exception/getorder', 'ExceptionController@getrfcorder')->name('getExceptionOrder');
Route::Post('/exception/getrepeatorder', 'ExceptionController@getRepeatOrder')->name('getRepeatOrder');
Route::get('/exceptionexport', 'ExceptionController@export')->name('exportException');
Route::get('/reviewexport', 'ReviewController@export')->name('exportReview');
Route::get('/dreportexport', 'SkuController@export')->name('exportDreport');
Route::get('/config_option', 'ConfigOptionController@index')->name('config_option');
Route::Post('/config_option/get', 'ConfigOptionController@get')->name('config_option_get');
Route::get('/config_option/create', 'ConfigOptionController@create')->name('config_option_create');
Route::Post('/config_option/store', 'ConfigOptionController@store')->name('config_option_store');
Route::get('/config_option/{id}/edit', 'ConfigOptionController@edit')->name('config_option_edit');
Route::Post('/config_option/{id}/update', 'ConfigOptionController@update')->name('config_option_update');

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
// Route::resource('rsgproducts', 'RsgproductsController');
Route::match(['post','get'],'/rsgproducts', 'RsgproductsController@list');//产品列表
Route::get('/rsgproducts/edit', 'RsgproductsController@edit');//编辑产品
Route::post('/rsgproducts/update', 'RsgproductsController@update');//更新产品
Route::match(['post','get'],'/rsgproducts/export', 'RsgproductsController@export');//下载产品列表
Route::match(['post','get'],'/rsgtask', 'RsgproductsController@rsgtask');//rsgTask任务列表

Route::match(['post','get'],'/mrp', 'MrpController@list');

Route::Post('/rsgproducts/get', 'RsgproductsController@get')->name('getrsgproducts');
Route::get('/rsgrequests/process', 'RsgrequestsController@process');
Route::Post('/rsgrequests/process', 'RsgrequestsController@process');
Route::get('/rsgrequests/export', 'RsgrequestsController@export');
Route::Post('/rsgrequests/export', 'RsgrequestsController@export');
Route::Post('/rsgrequests/updateAction', 'RsgrequestsController@updateAction');
Route::resource('rsgrequests', 'RsgrequestsController');
Route::Post('/rsgrequests/get', 'RsgrequestsController@get')->name('getrsgrequests');
Route::get('/rsgrequestsUpdateHistory', 'RsgrequestsController@updateHistory');
Route::resource('salesp', 'SalespController');
Route::resource('rr', 'RrController');
Route::Post('/salesp/get', 'SalespController@get')->name('getSalesp');
Route::resource('proline', 'ProlineController');
Route::Post('/proline/get', 'ProlineController@get')->name('getproline');
Route::get('/skus', 'SkuController@index');
Route::Post('/skus', 'SkuController@update');
Route::get('/budgets', 'BudgetController@index');
Route::Post('/budgets', 'BudgetController@index');
Route::get('/reqrev', 'ReqrevController@index');
Route::Post('/reqrev', 'ReqrevController@index');
Route::Post('/budget', 'BudgetController@update');
Route::get('/budgets/edit', 'BudgetController@edit');
Route::Post('/budgets/upload', 'BudgetController@upload')->name('uploadBudget');
Route::get('/budgets/export', 'BudgetController@export')->name('exportBudgets');
Route::get('/budgets/exportsku', 'BudgetController@exportSku')->name('exportBudgetSku');
Route::match(['post','get'],'/budgets/create', 'BudgetController@create');
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
Route::get('/crm/trackLogAdd','CrmController@trackLogAdd')->name('trackLogAdd');
Route::post('/crm/trackLogStore','CrmController@trackLogStore')->name('trackLogStore');
Route::post('/crm/getTrackLog','CrmController@getTrackLog')->name('getTrackLog');
Route::post('/crm/getRsgRequestList','CrmController@getRsgRequestList')->name('getRsgRequestList');

Route::resource('task', 'TaskController');
Route::Post('/task/get', 'TaskController@get')->name('getTask');
Route::Post('/taskajaxupdate', 'TaskController@taskAjaxUpdate')->name('taskAjaxUpdate');
Route::Post('/getasinbysku', 'AsinController@getAsinBySku')->name('getAsinBySku');


//产品调拨系统模块
Route::get('/productTransfer', 'ProductTransferController@list');
Route::post('/productTransfer', 'ProductTransferController@list');
Route::post('/productTransfer/updateDays', 'ProductTransferController@updateDays');//批量更新天数操作
Route::post('/productTransfer/reply', 'ProductTransferController@reply');//申请调拨操作
Route::post('/productTransfer/ignore', 'ProductTransferController@ignore');//忽略调拨操作
Route::get('/productTransfer/replyList', 'ProductTransferController@replyList');//调拨申请列表
Route::post('/productTransfer/replyList', 'ProductTransferController@replyList');//调拨申请列表
Route::post('/productTransfer/updateReply', 'ProductTransferController@updateReply');//修改申请调拨内容，例如修改调拨数量
Route::post('/productTransfer/showLog', 'ProductTransferController@showLog');//申请列表显示操作日志
Route::post('/productTransfer/replyAudit', 'ProductTransferController@replyAudit');//审核调拨请求的批准，拒绝状态
Route::get('/productTransfer/replyExport', 'ProductTransferController@replyExport');//调拨请求的下载功能
//RsgUser模块
Route::match(['post','get'],'/rsgUser/list', 'RsgUserController@list');//rsgUser列表展示

Route::post('/star/updatePost', 'StarController@updatePost');//更新帖子状态和帖子类型

//跟卖追踪

Route::get('/hijack/index1/', 'hijack\\HijackController@index1')->name('index1');
Route::get('/hijack/index2/', 'hijack\\HijackController@index2')->name('index2');
Route::get('/hijack/index/', 'hijack\\HijackController@index')->name('index');
Route::get('/hijack/detail/', 'hijack\\HijackController@detail')->name('detail');
Route::post('/hijack/index', 'hijack\\HijackController@index');//查询产品信息
Route::post('/hijack/asinSearch', 'hijack\\HijackController@asinSearch');//查询产品信息
Route::post('/hijack/updateAsinSta', 'hijack\\HijackController@updateAsinSta');//修改asin 开启关闭
Route::post('/hijack/updateAsinStaAll', 'hijack\\HijackController@updateAsinStaAll');//批量修改asin 开启关闭
Route::post('/hijack/resellingList', 'hijack\\HijackController@resellingList');//查询跟卖列表信息
Route::post('/hijack/resellingDetail', 'hijack\\HijackController@resellingDetail');
Route::post('/hijack/resellingDetail2', 'hijack\\HijackController@resellingDetail2');
Route::post('/hijack/upResellingDetail', 'hijack\\HijackController@upResellingDetail');//修改 detail 备注信息
Route::post('/hijack/hijackExport', 'hijack\\HijackController@hijackExport');//导出
//跟卖追踪 END


//RSG MarketingPlan
Route::get('/marketingPlan/index', 'MarketingPlanController@index')->name('marketingPlan');
Route::get('/marketingPlan/detail', 'MarketingPlanController@detail')->name('detail');
Route::match(['post','get'],'/marketingPlan/rsgList', 'MarketingPlanController@rsgList')->name('rsgList');
Route::post('/marketingPlan/index1', 'MarketingPlanController@index1')->name('marketingPlan');
Route::post('/marketingPlan/showData', 'MarketingPlanController@showData');//展示基础信息
Route::match(['post'],'/marketingPlan/updatePlan', 'MarketingPlanController@updatePlan');//修改计划信息
Route::post('/marketingPlan/addMarketingPlan', 'MarketingPlanController@addMarketingPlan');//新增接口
Route::match(['post','get'],'/marketingPlan/detailEdit', 'MarketingPlanController@detailEdit')->name('detailEdit');
Route::match(['post','get'],'/marketingPlan/timingUpdate', 'MarketingPlanController@timingUpdate');//定时刷新 已完结
Route::match(['post','get'],'/marketingPlan/achieveGoals', 'MarketingPlanController@achieveGoals');//定时更新 完成时间
Route::match(['post','get'],'/marketingPlan/delfiles', 'MarketingPlanController@delfiles');//删除图片
Route::post('/marketingPlan/getAsinDailyReport', 'MarketingPlanController@getAsinDailyReport');

//Manage Distribute Time
Route::match(['post','get'],'/manageDistributeTime/safetyStockDays', 'ManageDistributeTimeController@safetyStockDays');
Route::post('/manageDistributeTime/updateSafetyStockDays', 'ManageDistributeTimeController@updateSafetyStockDays');
Route::get('/manageDistributeTime/exportSafetyStockDays', 'ManageDistributeTimeController@exportSafetyStockDays');
Route::match(['post','get'],'/manageDistributeTime/fba', 'ManageDistributeTimeController@fba');
Route::post('/manageDistributeTime/updateFba', 'ManageDistributeTimeController@updateFba');
Route::get('/manageDistributeTime/exportFba', 'ManageDistributeTimeController@exportFba');
Route::match(['post','get'],'/manageDistributeTime/fbm', 'ManageDistributeTimeController@fbm');
Route::post('/manageDistributeTime/updateFbm', 'ManageDistributeTimeController@updateFbm');
Route::get('/manageDistributeTime/exportFbm', 'ManageDistributeTimeController@exportFbm');
Route::match(['post','get'],'/manageDistributeTime/internationalTransportTime', 'ManageDistributeTimeController@internationalTransportTime');
Route::post('/manageDistributeTime/upload', 'ManageDistributeTimeController@upload');
Route::post('/manageDistributeTime/updateTransportTime', 'ManageDistributeTimeController@updateTransportTime');
Route::post('/manageDistributeTime/batchUpdateTransportTime', 'ManageDistributeTimeController@batchUpdateTransportTime');
Route::get('/manageDistributeTime/exportTransportTime', 'ManageDistributeTimeController@exportTransportTime');

//投入产出分析
Route::resource('roi', 'RoiController');
Route::Post('/roi/analyse', 'RoiController@analyse')->name('roiAnalyse');
Route::Post('/roi/updateRecord', 'RoiController@updateRecord');
Route::Post('/roi/get', 'RoiController@get')->name('getRoi');
//下载ROI列表。如果用/roi/export, 实际上是进入了resource里面的show动作：GET /roi/{id}
Route::get('/roi_export', 'RoiController@export');
Route::get('/roi_export_show_page', 'RoiController@exportShowPage');
Route::Post('/roi_archive', 'RoiController@archive');
Route::get('/roi_copy', 'RoiController@copy');
Route::post('/roi_fresh_time', 'RoiController@roiRefreshTime');
Route::get('/roi_delete', 'RoiController@deleteRecord');

Route::match(['post','get'],'/mrp', 'MrpController@index');
Route::match(['post','get'],'/mrp/list', 'MrpController@list');
Route::get('/mrp/edit', 'MrpController@edit');
Route::post('/mrp/update', 'MrpController@update');
Route::get('/mrp/export', 'MrpController@export');
Route::post('/mrp/weekupdate', 'MrpController@weekupdate');
Route::post('/mrp/updateStatus', 'MrpController@updateStatus');
Route::get('/mrp/asinexport', 'MrpController@asinExport');
Route::post('/mrp/import', 'MrpController@import');

//CPFR协同补货
Route::get('/cpfr/index', 'CpfrController@index')->name('index');
Route::get('/cpfr/allocationProgress', 'CpfrController@allocationProgress')->name('allocationProgress');

Route::match(['post','get'],'/shipment/index', 'ShipmentController@index');//调拨需求列表
Route::match(['post','get'],'/shipment/addShipment', 'ShipmentController@addShipment');//新增调拨需求列表
Route::match(['post','get'],'/shipment/detailShipment', 'ShipmentController@detailShipment');//调拨需求  详情页面
Route::match(['post','get'],'/shipment/upShipment', 'ShipmentController@upShipment');//新增调拨需求列表
Route::match(['post'],'/shipment/upAllStatus', 'ShipmentController@upAllStatus');//批量更新状态
Route::match(['post','get'],'/shipment/purchaseList', 'ShipmentController@purchaseList');//采购列表页面
Route::match(['post'],'/shipment/upAllPurchase', 'ShipmentController@upAllPurchase');//批量更改 采购状态
Route::match(['post','get'],'/shipment/detailPurchase', 'ShipmentController@detailPurchase');// 采购 详情
Route::match(['post','get'],'/shipment/addPurchase', 'ShipmentController@addPurchase');// 采购 新增
Route::match(['post'],'/shipment/upPurchase', 'ShipmentController@upPurchase');// 采购 更新
Route::match(['post','get'],'/shipment/allotProgress', 'ShipmentController@allotProgress');//调拨进度列表
Route::match(['post'],'/shipment/upCargoData', 'ShipmentController@upCargoData');//修改 大货资料

Route::match(['post'],'/shipment/getNextData', 'ShipmentController@getNextData');//请求下一级 asin 或者 sellersku列表
Route::match(['post'],'/shipment/getSellerSku', 'ShipmentController@getSellerSku');
Route::match(['post'],'/shipment/importExecl', 'ShipmentController@importExecl');//上传 表格
Route::match(['post'],'/cpfr/importExecl', 'CpfrController@importExecl');//上传 表格
Route::match(['post'],'/shipment/getBoxDetail', 'ShipmentController@getBoxDetail');//查询 装箱数据信息
Route::match(['post'],'/shipment/upShippingMethod', 'ShipmentController@upShippingMethod');//修改 发货方式
Route::match(['post'],'/shipment/exportExecl', 'ShipmentController@exportExecl');//调拨进度 下载
Route::match(['post'],'/shipment/upAllAllot', 'ShipmentController@upAllAllot');//批量修改调拨状态
Route::match(['post'],'/shipment/upShippmentID', 'ShipmentController@upShippmentID');//修改ShippmentID
Route::match(['post'],'/shipment/upReceiptsNum', 'ShipmentController@upReceiptsNum');//修改跟踪单号
Route::match(['post'],'/shipment/upShipment2', 'ShipmentController@upShipment2');//调拨进度页面修改调拨计划
Route::match(['post'],'/shipment/upAdjustmentQquantity', 'ShipmentController@upAdjustmentQquantity');//修改 调整需求数量
Route::match(['post'],'/shipment/upAlltoStatus', 'ShipmentController@upAlltoStatus');//修改 调拨状态
Route::match(['post'],'/shipment/getBarcodepub', 'ShipmentController@getBarcodepub');//获取 条形码信息
Route::match(['post'],'/shipment/getCargoData', 'ShipmentController@getCargoData');//获取大货资料
Route::match(['post','get'],'/shipment/downloadPDF', 'ShipmentController@downloadPDF');//条形码PDF
Route::match(['post','get'],'/shipment/getShippmentIDList', 'ShipmentController@getShippmentIDList');//获取shippmentid 及订单号
Route::match(['post','get'],'/shipment/addShippments', 'ShipmentController@addShippments');//增加shippment 或 跟踪单号 单据号
Route::match('post','/shipment/delShippments', 'ShipmentController@delShippments');//删除shippment 或 跟踪单号 单据号
//


Route::get('/marketingPlan/test', 'MarketingPlanController@test')->name('marketingPlan');


Route::get('/cpfr/purchase', 'CpfrController@purchase')->name('purchase');
Route::get('/cpfr/barcode', 'CpfrController@barcode')->name('barcode');
//权限管理
Route::get('/management', 'ManagementController@index')->name('index');

// Route::get('/mws', 'MwsController@index')->name('index'); //mws后台管理

Route::get('/getOrderDataBySap', 'ApiController@getOrderDataBySap')->name('getOrderDataBySap'); //获取sap接口数据