@extends('layouts.layout')
@section('crumb')
    <a href="/cpfr/index">CPFR协同补货</a>
@endsection
@section('content')
<style>
	.nav_list{
		overflow: hidden;
		height: 45px;
		line-height: 45px;
		border-bottom: 2px solid #fff;
		padding: 0;
		margin: 0;
	}
	.nav_list li{
		float: left;
		line-height: 36px;
		padding: 5px 10px 0 10px;
		margin: 0 10px 0 0;
		list-style: none;
	}
	.nav_list li a{
		text-decoration: none;
		color: #666;
	}
	.nav_active{
		border-bottom: 2px solid #4B8DF8;
	}
	.nav_active a{
		color: #4B8DF8 !important;
	}
	.content{
		padding: 10px 40px 20px 40px;
		overflow: hidden;
		border-radius: 4px !important;
		background-color: rgba(255, 255, 255, 1);
	}
	.button_box{
		text-align: right;
		padding: 20px 0;
	}
	.button_box > button:first-child{
		width: 130px !important;
	}
	.button_box > button{
		width: 105px;
		border-radius: 4px !important;
	}
	.filter_box{
		overflow: hidden;
	}
	.filter_box select{
		border-radius: 4px !important;
		width: 240px;
		height: 36px;
		color: #666;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	.filter_option{
		float: left;
		margin-right: 20px;
		padding-top: 10px;
	}
	.filter_option > label{
		display: block;
		color: rgba(48, 49, 51, 1);
		font-size: 14px;
		text-align: left;
		font-family: PingFangSC-Semibold;
	}
	.filter_option .btn{
		padding: 7px 0 7px 12px !important;
	}
	.date-range-toggle.default:not(.btn-outline) {
	    background-color: #fff;
	    height: 34px;
	    border-right: none;
		width: 30px;
		line-height: 18px;
	}
	.mask_default_btn.default:not(.btn-outline) {
	    background-color: #fff;
	    height: 28px;
	    border-left: none;
		width: 30px;
		line-height: 18px;
	}
	.input-group .form-control{
		border: 1px solid rgba(220, 223, 230, 1);
		background: #fff;
	}
	.keyword{
		outline: none;
		padding-left: 10px;
	}
	.search_box input{
		width: 280px;
		height: 36px;
		border-top-left-radius: 4px !important;
		border-bottom-left-radius: 4px !important;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	.search{
		width: 90px;
		height: 36px;
		background-color: rgba(99, 197, 209, 1);
		border: 1px solid rgba(99, 197, 209, 1);
		margin-left: -5px;
		border-top-right-radius: 4px !important;
		border-bottom-right-radius: 4px !important;
		color: #fff;
		outline: none;
	}
	.search svg{
		display: inline-block;
		margin-bottom: -4px;
	}
	.clear{
		width: 90px;
		height: 36px;
		background-color: #909399;
		border: 1px solid #909399;
		border-radius: 4px !important;
		outline: none;
		color: #fff;
		margin-left: 20px;
	}
	.batch_list{
		border: 1px solid rgba(220, 223, 230, 1);
		width: 180px;
		margin-left: -40px !important;
		padding: 15px 0 !important;
		display: none;
	}
	.batch_list,.batch_list li{
		background: #fff;
		padding: 0;
		margin: 0;
		list-style: none;
	}
	.batch_list li{
		text-align: center;
	}
	.batch_list li button{
		color: #FFFFFF;
		border: none;
		width: 95px;
		margin: 5px 0;
	}
	.batch_list:after{
		position: absolute;
		top: 24px;
		left: 50px;
		right: auto;
		display: inline-block !important;
		border-right: 7px solid transparent;
		border-bottom: 7px solid #fff;
		border-left: 7px solid transparent;
		content: '';
		box-sizing: border-box;
	}
	#thetable_filter{
		display: none;
	}
	/* 弹出框样式 */
	.mask_box{
		display: block;
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background: rgb(0,0,0,.3);
		z-index: 999;
	}
	.mask-dialog{
		width: 600px;
		height: 720px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px 60px;
		margin-top: -360px;
		margin-left: -300px;
	}
	
	.form_btn{
		text-align: right;
		margin: 5px 0;
	}
	.form_btn button{
		padding: 8px 24px;
		outline: none;
		color: #fff;
		border-radius: 4px !important;
	}
	.form_btn button:first-child{
		background-color: #909399;
		border: 1px solid #909399;	
	}
	.form_btn button:last-child{
		margin-left: 10px;
		background-color: dodgerblue;
		border: 1px solid dodgerblue;
	}
	.mask-form > div > label > input{
		margin-right: 6px;
	}
	.cancel_mask{
		position: absolute;
		top: 20px;
		right: 20px;
		cursor: pointer;
		width: 30px;
		padding: 8px;
		height: 30px;
	}
	.err {
	    color: #f00;
	    position: absolute;
	    top: 52px;
	    left: 0;
		font-size: 12px;
		font-weight: normal;
		display: none;
	}
	.wrap_one_single {
		height: auto;
	    margin-bottom: 20px;
	    align-items: center;
	    position: relative;
		height: 50px;
		
	}
	.must::before {
	    content: "*";
	    display: inline-block;
	    margin-right: 4px;
	    line-height: 1;
	    font-family: SimSun;
	    font-size: 18px;
	    color: #ed3f14;
	}
	.radio_form{
		height: 20px;
		margin-bottom: 15px;
	}
	.warp_box{
		overflow: hidden;
	}
	.warp_box > label:first-child,.radio_form > label:first-child{
		width: 45%;
		float: left;
	}
	.warp_box > label:last-child,.radio_form > label:last-child{
		width: 45%;
		float: right;
	}
	.warp_box > label > input,.warp_box > label > select{
		width: 100%;
		height: 28px;
		margin-bottom: 10px;
		border: 1px solid rgba(220, 223, 230, 1)
	}
	.control-label{
		height: 52px;
		overflow: hidden;
		margin-bottom: 20px;
	}
	.control-label > select,.control-label > input{
		height: 28px;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	#createTimeInput{
		border-left: none;
	}
