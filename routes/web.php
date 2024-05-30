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
Route::Post('/send/batchUpdate', 'SendController@batchUpdate')->name('sendBatchUpdate');

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
Route::Post('/inbox/unbindInboxOrder', 'InboxController@unbindInboxOrder');//收件箱里面解绑订单号操作
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
Route::get('/exception/download', 'ExceptionController@download');
Route::resource('exception', 'ExceptionController');
Route::Post('/exception/fromService', 'ExceptionController@fromService')->name('exceptionFromService');
Route::Post('/exception/get', 'ExceptionController@get')->name('getException');
Route::get('/exception/111/111', 'ExceptionController@get')->name('getException');
Route::Post('/exception/getorder', 'ExceptionController@getrfcorder')->name('getExceptionOrder');
Route::Post('/exception/getrepeatorder', 'ExceptionController@getRepeatOrder')->name('getRepeatOrder');
Route::get('/exceptionexport', 'ExceptionController@export')->name('exportException');
Route::Post('/exception/upload', 'ExceptionController@upload');
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
Route::get('/mcforderExport', 'McforderController@mcforderExport');//mcforder功能的导出
Route::Post('/mcforder/toSap', 'McforderController@toSap');//mcforder功能中,把重发单插入到sap系统中
Route::Post('/mcforder/getBindOrder', 'McforderController@getBindOrder');//mcforder功能中,得到可以让用户绑定的原始订单
Route::Post('/mcforder/bindOrder', 'McforderController@bindOrder');//mcforder功能中,绑定原始订单操作
// Route::resource('rsgproducts', 'RsgproductsController');
Route::match(['post','get'],'/rsgproducts', 'RsgproductsController@list');//产品列表
Route::get('/rsgproducts/edit', 'RsgproductsController@edit');//编辑产品
Route::post('/rsgproducts/update', 'RsgproductsController@update');//更新产品
Route::match(['post','get'],'/rsgproducts/export', 'RsgproductsController@export');//下载产品列表
Route::match(['post','get'],'/rsgtask', 'RsgproductsController@rsgtask');//rsgTask任务列表

Route::match(['post','get'],'/mrp', 'MrpController@list');
Route::Post('/rsgrequests/import', 'RsgrequestsController@import');
Route::get('/rsgrequests/download', 'RsgrequestsController@download');
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
Route::Post('/salesp/upload', 'SalespController@upload');
Route::resource('rr', 'RrController');
Route::Post('/salesp/get', 'SalespController@get')->name('getSalesp');
Route::resource('proline', 'ProlineController');
Route::Post('/proline/get', 'ProlineController@get')->name('getproline');
Route::get('/skus', 'SkuController@index');
Route::get('/skus/keywords', 'SkuController@keywords');
Route::Post('/skus/keywords', 'SkuController@updatekeywords');
Route::Post('/skus', 'SkuController@update');
Route::Post('/skus/upload', 'SkuController@upload');
Route::Post('/skus/batchUpdate', 'SkuController@batchUpdate');
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

Route::get('/customer', 'CustomerController@index');
Route::post('/customer/get', 'CustomerController@get');

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

Route::match(['post','get'],'/hijack/index1/', 'hijack\\HijackController@index1')->name('index1');
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
Route::get('/mrp/download', 'MrpController@download');//下载上传的execl表格模板
Route::post('/get22WeekDate', 'Controller@get22WeekDate');//得到22周日期

//CPFR协同补货
Route::get('/cpfr/index', 'CpfrController@index')->name('index');
Route::get('/cpfr/editShipmentRequest', 'CpfrController@editShipmentRequest')->name('editShipmentRequest');
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

Route::get('/ccp', 'CcpController@index')->name('index'); //mws后台管理
Route::post('/ccp/showTotal', 'CcpController@showTotal')->name('showTotal'); //ccp功能展示顶部统计数据
Route::post('/ccp/list', 'CcpController@list')->name('ccpList');//ccp功能的列表展示
Route::get('/ccp/export', 'CcpController@export')->name('ccpExport');//ccp功能的列表展示
Route::post('/showAccountBySite', 'Controller@showTheAccountBySite')->name('showTheAccountBySite');//ccp功能的列表展示
Route::get('ccp/showOrderList', 'CcpController@showOrderList')->name('showOrderList');//ccp功能的列表中点击订单数查看订单列表的功能

//测试
Route::get('/test', 'TestController@test'); //mws后台管理

//销售额预警
Route::resource('salesAlert', 'SalesAlertController');

Route::match(['post','get'],'/getSalesAlertSku', 'SalesAlertController@salesAlertSku');
Route::match(['post','get'],'/getSalesAlertWeek', 'SalesAlertController@SalesAlertWeek');
Route::match(['post','get'],'/salesAlert/totalSku/list', 'SalesAlertController@salesAlertTotalSkuList'); //sku维度
Route::match(['post','get'],'/salesAlert/totalWeek/list', 'SalesAlertController@salesAlertTotalWeekList'); //sku维度

