@extends('layouts.layout')
@section('crumb')
    <a href="/hijack/index">Hijack Alerts</a>
@endsection
@section('content')
<style>
		
		table thead tr th{
			text-align: center !important;
		}
		table.dataTable tbody th,
		table.dataTable tbody td {
			padding: 8px 10px;	
		}
		table.dataTable tr{
			border-bottom: 1px solid #eee;
		}
		table.dataTable.stripe tbody tr.odd, table.dataTable.display tbody tr.odd{
			background: #fff;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button{
			padding: 0.2em .5em;
		}
		.content {
			padding-top: 20px;
			overflow: hidden;
		}
		.tabs{
			width: 30%;
			float: left;
			margin: 20px;
			background: #fff;
		}
		.tabs_list{
			width: 63%;
			float: right;
			margin: 20px;
			background: #fff;
		}
		#tabsObj,#listObj{
			text-align: center;
		}
		.detail_label{
			float: left;
			height: 50px;
			width: 50px;
			margin: 5px 20px;
		}
		.detail_span{
			float: left;
			display: inline-block;
			line-height: 24px;
			width: 1100px;
		}
		.w200{
			min-width: 200px;
		}
		.w8{
			min-width: 80px;
		}
		.w6{
			min-width: 60px;
		}
		.detail_label img{
			width: 100%;
			height: 100%;
		}
		.product_title{
			-webkit-box-orient: vertical;
			line-height: 18px;
			overflow: hidden;
			text-overflow: ellipsis;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			margin: 0;
			font-size: 14px;
		}
		.product_span{
			margin-top: 5px;
		}
		.product_span span{
			font-size: 12px;
		}
		.product_span img{
			margin-right: 5px;
		}
		.product_data{
			overflow: hidden;
		}
		.product_data > p{
			float: left;
			margin: 0 80px 0 0;
			font-size: 14px;
		}
		.handlerSearch{
			width: 70px;
			height: 30px;
			background: none;
			border: 1px solid #ccc
		}
		table thead tr,#listObj_wrapper,#tabsObj_wrapper{
			background: #EEF1F5;
		}
		.country{
			background: #2096fa;
			color: #fff;
			padding: 2px 7px;
		}
	</style>
	<div class="content">
		<div style="overflow: hidden;">
			<div class="detail_label">
				<img src="" class="product_img" alt="">
			</div>
			<div class="detail_span">
				<p class="product_title"></p>
				<p class="product_span">
					<span class="country"></span>
					<span class="span1"></span>
					/
					<span class="span2"></span>
				</p>
				<div class="product_data">
					<p><label for="">Last Updated:</label><span class="times"></span></p>
					<p><label for="">Hijackers:</label><span class="number"></span></p>
					<p><label for="">SKU Status:</label><span class="status"></span></p>
					<p><label for="">Seller:</label><span class="prople"></span></p>
				</div>
			</div>
		</div>
		<div style="overflow: hidden; margin-top: 20px;">
			<div class="col-md-2">
			    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
			        <input type="text" class="form-control form-filter input-sm date1" readonly name="date_from" placeholder="From" value="">
			        <span class="input-group-btn">
						<button class="btn btn-sm default" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
			    </div>
			</div>
			<div class="col-md-2">
			    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
			        <input type="text" class="form-control form-filter input-sm date2" readonly name="date_to" placeholder="To" value="">
			        <span class="input-group-btn">
						<button class="btn btn-sm default" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
			    </div>
			</div>
			<div class="col-md-2">
				<button class="handlerSearch">Search</button>
			</div>
		</div>
		<div style="border-top: 1px solid #eee;margin-top: 30px; overflow: hidden;">
			<div class="tabs">
				<table id="tabsObj" class="display table-striped table-bordered table-hover" style="width:100%">
					<thead>
						<tr>
							<th>Time Checked</th>
							<th>Hijackers</th>
						</tr>
					</thead>
				</table>
			</div>
			<div class="tabs_list">
				<table id="listObj" class="display table-striped table-bordered table-hover" style="width:100%">
					<thead>
						<tr>
							<th class="w6">Seller</th>
							<th class="w6">Seller ID</th>
							<th class="w6">Price</th>
							<th class="w8">Delivery</th>
							<th class="w6">Duration of Hijacking</th>
							<th class="w200">Notes</th>
						</tr>
					</thead>
				</table>
			</div>
				
		</div>
		
	</div>
	
	<script>
		$(document).ready(function () {
			let tableObj , ids , urlIndex , detailId , listObj,time1,time2;
			ids = window.location.href
			urlIndex=ids.lastIndexOf("=");
			ids=ids.substring(urlIndex+1,ids.length);
			
			//左边table
			tableObj = $('#tabsObj').DataTable({
				"searching": false,  //去掉搜索框
				"bLengthChange": false, //去掉每页多少条框体
				"paging": true,  // 是否显示分页
				"info": false,// 是否表格左下角显示的文字
				"pageLength": 10,
				"ordering": false,
				"pagingType": 'numbers',
				columns: [
					{ data: "reselling_time",},
					{ data: "reselling_num" },
				],
				"serverSide": false,
				ajax: {
					url: "http://10.10.42.14/vl/public/hijack/resellingList",
					type: "post",
					data : function(){
						reqList = {
							"id" : ids,
							"startTime": time1,
							"endTime":time2
						};
						return reqList;
					},
					dataSrc:function(res){
						let dataList
						let product = res[0][0]
						if(product.images != null){
							str = product.images;
							dot = str.split(',');
							dot.length > 1 ? img = 'https://images-na.ssl-images-amazon.com/images/I/' + dot[1] : img = ''
						}
						$('.product_title').text(product.title);
						$('.span1').text(product.asin);
						$('.span2').text(product.sku);
						$('.product_img').attr("src",img);
						$('.country').text(product.domin_sx)
						$('.times').text(product.asin_reselling_time);
						$('.number').text(product.asin_reselling_num);
						$('.status').text(product.sku_status);
						$('.prople').text(product.user_name); 
						res[1][0] != undefined ? detailId = res[1][0].id : detailId = ''	
						listObj.ajax.reload()
						return dataList = res[1];
					}
				},
				data: [],
				"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {  //行回调函数
					$(nRow).on( 'click', function () {
						detailId = aData.id
						var rowdata = {"taskId" : detailId,};
						listObj.ajax.reload()
					});
				},
			});
			

			//右边table
			listObj = $('#listObj').DataTable({
				"searching": false,  //去掉搜索框
				"bLengthChange": false, //去掉每页多少条框体
				"paging": false,  // 是否显示分页
				"info": false,// 是否表格左下角显示的文字
				"serverSide": true,
				"ordering": false, // 禁止排序
				"ajax": {
					url: "http://10.10.42.14/vl/public/hijack/resellingDetail",
					type: "post",
					data : function(){
						rowdata = {"taskId" : detailId};
						return rowdata;
					},
					dataSrc:function(res){
						return res
					}
				},
				"columns": [
					{ "data": "account"},
					{ "data": "sellerid"},
					{ "data": "price"},
					{ "data": "shipping_fee"},
					{ "data": "timecount"},
					{ "data": "reselling_remark"},
				],
				data: [],
				columnDefs: [
					{
						"targets": [5],
						render: function (data, type, row) {
							return '<div><span>'+data+'</span><img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
						},
						
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).click(function (e) {
								$(this).html('<input type="text" size="16" style="width: 100%"/>');
								var aInput = $(this).find(":input");
								aInput.focus().val(cellData);
							});
							$(cell).on("blur", ":input", function (e) {
								var text = $(this).val();
								if($(this).val() != cellData){
									$(cell).html(text);
									listObj.cell(cell).data(text);
									$.ajax({
										type:"post",
										url:'http://10.10.42.14/vl/public/hijack/upResellingDetail',
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
								}
								
								
							})
						}
					}
				],
			});
			
			//时间选择器
			function initPickers() {
			    $('.date-picker').datepicker({
			        rtl: App.isRTL(),
			        autoclose: true
			    });
			}
			initPickers();
			//时间戳转换
			function dateStr(str){
				str = str.replace(/-/g,'/'); // 将-替换成/，因为下面这个构造函数只支持/分隔的日期字符串
				return  Math.round(new Date(str).getTime()/1000); // 构造一个日期型数据，值为传入的字符串
			}
			//搜索
			$('.handlerSearch').click(function(){
				time1 = dateStr($('.date1').val());
				time2 = dateStr($('.date2').val());
				tableObj.ajax.reload();
				
			})
			
		})
	</script>

@endsection
