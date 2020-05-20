@extends('layouts.layout')
@section('crumb')
    <a href="/manageDistributeTime/internationalTransportTime">International transport Time</a>
@endsection
@section('content')
<style>
	.title{
		font-size: 17px;
		font-weight: bold;
		padding: 5px 0;
	}
	.content{
		padding: 15px 40px 15px 40px;
		overflow: hidden;
		border-radius: 4px !important;
		background-color: rgba(255, 255, 255, 1);
		margin-bottom: 20px;
	}
	.filter_box{
		overflow: hidden;
		padding-bottom: 20px;
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
		width: 500px;
		height: 386px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 40px 60px;
		margin-top: -193px;
		margin-left: -250px;
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
	table.dataTable thead td, table.dataTable thead th{
		text-align: center;
	}
	.mask-dialog table.table-bordered.dataTable th, table.table-bordered.dataTable td{
		padding: 4px;
	}
	.comparisonTable{
		display: inline-block;
		cursor: pointer;
		padding: 8px;
	}
	.mask-dialog .dataTables_scrollHead,#logisticsTable_filter{
		display: none;
	}
	.batch_list{
		border: 1px solid rgba(220, 223, 230, 1);
		width: 250px;
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
		margin: 10px 0;
	}
	.batch_list li span{
		display: inline-block;
		width: 60px;
		text-align: right;
		margin-right: 10px;
	}
	.batch_list li button{
		color: #FFFFFF;
		border: none;
		height: 25px;
		width: 60px;
		margin-left: 10px;
		background: #1BBC9B;
	}
	.batch_list:after{
		position: absolute;
		top: 24px;
		left: 30px;
		right: auto;
		display: inline-block !important;
		border-right: 7px solid transparent;
		border-bottom: 7px solid #fff;
		border-left: 7px solid transparent;
		content: '';
		box-sizing: border-box;
	}
	.table-scrollable{
		background: #fff;
	}
	.fa-angle-down:before{
		padding-left: 5px;
	}
	.batch_list_input{
		width: 90px;
		padding-left: 8px;
	}
	.batch_operation{
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
	<h4 class="title">物流时效管理</h4>
	<div class="content">
		<div class="filter_box">
			<div class="filter_option">
				<label for="factorySelect">工厂</label>
				<select id="factorySelect" onchange="status_filter(this.value,1)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="logisticsSelect">物流商</label>
				<select id="logisticsSelect" onchange="status_filter(this.value,2)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="typeSelect">运输方式</label>
				<select id="typeSelect" onchange="status_filter(this.value,4)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="regionSelect">地区</label>
				<select id="regionSelect" onchange="status_filter(this.value,5)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="usernameSelect">用户名</label>
				<select id="usernameSelect" onchange="status_filter(this.value,12)">
					<option value ="">全部</option>
				</select>
			</div>
			<div style="float: left;margin-top: 35px;">
				<span style="color: #3598dc;" class="comparisonTable">运输方式对照表</span>
			</div>
			<div style="float: right;margin-top: 30px;">
				<button class="clear">清空筛选</button>
				<button id="export" class="btn sbold blue" style="margin-bottom: 4px;border-radius: 4px !important;"> Export
					<i class="fa fa-download"></i>
				</button>
			</div>
		</div>
		
		<div style="height: 45px;">
			<div style="z-index: 999;padding-left:0; position: absolute;" class="col-md-2">
				<button type="button" class="btn btn-sm green-meadow batch_operation">批量操作<i class="fa fa-angle-down"></i></button>
				<ul class="batch_list">
					<li><span>ETD:</span><input type="number" class="batch_list_input"><button class="batch_list_button">change</button></li>
					<li><span>ETA:</span><input type="number" class="batch_list_input"><button class="batch_list_button">change</button></li>
					<li><span>清关日期:</span><input type="number" class="batch_list_input"><button class="batch_list_button">change</button></li>
					<li><span>派送日期:</span><input type="number" class="batch_list_input"><button class="batch_list_button">change</button></li>
				</ul>
			</div>
			<div class="col-md-10" style="float: right;padding-right: 0;">
			    <div class="col-md-4">        
			    </div>
				<div class="col-md-2">
				</div>
				<form action="{{url('review/upload')}}" method="post" enctype="multipart/form-data">
				<div class="col-md-2" style="text-align:right;" >
					<a href="{{ url('/uploads/reviewUpload/review_customers.csv')}}" >Import Template
			            </a>	
				</div>
				<div class="col-md-2">
					<input type="file" name="importFile"  />
				</div>
				<div class="col-md-2" style="text-align: right;padding-right: 0;">
					<button type="submit" class="btn blue" id="data_search">导入</button>
				</div>
				</form>
			</div>
			
		</div>
		
		
	</div>
	
	<table id="logisticsTable" class="display table-striped table-bordered table-hover" style="width:100%">
		<thead>
			<tr style="text-align: center;">
				<th><input type="checkbox" id="selectAll" /></th>
				<th>工厂</th>
				<th>物流商</th>
				<th>运输方式代码</th>
				<th>运输方式</th>
				<th>地区</th>
				<th>ETD</th>
				<th>ETA</th>
				<th>清关日期</th>
				<th>派送日期</th>
				<th>FBA签收日期</th>
				<th>总时效</th>
				<th>维护人</th>
				<th>维护日期</th>
				<th>
					是否默认
					<span title="每个工厂都必须要有一个默认的运输方式，作为协同补货时效的参考">
						<svg t="1589247400458" class="icon tipsIcon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2166" width="18" height="18"><path d="M505.306 917.208c-221.127 0-400.391-179.155-400.391-400.288 0-221.127 179.263-400.424 400.391-400.424 221.093 0 400.319 179.296 400.319 400.424 0 221.133-179.227 400.288-400.319 400.288v0zM505.306 76.453c-243.249 0-440.402 197.184-440.402 440.468 0 243.144 197.151 440.332 440.402 440.332 243.179 0 440.362-197.182 440.362-440.332-0.001-243.283-197.183-440.468-440.362-440.468v0zM549.098 296.481c-15.547 0-28.898 5.091-39.974 15.137-11.144 10.043-16.684 22.155-16.684 36.327s5.539 26.281 16.684 36.189c11.076 10.046 24.427 14.998 39.974 14.998 15.513 0 28.828-4.953 39.836-14.998 11.008-9.907 16.545-22.016 16.545-36.189 0-14.174-5.537-26.284-16.545-36.327-11.009-10.046-24.323-15.137-39.836-15.137v0zM564.132 697.32c-12.283 0-20.918-1.929-25.906-5.779-4.953-3.99-7.462-11.285-7.462-22.016 0-4.403 0.756-10.732 2.338-19.13 1.512-8.389 3.234-15.825 5.159-22.289l23.669-81.461c2.268-7.432 3.885-15.547 4.747-24.495 0.862-8.943 1.31-15.273 1.31-18.852 0-17.062-6.227-30.959-18.615-41.692-12.383-10.732-29.997-16.099-52.802-16.099-12.661 0-26.145 2.201-40.352 6.605-14.172 4.266-29.067 9.633-44.615 15.825l-6.33 25.18c4.575-1.789 10.147-3.439 16.545-5.365 6.469-1.927 12.797-2.889 18.885-2.889 12.557 0 20.986 2.201 25.424 6.192 4.403 4.128 6.637 11.422 6.637 21.877 0 5.783-0.723 12.112-2.167 19.125-1.446 7.023-3.234 14.449-5.367 22.157l-23.737 81.739c-2.131 8.529-3.645 16.234-4.607 22.974-0.965 6.882-1.413 13.492-1.413 20.092 0 16.651 6.365 30.551 19.127 41.423 12.728 10.868 30.652 16.374 53.598 16.374 15 0 28.14-1.929 39.456-5.783 11.32-3.709 26.459-9.352 45.443-16.51l6.328-25.18c-3.265 1.375-8.529 3.164-15.785 5.229-7.296 1.788-13.763 2.75-19.508 2.75v0z" p-id="2167" fill="#bfbfbf"></path></svg>
					</span>
				</th>
			</tr>
		</thead>
		
	</table>
</div>
<div class="mask_box">
	<div class="mask-dialog">
		<svg t="1588919283810" class="icon cancel_mask cancel_btn" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
		<table id="typeTable" class="display table-striped table-bordered table-hover" style="width:100%;margin-top: 10px;">
		</table>
	</div>
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
			status_filter('',1)
			status_filter('',2)
			status_filter('',4)
			status_filter('',5)
			status_filter('',12)
			//tableObj.ajax.reload();
		})
		//全选
		$("#selectAll").on('change',function(e) {  
		    $("input[name='checkedInput']").prop("checked", this.checked);
			isBatchOperationShow()
			//let checkedBox = $("input[name='checkedInput']:checked");
		});  
		//单条选中
		$("body").on('change','.checkbox-item',function(){
			var $subs = $("input[name='checkedInput']");
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 
			isBatchOperationShow()
			//let checkedBox = $subs.filter(":checked");
		});
		//批量编辑
		$('.change').on('click',function(){
					
		})
		//当有数据选中时展示批量操作按钮
		function isBatchOperationShow(){
			let checkbox_list = [];
			$("input[name='checkedInput']:checked").each(function () {
				checkbox_list.push($(this).val());		 		 			
			});
			if(checkbox_list.length < 1){
				$('.batch_operation').hide();
			}else{
				$('.batch_operation').show();
			} 
		}
		
		//批量操作下拉框
		$('.batch_operation').click(function(e){
			$('.batch_list').slideToggle();
			e.stopPropagation();
			
			/* $(document).one('click',function(){
				$('.batch_list').hide();
			}) */
			
		})
		$('.batch_list_button').on('click',function(e){
			let checkbox_list = [];
			$("input[name='checkedInput']:checked").each(function () {
				checkbox_list.push($(this).val());		 		 			
			});
			console.log(checkbox_list)
			$('.batch_list').hide();
			e.stopPropagation();
		})
		$('.cancel_mask').on('click',function(){
			$('.mask_box').hide();
		})
		$('.comparisonTable').on('click',function(){
			$('.mask_box').show();
		})
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		tableObj = $('#logisticsTable').DataTable({
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			dispalyLength: 2, // default record count per page
			paging: true,  // 是否显示分页
			info: false,// 是否表格左下角显示的文字
			ordering: false,
			serverSide: false,//是否所有的请求都请求服务器	
			scrollX: "100%",
			scrollCollapse: false,
			ajax: {
				url: "",
				type: "post",
				data : function(){
					reqList = {
						/* "sap_seller_id" : sap_seller_id,
						"created_at_s": cusstr($('.createTimeInput').val() , ' - ' , 1),
						"created_at_e": cusstr1($('.createTimeInput').val() , ' - ' , 1),
						"from_time": cusstr($('.estTimeInput').val() , ' - ' , 1),
						"to_time": cusstr1($('.estTimeInput').val() , ' - ' , 1),
						"condition": $('.keyword').val(), */
					};
					return reqList;
				},
				dataSrc:function(res){
					console.log(res)
					return res;
				},
			},			
			data: [
				{
					factory: 'US01',
					logisticsProvider: 'SZ-BAIYSHH',
					transportCode: '70',
					typeOfShipping: '海运+卡车',
					region: '美东',
					etd: '7',
					eta: '15',
					customsClearanceDate: '5',
					deliveryDate: '16',
					fbaSignInDate: '3',
					totalAging: '46',
					maintainer: 'WM0004',
					maintenanceDate: '2019-4-15',
					idDefault: '是',
					id:3,
				},
				{
					factory: 'US01',
					logisticsProvider: 'SZ-BAIYSHH',
					transportCode: '70',
					typeOfShipping: '海运+卡车',
					region: '美东',
					etd: '7',
					eta: '15',
					customsClearanceDate: '5',
					deliveryDate: '16',
					fbaSignInDate: '3',
					totalAging: '46',
					maintainer: 'WM0004',
					maintenanceDate: '2019-4-15',
					idDefault: '是',
					id:4,
				},
				{
					factory: 'US01',
					logisticsProvider: 'SZ-BAIYSHH',
					transportCode: '70',
					typeOfShipping: '海运+卡车',
					region: '美东',
					etd: '7',
					eta: '15',
					customsClearanceDate: '5',
					deliveryDate: '16',
					fbaSignInDate: '3',
					totalAging: '46',
					maintainer: 'WM0004',
					maintenanceDate: '2019-4-15',
					idDefault: '是',
					id:5,
				}
			],
			/* "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {  //行回调函数
				$(nRow).on( 'click', function () {
					console.log(aData.id);
					$('.mask_box').show();
					$('.formId').val(aData.id)
				});
			}, */
			columns: [
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
					data: "factory",
				},
				{
					data: 'logisticsProvider',
				},
				{
					data: 'transportCode',
				},
				{
					data: 'typeOfShipping',
				},
				{
					data: 'region',
				},
				{
					data: 'etd',
				},
				{
					data: 'eta',
				},
				{ 
					data: 'customsClearanceDate',
				},
				{
					data: 'deliveryDate',
				},
				{
					data: 'fbaSignInDate',
				},
				{
					data: 'totalAging',
				},
				{
					data: "maintainer",
				},
				{
					data: "maintenanceDate",
				},
				{
					data: "idDefault",
				},
				
			], 
			columnDefs: [
				{
					"targets": [6],
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
				{
					"targets": [7],
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
				{
					"targets": [8],
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
				{
					"targets": [9],
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
				{
					"targets": [14],
					render: function (data, type, row) {
						return '<div><span>'+data+'</span><img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
					},
			
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function (e) {
							$(this).html('<input type="number" size="16" style="width: 100%"/>');
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
		
		$('#typeTable').DataTable({
			searching : false,
			info: false,
			ordering: false,
			serverSide: false,
			bLengthChange: false,
			scrollX: "100%",
			paging: false,
			data: [
				{
					id:"运输方式ID",
					type: '方式',
				},
				{
					id:10,
					type: '空运',
				},
				{
					id:20,
					type: '海运',
				},
				{
					id:30,
					type: '空运&海运',
				},
				{
					id:40,
					type: '快递',
				},
				{
					id:50,
					type: '铁路',
				},
				{
					id:60,
					type: '陆运',
				},
				{
					id:70,
					type: '海运+卡车',
				},
				{
					id:80,
					type: '海运+快递',
				},
				{
					id:90,
					type: '美森快船',
				},
			],	
			columns: [
				{
					data: "id" ,
				},
				{
					data: 'type',
				},
				
			]
		});
	})
</script>

@endsection