//ccp-ad模块
Route::get('/ccp/adProduct', 'CcpAdProductController@index'); //
Route::post('/ccp/adProduct/showTotal', 'CcpAdProductController@showTotal'); //ccp功能展示顶部统计数据
Route::post('/ccp/adProduct/list', 'CcpAdProductController@list');//ccp功能的列表展示
Route::get('/ccp/adProduct/export', 'CcpAdProductController@export');//ccp功能的导出功能

Route::get('/ccp/adKeyword', 'CcpAdKeywordController@index'); //功能入口
Route::post('/ccp/adKeyword/showTotal', 'CcpAdKeywordController@showTotal'); //ccp功能展示顶部统计数据
Route::post('/ccp/adKeyword/list', 'CcpAdKeywordController@list');//ccp功能的列表展示
Route::get('/ccp/adKeyword/export', 'CcpAdKeywordController@export');//ccp功能的导出功能

Route::get('/ccp/adCampaign', 'CcpAdCampaignController@index'); //功能入口
Route::post('/ccp/adCampaign/showTotal', 'CcpAdCampaignController@showTotal'); //ccp功能展示顶部统计数据
Route::post('/ccp/adCampaign/list', 'CcpAdCampaignController@list');//ccp功能的列表展示
Route::get('/ccp/adCampaign/export', 'CcpAdCampaignController@export');//ccp功能的导出功能

Route::get('/ccp/adGroup', 'CcpAdGroupController@index'); //功能入口
Route::post('/ccp/adGroup/showTotal', 'CcpAdGroupController@showTotal'); //ccp功能展示顶部统计数据
Route::post('/ccp/adGroup/list', 'CcpAdGroupController@list');//ccp功能的列表展示
Route::get('/ccp/adGroup/export', 'CcpAdGroupController@export');//ccp功能的导出功能

Route::get('/ccp/adTarget', 'CcpAdTargetController@index'); //功能入口
Route::post('/ccp/adTarget/showTotal', 'CcpAdTargetController@showTotal'); //ccp功能展示顶部统计数据
Route::post('/ccp/adTarget/list', 'CcpAdTargetController@list');//ccp功能的列表展示
Route::get('/ccp/adTarget/export', 'CcpAdTargetController@export');//ccp功能的导出功能
//ccp-ad模块结束

Route::get('/ccp/salesboard', 'CcpSalesboardController@index');
Route::post('/ccp/salesboard/showTotal', 'CcpSalesboardController@showTotal');
Route::post('/ccp/salesboard/list', 'CcpSalesboardController@list');
Route::post('/ccp/salesboard/showAccountBySite', 'CcpSalesboardController@showAccountBySite');

Route::get('/getOrderDataBySap', 'ApiController@getOrderDataBySap')->name('getOrderDataBySap'); //获取sap接口数据

Route::resource('skuforuser', 'SkuForUserController');
Route::Post('/skuforuser/get', 'SkuForUserController@get')->name('getSkuForUser');
Route::Post('/skuforuser/upload', 'SkuForUserController@upload')->name('uploadSkuForUser');
Route::get('/skuforuserexport', 'SkuForUserController@export')->name('exportSkuForUser');
Route::Post('/skuforuser/batchUpdate', 'SkuForUserController@batchUpdate')->name('skuforuserBatchUpdate');
/*
 * EDM模块
 */
Route::get('/edm/tag', 'EdmTagController@index');//标签的列表
Route::post('/edm/tagList', 'EdmTagController@list');//标签的列表
Route::match(['post','get'],'/edm/tag/add', 'EdmTagController@add');//添加标签
Route::match(['post','get'],'/edm/tag/update', 'EdmTagController@update');//更新标签

Route::get('/edm/customers', 'EdmCustomersController@index')->name('edmCustomersIndex');//客户信息的列表
Route::post('/edm/customersList', 'EdmCustomersController@list')->name('edmCustomersList');//客户信息的列表
Route::Post('/edm/customers/import', 'EdmCustomersController@import');//导入数据
Route::get('/edm/customers/download', 'EdmCustomersController@download');//下载模板
Route::post('/edm/customers/action', 'EdmCustomersController@action');//客户列表中的操作
Route::match(['post','get'],'/edm/customers/add', 'EdmCustomersController@add');//添加单个客户数据
Route::match(['post','get'],'/edm/customers/update', 'EdmCustomersController@update');//更新单个客户数据
Route::Post('/edm/customers/pullByMailchimp', 'EdmCustomersController@pullByMailchimp');//从mailchimp拉取数据

Route::get('/edm/template', 'EdmTemplateController@index');//模板的列表
Route::post('/edm/templateList', 'EdmTemplateController@list');//模板的列表
Route::match(['post','get'],'/edm/template/add', 'EdmTemplateController@add');//添加模板
Route::match(['post','get'],'/edm/template/update', 'EdmTemplateController@update');//更新模板

Route::get('/edm/campaign', 'EdmCampaignController@index');//campaign活动的列表
Route::post('/edm/campaignList', 'EdmCampaignController@list');//campaign活动的列表
Route::match(['post','get'],'/edm/campaign/add', 'EdmCampaignController@add');//添加campaign活动
Route::match(['post','get'],'/edm/campaign/update', 'EdmCampaignController@update');//更新campaign活动
Route::post('/edm/getContentByTmpAsin', 'EdmCampaignController@getContentByTmpAsin');//添加campaign活动时,通过模板和asin得到发送content的内容

