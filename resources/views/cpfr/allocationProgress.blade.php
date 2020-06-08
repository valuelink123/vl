@extends('layouts.layout')
@section('crumb')
    <a href="/cpfr/index">CPFR协同补货</a>
@endsection
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
	.table-scrollable > .table-bordered > thead > tr:last-child > th{
		text-align: center;
	}
	.warn_icon{
		position: relative;
		bottom: -4px;
		cursor: pointer;
	}
	#thetable_filter{
		display: none;
	}
	.batch_list{
		border: 1px solid rgba(220, 223, 230, 1);
		width: 180px;
		left: 0;
		padding: 15px 0 !important;
		display: none;
		position: absolute;
		z-index: 99;
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
		top: -10px;
		left: 30px;
		right: auto;
		display: inline-block !important;
		border-right: 10px solid transparent;
		border-bottom: 10px solid #fff;
		border-left: 10px solid transparent;
		content: '';
		box-sizing: border-box;
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
		z-index: 9999;
	}
	.error_mask .mask_text{
		color: #f56c6c !important;
	}
	.table-scrollable{
		overflow-x: hidden;
	}
	.table>thead:first-child>tr:first-child>th{
		text-align: center;
	}
	.table-stripeds>tbody>tr>td{
		padding:12px
	}
	.mask_box,.mask_file_upload{
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
		height: 740px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px 60px;
		margin-top: -370px;
		margin-left: -300px;
	}
	.mask_upload_dialog,.upload_file_from{
		width: 500px;
		height: 220px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px;
		margin-top: -110px;
		margin-left: -250px;
	}
	.upload_file_from{
		height: 500px;
		margin-top: -250px;
		overflow: auto;
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
	.wrap_one_single{
		position: relative;
		height: 70px;
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
	.cloumn_box{
		position: absolute;
		right: 0;
		z-index: 999;
		display: none;
		background: #fff;
		padding: 20px;
		border: 1px solid #eee;
		height: 365px;
		padding-right: 0;
	}
	.cloumn_list{
		height: 300px;
		overflow: auto;
		padding: 0;
		margin: 0;
	}
	.cloumn_list li{
		padding: 0;
		margin: 0;
		line-height: 25px;
		text-align: left;
		list-style: none;
	}
	.cloumn_list li input{
		margin-right: 10px;
	}
	.cloumn_box:after{
		position: absolute;
		top: -9px;
		right: 45px;
		display: inline-block !important;
		border-right: 10px solid transparent;
		border-bottom: 10px solid #fff;
		border-left: 7px solid transparent;
		content: '';
		box-sizing: border-box;
	}
	.file_adress{
		margin: 30px;
	}
	.titleHidden{
		display: inline-block;
		width: 350px;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}
</style>
<link rel="stylesheet" type="text/css" media="all" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>

<div>
	<ul class="nav_list">
		<li><a href="/cpfr/index">调拨计划</a></li>
		<li><a href="/cpfr/purchase">采购计划</a></li>
		<li class="nav_active"><a href="/cpfr/allocationProgress">调拨进度</a></li>
	</ul>
	<div class="button_box" style="overflow:hidden">
		<button id="downloadTemplate" style="float:right" class="btn sbold green-meadow"> 下载导入模板
			<i class="fa fa-download"></i>
		</button>
		
		<button id="export" style="float:right;margin:0 10px" class="btn sbold blue"> 导出
			<i class="fa fa-download"></i>
		</button>
		<div style="float: right;">
			<form action="/shipment/importExecl" style="display: inline-block;" method="POST" class="pull-right " enctype="multipart/form-data">
				{{ csrf_field() }}
				<input style="display: inline-block;" type="file" name="files" id="input" multiple=""> 
				<button type="submit" class="btn blue start">
				    <i class="fa fa-upload"></i>
				    <span>上传</span>
				</button>
			</form>
		</div>   
	</div>
	<div class="content">
		<div class="filter_box">
			<div class="filter_option">
				<label for="createTimes">日期</label>
				<div class="input-group input-medium" id="createTimes">
					<span class="input-group-btn">
						<button class="btn default date-range-toggle" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
					<input type="text" class="form-control createTimeInput" value="-" id="createTimeInput">  
				</div>
			</div>	
			<div class="filter_option">
				<label for="account_number">账号</label>
				<select id="account_number" onchange="status_filter(this.value,10)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="transfer_status">调拨状态</label>
				<select id="transfer_status" onchange="status_filter(this.value,5)">
					<option value ="">全部</option>
					<option id="0" value ="资料提供中">资料提供中</option>
					<option id="1" value ="换标中">换标中</option>
					<option id="2" value ="待出库">待出库</option>
					<option id="3" value ="已发货">已发货</option>
					<option id="4" value ="取消发货">取消发货</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="callout_factory">调出工厂</label>
				<select id="callout_factory" onchange="status_filter(this.value,8)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="callin_factory">调入工厂</label>
				<select id="callin_factory" onchange="status_filter(this.value,9)">
					<option value ="">全部</option>
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
				<button class="clear" onclick="handleClear()">清空筛选</button>
			</div>
		</div>
	</div>
	<div class="portlet light bordered">
	    <div style="margin-bottom: 15px"></div>
	    <div class="portlet-body">
	        <div class="table-container" style="position: relative;">
				<div style="width: 100%; height: 45px; line-height: 45px;">
					<div style="padding-left: 0;" class="col-md-3">
						<button type="button" class="btn btn-sm green-meadow batch_operation">批量操作<i class="fa fa-angle-down"></i></button>
						<ul class="batch_list">
							<li><button class="btn btn-sm red-sunglo noConfirmed" onclick="statusAjax(0)">资料提供中</button></li>
							<li><button class="btn btn-sm yellow-crusta" onclick="statusAjax(1)">换标中</button></li>
							<li><button class="btn btn-sm purple-plum" onclick="statusAjax(2)">待出库</button></li>
							<li><button class="btn btn-sm blue-hoki" onclick="statusAjax(3)">已发货</button></li>
							<li><button class="btn btn-sm blue-madison" onclick="statusAjax(4)">取消发货</button></li>
						</ul>
					</div>
					<div class="col-md-7">
						<button type="button" class="btn btn-sm red-sunglo" onclick="status_filter('资料提供中',5)">资料提供中 : <span class="status0"></span></button>
						<button type="button" class="btn btn-sm yellow-crusta" onclick="status_filter('换标中',5)">换标中 : <span class="status1"></span></button>
						<button type="button" class="btn btn-sm purple-plum" onclick="status_filter('待出库',5)">待出库 : <span class="status2"></span></button>
						<button type="button" class="btn btn-sm green-meadow" onclick="status_filter('已发货',5)">已发货 : <span class="status3"></span></button>
						<button type="button" class="btn btn-sm blue-madison" onclick="status_filter('取消发货',5)">取消发货 : <span class="status4"></span></button>
					</div>
					<div class="col-md-2" style="text-align: right;">
						<button type="button" class="btn btn-sm green-meadow cloumn">隐藏列操作</button>
						<div class="cloumn_box">
							<p style="padding: 0;margin: 0;line-height: 25px; text-align: left;"><input type="checkbox" class="checkboxAll" style="margin-right: 10px;" />是否全选</p>
							<ul class="cloumn_list">
								<li><input type="checkbox" />需求提交日期</li>
								<li><input type="checkbox" />调拨状态</li>
								<li><input type="checkbox" />销售员</li>
								<li><input type="checkbox" />发货批号</li>
								<li><input type="checkbox" />调出工厂</li>
								<li><input type="checkbox" />调入工厂</li>
								<li><input type="checkbox" />亚马逊账号</li>
								<li><input type="checkbox" />SKU</li>
								<li><input type="checkbox" />调拨数量</li>
								<li><input type="checkbox" />RMS标贴SKU</li>
								<li><input type="checkbox" />条码标签</li>
								<li><input type="checkbox" />发货方式</li>
								<li><input type="checkbox" />大货资料</li>
								<li><input type="checkbox" />Shippment ID</li>
								<li><input type="checkbox" />跟踪号/单据号</li>
								<li><input type="checkbox" />上次更新时间</li>
								<li><input type="checkbox" />装箱数据</li>
							</ul>
						</div>
					</div>
				</div>
				
	            <table class="table table-striped table-bordered" id="thetable" style="width:100%">
	                <thead>
	                <tr>
						<th>BG</th>
						<th>BU</th>
						<th>station</th>
	                    <th><input type="checkbox" id="selectAll" /></th>
	                    <th style="width:90px">需求提交日期</th>
	                    <th style="width:65px;min-width: 65px;">调拨状态</th>
	                    <th style="width:50px">销售员</th>
	                    <th>发货批号</th>
	                    <th style="width:65px">调出工厂</th>
						<th style="width:65px">调入工厂</th>
						<th>亚马逊账号</th>
	                    <th>SKU</th>
	                    <th style="width:55px">调拨数量</th>
	                    <th style="width:45px">RMS标贴SKU</th>
	                    <th style="width:30px">条码标签</th>
	                    <th style="width:95px">发货方式</th>
	                    <th style="width:35px">大货资料</th>
	                    <th style="width:25px">Shippment ID</th>
	                    <th style="width:60px">跟踪号/单据号</th>
	                    <th style="width:90px">上次更新时间</th>
	                    <th style="width:25px;min-width: 25px; font-size: 10px;">装箱数据</th>
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
			<h4 style="text-align: center; line-height: 38px;">调拨进度</h4>
			
			<div>
				<label for="audit_status_select" style="display: block;">审核</label>
				<select name="audit_status_select" disabled="disabled" id="audit_status_select" style="width:100%;height: 28px;margin-bottom: 20px;border: 1px solid rgba(220, 223, 230, 1);">
					<option value="0">BU经理审核</option>
					<option value="1">BG总经理审核</option>
					<option value="2">计划员审核</option>
					<option value="3">计划经理确认</option>
					<option value="4">已审批</option>
					<option value="5">取消调拨请求</option>
				</select>
			</div>
			
			<form  method="post" onsubmit="return false" action="##" id="formtest">
				<div class="mask-form">
					<div>
						<div class="wrap_one_single">
							<label for="asin_select">ASIN</label>
							<input type="text" id="asin_select" value="" disabled="disabled" class="">
						</div>
						<div class="wrap_one_single">
							<label for="sku_select">SKU</label>
							<input type="text" id="sku_select" disabled="disabled" class="">
						</div>
						<div class="wrap_one_single">
							<label for="quant_select">数量</label>
							<input type="text" id="quant_select" disabled="disabled" class="">
						</div>
						<div class="wrap_one_single">
							<label for="">
								到货时间
								<span title="期望到货时间，为预计FBA签收时间，需满足销售计划" class="mask_hover_svg">
									<svg t="1588835330500" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2629" width="13" height="13"><path d="M459.364486 360.47352h102.080997v-102.080997h-102.080997v102.080997z m0 408.323988h102.080997V462.554517h-102.080997v306.242991z m51.040498 255.202492c-280.722741 0-510.404984-229.682243-510.404984-510.404984S226.492212 3.190031 510.404984 3.190031s510.404984 229.682243 510.404985 510.404985-229.682243 510.404984-510.404985 510.404984z m0-918.728972C285.507788 105.271028 102.080997 288.697819 102.080997 513.595016S285.507788 921.919003 510.404984 921.919003s408.323988-183.426791 408.323988-408.323987C918.728972 288.697819 735.302181 105.271028 510.404984 105.271028z" p-id="2630" fill="#2c2c2c"></path></svg>
								</span>
							</label>
							<input type="text" id="maskDate" disabled="disabled" class="maskDate">
						</div>
						
					</div>
					<div>
						<label for="site_select">站点</label>
						<select name="site_select" id="site_select" disabled="disabled" class="isSellerDisabled">
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
							<input type="text" id="seller_sku_select" disabled="disabled" class="seller_sku_select">
						</div>
						<div class="wrap_one_single">
							<label for="warehouse_select">调入工厂</label>
							<input type="text" id="warehouse_select" disabled="disabled" class="warehouse_select">
						</div>
						
						
						<label for="rms_input">
							是否贴RMS标签
							<span title="是否需要贴RMS标贴，若是，则需要输入标贴物料号" class="mask_hover_svg">
								<svg t="1588835330500" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2629" width="13" height="13"><path d="M459.364486 360.47352h102.080997v-102.080997h-102.080997v102.080997z m0 408.323988h102.080997V462.554517h-102.080997v306.242991z m51.040498 255.202492c-280.722741 0-510.404984-229.682243-510.404984-510.404984S226.492212 3.190031 510.404984 3.190031s510.404984 229.682243 510.404985 510.404985-229.682243 510.404984-510.404985 510.404984z m0-918.728972C285.507788 105.271028 102.080997 288.697819 102.080997 513.595016S285.507788 921.919003 510.404984 921.919003s408.323988-183.426791 408.323988-408.323987C918.728972 288.697819 735.302181 105.271028 510.404984 105.271028z" p-id="2630" fill="#2c2c2c"></path></svg>
							</span>
						</label>
						<div>
							<select name="" disabled="disabled" id="rms_input" style="border: 1px solid rgba(220, 223, 230, 1); height: 28px;">
								<option value="1">是</option>
								<option value="0">否</option>
							</select>
							<input type="text" disabled="disabled" id="rms_sku_input" placeholder="是,请输入RMS_SKU" class="" style="border: 1px solid rgba(220, 223, 230, 1); height: 28px; width: 80%;">
						</div>
					</div>
				</div>
				<div style="border-bottom: 1px dashed rgba(220, 223, 230, 1);padding-bottom: 10px;">
					<label for="remarks_input" style="display: block;">调拨理由</label>
					<input disabled="disabled" type="text" id="remarks_input" class="" style="width: 100%;margin-bottom: 10px;height: 28px;border: 1px solid rgba(220, 223, 230, 1)">
				</div>
				<div class="mask-form" style="padding-top: 10px;">
					<div>
						
						<label for="out_warehouse_input">调出工厂</label><input type="text" class="" id="out_warehouse_input">
						<label for="adjustment_quantity_input">
							计划确认数量
							<span title="计划和物流确认后的实际可调拨数量" class="mask_hover_svg">
								<svg t="1588835330500" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2629" width="13" height="13"><path d="M459.364486 360.47352h102.080997v-102.080997h-102.080997v102.080997z m0 408.323988h102.080997V462.554517h-102.080997v306.242991z m51.040498 255.202492c-280.722741 0-510.404984-229.682243-510.404984-510.404984S226.492212 3.190031 510.404984 3.190031s510.404984 229.682243 510.404985 510.404985-229.682243 510.404984-510.404985 510.404984z m0-918.728972C285.507788 105.271028 102.080997 288.697819 102.080997 513.595016S285.507788 921.919003 510.404984 921.919003s408.323988-183.426791 408.323988-408.323987C918.728972 288.697819 735.302181 105.271028 510.404984 105.271028z" p-id="2630" fill="#2c2c2c"></path></svg>
							</span>
						</label><input type="text" class="" id="adjustment_quantity_input">
					</div>
					<div>
						<label for="arrivalMaskDate">
							预计到货时间
							<span title="计划和物流确认过的预计到货时间" class="mask_hover_svg">
								<svg t="1588835330500" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2629" width="13" height="13"><path d="M459.364486 360.47352h102.080997v-102.080997h-102.080997v102.080997z m0 408.323988h102.080997V462.554517h-102.080997v306.242991z m51.040498 255.202492c-280.722741 0-510.404984-229.682243-510.404984-510.404984S226.492212 3.190031 510.404984 3.190031s510.404984 229.682243 510.404985 510.404985-229.682243 510.404984-510.404985 510.404984z m0-918.728972C285.507788 105.271028 102.080997 288.697819 102.080997 513.595016S285.507788 921.919003 510.404984 921.919003s408.323988-183.426791 408.323988-408.323987C918.728972 288.697819 735.302181 105.271028 510.404984 105.271028z" p-id="2630" fill="#2c2c2c"></path></svg>
							</span>
						</label>
						<div class="input-group date date-picker margin-bottom-5 bw9" id="arrivalMaskDate">
							<input type="text" class="form-control form-filter input-sm arrivalMaskDate" style="height: 28px;" readonly name="date_from" placeholder="From" value="">
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
<div class="mask_file_upload">
			<div class="upload_file_from">
				<svg t="1588919283810"class="icon cancel_upload_btn cancel_file" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
				<div style="overflow: auto; height: 395px;">
					<div class="file_adress"></div>
					<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
					<form id="fileupload" action="{{ url('send') }}" method="POST" enctype="multipart/form-data">
					    {{ csrf_field() }}
						<input type="hidden" name="warn" id="warn" value="0">
					    <input type="hidden" name="inbox_id" id="inbox_id" value="0">
					   <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
										
					    <div style="margin-top: 20px;">
					        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
					        <div class="fileupload-buttonbar">
					            <div class="col-lg-12" style="text-align: center;">
					                <!-- The fileinput-button span is used to style the file input field as button -->
					                <span class="btn green fileinput-button">
										<i class="fa fa-plus"></i>
										<span>添加文件</span>
										<input type="file" name="files[]" multiple=""> 
									</span>
					                <button type="submit" class="btn blue start">
					                    <i class="fa fa-upload"></i>
					                    <span>开始上传</span>
					                </button>
					                <button type="reset" class="btn warning cancel">
					                    <i class="fa fa-ban-circle"></i>
					                    <span>取消上传 </span>
					                </button>
					
					                <button type="button" class="btn red delete">
					                    <i class="fa fa-trash"></i>
					                    <span>删除</span>
					                </button>
					               <!-- <input type="checkbox" class="toggle"> -->
					                <!-- The global file processing state -->
					                <span class="fileupload-process"> </span>
					            </div>
					            <!-- The global progress information -->
					            <div class="col-lg-12 fileupload-progress fade">
					                <!-- The global progress bar -->
					                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
					                    <div class="progress-bar progress-bar-success" style="width:0%;"> </div>
					                </div>
					                <!-- The extended global progress information -->
					                <div class="progress-extended"> &nbsp; </div>
					            </div>
					        </div>
					        <!-- The table listing the files available for upload/download -->
					        <table role="presentation" class="table table-striped clearfix" id="table-striped" style="margin-bottom: 0;">
					            <tbody class="files" id="filesTable"> </tbody>
					        </table>
					        <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
					            <div class="slides"> </div>
					            <h3 class="title"></h3>
					            <a class="prev"> ‹ </a>
					            <a class="next"> › </a>
					            <a class="close white"> </a>
					            <a class="play-pause"> </a>
					            <ol class="indicator"> </ol>
					        </div>
					        <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
					        <script id="template-upload" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
					        <tr class="template-upload fade">
					            <td>
					                <p class="name" style="margin: 0;">{%=file.name%}</p>
					                <strong class="error text-danger label label-danger" style="padding: 0 6px;"></strong>
					            </td>
					            <!-- <td>
					                <p class="size">Processing...</p>
					                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
					                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
					                </div>
					            </td> -->
					            <td> {% if (!i && !o.options.autoUpload) { %}
					                <button class="btn blue start" disabled>
					                    <i class="fa fa-upload"></i>
					                    <span>开始</span>
					                </button> {% } %} {% if (!i) { %}
					                <button class="btn red cancel">
					                    <i class="fa fa-ban"></i>
					                    <span>取消</span>
					                </button> {% } %} </td>
					        </tr> {% } %} </script>
					        <!-- The template to display files available for download -->
					        <script id="template-download" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
					        <tr class="template-download fade">
					            <td>
					                <p class="name" style="margin: 0;"> {% if (file.url) { %}
					                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl? 'data-gallery': ''%}>{%=file.name%}</a> {% } else { %}
					                    <span>{%=file.name%}</span> {% } %}
					                    {% if (file.name) { %}
					                        <input type="hidden" name="fileid[]" class="filesUrl" value="{%=file.url%}">
					                    {% } %}
					
					                    </p> {% if (file.error) { %}
					                <div>
					                    <span class="label label-danger">Error</span> {%=file.error%}</div> {% } %} </td>
					            <!-- <td>
					                <span class="size">{%=o.formatFileSize(file.size)%}</span>
					            </td> -->
					            <td> {% if (file.deleteUrl) { %}
					                <button class="btn red delete btn-sm" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}" {% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}' {% } %}>
					                    <i class="fa fa-trash-o"></i>
					                    <span>删除</span>
					                </button>
					                <!-- <input type="checkbox" name="delete" value="1" class="toggle"> --> {% } else { %}
					                <button class="btn yellow cancel btn-sm">
					                    <i class="fa fa-ban"></i>
					                    <span>取消</span>
					                </button> {% } %} </td>
					        </tr> {% } %} </script>
					        <div style="clear:both;"></div>
					    </div>
					</form>	
					
				</div>
				<div style="text-align: center;">
					<input type="hidden" class="fileId">
					<button class="btn warning cancel cancelFile" style="width: 80px;border: 1px solid #ccc;">取消</button>
					<button class="btn blue start" id="fileUpload">确认上传</button>
				</div>
			</div>
		</div>
