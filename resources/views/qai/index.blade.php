@extends('layouts.layout')
@section('label', 'Asins List')
@section('content')
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
                       
                        <div class="col-md-6">
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
								<th width="10%">Product Line</th>
                                <th width="10%">Product</th>
                                <th width="10%"> Model </th>
								<th width="10%"> Item No. </th>
								<th width="20%"> Title </th>
                                <th width="10%"> User </th>
								<th width="10%"> Status </th>
                                <th width="15%"> Update Date </th>
                                <th width="5%"> Action </th>
                            </tr>
                            <tr role="row" class="filter">
                                <td> </td>
								<td>
                                    <input type="text" class="form-control form-filter input-sm" name="product_line">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="product">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="model">
                                </td>
								<td>
                                    <input type="text" class="form-control form-filter input-sm" name="item_no">
                                </td>
								<td>
                                    <input type="text" class="form-control form-filter input-sm" name="title">
                                </td>
                               
     
                                <td>
   
                                    <select class="form-control form-filter input-sm" name="user_id">
                                        <option value="">Select...</option>
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
                                </td>
								 <td>

                                    <select class="form-control form-filter input-sm" name="confirm">
										<option value="">Select...</option>
										<option value="unconfirm" >UnConfirm</option>
										<option value="confirm" >Confirmed</option>
										
									</select>
		
                                </td>
                                <td>

                                   <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="update_date_from" placeholder="From">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="update_date_to" placeholder="To">
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
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 9 ] }],
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
                        $(row).children('td').eq(3).attr('style', 'text-align: left;')
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
            grid.setAjaxParam("product", $("input[name='product']").val());
            grid.setAjaxParam("model", $("input[name='model']").val());
			grid.setAjaxParam("product_line", $("input[name='product_line']").val());
            grid.setAjaxParam("item_no", $("input[name='item_no']").val());
			grid.setAjaxParam("title", $("input[name='title']").val());
            grid.setAjaxParam("confirm", $("select[name='confirm']").val());
   
            grid.setAjaxParam("update_date_from", $("input[name='update_date_from']").val());
            grid.setAjaxParam("update_date_to", $("input[name='update_date_to']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id']").val());
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
});


</script>


@endsection