/*
 * 计划员预测销售情况
 */
Route::match(['post','get'],'/plansforecast/list', 'PlansForecastController@list');
Route::post('/plansforecast/import', 'PlansForecastController@import');
Route::post('/plansforecast/updateStatus', 'PlansForecastController@updateStatus');
Route::get('/plansforecast/export', 'PlansForecastController@export');
Route::post('/plansforecast/weekupdate', 'PlansForecastController@weekupdate');
Route::post('/plansforecast/updateStatus', 'PlansForecastController@updateStatus');
Route::get('/plansforecast/edit', 'PlansForecastController@edit');


/*
 * 协同补货模块
 */
Route::match(['post','get'],'/transfer/request/list', 'TransferRequestController@list');//调货请求列表
Route::match(['post','get'],'/transfer/request/add', 'TransferRequestController@add');//添加调货请求列表
Route::match(['post','get'],'/transfer/request/edit', 'TransferRequestController@edit');//更新/查看调货请求内容
Route::post('/checkAsin', 'Controller@checkAsin');//销售在页面上填写asin，ajax检测是否属于自己的asin
Route::post('/transfer/request/updateStatus', 'TransferRequestController@updateStatus');//审核操作，更新状态
Route::post('/transfer/request/uploadAttach', 'TransferRequestController@uploadAttach');//上传大货资料
Route::get('/transfer/request/downloadAttach', 'TransferRequestController@downloadAttach');//查看大货资料

//调拨计划
Route::resource('transferPlan', 'TransferPlanController');
Route::Post('/transferPlan/get', 'TransferPlanController@get')->name('getTransferPlan');
Route::Post('/transferPlan/batchUpdate', 'TransferPlanController@batchUpdate')->name('transferPlanBatchUpdate');
Route::Post('/transferPlan/getSellerSku', 'TransferPlanController@getSellerSku')->name('getSellerSku');
Route::Post('/transferPlan/update', 'TransferPlanController@update')->name('update');
Route::Post('/transferPlan/getUploadData', 'TransferPlanController@getUploadData')->name('getUploadData');
Route::Post('/transferPlan/updateFiles', 'TransferPlanController@updateFiles')->name('updateFiles');

Route::resource('daPlan', 'DaPlanController');
Route::Post('/daPlan/get', 'DaPlanController@get')->name('getDaPlan');
Route::Post('/daPlan/batchUpdate', 'DaPlanController@batchUpdate')->name('daPlanBatchUpdate');
Route::Post('/daPlan/update', 'DaPlanController@update')->name('update');
Route::Post('/daPlan/upload', 'DaPlanController@upload');

Route::resource('shipPlan', 'ShipPlanController');
Route::Post('/shipPlan/get', 'ShipPlanController@get')->name('getShipPlan');
Route::Post('/shipPlan/batchUpdate', 'ShipPlanController@batchUpdate')->name('shipPlanBatchUpdate');
Route::Post('/shipPlan/update', 'ShipPlanController@update')->name('update');

Route::resource('daSkuMatch', 'DaSkuMatchController');
Route::Post('/daSkuMatch/get', 'DaSkuMatchController@get')->name('getDaSkuMatch');
Route::Post('/daSkuMatch/update', 'DaSkuMatchController@update')->name('updateDaSkuMatch');
Route::Post('/daSkuMatch/upload', 'DaSkuMatchController@upload');

Route::resource('otherSku', 'OtherSkuController');
Route::Post('/otherSku/get', 'OtherSkuController@get')->name('getOtherSku');
Route::Post('/otherSku/update', 'OtherSkuController@update')->name('updateOtherSku');
Route::Post('/otherSku/upload', 'OtherSkuController@upload');

Route::resource('replaceSku', 'ReplaceSkuController');
Route::Post('/replaceSku/get', 'ReplaceSkuController@get')->name('getReplaceSku');
Route::Post('/replaceSku/update', 'ReplaceSkuController@update')->name('updateReplaceSku');

Route::resource('amazonWarehouse', 'AmazonWarehouseController');
Route::Post('/amazonWarehouse/get', 'AmazonWarehouseController@get')->name('getAmazonWarehouse');
Route::Post('/amazonWarehouse/update', 'AmazonWarehouseController@update')->name('updateAmazonWarehouse');
Route::Post('/amazonWarehouse/upload', 'AmazonWarehouseController@upload');

Route::resource('skuSize', 'SkuSizeController');
Route::Post('/skuSize/get', 'SkuSizeController@get')->name('getSkuSize');
Route::Post('/skuSize/update', 'SkuSizeController@update')->name('updateSkuSize');
Route::Post('/skuSize/upload', 'SkuSizeController@upload');



Route::match(['post','get'],'/transfer/plan/createPlan', 'TransferPlanController@createPlan');//计划确认后的挑货请求，生成计划
//调拨任务
Route::resource('transferTask', 'TransferTaskController');
Route::Post('/transferTask/get', 'TransferTaskController@get')->name('getTransferTask');

