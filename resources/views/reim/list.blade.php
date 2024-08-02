@extends('layouts.layout')
@section('label', '索赔')
@section('content')
<style type="text/css">
	th, td { white-space: nowrap;word-break:break-all; }
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('reims')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						<div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" readonly name="date_from" placeholder="Date From" value="{{date('Y-m-d',strtotime('-180 days'))}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " name="date_to" placeholder="Date To" value="{{date('Y-m-d')}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
						
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择账号" name="seller_account_id[]" id="seller_account_id[]">
                            @foreach ($accounts as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择销售" name="sap_seller_id[]" id="sap_seller_id[]">
                            @foreach ($users as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
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
						<input type="text" class="form-control " name="shipment_id" placeholder="Shipmend ID">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control " name="case_id" placeholder="CASE ID">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control" name="sku" placeholder="Sku">
						</div>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择进度" name="step[]" id="step[]">
                            @foreach (\App\Models\AmazonShipmentItem::STATUS as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>
						<div class="col-md-2">
						<select class="form-control " name="status" id="status" >
                            <option value="" >All</option>
							<option value="Pending" >Pending</option>
							<option value="Success" >Success</option>
                        </select>
						</div>
						
						<div class="col-md-2">
							
							<button type="button" class="btn blue" id="data_search">搜索</button>
									
                            </div>

						</div>	
						

                    </form>
					
					
					
					
					
					
                </div>
				
                <div class="portlet-title">
                    
					<div class="btn-group " style="float:left;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
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
     					<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                        <span></span>
                                    </label>                                  
                                    </th>
                                    <th>更新日期</th>
									<th>Bg - Bu</th>
									<th>销售员</th>
									<th>账号</th>
									<th>ShipmentID</th>
									<th>SellerSku</th>
									<th>Sku</th>
									<th>发货数量</th>
									<th>收货数量</th>
									<th>差异数量</th>
									<th>货件状态</th>
                                    <th>POD/ISA</th>
									<th>CaseID</th>
									<th>赔偿金额</th>
									<th>货币</th>
                                    <th>赔偿数量</th>
                                    <th>索赔状态</th>
									<th>进度</th>
                                    <th>备注</th>                      
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
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
			grid.setAjaxParam("date_to", $("input[name='date_to']").val());
			grid.setAjaxParam("shipment_id", $("input[name='shipment_id']").val());
			grid.setAjaxParam("case_id", $("input[name='case_id']").val());
			grid.setAjaxParam("sku", $("input[name='sku']").val());
			grid.setAjaxParam("sap_seller_id", $("select[name='sap_seller_id[]']").val());
            grid.setAjaxParam("seller_account_id", $("select[name='seller_account_id[]']").val());
			grid.setAjaxParam("bg", $("select[name='bg[]']").val());
            grid.setAjaxParam("bu", $("select[name='bu[]']").val());
			grid.setAjaxParam("step", $("select[name='step[]']").val());
            grid.setAjaxParam("status", $("select[name='status']").val());
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
                    "pageLength": -1,
                    "ajax": {
                        "url": "{{ url('reims/get')}}",
                    },
                    scrollY:500,
                    scrollX:true,
                    
                    dom: 'Bfrtip',
					"bFilter": false,
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: 'Export',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19 ]
                            },
                        },
                     ],
		     
                        "drawCallback": function(){
                            $(".dataTables_scroll input[class='group-checkable']").on('change',function(e) {
                                $(".dataTables_scroll input[class='checkboxes']").prop("checked", this.checked);
                            });	

                            $(".dataTables_scroll input[class='checkboxes']").on('change',function(e) {
                                $(".dataTables_scroll input[class='group-checkable']").prop("checked", false);
                            });	
                        }

                    
                     
                 }
            });

            //批量更改状态操作
            $(".btn-group").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();

                if (grid.getSelectedRowsCount() > 0) {
					$('#ajax').modal({
						remote: '/reims/'+grid.getSelectedRows()+'/edit',
					});
                }else if (grid.getSelectedRowsCount() === 0) {
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

