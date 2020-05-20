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
	<div class="button_box">
		<button id="sample_editable_1_2_new" class="btn sbold green-meadow"> 下载导入模板
			<i class="fa fa-download"></i>
		</button>
		<button id="sample_editable_1_2_new" class="btn sbold blue"> 上传
			<i class="fa fa-upload"></i>
		</button>
		<button id="export" class="btn sbold blue"> 导出
			<i class="fa fa-download"></i>
		</button>
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
					<input type="text" class="form-control createTimeInput" id="createTimeInput">  
				</div>
			</div>	
			<div class="filter_option">
				<label for="account_number">账号</label>
				<select id="account_number">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="transfer_status">调拨状态</label>
				<select id="transfer_status">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="callout_factory">调出工厂</label>
				<select id="callout_factory">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="callin_factory">调入工厂</label>
				<select id="callin_factory">
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
				<label for="seller_select">Seller</label>
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
				<button class="clear">清空筛选</button>
			</div>
		</div>
	</div>
	<div class="portlet light bordered">
	    <div style="margin-bottom: 15px"></div>
	    <div class="portlet-body">
	        <div class="table-container" style="position: relative;">
				<div class="col-md-6"  style="position: absolute;left: 520px; z-index: 999;top:0">
					<button type="button" class="btn btn-sm red-sunglo">待计划确认 : 22</button>
					<button type="button" class="btn btn-sm yellow-crusta">BU经理审核 : 22</button>
					<button type="button" class="btn btn-sm purple-plum">BG总监审核 : 2</button>
					<button type="button" class="btn btn-sm green-meadow">已确认 : 11</button>
					<button type="button" class="btn btn-sm blue-madison">调拨取消 : 2</button>
				</div>
	            <table class="table table-striped table-bordered" id="thetable">
	                <thead>
	                <tr>
						<th>BG</th>
						<th>BU</th>
						<th>station</th>
	                    <th><input type="checkbox" id="selectAll" /></th>
	                    <th>需求提交日期</th>
	                    <th>调拨状态</th>
	                    <th>销售员</th>
	                    <th>发货批号</th>
	                    <th>调出工厂</th>
						<th>调入工厂</th>
						<th>亚马逊账号</th>
	                    <th>物料号</th>
	                    <th>调拨数量</th>
	                    <th>RMS标贴SKU</th>
	                    <th>条码标签</th>
	                    <th>发货方式</th>
	                    <th>大货资料</th>
	                    <th>Shippment ID</th>
	                    <th>跟踪号/单据号</th>
	                    <th>上次更新时间</th>
	                    <th>展开装箱数据</th>
	                </tr>
	                </thead>
	                <tbody></tbody>
	            </table>
	        </div>
	    </div>
	</div>
</div>	
<script type="text/template" id="sub-table-tpl">
        <table class="table">
            <thead>
            <tr>
                <th>Item No</th>
                <th>Seller Name</th>
                <th>Asin</th>
                <th>Seller SKU</th>
                <th>Item Name</th>
                <th>Fbm Stock</th>
                <th>Fbm Valid Stock</th>
                <th>Fba Stock</th>
                <th>Fba Transfer</th>
                <th>Unsellable</th>
                <th>Fbm Update</th>
                <th>Fba Update</th>
            </tr>
            </thead>
            <tbody>
            <% for(let row of rows){ %>
            <tr>
                <td>${row.item_code}</td>
                <td>${row.seller_name}</td>
                <td>${row.asin}</td>
                <td>${row.seller_sku}</td>
                <td>${row.item_name}</td>
                <td>${row.fbm_stock}</td>
                <td>${row.fbm_valid_stock}</td>
                <td>${row.fba_stock}</td>
                <td>${row.fba_transfer}</td>
                <td>${row.unsellable}</td>
                <td>${row.fbm_update}</td>
                <td>${row.fba_update}</td>
            </tr>
            <% } %>
            </tbody>
        </table>
    </script>
