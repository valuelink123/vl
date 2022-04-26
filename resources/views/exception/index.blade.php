@extends('layouts.layout')
@section('label', 'Exception List')
@section('content')
<style type="text/css">
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 0px;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;
}
    .left{
        float:left;
    }
    .input-small{
        width:75% !important;
    }
    .addbtn,.delbtn{
        height: 34px;
        padding: 7px 4px;
        text-decoration:none;
    }
    .filter a{
        text-decoration: none;
    }

</style>
    <h1 class="page-title font-red-intense"> Exception List
        <small>Exception.</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
						<div class="col-md-4">
							@permission('exception-create')
                            <div class="btn-group">
                                <a href="{{ url('exception/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
							@endpermission
                            
                        </div>
						<div class="col-md-8 " >

                            @permission('exception-import')
                            <input id="importFile" name="importFile" type="file" style="display:none">
							{{ csrf_field() }}
							<input id="importFileTxt" name="importFileTxt" type="text" class="form-control input-inline">
							<a id="importButton" class="btn red input-inline" >Browse</a>

							<button id="importSubmit" class="btn blue input-inline">Upload</button>
		
							<a href="{{ url('/uploads/exception/exception.xls')}}" class="help-inline" style="margin-top:8px;margin-left:10px;">Template </a>
                            @endpermission
                            @permission('exception-export')
                            <div class="btn-group " style="float:right;">
                                <button id="vl_list_export" class="btn sbold blue"> Export
                                    <i class="fa fa-download"></i>
                                </button>
                               
                            </div>
							@endpermission
                        </div>
						<div style="clear:both"></div>
                    <div class="table-container">
						<?php if( Auth::user()->can(['exception-batch-update']) || array_get($mygroups,'manage_groups')){ ?>

                        <div class="table-actions-wrapper">
                            <span> </span>
                            <select id="process_status" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select...</option>
                                <option value="done">Done</option>
								<option value="auto done">Auto Done</option>
                                <option value="cancel">Cancelled</option>
								<option value="auto_sap">Auto SAP</option>
								<option value="auto_mcf">Auto MCF</option>
                            </select>
							
																	
                            <input id="process_content" class="table-group-action-input form-control input-inline input-small input-sm">
                               
                            <button class="btn btn-sm green table-group-action-submit">
                                <i class="fa fa-check"></i> Batch Process</button>
                        </div>
						<?php } ?>
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                            <input type="hidden" id='linkIndex' value="{{$linkIndex}}" />
                            <thead>
                            <tr role="row" class="heading">
                                <th width="2%">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                        <span></span>
                                    </label>
                                </th>
                                <th width="8%"> Account </th>
                                
                                <th width="8%"> OrderID </th>
                                <th width="8%"> Type </th>
								<th width="15%">Order Sku </th>
								<th width="10%">Create Date</th>
								<th width="5%">Status</th>
								<th width="22%"> Operate </th>
                                <th width="8%"> Operator </th>
								<th width="15%">Creator</th>
                                <th width="8%"> BGBU </th>
                                <th width="8%"> Sales </th>
                                <th width="8%"> Process Remark </th>
                                <th width="5%"> Action </th>
                            </tr>
                            <tr role="row" class="filter">
                                <td> </td>
                                <td>
								<select id="sellerid" class="form-control form-filter input-sm" name="sellerid">
								<option value ="">Select
								@foreach ($sellerids as $id=>$name)
									<option value="{{$id}}">{{$name}}</option>
								@endforeach
								</select>
                                </td>
                              
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="amazon_order_id">
                                </td>
                                <td>
									<select class="form-control form-filter input-sm" name="type">
                                        <option value="">Select...</option>
                                        <option value="2">Replacement
                                        <option value="1">Refund
                                        <option value="4">Gift Card
                                    </select>
                                </td>
								
							
								<td>
                                    <input type="text" class="form-control form-filter input-sm" name="order_sku">
                                </td>
								
                                <td>
                                    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                </td>
                                <td>
                                    <select name="status" class="form-control form-filter input-sm">
                                        <option value="">Select...</option>
                                        <option value="submit">Pending</option>
                                        <option value="cancel" @if($linkIndex==3) selected @endif>Cancelled</option>
                                        <option value="done">Done</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="auto done">Auto Done</option>
										<option value="auto_failed">Auto Failed</option>
										<option value="sap_failed">Sap Failed</option>
                                    </select>
                                </td>

                               
								 <td>
                                <select id="resellerid" class="mt-multiselect btn btn-default form-control form-filter input-sm" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="resellerid">
								<option value ="">Replace Account
									<option value ="FBM">FBM	
								@foreach ($sellerids as $id=>$name)
									<option value="{{$id}}">{{$name}}</option>
								@endforeach
								</select>
								<input type="text" class="form-control form-filter input-sm" name="resku" placeholder="Replace SKU">
                                </td>
								 <td>
                                     <input type="hidden" class="form-filter"  id="from-operatorid" name="operator_id" value="">
                                     <select class="mt-multiselect btn btn-default select-operator-id" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="operator_id[]" id="operator_id[]" value="">
                                         @foreach ($users as $user_id=>$user)
                                             <option value="{{$user_id}}">{{$user}}</option>
                                         @endforeach
                                     </select>
                                </td>
                                <td>
                                    
                                    <select class="mt-multiselect btn btn-default form-control form-filter input-sm" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="group_id">
                                        <option value="">Group</option>
										@foreach ($groups as $group_id=>$group)
										
											<option value="{{$group_id}}">{{array_get($group,'group_name')}}</option>
											
										@endforeach
                                    </select>

                                    <div class="usergrouptot">
                                        <input type="hidden" class="form-filter"  id="from-userid" name="user_id" value="{{$currentUserId}}">
                                        <select class="mt-multiselect btn btn-default select-user-id" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]" value="">
                                            @foreach ($users as $user_id=>$user)
                                                <option value="{{$user_id}}" @if($currentUserId==$user_id) selected @endif>{{$user}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
								<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">BGBU</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 
											$selected = '';
											if($bg!=$team->bg) echo '<option value="'.$team->bg.'_">'.$team->bg.'</option>';	
											$bg=$team->bg;
											$selected = '';
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'" '.$selected.'>'.$team->bg.' - '.$team->bu.'</option>';
										} ?>
                                    </select>
									
									</td>
                                <td>
								<select class="mt-multiselect btn btn-default form-control form-filter input-sm" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="sap_seller_id" id="sap_seller_id">
										<option value="">Sellers</option>
                                        @foreach ($sap_sellers as $user_id=>$user_name)
                                            <option value="{{$user_id}}" >{{$user_name}}</option>
                                        @endforeach
                                    </select>
								</td>
                                <td></td>
                                <td>
                                    <div class="margin-bottom-5">
                                        <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
                                            <i class="fa fa-search"></i> Search</button>
                                    </div>
                                    <button class="btn btn-sm red btn-outline filter-cancel">
                                        <i class="fa fa-times"></i> Reset</button>
                                </td>
                            </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




