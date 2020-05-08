@extends('layouts.layout')
@section('label', 'Marketing Plan')
@section('content')

<style>
	.table-scrollable{
		overflow: hidden;
	}
	.btn.default:not(.btn-outline){
		background: #fff;
	}
	.page-container-bg-solid .page-content{
		background: #eaeff8;
	}
	
	.createPlan{
		float: right !important;
	}
	.createPlan > .create_btn{
		border: none;
		padding: 5px 20px;
		background: #fff;
		border-radius: 5px !important;
		cursor: pointer;
		
	}
	.content{
		padding: 30px 40px 40px 40px;
		overflow: hidden;
		border-radius: 4px !important;
		background-color: rgba(255, 255, 255, 1);
	}
	.filter_box{
		overflow: hidden;
		padding-bottom: 10px;
	}
	.filter_box select{
		border-radius: 4px !important;
		width: 150px;
		height: 36px;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	
	.date_box{
		display: inline-block;
		width: 440px;
		margin-bottom: -15px;
	}
	.date_box1{
		width: 400px;
	}
	.search_box input{
		width: 410px;
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
	.plan_item{
		position: absolute;
		padding: 10px 20px;
		background: #fff;
		margin-top: 5px;
		display: none;
	}
	.plan_item li{
		list-style: none;
		padding: 5px 0;
		cursor: pointer;
	}
	#planTable_filter{
		display: none;
	}
	
	table.table-bordered.dataTable th, table.table-bordered.dataTable td{
		background: #fff;
		text-align: left;
		padding-left: 15px;
	}
	.table-scrollable > .table-bordered > thead > tr:last-child > th{
		border-bottom: 1px solid #ddd;
	}
	#planTable{
		border-radius: 15px !important;
		overflow: hidden;
	}
	.dataTables_wrapper > .row:first-child{
		margin-top: 10px;
		margin-bottom: -15px;
	}
	.handleLook{
		cursor: pointer;
	}
	.pr10{
		padding-right: 10px;
	}
	.editorSave{
		background: #2096fa;
		color: #fff;
		border: none;
		width: 55px;
		height: 25px;
		border-radius: 5px !important;
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
	}
	.error_mask .mask_text{
		color: #f56c6c !important;
	}
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
	.time-style{
		display: inline-block;
		width: 300px;
	}
	.calendar-time,.drp-selected{
		display: none !important;
	} 
	.daterangepicker td.in-range{
		border-radius: 0 !important;
	}
	.input-group .form-control{
		height: 36px;
		border-left: none !important;
		border: 1px solid rgba(220, 223, 230, 1);
		border-bottom-right-radius: 4px !important;
		border-top-right-radius: 4px !important;
	}
	.input-group-btn:first-child>.btn, .input-group-btn:first-child>.btn-group{
		border: 1px solid rgba(220, 223, 230, 1);
		border-bottom-left-radius: 4px !important;
		border-top-left-radius: 4px !important;
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
	.input-medium{
		width: 215px !important;
	}
	.button_box{
		text-align: right;
		padding: 20px 0;
	}
	.button_box > button{
		width: 120px;
		height: 36px;
		border-radius: 4px !important;
		background-color: rgba(99, 197, 209, 1);
		border: none;
		color: #fff;
		font-size: 14px;
	}
	.button_box > button a{
		color: #fff;
		width: 100%;
		height: 100%;
		text-decoration: none;
	}
	.button_box > button svg{
		display: inline-block;
		margin-bottom: -2px;
	}
	.total_records{
		color: rgba(144, 147, 153, 1);
		font-size: 12px;
		font-family: PingFangSC-Regular;
		margin-top: 35px;
		display: block;
	}
	.total_records span{
		color: #63C5D1;
	}
	.form-control{
		border: 1px solid rgba(220, 223, 230, 1) !important;
	}
	div.dataTables_wrapper div.dataTables_length select{
		width: 60px !important;
		height: 28px;
	}
	.fa-calendar:before{
		color: #C0C4CC;
	}
	.btn-circle{
		z-index: 999;
	}
	#sortSelect{
		position: absolute;
		left: 130px;
		width: 120px;
		height: 28px;
		z-index: 9999;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	.keyword{
		outline: none;
		padding-left: 10px;
	}
	.dataTables_empty{
		text-align: center !important;
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
	.openscenter{
		z-index: 99999 !important;
	}
	.select2-container--open .select2-dropdown--below,.select2-container .select2-search--inline .select2-search__field{
		width: 130px !important;
	}
	.select2-selection__choice{
		float: left;
		padding: 0 5px;
		margin-right: 5px;
		list-style: none;
		height: 34px;
		background: #fff !important;
		border: 1px solid #fff !important;
	}
	.select2-container .select2-search--inline .select2-search__field{
		display: none;
	}
	.select2-container--bootstrap .select2-selection, .select2-container--bootstrap.select2-container--focus .select2-selection{
		border: 1px solid rgba(220, 223, 230, 1);
		outline: none;
		border-radius: 4px !important;
	}
	.select2-container .select2-selection--multiple{
		min-width: 150px;
		height: 36px;
		line-height: 36px;
	}
	.select2-container .select2-selection--multiple .select2-selection__rendered{
		list-style: none;
		padding: 0;
		margin: 0;
		padding-right: 10px !important;
	}
	.btn.default:not(.btn-outline){
		border-right: none;
		height: 36px;
	}
	.select2-selection__clear{
		display: none;
	}
</style>
<link rel="stylesheet" type="text/css" media="all" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>

<div>
	<div class="button_box">
		<!-- <button>
			<svg t="1588042241983" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2835" width="14" height="14"><path d="M821.1456 276.8384c-235.9296 25.1392-449.1776 226.7136-490.5472 452.352a38.4 38.4 0 1 1-75.5712-13.824c45.568-248.576 269.312-468.48 526.6944-510.6688l-117.8112-69.1712a38.4 38.4 0 0 1 38.912-66.2528l223.3344 131.1744a38.4 38.4 0 0 1 10.1376 57.6l-170.752 206.6432a38.4 38.4 0 1 1-59.1872-48.9472l114.7904-138.9056z" p-id="2836" fill="#ffffff"></path><path d="M832 620.0832a38.4 38.4 0 0 1 76.8 0v158.208c0 85.9648-61.5936 157.8496-140.8 157.8496H204.8c-79.2064 0-140.8-71.8848-140.8-157.9008V300.3904c0-86.016 61.5936-157.8496 140.8-157.8496h220.2112a38.4 38.4 0 1 1 0 76.8H204.8c-33.8944 0-64 35.072-64 81.0496V778.24c0 45.9776 30.1056 81.1008 64 81.1008h563.2c33.8944 0 64-35.1232 64-81.1008v-158.1568z" p-id="2837" fill="#ffffff"></path></svg>
			Export
		</button> -->
		<button>
			<a href="/marketingPlan/detail?id=null" target="_blank">
				<svg t="1588042189957" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1900" width="14" height="14"><path d="M191.703625 460.631052l639.908158 0 0 100.316753-639.908158 0 0-100.316753Z" p-id="1901" fill="#ffffff"></path><path d="M461.67022 192.045921l100.31573 0 0 639.908158-100.31573 0 0-639.908158Z" p-id="1902" fill="#ffffff"></path></svg>
				新建营销计划
			</a>
			
		</button>
	</div>
	<div class="content">
		<div class="filter_box">
			<div class="filter_option">
				<label for="type_select">类型</label>
				<select id="mutiSelect" name="mutiSelect" multiple="multiple" style="width:100%;" onchange="status_filter(this.value,8)">
					<option value =""></option>
					<option value ="CPC">ACPC</option>
					<option value ="LD">LD</option>
					<option value ="RSG">RSG</option>
					<option value ="CTG">CTG</option>
					<option value ="Deal">Deal</option>
				</select>
			</div>
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
				<label for="seller_select">销售</label>
				<select id="seller_select" onchange="status_filter(this.value,7)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="status_select">状态</label>
				<select id="status_select"  onchange="status_filter(this.value,3)">
					<option value ="">全部</option>
					<option value ="待审批">待审批</option>
					<option value ="进行中">进行中</option>
					<option value ="已完结">已完结</option>
					<option value ="已中止">已中止</option>
					<option value ="已拒绝">已拒绝</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="">创建时间</label>
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
				<label for="">预计起始时间</label>
				<div class="input-group input-medium" id="estStartTime">
					<span class="input-group-btn">
					    <button class="btn default date-range-toggle" type="button">
					        <i class="fa fa-calendar"></i>
					    </button>
					</span>
				    <input type="text" class="form-control estTimeInput">  
				</div>
			</div>
			<!-- <div class="filter_option">
				<label for="">实际时间</label>
				<div class="input-group input-medium" id="actualTime">
					<span class="input-group-btn">
					    <button class="btn default date-range-toggle" type="button">
					        <i class="fa fa-calendar"></i>
					    </button>
					</span>
				    <input type="text" class="form-control actualTimeInput">  
				</div>
			</div> -->
			<div class="filter_option search_box">
				<label for="">搜索</label>
				<input type="text" class="keyword">
				<button class="search">
					<svg t="1588043111114" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3742" width="18" height="18"><path d="M400.696889 801.393778A400.668444 400.668444 0 1 1 400.696889 0a400.668444 400.668444 0 0 1 0 801.393778z m0-89.031111a311.637333 311.637333 0 1 0 0-623.331556 311.637333 311.637333 0 0 0 0 623.331556z" fill="#ffffff" p-id="3743"></path><path d="M667.904 601.998222l314.766222 314.823111-62.919111 62.976-314.823111-314.823111z" fill="#ffffff" p-id="3744"></path></svg>
					搜索
				</button>	
				<button class="clear">清空筛选</button>
			</div>
			<!-- <div class="filter_option">
				<span for="" class="total_records">Found total <span class="tableTotal"></span> records</span>
			</div> -->
		</div>
		<div style="position: relative;">
			<!-- <select name="" id="sortSelect">
				<option value="">Submitted</option>
				<option value="">ROMI</option>
				<option value="">Actual Spend</option>
				<option value="">Est.Spend</option>
				<option value="">RSG Goal</option>
				<option value="">Review %</option>
			</select> -->
			<table id="planTable" class="display table-striped table-bordered table-hover" style="width:100%">
				<thead>
					<tr style="text-align: center;">
						<th>BG</th>
						<th>BU</th>
						<th>Station</th>
						<th>planStatus</th>
						<th><input type="checkbox" id="selectAll" /></th>
						<th>产品</th>
						<th>Asin/Sku</th>
						<th>销售</th>
						<th>类型</th>
						<th>预计</th>
						<th>实际</th>
						<th>ROMI</th>
						<th>状态</th>
						<th>操作</th>
					</tr>
				</thead>
				
			</table>
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
<script>
	
	//筛选
	function status_filter(value,column) {
	    if (value == '') {
	        tableObj.column(column).search('').draw();
	    }
	    else tableObj.column(column).search(value).draw();
	}
	$(document).ready(function () {
		let sap_seller_id = <?php echo $sap_seller_id;?>;
		$('#mutiSelect').select2({
			allowClear:true,
			templateSelection:function(data){
				if(data.id === ""){
					return 'aaa'
				}
				return data.text
			}
		})
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
			tableObj.ajax.reload();
			handleClear();
			status_filter(val,0);
			status_filter(val,1);
			status_filter(val,2);
			status_filter(val,3);
			status_filter(val,7);
			status_filter(val,8);
		})
		$("#estStartTime").daterangepicker({
		    opens: "left", //打开的方向，可选值有'left'/'right'/'center'
		    format: "YYYY-MM-DD",
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
		    /* minDate: "01/01/2012",
		    maxDate: "12/31/2018" */
		}, function (t, e) {
			$("#seller_select").empty();
			$("#seller_select").append("<option value=''>全部</option>");
		    $("#estStartTime input").val(t.format("YYYY-MM-DD") + " - " + e.format("YYYY-MM-DD"));
			let reqList = {
				"created_at_s": cusstr($('#estStartTime input').val() , ' - ' , 1),
				"created_at_e": cusstr1($('#estStartTime input').val() , ' - ' , 1),
			};
			let val = ''
			tableObj.ajax.reload();
			handleClear();
			status_filter(val,0);
			status_filter(val,1);
			status_filter(val,2);
			status_filter(val,3);
			status_filter(val,7);
			status_filter(val,8);
		})
		$("#actualTime").daterangepicker({
		    opens: "left", //打开的方向，可选值有'left'/'right'/'center'
		    format: "YYYY-MM-DD",
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
		    /* minDate: "01/01/2012",
		    maxDate: "12/31/2018" */
		}, function (t, e) {
			$("#seller_select").empty();
			$("#seller_select").append("<option value=''>全部</option>");
		    $("#actualTime input").val(t.format("YYYY-MM-DD") + " - " + e.format("YYYY-MM-DD"));	
			let reqList = {
				"created_at_s": cusstr($('#actualTime input').val() , ' - ' , 1),
				"created_at_e": cusstr1($('#actualTime input').val() , ' - ' , 1),
			};
			let val = ''
			tableObj.ajax.reload();
			handleClear();
			status_filter(val,0);
			status_filter(val,1);
			status_filter(val,2);
			status_filter(val,3);
			status_filter(val,7);
			status_filter(val,8);
		})
		
		$("#createTimes input").val(moment().format("YYYY-MM-DD")+ " - "+ moment().format("YYYY-MM-DD")); //设置初始值
		$("#estStartTime input").val(moment().format("YYYY-MM-DD")+ " - "+ moment().format("YYYY-MM-DD")); //设置初始值
		//$("#actualTime input").val(moment().subtract("days", 6).format("YYYY-MM-DD")+ " - "+ moment().format("YYYY-MM-DD")); //设置初始值  
		//全选
		$("#selectAll").on('change',function() {  
		    $("input[name='checkedInput']").prop("checked", this.checked);
			let checkedBox = $("input[name='checkedInput']:checked");
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide()
		});  
		//单条选中
		$("body").on('change','.checkbox-item',function(){
			var $subs = $("input[name='checkedInput']");
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 
			let checkedBox = $subs.filter(":checked");
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide()
		});
		//排序
		$('#sortSelect').on('change',function(){
			console.log(1)
		})
		function handleClear(){
			$('.select2-selection').text('');
			$('#marketplace_select').val('');
			$('#bg_select').val('');
			$('#bu_select').val('');
			$('#seller_select').val('');
			$('#status_select').val('');
		}
		//清空所有筛选
		$('.clear').on('click',function(){
			handleClear();
			$('.createTimeInput').val("");
			$('.estTimeInput').val("");
			/* $('.actualTimeInput').val(); */
			$('.keyword').val("");
			let val = ' '
			status_filter(val,0)
			status_filter(val,1)
			status_filter(val,2)
			status_filter(val,3)
			status_filter(val,7)
			status_filter(val,8)
			tableObj.ajax.reload();
		})
		//时间戳转换
		function dateStr(str){
			str = str.replace(/-/g,'/'); // 将-替换成/，因为下面这个构造函数只支持/分隔的日期字符串
			return  Math.round(new Date(str).getTime()/1000); // 构造一个日期型数据，值为传入的字符串
		}
		
		$('.create_btn').on('click',function(){
			$('.plan_item').toggle();
		});
		//提交表单
		$('.mask_submit').click(function(){
			let rating = $('.rating').val();
			let rsgD = $('.rsgD').val();
			let totalRsg = $('.totalRsg').text();
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
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		
		tableObj = $('#planTable').DataTable({
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			dispalyLength: 2, // default record count per page
			paging: true,  // 是否显示分页
			info: false,// 是否表格左下角显示的文字
			ordering: true,
			serverSide: false,//是否所有的请求都请求服务器	
			scrollX: "100%",
			scrollCollapse: false,
			/* fixedColumns: { //固定列的配置项
				leftColumns: 1, //固定左边第一列
				rightColumns:1 //固定右边第一列
			}, */
			/* pagingType: 'numbers',
			searching: true,  //去掉搜索框
			bLengthChange: false, //去掉每页多少条框体
			 */
			ajax: {
				url: "/marketingPlan/rsgList",
				type: "post",
				data : function(){
					reqList = {
						"sap_seller_id" : sap_seller_id,
						"created_at_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
						"created_at_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
						"from_time": cusstr($('.estTimeInput').val() , ' - ' , 1),
						"to_time": cusstr1($('.estTimeInput').val() , ' - ' , 1),
						"condition": $('.keyword').val(),
					};
					return reqList;
				},
				dataSrc:function(res){
					if(res.status == 1){
						$.each(res[1], function (index, value) {
							$("#seller_select").append("<option value='" + value + "'>" + value + "</option>");
						})
						
						//$('.tableTotal').text(res[0].length);
						return res[0];
					}else{
						return ""
					}
					
				},
			},			
			data: [],
			columns: [
				{
					data: "bg" ,
					visible: false,
				},
				{
					data: "bu" ,
					visible: false,
				},
				{
					data: 'station',
					visible: false,
				},
				{
					data: 'plan_status',
					visible: false,
				},
				{
					data: "ids",
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
					data: "image",
					render: function(data, type, row, meta) {
						let img,dot,str;
						if(row.images != null){
							str = row.images;
							dot = str.split(',');
							dot.length > 1 ? img = 'https://images-na.ssl-images-amazon.com/images/I/' + dot[0] : img = ''
						}
						var content = '<img src="'+img+'" alt="" style="display:block; width:50px; height:60px;">';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta) {
						var content = '<div><a href="https://'+row.toUrl+'/dp/'+ row.asin +'" target="_blank" style="text-decoration:none">'+row.asin+'</a></div><div style="color: #909399;">'+row.sku+'</div>';
						return content;
					}
				},
				{
					data: 'Seller',
				},
				{
					data: 'type',
				},
				{ 
					data: null,
					render: function(data, type, row, meta) {
						let content = '<div style="text-align:left"><div><span style="padding-right:10px">RSG 数量:</span>'+row.goal+'</div><div><span style="padding-right:10px">预计花费:</span>'+row.est_spend+'</div></div>';
						return content;
					},
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div style="text-align:left"><div><span style="padding-right:10px">申请数:</span>'+row.applied+'</div><div><span style="padding-right:10px">评论数:</span>'+row.reivews+'</div><div><span style="padding-right:10px">实际花费:</span>'+row.actual_spend+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						var content = '<div style="text-align:left"><div><span style="padding-right:10px">预计(60天):</span>'+row.current_60romi+'</div><div><span style="padding-right:10px">实际(60天):</span>'+row.actual_60romi+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						var content = '<div style="text-align:left"><div>'+row.plan_status+'</div><div><span>提交:</span>'+row.updated_at+'</div></div>';
						return content;
					}
				},
				{
					data: "id",
					render: function(data, type, row, meta) {
						let id = row.id;
						var content = '<a style="color: #63C5D1;cursor:pointer; text-decoration: none;" href="/marketingPlan/detail?id='+ row.id +'" target="_blank">详情</a>';
						return content;
					},
					
				},
				
			], 
			
		});
		$('.search').on('click',function(){
			handleClear();
			$("#seller_select").empty();
			$("#seller_select").append("<option value=''>全部</option>");
			let reqList = {
				"sap_seller_id" : sap_seller_id,
				"created_at_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
				"created_at_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
				"from_time": cusstr($('.estTimeInput').val() , ' - ' , 1),
				"to_time": cusstr1($('.estTimeInput').val() , ' - ' , 1),
				"condition": $('.keyword').val(),
			};
			tableObj.ajax.reload();
		})
		

	})
</script>
@endsection