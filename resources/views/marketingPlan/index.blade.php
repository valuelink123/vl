@extends('layouts.layout')
@section('label', 'Marketing Plan')
@section('content')

<style>
	.table-scrollable{
		overflow: hidden;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
		background: none;
		border: 1px solid #eaeff8;
	}
	.btn.default:not(.btn-outline){
		background: #fff;
	}
	.page-container-bg-solid .page-content{
		background: #eaeff8;
	}
	.plan_title{
		font-weight: 900;
		font-size: 28px;
		margin-bottom: 0;
		margin-top: 30px;
	}
	.plan_nav{
		border-bottom: 1px solid #fff;
		overflow: hidden;
		padding: 0;
		margin: 0;
	}
	.plan_nav > li{
		float: left;
		padding: 15px 20px;
		list-style: none;
		margin-bottom: -5px;
	}
	.plan_nav > li:first-child{
		border-bottom: 5px solid #3bbeca;
	}
	.plan_nav > li a{
		color: #65747f;
		font-size: 14px;
		display: block;
		text-decoration: none;
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
	.filter_box{
		padding: 15px 30px 10px 30px;
		background: #fff;
		margin-top: 25px;
		border-radius: 10px !important;
		overflow: hidden;
	}
	.search_box{
		float: right;
	}
	.filter_box select{
		border: 1px solid #eaeff8;
		padding: 5px 10px;
		margin-right: 20px;
		border-radius: 20px !important;
		outline: none;
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
		border: 1px solid #eaeff8;
		padding: 6px 5px;
		width: 250px;
		border-radius: 20px !important;
	}
	.search{
		background: #fff;
		border: 1px solid #eaeff8;
		padding: 6px 20px;
		border-radius: 20px !important;
		margin-left: 10px;
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
	.w4{
		min-width: 40px;
	}
	.w6{
		min-width: 60px;
	}
	.w8{
		min-width: 80px;
	}
	.w10{
		min-width: 100px;
	}
	.w22{
		min-width: 220px;
	}
	table.table-bordered.dataTable th, table.table-bordered.dataTable td{
		text-align: center;
		background: #fff;
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
</style>
<!-- <link rel="stylesheet" type="text/css" media="all" href="daterangepicker.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script type="text/javascript" src="daterangepicker.js"></script> -->
<div>
	<h3 class="plan_title">Marketing Plan</h3>
	<ul class="plan_nav">
		<li><a href="">RSG</a></li>
		<li><a href="">Promotion</a></li>
		<li><a href="">Others</a></li>
		<li class="createPlan">
			<button class="create_btn">+New Plan</button>
			<ul class="plan_item">
				<li class="show_rsg_plan"><a href="detail?id=null" target="_blank">RSG</a></li>
				<li><a href="" target="_blank">Promotion</a></li>
				<li><a href="" target="_blank">Others</a></li>
			</ul>
		</li>
	</ul>
	<div class="filter_box">
			<select>
				<option value ="">Campaign</option>
			</select>
			<select>
				<option value ="">Activity</option>
			</select>
			<select>
				<option value ="">Status</option>
			</select>
			<select>
				<option value ="">Seller</option>
			</select>
			<select>
				<option value ="">This Week</option>
			</select>
			<!-- <input type="text" id="config-demo" class="form-control"> -->
			<!-- <div class="date_box date_box1">
				<div style="float: left; width: 70px; line-height: 30px;text-align: right;padding-right: 10px;">Est. Start</div>
				<div style="float: left;width: 130px;">
				    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
				        <input type="text" class="form-control form-filter input-sm date1" readonly name="date_from" onchange="handleFilter(this.value,10)" placeholder="From" value="">
				        <span class="input-group-btn">
							<button class="btn btn-sm default" type="button">
								<i class="fa fa-calendar"></i>
							</button>
						</span>
				    </div>
				</div>
				<div style="float: left; width: 70px; line-height: 30px;text-align: right;padding-right: 10px;">Est. End</div>
				<div style="float: left;width: 130px;">
				    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
				        <input type="text" class="form-control form-filter input-sm date2" readonly name="date_to" onchange="handleFilter(this.value,11)" placeholder="To" value="">
				        <span class="input-group-btn">
							<button class="btn btn-sm default" type="button">
								<i class="fa fa-calendar"></i>
							</button>
						</span>
				    </div>
				</div>		
			</div>
			
			<div class="date_box">
				<div style="float: left; width: 90px; line-height: 30px;text-align: right;padding-right: 10px;">Actual Start</div>
				<div style="float: left;width: 130px;">
				    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
				        <input type="text" class="form-control form-filter input-sm date1" readonly name="date_from" onchange="handleFilter(this.value,15)" placeholder="From" value="">
				        <span class="input-group-btn">
							<button class="btn btn-sm default" type="button">
								<i class="fa fa-calendar"></i>
							</button>
						</span>
				    </div>
				</div>
				<div style="float: left; width: 90px; line-height: 30px;text-align: right;padding-right: 10px;">Actual End</div>
				<div style="float: left;width: 130px;">
				    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
				        <input type="text" class="form-control form-filter input-sm date2" readonly name="date_to" onchange="handleFilter(this.value,16)" placeholder="To" value="">
				        <span class="input-group-btn">
							<button class="btn btn-sm default" type="button">
								<i class="fa fa-calendar"></i>
							</button>
						</span>
				    </div>
				</div>		
			</div> -->
		
		<div class="search_box">
			<input type="text">
			<button class="search">Search</button>
		</div>
	</div>
	<div>
		<table id="planTable" class="display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<tr style="text-align: center;">
					<th class="w6">Plan ID</th>
					<th>Plan Status</th>
					<th>SKU Status</th>
					<th>RSG Goal</th>
					<th>ASIN/SKU</th>
					<th class="w6">Price/FBA Stk</th>
					<th>Units/D</th>
					<th class="w8">Ratings</th>
					<th class="w8">Rank</th>
					<th>CR%</th>
					<th class="w8">Est. Start</th>
					<th class="w8">Est. End</th>
					<th class="w10">Est. RSG</th>
					<th>RSG Spend</th>
					<th class="w6">E Value</th>
					<th class="w8">Actual Start</th>
					<th class="w8">Actual End</th>
					<th class="w8">RSG Details</th>
					<th class="w10">Notes</th>
					<th>Action</th>
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
<script>
	function handleEdit(e){
		let _this = e;
		$(_this).find('.editorTxt').show();
		$(_this).find('.editorData').hide();
		$(_this).find('.editorSave').show();
	}
	function handleSaveEditor(e){
		let _this = e
		
		let id = $(_this).parent().attr("id");
		let val = $(_this).parent().find('.editorTxt').val();
		$(_this).parent().find('.editorTxt').hide();
		$(_this).parent().find('.editorData').show();
		$(_this).parent().find('.editorSave').hide();
		
		/* $.ajax({
			type:"post",
			url:"",
			data:{
				"id": id,
				"notes": val
			},
			success:function(res){
				$('.success_mask_text').text('success to save.');
				$('.success_mask').show();
				tableObj.ajax.reload();
				setTimeout(function(){
					$('.success_mask').fadeOut(1000);
				},2000)	
			},
			error:function(err){
				$('.error_mask_text').text('error to save.');
				$('.error_mask').show();
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			},
		}); */
		event.stopPropagation();
	}
	//筛选
	 function handleFilter(value,column) {
		 console.log(value,column)
		 if (value == '') {
			 tableObj.column(column).search('').draw();
		 }
		 else tableObj.column(column).search(value).draw();
	 }
	$(document).ready(function () {
		/* updateConfig();
		function updateConfig() {
		  var options = {
			  autoApply: false,
			  showDropdowns: false,
			  showWeekNumbers: false,
			  showISOWeekNumbers: false,
			  timePicker: true,
			  timePicker24Hour: true,
			  timePickerSeconds: true,
			  alwaysShowCalendars: true,
			  linkedCalendars: true,
			  autoUpdateInput: true,
			  showCustomRangeLabel: true,
			  opens: 'center',
			  drops: 'down',
			  singleDatePicker: false,
			  //dateLimit: { days: 7 }, 
			  ranges : {
			    'Today': [moment(), moment()],
			    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			    'This Month': [moment().startOf('month'), moment().endOf('month')],
			    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			  },
			 locale: {
			    direction: $('#rtl').is(':checked') ? 'rtl' : 'ltr',
			    format: 'MM/DD/YYYY HH:mm',
			    separator: ' - ',
			    applyLabel: 'Apply',
			    cancelLabel: 'Cancel',
			    fromLabel: 'From',
			    toLabel: 'To',
			    customRangeLabel: 'Custom',
			    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
			    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			    firstDay: 1
			  }
		  };
		  $('#config-demo').daterangepicker(options, function(start, end, label) { console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')'); }).click();;       
		} */
		
		//时间戳转换
		function dateStr(str){
			str = str.replace(/-/g,'/'); // 将-替换成/，因为下面这个构造函数只支持/分隔的日期字符串
			return  Math.round(new Date(str).getTime()/1000); // 构造一个日期型数据，值为传入的字符串
		}
		
		//时间选择器
		function initPickers() {
		    $('.date-picker').datepicker({
		        rtl: App.isRTL(),
		        autoclose: true
		    });
		}
		initPickers();

		
		$('.create_btn').on('click',function(){
			$('.plan_item').toggle();
		});
		//提交表单
		$('.mask_submit').click(function(){
			let rating = $('.rating').val();
			let rsgD = $('.rsgD').val();
			let totalRsg = $('.totalRsg').text();
			console.log(rating,rsgD,totalRsg)
		})
		
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
			fixedColumns: { //固定列的配置项
				leftColumns: 1, //固定左边第一列
				rightColumns:1 //固定右边第一列
			},
			/* pagingType: 'numbers',
			searching: true,  //去掉搜索框
			bLengthChange: false, //去掉每页多少条框体
			 */
			
			/* ajax: {
				url: "",
				dataSrc:function(res){
					console.log(res)
				},
			}, */			
			data: [
				{
					id: 'R00001',
					planStatus: '待审批',
					sukStatus: 'Normal',
					rsgGoal: 'Improve Ratings',
					asin: 'B0833P5XFD',
					sku: 'WB6666',
					price: '$19.99',
					fba: '1466',
					unitsBefore: 4,					
					unitsTarget: 7,			
					unitsAfter: 9,
					ratingsBeforeStart: "4.8",
					ratingsBeforeNum: 90,
					ratingsGoalStart:"4.8",
					ratingsGoalNum:200,
					ratingsAfterStart: "4.8",
					ratingsAfterNum: 240,
					rankBefore: 'P2-10',
					rankTarget: 'P1-5',			
					rankAfter: 'P1-5',
					crBefore: "15%",
					crTarget: "10%",			
					crAfter: "17%",
					units: 'B00XDM3FMM',
					ratings: 'Full: $20 Promo：$15',
					rank: '25%',
					cr: '50BUVNXI',
					estDaily: 5,
					estTotal: 100,
					rsgEst: "$499",					
					rsgctual: "$500",
					eEst: "$499",
					ectual: "$500",
					rsgApplied: 100,
					rsgReviews: 95,		
					rsgReviewsB: "95%",
					estStart: '2020-03-15',
					estEnd: '2020-03-23',
					estRsg: '$100',
					rsgSpend: '20',
					value: '$200',
					actualStart: '2020-03-15',
					actualEnd: '2020-03-20',
					rsgDetails: '20',
					notes: 'You are not required to leave a review for any product you get from',
				},
				{
					id: 'R00002',
					planStatus: '待审批',
					sukStatus: 'Normal',
					rsgGoal: 'Improve Ratings',
					asin: 'B0833P5XFD',
					sku: 'WB6666',
					price: '$19.99',
					fba: '1466',
					unitsBefore: 4,					
					unitsTarget: 7,			
					unitsAfter: 9,
					ratingsBeforeStart: "4.8",
					ratingsBeforeNum: 90,
					ratingsGoalStart:"4.8",
					ratingsGoalNum:200,
					ratingsAfterStart: "4.8",
					ratingsAfterNum: 240,
					rankBefore: 'P2-10',
					rankTarget: 'P1-5',			
					rankAfter: 'P1-5',
					crBefore: "15%",
					crTarget: "10%",			
					crAfter: "17%",
					units: 'B00XDM3FMM',
					ratings: 'Full: $20 Promo：$15',
					rank: '25%',
					cr: '50BUVNXI',
					estDaily: 5,
					estTotal: 100,
					rsgEst: "$499",					
					rsgctual: "$500",
					eEst: "$499",
					ectual: "$500",
					rsgApplied: 100,
					rsgReviews: 95,		
					rsgReviewsB: "95%",
					estStart: '2020-3-15',
					estEnd: '2020-3-23',
					estRsg: '$100',
					rsgSpend: '20',
					value: '$200',
					actualStart: '2020-3-15',
					actualEnd: '2020-3-20',
					rsgDetails: '20',
					notes: 'You are not required to leave a review for any product you get from',
				},
				{
					id: 'R00003',
					planStatus: '待审批',
					sukStatus: 'Normal',
					rsgGoal: 'Improve Ratings',
					asin: 'B0833P5XFD',
					sku: 'WB6666',
					price: '$19.99',
					fba: '1466',
					unitsBefore: 4,					
					unitsTarget: 7,			
					unitsAfter: 9,
					ratingsBeforeStart: "4.8",
					ratingsBeforeNum: 90,
					ratingsGoalStart:"4.8",
					ratingsGoalNum:200,
					ratingsAfterStart: "4.8",
					ratingsAfterNum: 240,
					rankBefore: 'P2-10',
					rankTarget: 'P1-5',			
					rankAfter: 'P1-5',
					crBefore: "15%",
					crTarget: "10%",			
					crAfter: "17%",
					units: 'B00XDM3FMM',
					ratings: 'Full: $20 Promo：$15',
					rank: '25%',
					cr: '50BUVNXI',
					estDaily: 5,
					estTotal: 100,
					rsgEst: "$499",					
					rsgctual: "$500",
					eEst: "$499",
					ectual: "$500",
					rsgApplied: 100,
					rsgReviews: 95,		
					rsgReviewsB: "95%",
					estStart: '2020-3-15',
					estEnd: '2020-3-23',
					estRsg: '$100',
					rsgSpend: '20',
					value: '$200',
					actualStart: '2020-3-15',
					actualEnd: '2020-3-20',
					rsgDetails: '20',
					notes: 'You are not required to leave a review for any product you get from',
				},
			],
			columns: [
				{
					data: "id",
					render: function(data, type, row, meta) {
						let id = row.id;
						var content = '<span class="handleLook">'+id+'</span>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {	
						$(cell).on("click", ".handleLook", function (e) {							
							let id = rowData.id;
							console.log(id);
							$('.mask').show();
							
							$('.rsgGoal').val('Improve Ratings');
							$('.planStatus').val('Pending');
							$('.asin').val('B0833P5XFD');
							$('.sku').text('2');
							$('.skuStatus').text('3');
							$('.star').text('2');
							$('.reviews').text('2');
							$('.star').text('2');
							$('.rating').val('2');
							$('.inventory').text('2');
							$('.targetRating').val('2');
							$('.targetUnitsSold').val('2');
							$('.totalRsg').text('2');
							$('.rsgPrice').val('2');
							$('.estSpend').text();
							$('.fromDate').val();
							$('.toDate').val();
							$('.rsgD').val();
							$('.currentRank1').text();
							$('.currentCr1').text();
							$('.currentSold1').text();
							$('.eValue').text();
							$('.estRoi60').text();
							$('.estRank').val();
							$('.estCr').val();
							$('.estSold').val();
							$('.estDay').val();
							$('.estRoi120').text();
							$('.crChange').text();
							$('.dailyChange').text();
							$('.estAdded').text();
							$('.investmentCycle1').text();
							$('.currentRank2').text();
							$('.actualSpend').val();
							$('.currentCr2').text();
							$('.currentSold2').text();
							$('.eUnit').text();
							$('.estDay60').text();
							$('.conversionComplete').text();
							$('.dailyComplete').text();
							$('.eComplete').text();
							$('.investmentCycle2').text();
							$('.remarks').text();
							
						})
					}
				},
				{
					data: "planStatus" ,
				},
				{
					data: 'sukStatus',
				},
				{
					data: 'rsgGoal',
				},
				{ 
					data: null,
					render: function(data, type, row, meta) {
						let url = "https://www.amazon.com/dp/"+row.asin;
						let content = '<div><div><a href="'+url+'" target="_blank" style="color:blue">'+row.asin+'</a></div><div>'+row.sku+'</div></div>';
						return content;
					},
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div>'+row.price+'</div><div>'+row.fba+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Before:</span>'+row.unitsBefore+'</div><div><span class="pr10">Target:</span>'+row.unitsTarget+'</div><div><span class="pr10">After:</span>'+row.unitsAfter+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Before:</span>'+row.ratingsBeforeStart+'('+row.ratingsBeforeNum+')</div><div><span class="pr10">Goal:</span>'+row.ratingsGoalStart+'('+row.ratingsGoalNum+')</div><div><span class="pr10">After:</span>'+row.ratingsAfterStart+'('+row.ratingsAfterNum+')</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Before:</span>'+row.rankBefore+'</div><div><span class="pr10">Goal:</span>'+row.rankTarget+'</div><div><span class="pr10">After:</span>'+row.rankAfter+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Before:</span>'+row.crBefore+'</div><div><span class="pr10">Goal:</span>'+row.crTarget+'</div><div><span class="pr10">After:</span>'+row.crAfter+'</div></div>';
						return content;
					}
				},
				{
					data: "estStart",
				},
				{
					data: "estEnd",
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Daily:</span>'+row.estDaily+'</div><div><span class="pr10">Total:</span>'+row.estTotal+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Est:</span>'+row.rsgEst+'</div><div><span class="pr10">Actual:</span>'+row.rsgctual+'</div></div>';
						return content;
					}
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Est/D:</span>'+row.eEst+'</div><div><span class="pr10">Actual/D:</span>'+row.ectual+'</div></div>';
						return content;
					}
				},
				{ 
					data: "actualStart",
				},
				{
					data: "actualEnd",
				},
				{
					data: null,
					render: function(data, type, row, meta){
						let content = '<div><div><span class="pr10">Applied:</span>'+row.rsgApplied+'</div><div><span class="pr10">Reviews:</span>'+row.rsgReviews+'</div><div><span class="pr10">Reviews%:</span>'+row.rsgReviewsB+'</div></div>';
						return content;
					}
				},
				{
					data: "notes",
					render: function(data, type, row, meta){
						let id = row.id
						let content = '<div onclick="handleEdit(this)" id='+id+'><span class="editorData">'+data+'</span><textarea type="text" size="16" style="width: 100%; display:none" class="editorTxt" rows="3" cols="20">'+data+'</textarea><button style="display:none;" class="editorSave" onclick="handleSaveEditor(this)">Save</button></div>';
						return content;
					}
					/* createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function (e) {
							$(this).html('<textarea type="text" size="16" style="width: 100%"  rows="3" cols="20">'+cellData+'</textarea><button class="save">保存</button>');
							 var aInput = $(this).find(":textarea");
							aInput.focus().val(cellData); 
						});
						$(cell).on("click", ".save", function (e) {
							var text = $(this).val();
							console.log(text)
							 if($(this).val() != cellData){
								$(cell).html(text);
								listObj.cell(cell).data(text);
								$.ajax({
									type:"post",
									url:'/hijack/upResellingDetail',
									data:{
										id: rowData.id,
										remark: rowData.reselling_remark
									},
									error:function(err){
									    alert(err);
									},
									success:function(res){
										listObj.ajax.reload()
									}
								});
							}else{
								$(cell).html(text);
								listObj.cell(cell).data(text);
							} 		
						})
					} */
				},
				{
					data: "null",
					render: function(data, type, row, meta) {
						let id = row.id;
						var content = '<a href="detail?id='+id+'" target="_blank"><img src="../assets/global/img/lookover.png" class="handleLook"></a>';
						return content;
					},
					/* createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {	
						$(cell).on("click", ".handleLook", function (e) {							
							let id = rowData.id;
							console.log(id);
							$('.mask').show();
							$('.rsgGoal').val('Improve Ratings');
							$('.planStatus').val('Pending');
							$('.asin').val('B0833P5XFD');
							$('.sku').text('1');
							$('.skuStatus').text('1');
							$('.star').text('1');
							$('.reviews').text('1');
							$('.star').text('1');
							$('.rating').val('1');
							$('.inventory').text('1');
							$('.targetRating').val('1');
							$('.targetUnitsSold').val('1');
							$('.totalRsg').text('1');
							$('.rsgPrice').val('1');
							$('.estSpend').text();
							$('.fromDate').val();
							$('.toDate').val();
							$('.rsgD').val();
							$('.currentRank1').text();
							$('.currentCr1').text();
							$('.currentSold1').text();
							$('.eValue').text();
							$('.estRoi60').text();
							$('.estRank').val();
							$('.estCr').val();
							$('.estSold').val();
							$('.estDay').val();
							$('.estRoi120').text();
							$('.crChange').text();
							$('.dailyChange').text();
							$('.estAdded').text();
							$('.investmentCycle1').text();
							$('.currentRank2').text();
							$('.actualSpend').val();
							$('.currentCr2').text();
							$('.currentSold2').text();
							$('.eUnit').text();
							$('.estDay60').text();
							$('.conversionComplete').text();
							$('.dailyComplete').text();
							$('.eComplete').text();
							$('.investmentCycle2').text();
							$('.remarks').text();
							
						})
					} */
				},
				
			], 
			
		});

	})
</script>
@endsection