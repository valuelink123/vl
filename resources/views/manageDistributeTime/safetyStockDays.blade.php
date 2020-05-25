@extends('layouts.layout')
@section('crumb')
    <a href="/manageDistributeTime/safetyStockDays">Safety Stock Days</a>
@endsection
@section('content')
<style>
	.title{
		font-size: 17px;
		font-weight: bold;
		padding: 20px 0;
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
	.filter_option .btn{
		padding: 7px 0 7px 12px !important;
	}
	.search_box input,.change_box input{
		width: 250px;
		height: 36px;
		padding-left: 10px;
		border-top-left-radius: 4px !important;
		border-bottom-left-radius: 4px !important;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	.search,.change{
		width: 90px;
		height: 36px;
		background-color: rgba(99, 197, 209, 1);
		border: 1px solid rgba(99, 197, 209, 1);
		margin-left: 10px;
		border-top-right-radius: 4px !important;
		border-bottom-right-radius: 4px !important;
		color: #fff;
		outline: none;
	}
	.change,.change_box input{
		border-radius: 4px !important;
	}
	.search svg,.change svg{
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
	table.dataTable thead td, table.dataTable thead th{
		text-align: center;
	}
	.table-scrollable{
		background: #fff;
	}
	div.dataTables_wrapper div.dataTables_filter{
		display: none;
	}
	.tipsIcon{
		margin-bottom: -4px;
	}
	.tipsIcon:hover path{
		fill: red;
	}
</style>
<div>
	<h4 class="title">安全库存天数管理
	<button id="export" class="btn sbold blue" style="margin-bottom: 4px;border-radius: 4px !important; float: right;"> Export
		<i class="fa fa-download"></i>
	</button>
	</h4>
	<div class="content">
		<div class="filter_box">
			<div class="filter_option">
				<label for="stationSelect">站点</label>
				<select id="stationSelect" onchange="status_filter(this.value,2)">
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
				<label for="skuStatusSelect">SKU状态</label>
				<select id="skuStatusSelect" onchange="status_filter(this.value,4)">
					<option value ="">全部</option>
					<option value ="2">新品</option>
					<option value ="1">保留</option>
					<option value ="0">淘汰</option>
					<option value ="4">替换</option>
					<option value ="5">待定</option>
					<option value ="3">配件</option>
					<option value ="6">停售</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="skuGradeSelect">SKU等级</label>
				<select id="skuGradeSelect" onchange="status_filter(this.value,5)">
					<option value ="">全部</option>
					<option value ="S">S</option>
					<option value ="A">A</option>
					<option value ="B">B</option>
					<option value ="C">C</option>
					<option value ="D">D</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="bgSelect">BG</label>
				<select id="bgSelect" onchange="status_filter(this.value,6)">
					<option value ="">全部</option>
					<option value ="BG">BG</option>
					<option value ="BG1">BG1</option>
					<option value ="BG2">BG3</option>
					<option value ="BG3">BG4</option>
				</select>
			</div>
			<div class="filter_option">
				<label for="buSelect">BU</label>
				<select id="buSelect" onchange="status_filter(this.value,7)">
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
				<label for="maintainerSelect">维护人</label>
				<select id="maintainerSelect" onchange="status_filter(this.value,10)">
					<option value ="">全部</option>
				</select>
			</div>
			<div class="filter_option search_box">
				<label for="">搜索</label>
				<input type="text" class="keyword" placeholder="请输入关键字">
				<button class="search" onclick="keyword_filter()">
					<svg t="1588043111114" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3742" width="18" height="18"><path d="M400.696889 801.393778A400.668444 400.668444 0 1 1 400.696889 0a400.668444 400.668444 0 0 1 0 801.393778z m0-89.031111a311.637333 311.637333 0 1 0 0-623.331556 311.637333 311.637333 0 0 0 0 623.331556z" fill="#ffffff" p-id="3743"></path><path d="M667.904 601.998222l314.766222 314.823111-62.919111 62.976-314.823111-314.823111z" fill="#ffffff" p-id="3744"></path></svg>
					搜索
				</button>	
				<button class="clear">清空筛选</button>
			</div>
		</div>
		<div class="update_box">
			<div class="filter_option change_box">
				<label for="" style="display: inline-block; margin-right: 5px;">更改安全库存天数:</label>
				<input type="text" class="keyword" placeholder="输入安全库存天数">
				<button class="change">
					<svg t="1589004081856" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1149" width="18" height="18"><path d="M310.144 605.587s107.454 38.525 172.491 110.569 121.692 134.819 121.692 134.819 49.018-104.785 178.609-245.388 213.648-178.609 213.648-178.609-10.271-58.474-11.828-107.9c-2.447-78.125 1.298-146.017 1.298-146.017s-102.746 19.244-234.635 215.391S593.834 668.62 593.834 668.62l-94.551-192.624-189.139 129.591z" fill="#ffffff" p-id="1150"></path></svg>
					change
				</button>
			</div>
		</div>
	</div>
</div>
<table id="safetyStockTable" class="display table-striped table-bordered table-hover" style="width:100%;margin-top: 10px;">
	<thead>
		<tr style="text-align: center;">
			<th><input type="checkbox" id="selectAll" /></th>
			<th>SKU</th>
			<th>站点</th>
			<th>物料描述</th>
			<th>SKU状态</th>
			<th>SKU等级</th>
			<th>BG</th>
			<th>BU</th>
			<th>
				加权日均
				<span title="最近7天的日均销量*20%+最近14天内的日均销售量*30%+最近28天的日均销量*50%">
					<svg t="1589247400458" class="icon tipsIcon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2166" width="18" height="18"><path d="M505.306 917.208c-221.127 0-400.391-179.155-400.391-400.288 0-221.127 179.263-400.424 400.391-400.424 221.093 0 400.319 179.296 400.319 400.424 0 221.133-179.227 400.288-400.319 400.288v0zM505.306 76.453c-243.249 0-440.402 197.184-440.402 440.468 0 243.144 197.151 440.332 440.402 440.332 243.179 0 440.362-197.182 440.362-440.332-0.001-243.283-197.183-440.468-440.362-440.468v0zM549.098 296.481c-15.547 0-28.898 5.091-39.974 15.137-11.144 10.043-16.684 22.155-16.684 36.327s5.539 26.281 16.684 36.189c11.076 10.046 24.427 14.998 39.974 14.998 15.513 0 28.828-4.953 39.836-14.998 11.008-9.907 16.545-22.016 16.545-36.189 0-14.174-5.537-26.284-16.545-36.327-11.009-10.046-24.323-15.137-39.836-15.137v0zM564.132 697.32c-12.283 0-20.918-1.929-25.906-5.779-4.953-3.99-7.462-11.285-7.462-22.016 0-4.403 0.756-10.732 2.338-19.13 1.512-8.389 3.234-15.825 5.159-22.289l23.669-81.461c2.268-7.432 3.885-15.547 4.747-24.495 0.862-8.943 1.31-15.273 1.31-18.852 0-17.062-6.227-30.959-18.615-41.692-12.383-10.732-29.997-16.099-52.802-16.099-12.661 0-26.145 2.201-40.352 6.605-14.172 4.266-29.067 9.633-44.615 15.825l-6.33 25.18c4.575-1.789 10.147-3.439 16.545-5.365 6.469-1.927 12.797-2.889 18.885-2.889 12.557 0 20.986 2.201 25.424 6.192 4.403 4.128 6.637 11.422 6.637 21.877 0 5.783-0.723 12.112-2.167 19.125-1.446 7.023-3.234 14.449-5.367 22.157l-23.737 81.739c-2.131 8.529-3.645 16.234-4.607 22.974-0.965 6.882-1.413 13.492-1.413 20.092 0 16.651 6.365 30.551 19.127 41.423 12.728 10.868 30.652 16.374 53.598 16.374 15 0 28.14-1.929 39.456-5.783 11.32-3.709 26.459-9.352 45.443-16.51l6.328-25.18c-3.265 1.375-8.529 3.164-15.785 5.229-7.296 1.788-13.763 2.75-19.508 2.75v0z" p-id="2167" fill="#bfbfbf"></path></svg>
				</span>
			</th>
			<th>安全库存(天)</th>
			<th>维护人</th>
			<th>维护日期</th>
		</tr>
	</thead>
</table>
<script>
	//正则判断输入整数
	function validataInt(ob) {
		ob.value = ob.value.replace(/^(0+)|[^\d]+/g,'')
	}
	//筛选
	function status_filter(value,column) {
	    if (value == '') {
	        tableObj.column(column).search('').draw();
	    }
	    else tableObj.column(column).search(value).draw();
	}
	function keyword_filter() {
		let val = $('.keyword').val();
		tableObj.search(val).draw();
	}
	$(document).ready(function(){
		//批量编辑
		$('.change').on('click',function(){
			let checkbox_list = [];
			$("input[name='checkedInput']:checked").each(function () {
				checkbox_list.push($(this).val());	
			});
			if(checkbox_list.length < 1){
				alert('请先选择产品')
			}else{
				console.log(checkbox_list)
			} 		
		})
		//全选
		$("#selectAll").on('change',function(e) {  
		    $("input[name='checkedInput']").prop("checked", this.checked);
			//let checkedBox = $("input[name='checkedInput']:checked");
		});  
		//单条选中
		$("body").on('change','.checkbox-item',function(){
			var $subs = $("input[name='checkedInput']");
		    $("#selectAll").prop("checked" , $subs.length == $subs.filter(":checked").length ? true :false); 
			//let checkedBox = $subs.filter(":checked");
		});
		//清空筛选
		$('.clear').on('click',function(){
			$('.keyword').val('');
			$('#maintainerSelect').val('');
			$('#buSelect').val('');
			$('#bgSelect').val('');
			$('#skuGradeSelect').val('');
			$('#skuStatusSelect').val('');
			$('#stationSelect').val('');
			status_filter('',2)
			status_filter('',4)
			status_filter('',5)
			status_filter('',6)
			status_filter('',7)
			status_filter('',10)
			keyword_filter();
			//tableObj.ajax.reload();
		})
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		
		tableObj = $('#safetyStockTable').DataTable({
			lengthMenu: [
			    20, 50, 100, 'All'
			],
			order: [ 9, "desc" ],
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
					sku: 'sku',
					station: 'station',
					item_description: 'item_description',
					sku_status: 'sku_status',
					sku_grade: 'sku_grade',
					bg: 'bg',
					bu: 'bu',
					average: 'average',
					days: 'days',
					maintainer: 'maintainer',
					updateTime: '2020-05-20',
				},
				{
					id:2,
					sku: 'sku',
					station: 'mmm',
					item_description: 'item_description',
					sku_status: 'sku_status',
					sku_grade: 'sku_grade',
					bg: 'bg',
					bu: 'bu',
					average: 'average',
					days: 'days',
					maintainer: 'maintainer',
					updateTime: '2020-05-20',
				},
				{
					id:2,
					sku: 'sku',
					station: 'station',
					item_description: 'item_description',
					sku_status: 'sku_status',
					sku_grade: 'sku_grade',
					bg: 'bg',
					bu: 'bu',
					average: 'average',
					days: 'days',
					maintainer: 'maintainer',
					updateTime: '2020-05-20',
				}
			],	
			columns: [
				{
					data: "id",
					render: function(data, type, row, meta) {
						var content = '<input type="checkbox" name="checkedInput"  class="checkbox-item" value="' + data + '" /  >';
						return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						
					}
				},
				{
					data: "sku" ,
				},
				{
					data: "station" ,
				},
				{
					data: 'item_description',
				},
				{
					data: 'sku_status',
				},
				{
					data: "sku_grade",
				},
				{
					data: "bg",
				},
				{
					data: 'bu',
				},
				{
					data: 'average',
				},
				{
					data: 'days',
				},
				{
					data: 'maintainer',
				},
				{ 
					data: 'updateTime',
				},
			], 
			columnDefs: [
				{ "bSortable": false, "aTargets": [ 0,1,2,3,4,5,6,7,8,10]},
				{
					"targets": [9],
					render: function (data, type, row) {
						return '<div><span>'+data+'</span><img src="../assets/global/img/editor.png" alt="" style="float:right" class="country_img"></div>';
					},
			
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).click(function (e) {
							$(this).html('<input type="text" size="16" onkeyup="validataInt(this)" style="width: 100%"/>');
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