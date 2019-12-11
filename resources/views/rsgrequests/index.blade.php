@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['Rsg Request']])
@endsection
@section('content')


	<style type="text/css">
		.dataTables_extended_wrapper .table.dataTable {
			margin: 0px !important;
		}

		table.dataTable thead th, table.dataTable thead td {
			padding: 10px 2px !important;}
		table.dataTable tbody th, table.dataTable tbody td {
			padding: 10px 2px;
		}
		th,td,td>span {
			font-size:12px !important;
			font-family:Arial, Helvetica, sans-serif;
		}
		.status-statis .status-one{
			width:225px;
			margin-top:10px;
			margin-bottom: 10px;
			cursor:pointer;
		}
		.status-statis .status-show{
			width:100px;
			margin-top:10px;
			margin-bottom: 10px;
		}
		#thetabletoolbar{
			margin-top: 80px;
			margin-bottom:0px !important;
		}
	</style>
	<div class="row">
		<div class="col-md-12">
			<!-- BEGIN EXAMPLE TABLE PORTLET-->
			<div class="portlet light bordered">
				<div class="portlet-title">
					{{--新添加的状态统计数据--}}
					<form id="search-form">
						<input type="hidden" id="search-status" name="status" value="">
						<div class="caption status-statis font-dark col-md-12">
							<div class="static-pending">
								<div class="col-md-12">
									<div class="status-show pull-left">Pending </div>
									<div class="status-one pull-left" data-status="-1">All Pending (<span class="all-pending-data">12</span>)</div>
									<div class="status-one pull-left orange" data-status="3">Submit PayPal (<span class="submit-paypal-data">0</span>)</div>
									<div class="status-one pull-left light-green" data-status="4">Waiting Payment (<span class="waiting-payment-data">5</span>)</div>
									<div class="status-one pull-left orange" data-status="5">Submit Order ID (<span class="submit-order-id-data">1</span>)</div>
									<div class="status-one pull-left light-green" data-status="6">Check Order ID (<span class="check-order-id-data">3</span>)</div>
									<div class="status-one pull-left orange" data-status="7">Submit Review ID (<span class="submit-review-id-data">0</span>)</div>
									<div class="status-one pull-left light-green" data-status="8">Check Review ID (<span class="check-review-id-data">3</span>)</div>
								</div>
							</div>

							<div class="static-status">
								<div class="col-md-12">
									<div class="status-show pull-left ">Status </div>
									<div class="status-one pull-left" data-status="0">All Requests (<span class="all-requests-data">24</span>)</div>
									<div class="status-one pull-left green" data-status="9">Completed (<span class="completed-data">5</span>)</div>
									<div class="status-one pull-left green" data-status="10">Closed (<span class="closed-data">5</span>)</div>
									<div class="status-one pull-left red" data-status="11">Charge Back (<span class="charge-back-data">2</span>)</div>
									<div class="status-one pull-left green" data-status="1">Check customer (<span class="check-customer-data">5</span>)</div>
									<div class="status-one pull-left green" data-status="2">Reject (<span class="reject-data">5</span>)</div>
									<div class="status-one pull-left green" data-status="-1">Pending (<span class="all-pending-data">12</span>)</div>
								</div>
							</div>
						</div>
						<br><br>
						<div class="table-toolbar" id="thetabletoolbar">
							<div class="row">
								<div class="col-md-2">
									<div class="input-group">
										<span class="input-group-addon">Date_from</span>
										<input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{{$submit_date_from}}" id="date_from" name="submit_date_from"/>
									</div>
									<br>
									<div class="input-group">
										<span class="input-group-addon">Date_to</span>
										<input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{{$submit_date_to}}" id="date_to" name="submit_date_to"/>
									</div>
								</div>

								<div class="col-md-2">
									<div class="input-group">
										<span class="input-group-addon">Channel</span>
										<select id="channel" name="channel" class="form-control  form-filter input-sm">
											<option value="-1">Select Channel</option>
											@foreach(getRsgRequestChannel() as $k=>$v)
												<option value="{{$k}}">{{$v}}</option>
											@endforeach
										</select>
									</div>
									<br>
									<div class="input-group">
										<span class="input-group-addon">FacebookGroup</span>
										<input id="facebook_group" type="text" class="form-control form-filter input-sm" name="facebook_group" list="list-facebook_group" placeholder="Facebook Group"/>
										<datalist id="list-facebook_group">
											@foreach(getFacebookGroup() as $id=>$name)
												<option value="{!! $id !!} | {!! $name !!}"></option>
											@endforeach
										</datalist>
									</div>
								</div>
								<div class="col-md-2">
									<div class="input-group">
										<span class="input-group-addon">Processor</span>
										<select name="processor" class="form-control form-filter input-sm">
											<option value="">Processor</option>
											@foreach ($users as $user_id=>$user_name)
												<option value="{{$user_id}}">{{$user_name}}</option>
											@endforeach
										</select>
									</div>
									<br>
									<div class="input-group">
										<span class="input-group-addon">Keywords</span>
										<input name="keyword" type="text" value="{{$email}}" class="form-control form-filter input-sm" placeholder="支持ID、邮箱、PayPal、ASIN搜索">
									</div>
								</div>

								<div class="btn-group">
									<button id="search" class="btn sbold blue">Search</button>
								</div>

							</div>
						</div>
					</form>

					<div class="col-md-12" style="padding: 0px;">

						<div class="btn-group " style="float:right;">
							@permission('rsgrequests-export')
							<button id="export" class="btn sbold blue"> Export
								<i class="fa fa-download"></i>
							</button>
							@endpermission
							@permission('rsgrequests-create')
							<a data-target="#ajax" data-toggle="modal" href="{{ url('rsgrequests/create')}}"><button id="sample_editable_1_2_new" class="btn sbold red"> Add New
									<i class="fa fa-plus"></i>
								</button>
							</a>
							@endpermission
						</div>
					</div>
				</div>
				<div class="portlet-body">
					@permission('rsgrequests-batch-update')
					<div class="table-actions-wrapper pull-right">
						{{--更新processor--}}
						<select id="processor" class="table-group-action-input form-control input-inline input-small input-sm">
							<option value="">Select Processor</option>
							<?php
							foreach($users as $k=>$v){
								echo '<option value="'.$k.'">'.$v.'</option>';
							}?>
						</select>

						<button class="btn btn-sm green table-action-submit" data-type="1">
							<i class="fa fa-check"></i> Update</button>

						<select id="customstatus" class="table-group-action-input form-control input-inline input-small input-sm">
							<option value="">Select Step</option>
							<?php
							foreach(getStepStatus() as $k=>$v){
								echo '<option value="'.$k.'">'.$v.'</option>';
							}?>
						</select>
						<button class="btn btn-sm green table-action-submit" data-type="2">
							<i class="fa fa-check"></i> Update</button>

						@permission('compose')
						<button class="btn btn-sm green" id="batch-send">
							<i class="fa fa-check"></i> Batch Send</button>
						@endpermission
					</div>
					@endpermission
					<div class="table-container">

						<div style="overflow:auto;width: 100%;">
							<table class="table table-striped table-bordered table-hover table-checkable" id="thetable">
								<thead>
								<tr role="row" class="heading">
									<th onclick="this===arguments[0].target && this.firstElementChild.click()">
										<input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>
									</th>
									<th width="10%"> Submit Date </th>
									<th width="10%"> Channel </th>
									<th width="6%"> Customer Email </th>
									<th width="6%"> Request Product </th>
									<th width="8%"> Current Step </th>
									<th width="4%"> Customer Paypal </th>
									<th width="4%"> Funded </th>
									<th width="6%"> Amazon OrderID </th>
									<th width="6%"> Review Url</th>
									<th width="6%"> Star rating</th>
									{{--<th width="6%"> Follow</th>--}}
									{{--<th width="6%"> Next follow date</th>--}}
									<th width="4%"> Sales</th>
									<th width="6%"> Site</th>
									<th width="4%"> Update Date </th>
									<th width="6%">FB Name</th>
									<th width="6%">Group</th>
									<th width="4%"> Processor </th>
									<th width="4%"> Action</th>
								</tr>
								</thead>
								<tbody> </tbody>
							</table>
						</div>


					</div>
				</div>
			</div>
			<!-- END EXAMPLE TABLE PORTLET-->
		</div>
	</div>


	<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" >
				<div class="modal-body" >
					<img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
					<span>Loading... </span>
				</div>
			</div>
		</div>
	</div>
	<script>
        $("#thetabletoolbar [id^='date']").each(function () {

            let defaults = {
                autoclose: true
            }

            let options = eval(`({${$(this).data('options')}})`)

            $(this).datepicker(Object.assign(defaults, options))
        });

        let $theTable = $(thetable)

        var initTable = function () {
            $theTable.dataTable({
                searching: false,//关闭搜索
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                "lengthMenu": [
                    [10, 50, 100, -1],
                    [10, 50, 100, 'All'] // change per page values here
                ],
                "pageLength": 10, // default record count per page
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  true,
                aoColumnDefs: [ { "bSortable": false, "aTargets": [0,2,3,4,5,6,7,8,9,10,11,12,14,15,16,17] }],
                order: [],
				select: {
					style: 'os',
					info: true, // info N rows selected
					// blurable: true, // unselect on blur
					selector: 'td:first-child', // 指定第一列可以点击选中
				},
                columns: [
					{
                        width: "1px",
                        defaultContent: '',
                        className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                    },
                    {data: 'created_at', name: 'created_at'},
                    {data: 'channel', name: 'channel'},
                    {data: 'customer_email', name: 'customer_email'},
                    {data: 'asin_link', name: 'asin_link'},
                    {data: 'step', name: 'step'},
                    {data: 'customer_paypal_email', name: 'customer_paypal_email'},
                    {data:'funded',name:'funded'},
                    {data:'amazon_order_id',name:'amazon_order_id'},
                    {data: 'review_url', name: 'review_url'},
                    {data:'star_rating',name:'star_rating'},
                    {data:'sales',name:'sales'},
                    {data:'site',name:'site'},
                    {data:'updated_at',name:'updated_at'},
                    {data:'facebook_name',name:'facebook_name'},
                    {data:'group',name:'group'},
                    {data:'processor',name:'processor'},
                    {data:'action',name:'action'},
                ],
                ajax: {
                    type: 'POST',
                    url: "{{ url('rsgrequests/get')}}",
                    data:  {search: $("#search-form").serialize()},
                    "dataSrc": function (json) {

                        var staticStatus = json.staticStatus;
						$('.all-pending-data').text(staticStatus.all_pending);
						$('.submit-paypal-data').text(staticStatus.submit_paypal);
						$('.waiting-payment-data').text(staticStatus.waiting_payment);
						$('.submit-order-id-data').text(staticStatus.submit_order_id);
						$('.check-order-id-data').text(staticStatus.check_order_id);
						$('.submit-review-id-data').text(staticStatus.submit_review_id);
						$('.check-review-id-data').text(staticStatus.check_review_id);
						$('.all-requests-data').text(staticStatus.all_requests);
						$('.completed-data').text(staticStatus.completed);
						$('.closed-data').text(staticStatus.closed);
						$('.charge-back-data').text(staticStatus.charge_back);
						$('.check-customer-data').text(staticStatus.check_customer);
						$('.reject-data').text(staticStatus.reject);
                        return json.data;
                    },
                }
            });
        }

        initTable();
        let dtApi = $theTable.api();

        var grid = new Datatable();
        //设置负责人操作、修改状态值
        $('.table-action-submit').click(function(){
            var type = $(this).attr('data-type');
            customstatus = '';
            if(type==1){
                var customstatus = $("#processor", grid.getTableWrapper());
			}else if(type==2){
                var customstatus = $("#customstatus", grid.getTableWrapper());
			}

            let selectedRows = dtApi.rows({selected: true})
            let ctgRows = selectedRows.data().toArray().map(obj => [obj.id]);//选中的行的id

            if ((customstatus.val() != "") && ctgRows.length > 0) {
                $.ajax({
                    type: 'post',
                    url: '/rsgrequests/updateAction',
                    data: {type:type,data:customstatus.val(),id:ctgRows},
                    dataType: 'json',
                    success: function(res) {
                        if(res){
                            //动态改变已修改的值，不用重新加载数据
                            dtApi.ajax.reload();
                            toastr.success('Saved !');
                        }else{
                            //编辑失败
                            toastr.error('Failed');
                        }
                    }
                });
            } else if (customstatus.val() == "") {
                toastr.error('Please select an processor !')
            } else if (!ctgRows.length) {
                toastr.error('Please select some rows first !')
            }
        });

        //批量发邮件操作
        $('#batch-send').click(function(){
            let selectedRows = dtApi.rows({selected: true})
            let ctgRows = selectedRows.data().toArray().map(obj => [obj.customer_email]);//选中的行的email
            if (ctgRows.length > 0) {
                selectId = ctgRows;
                var email = '';
                //通过选中的ID得到选中的email
                $(ctgRows).each(function (index,val){
					email = email + val +';';
                });
                window.open('/send/create?to_address='+email,'_blank');
                // location.href='/send/create?to_address='+email;
            } else if (!ctgRows.length) {
                toastr.error('Please select some rows first !')
            }
        });

		//点击状态统计切换列表数据（只展示该状态下的数据）
        $('.status-one').click(function(){
			var search_status = $(this).attr('data-status');
			$('#search-status').val(search_status);
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
		})

        //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
        $('#search').click(function () {
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
        });

        //下载数据
        $("#export").click(function(){
            location.href='/rsgrequests/export';
            return false;

        });

        $(function() {
            $("#ajax").on("hidden.bs.modal",function(){
                $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
            });
        });

	</script>

@endsection