//系统模块下的菜单路由
Route::get('/system/itRequirement', 'SystemController@itRequirement');//跳转进入到禅道系统
Route::get('/plugin/download', 'SystemController@pluginDownload');//下载插件包
/*
 * 弹窗插件模块
 */
Route::match(['post','get'],'/api/alertRemind', 'ApiController@alertRemind');//弹出验证框

Route::get('/reports', 'ReportsController@index');
Route::any('/reports/get', 'ReportsController@get')->name('getReport');

//订单列表模块
Route::get('/orderList', 'OrderListController@index');//订单列表
Route::post('/orderList/list', 'OrderListController@list');//获取订单列表数据
Route::match(['post','get'],'/orderList/export', 'OrderListController@export');//订单列表数据的下载

//退款列表模块
Route::get('/refund', 'RefundController@index');//退款列表
Route::post('/refund/list', 'RefundController@list');//获取退款列表数据
Route::match(['post','get'],'/refund/export', 'RefundController@export');//退款列表数据的下载

//退货列表模块
Route::get('/return', 'ReturnController@index');//退货列表
Route::post('/return/list', 'ReturnController@list');//获取退货列表数据
Route::match(['post','get'],'/return/export', 'ReturnController@export');//退货列表数据的下载

//重发单模块
Route::get('/McfOrderList', 'McfOrderListController@index');//重发单列表
Route::post('/McfOrderList/list', 'McfOrderListController@list');//获取重发单列表数据
Route::match(['post','get'],'/McfOrderList/export', 'McfOrderListController@export');//重发单列表数据的下载

//Amazon Settlement模块
Route::get('/settlement', 'SettlementController@index');//Settlement列表
Route::post('/settlement/list', 'SettlementController@list');//获取Settlement列表数据
Route::match(['post','get'],'/settlement/export', 'SettlementController@export');//Settlement列表数据的下载
Route::get('/settlement/detail', 'SettlementController@detail');//
Route::post('/settlement/detailList', 'SettlementController@detailList');//获取Settlement列表数据
Route::match(['post','get'],'/settlement/detailExport', 'SettlementController@detailExport');//Settlement列表数据的下载

//统计分析模块
Route::match(['post','get'],'/returnAnalysis/returnAnalysis', 'ReturnAnalysisController@returnAnalysis');//退货原因分析
Route::match(['post','get'],'/returnAnalysis/returnSummaryAnalysis', 'ReturnAnalysisController@returnSummaryAnalysis');//退货原因汇总
Route::match(['post','get'],'/returnAnalysis/export', 'ReturnAnalysisController@export');//导出退货原因分析
Route::match(['post','get'],'/returnAnalysis/asinAnalysis', 'ReturnAnalysisController@asinAnalysis');//asin退货分析，维度：asin+币种+account
Route::match(['post','get'],'/returnAnalysis/skuAnalysis', 'ReturnAnalysisController@skuAnalysis');//sku退货分析，维度：sku+币种

Route::get('/finance','FinanceDashBoardController@index');//财务看板

Route::resource('platformsku', 'Platform\PlatFormSkuController');
Route::Post('/platformsku/get', 'Platform\PlatFormSkuController@get');
Route::Post('/platformsku/batchUpdate', 'Platform\PlatFormSkuController@batchUpdate');
Route::resource('platformship', 'Platform\PlatFormShipController');
Route::Post('/platformship/get', 'Platform\PlatFormShipController@get');
Route::Post('/platformship/batchUpdate', 'Platform\PlatFormShipController@batchUpdate');
Route::resource('platformorder', 'Platform\PlatFormOrderController');
Route::Post('/platformorder/get', 'Platform\PlatFormOrderController@get');
Route::Post('/platformorder/batchUpdate', 'Platform\PlatFormOrderController@batchUpdate');

//ROI绩效模块
Route::get('/roiPerformance', 'RoiPerformanceController@index');//订单列表
Route::post('/roiPerformance/list', 'RoiPerformanceController@list');//获取订单列表数据
Route::match(['post','get'],'/roiPerformance/export', 'RoiPerformanceController@export');//订单列表数据的下载
Route::match(['post','get'],'/roiPerformance/calculate', 'RoiPerformanceController@calculate');//计算绩效结果

