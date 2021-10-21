@extends('layouts.layout')
@section('crumb')
    <a href="/hijack/index">Asin Reselling</a>
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
		table.dataTable.order-column tbody tr>.sorting_1, table.dataTable.order-column tbody tr>.sorting_2, table.dataTable.order-column tbody tr>.sorting_3, table.dataTable.display tbody tr>.sorting_1, table.dataTable.display tbody tr>.sorting_2, table.dataTable.display tbody tr>.sorting_3{
			background: none !important;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button{
			padding: 0.2em .5em;
		}

		#tabsObj.dataTable tbody tr{
			cursor: pointer !important;
		}
		#tabsObj td,#listObj td,#listObj th{padding:11px;}
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
		.bgC{
			background: #eef1f5 !important;
		}
		.switchSelect-table{
			width:60%;
			margin-bottom: 20px;

		}
		.switchSelect-table td{
			border:1px solid #676464;
			padding: 7px 12px;
			margin: 11px 20px 0px 0px;
			text-align:center;
		}
		.switchSelect-table .active{
			background-color:#CD4D00;
			color:#ffffff;
			border:1px solid #CD4D00;
		}
	</style>
	<div class="content">
		<div style="border-top: 1px solid #eee;">
			<div class="tabs">
				<table class="switchSelect switchSelect-table">
					<tr>
						<td class="switch_type active" data-value="1">ACTIVE</td>
						<td class="switch_type" data-value="2">ALL</td>
					</tr>
				</table>
				<div style="overflow-y:auto;width:100%;height:700px;">
				<table id="tabsObj" class="display table-striped table-bordered table-hover" >
					<thead>

					</thead>
					<tbody>

					</tbody>
				</table>
				</div>
			</div>
			<div class="tabs_list" id="tabs_list" data-asin="{!! $asinInfo['asin'] !!}" data-domain="{!! $asinInfo['domain'] !!}">
				<div style="padding: 12px;"><a target="_blank" style="font-size:18px;" href="https://{!! $asinInfo['domain'] !!}/gp/offer-listing/{!! $asinInfo['asin'] !!}">https://{!! $asinInfo['domain'] !!}/gp/offer-listing/{!! $asinInfo['asin'] !!}</a></div>
				<table id="listObj" class="display table-striped table-bordered table-hover" style="width:100%">
					<thead>
						<tr>
							<th class="w6">Seller</th>
							<th class="w6">Seller ID</th>
							<th class="w6">Main</th>
							<th class="w6">Price</th>
							<th class="w8">Shipping Fee</th>
						</tr>
					</thead>

					<tbody>

					</tbody>
				</table>
			</div>

		</div>

	</div>

	<script>
		$(document).ready(function () {

			let tableObj  , urlIndex , detailId , listObj,time1,time2,domin_url;
			let url = window.location.href
			let name = decodeURIComponent(url.substr(url.lastIndexOf('=') + 1));
			let str = url.substr(url.lastIndexOf('=', url.lastIndexOf('=') - 1) + 1);
			let ind1 = str.lastIndexOf('?');
			let id = str.substring(0,ind1)	;

			//点击左边表格的td显示出对应右边表格的数据
			$('#tabsObj').on('click', '.show-detail-account', function(e) {
				var task_id = $(this).attr('task_id');
				var product_id = $(this).attr('product_id');
				var asin = $('#tabs_list').attr('data-asin');
				var domain = $('#tabs_list').attr('data-domain');
				$.ajax({
					type: 'post',
					url: '/hijack/resellingDetail',
					data: {taskId:task_id,product_id:product_id},
					dataType:'json',
					success: function(res) {
						if(res.status==1){
							var data = res.data;
							var html = '';
							var href = '';
							$.each(data,function(i,item) {
								href = 'https://'+domain+'/sp?asin='+asin+'&seller='+item.sellerid;
								html += '<tr>';
								html += '<td><a target="_blank" href="'+href+'">'+item.account+'</a>'+item.remark+'</td>';//每一条数据是一行
								html += '<td>'+item.sellerid+'</td>';
								html += '<td>'+item.main+'</td>';
								html += '<td>'+item.price+'</td>';
								html += '<td>'+item.shipping_fee+'</td>';
								html += '</tr>';
							})
							$('#listObj tbody').html(html);

						}else{
							alert(res.msg);
						}
					}
				});
			});

			//点击Active或者all显示相应类别的数据
			$('.switchSelect .switch_type').click(function(){
				$('.switchSelect .switch_type').removeClass('active');
				$(this).addClass('active');
				var switch_type = $(this).attr('data-value');
				$.ajax({
					type: 'post',
					url: '/hijack/resellingList',
					data: {switch_type:switch_type,id:id},
					dataType:'json',
					success: function(res) {
						if(res.status==1){
							var data = res.data;
							var html = '';
							var num = 0;//默认第0个开始
							var first_class = '';//此类名用于标记第一个td，触发点击左边表格的第一个td显示右边表格的数据
							$.each(data,function(i,item) {
								if(i==0){
									first_class = 'first-class';
								}else{
									first_class = '';
								}
								num++;
								if(num==1){//数量为第一个的时候加上<tr>标签，用于新的一行的开始
									html += '<tr>';
								}
								html += '<td><a href="javascript:void(0);" class="show-detail-account  '+first_class+' " task_id="'+item.task_id+'" product_id="'+item.product_id+'">'+item.date+'('+item.reselling_num+')</a></td>';//每一个数据是一个td
								if(num==3){//数量为第6个的时候加上</tr>标签，用于这一行的结束
									html += '</tr>';
									num = 0;
								}
							})
							$('#tabsObj tbody').html(html);
							$("#tabsObj .first-class").trigger("click");//触发点击展示右边表格的数据
						}else{
							alert(res.msg);
						}
					}
				});
			})
			$(".switchSelect .active").trigger("click");
		})
	</script>

@endsection