</style>
<link rel="stylesheet" type="text/css" media="all" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<div>
	<ul class="nav_list">
		<li><a href="/cpfr/index">调拨计划</a></li>
		<li class="nav_active"><a href="/cpfr/purchase">采购计划</a></li>
		<li><a href="/cpfr/allocationProgress">调拨进度</a></li>
	</ul>
	<div class="button_box">
		<button id="sample_editable_1_2_new" class="btn sbold red"> 新建采购计划
			<i class="fa fa-plus"></i>
		</button>
		<button id="export" class="btn sbold blue"> Export
			<i class="fa fa-download"></i>
		</button>
	</div>
	<div class="content">
		<div class="filter_box">
			<div class="filter_option">
				<label for="">日期</label>
				<div class="input-group input-medium" id="createTimes">
					<span class="input-group-btn">
						<button class="btn default date-range-toggle" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
					<input type="text" class="form-control createTimeInput" id="createTimeInput">  
				</div>
			</div>	
			<div class="filter_option">
				<label for="status_select">状态</label>
				<select id="status_select">
					<option value ="">全部</option>
					<option value ="">申请通过</option>
					<option value ="">申请拒绝</option>
				</select>
			</div>
			
		</div>
		<div class="filter_box">
			<div class="filter_option">
				<label for="marketplace_select">站点</label>
				<select id="marketplace_select" onchange="status_filter(this.value,2)">
					<option value ="">全部</option>
					<option value ="US">US</option>
					<option value ="CA">CA</option>
					<option value ="MX">MX</option>
					<option value ="UK">UK</option>
					<option value ="FR">FR</option>
					<option value ="DE">DE</option>
					<option value ="IT">IT</option>
					<option value ="ES">ES</option>
					<option value ="JP">JP</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="bg_select">BG</label>
				<select id="bg_select" onchange="status_filter(this.value,0)">
					<option value ="">全部</option>
					<option value ="BG">BG</option>
					<option value ="BG1">BG1</option>
					<option value ="BG1">BG2</option>
					<option value ="BG2">BG3</option>
					<option value ="BG3">BG4</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="bu_select">BU</label>
				<select id="bu_select" onchange="status_filter(this.value,1)">
					<option value ="">全部</option>
					<option value ="BU">BU</option>
					<option value ="BU1">BU1</option>
					<option value ="BU2">BU2</option>
					<option value ="BU3">BU3</option>
					<option value ="BU4">BU4</option>
					<option value ="BU5">BU5</option>
				</select>
			</div>
			
			<div class="filter_option">
				<label for="seller_select">Seller</label>
				<select id="seller_select" onchange="status_filter(this.value,6)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option search_box">
				<label for="">搜索</label>
				<input type="text" class="keyword" placeholder="Search by ASIN, SKU, or keywords">
				<button class="search">
					<svg t="1588043111114" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3742" width="18" height="18"><path d="M400.696889 801.393778A400.668444 400.668444 0 1 1 400.696889 0a400.668444 400.668444 0 0 1 0 801.393778z m0-89.031111a311.637333 311.637333 0 1 0 0-623.331556 311.637333 311.637333 0 0 0 0 623.331556z" fill="#ffffff" p-id="3743"></path><path d="M667.904 601.998222l314.766222 314.823111-62.919111 62.976-314.823111-314.823111z" fill="#ffffff" p-id="3744"></path></svg>
					搜索
				</button>	
				<button class="clear">清空筛选</button>
			</div>
		</div>
	</div>
	<div class="portlet light bordered">
	    <div style="margin-bottom: 15px"></div>
	    <div class="portlet-body">
	        <div class="table-container" style="position: relative;">
				<div style="position: absolute;left: 130px; z-index: 999;top:0" class="col-md-2">
					<button type="button" class="btn btn-sm green-meadow batch_operation">批量操作<i class="fa fa-angle-down"></i></button>
					<ul class="batch_list">
						<li><button class="btn btn-sm red-sunglo noConfirmed">审核中</button></li>
						<li><button class="btn btn-sm yellow-crusta">生产中</button></li>
						<li><button class="btn btn-sm purple-plum">分拣中</button></li>
						<li><button class="btn btn-sm blue-hoki">已出库</button></li>
						<li><button class="btn btn-sm blue-madison">已签收</button></li>
						<li><button class="btn btn-sm green-meadow">已完成</button></li>
					</ul>
				</div>
				<div class="col-md-6"  style="position: absolute;left: 520px; z-index: 999;top:0">
					<button type="button" class="btn btn-sm red-sunglo">审核中 : 22</button>
					<button type="button" class="btn btn-sm yellow-crusta">生产中 : 22</button>
					<button type="button" class="btn btn-sm purple-plum">分拣中 : 2</button>
					<button type="button" class="btn btn-sm blue-hoki">已出库 : 11</button>
					<button type="button" class="btn btn-sm blue-madison">已签收 : 2</button>
					<button type="button" class="btn btn-sm green-meadow">已完成 : 2</button>
				</div>
	            <table class="table table-striped table-bordered" id="thetable" style="width:100%">
	                <thead>
	                <tr>
						<th>BG</th>
						<th>BU</th>
						<th>station</th>
	                    <th><input type="checkbox" id="selectAll" /></th>
	                    <th>提交日期</th>
	                    <th>销售员</th>
	                    <th>产品图片</th>
	                    <th>ASIN</th>
	                    <th style="text-align: center;">SKU</th>
						<th>需求数量</th>
						<th>期望到货时间</th>
	                    <th>海外库存</th>
	                    <th>未交订单</th>
	                    <th>加权日均</th>
	                    <th>到货后预计日销<div style="text-align: center;">(PCS)</div></th>
	                    <th>异常率</th>
	                    <th>利润率</th>
	                    <th>审核结果</th>
	                    <th>计划员</th>
	                    <th>MOQ</th>
	                    <th>收货工厂</th>
						<th>运输方式</th>
						<th>计划确认数量</th>
						<th>预计交货时间</th>
						<th>采购订单号</th>
						<th>完成进度</th>
	                </tr>
	                </thead>
	                <tbody></tbody>
	            </table>
	        </div>
	    </div>
	</div>