//...条码系统
Route::get('/barcode', 'BarcodeController@index');
Route::post('/barcode/getVendorList', 'BarcodeController@getVendorList');
Route::get('/barcode/purchaseOrderList', 'BarcodeController@purchaseOrderList');
Route::post('/barcode/getPurchaseOrderList', 'BarcodeController@getPurchaseOrderList');
Route::get('/barcode/purchaseOrderDetails', 'BarcodeController@purchaseOrderDetails');
Route::post('/barcode/getPurchaseOrderDetails', 'BarcodeController@getPurchaseOrderDetails');
Route::get('/barcode/generateBarcode', 'BarcodeController@generateBarcode');
Route::post('/barcode/saveBarcode', 'BarcodeController@saveBarcode');
Route::post('/barcode/saveNewVendor', 'BarcodeController@saveNewVendor');
Route::get('/barcode/printBarcode', 'BarcodeController@printBarcode');
Route::get('/barcode/addNewVendor', 'BarcodeController@addNewVendor');
Route::match(['post','get'], '/barcode/outputBarcode', 'BarcodeController@outputBarcode');
//Route::match(['post','get'], '/barcode/downloadPDF', 'BarcodeController@downloadPDF');
//Route::post('/barcode/downloadPDF', 'BarcodeController@downloadPDF');
//激活条码
Route::get('/barcode/scanBarcode', 'BarcodeScanController@scanBarcode');
Route::post('/barcode/checkPoSku', 'BarcodeScanController@checkPoSku');
Route::post('/barcode/checkToken', 'BarcodeScanController@checkToken');
Route::match(['post','get'], '/barcode/activateBarcode', 'BarcodeScanController@activateBarcode');
//解绑条码
Route::get('/barcode/detachBarcode', 'BarcodeScanController@detachBarcode');
Route::post('/barcode/verifyToken', 'BarcodeScanController@verifyToken');
Route::match(['post','get'], '/barcode/deactivateBarcode', 'BarcodeScanController@deactivateBarcode');
//add
Route::get('/barcode/qc', 'BarcodeController@qc');
Route::post('/barcode/getVendorTable', 'BarcodeController@getVendorTable');
Route::post('/barcode/modifyOperator', 'BarcodeController@modifyOperator');
Route::get('/barcode/check', 'BarcodeController@check');
Route::get('/barcode/scanDetach', 'BarcodeScanController@scanDetach');
Route::get('/barcode/vendorDetails', 'BarcodeController@vendorDetails');
Route::get('/barcode/businessLogin', 'BarcodeScanController@businessLogin');
Route::get('/barcode/updateToken', 'BarcodeScanController@updateToken');
Route::post('/barcode/generateNewToken', 'BarcodeScanController@generateNewToken');
Route::post('/barcode/verifyPo','BarcodeScanController@verifyPo');
Route::get('/barcode/changeOperator','BarcodeController@changeOperator');
Route::post('/barcode/checkQc', 'BarcodeController@checkQc');
Route::get('/barcode/changeOperator','BarcodeController@changeOperator');
Route::get('/barcode/editVendor','BarcodeController@editVendor');
Route::get('/barcode/test','BarcodeController@test');
Route::get('/barcode/exportBarcodePdf','BarcodeController@exportBarcodePdf');
Route::post('/barcode/verifyQc','BarcodeController@verifyQc');
Route::post('/barcode/modifyVendor','BarcodeController@modifyVendor');
Route::post('/barcode/getVendorOrderDetails', 'BarcodeController@getVendorOrderDetails');
//生成供应商token的条码
Route::get('/barcode/makeToken', 'BarcodeController@makeToken');
Route::post('/barcode/getSkuInfo', 'BarcodeController@getSkuInfo');
Route::post('/barcode/getActivatedCountInADay', 'BarcodeController@getActivatedCountInADay');

//导出
Route::get('/barcodePoListExport', 'BarcodeController@exportPoList');
Route::get('/barcodePoDetailsExport', 'BarcodeController@exportPoDetails');

Route::resource('giftcard', 'GiftCardController');
Route::Post('/giftcard/get', 'GiftCardController@get');
Route::Post('/giftcard/upload', 'GiftCardController@upload');
Route::get('/giftcardexport', 'GiftCardController@export');


Route::any('adv', 'AdvController@index');
Route::Post('/adv/listCampaigns', 'AdvController@listCampaigns');
Route::Post('/adv/campaignBatchUpdate', 'AdvController@campaignBatchUpdate');
Route::Post('/adv/updateCampaign', 'AdvController@updateCampaign');
Route::Post('/adv/copyCampaign', 'AdvController@copyCampaign');
Route::get('/adv/campaign/{profile_id}/{ad_type}/{campaign_id}/{tab}', 'AdvController@editCampaign');
Route::Post('/adv/listAdGroups', 'AdvController@listAdGroups');
Route::get('/adv/adgroup/{profile_id}/{ad_type}/{adgroup_id}/{tab}', 'AdvController@editAdGroup');
Route::Post('/adv/listAds', 'AdvController@listAds');
Route::Post('/adv/listKeywords', 'AdvController@listKeywords');
Route::Post('/adv/listProducts', 'AdvController@listProducts');
Route::Post('/adv/updateAdGroup', 'AdvController@updateAdGroup');
Route::Post('/adv/batchUpdate', 'AdvController@batchUpdate');
Route::Post('/adv/scheduleBatchUpdate', 'AdvController@scheduleBatchUpdate');
Route::Post('/adv/updateBid', 'AdvController@updateBid');
Route::Post('/adv/listNegkeywords', 'AdvController@listNegkeywords');
Route::Post('/adv/storeNegkeywords', 'AdvController@storeNegkeywords');
Route::Post('/adv/listNegproducts', 'AdvController@listNegproducts');
Route::Post('/adv/storeNegproducts', 'AdvController@storeNegproducts');
Route::Post('/adv/listGNegkeywords', 'AdvController@listGNegkeywords');
Route::Post('/adv/storeGNegkeywords', 'AdvController@storeGNegkeywords');
Route::Post('/adv/listGNegproducts', 'AdvController@listGNegproducts');
Route::Post('/adv/storeGNegproducts', 'AdvController@storeGNegproducts');
Route::Post('/adv/createAds', 'AdvController@createAds');
Route::Post('/adv/createCampaign', 'AdvController@createCampaign');
Route::Post('/adv/createAdGroup', 'AdvController@createAdGroup');
Route::Post('/adv/createKeyword', 'AdvController@createKeyword');
Route::Post('/adv/createTarget', 'AdvController@createTarget');
Route::get('/adv/scheduleEdit', 'AdvController@editSchedule');
Route::Post('/adv/saveSchedule', 'AdvController@saveSchedule');
Route::Post('/adv/listSchedules', 'AdvController@listSchedules');
Route::get('/adv/createWhole', 'AdvController@createWhole');
Route::Post('/adv/saveWhole', 'AdvController@saveWhole');
Route::get('/adv/batchScheduled', 'AdvController@batchScheduled');
Route::Post('/adv/batchSaveScheduled', 'AdvController@batchSaveScheduled');

