@extends('layouts.layout')
@section('label', '调拨计划列表')
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
                    <form role="form" action="{{url('transferPlan')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择站点" name="marketplace_id[]" id="marketplace_id[]">
                            @foreach (getSiteCode() as $k=>$v)
                                <option value="{{$v}}">{{$k}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择BG" name="bg[]" id="bg[]">
                            @foreach (getUsers('sap_bg') as $k=>$v)
                                <option value="{{$v->bg}}">{{$v->bg}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择BU" name="bu[]" id="bu[]">
                            @foreach (getUsers('sap_bu') as $k=>$v)
                                <option value="{{$v->bu}}">{{$v->bu}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-2">
						<input type="text" class="form-control " name="out_factory" placeholder="调出工厂">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control " name="in_factory" placeholder="调入工厂">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control" name="asin" placeholder="Asin">
						</div>
						
						
						

						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
							
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="sku" placeholder="Sku">             
                            </div>	
							<div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择状态" name="status[]" id="status[]" >
                            @foreach ($status as $k=>$v)
                                <option value="{{$k}}" >{{$v}}</option>
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
                        <span class="caption-subject bold uppercase">调拨计划列表</span>
                    </div>
					
					<div class="btn-group " style="float:right;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
							
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">选择更新状态</option>
                                <?php
                                foreach($status as $k=>$v){
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> 执行批量更新
                            </button>
                        		
                        </div>
                    </div>
                </div>
				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th >
         
                                            <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                            
                                    </th>
                                    <th>站点</th>
									<th>Bg</th>
									<th>Bu</th>
									<th>调出工厂</th>
									<th>调入工厂</th>
									<th>Asin</th>
									<th>Sku</th>
									<th>计划状态</th>
									<th>申请数量</th>
									<th>计划数量</th>
									<th>计划物流</th>
									<th>计划调出日</th>
									<th>计划调入日</th>
									<th>RMS标</th>
									<th>需大货资料</th>
									<th>需采购</th>
									<th>需换标</th>
									<th>任务号</th>
									<th>任务状态</th>
									<th>实际物流</th>
									<th>实际调出日</th>
									<th>实际调入日</th>                              
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
            grid.setAjaxParam("out_factory", $("input[name='out_factory']").val());
			grid.setAjaxParam("in_factory", $("input[name='in_factory']").val());
			grid.setAjaxParam("asin", $("input[name='asin']").val());
			grid.setAjaxParam("sku", $("input[name='sku']").val());
            grid.setAjaxParam("marketplace_id", $("select[name='marketplace_id[]']").val());
			grid.setAjaxParam("bg", $("select[name='bg[]']").val());
            grid.setAjaxParam("bu", $("select[name='bu[]']").val());
            grid.setAjaxParam("status", $("select[name='status[]']").val());
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
                        "url": "{{ url('transferPlan/get')}}",
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
		var dttable = $('#datatable_ajax').dataTable();
		dttable.fnClearTable(false); //清空一下table
	    dttable.fnDestroy(); //还原初始化了的datatable
		TableDatatablesAjax.init();
	});
});


</script>


@endsection

