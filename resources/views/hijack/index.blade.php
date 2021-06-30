@extends('layouts.layout')
@section('label', 'Asin Reselling')
@section('content')
 <style>
		#tableObj thead th{
			text-align: center !important;
		}
 		table.dataTable tbody th,
 		table.dataTable tbody td {
 			padding: 8px 10px;
 		}
		table.dataTable.stripe tbody tr.odd, table.dataTable.display tbody tr.odd{
			background: #fff;
		}
		#tableObj_wrapper > .row:first-child{
			display: none !important;
		}
		.content{
			padding-top: 10px;
		}
 		.dataTable {
 			text-align: center;
 		}
 
 		.search_main {
 			overflow: hidden;
 		}
 		.search_input {
 			width: 100%;
 			height: 38px;
 			border-radius: 2px;
 			border: 1px solid #ddd;
 			float: left;
			padding-left: 20px;
 		}
 		.search_btn{
			width: 68px;
			height: 38px;
 			border-radius: 5px;
 			border: 1px solid #ddd;
 			background: #fff;
 			float: left;
 			margin-left: 20px;
 			color: #666666;
 		}
		.export_btn{
			float: right !important;
			outline: none;
		}
 		.form_main {
 			margin-top: 15px;
 			overflow: hidden;
 		}
 
 		.form_main > select {
 			height: 42px;
 			border: 1px solid #ddd;
 			border-radius: 4px;
 			background: #fff;
 			width: 140px;
 			margin-right: 10px;
 			float: left;
			padding-left: 10px;
 		}
 
 		.form_main button {
 			width: 80px;
 			height: 42px;
 			border: none;
 			background: #2096fa;
 			color: #fff;
 			float: left;
 			margin-right: 10px;
 		}
 
 		.no-footer {
 			/* position: relative; */
 			padding-top: 15px !important;
 		}
 		.start_btn{
 			background: #61c737 !important;
 		}
 		.close_btn{
 			background: #eb5151 !important;
 		}
 		.isShow{
 			display: block;
 		}
 		.isHide{
 			display: none;
 		}
 		.switch_btn{
 			display: inline-block;
 			width: 20px;
 			height: 20px;
 		}
 		.link_btn{
 			display: inline-block;
 			width: 40px;
 			height: 20px;
 		}
 		/* switch开关 */
		.switch{
			cursor: pointer;
			width:40px;
			height:20px;
			border-radius:30px !important;
			overflow: hidden;
			vertical-align:middle;
			position:relative;
			display: inline-block;
			background:#ccc;
			box-shadow: 0 0 1px #61c737;
		}
		.switch input{
		  visibility: hidden;
		}
		.switch span{
		  position:absolute;
		  top:0;
		  left:0;
		  border-radius: 50%;
		  width:50%;
		  height:100%;
		  transition:all linear 0.2s;
		}
		.switch span::before{
		  position: absolute;
		  top:0;
		  left:-100%;
		  content:'';
		  width:200%;
		  height:100%;
		  border-radius: 30px;
		  background:#61c737;
		}
		.switch span::after{
		  content:'';
		  position:absolute;
		  left:0;
		  top:0;
		  width:100%;
		  height:100%;
		  border-radius: 50%;
		  background:#fff;
		}
		.switch input:checked +span{
		  transform:translateX(100%);
		}
		
 		.w8{
 			min-width: 80px;
 		}
 		.w6{
 			min-width: 60px;
 		}
 		.isSwitchHide{
 			display: none;
 		}
		table.dataTable.display tbody tr td a,table.dataTable.display tbody tr td a:active,table.dataTable.display tbody tr td a:hover{
			color: #333 !important;
		}
 		.product_main{
 			overflow: hidden;
			margin: auto;
			width: 600px;
 		}
 		.product_img{
 			float: left;
 			width: 50px;
 			height: 50px;
 			margin: 5px auto;
 		}
 		.product_img img{
 			width: 100%;
 			height: 100%;
 		}
 		.product_text{
 			float: right;
 			width: 520px;
 			text-align: left;
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
 		.country_img{
 			margin-right: 10px;
			background: #2096fa;
			color: #fff;
			padding: 2px 5px;
 		}
 		.product_span{
 			margin-top: 5px;
 		}
 		.product_span span{
 			font-size: 12px;
 		}
		.box{
			width: 400px;
			height: 300px;
			position: fixed;
			left: 50%;
			top: 50%;
			margin-left: -200px;
			margin-top: -150px;
			background: #fff;
			border: 1px solid #ccc;
		}
		.input-group{
			margin: 15px 65px;
		}
		.input-group:first-child{
			padding-top: 20px!important;
		}
		.dialogMain{
			width: 100%;
			height: 100%;
			position: fixed;
			display: none;
			top: 0;
			left: 0;
			background: rgba(0,0,0,0.3);
		}
		.box h4{
			height: 45px;
			font-size: 28px;
			padding-left: 30px;
			border-bottom: 1px solid #ccc;
		}
		.box_btn{
			height: 60px;
			margin: 20px 55px 30px 30px;
			padding-top: 20px;
			text-align: right;
		}
		.box_btn button{
			width: 80px;
			height: 36px;
			border-radius: 6px !important;
			border: 1px solid #ccc;
			background: #fff;
			margin-left: 15px;
		}
		.group-checkable{
			z-index: 9999;
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
 
 <body class="dt-tableObj">
 	<div class="content">
		<form id="search-form">
 		<div class="search_main">
			<span style="position: relative;height: 38px;display: inline-block; width: 700px; float: left;">
				<input type="text" name="asin" placeholder="Asin" class="search_input">
			</span>

 			<button class="search_btn" id="search">Search</button>
 		</div>
 		<div class="form_main" style="float:left;">
		 	
 			<select name="bg" class="bgList" >
				<option value="" >All BG</option>
				@foreach($bgs as $key=>$val)
					<option value="{!! $val !!}">{!! $val !!}</option>
				@endforeach
			</select>
			<select name="bu" >
				<option value="">All BU</option>
				@foreach($bus as $key=>$val)
					<option value="{!! $val !!}">{!! $val !!}</option>
				@endforeach
			</select>

			
 			
 			<select name="sku_status" >
				<option value="">All Status</option>
				@foreach($sku_status as $key=>$val)
					<option value="{!! $key !!}">{!! $val !!}</option>
				@endforeach
			</select>
 			<select name="site" >
				<option value="">All Site</option>
				@foreach($site as $key=>$val)
					<option value="{!! $key !!}">{!! $val !!}</option>
				@endforeach
			</select>
			
 			<select name="switchSelect" id="switchSelect">
				<option value="1">On</option>
 				<option value="2">All</option>
 				<option value="3">Off</option>
 			</select>

 			<button class="start_btn status_btn isHide">Turn On</button>
 			<button class="close_btn status_btn isHide">Turn Off</button>
{{--			<button class="export_btn">Export</button>--}}
 		</div>

		 <div class="col-md-2" style="margin-top:15px;">
			<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="All Sellers" name="sap_seller_id" id="sap_seller_id">
			@foreach($users as $key=>$val)
				<option value="{!! $key !!}">{!! $val !!}</option>
			@endforeach
			</select>
		</div>
		</form>
 		<div>
 		
 			<table id="tableObj" class="display table-striped table-bordered table-hover" style="width:100%">
 				<thead>
 					<tr style="text-align: center;">
{{--						<th>BG</th>--}}
{{--						<th>BU</th>--}}
{{--						<th>Marketplace</th>--}}
 						<th><input type="checkbox" id="selectAll" /></th>
 						<th>Product</th>
 						<th class="w8">SKU Status</th>
 						<th class="w6">Seller</th>
 						<th class="w8">Last Updated</th>
 						<th class="w8">Hijackers</th>
 						<th class="w8">Status</th>
 						<th class="w6">Action</th>
 					</tr>
 				</thead>
				
			</table>
 		</div>
 	</div>
	<div class="dialogMain">
		<div class="box">
			<h4>Export</h4>
			<div class="input-group date date-picker margin-bottom-5" style="padding-top: 20px;" data-date-format="yyyy-mm-dd">
				<label style="float: left;width: 40px;line-height: 32px;">From</label>
			    <div style="float: left;">
					<input style="width: 160px;margin-left: 15px;" type="text" class="form-control form-filter input-sm date1" readonly name="date_from" placeholder="From" value="">
					<span class="input-group-btn">
						<button class="btn btn-sm default" type="button">
							<i class="fa fa-calendar"></i>
						</button>
					</span>
				</div>
			</div>
			
			<div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
				<label style="float: left;width: 40px; line-height: 32px;">To</label>
				<div style="float: left;">
					<input type="text" style="width: 160px;margin-left: 15px;" class="form-control form-filter input-sm date2" readonly name="date_to" placeholder="To" value="">
					<span class="input-group-btn">
						<button class="btn btn-sm default" type="button">
							<i class="fa fa-calendar"></i>
						</button></span>
				</div>
			</div>
			<div class="box_btn">
				<button class="handlerCancel">Cancel</button>
				<button class="handlerExport">Export</button>
			</div>
		</div>
	</div>
	<div class="success_mask">
		<span class="mask_icon">
			<svg t="1586572594956" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="12690" width="24" height="24"><path d="M511.1296 0.2816C228.7616 0.2816 0 229.1456 0 511.4368c0 282.2656 228.864 511.1296 511.1296 511.1296 282.2912 0 511.1552-228.864 511.1552-511.1296C1022.2848 229.1712 793.4208 0.256 511.1296 0.256z m-47.104 804.8384l-244.5056-219.9808 72.448-73.2672 145.5872 112.9728c184.832-251.136 346.624-331.776 346.624-331.776l20.1984 30.464c-195.6864 152.192-340.48 481.5872-340.352 481.5872z" fill="#1DC50C" p-id="12691" data-spm-anchor-id="a313x.7781069.0.i18" class="selected"></path></svg>
		</span>
		<span class="mask_text">Updated Successfully.</span>
	</div>
	<div class="error_mask">
		<span class="mask_icon">
			<svg t="1586574167843" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="13580" width="24" height="24"><path d="M512 0A512 512 0 1 0 1024 512 512 512 0 0 0 512 0z m209.204301 669.673978a36.555699 36.555699 0 0 1-51.750538 51.640431L511.779785 563.64043 353.995699 719.662796a36.555699 36.555699 0 1 1-52.301075-51.089893 3.303226 3.303226 0 0 1 0.88086-0.88086L460.249462 511.779785l-157.013333-157.453763a36.665806 36.665806 0 1 1 48.777634-55.053764 37.876989 37.876989 0 0 1 2.972904 2.972903l157.233548 158.114409 157.784086-156.132473a36.555699 36.555699 0 0 1 51.420215 52.08086L563.750538 512.220215l157.013333 157.453763z" fill="#FF5252" p-id="13581"></path></svg>
		</span>
		<span class="mask_text">Failed to Update.</span>
	</div>
 </body>
 
 </html>
 
 <script>
 	$(document).ready(function () {
		let isOpen=1;
		$('#switchSelect').on("change",function(){
			isOpen = $(this).val()
			editTableObj.ajax.reload();
		})
		
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		
		editTableObj = $('#tableObj').DataTable({
			"searching": true,  //去掉搜索框
			"bLengthChange": false, //去掉每页多少条框体
			"paging": true,  // 是否显示分页
			pagingType: 'bootstrap_extended',
			"info": true,// 是否表格左下角显示的文字
			pageLength: 20,
			lengthMenu: [50,100,200],
			order: [[5, 'desc']],
			"ordering": true,
			"serverSide": true,//是否所有的请求都请求服务器
			ajax: {
				type: 'POST',
				url: '/hijack/index1',
				data:  {search: $("#search-form").serialize()},
				dataSrc:function(res){
					if(res.status == -1){
						alert(res.message)
						window.location.href="/service"
					}
					return res.data
				},
				error:function(err){
					console.log(err)
				}

			},
			data: [],
			columns: [
				{
					data: 'id',
					orderable: false,
					bSortable: false,
					render: function(data, type, row, meta) {
						var ids = row.id
						var content = '<input type="checkbox" name="checkedInput"  class="group-checkable" value="' + ids + '" />';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						
					}
				},
				{
					data: null,
					orderable: false,
					bSortable: false,
					render: function (data, type, row) {
						let img,dot,str;
						if(row.images != null){
							str = row.images;
							dot = str.split(',');
							dot.length > 1 ? img = 'https://images-na.ssl-images-amazon.com/images/I/' + dot[0] : img = ''
						}
						return '<a target="_blank" href="detail?id='+row.rla_id+'?name='+row.userName+'"><div class="product_main"><div class="product_img"><img src="'+img+'" alt=""></div><div class="product_text"><p class="product_title" title="'+row.title+'">'+row.title+'</p><div class="product_span"><span class="country_img">'+row.siteShort+'</span><span>'+row.asin+'</span> / <span>'+row.sku+'</span></div></div></div></a>';
					},
				},
				{ 
					data: "sku_status",
					orderable: false,
					bSortable: false,
				},
				{
					data: "userName",
					orderable: false,
					bSortable: false,
				},
				
				{ 
					data: "reselling_time",
				},
				{ 
					data: "hijachers",
				},
				{
					data:'reselling_switch',
					orderable: false,
					bSortable: false,
					render: function (data, type, row, meta) {
						let title = row.reselling_switch == 0 ? "Hijacker monitoring turned off.":"Hijacker monitoring turned on."
						var html = '<label class="switch" title="'+ title + '"> <input type="checkbox" class="switch_input" checked value=""><span>'+row.reselling_switch+'</span></label>';
						return html;
					},
					"createdCell": function (cell, cellData, rowData, rowIndex, colIndex) {
						if(rowData.reselling_switch == 0){
							$(cell).find('.switch_input').removeAttr("checked");
						}else {
							$(cell).find('.switch_input').attr("checked");
						}
						let element = $(cell).find('.switch_input')
						element.click(function(){
							let reselling_switch = rowData.reselling_switch;
							reselling_switch == 0 ? reselling_switch = 1 : reselling_switch = 0
							$.ajax({
								type:"post",
								url:"/hijack/updateAsinSta",
								data:{
									"id": rowData.id,
									"reselling_switch": reselling_switch
								},
								success:function(res){
									editTableObj.ajax.reload();
								},
								error:function(err){
									alert('Failed to update.')
								},
							});
						})
					},
				},
				{
					data: null,
					orderable: false,
					bSortable: false,
					render: function (data, type, row, meta) {
						var html = '<a href="https://'+row.domain+'/dp/'+ row.asin +'" target="_blank">'
							+ '<svg t="1585549427364" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="5258" width="26" height="26"><path d="M836.096 192H640a32 32 0 0 1 0-64h272a32 32 0 0 1 32 32v281.92a32 32 0 1 1-64 0V238.592L534.912 592.256a32 32 0 1 1-45.824-44.672L836.096 192zM768 826.368V570.176a32 32 0 1 1 64 0v288.192a32 32 0 0 1-32 32h-640a32 32 0 0 1-32-32V281.92a32 32 0 0 1 32-32h384a32 32 0 0 1 0 64H192v512.448h576z" p-id="5259" fill="#bfbfbf"></path></svg>'
						'</a>';
						return html;
					},
				},
			], 
			
		});

		$('#search').click(function () {
			editTableObj.settings()[0].ajax.data = {search: $("#search-form").serialize()};
			editTableObj.ajax.reload();
			return false;
		});

		//全选
		$("#selectAll").on('change',function() {  
		    $("input[name='checkedInput']").prop("checked", this.checked);
			let checkedBox = $("input[name='checkedInput']:checked");
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide()
		});  
		//单条选中
		$("body").on('change','.group-checkable',function(){
			var $subs = $("input[name='checkedInput']");
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 
			let checkedBox = $subs.filter(":checked");
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide()
		});

		//开启追踪
		$('.start_btn').click(function(){
			let chk_value = '';
			$("input[name='checkedInput']:checked").each(function () {
				if(chk_value != ''){
					chk_value = chk_value + ',' + $(this).val()	
				}else{
					chk_value = chk_value + $(this).val()
				}		
			});
			$.ajax({
				type:"post",
				url:"/hijack/updateAsinSta",
				data:{
					"id": chk_value,
					"reselling_switch": 1
				},
				success:function(res){			
					if(res.status == 0){
						$('.error_mask').fadeIn(1000);
						setTimeout(function(){
							$('.error_mask').fadeOut(1000);
						},2000)
					}else if(res.status == 1){
						$('.success_mask').fadeIn(1000);
						setTimeout(function(){
							$('.success_mask').fadeOut(1000);
						},2000)	
					}
					editTableObj.ajax.reload();
					$('#selectAll').removeAttr('checked');
					$('.start_btn').hide();
					$('.close_btn').hide();
					return false;
					
				},
				error:function(err){
					$('.error_mask').fadeIn(1000);
					setTimeout(function(){
						$('.error_mask').fadeOut(1000);
					},2000)
				},
			});
		})
		//关闭追踪
		$('.close_btn').click(function(){
			let chk_value = '';
			$("input[name='checkedInput']:checked").each(function () {
				if(chk_value != ''){
					chk_value = chk_value + ',' + $(this).val()	
				}else{
					chk_value = chk_value + $(this).val()
				}		
			});
			$.ajax({
				type:"post",
				url:"/hijack/updateAsinSta",
				data:{
					"id": chk_value,
					"reselling_switch": 0
				},
				success:function(res){
					if(res.status == 0){
						$('.error_mask').fadeIn(1000);
						setTimeout(function(){
							$('.error_mask').fadeOut(1000);
						},2000)
					}else if(res.status == 1){
						$('.success_mask').fadeIn(1000);
						setTimeout(function(){
							$('.success_mask').fadeOut(1000);
						},2000)	
					}
					editTableObj.ajax.reload();
					$('#selectAll').removeAttr('checked');
					$('.start_btn').hide();
					$('.close_btn').hide();
					return false;
				},
				error:function(err){
					$('.error_mask').fadeIn(1000);
					setTimeout(function(){
						$('.error_mask').fadeOut(1000);
					},2000)
				},
			});
		})
 	})
 </script>


@endsection
