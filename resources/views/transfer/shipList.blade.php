@extends('layouts.layout')
@section('label', 'Logistics Transfer List')
@section('content')
<style type="text/css">
    .DTFC_Cloned{
        margin-top:1px !important;
    }
    .DTFC_Cloned td{
        vertical-align: middle !important;
    }
    
    .DTFC_LeftBodyLiner{overflow: hidden !important;}
    .portlet.light .dataTables_wrapper .dt-buttons {
        margin-top: 0px !important;
    }
    table.dataTable thead th, table.dataTable thead td, .table td, .table th {padding:8px; white-space: nowrap;word-break:break-all; font-size:12px !important;}
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('daPlan')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						<div class="col-md-2">
						<input type="text" class="form-control" name="shipment_id" placeholder="Shipment ID">
						</div>
                        
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择调拨状态" name="tstatus[]" id="tstatus[]">
                            @foreach (\App\Models\TransferPlan::SHIPSHIPMENTSTATUS as $k=>$v)
                            <option value="{{$k}}">{{$v}}</option>
                            @endforeach 
                        </select>
						</div>

                        <div class="col-md-3">
							<div class="col-md-6">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="ship_start" placeholder="创建日期起" >
                                <span class="input-group-btn">
                                    <button class="btn  default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="ship_end" placeholder="创建日期止">
                                <span class="input-group-btn">
                                    <button class="btn  default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
						</div>

						
						<div class="col-md-2">
						<input type="text" class="form-control" name="keyword" placeholder="keyword">
						</div>
						
						
						
                        <div class="col-md-2">
                            <button type="button" class="btn blue" id="data_search">搜索</button> 
                        </div>
					    </div>

                    </form>
					
					
					
					
					
					
                </div>
				
                <div class="portlet-title">
					<div class="col-md-4 batchU" style="float:left;padding:0px;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                <option value="">选择调拨状态</option>
                                <?php
                                foreach(\App\Models\TransferPlan::SHIPSHIPMENTSTATUS as $k=>$v){
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> 批量更新状态
                            </button>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <?php 
                        $color_arr=['0'=>'red-sunglo','1'=>'yellow-crusta','2'=>'purple-plum','3'=>'blue-hoki','4'=>'blue-madison','5'=>'green-meadow'];
                        $i=0;
                        foreach (\App\Models\TransferPlan::SHIPSHIPMENTSTATUS as $k=>$v){
                        ?>
                        <button type="button" class="btn {{array_get($color_arr,(($i>=7)?$i-7:$i))}}">{{$v}} : {{array_get($statusList,$k,0)}}</button>
                        <?php 
                        $i++;
                        } ?>
                    </div>
					
                </div>
				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th width=3%>
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                    </th>
                                    <th >需求日期</th>
                                    <th >Shipment ID</th>
                                    <th >调拨状态</th>
                                    <th >仓库代码</th>
                                    <th >运输方式</th>
                                    <th >实际发货时间</th>
                                    <th style="width:400px;"> 调拨详情</th>
                                    <th style="width:400px;">实际发货详情</th>
                                    <th >预计总卡板数</th>
                                    <th >预计总箱数</th>
                                    <th >实际总卡板数</th>
                                    <th >实际总箱数</th>
                                    <th >运费</th>
                                    <th >TM</th>
                                    <th >DN</th>
                                    <th >ST0</th>
                                    <th >DA出库单号</th>
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
            grid.setAjaxParam("created_start", $("input[name='created_start']").val());
			grid.setAjaxParam("created_end", $("input[name='created_end']").val());
			grid.setAjaxParam("keyword", $("input[name='keyword']").val());
            grid.setAjaxParam("ship_order_id", $("input[name='shipment_id']").val());
            grid.setAjaxParam("tstatus", $("select[name='tstatus[]']").val());

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
                   "autoWidth":true,
                   "ordering": false,
                    "lengthMenu": [
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] 
                    ],
                    "bFilter":false,
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ url('shipPlan/get')}}",
                    },
                    "scrollX": true,
                    dom: 'Blrtip',
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: '导出当前页',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 1,2,3,4,5,6,7,8,9,10,11,12,13,14 ]
                            }
                        },
                    ],
                    fixedColumns:   {
                        leftColumns:3,
                        "drawCallback": function(){
                            $(".DTFC_Cloned input[class='group-checkable']").on('change',function(e) {
                                $(".DTFC_Cloned input[class='checkboxes']").prop("checked", this.checked);
                            });	

                            $(".DTFC_Cloned input[class='checkboxes']").on('change',function(e) {
                                $(".DTFC_Cloned input[class='group-checkable']").prop("checked", false);
                            });	
                        }

                    },
                     
                 },
                 
            });

            //批量更改状态操作

            
            $(".batchU").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();
                var confirmStatus = $("#confirmStatus", $("#table-actions-wrapper"));
                var count = $(".DTFC_Cloned input[class='checkboxes']:checked").size();
                var rows = [];
                $(".DTFC_Cloned input[class='checkboxes']:checked").each(function (index,value) {
                    rows.push($(this).val());
                });

                if (confirmStatus.val() != "" && count > 0) {
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                    });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ url('shipPlan/batchUpdate') }}",
                        data: {confirmStatus:confirmStatus.val(),id:rows},
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
                } else if (count === 0) {
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
	$('#datatable_ajax').on('click', 'td:not(:has(input),:has(button))', function (e) {
        e.preventDefault();
        var planId = $(this).closest('tr').find('.checkboxes').prop('value');
        $('#ajax').modal({
            remote: '/shipPlan/'+planId+'/edit'
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