<script>
	//筛选
	function status_filter(value,column) {
	    if (value == '') {
	        editTableObj.column(column).search('').draw();
	    }
	    else editTableObj.column(column).search(value).draw();
	}
	$(document).ready(function(){
		
		//待计划确认
		$('.noConfirmed').on('click',function(){
			let chk_value = '';
			$("input[name='checkedInput']:checked").each(function () {
				console.log($(this).val())
				if(chk_value != ''){
					chk_value = chk_value + ',' + $(this).val()	
				}else{
					chk_value = chk_value + $(this).val()
				}				 		 			
			});
			chk_value == ""? chk_value = -1 : chk_value;
			tableObj.ajax.reload();
			console.log(chk_value)
		})
		//导出
		$('#export').click(function(){
			 let chk_value = '';
			 $("input[name='checkedInput']:checked").each(function () {
				 if(chk_value != ''){
					 chk_value = chk_value + ',' + $(this).val()	
				 }else{
					 chk_value = chk_value + $(this).val()
				 }				 		 			
			 });
			 chk_value == ""? chk_value = -1 : chk_value;
			 console.log(chk_value)
			 /* $.ajax({
				url: "/hijack/hijackExport",
				 method: 'POST',
				 cache: false,
				 data: {
					 startTime: dateStr($('.date1').val()),
					 endTime: dateStr($('.date2').val()),
					 idList: chk_value
				 },
							
				 success: function (data) {
					$('.dialogMain').hide();
					 if(data != ""){
						var fileName = "VOP Hijack";
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
					 }
				 } 
			 });*/
				 
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
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		theTable = $("#thetable").dataTable({
			serverSide: false,
			processing: true,
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			pageLength: 20,
			dispalyLength: 2, // default record count per page
			order: [ 1, "desc" ],
			columns: [
				{data: 'BG', name: 'BG', visible: false,},
				{data: 'BU', name: 'BU', visible: false,},
				{data: 'station', name: 'station', visible: false,},
				{
					data: "item_code",
					name: 'item_code',
					render: function(data, type, row, meta) {
						var content = '<input type="checkbox" name="checkedInput"  class="checkbox-item" value="' + data + '" />';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						
					}
				},
				{
					data: 'date', 
					name: 'data',
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
				{data: 'status', name: 'status', },
				{data: 'seller', name: 'seller',},
				{data: 'batch_number', name: 'batch_number',},
				{data: 'transfer_in', name: 'transfer_in',},
				/* {data: 'amz_account', name: 'amz_account'}, */
				
				{data: 'warehouse', name: 'warehouse', },
				{data: 'amz_account', name: 'amz_account',},
				{data: 'number', name: 'number',},
				{data: 'rms', name: 'rms',},
				{data: 'sku', name: 'sku',},
				{
					data: 'method',
					name: 'method',
					render: function(data, type, row, meta) {
						var content = '<button>打印</button>';
						return content;
					}
				},
				{data: 'method',name: 'method',},
				{
					data: 'bulk_materials', 
					name: 'bulk_materials',
					render: function(data, type, row, meta) {
						var content = '<button>下载</button>';
						return content;
					}
				},
				{data: 'shippment_id', name: 'shippment_id',},
				{data: 'odd_numbers', name: 'odd_numbers'},
				{data: 'update_time', name: 'update_time'},
				{
					"className": 'details-control disabled',
					"orderable": false,
					"data": 'item_code',
					render(item_code) {
						return `<a class="ctrl-${item_code}"></a>`
					}
				}
			],
			data:[
				{
					date: '2020-05-21',
					isCancel: false,
					status: 'status',
					seller: 'seller',
					transfer_in: '111',
					batch_number: 'batch_number',
					warehouse: '222',
					item_no: 'item_no',
					update_time: "2020-05-15",
					number: 5,
					item_code: "VT0018",
					bulk_materials: "bulk_materials",
					barcode_label: 'barcode_label',
					rms: '11',
					id: 1,
					amz_account: 'amz_account',
					sku: 'sku',
					odd_numbers: 45,
					unsellable: 0,
					shippment_id: 'shippment_id',
					method: '海运'
				},
				{
					date: '2020-05-21',
					id: 2,
					isCancel: true,
					name: '张三',
					cancelDate: '2020/4/29 13：23',
					status: 'status',
					seller: 'seller',
					batch_number: 'batch_number',
					warehouse: '222',
					item_no: 'item_no',
					update_time: "2020-05-15",
					number: 5,
					item_code: "AP0373",
					bulk_materials: "bulk_materials",
					barcode_label: 'barcode_label',
					rms: '12',
					sku: 'sku',
					odd_numbers: 45,
					unsellable: 0,
					shippment_id: 'shippment_id',
					method: '海运'
				},
				{
					date: '2020-05-21',
					id: 3,
					isCancel: true,
					name: '李四',
					cancelDate: '2020/4/29 13：23',
					status: 'status',
					seller: 'seller',
					batch_number: 'batch_number',
					warehouse: '222',
					item_no: 'item_no',
					update_time: "2020-05-15",
					number: 5,
					item_code: "HPC0008",
					bulk_materials: "bulk_materials",
					barcode_label: 'barcode_label',
					rms: '13',
					sku: 'sku',
					odd_numbers: 45,
					unsellable: 0,
					shippment_id: 'shippment_id',
					method: '海运'
				},
			],
			ajax: {
				type: 'POST',
				url: 'http://192.168.10.33/kms/partslist/get',
				dataSrc(json) {
					console.log(json)
					/* let rows = json.data
					for (let row of rows) {
						let item_code = row.item_code
						// 根据每一行 item_code 进行预查询，如果有配件数据，则将加号按钮变绿
						$.post('http://192.168.10.33/kms/partslist/subitems', {item_code}).success(rows => {
							if (rows.length > 0) {
							if (false === rows[0]) return
							$(`#thetable .ctrl-${item_code}`).parent().removeClass('disabled')
						}
					})
					}
					return rows */
				}
			},
			columnDefs: [
				{ "bSortable": false, "aTargets": [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]},
				{
					targets: [15],
					render: function(data, type, row, meta) {
						var content = '<div>'+data+'<img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						var aInput;
						$(cell).click(function () {
							$(this).html(
									'<select style="width:100%;" placeholder="请选择发货方式">'
									+'<option value="1">亚马逊卡派</option>'
									+'<option value="2">亚马逊快递</option>'
									+'<option value="3">卡派-仓库直发</option>'
									+'<option value="4">快递-仓库直发</option>'
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
								url: "/api/AlarmInfo/SubmitDbd",
								data: {},
								success: function (res) {
									if (res.status == 200) {//后台数据操作成功时
										var text = $(this).find("option:selected").text();
										$(cell).html(text);
									}
								}
							});
							
						});
					}
				}
			],
			
		})
		async function buildSubItemTable(item_code) {
		
			let rows = await new Promise((resolve, reject) => {
				$.post('http://192.168.10.33/kms/partslist/subitems', {item_code})
				.success(rows => resolve(rows))
				.error((xhr, status, errmsg) => reject(new Error(errmsg)))
			})
		
			if (!rows.length) return ''
		
			if (false === rows[0]) return Promise.reject(new Error(rows[1]))
		
			return tplRender('#sub-table-tpl', {rows})
		}
		
		// Add event listener for opening and closing details
		theTable.on('click', 'td.details-control', function () {
		
			let $td = $(this)
		
			let row = theTable.api().row($td.closest('tr'));
		
			if (row.child.isShown()) {
				row.child.remove();
				$td.removeClass('closed');
			} else {
				let {item_code} = row.data()
				let id = `sub-item-loading-${item_code}`
		
				row.child(`<div id="${id}" style="padding:3em;">Data is Loading...</div>`, 'sub-item-row').show()
		
				buildSubItemTable(item_code).then(html => {
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
			$("#seller_select").empty();
			$("#seller_select").append("<option value=''>全部</option>");
			$("#createTimes input").val(t.format("YYYY-MM-DD") + " - " + e.format("YYYY-MM-DD"));	
			let reqList = {
				"created_at_s": cusstr($('#createTimes input').val() , ' - ' , 1),
				"created_at_e": cusstr1($('#createTimes input').val() , ' - ' , 1),
			};
			let val = ''
			//tableObj.ajax.reload();
			//handleClear();
			//status_filter(val,0);
			//status_filter(val,1);
			//status_filter(val,2);
			//status_filter(val,3);
			//status_filter(val,7);
			//status_filter(val,8);
		})
	})
</script>
@endsection