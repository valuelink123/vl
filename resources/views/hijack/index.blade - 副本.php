@extends('layouts.layout')
@section('content')
 <!-- <script src="../../../public/assets/apps/scripts/calendar.min.js"></script> -->
 <!-- 
 <link rel="stylesheet" href="../../../public/js/tag-editor/jquery.tag-editor.css"> -->
 <style>
		#tableObj thead th{
			text-align: center !important;
		}
 		table.dataTable tbody th,
 		table.dataTable tbody td {
 			padding: 8px 10px;
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
 			width: 60%;
 			height: 38px;
 			border-radius: 2px;
 			border: 1px solid #ddd;
 			float: left;
			padding-left: 20px;
 		}
 
 		.search_btn {
			width: 68px;
			height: 38px;
 			border-radius: 5px;
 			border: 1px solid #ddd;
 			background: #fff;
 			float: left;
 			margin-left: 20px;
 			color: #666666;
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
 		}
 
 		.form_main button {
 			width: 80px;
 			height: 42px;
 			border: none;
 			background: #2096fa;
 			color: #fff;
 			float: left;
 			margin-right: 10px;
 			border-radius: 5px;
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
			margin: 15px 35px;
		}
		.input-group:first-child{
			padding-top: 20px!important;
		}
		.box h4{
			height: 45px;
			font-size: 28px;
			padding-left: 30px;
			border-bottom: 1px solid #ccc;
		}
		.box_btn{
			height: 60px;
			margin: 20px 30px 30px 30px;
			padding-top: 50px;
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
 	</style>
 </head>
 
 <body class="dt-tableObj">
 	<div class="content">
 		<div class="search_main">
 			<input type="text" class="search_input">
 			<button class="search_btn" onclick="keyword_filter()">Search</button>
 		</div>
 		<div class="form_main">
 			<select name="" class="bgList" onchange="status_filter(this.value,1)">
				<option value="">All BG</option>
				<option value="BG1">BG1</option>
				<option value="BG3">BG3</option>
				<option value="BG4">BG4</option>
			</select>
			<select name="" onchange="status_filter(this.value,2)">
				<option value="">All BU</option>
				<option value="BG">BG</option>
				<option value="BU1">BU1</option>
				<option value="BU2">BU2</option>
				<option value="BU3">BU3</option>
				<option value="BU4">BU4</option>
				<option value="BU5">BU5</option>
			</select>
 			<select name="" class="sellerList" onchange="status_filter(this.value,6)">
				<option value="">All Seller</option>
			</select>
 			<!-- <select name="" onchange="status_filter(this.value,2)">
				<option value="">All Status</option>
			</select> -->
 			<select name="" onchange="status_filter(this.value,3)">
				<option value="">All Marketplace</option>
				<option value="www.amazon.com">www.amazon.com</option>
				<option value="www.amazon.ca">www.amazon.ca</option>
				<option value="www.amazon.mx">www.amazon.mx</option>
				<option value="www.amazon.co.uk">www.amazon.co.uk</option>
				<option value="www.amazon.fr">www.amazon.fr</option>
				<option value="www.amazon.de">www.amazon.de</option>
				<option value="www.amazon.it">www.amazon.it</option>
				<option value="www.amazon.es">www.amazon.es</option>
				<option value="www.amazon.co.jp">www.amazon.co.jp</option>
			</select>
 			<button class="export_btn">Export</button>
 			<button class="start_btn status_btn isHide">Turn On</button>
 			<button class="close_btn status_btn isHide">Turn Off</button>
 		</div>
 		<div>
 		
 			<table id="tableObj" class="display table-striped table-bordered table-hover" style="width:100%">
 				<thead>
 					<tr style="text-align: center;">
						<th>BG</th>
						<th>BU</th>
						<th>Marketplace</th>
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
	<div class="box" style="display: none;">
		<h4>Export</h4>
		<div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
		    <input type="text" class="form-control form-filter input-sm date1" readonly name="date_from" placeholder="From" value="">
		    <span class="input-group-btn">
				<button class="btn btn-sm default" type="button">
					<i class="fa fa-calendar"></i>
				</button>
			</span>
		</div>
		
		<div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
		    <input type="text" class="form-control form-filter input-sm date2" readonly name="date_to" placeholder="To" value="">
		    <span class="input-group-btn">
				<button class="btn btn-sm default" type="button">
					<i class="fa fa-calendar"></i>
				</button>
			</span>
		</div>
		<div class="box_btn">
			<button class="handlerCancel">Cancel</button>
			<button class="handlerExport">Export</button>
		</div>
	</div>
 </body>
 
 </html>
 <script>
	 //取消导出
	 $('.handlerCancel').click(function(){
		 $('.box').hide();
	 })
	 function dateStr(str){
	 	str = str.replace(/-/g,'/'); // 将-替换成/，因为下面这个构造函数只支持/分隔的日期字符串
	 	return  Math.round(new Date(str).getTime()/1000); // 构造一个日期型数据，值为传入的字符串
	 }
	 //导出
	 $('.handlerExport').click(function(){
		 let chk_value = '';
		 $("input[name='checkedInput']:checked").each(function () {
			 if(chk_value != ''){
				 chk_value = chk_value + ',' + $(this).val()	
			 }else{
				 chk_value = chk_value + $(this).val()
			 }
		 		 			
		 });
		 
		 $.ajax({
		     url: "http://10.10.42.14/vl/public/hijack/hijackExport",
		     method: 'POST',
		     cache: false,
		     data: {
		         startTime: dateStr($('.date1').val()),
		         endTime: dateStr($('.date2').val()),
		         idList: chk_value
		     },
		 				
		     success: function (res) {
		 					 console.log(res)
		         if (res == '') {
		             // draw_alert("No data exists for the dates and keywords selected.");
		         }
		         else {
		 //            var fileName = "amztracker";
		 
		 //             function msieversion() {
		 //                 var ua = window.navigator.userAgent;
		 //                 var msie = ua.indexOf("MSIE ");
		 //                 if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
		 //                     return true;
		 //                 } else {
		 //                     return false;
		 //                 }
		 //                 return false;
		 //             }
		 
		 //             if (msieversion()) {
		 //                 var IEwindow = window.open();
		 //                 IEwindow.document.write('sep=,\r\n' + data);
		 //                 IEwindow.document.close();
		 //                 IEwindow.document.execCommand('SaveAs', true, fileName + ".csv");
		 //                 IEwindow.close();
		 //             } else {
		 //                 //var uri = "data:text/csv;charset=utf-8,\ufeff" + data;
		 //                 var uri = 'data:application/csv;charset=utf-8,\ufeff' + encodeURI(data);
		 //                 var link = document.createElement("a");
		 //                 link.href = uri;
		 //                 link.style = "visibility:hidden";
		 //                 link.download = fileName + ".csv";
		 //                 document.body.appendChild(link);
		 //                 link.click();
		 //                 document.body.removeChild(link);
		 //             }
		         }
		 
		 
		         // $('#bulkExportModal .modal-footer .btn').removeAttr('disabled');
		         // $('#bulkExportModal .modal-footer .btn-success').html('Export');
		         // $('#bulkExportModal').modal('hide');
		 
		     }
		 });
		 
	 })
	 //时间选择器
	 function initPickers() {
	     //init date pickers
	     $('.date-picker').datepicker({
	         rtl: App.isRTL(),
	         autoclose: true
	     });
	 }
	 initPickers();
	//筛选
	function status_filter(value,column) {
		console.log(value)
	    if (value == '') {
	        editTableObj.column(column).search('').draw();
	    }
	    else editTableObj.column(column).search(value).draw();
	}
	function keyword_filter() {
		let val = $('.search_input').val();
		editTableObj.search(val).draw();
	}
	
 	$(document).ready(function () {
		function setSelectList(){
			$.ajax({
				type:"GET",
				url:'http://10.10.42.14/vl/public/hijack/index/1',
				
				async:true,
				error:function(XMLHttpReuqest,textStautus){
				    console.log(XMLHttpRequest.status);
				    console.log(textStautus);
				},
				success:function(res){
					$.each(res.userList, function (index, value) {
						$(".sellerList").append("<option value='" + value.name + "'>" + value.name + "</option>");
					})
				}
			});
		}
		setSelectList();
		editTableObj = $('#tableObj').DataTable({
			"searching": true,  //去掉搜索框
			"bLengthChange": false, //去掉每页多少条框体
			"paging": true,  // 是否显示分页
			"pagingType": 'numbers',
			"info": false,// 是否表格左下角显示的文字
			pageLength: 10,
			"ordering": true,
			"serverSide": false,//是否所有的请求都请求服务器
			"ajax": {
				url: "http://10.10.42.14/vl/public/hijack/index/1",
				dataSrc:function(res){
					let data 
					return data = res.productList
				}
			},
			"aoColumnDefs": [
				{ "bSortable": false, "aTargets": [ 0 ] },
				{ "bSortable": false, "aTargets": [ 1 ] },
			],
			
			"aaSorting": [[1, "asc"]],
			"pagingType": 'full_numbers',		
			
			"oLanguage": {
				"sInfoFiltered":'',
			},
			data: [],
			columns: [
				{
					data: "BG" ,
					visible: false,
				},
				{
					data: "BU" ,
					visible: false,
				},
				{
					data: 'domin_sx',
					visible: false,
				},
				{
					orderable: false,
					bSortable: false,
					data: "idKey",
					width: '30px',
					render: function(data, type, row, meta) {
						var ids = row.id
						var content = '<input type="checkbox" name="checkedInput"  class="group-checkable" value="' + ids + '" />';
						return content;
					},
					
				},
				{
					orderable: true,
					bSortable: true,
					render: function (data, type, row) {
						let img,dot,str;
						if(row.images != null){
							str = row.images;
							dot = str.split(',');
							dot.length > 1 ? img = 'https://images-na.ssl-images-amazon.com/images/I/' + dot[1] : img = ''
						}
						return '<div class="product_main"><div class="product_img"><img src="'+img+'" alt=""></div><div class="product_text"><p class="product_title" title="'+row.title+'">'+row.title+'</p><div class="product_span"><img src="../assets/global/img/es.png" alt="" class="country_img"><span>'+row.asin+'</span> / <span>'+row.sku+'</span></div></div></div>';
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function () {
							let processId = rowData.id;
							window.location.href = "detail?id=" + processId;
						});
					}
				},
				{ 
					data: "sku_status",
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function () {
							let processId = rowData.id;
							window.location.href = "detail?id=" + processId;
						});
					}
				},
				{
					data: "userName",
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function () {
							let processId = rowData.id;
							console.log();
							window.location.href = "detail?id=" + processId;
						});
					}
				},
				
				{ 
					data: "reselling_time" ,
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function () {
							let processId = rowData.id;
							console.log();
							window.location.href = "detail?id=" + processId;
						});
					}
				},
				{ 
					data: "reselling_num" ,
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function () {
							let processId = rowData.id;
							console.log();
							window.location.href = "detail?id=" + processId;
						});
					}
				},
				{
					orderable: true,
					bSortable: true,
					render: function (data, type, row, meta) {
						var rowId = row.id;
						var rowStatus = row.reselling_switch;
						var rowStr =  rowId + ";" + rowStatus;
						let title = rowStatus==0 ? "Hijacker monitoring turned off.":"Hijacker monitoring turned on."
						var html = '<label class="switch" title="'+ title + '"> <input type="checkbox" class="switch_input" checked value="'+rowStr+'"><span></span></label>';
						return html;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						if(rowData.reselling_switch == 0){
							$(cell).find('.switch_input').removeAttr("checked");
						}else {
							$(cell).find('.switch_input').attr("checked");
						}
						let element = $(cell).find('.switch_input')
						element.click(function(){
							let index = $(this).val().lastIndexOf(";")
							let id = $(this).val().substring(0,index);
							let reselling_switch = $(this).val().substring(index+1);
							console.log(id,reselling_switch)
							$.ajax({
								type:"post",
								url:"http://10.10.42.14/vl/public/hijack/updateAsinSta",
								data:{
									"id": id,
									"reselling_switch": reselling_switch
								},
								success:function(data){
									console.log("success",data);
								},
								error:function(){
									console.log("发生错误")
								},
								complete:function(){
									console.log("ajax请求完事，最终操作在这里完成")
								}
							});
						})
					},
				},
				{
					orderable: false,
					bSortable: false,
					render: function (data, type, row, meta) {
						var html = '<a href="https://www.amazon.com/dp/'+ row.asin +'" target="_blank">'
							+ '<svg t="1585549427364" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="5258" width="26" height="26"><path d="M836.096 192H640a32 32 0 0 1 0-64h272a32 32 0 0 1 32 32v281.92a32 32 0 1 1-64 0V238.592L534.912 592.256a32 32 0 1 1-45.824-44.672L836.096 192zM768 826.368V570.176a32 32 0 1 1 64 0v288.192a32 32 0 0 1-32 32h-640a32 32 0 0 1-32-32V281.92a32 32 0 0 1 32-32h384a32 32 0 0 1 0 64H192v512.448h576z" p-id="5259" fill="#bfbfbf"></path></svg>'
						'</a>';
						return html;
					},
				},
			], 
			
		});
		
		//全选
		$("#selectAll").on('change',function() {  
		    $("input[name='checkedInput']").prop("checked", this.checked);
			let checkedBox = $("input[name='checkedInput']:checked");
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide()
		});  
		$("input[name='checkedInput']").change("change",function() {
			console.log(1)
		    /* var $subs = $("input[name='checkedInput']");  
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 	
			//判断如果checkbox的length大于0则显示开关按钮，否则隐藏开关按钮
			let checkedBox = $subs.filter(":checked");
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide() */
		});
		//单条选中
		/* $(".group-checkable").click(function() {  
		console.log(111)
		    /* var $subs = $("input[name='checkedInput']");  
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 	
			//判断如果checkbox的length大于0则显示开关按钮，否则隐藏开关按钮
			let checkedBox = $subs.filter(":checked");
			console.log(checkedBox)
			checkedBox.length > 0 ? $('.status_btn').show(): $('.status_btn').hide();
			console.log(checkedBox.length) 
		});  */
		
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
				url:"http://10.10.42.14/vl/public/hijack/updateAsinSta",
				data:{
					"id": chk_value,
					"reselling_switch": 1
				},
				success:function(data){
					console.log("success",data);
				},
				error:function(){
					console.log("发生错误")
				},
				complete:function(){
					console.log("ajax请求完事，最终操作在这里完成")
				}
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
				url:"http://10.10.42.14/vl/public/hijack/updateAsinSta",
				data:{
					"id": chk_value,
					"reselling_switch": 0
				},
				success:function(data){
					window.location.reload()
				},
				error:function(){
					console.log("发生错误")
				},
				complete:function(){
					console.log("ajax请求完事，最终操作在这里完成")
				}
			});
		})
		//导出功能
		$('.export_btn').click(function(){
			let checkedBox = $("input[name='checkedInput']:checked");
			if (checkedBox.length == 0) {
			    alert("请先选择数据");
			    return;
			} else {
			    $('.box').show();
			}
		})
		//行内编辑
		$('.editorRemarks').click(function(){
			let val = $(this).find("span").text();
			console.log(val)
			$(this).html('<input type="text" size="16" class="remarksVal" style="width: 100%"/>');
			let aInput = $(this).find(":input");
			aInput.focus().val(val);
		})
		$('.editorRemarks').on("blur", ":input", function () {
			let text = $(this).parents('tr').find('.remarksVal').val();
			let ids = $(this).parents('tr').find('.checkchild').val();
			let param = {
				text: text,
				id: ids
			}
			console.log(param)
		}) 
 	})
 </script>


@endsection
