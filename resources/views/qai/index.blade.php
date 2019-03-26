@extends('layouts.layout')
@section('label', 'Asins List')
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
	font-family:Arial, Helvetica, sans-serif;}

</style>
    <h1 class="page-title font-red-intense"> Qa List
        <small>Configure your Qa.</small>
    </h1>
    <div class="row">



        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
					<div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a href="{{ url('qa/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
                        </div>
                       
                        <div class="col-md-6" style="text-align: right;">
                            <div class="table-actions-wrapper" id="table-actions-wrapper">
                            <span> </span>
                            <select id="QaAction" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select Action</option>
                                <option value="delete">Delete Qa</option>
								<option value="confirm">Confirm Qa</option>
								<option value="unconfirm">UnConfirm Qa</option>
                            </select>

                            
                            <button class="btn btn-sm green table-group-action-submit">
                                <i class="fa fa-check"></i> Change</button>
                        </div>
                        </div>
                        
                    </div>
                </div>

                    <div class="col-md-2" style=" padding-left: 0px;">
                        <div class="input-group">
                            <span class="input-group-addon">Group</span>
                            <input type="text" class="xform-autotrim form-control" data-init-by-query="ands.group" placeholder="Group..." id="group" autocomplete="off" name="group" />
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Item Group</span>
                            <input type="text" class="xform-autotrim form-control" data-init-by-query="ands.item_group" placeholder="Item Group..." id="item_group" autocomplete="off" name="item_group" />
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Model</span>
                            <input type="text" class="xform-autotrim form-control" data-init-by-query="ands.item_model" placeholder="Item Model..." id="item_model" autocomplete="off" name="item_model" />
                        </div>
                    </div>

                    <div class="col-md-2">
                        <input style="height: 34px;" type="text" class="form-control form-filter input-sm" name="keywords" placeholder="Keywords">

                    </div>
                    <div class="col-md-2">

                        <button type="button" class="btn blue" id="data_search">Search</button>

                    </div>

                    <div style="height:50px;"></div>

                    <div class="table-container">
                        
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_asin">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="2%">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_asin .checkboxes" />
                                        <span></span>
                                    </label>
                                </th>
								<th width="10%">Created Date</th>
                                <th width="10%">Created User</th>
                                <th width="10%">Status</th>
								<th width="38%">Title</th>
								<th width="10%">Clicks</th>
								<th width="10%">Update Date</th>
                                <th width="10%">Action</th>
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
                src: $("#datatable_ajax_asin"),
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
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 7 ] }],
                    "lengthMenu": [
                        [10, 20, 50, -1],
                        [10, 20, 50, 'All'] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
					 buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'reviews' }
                    ],
                    "ajax": {
                        "url": "{{ url('qa/get')}}", // ajax source
                    },
                    "order": [
                        [1, "desc"]
                    ],// set first column as a default sort by asc
                    "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(4).attr('style', 'text-align: left;padding-left: 5px;padding-right: 5px;')
                    },
                }
            });

            // handle group actionsubmit button click
			//$("#table-actions-wrapper") grid.getTableWrapper()
            $("#table-actions-wrapper").on('click', '.table-group-action-submit', function (e) {
                e.preventDefault();
                var QaAction = $("#QaAction", $("#table-actions-wrapper"));

				
                if ((QaAction.val() != "") && grid.getSelectedRowsCount() > 0) {
                    grid.setAjaxParam("customActionType", "group_action");
                    grid.setAjaxParam("QaAction", QaAction.val());
                    grid.setAjaxParam("id", grid.getSelectedRows());
                    grid.getDataTable().draw(false);
                    //grid.clearAjaxParams();
                } else if (QaAction.val() == "") {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'Please select an action',
                        container: $("#table-actions-wrapper"),
                        place: 'prepend'
                    });
                } else if (grid.getSelectedRowsCount() === 0) {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'No record selected',
                        container: $("#table-actions-wrapper"),
                        place: 'prepend'
                    });
                }
            });

            //grid.setAjaxParam("customActionType", "group_action");
//            grid.setAjaxParam("product", $("input[name='product']").val());
//            grid.setAjaxParam("model", $("input[name='model']").val());
//			grid.setAjaxParam("product_line", $("input[name='product_line']").val());
//            grid.setAjaxParam("item_no", $("input[name='item_no']").val());
//			grid.setAjaxParam("title", $("input[name='title']").val());
//            grid.setAjaxParam("confirm", $("select[name='confirm']").val());
//   			grid.setAjaxParam("etype", $("select[name='etype']").val());
//			grid.setAjaxParam("epoint", $("input[name='epoint']").val());
//            grid.setAjaxParam("update_date_from", $("input[name='update_date_from']").val());
//            grid.setAjaxParam("update_date_to", $("input[name='update_date_to']").val());
//            grid.setAjaxParam("user_id", $("select[name='user_id']").val());
            grid.setAjaxParam("group", $("input[name='group']").val());
            grid.setAjaxParam("item_group", $("input[name='item_group']").val());
            grid.setAjaxParam("item_model", $("input[name='item_model']").val());
            grid.setAjaxParam("keywords", $("input[name='keywords']").val());
            grid.getDataTable().ajax.reload(null,false);
            //grid.clearAjaxParams();
        };


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
    $('#data_search').on('click',function(){
        var dttable = $('#datatable_ajax_asin').dataTable();
        dttable.fnDestroy();
        dttable.fnClearTable();
        dttable.fnDestroy();
        TableDatatablesAjax.init();
    });
});

</script>


@endsection
