@extends('layouts.layout')
@section('label')
<a href="/adv">Advertising</a>  - Campaigns <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting">{{array_get($campaign,'name')}}</a>
@endsection
@section('content')
<style type="text/css">
	th, td { white-space: nowrap;word-break:break-all; }
    .unavailable{
        background-color: transparent;
        float: none;
    }
    .available{
        background-color: transparent;
        float: none;
    }
    .row {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .portlet.light   .portlet-title   .caption {
        color: #666;
        padding: 10px 0;
    }
</style>
<h1 class="page-title font-red-intense"> Campaign - {{array_get($campaign,'name')}}
</h1>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            
            <div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li >
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting"> Setting</a>
                </li>
                <li >
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/adgroup" >Ad Groups</a>
                </li>
                <li >
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/negkeyword" >Negative keywords</a>
                </li>
                <li>
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/negproduct" >Negative products</a>
                </li>
                <li class="active">
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/schedule" >Schedules</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="table-toolbar">
                    <form role="form" action="{{url('adv')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                        <input type="hidden" name="profile_id" value="{{$profile_id}}">
                        <input type="hidden" name="ad_type" value="{{$ad_type}}">
                        <input type="hidden" name="campaign_id" value="{{array_get($campaign,'campaignId')}}">
                        <div class="col-md-2">
                        <select class="form-control" name="status" id="status" >
                            <option value="" >All Status</option>
                            @foreach (\App\Models\PpcSchedule::STATUS as $k=>$v)
                                <option value="{{$k}}" >{{$v}}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="col-md-2">
                        <select class="form-control" name="record_type" id="record_type" >
                            <option value="" >All Type</option>
                            @foreach (\App\Models\PpcSchedule::TYPE as $k=>$v)
                                <option value="{{$k}}" >{{$v}}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="col-md-2">
                        <input type="text" class="form-control" name="record_name" placeholder="keyword">
                        </div>
                            <div class="col-md-2">
                            <button type="button" class="btn blue" id="data_search">Search</button>		
                            </div>
                        </div>

                    </form>	
                </div>

                <div class="portlet-title">
                    <div class="caption font-dark col-md-12">
                        <div class="btn-group batch-update">
                            <div class="table-actions-wrapper" id="table-actions-wrapper">
                                <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                    <option value="">Select Status</option>
                                    @foreach (\App\Models\PpcSchedule::STATUS as $k=>$v)
                                        <option value="{{$k}}" >{{$v}}</option>
                                    @endforeach
                                </select>
                                <button class="btn  green table-status-action-submit">
                                    <i class="fa fa-check"></i> Batch Update
                                </button>
                                    
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th>
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                    </th>
									<th>Type</th>
                                    <th>Name</th>    
                                    <th>Status</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Time</th>
                                    <th>Content</th>
                                    <th>Last Exec</th>
                                    <th>Last Update</th>
                                    <th>User</th>        
                                </tr>
                            </thead>
                            <tbody>	
                            </tbody>
                        </table>
					</div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" >
				Loading...
			</div>
		</div>
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
            grid.setAjaxParam("profile_id", $("input[name='profile_id']").val());
            grid.setAjaxParam("ad_type", $("input[name='ad_type']").val());
            grid.setAjaxParam("campaign_id", $("input[name='campaign_id']").val());
            grid.setAjaxParam("record_name", $("input[name='record_name']").val());
            grid.setAjaxParam("status", $("select[name='status']").val());
            grid.setAjaxParam("record_type", $("select[name='record_type']").val());
            grid.init({
                src: $("#datatable_ajax"),
                onSuccess: function (grid, response) {
                    grid.setAjaxParam("customActionType", '');
                },
                onError: function (grid) {
                },
                onDataLoad: function(grid) {
                },
                loadingMessage: 'Loading...',
                dataTable: {
                   //"serverSide":false,
                   "autoWidth":false,
                   "ordering": false,
                    "lengthMenu": [
                        [50, 100, 300, -1],
                        [50, 100, 300, 'All'] 
                    ],
                    "pageLength": 300,
                    "ajax": {
                        "url": "{{ url('adv/listSchedules')}}",
                    },

					
                    //"scrollX": true,
                    //"autoWidth":true
                    /*
                    dom: 'Bfrtip',
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: '导出当前页',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 3,2,6,7,8,9,4,5 ]
                            }
                        },
                     ],
                     */
                    
                 }
            });


            //批量更改状态操作
            $(".batch-update").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();
                var confirmStatus = $("#confirmStatus", $("#table-actions-wrapper"));
                if (confirmStatus.val() != "" && grid.getSelectedRowsCount() > 0) {
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                    });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ url('adv/scheduleBatchUpdate') }}",
                        data: {confirmStatus:confirmStatus.val(),id:grid.getSelectedRows()},
                        success: function (data) {
                            if(data.customActionStatus=='OK'){
                                toastr.success(data.customActionMessage);
                                grid.getDataTable().draw(false);
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
            dttable.fnClearTable(false);
            dttable.fnDestroy(); 
            TableDatatablesAjax.init();
        });
        $('#datatable_ajax').on('click', 'td:not(:has(input))', function (e) {
            e.preventDefault();
            var recordId = $(this).closest('tr').find('.checkboxes').prop('value');
            $('#ajax').modal({
                remote: '/adv/scheduleEdit?id='+recordId
            });
        } );
        $('#ajax').on('hidden.bs.modal', function (e) {
            $('#ajax .modal-content').html('<div class="modal-body" >Loading...</div>');
        });
    });


</script>
@endsection

