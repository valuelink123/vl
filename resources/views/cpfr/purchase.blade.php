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
		width: 210px;
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
	/* 弹出框样式 */
	.mask_box{
		display: none;
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
		height: 680px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px 60px;
		margin-top: -340px;
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
		border: 1px solid rgba(220, 223, 230, 1);
		padding-left: 10px;
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
	#purchasetable_filter{
		display: none;
	}
	.input-medium{
		width:210px !important;
	}
	.table-scrollable{
		overflow-x: hidden;
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
		<button id="addPurchaseBtn" class="btn sbold red"> 新建采购计划
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
				<label for="seller_select">销售员</label>
				<select id="seller_select" onchange="status_filter(this.value,5)">
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
				<button class="clear" onclick="handleClear()">清空筛选</button>
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
						<li><button class="btn btn-sm red-sunglo noConfirmed" onclick="statusAjax(0)">审核中</button></li>
						<li><button class="btn btn-sm yellow-crusta" onclick="statusAjax(1)">生产中</button></li>
						<li><button class="btn btn-sm purple-plum" onclick="statusAjax(2)">分拣中</button></li>
						<li><button class="btn btn-sm blue-hoki" onclick="statusAjax(3)">已出库</button></li>
						<li><button class="btn btn-sm blue-madison" onclick="statusAjax(4)">已签收</button></li>
						<li><button class="btn btn-sm green-meadow" onclick="statusAjax(5)">已完成</button></li>
					</ul>
				</div>
				<div class="col-md-6"  style="position: absolute;left: 520px; z-index: 999;top:0">
					<button type="button" class="btn btn-sm red-sunglo">审核中 : <span class="status0"></span></button>
					<button type="button" class="btn btn-sm yellow-crusta">生产中 : <span class="status1"></span></button>
					<button type="button" class="btn btn-sm purple-plum">分拣中 : <span class="status2"></span></button>
					<button type="button" class="btn btn-sm blue-hoki">已出库 : <span class="status3"></span></button>
					<button type="button" class="btn btn-sm blue-madison">已签收 : <span class="status4"></span></button>
					<button type="button" class="btn btn-sm green-meadow">已完成 : <span class="status5"></span></button>
				</div>
	            <table class="table table-striped table-bordered" id="purchasetable">
	                <thead>
	                <tr>
						<th>BG</th>
						<th>BU</th>
						<th>site</th>
	                    <th><input type="checkbox" id="selectAll" /></th>
	                    <th style="width:75px; text-align:center;">提交日期</th>
	                    <th style="width:55px; text-align:center;">销售员</th>
	                    <th style="text-align:center;">产品图片</th>
	                    <th style="text-align:center;">ASIN</th>
	                    <th style="text-align: center;">SKU</th>
						<th style="width:75px; text-align:center;">需求数量</th>
						<th style="width:110px; text-align:center;">期望到货时间</th>
	                    <th style="width:75px; text-align:center;">海外库存</th>
	                    <th style="width:75px; text-align:center;">未交订单</th>
	                    <th style="width:75px; text-align:center;">加权日均</th>
	                    <th style="width:120px; text-align:center;">到货后预计日销<div style="text-align: center;">(PCS)</div></th>
	                    <th style="width:55px; text-align:center;">利润率</th>
	                    <th style="width:75px; text-align:center;">审核结果</th>
	                    <th style="width:55px; text-align:center;">计划员</th>
	                    <th>MOQ</th>
	                    <th style="width:75px; text-align:center;">收货工厂</th>
						<th style="width:75px; text-align:center;">运输方式</th>
						<th style="width:100px; text-align:center;">计划确认数量</th>
						<th style="width:100px; text-align:center;">预计交货时间</th>
						<th style="width:80px; text-align:center;">采购订单号</th>
						<th style="width:75px; text-align:center;">完成进度</th>
	                </tr>
	                </thead>
	                <tbody></tbody>
	            </table>
	        </div>
	    </div>
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
		<h4 style="text-align: center; line-height: 26px;">采购计划</h4>
		<div class="control-label">
			<label for="audit_status_select" class="" style="display: block;">审核</label>
			<select name="audit_status_select" id="audit_status_select" disabled="true" class="col-sm-12">
				<option value="0">待计划确认</option>
				<option value="1">BU经理审核</option>
				<option value="2">BG总监审核</option>
				<option value="3">已确认</option>
				<option value="4">调拨取消</option>
			</select>
		</div>
		<div class="warp_box">
			<label for="sku_input" class="must wrap_one_single">SKU<input type="text" id="sku_input" class="isSellerDisabled" /><b class="err plan_status_err">SKU不能为空!</b></label>
			<label for="site_select" class="must wrap_one_single">站点
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
				<b class="err plan_status_err">站点不能为空!</b>
			</label>
		</div>
		<div class="warp_box">
			<label for="asin_select" class="wrap_one_single">ASIN<select name="asin_select" class="isSellerDisabled" id="asin_select"></select></label>
			<label for="sellersku_select" class="wrap_one_single">SellerSKU<select name="sellersku_select" class="isSellerDisabled" id="sellersku_select"></select></label>			
		</div>
		<div class="warp_box">
			<label class="must wrap_one_single" for="quantity_input">数量<input type="text" id="quantity_input" class="isSellerDisabled"><b class="err plan_status_err">数量不能为空!</b></label>
			<label for="sap_factory_select" class="must wrap_one_single">调入工厂<select name="sap_factory_select" class="isSellerDisabled" id="sap_factory_select"></select><b class="err plan_status_err">调入工厂不能为空!</b></label>			
		</div>
		<div class="warp_box">
			<label class="must wrap_one_single" for="">
				到货时间
				<div class="input-group date date-picker margin-bottom-5 bw9" id="maskDate">
					<input type="text" class="form-control form-filter input-sm maskDate isSellerDisabled" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
					<span class="input-group-btn">
						<button class="btn btn-sm default default_btn mask_default_btn request_date_btn" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
				</div>
				<b class="err plan_status_err">到货时间不能为空!</b>
			</label>
			<label class="wrap_one_single" for="">备注<input type="text" id="remark_input" class="isSellerDisabled"></label>
		</div>
		<!-- <div class="control-label" style="margin-bottom: 30px;">
			<label for="" class="" style="display: block;">备注</label>
			<input type="text" class="col-sm-12">
		</div> -->
		<div class="warp_box" style="border-top: 1px dashed #ccc; padding-top: 25px;">
			<label for="received_factory_input" class="wrap_one_single">调出工厂<input type="text" id="received_factory_input" class="isPlanDisabled"></label>
			<label for="sap_shipment_code" class="wrap_one_single">运输方式<input type="text" id="sap_shipment_code" class="isPlanDisabled"></label>
		</div>
		<div class="warp_box">
			<label for="confirmed_quantity" class="wrap_one_single">计划确认数量<input type="text" id="confirmed_quantity" class="isPlanDisabled"></label>
			<label for="" class="wrap_one_single">
				预计交货时间
				<div class="input-group date date-picker margin-bottom-5 bw9" id="maskDate1">
					<input type="text" class="form-control form-filter input-sm maskDate1 isPlanDisabled" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
					<span class="input-group-btn">
						<button class="btn btn-sm default default_btn mask_default_btn estimated_delivery_date_btn" type="button">
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
		$('.keyword').val("");
		$('#createTimeInput').val("");
		let val = '';
		status_filter(val,0);
		status_filter(val,1);
		status_filter(val,2);
		status_filter(val,5);
		status_filter(val,7);
		let reqList = {
			"condition" : '',
			"date_s": '',
			"date_e": '',
		};
		tableObj.ajax.reload();
	}
	//批量审核
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
				url: "/shipment/upAllPurchase",
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
						$('#selectAll').removeAttr('checked');
					}
				},
				error: function(err) {
					console.log(err)
				}
			});
			
		}
	}
	$(document).ready(function(){
		//新建调拨计划时清空内容
		function clearValue(){
			$('.formId').val("");
			$('#audit_status_select').val(0);
			$('#sku_input').val("");
			$('#asin_select').val("");
			$('#sellersku_select').val("");
			$('#site_select').val("ATVPDKIKX0DER");
			$('#sap_factory_select').val("");
			$('#quantity_input').val("");
			$('.maskDate').val("");
			$("#remark_input").val("");
			$('#received_factory_input').val("");
			$('#sap_shipment_code').val("");
			$('#confirmed_quantity').val("");
			$('.maskDate1').val("");
		}
		//通过SKU或者站点获取asin
		function getAsinData(site,sku){
			$.ajax({
			    type: "POST",
				url: "/shipment/getNextData",
				data: {
					marketplace_id: site,
					sku: sku,
				},
				success: function (res) {
					//获取asin下拉列表
					$("#asin_select").empty();
					$("#asin_select").append("<option value=''>请选择</option>");
					$.each(res, function (index, value) {
						$("#asin_select").append("<option value='"+value.asin+"'>"+value.asin+"</option>");
					});
				},
				error : function(err) {
					console.log(err)
				}
			});
		}
		//当sku为空时，显示必填项，并且获取asin下拉列表
		$('#sku_input').on("input",function(){
			getAsinData($('#site_select').val(),$(this).val())
			if($(this).val() == ""){
				$(this).parent().find('.err').show();
			}else{
				$(this).parent().find('.err').hide();
			}
		})
		//当数量为空时，显示必填项
		$("#quantity_input").on('input',function(){
			if($(this).val() == ""){
				$(this).parent().find('.err').show();
			}else{
				$(this).parent().find('.err').hide();
			}
		})
		
		//当调入工厂为空时，显示必填项
		$("#sap_factory_select").on('change',function(){
			if($(this).val() == ""){
				$(this).parent().find('.err').show();
			}else{
				$(this).parent().find('.err').hide();
			}
		})
		//当站点不为空时，隐藏必填项提示
		$('#site_select').on("change",function(){
			if($('#sku_input').val() == ""){
				$('#sku_input').parent().find('.err').show();
				return
			}else{
				$('#sku_input').parent().find('.err').hide();
			}
			getAsinData($(this).val(),$('#sku_input').val())
		})
		//当到货时间不为空时，隐藏必填项提示
		$('.maskDate').on('change',function(){
			if($(this).val() == ""){
				$(this).parent().parent().find('.err').show();
			}else{
				$(this).parent().parent().find('.err').hide();
			}
		})
		//通过asin获取SellerSKU和调入工厂
		$('#asin_select').on('change',function(){
			$.ajax({
			    type: "POST",
				url: "/shipment/getNextData",
				data: {
					marketplace_id: $('#site_select').val(),
					asin: $(this).val(),
				},
				success: function (res) {
					//获取sellersku下拉列表
					$("#sellersku_select").empty();
					$("#sellersku_select").append("<option value=''>请选择</option>");
					$.each(res[0], function (index, value) {
						$("#sellersku_select").append("<option value='"+value.seller_sku+"'>"+value.seller_sku+"</option>");
					});
					//获取调入工厂下拉列表
					$("#sap_factory_select").empty();
					$("#sap_factory_select").append("<option value=''>请选择</option>");
					$.each(res[1], function (index, value) {
						$("#sap_factory_select").append("<option value='"+value.sap_factory_code+"'>"+value.sap_factory_code+"</option>");
					});
				},
				error : function(err) {
					console.log(err)
				}
			});
		})
		//采购计划保存
		$('.confirm').on('click',function(){
			console.log($('.formId').val())
			if($('#sku_input').val() == ''){
				$('#sku_input').parent().find('.err').show();
				return
			}
			if($('#site_select').val() == ''){
				$('#site_select').parent().find('.err').show();
				return
			}
			if($('#quantity_input').val() == ''){
				$('#quantity_input').parent().find('.err').show();
				return
			}
			if($('#sap_factory_select').val() == '' || $('#sap_factory_select').val() == null){
				$('#sap_factory_select').parent().find('.err').show();
				return
			}
			if($('.maskDate').val() == ''||$('.maskDate').val() == null){
				$('#maskDate').parent().find('.err').show();
				return
			}
			if($('.formId').val() == ""){
				$.ajax({
				    type: "POST",
					url: "/shipment/addPurchase",
					data: {
						audit_status: $('#audit_status_select').val(),
						sku: $('#sku_input').val(),
						asin: $('#asin_select').val(),
						seller_sku: $('#sellersku_select').val(),
						marketplace_id: $('#site_select').val(),
						sap_factory_code: $('#sap_factory_select').val(),
						quantity: $('#quantity_input').val(),
						request_date: $('.maskDate').val(),
						remark: $("#remark_input").val(),
						received_factory: $('#received_factory_input').val(),
						sap_shipment_code: $('#sap_shipment_code').val(),
						confirmed_quantity: $('#confirmed_quantity').val(),
						estimated_delivery_date: $('.maskDate1').val()
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
							clearValue();
						}
					},
					error: function(err) {
						console.log(err)
					}
				});
			}else{
				$.ajax({
				    type: "POST",
					url: "/shipment/upPurchase",
					data: {
						id: $('.formId').val(),
						audit_status: $('#audit_status_select').val(),//审核
						sku: $('#sku_input').val(),
						asin: $('#asin_select').val(),
						seller_sku: $('#sellersku_select').val(),
						marketplace_id: $("#site_select").val(), //站点
						sap_factory_code: $('#sap_factory_select').val(),//调入工厂
						quantity: $('#quantity_input').val(), //数量
						request_date: $('.maskDate').val(),// 到货时间
						remark: $('#remark_input').val(), // 备注
						received_factory: $('#received_factory_input').val(), //收货工厂
						sap_shipment_code: $('#sap_shipment_code').val(), //运输方式
						confirmed_quantity: $('#confirmed_quantity').val(), //计划确认数量
						estimated_delivery_date: $('.maskDate1').val() // 预计交货时间
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
							clearValue();
						}
					},
					error: function(err) {
						console.log(err)
					}
				});
			}
		})
		//全选
		$("#selectAll").on('change',function(e) {  
		    $("input[name='checkedInput']").prop("checked", this.checked);
			//let checkedBox = $("input[name='checkedInput']:checked");
		});  
		//单条选中
		$("body").on('change','.checkbox-item',function(e){
			var $subs = $("input[name='checkedInput']");
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 
			e.cancelBubble=true;
		});
		//新建采购计划
		$('#addPurchaseBtn').on('click',function(){
			clearValue();
			$('.mask_box').show();
			$('.isPlanDisabled').attr('disabled',false).css('background',"#fff");
			$('#audit_status_select').attr('disabled',true).css('background',"#eee");
			$('.isSellerDisabled').attr('disabled',false).css('background',"#fff");
			$('.estimated_delivery_date_btn').attr('disabled',false).css('background',"#fff");
			$('.request_date_btn').attr('disabled',false).css('background',"#fff");
		})
		//时间选择器
		$('.date-picker').datepicker({
			format: 'yyyy-mm-dd',
		    autoclose: true,
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
		//编辑列表
		function editTableData(id){
			$.ajax({
			    type: "POST",
				url: "/shipment/detailPurchase",
				data: {
					id: id
				},
				success: function (res) {
					//获取seller_sku下拉列表
					$("#sellersku_select").empty();
					$("#sellersku_select").append("<option value=''>请选择</option>");
					$.each(res.sellersku, function (index, value) {
						$("#sellersku_select").append("<option value='"+value.seller_sku+"'>"+value.seller_sku+"</option>");
					});
					//获取调入工厂下拉列表
					$("#sap_factory_select").empty();
					$("#sap_factory_select").append("<option value=''>请选择</option>");
					$.each(res.factory_code, function (index, value) {
						$("#sap_factory_select").append("<option value='"+value.sap_factory_code+"'>"+value.sap_factory_code+"</option>");
					});
					//获取ASIN下拉列表
					$("#asin_select").empty();
					$("#asin_select").append("<option value=''>请选择</option>");
					$.each(res.asinList, function (index, value) {
						$("#asin_select").append("<option value='"+value.asin+"'>"+value.asin+"</option>");
					});
					$('#site_select').val(res.detail.marketplace_id);//站点
					$('#audit_status_select').val(res.detail.audit_status);//审核
					$('#sku_input').val(res.detail.sku);//SKU
					$('#asin_select').val(res.detail.asin);//ASIN
					$('#sellersku_select').val(res.detail.seller_sku);//SellerSku
					$('#sap_factory_select').val(res.detail.sap_factory_code);//调入工厂
					$('#received_factory_input').val(res.detail.received_factory);//调出工厂
					$('#quantity_input').val(res.detail.quantity);//数量
					$('.maskDate').val(res.detail.request_date);//到货时间
					$('.maskDate1').val(res.detail.estimated_delivery_date);//预计到货时间
					$('#confirmed_quantity').val(res.detail.confirmed_quantity);//计划确认数量
					$('#remark_input').val(res.detail.remark);//备注
					$('#sap_shipment_code').val(res.detail.sap_shipment_code);//运输方式
					if(res.role == 1){
						$('.isSellerDisabled').attr('disabled',false).css('background',"#fff");
						$('.isPlanDisabled').attr('disabled',true).css('background',"#eee");
						$('#audit_status_select').attr('disabled',true).css('background',"#eee");
						$('.estimated_delivery_date_btn').attr('disabled',true).css('background',"#eee");
						$('.request_date_btn').attr('disabled',false).css('background',"#fff");
					}else if(res.role == 2){
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
					
				},
				error : function(err) {
					console.log(err)
				}
			});
		}
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		tableObj = $("#purchasetable").DataTable({
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			dispalyLength: 2, // default record count per page
			paging: true,  // 是否显示分页
			info: false,// 是否表格左下角显示的文字
			ordering: false,
			fixedColumns: { //固定列的配置项
				leftColumns: 4, //固定左边第一列
				rightColumns: 1, //固定左边第一列
			},
			serverSide: false,//是否所有的请求都请求服务器	
			scrollX: "100%",
			scrollCollapse: false,
			ajax: {
				url: "/shipment/purchaseList",
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
					$('.status5').text(res[1].status5);
					$("#seller_select").empty();
					$("#seller_select").append("<option value=''>全部</option>");
					$.each(res[2], function (index, value) {
						$("#seller_select").append("<option value='" + value + "'>" + value + "</option>");
					})
					return res[0];
				},
			},
			columns: [
				{data: 'ubg', name: 'ubg', visible: false,},
				{data: 'ubu', name: 'ubu', visible: false,},
				{data: 'domin_sx', name: 'domin_sx', visible: false,},
				{
					data: "id",
					name: 'id',
					render: function(data, type, row, meta) {
						var content = '<input type="checkbox" name="checkedInput"  class="checkbox-item" value="' + data + '" />';
						return content;
					},
				},
				{
					data: 'created_at',
					name: 'created_at',
					render: function(data, type, row, meta) {
						var content = '<div class="data_bg" style="width:75px">'+data+'</div>';
						return content;
					},
				},
				{data: 'name', name: 'name', },
				{
					data: 'image',
					name: 'image',
					render: function(data, type, row, meta) {
						var content = '<a href="https://'+row.toUrl+'/dp/'+ row.asin +'" target="_blank" style="text-decoration:none"><img src="https://images-na.ssl-images-amazon.com/images/I/'+data+'" alt="" style="display:block; width:60px; height:60px; margin:0 auto"></a>';
						return content;
					},
				},
				{
					data: 'asin',
					name: 'asin',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{
					data: 'sku',
					name: 'sku',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{
					data: 'quantity',
					name: 'quantity',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{
					data: 'request_date', 
					name: 'request_date',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{data: 'overseas_stock', name: 'overseas_stock',},
				{data: 'backlog_order', name: 'backlog_order',},
				{data: 'day_sales', name: 'day_sales',},
				{data: 'PCS', name: 'PCS',},
				{data: 'profit_margin', name: 'profit_margin',},
				{
					data: 'audit_status', 
					name: 'audit_status',
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
				{data: 'planning_name', name: 'planning_name'},
				{data: 'MOQ', name: 'MOQ'},
				{
					data: 'received_factory', 
					name: 'received_factory',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{
					data: 'sap_shipment_code', 
					name: 'sap_shipment_code',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{
					data: 'confirmed_quantity', 
					name: 'confirmed_quantity',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{
					data: 'estimated_delivery_date', 
					name: 'estimated_delivery_date',
					render: function(data, type, row, meta) {
					 	var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.id);
							editTableData(rowData.id)
							 
						});
					}
				},
				{data: 'order_number', name: 'order_number',},
				{data: 'complete_status', name: 'complete_status',},
			],
			data:[],
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