</div>	
<div class="mask_box">
	<div class="mask-dialog">
		<svg t="1588919283810" class="icon cancel_mask cancel_btn" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
		<h4 style="text-align: center; line-height: 26px;">采购计划</h4>
		<div class="control-label">
			<label for="" class="" style="display: block;">审核</label>
			<select name="" id="" class="col-sm-12"></select>
		</div>
		<form action="" method="get" class="radio_form wrap_one_single">
			<label><input name="Fruit" type="radio" value="" checked="checked" />调拨需求</label> 
			<label><input name="Fruit" type="radio" value="" />采购需求</label>	
		</form>
		<div class="warp_box">
			<label for="" class="must wrap_one_single">SKU<select name="" id=""></select><b class="err plan_status_err">请选择SKU</b></label>
			<label for="" class="must wrap_one_single">ASIN<select name="" id=""></select><b class="err plan_status_err">请选择ASIN</b></label>
		</div>
		<div class="warp_box">
			<label for="" class="must wrap_one_single">SellerSKU<select name="" id=""></select><b class="err plan_status_err">请选择SellerSKU</b></label>
			<label for="" class="must wrap_one_single">调入工厂<select name="" id=""></select><b class="err plan_status_err">请选择调入工厂</b></label>
		</div>
		<div class="warp_box">
			<label class="must wrap_one_single" for="">数量<input type="text"><b class="err plan_status_err">请输入数量</b></label>
			<label class="must wrap_one_single" for="">
				到货时间
				<div class="input-group date date-picker margin-bottom-5 bw9" id="maskDate">
					<input type="text" class="form-control form-filter input-sm maskDate" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
					<span class="input-group-btn">
						<button class="btn btn-sm default default_btn mask_default_btn" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
				</div>
				<b class="err plan_status_err">请选择到货时间</b>
			</label>
		</div>
		<!-- <div class="warp_box">
			<label for="">RMS<select name="" id=""></select></label>
			<label for="">RMS<select name="" id=""></select></label>
		</div> -->
		<div class="control-label" style="margin-bottom: 30px;">
			<label for="" class="" style="display: block;">备注</label>
			<input type="text" class="col-sm-12">
		</div>
		<div class="warp_box" style="border-top: 1px dashed #ccc; padding-top: 25px;">
			<label for="" class="wrap_one_single">调出工厂<select name="" id=""></select></label>
			<label for="" class="wrap_one_single">运输方式<select name="" id=""></select></label>
		</div>
		<div class="warp_box">
			<label for="" class="wrap_one_single">计划确认数量<input type="text"></label>
			<label for="" class="wrap_one_single">
				预计交货时间
				<div class="input-group date date-picker margin-bottom-5 bw9" id="maskDate">
					<input type="text" class="form-control form-filter input-sm maskDate" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
					<span class="input-group-btn">
						<button class="btn btn-sm default default_btn mask_default_btn" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
				</div>
			</label>
		</div>
		<div class="form_btn">
			<button class="cancel_btn">取消</button>
			<input type="hidden" class="formId">
			<button class="confirm">确认</button>
		</div>
		
	</div>