Route::get('/ppcschedule','PpcscheduleController@index');
Route::get('/ppcschedule/scheduleEdit', 'PpcscheduleController@editSchedule');
Route::Post('/ppcschedule/saveSchedule', 'PpcscheduleController@saveSchedule');
Route::Post('/ppcschedule/listSchedules', 'PpcscheduleController@listSchedules');
Route::Post('/ppcschedule/scheduleBatchUpdate', 'PpcscheduleController@scheduleBatchUpdate');
//客诉品线问题细分
Route::Post('/category/import', 'CategoryController@import');


//Amazon fulfilled Shipments模块
Route::get('/amazonFulfiledShipments', 'AmazonFulfiledShipmentsController@index');//
Route::post('/amazonFulfiledShipments/list', 'AmazonFulfiledShipmentsController@list');//
Route::match(['post','get'],'/amazonFulfiledShipments/export', 'AmazonFulfiledShipmentsController@export');//
Route::match(['post','get'],'/amazonFulfiledShipments/download', 'AmazonFulfiledShipmentsController@download');//

Route::Post('/amazon/auth', 'AmazonAuthController@index');


//ebay订单列表模块
Route::get('/ebayOrderList', 'EBayOrderListController@index');//订单列表
Route::post('/ebayOrderList/list', 'EBayOrderListController@list');//获取订单列表数据
Route::match(['post','get'],'/ebayOrderList/export', 'EBayOrderListController@export');//订单列表数据的下载
Route::post('/ebayOrderList/refreshSkuMatchTable', 'EBayOrderListController@refreshSkuMatchTable');
Route::get('/ebayOrderList/addSkuMatch', 'EBayOrderListController@addSkuMatch');
Route::match(['post','get'],'/ebayOrderList/skuMatchList', 'EBayOrderListController@skuMatchList');
Route::get('/ebayOrderList/skuMatchEdit', 'EBayOrderListController@skuMatchEdit');
Route::post('/ebayOrderList/skuMatchUpdate', 'EBayOrderListController@skuMatchUpdate');
Route::get('/ebayOrderList/exportSkuMatchList', 'EBayOrderListController@exportSkuMatchList');

//joybuy订单列表模块
Route::get('/joybuyOrderList', 'JoybuyOrderListController@index');//订单列表
Route::post('/joybuyOrderList/list', 'JoybuyOrderListController@list');//获取订单列表数据
Route::match(['post','get'],'/joybuyOrderList/export', 'JoybuyOrderListController@export');//订单列表数据的下载
Route::post('/joybuyOrderList/refreshSkuMatchTable', 'JoybuyOrderListController@refreshSkuMatchTable');
Route::get('/joybuyOrderList/addSkuMatch', 'JoybuyOrderListController@addSkuMatch');
Route::match(['post','get'],'/joybuyOrderList/skuMatchList', 'JoybuyOrderListController@skuMatchList');
Route::get('/joybuyOrderList/skuMatchEdit', 'JoybuyOrderListController@skuMatchEdit');
Route::post('/joybuyOrderList/skuMatchUpdate', 'JoybuyOrderListController@skuMatchUpdate');
Route::get('/joybuyOrderList/exportSkuMatchList', 'JoybuyOrderListController@exportSkuMatchList');

//newegg订单列表模块
Route::get('/neweggOrderList', 'NeweggOrderListController@index');//订单列表
Route::post('/neweggOrderList/list', 'NeweggOrderListController@list');//获取订单列表数据
Route::match(['post','get'],'/neweggOrderList/export', 'NeweggOrderListController@export');//订单列表数据的下载
Route::post('/neweggOrderList/refreshSkuMatchTable', 'NeweggOrderListController@refreshSkuMatchTable');
Route::get('/neweggOrderList/addSkuMatch', 'NeweggOrderListController@addSkuMatch');
Route::match(['post','get'],'/neweggOrderList/skuMatchList', 'NeweggOrderListController@skuMatchList');
Route::get('/neweggOrderList/skuMatchEdit', 'NeweggOrderListController@skuMatchEdit');
Route::post('/neweggOrderList/skuMatchUpdate', 'NeweggOrderListController@skuMatchUpdate');
Route::get('/neweggOrderList/exportSkuMatchList', 'NeweggOrderListController@exportSkuMatchList');