<script type="text/template" id="sub-table-tpl">
        <table class="table">
            <thead>
            <tr>
                <th>宽<div>(IN)</div></th>
                <th>高<div>(IN)</div></div></th>
                <th>运输方式<div>transportation</div></div></th>
                <th>卡板号<div>pallets</div></th>
                <th>打板尺寸<div>(in)</div><div>pallets size</div></th>
            </tr>
            </thead>
            <tbody>
            <% for(let row of rows){ %>
            <tr>
                <td>${row.width}</td>
                <td>${row.height}</td>
                <td>${row.transportation}</td>
                <td>${row.pallets}</td>
                <td>${row.pallets_size}</td>
            </tr>
            <% } %>
            </tbody>
        </table>
    </script>
<script>
	let msg = <?php echo @$msg?$msg:0?>;
	console.log(msg)
	/* if(msg == 0){
		$('.error_mask').fadeIn(1000);
		$('.error_mask_text').text(msg);
		setTimeout(function(){
			$('.error_mask').fadeOut(1000);
		},2000)
	}else if(msg == 1){
		$('.success_mask').fadeIn(1000);
		$('.success_mask_text').text(msg);
		setTimeout(function(){
			$('.success_mask').fadeOut(1000);
		},2000)	
		tableObj.ajax.reload();
		$('#selectAll').removeAttr('checked');
	} */
	function tplCompile(tpl) {
	
	    tpl = tpl.replace(/<%([^]+?)%>/g, "`);$1;_push(`")
	
	    return new Function(
	        'vars',
	        `let _output = []
	         let _push = _output.push.bind(_output)
	         eval(\`var {\${Object.keys(vars).join(",")}} = vars\`)
	         _push(\`${tpl}\`)
	         return _output.join("")`
	    )
	}
	
	function tplRender(selector, vars) {
	
	    if (!(selector instanceof Element)) {
	        selector = document.querySelector(selector)
	        if (!selector) return ''
	    }
	
	    if (!selector._compile) {
	        selector._compile = tplCompile(selector.innerHTML)
	    }
	
	    return selector._compile(vars)
	}
	//清空筛选
	function handleClear(){
		$("#createTimes input").val(''+ " - " + '');
		$('#account_number').val("");
		$('#transfer_status').val("");
		$('#callout_factory').val("");
		$('#callin_factory').val("");
		$("#marketplace_select").val("");
		$("#bg_select").val("");
		$("#bu_select").val("");
		$("#seller_select").val("");
		$('.keyword').val("");
		let val = '';
		status_filter(val,0);
		status_filter(val,1);
		status_filter(val,2);
		status_filter(val,5);
		status_filter(val,6);
		status_filter(val,8);
		status_filter(val,9);
		status_filter(val,10);
		let reqList = {
			"condition" : '',
			"date_s": '',
			"date_e": '',
			"downLoad": '',
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
				url: "/shipment/upAllAllot",
				data: {
					status: status,
					shipment_requests_id_list: chk_value
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
	//筛选
	function status_filter(value,column) {
	    if (value == '') {
	        tableObj.column(column).search('').draw();
	    }
	    else tableObj.column(column).search(value).draw();
	}
	$(document).ready(function(){
		
		//上传大货资料
		$('#fileUpload').on('click',function(){
			let fileList1 = '';
			let fileLists = " ";
			let str1 = $('.file_adress div').find('.button')
			for(var i=0;i<str1.length;i++){
				if(fileList1 != ''){
					fileList1 = fileList1 + ',' + str1[i].href;
				}else{
					fileList1 = fileList1 + str1[i].href;
				}
			}
			let fileList = '';
			let str = $('.table-striped tbody tr td').find('.filesUrl');
			for(var i=0;i<str.length;i++){
				if(fileList != ''){
					fileList = fileList + ',' + str[i].defaultValue;
				}else{
					fileList = fileList + str[i].defaultValue;
				}
			}
			if(fileList1 != "" && fileList != ""){
				fileLists = fileList1 + ',' + fileList
			}else if(fileList1 != "" && fileList == ""){
				fileLists = fileList1
			}else if(fileList1 == "" && fileList != ""){
				fileLists = fileList
			}
			$.ajax({
			    type: "POST",
				url: "/shipment/upCargoData",
				data: {
					id: $('.fileId').val(),
					cargo_data: decodeURI(fileLists)
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
					}
					$('.mask_file_upload').hide()
				},
				error: function(err) {
					console.log(err)
				}
			});
		});
		//批量操作列表展开
		$('.cloumn').click(function(e){
			$('.cloumn_box').slideToggle();
			e.stopPropagation();
		})
		$(".cloumn_list").children("li").each(function(index,element){
			$(this).find('input').click(function(){
				let id = $(this).parent().index() + 4;
				if($(this).is(':checked')){
					tableObj.column(id).visible(false)
				}else{
					tableObj.column(id).visible(true)
				}
			})
		})
		$('.checkboxAll').on('click',function(){
			if($(this).is(':checked')){
				$(".cloumn_list").find('input').prop('checked',true);
				for(var i=4; i<21; i++){
					tableObj.column(i).visible(false)
				}
			}else{
				$(".cloumn_list").find('input').prop('checked',false)
				for(var i=4; i<21; i++){
					tableObj.column(i).visible(true)
				}
			}
		})
		//上传大货资料弹窗隐藏
		$('.cancel_file').on('click',function(){
			$('.mask_file_upload').hide();
		})
		$('.cancelFile').on('click',function(){
			$('.mask_file_upload').hide();
		})
		
		
		//下载导入模板
		$('#downloadTemplate').on('click',function(){
			let chk_value = '';
			$("input[name='checkedInput']:checked").each(function (index,value) {
				if(chk_value != ''){
					chk_value = chk_value + ',' + $(this).attr('shipment_requests_id')	
				}else{
					chk_value = chk_value + $(this).attr('shipment_requests_id')	
				}
			});
			$.ajax({
			    type: "POST",
				url: "/shipment/allotProgress",
				data: {
					downLoad: 1,
					date_s: cusstr($('.createTimeInput').val() , ' - ' , 1),
					date_e: cusstr1($('.createTimeInput').val() , ' - ' , 1),
					label: $('#account_number').val(),
					bg: $('#bg_select').val(),
					bu: $('#bu_select').val(),
					name: $('#seller_select').val(),
					status: $('#transfer_status').find("option:selected").attr("id"),
					out_warehouse: $('#callout_factory').val(),
					marketplace_id: $('#marketplace_select').val(),
					sap_factory_code: $('#callin_factory').val(),
					shipment_id_list: chk_value,
				},
				success: function (data) {
					if(data != ""){
						var fileName = "导入模板";
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
						 $('#selectAll').prop('checked',false);
						 $("input[name='checkedInput']:checked").prop('checked',false);
					}
				},
				error: function(err) {
					console.log(err)
				}
			});
			
		})
		$('.batch_operation').click(function(e){
			$('.batch_list').slideToggle();
			$(document).one('click',function(){
				$('.batch_list').hide();
			})
			e.stopPropagation();
		})
		//编辑保存
		$('.updateConfirm').on('click',function(){
			$.ajax({
			    type: "POST",
				url: "/shipment/upShipment2",
				data: {
					id: $('.formId').val(),
					out_warehouse: $('#out_warehouse_input').val(),  
					adjustreceived_date: $('.arrivalMaskDate').val(),
					adjustment_quantity: $('#adjustment_quantity_input').val(),
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
		})
		//待计划确认
		$('.noConfirmed').on('click',function(){
			let chk_value = '';
			$("input[name='checkedInput']:checked").each(function () {
				if(chk_value != ''){
					chk_value = chk_value + ',' + $(this).val()	
				}else{
					chk_value = chk_value + $(this).val()
				}				 		 			
			});
			chk_value == ""? chk_value = -1 : chk_value;
			tableObj.ajax.reload();
		})
		//导出调拨进度
		$('#export').click(function(){
			 let chk_value = '';
			 $("input[name='checkedInput']:checked").each(function (index,value) {
			 	if(chk_value != ''){
			 		chk_value = chk_value + ',' + $(this).attr('shipment_requests_id')	
			 	}else{
			 		chk_value = chk_value + $(this).attr('shipment_requests_id')	
			 	}
			 });
			 $.ajax({
				url: "/shipment/exportExecl",
				 method: 'POST',
				 cache: false,
				 data: {
					date_s: cusstr($('.createTimeInput').val() , ' - ' , 1),
					date_e: cusstr1($('.createTimeInput').val() , ' - ' , 1),
					label: $('#account_number').val(),
					bg: $('#bg_select').val(),
					bu: $('#bu_select').val(),
					name: $('#seller_select').val(),
					status: $('#transfer_status').find("option:selected").attr("id"),
					out_warehouse: $('#callout_factory').val(),
					marketplace_id: $('#marketplace_select').val(),
					sap_factory_code: $('#callin_factory').val(),
					shipment_id_list: chk_value,
				 },
							
				 success: function (data) {
					 if(data != ""){
						var fileName = "调拨进度";
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
						$('#selectAll').removeAttr('checked');
						$("input[name='checkedInput']:checked").removeAttr('checked');
					 }
				 } 
			 });
				 
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
		//搜索
		$('.search').on('click',function(){
			let reqList = {
				"condition" : $('.keyword').val(),
				"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
				"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
				"downLoad": ""
			};
			tableObj.ajax.reload();
		})
		$('.keyword').on('input',function(){
			let reqList = {
				"condition" : $('.keyword').val(),
				"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
				"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
				"downLoad": ""
			};
			tableObj.ajax.reload();
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
		//关闭弹窗
		$('.cancel_btn').on('click',function(){
			$('.mask_box').hide();
		})
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		tableObj = $("#thetable").DataTable({
			serverSide: false,
			processing: false,
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			scrollX: "100%",
			scrollCollapse: false,
			/* fixedColumns: { //固定列的配置项
				leftColumns: 4, //固定左边第一列
				rightColumns: 1, //固定左边第一列
			}, */
			pageLength: 20,
			dispalyLength: 2, // default record count per page
			order: [ 1, "desc" ],
			ajax: {
				type: 'POST',
				url: '/shipment/allotProgress',
				data :  function(){
					reqList = {
						"condition" : $('.keyword').val(),
						"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
						"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
						"downLoad": ""
					};
					return reqList;
				},
				dataSrc:function(res){
					//进行预查询,改变按钮颜色
					for (let row of res[0]) {
					    let shipment_requests_id = row.shipment_requests_id
					    // 根据每一行 shipment_requests_id 进行预查询，如果有配件数据，则将加号按钮变绿
					    $.post('/shipment/getBoxDetail', {shipment_requests_id}).success(rows => {
					        if (rows.length > 0) {
					            if (false === rows[0]) return
					            $(`#thetable .ctrl-${shipment_requests_id}`).parent().removeClass('disabled')
					        }
					    })
					}
					
					$('.status0').text(res[4].status0);
					$('.status1').text(res[4].status1);
					$('.status2').text(res[4].status2);
					$('.status3').text(res[4].status3);
					$('.status4').text(res[4].status4);
					$('.status5').text(res[4].status5);
					$("#seller_select").empty();
					$("#seller_select").append("<option value=''>全部</option>");
					$.each(res[1], function (index, value) {
						$("#seller_select").append("<option value='" + value + "'>" + value + "</option>");
					});
					$("#account_number").empty();
					$("#account_number").append("<option value=''>全部</option>");
					$.each(res[2], function (index, value) {
						$("#account_number").append("<option value='" + value + "'>" + value + "</option>");
					});
					$("#callout_factory").empty();
					$("#callout_factory").append("<option value=''>全部</option>");
					$.each(res[3], function (index, value) {
						$("#callout_factory").append("<option value='" + value.sap_factory_code + "'>" + value.sap_factory_code+ "</option>");
					});
					$("#callin_factory").empty();
					$("#callin_factory").append("<option value=''>全部</option>");
					$.each(res[3], function (index, value) {
						$("#callin_factory").append("<option value='" + value.sap_factory_code + "'>" + value.sap_factory_code + "</option>");
					});
					return res[0];
				}
			},
			columns: [
				{data: 'ubg', name: 'ubg', visible: false,},
				{data: 'ubu', name: 'ubu', visible: false,},
				{data: 'domin_sx', name: 'domin_sx', visible: false,},
				{
					data: "id",
					name: 'id',
					render: function(data, type, row, meta) {
						var content = '<input type="checkbox" name="checkedInput" shipment_requests_id="'+ row.shipment_requests_id +'"  class="checkbox-item" value="' + data + '" />';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						
					}
				},
				{
					data: 'created_at', 
					name: 'created_at',
					render: function(data, type, row, meta) {
						var content = '<div class="data_bg">'+data+'<span><svg t="1589536384161" class="icon warn_icon" viewBox="0 0 1107 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2119" width="20" height="20"><path d="M581.34438559 757.66109686c0-12.54264615-6.68255768-24.16001577-17.58026623-30.43133844-10.89770938-6.27132349-24.26282473-6.27132349-35.05772516 0-10.89770938 6.27132349-17.58026707 17.88869229-17.58026707 30.43133844 0 19.32801279 15.72971238 35.05772516 35.05772516 35.05772517 19.53362989 0 35.1605333-15.72971238 35.1605333-35.05772517M511.22893527 655.57217947V368.53062949c0-17.68307519 15.72971238-31.97346789 35.05772516-31.97346789s35.05772516 14.2903927 35.05772516 31.97346789V655.57217947c0 17.68307519-15.72971238 31.97346789-35.05772516 31.97346789-19.32801279-0.10280896-35.05772516-14.2903927-35.05772515-31.97346789" fill="#d81e06" p-id="2120"></path><path d="M983.83996915 771.74587246L637.88910346 154.17474392C615.37402527 113.97658972 581.85842874 90.94746838 546.08104333 90.94746838s-69.29298193 23.13193029-91.70525115 63.3300845L108.73335254 771.6430635c-22.41227004 39.99253711-24.67405893 80.8075426-6.16851452 112.16415921 18.40273545 31.3566166 55.20820633 49.34811786 101.266449 49.34811704H888.94765176c45.85262558 0 82.86371357-17.88869229 101.26644901-49.2453089 18.40273545-31.3566166 16.03813843-72.1716221-6.37413162-112.16415839z m-55.00258924 73.40532468c-6.37413162 11.41175254-22.61788714 17.78588416-44.618923 17.78588416H208.35486478c-21.8982269 0-38.14198242-6.47694058-44.51611405-17.78588416-6.37413162-11.41175254-3.90672564-29.40325296 6.78536582-49.45092599L511.64017029 192.21391821c10.38366623-19.53362989 23.23473843-31.15099951 34.44087304-31.15099951 11.20613462 0 24.05720764 11.61736963 34.54368284 31.04819055l341.42728711 603.5891619c10.69209229 20.04767303 13.15949827 38.03917429 6.78536663 49.45092599z" fill="#d81e06" p-id="2121"></path></svg></span></div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						if(rowData.isCancel == true){
							$(cell).find('.warn_icon').show().parent().attr('title','调拨需求被'+rowData.name+'取消'+rowData.cancelDate+'');
						}else{
							$(cell).find('.warn_icon').hide();
						}
					}
				},
				{
					data: 'status', 
					name: 'status',
					render: function(data, type, row, meta) {
					 	if(data == 0){ data = '资料提供中' }
					 	else if(data == 1){ data = '换标中' }
					 	else if(data == 2){ data = '待出库' }
					 	else if(data == 3){ data = '已发货' }
					 	else if(data == 4){ data = '取消发货' }
					 	var content = '<div>'+data+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						var aInput;
						$(cell).click(function () {
							$(this).html(
									'<select style="width:100%;" placeholder="请选择发货方式" id="editorStatusSelect">'
									+'<option value="资料提供中" status="0">资料提供中</option>'
									+'<option value="换标中" status="1">换标中</option>'
									+'<option value="待出库" status="2">待出库</option>'
									+'<option value="已发货" status="3">已发货</option>'
									+'<option value="取消发货" status="4">取消发货</option>'
									+'</select>'
								
								);
							var aInput = $(this).find(":input");
							aInput.focus().val("");
						});
						$(cell).on("click", ":input", function (e) {
							e.stopPropagation();
						});
						$(cell).on("change", ":input", function () {
							$(this).blur();
						});
						$(cell).on("blur", ":input", function () {
							$.ajax({
								type: "POST",
								url: "/shipment/upAllAllot",
								data: {
									shipment_requests_id_list: rowData.shipment_requests_id,
									status: $(this).find("option:selected").attr("status"),
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
										$(cell).html($("#editorStatusSelect").val()+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img">');
									}
								}
							});
							
						});
					}
				},
				{data: 'name', name: 'name',},
				{data: 'batch_num', name: 'batch_num',},
				{
					data: 'out_warehouse', 
					name: 'out_warehouse',
					render: function(data, type, row, meta) {
						var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.shipment_requests_id);
							editTableData(rowData.shipment_requests_id);
						});
					}
				},
				/* {data: 'amz_account', name: 'amz_account'}, */
				
				{
					data: 'sap_factory_code', 
					name: 'sap_factory_code', 
					render: function(data, type, row, meta) {
						var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.shipment_requests_id);
							editTableData(rowData.shipment_requests_id);
						});
					}
				},
				{
					data: 'label', 
					name: 'label',
					render: function(data, type, row, meta) {
						var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.shipment_requests_id);
							editTableData(rowData.shipment_requests_id);
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
							$('.formId').val(rowData.shipment_requests_id);
							editTableData(rowData.shipment_requests_id);
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
							$('.formId').val(rowData.shipment_requests_id);
							editTableData(rowData.shipment_requests_id);
						});
					}
				},
				{
					data: 'rms_sku',
					name: 'rms_sku',
					render: function(data, type, row, meta) {
						var content = '<div style="color:blue;cursor:pointer">'+data +'</div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							$('.mask_box').show();
							$('.formId').val(rowData.shipment_requests_id);
							editTableData(rowData.shipment_requests_id);
						});
					}
				},
				{
					data: 'method',
					name: 'method',
					render: function(data, type, row, meta) {
						var content = '<button><a target="_blank" href="barcode?id='+row.shipment_requests_id+'">打印</a></button>';
						return content;
					},
				},
				{
					data: 'shipping_method',
					name: 'shipping_method',
					render: function(data, type, row, meta) {
					 	if(data == 0){ data = '资料提供中' }
					 	else if(data == 1){ data = '换标中' }
					 	else if(data == 2){ data = '待出库' }
					 	else if(data == 3){ data = '已发货' }
					 	else if(data == 4){ data = '取消发货' }
					 	var content = '<div>'+data+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
					 	return content;
					},
				},
				{
					data: 'cargo_data', 
					name: 'cargo_data',
					render: function(data, type, row, meta) {
						var content = '<button>查看</button>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function () {
							$('.mask_file_upload').show();
							$('.fileId').val(rowData.shipment_requests_id)
							$('.table-striped #filesTable').html("");
							$.ajax({
								type:"post",
								url:'/shipment/getCargoData',
								data:{
									shipment_requests_id: rowData.shipment_requests_id,
								},
								error:function(err){
								    console.log(err);
								},
								success:function(res){
									$('.file_adress').html("")
									let fileAddress1="" ,fileAddress2="";
									for(var i=0;i<res.length;i++){
										let reg = /\.(png|jpg|gif|jpeg|webp|pdf)$/;
										if(reg.test(res[i].url)){
											fileAddress1 += '<div><a class="titleHidden" href="' + res[i].url + '" target="_blank">' + res[i].title + '</a><a style="float:right" href="' + res[i].url + '" class="button" download="' + res[i].title + '">下载</a></div>';
										}else{
											fileAddress2 += '<div><span class="titleHidden">' + res[i].title + '</span><a style="float:right" href="' + res[i].url + '" download="' + res[i].title + '" class="button">下载</a></div>';
										}
									}
									$('.file_adress').append(fileAddress1 + fileAddress2 );
								}
							});
						});
					}
				},
				{
					data: 'shippment_id', 
					name: 'shippment_id',
					render: function(data, type, row, meta) {
						var content = '<div>'+data+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						let inputTxt = cellData
						$(cell).click(function (e) {
							$(this).html('<input type="text" size="16" style="width: 100%" />');
							var aInput = $(this).find(":input");
							aInput.focus().val(inputTxt);
						});
						$(cell).on("blur", ":input", function (e) {
							var text = $(this).val();
							if($(this).val() != inputTxt){
								//$(cell).html(text);
								tableObj.cell(cell).data(text);
								$.ajax({
									type:"post",
									url:'/shipment/upShippmentID',
									data:{
										id: rowData.id,
					                    shippment_id: rowData.shippment_id 
									},
									error:function(err){
									    console.log(err);
									},
									success:function(res){
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
											$(cell).html(rowData.shippment_id +'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img">');
										}
										inputTxt = rowData.shippment_id
									}
								});
							}else{
								$(cell).html(text);
								tableObj.cell(cell).data(text);
							}
						})
					}
				},
				{
					data: 'receipts_num', 
					name: 'receipts_num',
					render: function(data, type, row, meta) {
						var content = '<div>'+data+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						let inputTxt = cellData
						$(cell).click(function (e) {
							$(this).html('<input type="text" size="16" style="width: 100%" />');
							var aInput = $(this).find(":input");
							aInput.focus().val(inputTxt);
						});
						$(cell).on("blur", ":input", function (e) {
							var text = $(this).val();
							if($(this).val() != inputTxt){
								$(cell).html(text);
								tableObj.cell(cell).data(text);
								$.ajax({
									type:"post",
									url:'/shipment/upReceiptsNum',
									data:{
										id: rowData.id,
					                    receipts_num: rowData.receipts_num
									},
									error:function(err){
									    console.log(err);
									},
									success:function(res){
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
											$(cell).html(rowData.receipts_num +'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img">');
										}
										
										inputTxt = rowData.receipts_num
									}
								});
							}else{
								$(cell).html(text);
								tableObj.cell(cell).data(text);
							}
						})
					}
				},
				{data: 'updated_at', name: 'updated_at'},
				{
					"className": 'details-control disabled',
					"orderable": false,
					"data": 'shipment_requests_id',
					render(shipment_requests_id) {
					    return `<a class="ctrl-${shipment_requests_id}"></a>`
					}
				}
			],
			data:[],
			
			columnDefs: [
				{ "bSortable": false, "aTargets": [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]},
				{
					targets: [15],
					render: function(data, type, row, meta) {
						var content = '<div class="editorMethods">'+data+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						var aInput;
						$(cell).click(function () {
							$(this).html(
									'<select style="width:100%;" placeholder="请选择发货方式" id="shippingMethodSelect">'
									+'<option value="亚马逊卡派">亚马逊卡派</option>'
									+'<option value="亚马逊快递">亚马逊快递</option>'
									+'<option value="卡派-仓库直发">卡派-仓库直发</option>'
									+'<option value="快递-仓库直发">快递-仓库直发</option>'
									+'</select>'
								
								);
							var aInput = $(this).find(":input");
							aInput.focus().val("");
						});
						$(cell).on("click", ":input", function (e) {
							e.stopPropagation();
						});
						$(cell).on("change", ":input", function () {
							$(this).blur();
						});
						$(cell).on("blur", ":input", function () {
							$.ajax({
								type: "POST",
								url: "/shipment/upShippingMethod",
								data: {
									id: rowData.id,
									shippingMethod: $(this).val()
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
										$(cell).html($("#shippingMethodSelect").val()+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img">');
									}
								}
							});
							
						});
					}
				}
			],
			
		})
		
		async function buildSubItemTable(shipment_requests_id) {
		
		    let rows = await new Promise((resolve, reject) => {
		        $.post('/shipment/getBoxDetail', {shipment_requests_id})
		            .success(rows => resolve(rows))
		            .error((xhr, status, errmsg) => reject(new Error(errmsg)))
		    })
		
		    if (!rows.length) return ''
		
		    if (false === rows[0]) return Promise.reject(new Error(rows[1]))
		
		    return tplRender('#sub-table-tpl', {rows})
		}
		
		tableObj.on('click', 'td.details-control', function () {
		
		    let $td = $(this)
		
		    let row = tableObj.row($td.closest('tr'));
		
		    if (row.child.isShown()) {
		        row.child.remove();
		        $td.removeClass('closed');
		    } else {
		        let {shipment_requests_id} = row.data()
		        let id = `sub-item-loading-${shipment_requests_id}`
		
		        row.child(`<div id="${id}" style="padding:3em;">Data is Loading...</div>`, 'sub-item-row').show()
		
		        buildSubItemTable(shipment_requests_id).then(html => {
		            if (html) {
		                $td.removeClass('disabled')
		                $(`#${id}`).parent().html(html)
		            } else {
		                $(`#${id}`).html('Nothing to Show.')
		            }
		        }).catch(err => {
		            $(`#${id}`).html(`<span style="color:red">Server Error: ${err.message}</span>`)
		        })
		
		        $td.addClass('closed');
		    }
		});
		
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
		//日期初始化
		$("#createTimes").daterangepicker({
			opens: 'left',
			format: "YYYY-MM-DD",
			autoUpdateInput: true,
			timePicker: true,
			separator: " to ",
			showISOWeekNumbers: false,
			autoApply: false,
			showDropdowns: false,
			showWeekNumbers: false,
			alwaysShowCalendars: true,
			linkedCalendars: true,
			showCustomRangeLabel: true,
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
		}, function (start,end) {
				var s = start.format('YYYY-MM-DD');
				var e = end.format('YYYY-MM-DD');
				var t = s + ' 至 ' + e;
				if (start._isValid == false && end._isValid == false) {
					s = "";
					e = "";
					t ="请选择日期范围"
					$("#createTimes input").val(''+ " - " + '');
				}else{
					$("#createTimes input").val(s + " - " + e);
				}
			let reqList = {
				"condition" : $('.keyword').val(),
				"date_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
				"date_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
				"downLoad": ""
			};
			tableObj.ajax.reload();
		})
		//时间选择器
		$('.date-picker').datepicker({
			format: 'yyyy-mm-dd',
		    autoclose: true,
			//datesDisabled : new Date(),
			
		});
	})
</script>
@endsection