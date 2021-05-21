@extends('layouts.layout')
@section('label', '平台SKU对照表')
@section('content')
<style type="text/css">
	th, td { white-space: nowrap;word-break:break-all; }
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('platformsku')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择平台" name="platform[]" id="platform[]" >
                            @foreach (\App\Models\PlatformOrder::PLATFORM as $k=>$v)
                                <option value="{{$k}}" >{{$v}}</option>
                            @endforeach
                        </select>
						</div>

						
                        <div class="col-md-2">
						<input type="text" class="form-control " name="country_code" placeholder="国家代码">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control " name="platform_sku" placeholder="平台SKU">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control" name="product_sku" placeholder="谷仓SKU">
						</div>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择用户" name="user_id[]" id="user_id[]">
                            @foreach (getUsers() as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>
						
						
						    <div class="col-md-2">
							
							<button type="button" class="btn blue" id="data_search">搜索</button>
									
                            </div>
					    </div>

                    </form>
					
					
					
					
					
					
                </div>
				
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">平台SKU对照表</span>
                        <a data-target="#ajax" data-toggle="modal" href="/platformsku/create"> 
                                   
                        <button class="btn  green ">
                                <i class="fa fa-plus"></i> 添加记录
                        </button>
                        </a>
                    </div>
                    <div class="btn-group " style="float:right;margin-top:10px;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                <option value="">选择更新状态</option>
                                <option value="-1">删除</option>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> 执行批量更新
                            </button>

                            
                        		
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
                                    <th>平台</th>
									<th>国家/站点</th>
									<th>平台SKU</th>
									<th>谷仓SKU</th>
									<th>编辑用户</th>
									<th>创建时间</th>
									<th>编辑时间</th>                              
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
            grid.setAjaxParam("country_code", $("input[name='country_code']").val());
			grid.setAjaxParam("platform_sku", $("input[name='platform_sku']").val());
			grid.setAjaxParam("product_sku", $("input[name='product_sku']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
            grid.setAjaxParam("platform", $("select[name='platform[]']").val());
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
                        [10, 20, 50, -1],
                        [10, 20, 50, 'All'] 
                    ],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ url('platformsku/get')}}",
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
                     ]
                     */
                 }
            });


            //批量更改状态操作
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
                        url: "{{ url('platformsku/batchUpdate') }}",
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
            remote: '/platformsku/'+recordId+'/edit'
        });
    } );
	$('#ajax').on('hidden.bs.modal', function (e) {
        $('#ajax .modal-content').html('<div class="modal-body" >Loading...</div>');
    });
});


</script>

<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" >
				Loading...
			</div>
		</div>
	</div>
</div>
@endsection

