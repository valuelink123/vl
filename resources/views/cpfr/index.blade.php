@extends('layouts.layout')
@section('label', 'CPFR协同补货')
@section('content')
<style>
	.daterangepicker .calendar-table table{
		display: grid;
	}
	.daterangepicker .calendar-table td, .daterangepicker .calendar-table th{
		background: #fff;
	}
	.daterangepicker td{
		float: left;
		line-height: 15px !important;
	}
	.button_box{
		text-align: right;
		padding: 20px 0;
	}
	.button_box > button{
		border-radius: 4px !important;
	}
	
	.content{
		padding: 30px 40px 40px 40px;
		overflow: hidden;
		border-radius: 4px !important;
		background-color: rgba(255, 255, 255, 1);
	}
	.filter_box{
		overflow: hidden;
		padding-bottom: 5px;
		width: 1280px;
	}
	.filter_box select{
		border-radius: 4px !important;
		width: 150px;
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
	.btn.default:not(.btn-outline) {
	    background-color: #fff;
	    height: 34px;
	    border-right: none;
		width: 30px;
		line-height: 18px;
	}
	.input-group .form-control{
		border-left: none !important;
		border: 1px solid rgba(220, 223, 230, 1);
		background: #fff;
	}
	.keyword{
		outline: none;
		padding-left: 10px;
	}
	.search_box input{
		width: 250px;
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
	table.table-bordered.dataTable th, table.table-bordered.dataTable td{
		text-align: center;
	}
	#planTable_filter{
		display: none;
	}
	.table-scrollable{
		margin: 0 0 10px 0 !important;
	}
	.batch_operation i{
		padding-left: 8px;
	}
	.btn.green-meadow:not(.btn-outline){
		/* height: 30px;
		line-height: 20px; */
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
	.mask_box,.mask_upload_box{
		display: none;
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background: rgb(0,0,0,.3);
		z-index: 999;
	}
	.mask_upload_dialog{
		width: 500px;
		height: 200px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px;
		margin-top: -150px;
		margin-left: -150px;
	}
	.mask-dialog{
		width: 600px;
		height: 740px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px 60px;
		margin-top: -370px;
		margin-left: -300px;
	}
	.mask-dialog input{
		padding-left: 8px;
	}
	.mask-form{
		overflow: hidden;
	}
	.mask-form > div:first-child{
		float: left;
	}
	.mask-form > div:last-child{
		float: right;
	}
	.mask-form > div{
		width: 45%;
	}
	.mask-form > div > input,.mask-form > div > select{
		width: 100%;
		height: 28px;
		margin-bottom: 18px;
		border: 1px solid rgba(220, 223, 230, 1)
	}
	.mask-form > div > label{
		display: block;
		text-align: left;
	}
	.form_btn{
		text-align: right;
		margin: 20px 0;
	}
	.form_btn button{
		width: 75px;
		height: 32px;
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
		background-color: #3598dc;
		border: 1px solid #3598dc;
	}
	.mask-form > div > label > input{
		margin-right: 6px;
	}
	.cancel_mask,.cancel_upload_btn{
		position: absolute;
		top: 20px;
		right: 20px;
		cursor: pointer;
		width: 30px;
		padding: 8px;
		height: 30px;
		z-index: 999;
	}
	.cancel_upload_btn{
		top: 10px!important;
		right: 12px !important;
	}
	.default_btn:not(.btn-outline){
		height: 28px !important;
	}
	#maskDate,#arrivalMaskDate{
		border-left:1px solid rgba(220, 223, 230, 1);
		border-right:1px solid rgba(220, 223, 230, 1);
		margin-bottom: 10px;
	}
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
	.success_mask{
		width: 400px;
		height: 50px;
		border-radius: 10px !important;
		position: fixed;
		left: 50%;
		margin-left: -200px;
		top: 250px;
		margin-top: -70px;
		background: #f0f9eb;
		border: 1px solid #e1f3d8;
		display: none;
		z-index:9999;
	}
	.mask_icon{
		float: left;
		margin: 11px 15px;
	}
	.mask_text{
		float: left;
		line-height: 45px;
		color: #67c23a;
	}
	
	.error_mask{
		width: 400px;
		height: 50px;
		border-radius: 10px !important;
		position: fixed;
		left: 50%;
		margin-left: -200px;
		top: 250px;
		margin-top: -70px;
		background: #fef0f0;
		border: 1px solid #fde2e2;
		display: none;
		z-index:9999;
	}
	.error_mask .mask_text{
		color: #f56c6c !important;
	}
	.wrap_one_single{
		position: relative;
		height: 70px;
	}
	.wrap_one_single:before{
		content: '*';
		color: red;
		position: absolute;
		left: 0;
		top: 0px;
	}
	.wrap_one_single > label{
		display: block;
		padding-left: 10px;
	}
	.wrap_one_single > select,.wrap_one_single > input{
		width: 100%;
		height: 28px;
		margin-bottom: 10px;
		border: 1px solid rgba(220, 223, 230, 1)
	}
	.errCode{
		position: absolute;
		color: red;
		left: 2px;
		font-size: 8px;
		bottom: -1px;
		display: none;
	}
	.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th{
		text-align: center;
		vertical-align:middle;
		padding: 0;
	}
</style>
<link rel="stylesheet" type="text/css" media="all" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
    <!-- <a href="/collaborativeReplenishment/index">Collaborative Replenishment</a> -->
	<ul class="nav_list">
		<li class="nav_active"><a href="/cpfr/index">调拨计划</a></li>
		<li><a href="/cpfr/purchase">采购计划</a></li>
		<li><a href="/cpfr/allocationProgress">调拨进度</a></li>
	</ul>
	<div class="button_box">
		<button id="addShipment" class="btn sbold red"> 新建调拨计划
			<i class="fa fa-plus"></i>
		</button>
		<button id="export" class="btn sbold blue"> Export
			<i class="fa fa-download"></i>
		</button>
	</div>
	<div class="content">
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
				<label for="seller_select">销售员</label>
				<select id="seller_select" onchange="status_filter(this.value,5)"></select>
			</div>
			<div class="filter_option">
				<label for="status_select">调拨状态</label>
				<select id="status_select"  onchange="status_filter(this.value,23)">
					<option id="" value="">全部</option>
					<option id="0" value ="资料提供中">资料提供中</option>
					<option id="1" value ="换标中">换标中</option>
					<option id="2" value ="待出库">待出库</option>
					<option id="3" value ="已发货">已发货</option>
					<option id="4" value ="取消发货">取消发货</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="account_number">账号</label>
				<select id="account_number" onchange="status_filter(this.value,7)"></select>
			</div>
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
			
		</div>
		<div style="height: 70px;">
			<div class="filter_option search_box">
				<input type="text" class="keyword" placeholder="Search by ASIN,SKU, or keywords">
				<button class="search">
					<svg t="1588043111114" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3742" width="18" height="18"><path d="M400.696889 801.393778A400.668444 400.668444 0 1 1 400.696889 0a400.668444 400.668444 0 0 1 0 801.393778z m0-89.031111a311.637333 311.637333 0 1 0 0-623.331556 311.637333 311.637333 0 0 0 0 623.331556z" fill="#ffffff" p-id="3743"></path><path d="M667.904 601.998222l314.766222 314.823111-62.919111 62.976-314.823111-314.823111z" fill="#ffffff" p-id="3744"></path></svg>
					搜索
				</button>	
				<button class="clear" onclick="handleClear()">清空筛选</button>
			</div>
		</div>
		<div style="position: relative;">
			<div style="position: absolute;left: 130px; z-index: 999;top:0" class="col-md-2">
				<button type="button" class="btn btn-sm green-meadow batch_operation">批量操作<i class="fa fa-angle-down"></i></button>
				<ul class="batch_list">
					<li><button class="btn btn-sm red-sunglo" onclick="statusAjax(0)">待计划确认</button></li>
					<li><button class="btn btn-sm yellow-crusta" onclick="statusAjax(1)">BU经理审核</button></li>
					<li><button class="btn btn-sm purple-plum" onclick="statusAjax(2)">BG总监审核</button></li>
					<li><button class="btn btn-sm green-meadow" onclick="statusAjax(3)">已确认</button></li>
					<li><button class="btn btn-sm blue-madison" onclick="statusAjax(4)">调拨取消</button></li>
				</ul>
			</div>
			<div class="col-md-5"  style="position: absolute;left: 520px; z-index: 999;top:0">
				<button type="button" class="btn btn-sm red-sunglo">待计划确认 : <span class="status0">0</span></button>
				<button type="button" class="btn btn-sm yellow-crusta">BU经理审核 : <span class="status1">0</span></button>
				<button type="button" class="btn btn-sm purple-plum">BG总监审核 : <span class="status2">0</span></button>
				<button type="button" class="btn btn-sm green-meadow">已确认 : <span class="status3">0</span></button>
				<button type="button" class="btn btn-sm blue-madison">调拨取消 : <span class="status4">0</span></button>
			</div>
			<table id="planTable" class="display table-striped table-bordered table-hover">
				<thead>
					<tr style="text-align: center;">
						<th>BG</th>
						<th>BU</th>
						<th>Station</th>
						<th><input type="checkbox" id="selectAll" name="selectAll" /></th>
						<th style="width:70px;">提交日期</th>
						<th style="width:55px;">销售员</th>
						<th style="width:70px;">产品图片</th>
						<th>账号</th>
						<th>Seller SKU</th>
						<th>ASIN <br>SKU</th>
						<th style="width:95px;">调入工厂仓库</th>
						<th style="width:70px;">需求数量</th>
						<th style="width:95px;">期望到货时间</th>
						<th style="width:80px;">是否贴标签</th>
						<th style="width:70px;">其它需求</th>
						<th style="width:80px;">可维持天数</th>
						<th style="width:70px;">FBA在库</th>
						<th style="width:110px;">FBA可维持天数</th>
						<th style="width:70px;">调拨在途</th>
						<th style="width:40px;">审核</th>
						<th style="width:100px;">调整需求数量</th>
						<th style="width:100px;">预计到货时间</th>
						<th style="width:100px;">调出仓库库位</th>
						<th style="width:70px;">调拨状态</th>
						<th style="width:70px;">待办事项</th>
					</tr>
				</thead>
				
			</table>
		</div>
		<div class="success_mask">
			<span class="mask_icon">
				<svg t="1586572594956" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="12690" width="24" height="24"><path d="M511.1296 0.2816C228.7616 0.2816 0 229.1456 0 511.4368c0 282.2656 228.864 511.1296 511.1296 511.1296 282.2912 0 511.1552-228.864 511.1552-511.1296C1022.2848 229.1712 793.4208 0.256 511.1296 0.256z m-47.104 804.8384l-244.5056-219.9808 72.448-73.2672 145.5872 112.9728c184.832-251.136 346.624-331.776 346.624-331.776l20.1984 30.464c-195.6864 152.192-340.48 481.5872-340.352 481.5872z" fill="#1DC50C" p-id="12691" data-spm-anchor-id="a313x.7781069.0.i18" class="selected"></path></svg>
			</span>
			<span class="mask_text success_mask_text"></span>
		</div>
		<div class="error_mask">
			<span class="mask_icon">
				<svg t="1586574167843" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="13580" width="24" height="24"><path d="M512 0A512 512 0 1 0 1024 512 512 512 0 0 0 512 0z m209.204301 669.673978a36.555699 36.555699 0 0 1-51.750538 51.640431L511.779785 563.64043 353.995699 719.662796a36.555699 36.555699 0 1 1-52.301075-51.089893 3.303226 3.303226 0 0 1 0.88086-0.88086L460.249462 511.779785l-157.013333-157.453763a36.665806 36.665806 0 1 1 48.777634-55.053764 37.876989 37.876989 0 0 1 2.972904 2.972903l157.233548 158.114409 157.784086-156.132473a36.555699 36.555699 0 0 1 51.420215 52.08086L563.750538 512.220215l157.013333 157.453763z" fill="#FF5252" p-id="13581"></path></svg>
			</span>
			<span class="mask_text error_mask_text"></span>
		</div>
	</div>
	<div class="mask_box">
		<div class="mask-dialog">
			<svg t="1588919283810" class="icon cancel_mask cancel_btn" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
			<h4 style="text-align: center; line-height: 38px;">调拨计划</h4>
			
			<div>
				<label for="audit_status_select" style="display: block;">审核</label>
				<select name="audit_status_select" disabled="disabled" id="audit_status_select" style="width:100%;height: 28px;margin-bottom: 20px;border: 1px solid rgba(220, 223, 230, 1);">
					<option value="0">待计划确认</option>
					<option value="1">BU经理审核</option>
					<option value="2">BG总监审核</option>
					<option value="3">已确认</option>
					<option value="4">调拨取消</option>
				</select>
			</div>
			
			<form  method="post" onsubmit="return false" action="##" id="formtest">
				<div class="mask-form">
					<div>
						<div class="wrap_one_single">
							<label for="asin_select">ASIN</label>
							<input type="text" id="asin_select" class="isSellerDisabled">
							<span class="errCode">ASIN不能为空!</span>
						</div>
						<div class="wrap_one_single">
							<label for="sku_select">SKU</label>
							<input type="text" id="sku_select" class="isSellerDisabled">
							<span class="errCode">SKU不能为空!</span>
						</div>
						<div class="wrap_one_single">
							<label for="quant_select">数量</label>
							<input type="text" id="quant_select" class="isSellerDisabled">
							<span class="errCode">数量不能为空!</span>
						</div>
						<div class="wrap_one_single">
							<label for="">到货时间</label>
							<div class="input-group date date-picker margin-bottom-5 bw9" id="maskDate">
								<input type="text" class="form-control form-filter input-sm maskDate isSellerDisabled" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
								<span class="input-group-btn">
									<button class="btn btn-sm default default_btn request_date_btn" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
							</div>
							<span class="errCode">到货时间不能为空!</span>
						</div>
						
					</div>
					<div>
						<label for="site_select">站点</label>
						<select name="site_select" id="site_select" class="isSellerDisabled">
							<option value ="ATVPDKIKX0DER">US</option>
							<option value ="A2EUQ1WTGCTBG2">CA</option>
							<option value ="A1AM78C64UM0Y8">MX</option>
							<option value ="A1F83G8C2ARO7P">UK</option>
							<option value ="A13V1IB3VIYZZH">FR</option>
							<option value ="A1PA6795UKMFR9">DE</option>
							<option value ="APJ6JRA9NG5V4">IT</option>
							<option value ="A1RKKUPIHCS9HS">ES</option>
							<option value ="A1VC38T7YXB528">JP</option>
						</select>
						<div class="wrap_one_single">
							<label for="seller_sku_select">SellerSKU</label>
							<select name="seller_sku_select" id="seller_sku_select" class="isSellerDisabled"><option value ="">请选择</option></select>
							<span class="errCode">SellerSKU不能为空!</span>
						</div>
						<div class="wrap_one_single">
							<label for="warehouse_select">调入工厂</label>
							<select name="warehouse_select" id="warehouse_select" class="isSellerDisabled"><option value ="">请选择</option></select>
							<span class="errCode">调入仓库不能为空!</span>
						</div>
						
						
						<label for="">是否贴RMS</label>
						<div>
							<select name="" id="rms_input" style="border: 1px solid rgba(220, 223, 230, 1); height: 28px;">
								<option value="1">是</option>
								<option value="0">否</option>
							</select>
							<input type="text" id="rms_sku_input" placeholder="是,请输入RMS_SKU" class="isSellerDisabled" style="border: 1px solid rgba(220, 223, 230, 1); height: 28px; width: 80%;">
						</div>
					</div>
				</div>
				<div style="border-bottom: 1px dashed rgba(220, 223, 230, 1);padding-bottom: 10px;">
					<label for="" style="display: block;">备注</label>
					<input type="text" id="remarks_input" class="isSellerDisabled" style="width: 100%;margin-bottom: 10px;height: 28px;border: 1px solid rgba(220, 223, 230, 1)">
				</div>
				<div class="mask-form" style="padding-top: 10px;">
					<div>
						
						<label for="">调出工厂</label><input type="text" class="isPlanDisabled" id="out_warehouse_input">
						<label for="">计划确认数量</label><input type="text" class="isPlanDisabled" id="adjustment_quantity_input">
					</div>
					<div>
						<label for="">预计到货时间</label>
						<div class="input-group date date-picker margin-bottom-5 bw9" id="arrivalMaskDate">
							<input type="text" class="form-control form-filter input-sm arrivalMaskDate isPlanDisabled" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
							<span class="input-group-btn">
								<button class="btn btn-sm default default_btn estimated_delivery_date_btn" type="button">
									<i class="fa fa-calendar"></i>
								</button>
							</span>
						</div>
						
					</div>
				</div>
			</form>
			<div class="form_btn">
				<button class="cancel_btn">取消</button>
				<input type="hidden" class="formId">
				<button class="updateConfirm">确认</button>
			</div>
			
		</div>
	</div>
	<div class="mask_upload_box">
		<div class="mask_upload_dialog">
			<svg t="1588919283810"class="icon cancel_upload_btn cancelUpload" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
			
			<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
			<form style="height: 130px; overflow: hidden;" id="fileupload" action="{{ url('send') }}" method="POST" enctype="multipart/form-data">
			    {{ csrf_field() }}
				<input type="hidden" name="warn" id="warn" value="0">
			    <input type="hidden" name="inbox_id" id="inbox_id" value="0">
			    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
								
			    <div>
			        <div class="fileupload-buttonbar">
			            <div class="col-lg-12" style="text-align: center;margin-bottom: 20px;">
			                <span class="btn green fileinput-button">
								<i class="fa fa-plus"></i>
								<span>添加文件</span>
								<input type="file" name="files[]" multiple=""> 
							</span>
			                <span class="fileupload-process"> </span>
			            </div>
			        </div>
					<table role="presentation" class="table table-striped clearfix" id="table-striped" style="margin-bottom: 0;">
					    <tbody class="files" id="filesTable"> </tbody>
					</table>
					<div class="col-lg-12 fileupload-progress fade">
					    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
					        <div class="progress-bar progress-bar-success" style="width:0%;"> </div>
					    </div>
					    <div class="progress-extended"> &nbsp; </div>
					</div>
			        
			        <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
			            <div class="slides"> </div>
			            <h3 class="title"></h3>
			            <a class="prev"> ‹ </a>
			            <a class="next"> › </a>
			            <a class="close white"> </a>
			            <a class="play-pause"> </a>
			            <ol class="indicator"> </ol>
			        </div>
			        <script id="template-upload" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
			        <tr class="template-upload fade">
			            <td style="text-align: center;">
			                <p style="width: 200px; overflow: hidden; margin: 7px auto; text-overflow: ellipsis;" class="name">{%=file.name%}</p>
			                <strong class="error text-danger label label-danger" style="padding: 0 6px;"></strong>
			            </td>
			            <td style="text-align: center;"> {% if (!i && !o.options.autoUpload) { %}
			                <button class="btn blue start" disabled>
			                    <i class="fa fa-upload"></i>
			                    <span>开始</span>
			                </button> {% } %} {% if (!i) { %}
			                <button class="btn red cancel">
			                    <i class="fa fa-ban"></i>
			                    <span>取消</span>
			                </button> {% } %} </td>
			        </tr> {% } %} </script>
			        <script id="template-download" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
			        <tr class="template-download fade">
			            <td>
			                <p class="name"> {% if (file.url) { %}
			                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl? 'data-gallery': ''%}>{%=file.name%}</a> {% } else { %}
			                    <span>{%=file.name%}</span> {% } %}
			                    {% if (file.name) { %}
			                        <input type="hidden" name="fileid[]" class="filesUrl" value="{%=file.url%}">
			                    {% } %}
			                    </p> {% if (file.error) { %}
			                <div>
			                    <span class="label label-danger">Error</span> {%=file.error%}</div> {% } %} </td>
			            
			        </tr> {% } %} </script>
			        <div style="clear:both;"></div>
			    </div>
			</form>	
			<div style="text-align: center;">
				<input type="hidden" class="uploadId">
				<button class="btn warning cancel cancelUpload" style="width: 80px;border: 1px solid #ccc;">取消</button>
				<button class="btn blue start" id="confirmUpload">确认上传</button>
			</div>
		</div>
	</div>
	
	<script>
		/* http://10.10.42.14/vl/public */
		/*审核 销售员不可编辑 */
		//筛选
		function status_filter(value,column) {
		    if (value == '') {
		        tableObj.column(column).search('').draw();
		    }
		    else tableObj.column(column).search(value).draw();
		}
		//清空筛选
		function handleClear(){
			$('#marketplace_select').val("");
			$('#bg_select').val("");
			$('#bu_select').val("");
			$('#seller_select').val("");
			$('#status_select').val("");
			$('#account_number').val("");
			$('.keyword').val("");
			$('.createTimeInput').val("");
			let val = '';
			status_filter(val,0);
			status_filter(val,1);
			status_filter(val,2);
			status_filter(val,5);
			status_filter(val,7);
			status_filter(val,24);
			let reqList = {
				"condition" : '',
				"date_s": '',
				"date_e": '',
			};
			tableObj.ajax.reload();
		}
		//批量操作
		function statusAjax(status){
			let chk_value = '';
			$("input[name='checkedInput']:checked").each(function (index,value) {
				if(chk_value != ''){
					chk_value = chk_value + ',' + $(this).val()	
				}else{
					chk_value = chk_value + $(this).val()
				}
			});
			if(chk_value == ""){
				alert('请先选择数据!')
			}else{
				$.ajax({
				    type: "POST",
					url: "/shipment/upAllStatus",
					data: {
						status: status,
						idList: chk_value
					},
					success: function (res) {
						if(res.status == 0){
							$('.error_mask').fadeIn(1000);
							$('.error_mask_text').text(res.msg);
							setTimeout(function(){
								$('.error_mask').fadeOut(1000);
							},2000)
						}else if(res.status == 1){
							$('.success_mask').fadeIn(1000);
							$('.success_mask_text').text(res.msg);
							setTimeout(function(){
								$('.success_mask').fadeOut(1000);
							},2000)	
							tableObj.ajax.reload();
							$('#selectAll:checked').prop('checked',false);
						}
					},
					error: function(err) {
						console.log(err)
					}
				});
				
			}
		}
		//判断sku、asin、sellersku为空时，显示必填提示
		function isShowErrCode(){
			if($('#audit_status_select').val() == ""){
				$('#audit_status_select').parent().find('.errCode').show();
			}
			if($('#sku_select').val() == ""){
				$('#sku_select').parent().find('.errCode').show();
			}
			if($('#asin_select').val() == ""){
				$('#asin_select').parent().find('.errCode').show();
			}
			if($('#seller_sku_select').val() == ""){
				$('#seller_sku_select').parent().find('.errCode').show();
			}
			if($('#warehouse_select').val() == ""){
				$('#warehouse_select').parent().find('.errCode').show();
			}
			if($('#quant_select').val() == ""){
				$('#quant_select').parent().find('.errCode').show();
			}
			if($('.maskDate').val() == ""){
				$('#maskDate').parent().find('.errCode').show();
			}
		}
		$(document).ready(function () {	
			//导出调拨进度
			$('#export').click(function(){
				 let chk_value = '';
				 $("input[name='checkedInput']:checked").each(function (index,value) {
				 	if(chk_value != ''){
				 		chk_value = chk_value + ',' + $(this).val()	
				 	}else{
				 		chk_value = chk_value + $(this).val()	
				 	}
				 });
				 $.ajax({
					url: "/shipment/index",
					 method: 'POST',
					 cache: false,
					 data: {
						downLoad: 1,
						ids: chk_value,
						label: $('#account_number').val(),
						date_s: cusstr($('.createTimeInput').val() , ' - ' , 1),
						date_e: cusstr1($('.createTimeInput').val() , ' - ' , 1),
						allor_status: $('#status_select').find("option:selected").attr("id"),
						sx: $("#marketplace_select").val(),
						ubg: $("#bg_select").val(),
						ubu: $("#bu_select").val(),
						name: $("#seller_select").val(),
						condition: $(".keyword").val(),
					 },
								
					 success: function (data) {
						 if(data != ""){
							var fileName = "调拨计划";
							function msieversion() {
								 var ua = window.navigator.userAgent;
								 var msie = ua.indexOf("MSIE ");
								 if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
									 return true;
								 } else {
									 return false;
								 }
								 return false;
							}
										 
							if (msieversion()) {
								 var IEwindow = window.open();
								 IEwindow.document.write('sep=,\r\n' + data);
								 IEwindow.document.close();
								 IEwindow.document.execCommand('SaveAs', true, fileName + ".csv");
								 IEwindow.close();
							} else {
								 var uri = "data:text/csv;charset=utf-8,\ufeff" + data;
								 var uri = 'data:application/csv;charset=utf-8,\ufeff' + encodeURI(data);
								 var link = document.createElement("a");
								 link.href = uri;
								 link.style = "visibility:hidden";
								 link.download = fileName + ".csv";
								 document.body.appendChild(link);
								 link.click();
								 document.body.removeChild(link);
							}
							$('#selectAll:checked').prop('checked',false);
							$("input[name='checkedInput']:checked").prop('checked',false);
						 }
					 } 
				 });
					 
			})
			
			//上传大货资料弹窗隐藏
			$('.cancelUpload').on('click',function(){
				$('.mask_upload_box').hide();
			})
			
			//上传大货资料
			$('#confirmUpload').on('click',function(){
				let fileList = '';
				let str = $('#table-striped tbody tr td').find('.filesUrl');
				
				for(var i=0;i<str.length;i++){
					fileList=(str[0].defaultValue)
				}
				$.ajax({
				    type: "POST",
					url: "/shipment/upCargoData",
					data: {
						id: $('.uploadId').val(),
						cargo_data: fileList
					},
					success: function (res) {
						if(res.status == 0){
							$('.error_mask').fadeIn(1000);
							$('.error_mask_text').text(res.msg);
							setTimeout(function(){
								$('.error_mask').fadeOut(1000);
							},2000)
						}else if(res.status == 1){
							$('.success_mask').fadeIn(1000);
							$('.success_mask_text').text(res.msg);
							setTimeout(function(){
								$('.success_mask').fadeOut(1000);
							},2000)	
							$('.mask_upload_box').hide();
						}
					},
					error: function(err) {
						console.log(err)
					}
				});
			})
			//新建调拨计划
			$('#addShipment').on('click',function(){
				clearVal();
				$('.mask_box').show();
				$('.isPlanDisabled').attr('disabled',false).css('background',"#fff");
				$('#audit_status_select').attr('disabled',true).css('background',"#eee");
				$('.isSellerDisabled').attr('disabled',false).css('background',"#fff");
				$('.estimated_delivery_date_btn').attr('disabled',false).css('background',"#fff");
				$('.request_date_btn').attr('disabled',false).css('background',"#fff");
			})
			
			$('#sku_select').on('input',function(){
				if($(this).val() == ""){
					$(this).parent().find('.errCode').show();
				}else{
					$(this).parent().find('.errCode').hide();
				}
			})
			//获取sku、sellerSku、及调入工厂
			function getAjaxData(site,asin){
				$.ajax({
				    type: "POST",
					url: "/shipment/getSellerSku",
					data: {
						marketplace_id: site,
						asin: asin
					},
					success: function (res) {
						$('#sku_select').val(res.sku);
						//获取seller_sku下拉列表
						$("#seller_sku_select").empty();
						$("#seller_sku_select").append("<option value=''>请选择</option>");
						$.each(res.seller_sku_list, function (index, value) {
							$("#seller_sku_select").append("<option value='"+value.seller_sku+"'>"+value.seller_sku+"</option>");
						});
						//获取调入工厂下拉列表
						$("#warehouse_select").empty();
						$("#warehouse_select").append("<option value=''>请选择</option>");
						$.each(res.factoryList, function (index, value) {
							$("#warehouse_select").append("<option value='"+value.sap_factory_code+"'>"+value.sap_factory_code+"</option>");
						});
					},
					error: function(err) {
						console.log(err)
					}
				});
			}
			//通过ASIN获取SellerSKU
			$('#asin_select').on('input',function(){
				if($(this).val() == ""){
					$(this).parent().find('.errCode').show();
				}else{
					$(this).parent().find('.errCode').hide();
				}
				getAjaxData($('#site_select').val(),$(this).val());
			})
			$('#site_select').on('change',function(){
				getAjaxData($(this).val(),$('#asin_select').val());
				if($('#sku_select').val() != ""){
					$('#sku_select').parent().find('.errCode').show();
				}else{
					$('#sku_select').parent().find('.errCode').hide();
				}
			})
			//通过SellerSKU获取调入仓库
			$('#seller_sku_select').on('change',function(){
				if($(this).val() == ""){
					$(this).parent().find('.errCode').show();
				}else{
					$(this).parent().find('.errCode').hide();
				}
			})
			
			//当调入仓库不为空时，隐藏必填项提示
			$('#warehouse_select').on('change',function(){
				if($(this).val() == ""){
					$(this).parent().find('.errCode').show();
				}else{
					$(this).parent().find('.errCode').hide();
				}	
			})
			//当数量不为空时，隐藏必填项提示
			$('#quant_select').on('input',function(){
				if($(this).val() == ""){
					$(this).parent().find('.errCode').show();
				}else{
					$(this).parent().find('.errCode').hide();
				}	
			})
			//当到货时间不为空时，隐藏必填项提示
			$('.maskDate').on('change',function(){
				if($(this).val() == ""){
					$(this).parent().parent().find('.errCode').show();
				}else{
					$(this).parent().parent().find('.errCode').hide();
				}
			})
			
			//新建调拨计划时清空内容
			function clearVal(){
				$('.formId').val("");
				$('#audit_status_select').val(0);
				$('#site_select').val('ATVPDKIKX0DER');
				$('#sku_select').val('');
				$('#asin_select').val('');
				$('#seller_sku_select').val('');
				$('#warehouse_select').val('');
				$('#out_warehouse_input').val('');
				$('#quant_select').val('');
				$('.maskDate').val('');
				$('.arrivalMaskDate').val('');
				$('#adjustment_quantity_input').val('');
				$('#rms_input').val(1);
				$('#rms_sku_input').val('');
				$('#remarks_input').val('');
			}
			//调拨计划更新
			$('.updateConfirm').on('click',function(){
				if($('#asin_select').val() == ''){
					$('#asin_select').parent().find('.errCode').show();
					return
				}
				if($('#sku_select').val() == ""){
					$('#sku_select').parent().find('.errCode').show();
					return
				}
				if($('#seller_sku_select').val() == ''){
					$('#seller_sku_select').parent().find('.errCode').show();
					return
				}
				if($('#quant_select').val() == ""){
					$('#quant_select').parent().find('.errCode').show();
					return
				}
				if($('#warehouse_select').val() == ""){
					$('#warehouse_select').parent().find('.errCode').show();
					return
				}
				if($('.maskDate').val() == ""){
					$('#maskDate').parent().find('.errCode').show();
					return
				}
				if($('.formId').val() == ""){ //判断有id时为编辑，没有id为新增
					$.ajax({
					    type: "POST",
						url: "/shipment/addShipment",
						data: {
							sku: $('#sku_select').val(),
							asin: $('#asin_select').val(),
							status: $('#audit_status_select').val(),
							seller_sku: $('#seller_sku_select').val(),
							warehouse: $('#warehouse_select').val(),
							out_warehouse: $('#out_warehouse_input').val(),
							quantity: $('#quant_select').val(),
							received_date: $('.maskDate').val(),
							adjustreceived_date: $('.arrivalMaskDate').val(),
							adjustment_quantity: $('#adjustment_quantity_input').val(),
							rms: $('#rms_input').val(),
							rms_sku: $('#rms_sku_input').val(),
							remark: $('#remarks_input').val(),
							marketplace_id: $("#site_select").val()
						},
						success: function (res) {
							$('.mask_box').hide();
							if(res.status == 0){
								$('.error_mask').fadeIn(1000);
								$('.error_mask_text').text(res.msg);
								setTimeout(function(){
									$('.error_mask').fadeOut(1000);
								},2000)
							}else if(res.status == 1){
								$('.success_mask').fadeIn(1000);
								$('.success_mask_text').text(res.msg);
								setTimeout(function(){
									$('.success_mask').fadeOut(1000);
								},2000)	
								tableObj.ajax.reload();
								clearVal();
							}
						},
						error: function(err) {
							console.log(err)
						}
					});
				}else{
					$.ajax({
					    type: "POST",
						url: "/shipment/upShipment",
						data: {
							id: $('.formId').val(),
							sku: $('#sku_select').val(),
							asin: $('#asin_select').val(),
							status: $('#audit_status_select').val(),
							seller_sku: $('#seller_sku_select').val(),
							sap_factory_code: $('#warehouse_select').val(),
							out_warehouse: $('#out_warehouse_input').val(),
							quantity: $('#quant_select').val(),
							received_date: $('.maskDate').val(),
							adjustreceived_date: $('.arrivalMaskDate').val(),
							adjustment_quantity: $('#adjustment_quantity_input').val(),
							rms: $('#rms_input').val(),
							rms_sku: $('#rms_sku_input').val(),
							remark: $('#remarks_input').val(),
							marketplace_id: $("#site_select").val()
						},
						success: function (res) {
							$('.mask_box').hide();
							if(res.status == 0){
								$('.error_mask').fadeIn(1000);
								$('.error_mask_text').text(res.msg);
								setTimeout(function(){
									$('.error_mask').fadeOut(1000);
								},2000)
							}else if(res.status == 1){
								$('.success_mask').fadeIn(1000);
								$('.success_mask_text').text(res.msg);
								setTimeout(function(){
									$('.success_mask').fadeOut(1000);
								},2000)	
								tableObj.ajax.reload();
								clearVal();
							}
						},
						error: function(err) {
							console.log(err)
						}
					});
				}
				
				
			})
			
			//时间选择器
			$('.date-picker').datepicker({
				format: 'yyyy-mm-dd',
			    autoclose: true,
				//datesDisabled : new Date(),
				
			});
			$('.cancel_btn').on('click',function(){
				$('.mask_box').hide();
			})
			
			//批量操作列表展开
			$('.batch_operation').click(function(e){
				$('.batch_list').slideToggle();
				$(document).one('click',function(){
					$('.batch_list').hide();
				})
				e.stopPropagation();
			})
			//编辑列表
			function editTableData(id){
				$.ajax({
				    type: "POST",
					url: "/shipment/detailShipment",
					data: {
						id: id
					},
					success: function (res) {
						//获取seller_sku下拉列表
						$("#seller_sku_select").empty();
						$("#seller_sku_select").append("<option value=''>请选择</option>");
						$.each(res.seller_sku_list, function (index, value) {
							$("#seller_sku_select").append("<option value='"+value.seller_sku+"'>"+value.seller_sku+"</option>");
						});
						//获取调入工厂下拉列表
						$("#warehouse_select").empty();
						$("#warehouse_select").append("<option value=''>请选择</option>");
						$.each(res.factoryList, function (index, value) {
							$("#warehouse_select").append("<option value='"+value.sap_factory_code+"'>"+value.sap_factory_code+"</option>");
						});
						if(res.shipment.role == 1){
							$('#audit_status_select').attr('disabled',true).css('background',"#eee");
							$('.isSellerDisabled').attr('disabled',false).css('background',"#fff");
							$('.isPlanDisabled').attr('disabled',true).css('background',"#eee");
							$('.estimated_delivery_date_btn').attr('disabled',true).css('background',"#eee");
							$('.request_date_btn').attr('disabled',false).css('background',"#fff");
						}else if(res.shipment.role == 2){
							$('.isPlanDisabled').attr('disabled',false).css('background',"#fff");
							$('#audit_status_select').attr('disabled',false).css('background',"#fff");
							$('.isSellerDisabled').attr('disabled',true).css('background',"#eee");
							$('.estimated_delivery_date_btn').attr('disabled',false).css('background',"#fff");
							$('.request_date_btn').attr('disabled',true).css('background',"#eee");
						}else{
							$('#audit_status_select').attr('disabled',true).css('background',"#eee");
							$('.isPlanDisabled').attr('disabled',true).css('background',"#eee");
							$('.isSellerDisabled').attr('disabled',true).css('background',"#eee");
							$('.estimated_delivery_date_btn').attr('disabled',true).css('background',"#eee");
							$('.request_date_btn').attr('disabled',true).css('background',"#eee");
						}
						$('#audit_status_select').val(res.shipment.status);//审核
						$('#sku_select').val(res.shipment.sku);//SKU
						$('#site_select').val(res.shipment.marketplace_id);//站点
						$('#asin_select').val(res.shipment.asin);//ASIN
						$('#seller_sku_select').val(res.shipment.seller_sku);//SellerSku
						$('#warehouse_select').val(res.shipment.sap_factory_code);//调入工厂
						$('#out_warehouse_input').val(res.shipment.out_warehouse);//调出工厂
						$('#quant_select').val(res.shipment.quantity);//数量
						$('.maskDate').val(res.shipment.received_date);//到货时间
						$('.arrivalMaskDate').val(res.shipment.adjustreceived_date);//预计到货时间
						$('#adjustment_quantity_input').val(res.shipment.adjustment_quantity);//计划确认数量
						$('#rms_input').val(res.shipment.rms);//RMS
						$('#rms_sku_input').val(res.shipment.rms_sku);//RMS_SKU
						$('#remarks_input').val(res.shipment.remark);//备注
					},
					error : function(err) {
						console.log(err)
					}
				});
			}
			//禁止警告弹窗弹出
			$.fn.dataTable.ext.errMode = 'none';
			tableObj = $('#planTable').DataTable({
				lengthMenu: [
				    20, 50, 100, 'All'
				],
				dispalyLength: 2, // default record count per page
				paging: true,  // 是否显示分页
				info: false,// 是否表格左下角显示的文字
				ordering: false,
				//scrollX: "100%",
				//scrollCollapse: false,
				fixedColumns: { //固定列的配置项
					leftColumns: 4, //固定左边第一列
					rightColumns: 1, //固定左边第一列
				},
				serverSide: false,//是否所有的请求都请求服务器	
				scrollX: "100%",
				scrollCollapse: false,
				ajax: {
					url: "/shipment/index",
					type: "post",
					data :  function(){
						reqList = {
							"condition" : $('.keyword').val(),
							"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
							"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
						};
						return reqList;
					},
					dataSrc:function(res){
						$('.status0').text(res[1].status0);
						$('.status1').text(res[1].status1);
						$('.status2').text(res[1].status2);
						$('.status3').text(res[1].status3);
						$('.status4').text(res[1].status4);
						$("#seller_select").empty();
						$("#seller_select").append("<option value=''>全部</option>");
						$.each(res[2], function (index, value) {
							$("#seller_select").append("<option value='" + value + "'>" + value + "</option>");
						})
						
						$("#account_number").empty();
						$("#account_number").append("<option value=''>全部</option>");
						$.each(res[3], function (index, value) {
							$("#account_number").append("<option value='" + value + "'>" + value + "</option>");
						})
						return res[0];
					},
				},			
				data: [],
				columns: [
					{
						data: "ubg" ,
						visible: false,
					},
					{
						data: "ubu" ,
						visible: false,
					},
					{
						data: 'domin_sx',
						visible: false,
					},
					{
						data: "id",
						orderable: false,
						bSortable: false,
						render: function(data, type, row, meta) {
							var content = '<input type="checkbox" name="checkedInput"  class="checkbox-item" value="' + data + '" />';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							
						}
					},
					{
						data: "created_at",
					},
					{
						data: 'name',
					},
					{
						data: 'image',
						render: function(data, type, row, meta) {
							var content = '<a href="https://'+row.toUrl+'/dp/'+ row.asin +'" target="_blank" style="text-decoration:none"><img src="https://images-na.ssl-images-amazon.com/images/I/'+data+'" alt="" style="display:block; width:60px; height:60px; margin:0 auto"></a>';
							return content;
						},
					},
					{
						data: 'label',
					},
					{ 
						data: 'seller_sku',
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id);
							});
						}
					},
					{
						data: null,
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+row.asin +'<br>'+ row.sku+'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id); 
							});
						}
					},
					{
						data: 'warehouse',
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id); 
							});
						}
					},
					{
						data: 'quantity',
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id); 
							});
						}
					},
					{
						data: "received_date",
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id); 
							});
						}
					},
					{
						data: "package",
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id);
							});
						}
					},
					{
						data: "remark",
						render: function(data, type, row, meta) {
							var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on( 'click', function () {
								$('.mask_box').show();
								$('.formId').val(rowData.id);
								editTableData(rowData.id);
							});
						}
					},
					{ data: "stock_day_num" },
					{ data: "FBA_Stock" },
					{ data: "FBA_keepday_num" },
					{ data: "transfer_num" },
					{
						data: "status",
						render: function(data, type, row, meta) {
							if(data == 0){ data = '待审核' }
							else if(data == 1){ data = 'bu审核' }
							else if(data == 2){ data = 'bg审核' }
							else if(data == 3){ data = '已确认' }
							else if(data == 4){ data = '调拨取消' }
							var content = '<div>'+data+'</div>';
							return content;
						}
					},
					{ data: "adjustment_quantity" },
					{ data: "adjustreceived_date" },
					{ data: "out_warehouse" },
					{
						data: "allor_status",
						render: function(data, type, row, meta) {
							if(data == 0){ data = '资料提供中' }
							else if(data == 1){ data = '换标中' }
							else if(data == 2){ data = '待出库' }
							else if(data == 3){ data = '已发货' }
							else if(data == 4){ data = '取消发货' }
							var content = '<div>'+data+'</div>';
							return content;
						}
					},
					{
						data: "allot",
						render: function(data, type, row, meta) {
							if(data == 0){
								data = '<button style="width:110px" class="upCargoDataBtn">上传大货资料</button>'
							}else if(data == 1){
								data = '<div>维护条形码</div>'
							}else{
								data = ''
							}
							var content = data;
							return content;
						},
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).on("click",".upCargoDataBtn",function(){ 
								$('.mask_upload_box').show();
								$('.uploadId').val(rowData.id);
							}) 
						}
					},
				], 
				
			});
			
			//全选
			$("body").on('change','#selectAll',function(e) {
			    $("input[name='checkedInput']").prop("checked", this.checked);
			}); 
			//单条选中
			$("body").on('change','.checkbox-item',function(e){
				console.log(this.checked) 
				var $subs = $("input[name='checkedInput']");
				$("input[name='selectAll']").prop("checked", $subs.length == $subs.filter(":checked").length ? true :false);
				e.cancelBubble=true;
			});
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
			        "昨天": [moment().subtract( 1,"days"), moment().subtract(1,"days")],
			        "7天前": [moment().subtract(6,"days"), moment()],
			        "30天前": [moment().subtract(29,"days"), moment()],
			        "这个月": [moment().startOf("month"), moment().endOf("month")],
			        "上个月": [moment().subtract(1,"month").startOf("month"), moment().subtract(1,"month").endOf("month")]
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
				}
			    //minDate: "01/01/2012",
			    //maxDate: "12/31/2018"
			}, function (t, e) {
			    $("#createTimes input").val(t.format("YYYY-MM-DD") + " - " + e.format("YYYY-MM-DD"));	
				let reqList = {
					"condition" : $('.keyword').val(),
					"created_at_s": cusstr($('#createTimes input').val() , ' - ' , 1),
					"created_at_e": cusstr1($('#createTimes input').val() , ' - ' , 1),
				};
				tableObj.ajax.reload();
			})
			
			//截取字符前面的
			function cusstr(str, findStr, num){
				if(str.length > 0){
					let idx = str.indexOf(findStr);
					let count = 1;
					while(idx >= 0 && count < num){
					    idx = str.indexOf(findStr, idx+1);
					    count++;
					}    
					if(idx < 0){
					    return '';
					}
					return str.substring(0, idx);
				}else{
					return ''
				}
			}
			//截取字符前面的
			function cusstr1(str, findStr, num){
				if(str.length > 0){
					let idx = str.indexOf(findStr);
					let count = 1;
					while(idx >= 0 && count < num){
						idx = str.indexOf(findStr, idx+1);
						count++;
					}    
					if(idx < 0){
						return '';
					}
					return str.substring(idx+3);
				}else{
					return ''
				}
			}
			
			//搜索
			$('.search').on('click',function(){
				let reqList = {
					"condition" : $('.keyword').val(),
					"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
					"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
				};
				tableObj.ajax.reload();
			})
			$('.keyword').on('input',function(){
				let reqList = {
					"condition" : $('.keyword').val(),
					"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
					"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
				};
				tableObj.ajax.reload();
			})
		})
		
	</script>
@endsection