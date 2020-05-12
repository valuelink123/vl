@extends('layouts.layout')
@section('crumb')
    <a href="/manageDistributeTime/fbm">FBM-FBATransfer time</a>
@endsection
@section('content')
<style>
	.title{
		font-size: 17px;
		font-weight: bold;
		padding: 5px 0;
	}
	.content{
		padding: 15px 40px 30px 40px;
		overflow: hidden;
		border-radius: 4px !important;
		background-color: rgba(255, 255, 255, 1);
		margin-bottom: 20px;
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
	table.dataTable thead td, table.dataTable thead th{
		text-align: center;
	}
	.table-scrollable{
		background: #fff;
	}
	div.dataTables_wrapper div.dataTables_filter{
		display: none;
	}
	.clear{
		width: 90px;
		height: 34px;
		background-color: #909399;
		border: 1px solid #909399;
		border-radius: 4px !important;
		outline: none;
		color: #fff;
		margin-right: 20px;
	}
	.tipsIcon{
		margin-bottom: -4px;
	}
	.tipsIcon:hover path{
		fill: red;
	}
</style>
<div>
	<h4 class="title">仓库调拨时效管理</h4>
		<div class="content">
			<div class="filter_box">
				<div class="filter_option">
					<label for="stationSelect">站点</label>
					<select id="stationSelect" onchange="status_filter(this.value,0)">
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
					<label for="skuStatusSelect">出库工厂</label>
					<select id="skuStatusSelect" onchange="status_filter(this.value,1)">
						<option value ="">全部</option>
					</select>
				</div>
				<div class="filter_option">
					<label for="skuStatusSelect">收货工厂</label>
					<select id="skuStatusSelect" onchange="status_filter(this.value,2)">
						<option value ="">全部</option>
					</select>
				</div>
				<div class="filter_option">
					<label for="skuGradeSelect">维护人</label>
					<select id="skuGradeSelect" onchange="status_filter(this.value,4)">
						<option value ="">全部</option>
					</select>
				</div>
				<div style="float: right;margin-top: 30px;">
					<button class="clear">清空筛选</button>
					<button id="export" class="btn sbold blue" style="margin-bottom: 4px;border-radius: 4px !important;"> Export
						<i class="fa fa-download"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
	<table id="fbmTable" class="display table-striped table-bordered table-hover" style="width:100%;margin-top: 10px;">
		<thead>
			<tr style="text-align: center;">
				<th>站点</th>
				<th>出库工厂</th>
				<th>收货工厂</th>
				<th>
					调拨时效(天)
					<span title="从接到调拨请求到货物发出的时间">
						<svg t="1589247400458" class="icon tipsIcon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2166" width="18" height="18"><path d="M505.306 917.208c-221.127 0-400.391-179.155-400.391-400.288 0-221.127 179.263-400.424 400.391-400.424 221.093 0 400.319 179.296 400.319 400.424 0 221.133-179.227 400.288-400.319 400.288v0zM505.306 76.453c-243.249 0-440.402 197.184-440.402 440.468 0 243.144 197.151 440.332 440.402 440.332 243.179 0 440.362-197.182 440.362-440.332-0.001-243.283-197.183-440.468-440.362-440.468v0zM549.098 296.481c-15.547 0-28.898 5.091-39.974 15.137-11.144 10.043-16.684 22.155-16.684 36.327s5.539 26.281 16.684 36.189c11.076 10.046 24.427 14.998 39.974 14.998 15.513 0 28.828-4.953 39.836-14.998 11.008-9.907 16.545-22.016 16.545-36.189 0-14.174-5.537-26.284-16.545-36.327-11.009-10.046-24.323-15.137-39.836-15.137v0zM564.132 697.32c-12.283 0-20.918-1.929-25.906-5.779-4.953-3.99-7.462-11.285-7.462-22.016 0-4.403 0.756-10.732 2.338-19.13 1.512-8.389 3.234-15.825 5.159-22.289l23.669-81.461c2.268-7.432 3.885-15.547 4.747-24.495 0.862-8.943 1.31-15.273 1.31-18.852 0-17.062-6.227-30.959-18.615-41.692-12.383-10.732-29.997-16.099-52.802-16.099-12.661 0-26.145 2.201-40.352 6.605-14.172 4.266-29.067 9.633-44.615 15.825l-6.33 25.18c4.575-1.789 10.147-3.439 16.545-5.365 6.469-1.927 12.797-2.889 18.885-2.889 12.557 0 20.986 2.201 25.424 6.192 4.403 4.128 6.637 11.422 6.637 21.877 0 5.783-0.723 12.112-2.167 19.125-1.446 7.023-3.234 14.449-5.367 22.157l-23.737 81.739c-2.131 8.529-3.645 16.234-4.607 22.974-0.965 6.882-1.413 13.492-1.413 20.092 0 16.651 6.365 30.551 19.127 41.423 12.728 10.868 30.652 16.374 53.598 16.374 15 0 28.14-1.929 39.456-5.783 11.32-3.709 26.459-9.352 45.443-16.51l6.328-25.18c-3.265 1.375-8.529 3.164-15.785 5.229-7.296 1.788-13.763 2.75-19.508 2.75v0z" p-id="2167" fill="#bfbfbf"></path></svg>
					</span>
				</th>
				<th>维护人</th>
				<th>维护日期</th>
			</tr>
		</thead>
	</table>
</div>
<script>
	//正则判断输入整数
	function validataInt(ob) {
		if(ob.value.length==1){
			ob.value=ob.value.replace(/[^1-9]/g,'')
		}else{
			ob.value=ob.value.replace(/\D/g,'')
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
		//清空筛选
		$('.clear').on('click',function(){
			status_filter('',0)
			status_filter('',1)
			status_filter('',2)
			status_filter('',4)
			//tableObj.ajax.reload();
		})
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		
		tableObj = $('#fbmTable').DataTable({
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			order: [ 5, "desc" ],
			dispalyLength: 2, // default record count per page
			paging: true,  // 是否显示分页
			info: false,// 是否表格左下角显示的文字
			serverSide: false,//是否所有的请求都请求服务器	
			scrollX: "100%",
			scrollCollapse: false,
			/* ajax: {
				url: "",
				type: "post",
				data : function(){
					reqList = {
						"sap_seller_id" : 1,
					};
					return reqList;
				},
				dataSrc:function(res){
					console.log(res)
					//return res
				},
			}, */	
			data: [
				{
					id:1,
					station: 'US',
					outboundFactory: 'US02',
					receivingFactory: 'US1',
					day: 5,
					maintainer: 'nana',
					maintenanceDate: '2020-05-20',
				},
				{
					id:2,
					station: 'US',
					outboundFactory: 'US02',
					receivingFactory: 'US1',
					day: 5,
					maintainer: 'nana',
					maintenanceDate: '2020-05-20',
				},
				{
					id:3,
					station: 'US',
					outboundFactory: 'US02',
					receivingFactory: 'US1',
					day: 5,
					maintainer: 'nana',
					maintenanceDate: '2020-05-20',
				}
			],	
			columns: [
				{
					data: "station" ,
				},
				{
					data: 'outboundFactory',
				},
				{
					data: 'receivingFactory',
				},
				{
					data: "day",
				},
				{
					data: "maintainer",
				},
				{
					data: "maintenanceDate",
				},
			], 
			columnDefs: [
				{ "bSortable": false, "aTargets": [ 0,1,2,3,4]},
				{
					"targets": [3],
					render: function (data, type, row) {
						return '<div><span>'+data+'</span><img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
					},
			
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function (e) {
							$(this).html('<input type="text" onkeypress="validataInt(this)" οnkeyup="validataInt(this)" size="16" style="width: 100%"/>');
							var aInput = $(this).find(":input");
							aInput.focus().val(cellData);
						});
						$(cell).on("blur", ":input", function (e) {
							var text = $(this).val();
							if($(this).val() != cellData){
								$(cell).html(text);
								tableObj.cell(cell).data(text);
								$.ajax({
									type:"post",
									url:'',
									data:{
										id: rowData.id,
									},
									error:function(err){
									    console.log(err);
									},
									success:function(res){
										tableObj.ajax.reload()
									}
								});
							}else{
								$(cell).html(text);
								tableObj.cell(cell).data(text);
							}
						})
					}
				},
			],
		});
	})
</script>
@endsection