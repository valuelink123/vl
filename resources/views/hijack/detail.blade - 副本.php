@extends('layouts.layout')
@section('content')
<style>
		body {
			font: 90%/1.45em "Helvetica Neue", HelveticaNeue, Arial, Helvetica, sans-serif;
			margin: 0;
			padding: 0;
			color: #333;
			background-color: #fff;
		}

		table.dataTable tbody th,
		table.dataTable tbody td {
			padding: 8px 10px;
		}
		table.dataTable tr{
			border-bottom: 1px solid #eee;
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
			width: 65%;
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
					<img src="../assets/global/img/es.png" alt="" class="country_img">
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
		<!-- <div>
			<div class="col-md-2">
			    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
			        <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From" value="">
			        <span class="input-group-btn">
						<button class="btn btn-sm default" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
			    </div>
			</div>
			<div class="col-md-2">
			    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
			        <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To" value="">
			        <span class="input-group-btn">
						<button class="btn btn-sm default" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
			    </div>
			</div>
		</div> -->
		<div style="overflow: hidden;">
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
		<div style="border-top: 1px solid #eee;margin-top: 30px;background: #f5f5f5; overflow: hidden;">
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
							<th class="w6">Notes</th>
						</tr>
					</thead>
				</table>
			</div>
				
		</div>
		
	</div>
	
	<script>
		$(document).ready(function () {
			
			let tableObj , ids , urlIndex , detailId , listObj;
			ids = window.location.href
			urlIndex=ids.lastIndexOf("=");
			ids=ids.substring(urlIndex+1,ids.length);
			function initPickers() {
			    //init date pickers
			    $('.date-picker').datepicker({
			        rtl: App.isRTL(),
			        autoclose: true
			    });
			}
			initPickers();
			function dateStr(str){
				str = str.replace(/-/g,'/'); // 将-替换成/，因为下面这个构造函数只支持/分隔的日期字符串
				return new Date(str).getTime(); // 构造一个日期型数据，值为传入的字符串
			}
			$('.handlerSearch').click(function(){
				let time1 = $('.date1').val();
				let time2 = $('.date2').val();
				let startTime = dateStr(time1);
				let endTime = dateStr(time2);
				var reqList = {
					"id": detailId,
					"startTime": startTime,
					"endTime": endTime
				}
				console.log(reqList)
				var tableList = $("#tabsObj").DataTable();
				tableList.ajax.reload();
			})
			
			function getDetailList(detailId){
				$.ajax({
					type:"post",
					url:'http://10.10.42.14/vl/public/hijack/resellingList',
					data:{
						"id" : ids,
					},
					async:false,
					error:function(err){
					    console.log(err);
					},
					success:function(res){
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
						$('.times').text(product.asin_reselling_time);
						$('.number').text(product.asin_reselling_num);
						$('.prople').text(product.user_name); 
						detailId = res[1][0].id	
					}
				});
				return detailId
			}
			getDetailList()
			detailId = getDetailList()
			

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
				"ajax": {
					url: "http://10.10.42.14/vl/public/hijack/resellingList",
					type: "post",
					data : function(){
						reqList = {
							"id" : detailId,
							"startTime": '',
							"endTime":''
						};//这里可以调用一个方法，获取rowdata
						return reqList;
					},
					dataSrc:function(res){
						let dataList
						return dataList = res[1];
					}
				},
	  			data: [],
				"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {  //行回调函数
					$(nRow).on( 'click', function () {
						detailId = aData.id
						var rowdata = {"taskId" : detailId,};
						var table = $("#listObj").DataTable()
						table.ajax.reload()
					});
				},
			});
			
			
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
						rowdata = {"taskId" : detailId};//这里可以调用一个方法，获取rowdata
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
						createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
							$(cell).click(function () {
								$(this).html('<input type="text" size="16" style="width: 100%"/>');
								var aInput = $(this).find(":input");
								aInput.focus().val(cellData);
							});
							$(cell).on("blur", ":input", function () {
								var text = $(this).val();
								$(cell).html(text);
								listObj.cell(cell).data(text)
								console.log(111,cell, cellData, rowData, rowIndex, colIndex)
								$.ajax({
									type:"post",
									url:'http://10.10.42.14/vl/public/hijack/upResellingDetail',
									data:{
										id: rowData.id,
										remark: rowData.reselling_remark
									},
									error:function(err){
									    console.log(err);
									},
									success:function(res){
										console.log(res)
									}
								});
								
							})
						}
					}
				],
			});
			
		})
	</script>

@endsection
