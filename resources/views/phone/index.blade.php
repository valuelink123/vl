@extends('layouts.layout')
@section('label', 'Phone List')
@section('content')
    <h1 class="page-title font-red-intense"> Phone List
        <small>The Phone history of your received.</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
					<div class="row">
                        <div class="col-md-6">
							@permission('callmessage-create')
                            <div class="btn-group">
                                <a href="{{ url('phone/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
							@endpermission
                        </div>
                        <div class="col-md-6 " >
							@permission('callmessage-export')
                            <div class="btn-group " style="float:right;">
                                <button id="vl_list_export" class="btn sbold blue"> Export
                                    <i class="fa fa-download"></i>
                                </button>

                            </div>
							@endpermission
                        </div>
					</div>

                    <div style="clear:both"></div>
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_phone">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="2%">
                                    ID
                                </th>
                                <th width="15%"> Phone Nunber </th>
                                <th width="20%"> Buyer Email </th>
                                <th width="15%"> Amazon OrderID </th>
								<th width="20%"> Call Notes </th>
                                <th width="10%"> Creator </th>
                                <th width="10%"> Date </th>
                                <th width="5%"> Action </th>
                            </tr>
                            <tr role="row" class="filter">
                                <td><input type="text" class="form-control form-filter input-sm" name="id"></td>
                                <td>
								<input type="text" class="form-control form-filter input-sm" name="phone">
                                </td>
                                <td>
								<input type="text" class="form-control form-filter input-sm" name="buyer_email">
                                </td>
                                <td>
								<input type="text" class="form-control form-filter input-sm" name="amazon_order_id">
                                </td>
								<td>
								<input type="text" class="form-control form-filter input-sm" name="content">
                                </td>
                                {{--新添加的创建人搜索--}}
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="user_name">
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
                src: $("#datatable_ajax_phone"),
                onSuccess: function (grid, response) {
                    // grid:        grid object
                    // response:    json object of server side ajax response
                    // execute some code after table records loaded
                },
                onError: function (grid) {
                    // execute some code on network or other general error
                },
                onDataLoad: function(grid) {
                    // execute some code on ajax data load
                    //alert('123');
                    //alert($("#subject").val());
                    //grid.setAjaxParam("subject", $("#subject").val());
                },
                loadingMessage: 'Loading...',
                dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                    // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                    // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                    // So when dropdowns used the scrollable div should be removed.
                    "dom": "<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'>>",

                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 5 , 7] }],
                    "lengthMenu": [
                        [10, 20, 50],
                        [10, 20, 50] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('phone/get')}}", // ajax source
                    },
                    "order": [
                        [6, "desc"]
                    ],// set first column as a default sort by asc
                    "createdRow": function( row, data, dataIndex ) {
						$(row).children('td').eq(4).attr('style', 'text-align: left;word-break: break-all;')
                    },
                }
            });


            //grid.setAjaxParam("customActionType", "group_action");
            grid.setAjaxParam("phone", $("input[name='phone']").val());
            grid.setAjaxParam("buyer_email", $("input[name='buyer_email']").val());
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("amazon_order_id", $("input[name='amazon_order_id']").val());
			grid.setAjaxParam("content", $("input[name='content']").val());
            grid.setAjaxParam("user_name", $("input[name='user_name']").val());
            grid.setAjaxParam("id", $("input[name='id']").val());
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
    TableDatatablesAjax.init();
    $("#vl_list_export").click(function(){
        location.href='/phoneExport?user_name='+$("input[name='user_name']").val()+'&amazon_order_id='+$("input[name='amazon_order_id']").val()+'&date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&buyer_email='+$("input[name='buyer_email']").val()+'&content='+$("input[name='content']").val()+'&phone='+$("input[name='phone']").val()+'&id='+$("input[name='id']").val();
    });
});


</script>


@endsection
