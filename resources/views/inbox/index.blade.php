@extends('layouts.layout')
@section('label', 'Inbox List')
@section('content')
    <h1 class="page-title font-red-intense"> Inbox List
        <small>The mail history of your received.</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">

                    <div class="table-container">
                        <div class="table-actions-wrapper">
                            <span> </span>
							<input type="hidden" id='mailType' value="{{$type}}" />
                            <select id="replyStatus" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select...</option>
                                <option value="1">Do not need to reply</option>
                                <option value="0">Need reply</option>
                            </select>
							
																	
                            <select id="giveUser" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select...</option>
                                @foreach ($groups as $group_id=>$group)
                                    <optgroup label="{{array_get($group,'group_name')}}">
										@foreach (array_get($group,'user_ids') as $user_id)
										<option value="{{$group_id.'_'.$user_id}}">{{array_get($users,$user_id)}}</option>
										@endforeach
									</optgroup>
                                @endforeach
                            </select>
							
							<select id="giveMark" class="table-group-action-input form-control input-inline input-small input-sm" name="mark">
								<option value="">Select...</option>
								@foreach (getMarks() as $mark)
									<option value="{{$mark}}">{{$mark}}</option>
								@endforeach
							</select>
                            <button class="btn btn-sm green table-group-action-submit">
                                <i class="fa fa-check"></i> Change</button>
                        </div>
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_{{$type}}">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="2%">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_{{$type}} .checkboxes" />
                                        <span></span>
                                    </label>
                                </th>
                                <th width="30%"> From </th>
                                <th width="20%"> To </th>
                                <th width="30%"> Subject </th>
                                <th width="15%"> Date </th>

                                <th width="5%"> Action </th>
                            </tr>
                            <tr role="row" class="filter">
                                <td> </td>
                                <td>
								<div class="input-group margin-bottom-5">
                                    <input type="text" class="form-control form-filter input-sm" name="from_address">
									</div>
									<div class="input-group ">
									<select name="reply" class="form-control form-filter input-sm">
                                        <option value="">Select Status</option>
                                        <option value="2">Replied</option>
                                        <option value="1">Do not need to reply</option>
                                        <option value="0">Need reply</option>
                                    </select>
									</div>
                                </td>
                                <td>
								<div class="input-group margin-bottom-5">
                                    <input type="text" class="form-control form-filter input-sm" name="to_address">
									
									</div>
									<div class="input-group col-md-6 pull-left">
									<select class="form-control form-filter input-sm  " name="group_id">
                                        <option value="">Group</option>
										@foreach (array_get($mygroups,'groups',array()) as $group_id=>$group)
										
											<option value="{{$group_id}}">{{array_get($groups,$group_id.'.group_name')}}</option>
											
										@endforeach
                                    </select>
									</div>
									<div class="input-group col-md-6 pull-left">
									<select class="form-control form-filter input-sm " name="user_id">
                                        <option value="">User</option>
										@foreach (array_get($mygroups,'users',array()) as $user_id=>$user)
										
											<option value="{{$user_id}}">{{array_get($users,$user_id)}}</option>
											
										@endforeach
                                    </select>
									</div>
                                </td>
                                <td>
								<div class="input-group margin-bottom-5">
                                    <input type="text" class="form-control form-filter input-sm" name="subject">
									</div>
									<div class="input-group ">
									<select class="form-control form-filter input-sm" name="mark">
                                        <option value="">Select...</option>
                                        @foreach (getMarks() as $mark)
                                            <option value="{{$mark}}">{{$mark}}</option>
                                        @endforeach
                                    </select>
									</div>
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
                src: $("#datatable_ajax_{{$type}}"),
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
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 5 ] }],
                    "lengthMenu": [
                        [10, 20, 50],
                        [10, 20, 50] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('inbox/get')}}", // ajax source
                    },
                    "order": [
                        [4, "desc"]
                    ],// set first column as a default sort by asc
                    "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(1).attr('style', 'text-align: left;word-break: break-all; ')
						$(row).children('td').eq(2).attr('style', 'text-align: left;')
						$(row).children('td').eq(3).attr('style', 'text-align: left;')
						$(row).children('td').eq(4).attr('style', 'text-align: left;')
                    },
                }
            });

            // handle group actionsubmit button click
            grid.getTableWrapper().on('click', '.table-group-action-submit', function (e) {
                e.preventDefault();
                var replyStatus = $("#replyStatus", grid.getTableWrapper());
                var giveUser = $("#giveUser", grid.getTableWrapper());
				var giveMark = $("#giveMark", grid.getTableWrapper());
				
                if ((replyStatus.val() != "" || giveUser.val() != "" || giveMark.val() != "") && grid.getSelectedRowsCount() > 0) {
                    grid.setAjaxParam("customActionType", "group_action");
                    grid.setAjaxParam("replyStatus", replyStatus.val());
                    grid.setAjaxParam("giveUser", giveUser.val());
					grid.setAjaxParam("giveMark", giveMark.val());
                    grid.setAjaxParam("id", grid.getSelectedRows());
                    grid.getDataTable().draw(false);
                    //grid.clearAjaxParams();
                } else if (replyStatus.val() == "" && giveUser.val() == "" && giveMark.val() == "") {
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
			grid.setAjaxParam("mail_type", $("#mailType").val());
            grid.setAjaxParam("from_address", $("input[name='from_address']").val());
            grid.setAjaxParam("to_address", $("input[name='to_address']").val());
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("subject", $("input[name='subject']").val());
            grid.setAjaxParam("reply", $("select[name='reply']").val());
			grid.setAjaxParam("remark", $("select[name='remark']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id']").val());
			grid.setAjaxParam("group_id", $("select[name='group_id']").val());
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