<script>
    var TableDatatablesAjax = function () {

        var initPickers = function () {
            //init date pickers
            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
        }

        var initTable = function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            var grid = new Datatable();

            grid.init({
                src: $("#datatable_ajax"),
                onSuccess: function (grid, response) {
                    // grid:        grid object
                    // response:    json object of server side ajax response
                    // execute some code after table records loaded
                },
                onError: function (grid) {
                    // execute some code on network or other general error
                },
                //onDataLoad每次表格数据加载完后都执行（1. 表格初始化后 2. 重新设置搜索条件，点击search按钮后 3. 列表分页时，切换页面）
                onDataLoad: function(grid) {
                    // execute some code on ajax data load
                    //如果将linkIndex的值清空，从service链接过来的列表有多页时，切换页面就不能正确显示每页内容以及总记录数
                    //grid.setAjaxParam("linkIndex", '');
                },
                loadingMessage: 'Loading...',
                dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                    // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                    // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                    // So when dropdowns used the scrollable div should be removed.
                    "dom": "<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'>>",

                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 ,4,7,8,9,10,11,12,13] }],
                    "lengthMenu": [
                        [10, 20, 50],
                        [10, 20, 50] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('exception/get')}}", // ajax source
                    },
                    "order": [
                        [5, "desc"]
                    ],// set first column as a default sort by asc
                    "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(4).attr('style', 'text-align: left;')
						 $(row).children('td').eq(7).attr('style', 'max-width: 250px;word-wrap: break-word;word-break: normal;text-align: left; ')

                    },
                }
            });

            // handle group actionsubmit button click
            grid.getTableWrapper().on('click', '.table-group-action-submit', function (e) {
                e.preventDefault();
                var process_status = $("#process_status", grid.getTableWrapper());
                var process_content = $("#process_content", grid.getTableWrapper());
				
                if ((process_status.val() != "" || process_content.val() != "") && grid.getSelectedRowsCount() > 0) {
                    grid.setAjaxParam("customActionType", "group_action");
                    grid.setAjaxParam("process_status", process_status.val());
                    grid.setAjaxParam("process_content", process_content.val());
                    grid.setAjaxParam("id", grid.getSelectedRows());
                    grid.getDataTable().draw(false);
                    //grid.clearAjaxParams();
                } else if (process_status.val() == "" && process_content.val() == "") {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'Please select an action',
                        container: grid.getTableWrapper(),
                        place: 'prepend'
                    });
                } else if (grid.getSelectedRowsCount() === 0) {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'No record selected',
                        container: grid.getTableWrapper(),
                        place: 'prepend'
                    });
                }
            });

            //grid.setAjaxParam("customActionType", "group_action");
            //linkIndex 从service页面的超链接带过来。
            grid.setAjaxParam("linkIndex", $("#linkIndex").val());
            grid.setAjaxParam("sellerid", $("input[name='sellerid']").val());
            grid.setAjaxParam("amazon_order_id", $("input[name='amazon_order_id']").val());
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("type", $("select[name='subject']").val());
            grid.setAjaxParam("order_sku", $("input[name='order_sku']").val());
			grid.setAjaxParam("status", $("select[name='status']").val());
            // grid.setAjaxParam("status", $("select[name='mcf_status']").val());
            grid.setAjaxParam("operator_id", $("input[name='operator_id']").val());
            grid.setAjaxParam("user_id", $("input[name='user_id']").val());
			grid.setAjaxParam("group_id", $("select[name='group_id']").val());
			grid.setAjaxParam("bgbu", $("select[name='bgbu']").val());
			grid.setAjaxParam("sap_seller_id", $("select[name='sap_seller_id']").val());
            grid.getDataTable().ajax.reload(null,false);
            //grid.clearAjaxParams();
        }


        return {

            //main function to initiate the module
            init: function () {
                initPickers();
                initTable();
            }

        };

    }();