</div>

<script>
	$(document).ready(function(){
		//时间选择器
		$('.date-picker').datepicker({
			format: 'yyyy-mm-dd',
		    autoclose: true,
			//datesDisabled : new Date(),
			startDate: '0',
		});
		$('.cancel_btn').on('click',function(){
			$('.mask_box').hide();
		})
		$('.batch_operation').click(function(e){
			$('.batch_list').slideToggle();
			$(document).one('click',function(){
				$('.batch_list').hide();
			})
			e.stopPropagation();
		})
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		theTable = $("#thetable").dataTable({
			serverSide: false,
			processing: true,
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			pageLength: 20,
			dispalyLength: 2, // default record count per page
			order: [ 1, "desc" ],
			//scrollX: "100%",
			//scrollCollapse: false,
			/* fixedColumns: { //固定列的配置项
				leftColumns: 1, //固定左边第一列
			}, */
			columns: [
				{data: 'BG', name: 'BG', visible: false,},
				{data: 'BU', name: 'BU', visible: false,},
				{data: 'station', name: 'station', visible: false,},
				{
					data: "id",
					name: 'id',
					render: function(data, type, row, meta) {
						var content = '<input type="checkbox" name="checkedInput"  class="checkbox-item" value="' + data + '" />';
						return content;
					},
				},
				{
					data: 'date', 
					name: 'date',
					render: function(data, type, row, meta) {
						var content = '<div class="data_bg">'+data+'</div>';
						return content;
					},
				},
				{data: 'seller', name: 'seller', },
				{data: 'product_image', name: 'product_image',},
				{data: 'asin', name: 'asin',},
				{data: 'sku', name: 'sku',},
				{
					data: 'demand_quantity',
					name: 'demand_quantity', 
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id); 
						});
					}
				},
				{data: 'arrival_time', name: 'arrival_time',},
				{data: 'overseas_stock', name: 'overseas_stock',},
				{data: 'unpaid_order', name: 'unpaid_order',},
				{data: 'average_per_day', name: 'average_per_day',},
				{data: 'pcs', name: 'pcs',},
				{data: 'abnormal_rate',name: 'abnormal_rate',},
				{data: 'profit_margin', name: 'profit_margin',},
				{data: 'findings_of_audit', name: 'findings_of_audit'},
				{data: 'planner', name: 'planner'},
				{data: 'moq', name: 'moq'},
				{data: 'receiving_factory', name: 'receiving_factory',},
				{data: 'transport_mode', name: 'transport_mode',},
				{data: 'confirm_quantity', name: 'confirm_quantity',},
				{data: 'delivery_time', name: 'delivery_time',},
				{data: 'purchase_order', name: 'purchase_order',},
				{data: 'progress', name: 'progress',},
			],
			data:[
				{
					bg: 'bg2',
					bu: 'bu1',
					station: 'UK',
					date: '2020-04-12',
					seller: '小李',
					images: "images",
					asin: 'asin',
					sku: 'APC000451',
					demand_quantity: 520,
					arrival_time: '2020-05-19',
					overseas_stock: 1000,
					unpaid_order: 'A210005',
					average_per_day: 44,
					pcs: 555,
					abnormal_rate: "21%",
					profit_margin: "50%",
					findings_of_audit: '计划部确认',
					planner: '张三',
					id: 1,
					moq: 'moq',
					receiving_factory: '深仓',
					transport_mode: '海运',
					confirm_quantity: 54,
					delivery_time: '2020-05-20',
					purchase_order: 'A45001',
					progress: '未交深仓'
				},
				{
					bg: 'bg2',
					bu: 'bu1',
					station: 'UK',
					date: '2020-04-14',
					seller: '小李',
					images: "images",
					asin: 'asin',
					sku: 'APC000451',
					demand_quantity: 520,
					arrival_time: '2020-05-19',
					overseas_stock: 1000,
					unpaid_order: 'A210005',
					average_per_day: 44,
					pcs: 555,
					abnormal_rate: "21%",
					profit_margin: "50%",
					findings_of_audit: '计划部确认',
					planner: '张三',
					id: 1,
					moq: 'moq',
					receiving_factory: '深仓',
					transport_mode: '海运',
					confirm_quantity: 54,
					delivery_time: '2020-05-20',
					purchase_order: 'A45001',
					progress: '未交深仓'
				},
				{
					bg: 'bg2',
					bu: 'bu3',
					station: 'US',
					date: '2020-04-15',
					seller: '小李',
					images: "images",
					asin: 'asin',
					sku: 'APC000451',
					demand_quantity: 520,
					arrival_time: '2020-05-19',
					overseas_stock: 1000,
					unpaid_order: 'A210005',
					average_per_day: 44,
					pcs: 555,
					abnormal_rate: "21%",
					profit_margin: "50%",
					findings_of_audit: '计划部确认',
					planner: '张三',
					id: 1,
					moq: 'moq',
					receiving_factory: '深仓',
					transport_mode: '海运',
					confirm_quantity: 54,
					delivery_time: '2020-05-20',
					purchase_order: 'A45001',
					progress: '未交深仓'
				},
			],
			ajax: {
				type: 'POST',
				url: '',
				dataSrc(json) {
					console.log(json)
				}
			},
			columnDefs: [
				{ "bSortable": false, "aTargets": [ 0,1,2,3,5,6,7,8,9,10,11,12,13,14,15]},
				
			],
			
		})
		//日期初始化
		$("#createTimes").daterangepicker({
			opens: "left", //打开的方向，可选值有'left'/'right'/'center'
			format: "YYYY-MM-DD",
			autoUpdateInput: false,
			separator: " to ",
			startDate: moment(),
			endDate: moment(),
			opens: 'center',
			ranges: {
				"今天": [moment(), moment()],
				"昨天": [moment().subtract("days", 1), moment().subtract("days", 1)],
				"7天前": [moment().subtract("days", 6), moment()],
				"30天前": [moment().subtract("days", 29), moment()],
				"这个月": [moment().startOf("month"), moment().endOf("month")],
				"上个月": [moment().subtract("month", 1).startOf("month"), moment().subtract("month", 1).endOf("month")]
			},
			locale: {
				applyLabel: '确定',
				cancelLabel: '取消',
				fromLabel: '起始时间',
				toLabel: '结束时间',
				customRangeLabel: '自定义',
				daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
				monthNames: ['一月', '二月', '三月', '四月', '五月', '六月','七月', '八月', '九月', '十月', '十一月', '十二月'],
				firstDay: 1,
		
			},
			onChangeDateTime:function(dp,$input){
				console.log(1)
			}
			/* minDate: "01/01/2012",
			maxDate: "12/31/2018" */
		}, function (t, e) {
			$("#seller_select").empty();
			$("#seller_select").append("<option value=''>全部</option>");
			$("#createTimes input").val(t.format("YYYY-MM-DD") + " - " + e.format("YYYY-MM-DD"));	
			let reqList = {
				"created_at_s": cusstr($('#createTimes input').val() , ' - ' , 1),
				"created_at_e": cusstr1($('#createTimes input').val() , ' - ' , 1),
			};
			let val = ''
			//tableObj.ajax.reload();
			//handleClear();
			//status_filter(val,0);
			//status_filter(val,1);
			//status_filter(val,2);
			//status_filter(val,3);
			//status_filter(val,7);
			//status_filter(val,8);
		})
	})
</script>
@endsection