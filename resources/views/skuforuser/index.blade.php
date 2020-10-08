@extends('layouts.layout')
@section('label', 'Sku For Users List')
@section('content')
<style type="text/css">
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 2px;
    text-align: left;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
.text{
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('skuforuser')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date" placeholder="Date" value="{{$date}}">
                                <span class="input-group-btn">
                                    <button class="btn btn-sm default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Producter" name="producter[]" id="producter[]">
                            @foreach ($users as $user_id=>$user_name)
                                <option value="{{$user_id}}">{{$user_name}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Planer" name="planer[]" id="planer[]">
                            @foreach ($users as $user_id=>$user_name)
                                <option value="{{$user_id}}">{{$user_name}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select DQE" name="dqe[]" id="dqe[]">
                            @foreach ($users as $user_id=>$user_name)
                                <option value="{{$user_id}}">{{$user_name}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select TE" name="te[]" id="te[]">
                            @foreach ($users as $user_id=>$user_name)
                                <option value="{{$user_id}}">{{$user_name}}</option>
                            @endforeach
                        </select>
						</div>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Status" name="status[]" id="status[]" >
                            @foreach (getSkuStatuses() as $key=>$v)
                                <option value="{{$key}}" >{{$v}}</option>
                            @endforeach
                        </select>
						</div>

						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
                            <div class="col-md-2">
                                <input type="text" class="form-control form-filter input-sm" name="sku" placeholder="skus" value ="{{array_get($_REQUEST,'sku')}}">             
                            </div>	
						
						
						
						    <div class="col-md-1">
							
							<button type="button" class="btn blue" id="data_search">Search</button>
									
                            </div>
					    </div>

                    </form>
					
					
					@permission('skuforuser-import')
					<div class="row" style="margin-top:30px">
                        <div class="col-md-4">
                            
                        </div>
						<div class="col-md-2">
						</div>
						<form action="{{url('skuforuser/upload')}}" method="post" enctype="multipart/form-data">
						<div class="col-md-2" style="text-align:right;" >

							<a href="{{ url('/uploads/skuforuserUpload/skuforuser.csv')}}" >Import Template
                                </a>	
						</div>
						<div class="col-md-2">
							{{ csrf_field() }}
								 <input type="file" name="importFile"  />
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn blue">Upload</button>

						</div>
						
						</form>
						
					</div>
					@endpermission
					
					
					
                </div>
				
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Sku For User List</span>
                    </div>
					
					<div class="btn-group " style="float:right;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
							@permission('skuforuser-batch-update')
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select Status</option>
                                <?php
                                foreach($status as $k=>$v){
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> Update
                            </button>
                        	@endpermission	
							@permission('skuforuser-export')

                            <button type="button" class="btn  green-meadow" id = "current_export">Export Current</button>

                            <button type="button" class="btn  green-meadow" id = "all_export">Export All</button>

                            <!--
                            <div class="btn-group">
                                <button type="button" class="btn  green-meadow">Export</button>
                                <button type="button" class="btn  green-meadow dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-download"></i>
                                </button>
                                <ul class="dropdown-menu" role="menu" id="vl_list_export">
                                    <li id="all">
                                        <a href="">All</a>
                                    </li>
                                    <li id="curent">
                                        <a href="">Current Page</a>
                                    </li>
                                </ul>
                            </div>-->
							@endpermission	
                        </div>
                    </div>
                </div>
				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax_skuforuser">
                            <thead>
                                <tr role="row" class="heading">
                                    <th style="width:20px;">
                                        <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                            <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_skuforuser .checkboxes" />
                                            <span></span>
                                        </label>
                                    </th>
                                    <th style="width:20px;">ID</th>
                                    <th style="width:50px;">Site</th>
                                    <th style="width:80px;">SKU</th>
                                    <th style="width:200px;">Description</th>
                                    <th style="width:80px;">Status</th>
                                    <th style="width:150px;">Producter</th>
                                    <th style="width:150px;">Planer</th>
                                    <th style="width:150px;">Dqe</th>
                                    <th style="width:150px;">Te</th>
                                </tr>
                            </thead>
                            <tbody>	
                            </tbody>
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
			grid.setAjaxParam("date", $("input[name='date']").val());
            grid.setAjaxParam("sku", $("input[name='sku']").val());
			grid.setAjaxParam("producter", $("select[name='producter[]']").val());
            grid.setAjaxParam("planer", $("select[name='planer[]']").val());
            grid.setAjaxParam("dqe", $("select[name='dqe[]']").val());
            grid.setAjaxParam("te", $("select[name='te[]']").val());
            grid.setAjaxParam("status", $("select[name='status[]']").val());
            grid.init({
                src: $("#datatable_ajax_skuforuser"),
                onSuccess: function (grid, response) {
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
                        [10, 20, 50, -1],
                        [10, 20, 50, 'All'] 
                    ],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ url('skuforuser/get')}}",
                    },

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
                     ]
                     */
                 }
            });

            //批量更改状态操作
            $(".btn-group").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();
                var confirmStatus = $("#confirmStatus", $("#table-actions-wrapper"));

                if (confirmStatus.val() != "" && grid.getSelectedRowsCount() > 0) {
                    grid.setAjaxParam("customActionType", "group_action");
                    grid.setAjaxParam("confirmStatus", confirmStatus.val());
                    grid.setAjaxParam("id", grid.getSelectedRows());
                    grid.getDataTable().draw(false);
                } else if ( confirmStatus.val() == "" ) {
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
		var dttable = $('#datatable_ajax_skuforuser').dataTable();
		dttable.fnClearTable(false); //清空一下table
	    dttable.fnDestroy(); //还原初始化了的datatable
		TableDatatablesAjax.init();
	});
	$("#vl_list_export li").click(function(){
        var baseUrl ='/skuforuserexport?status='+(($("select[name='status[]']").val())?$("select[name='status[]']").val():'')+'&sku='+$("input[name='sku']").val()+'&date='+$("input[name='date']").val()+'&producter='+(($("select[name='producter[]']").val())?$("select[name='producter[]']").val():'')+'&planer='+(($("select[name='planer[]']").val())?$("select[name='planer[]']").val():'')+'&dqe='+(($("select[name='dqe[]']").val())?$("select[name='dqe[]']").val():'')+'&te='+(($("select[name='te[]']").val())?$("select[name='te[]']").val():'');
        if(this.id =='curent'){
            var dttable = $('#datatable_ajax_skuforuser').dataTable();
            var oSettings = dttable.fnSettings();
            baseUrl = baseUrl+'&offset='+oSettings._iDisplayStart;
            baseUrl = baseUrl+'&limit='+oSettings._iDisplayLength;
        }
		location.href =  baseUrl;
	});

    $("#current_export").click(function(){
        var baseUrl ='/skuforuserexport?status='+(($("select[name='status[]']").val())?$("select[name='status[]']").val():'')+'&sku='+$("input[name='sku']").val()+'&date='+$("input[name='date']").val()+'&producter='+(($("select[name='producter[]']").val())?$("select[name='producter[]']").val():'')+'&planer='+(($("select[name='planer[]']").val())?$("select[name='planer[]']").val():'')+'&dqe='+(($("select[name='dqe[]']").val())?$("select[name='dqe[]']").val():'')+'&te='+(($("select[name='te[]']").val())?$("select[name='te[]']").val():'');
        if(this.id =='curent'){
            var dttable = $('#datatable_ajax_skuforuser').dataTable();
            var oSettings = dttable.fnSettings();
            baseUrl = baseUrl+'&offset='+oSettings._iDisplayStart;
            baseUrl = baseUrl+'&limit='+oSettings._iDisplayLength;
        }
		location.href =  baseUrl;
	});

    $("#all_export").click(function(){
        var baseUrl ='/skuforuserexport?status='+(($("select[name='status[]']").val())?$("select[name='status[]']").val():'')+'&sku='+$("input[name='sku']").val()+'&date='+$("input[name='date']").val()+'&producter='+(($("select[name='producter[]']").val())?$("select[name='producter[]']").val():'')+'&planer='+(($("select[name='planer[]']").val())?$("select[name='planer[]']").val():'')+'&dqe='+(($("select[name='dqe[]']").val())?$("select[name='dqe[]']").val():'')+'&te='+(($("select[name='te[]']").val())?$("select[name='te[]']").val():'');
		location.href =  baseUrl;
	});
});


</script>


@endsection