$(function() {
    //点击搜索框的重置的时候
    $('.filter-cancel').click(function(){
        $('#from-userid').val('');
        $('#from-operatorid').val('');
    })

    $("table").delegate(".select-user-id","change",function(){
        var user_id = $("select[name='user_id[]']").val();
        $('#from-userid').val(user_id);
    });

    $("table").delegate(".select-operator-id","change",function(){
        var operator_id = $("select[name='operator_id[]']").val();
        $('#from-operatorid').val(operator_id);
    });

    TableDatatablesAjax.init();

	$("#vl_list_export").click(function(){
		location.href='/exceptionexport?sellerid='+$("select[name='sellerid']").val()+'&amazon_order_id='+$("input[name='amazon_order_id']").val()+'&date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&type='+$("select[name='type']").val()+'&order_sku='+$("input[name='order_sku']").val()+'&status='+$("select[name='status']").val()+'&user_id='+$("input[name='user_id']").val()+'&group_id='+$("select[name='group_id']").val()+'&operator_id='+$("input[name='operator_id']").val()+'&resellerid='+$("select[name='resellerid']").val()+'&resku='+$("input[name='resku']").val()+'&bgbu='+$("select[name='bgbu']").val()+'&sap_seller_id='+$("select[name='sap_seller_id']").val();
	});


    $("#importButton,#importFileTxt").click(function(){
		$("#importFile").trigger("click");
	});

	$('input[id=importFile]').change(function() {
		$('#importFileTxt').val($(this).val());
	});

	$("#importSubmit").click(function () {
		var fileObj = document.getElementById("importFile").files[0];
		if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
			alert("Please Select File!");
			return false;
		}
		var formFile = new FormData();
		formFile.append("file", fileObj);
		var data = formFile;
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			url: "/exception/upload",
			data: data,
			type: "Post",
			dataType: "json",
			cache: false,
			processData: false,
			contentType: false,
			success: function (result) {
				if(result.customActionStatus=='OK'){  
					toastr.success(result.customActionMessage);
                    var dttable = $('#datatable_ajax').dataTable();
					dttable.api().ajax.reload(null, false);
				}else{
					toastr.error(result.customActionMessage);
				}
			},
			error: function(result) {
                toastr.error(result.responseText);
			}
		});
	});
});


</script>


@endsection
