@extends('layouts.layout')
@section('label', 'SendBox List')
@section('content')
    <h1 class="page-title font-red-intense"> SendBox List
        <small>The mail history of your send out.</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
					<div class="btn-group " style="float:left;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
							@permission('skuforuser-batch-update')
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select Status</option>
                                <option value="Draft">WithDraw (Errors and Waitings)</option>
                                <option value="Waiting">ReSend (Errors and Drafts)</option>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> Update
                            </button>
                        	@endpermission	
                        </div>
                    </div>
                </div>
                <div class="portlet-body">

                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_customer">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="2%">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_customer .checkboxes" />
                                        <span></span>
                                    </label>
                                </th>

                                <th width="15%"> From </th>
                                <th width="10%"> To </th>
                                <th width="35%"> Subject </th>
                                <th width="15%"> Date </th>
								<th width="10%"> User </th>
                                <th width="10%"> Status </th>
                                <th width="10%"> Action </th>
                            </tr>
                            <tr role="row" class="filter">
                                <td> </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="from_address">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="to_address">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="subject">
                                </td>
                                <td>
                                    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From" value="{{date('Y-m-d',strtotime('-7 days'))}}">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To" value="{{date('Y-m-d')}}">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                </td>
								<td>
                                    <?php //if(Auth::user()->admin){ ?>
                                    <select class="mt-multiselect btn btn-default form-control form-filter input-sm" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id">
                                        <option value="">Select...</option>
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
                                    <?php //} ?>
                                </td>
                                <td>
                                    <select name="status" class="form-control form-filter input-sm">
                                        <option value="">Select...</option>
                                        <option value="Send">Send</option>
                                        <option value="Waiting">Waiting</option>
										<option value="Draft">Draft</option>
                                        <option value="Error">Error</option>
                                    </select>
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

            grid.setAjaxParam("from_address", $("input[name='from_address']").val());
            grid.setAjaxParam("to_address", $("input[name='to_address']").val());
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("subject", $("input[name='subject']").val());
            grid.setAjaxParam("status", $("select[name='status']").val());

            grid.init({
                src: $("#datatable_ajax_customer"),
                onSuccess: function (grid, response) {
                    grid.setAjaxParam("customActionType", '');
                },
                onError: function (grid) {
                    // execute some code on network or other general error
                },
                onDataLoad: function(grid) {
                    // execute some code on ajax data load
                },
                loadingMessage: 'Loading...',
                dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                    // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                    // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                    // So when dropdowns used the scrollable div should be removed.
                    //"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",

                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 5,6 ] }],
                    "lengthMenu": [
                        [10, 20, 50],
                        [10, 20, 50] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('send/get')}}", // ajax source
                    },
                    "order": [
                        [4, "desc"]
                    ],// set first column as a default sort by asc
                    "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(2).attr('style', 'text-align: left;')
                    },
                }
            });

            // handle group actionsubmit button click

            $(".btn-group").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();
                var confirmStatus = $("#confirmStatus", $("#table-actions-wrapper"));

                if (confirmStatus.val() != "" && grid.getSelectedRowsCount() > 0) {
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                    });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ url('send/batchUpdate') }}",
                        data: {confirmStatus:confirmStatus.val(),id:grid.getSelectedRows()},
                        success: function (data) {
                            if(data.customActionStatus=='OK'){
                                grid.getDataTable().draw(false);
                                toastr.success(data.customActionMessage);
                            }else{
                                toastr.error(data.customActionMessage);
                            }
                        },
                        error: function(data) {
                            toastr.error(data.responseText);
                        }
                    });
                } else if ( confirmStatus.val() == "" ) {
                    toastr.error('Please select an action');
                } else if (grid.getSelectedRowsCount() === 0) {
                    toastr.error('No record selected');
                }
            });
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
});
</script>


@endsection
