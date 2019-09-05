@extends('layouts.layout')
@section('label', 'Tasks')
@section('content')

    <h1 class="page-title font-red-intense"> Tasks
        <small></small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
					
                    <form role="form" action="{{url('task')}}" method="GET">
						<div class="row" style="margin-bottom:20px;">
						
							<div class="col-md-6">
								@permission('task-create')
								<div class="btn-group">
									<a data-target="#ajax" data-toggle="modal" href="{{ url('task/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Create New Tasks
										<i class="fa fa-plus"></i>
									</button>
									</a>
								</div>
								@endpermission
							</div>
						
						</div>
                        {{ csrf_field() }}
                        <div class="row">
						
						<div class="col-md-3">
							<div class="col-md-6">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Date From" value="{{$date_from}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Date to" value="{{$date_to}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
						</div>
						@permission('task-show-all')
						<div class="col-md-3">
						<div class="col-md-6">
						<select class="mt-multiselect btn btn-default input-sm form-control form-filter" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Assigned To" name="response_user_id[]" id="response_user_id[]">

                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						
						<div class="col-md-6">
						<select class="mt-multiselect btn btn-default input-sm form-control form-filter" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Assigned By" name="request_user_id[]" id="request_user_id[]">

                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						</div>
						@endpermission
						<div class="col-md-3">
						<div class="col-md-6">
						<select class="form-control form-filter input-sm" name="type">
                                        <option value="">Task Type</option>
										@foreach (getTaskTypeArr() as $k=>$v)
                                            <option value="{{$k}}">{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>	

						<div class="col-md-6">
						<select class="form-control form-filter input-sm" name="stage">
							<option value="">Task Stage</option>
							@foreach (getTaskStageArr() as $k=>$v)
								<option value="{{$k}}">{{$v}}</option>
							@endforeach
						</select>
                                       
						</div>
						</div>
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="keywords" placeholder="Keywords" value ="{{array_get($_REQUEST,'keywords')}}">
                                       
						</div>	
						
						<div class="col-md-1"><button type="button" class="btn blue" id="data_search">Search</button>
						</div>
						</div>
						
					
                    </form>
					<div style="clear:both;"></div>
                </div>
			
                <div class="table-container">

                    <table class="table table-striped table-bordered table-hover " id="datatable_ajax">
                        <thead>
							
                            <tr role="row" class="heading">
								<th> Task Type </th>	
								<th> Task Details </th>	
								<th> Priority </th>	
								<th> Assigned By </th>	
								<th > Due To </th>	
								<th> Assigned To</th>
								<th> Stage </th>
								<th> Create Date </th>
								<th> Score </th>
								<th> </th>
                            </tr>
							
                            
                            </thead>
                            <tbody></tbody>
                    </table>
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
			grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("response_user_id", $("select[name='response_user_id[]']").val());
			grid.setAjaxParam("request_user_id", $("select[name='request_user_id[]']").val());
			grid.setAjaxParam("keywords", $("input[name='keywords']").val());
			grid.setAjaxParam("type", $("select[name='type']").val());
			grid.setAjaxParam("stage", $("select[name='stage']").val());
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
                onDataLoad: function(grid) {
                    // execute some code on ajax data load
                },
                loadingMessage: 'Loading...',
                dataTable: { 
					"bStateSave": true,
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 20, // default record count per page
					"aoColumnDefs": [{ "bSortable": false, "aTargets": [1,9] }],	
					"order": [
                        [7, "desc"]
                    ],
                    "ajax": {
                        "url": "{{ url('task/get')}}", // ajax source
                    },
					"createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(1).attr('style', 'max-width: 400px;width: 400px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(1).attr('title', $(row).children('td').eq(10).text());
                    },
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
	$('#data_search').on('click',function(){
		var dttable = $('#datatable_ajax').dataTable();
	    dttable.fnClearTable(false); //清空一下table
	    dttable.fnDestroy(); //还原初始化了的datatable
		TableDatatablesAjax.init();
	});
	
});


</script>

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
@endsection