//letian订单列表模块
Route::get('/letianOrderList', 'LetianOrderListController@index');//订单列表
Route::post('/letianOrderList/list', 'LetianOrderListController@list');//获取订单列表数据
Route::match(['post','get'],'/letianOrderList/export', 'LetianOrderListController@export');//订单列表数据的下载
Route::post('/letianOrderList/refreshSkuMatchTable', 'LetianOrderListController@refreshSkuMatchTable');
Route::get('/letianOrderList/addSkuMatch', 'LetianOrderListController@addSkuMatch');
Route::match(['post','get'],'/letianOrderList/skuMatchList', 'LetianOrderListController@skuMatchList');
Route::get('/letianOrderList/skuMatchEdit', 'LetianOrderListController@skuMatchEdit');
Route::post('/letianOrderList/skuMatchUpdate', 'LetianOrderListController@skuMatchUpdate');
Route::get('/letianOrderList/exportSkuMatchList', 'LetianOrderListController@exportSkuMatchList');


Route::get('/shopsaver', 'ShopSaverController@index');
Route::post('/shopsaver/list', 'ShopSaverController@list');
Route::get('/shopsaver/edit', 'ShopSaverController@edit');
Route::post('/shopsaver/update', 'ShopSaverController@update');
Route::match(['post','get'],'/shopsaver/users', 'ShopSaverController@users');
Route::match(['post','get'],'/shopsaver/orderList', 'ShopSaverController@orderList');
Route::get('/shopsaver/userEdit', 'ShopSaverController@userEdit');
Route::post('/shopsaver/userUpdate', 'ShopSaverController@userUpdate');

//库存盘点
Route::get('/inventoryCycleCount', 'InventoryCycleCountController@index');//展示index页面
Route::post('/inventoryCycleCount/list', 'InventoryCycleCountController@list');//获取列表数据
Route::get('/inventoryCycleCount/downloadSku', 'InventoryCycleCountController@downloadSku');//下载企管部需要的添加sku模板
Route::post('/inventoryCycleCount/importSku', 'InventoryCycleCountController@importSku');//导入sku数据
Route::get('/inventoryCycleCount/downloadAccountNumber', 'InventoryCycleCountController@downloadAccountNumber');//下载财务部需要的添加账面数量模板
Route::post('/inventoryCycleCount/importAccountNumber', 'InventoryCycleCountController@importAccountNumber');//财务部导入账面数量数据
Route::get('/inventoryCycleCount/downloadDisposeAfterAccountNumber', 'InventoryCycleCountController@downloadDisposeAfterAccountNumber');//下载财务部需要添加的处理后账面数量模板
Route::post('/inventoryCycleCount/importDisposeAfterAccountNumber', 'InventoryCycleCountController@importDisposeAfterAccountNumber');//财务部导入处理后的账面数量数据
Route::get('/inventoryCycleCount/downloadActualNumber', 'InventoryCycleCountController@downloadActualNumber');//下载物流部需要的添加真实数量模板
Route::post('/inventoryCycleCount/importActualNumber', 'InventoryCycleCountController@importActualNumber');//导入真实数量数据
//Route::get('/inventoryCycleCount/downloadReason', 'InventoryCycleCountController@downloadReason');//下载导入差异原因的模板
//Route::post('/inventoryCycleCount/importReason', 'InventoryCycleCountController@importReason');//导入差异原因数据
Route::get('/inventoryCycleCount/show', 'InventoryCycleCountController@show');//导入差异原因数据
Route::post('/inventoryCycleCount/edit', 'InventoryCycleCountController@edit');//编辑操作
Route::post('/inventoryCycleCount/deleteReason', 'InventoryCycleCountController@deleteReason');//删除原因操作
Route::post('/inventoryCycleCount/addReason', 'InventoryCycleCountController@addReason');//添加原因操作
Route::post('/inventoryCycleCount/editReason', 'InventoryCycleCountController@editReason');//编辑原因操作
Route::get('/inventoryCycleCount/export', 'InventoryCycleCountController@export');//导出操作

/*
 * 物料对照关系维护
 */
Route::get('/asinMatchRelation', 'AsinMatchRelationController@index');//列表
Route::post('/asinMatchRelationList', 'AsinMatchRelationController@list');//列表获取数据
Route::match(['post','get'],'/asinMatchRelation/add', 'AsinMatchRelationController@add');//添加
Route::match(['post','get'],'/asinMatchRelation/update', 'AsinMatchRelationController@update');//更新
Route::match(['post','get'],'/asinMatchRelation/delete', 'AsinMatchRelationController@delete');//更新

/*
 * 账号状态设置
 */
Route::get('/sellerAccountsStatus', 'SellerAccountsStatusController@index');//列表
Route::post('/sellerAccountsStatusList', 'SellerAccountsStatusController@list');//列表获取数据
Route::match(['post','get'],'/sellerAccountsStatus/add', 'SellerAccountsStatusController@add');//添加
Route::match(['post','get'],'/sellerAccountsStatus/view', 'SellerAccountsStatusController@view');//查看明细

Route::Post('/cuckoo/gather', 'CuckooController@index');
Route::get('/cuckoo/feedback', 'CuckooController@feedback');

Route::get('/accountStocklist', 'AccountStocklistController@index');//列表
//Route::post('/sellerAccountsStatusList', 'SellerAccountsStatusController@list');//列表获取数据

//cuckoo相关模块
Route::get('/cuckoo/show', 'CuckooController@show');
Route::post('/cuckoo/showList', 'CuckooController@showList');

Route::get('/cuckoo/deals', 'CuckooController@deals');
Route::post('/cuckoo/dealsList', 'CuckooController@dealsList');
Route::get('/cuckoo/view', 'CuckooController@view');
Route::get('/cuckoo/coupons', 'CuckooController@coupons');
Route::post('/cuckoo/couponsList', 'CuckooController@couponsList');
Route::get('/cuckoo/promotions', 'CuckooController@promotions');
Route::post('/cuckoo/promotionsList', 'CuckooController@promotionsList');

Route::resource('budgetSku', 'BudgetskuController');
Route::Post('/budgetSku/get', 'BudgetskuController@get');
Route::Post('/budgetSku/upload', 'BudgetskuController@upload');
Route::get('/budgetSkuExport', 'BudgetskuController@export');

//广告汇总相关模块
Route::get('/ccp/adMatchAsin', 'CcpAdMatchAsinController@index'); //广告映射关系
Route::post('/ccp/adMatchAsin/list', 'CcpAdMatchAsinController@list');//广告映射关系的列表展示
Route::get('/ccp/adMatchAsin/export', 'CcpAdMatchAsinController@export');//广告映射关系的导出
Route::post('/getCampaignBySiteAccount', 'Controller@getCampaignBySiteAccount')->name('getCampaignBySiteAccount');//通过选择的站点账号得到Campaign
Route::post('/getGroupBySiteCampaign', 'Controller@getGroupBySiteCampaign')->name('getGroupBySiteCampaign');//通过选择的campaign得到组别
Route::post('/getDataBySiteCampaign', 'Controller@getDataBySiteCampaign')->name('getDataBySiteCampaign');//通过选择的campaign得到该campaign的type
Route::match(['post','get'],'/ccp/adMatchAsin/add', 'CcpAdMatchAsinController@add'); //广告映射关系
Route::post('/ccp/asinMatchSkuDataByAsin', 'Controller@asinMatchSkuDataByAsin');//通过asin得到匹配的asin相关数据
Route::post('/ccp/adMatchAsin/delete', 'CcpAdMatchAsinController@delete');//删除数据
//相关统计数据
Route::get('/ccp/adTotalBg', 'CcpAdTotalController@adTotalBgIndex'); //广告汇总BG维度
Route::post('/ccp/adTotalBg/list', 'CcpAdTotalController@adTotalBgList'); //广告汇总BG维度
Route::get('/ccp/adTotalBg/export', 'CcpAdTotalController@adTotalBgExport'); //广告汇总BG维度导出
Route::get('/ccp/adTotalBu', 'CcpAdTotalController@adTotalBuIndex'); //广告汇总BU维度
Route::post('/ccp/adTotalBu/list', 'CcpAdTotalController@adTotalBuList'); //广告汇总BU维度
Route::get('/ccp/adTotalBu/export', 'CcpAdTotalController@adTotalBuExport'); //广告汇总BU维度导出
Route::get('/ccp/adTotalSeller', 'CcpAdTotalController@adTotalSellerIndex'); //广告汇总销售员维度
Route::post('/ccp/adTotalSeller/list', 'CcpAdTotalController@adTotalSellerList'); //广告汇总销售员维度
Route::get('/ccp/adTotalSeller/export', 'CcpAdTotalController@adTotalSellerExport'); //广告汇总销售员维度导出

Route::post('/interfaceAddException', 'Controller@interfaceAddException');//客服系统添加异常单同步到VOP系统的接口
Route::any('/exceptionResendApi', 'ExceptionController@resendApi');
Route::any('/exceptionTaskApi', 'TaskController@taskApi');

Route::get('/exceptionReminder', 'ExceptionController@remind');
Route::Post('/exceptionReminder/get', 'ExceptionController@getRemind');

Route::resource('reims', 'ReimController');
Route::Post('/reims/get', 'ReimController@get')->name('getReim');
Route::Post('/reims/batchUpdate', 'ReimController@batchUpdate')->name('ReimBatchUpdate');


//cs-crm模块
Route::get('/cscrm', 'CscrmController@index');
Route::match(['post','get'],'/cscrm/get', 'CscrmController@get')->name('getCscrm');
Route::match(['post','get'],'/cscrm/export', 'CscrmController@export')->name('exportCscrm');
