@extends('layouts.layout')
@section('label', 'Fba Inventory Adjustments Report')
@section('content')
<style type="text/css">
th,td,td>span {
    font-size:12px !important;
    text-align:center;
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
                    <form role="form" action="{{url('reports')}}" method="POST">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-2">
                                <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" readonly name="adjusted_date_from" placeholder="DateFrom" value="">
                                    <span class="input-group-btn">
                                        <button class="btn  default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" readonly name="adjusted_date_to" placeholder="DateTo" value="">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-2">
                            <select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Accounts" name="seller_account_id[]" id="seller_account_id[]">
                                @foreach ($accounts_data as $id=>$name)
                                    <option value="{{$id}}">{{$name}}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="transaction_item_id" placeholder="Transaction Item Id">  
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="seller_sku" placeholder="Seller skus">  
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="fnsku" placeholder="Fnskus">  
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="fulfillment_center_id" placeholder="Fulfillment Center Id">  
                            </div>
                            <div class="col-md-2">
                                <select class="form-control " name="fba_inventory_adjustments_report_state" id="fba_inventory_adjustments_report_state">
                                    <option value="">Select State</option>
                                    @foreach (App\Models\FbaInventoryAdjustmentsReport::STATE as $k=>$v)
                                        <option value="{{$k}}">{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                        
                            <div class="col-md-2">
                                <select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Reason" name="reason[]" id="reason[]">
                                    @foreach (App\Models\FbaInventoryAdjustmentsReport::REASONSEARCH as $k=>$v)
                                        <option value="{{$k}}">{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Disposition" name="disposition[]" id="disposition[]">
                                    @foreach (App\Models\FbaInventoryAdjustmentsReport::DISPOSITION as $k=>$v)
                                        <option value="{{$k}}">{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                            <button type="button" class="btn blue" id="data_search">Search</button>
                            </div>
						</div>	
                    </form>
                </div>
				
                <div class="portlet-title">
					<div class="btn-group " style="float:right;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
					
                            <button type="button" class="btn  green-meadow" id = "select_export">Export Selected</button>        
                            <button type="button" class="btn  green-meadow" id = "current_export">Export Current</button>
                            <button type="button" class="btn  green-meadow" id = "all_export">Export All</button>

                        </div>
                    </div>
                </div>
				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-checkable table-striped table-bordered table-hover" id="datatable_ajax_report">
                            <thead>
                                <tr role="row" class="heading">
                                    <th>
                                        <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                            <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_report .checkboxes" />
                                            <span></span>
                                        </label>
                                    </th>
                                    <th>Account</th>
                                    <th>Adjusted Date</th>
                                    <th>Transaction Item Id</th>
                                    <th>SellerSku</th>
                                    <th>Fnsku</th>
                                    <th>Fulfillment Center Id</th>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>State</th>
                                    <th>Disposition</th>
                                    <th>Reconciled</th>
                                    <th>Unreconciled</th>
                                    <th>Updated At</th>
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
        grid.setAjaxParam("type", 'fba_inventory_adjustments_report');
        grid.init({
            src: $("#datatable_ajax_report"),
            onSuccess: function (grid, response) {
            },
            onError: function (grid) {
            },
            onDataLoad: function(grid) {
            },
            loadingMessage: 'Loading...',
            dataTable: {
                "autoWidth":false,
                "ordering": false,
                "lengthMenu": [
                    [10, 20, 50, -1],
                    [10, 20, 50, 'All'] 
                ],
                "pageLength": 10,
                "ajax": {
                    "url": "{{ url('reports/get')}}",
                },
                }
        });


        $('#data_search').on('click',function(e){
            e.preventDefault();
            grid.setAjaxParam("type", 'fba_inventory_adjustments_report');
            grid.setAjaxParam("adjusted_date_from", $("input[name='adjusted_date_from']").val());
            grid.setAjaxParam("adjusted_date_to", $("input[name='adjusted_date_to']").val());
            grid.setAjaxParam("fulfillment_center_id", $("input[name='fulfillment_center_id']").val());
            grid.setAjaxParam("seller_sku", $("input[name='seller_sku']").val());
            grid.setAjaxParam("seller_account_id", $("select[name='seller_account_id[]']").val());
            grid.setAjaxParam("fnsku", $("input[name='fnsku']").val());
            grid.setAjaxParam("transaction_item_id", $("input[name='transaction_item_id']").val());
            grid.setAjaxParam("disposition", $("select[name='disposition[]']").val());
            grid.setAjaxParam("reason", $("select[name='reason[]']").val());
            grid.setAjaxParam("fba_inventory_adjustments_report_state", $("select[name='fba_inventory_adjustments_report_state']").val());
            grid.getDataTable().draw(false);
        });

        $("#select_export").click(function(){
            if (grid.getSelectedRowsCount() <= 0) return false;
            var baseUrl ='/reports/get?type=fba_inventory_adjustments_report&action=export&id='+grid.getSelectedRows();
            location.href =  baseUrl;
        });

        $("#current_export").click(function(){
            var baseUrl ='/reports/get?type=fba_inventory_adjustments_report&action=export&'+ $('#update_form').serialize();
            var dttable = $('#datatable_ajax_report').dataTable();
            var oSettings = dttable.fnSettings();
            baseUrl = baseUrl+'&offset='+oSettings._iDisplayStart;
            baseUrl = baseUrl+'&limit='+oSettings._iDisplayLength;
            location.href =  baseUrl;
        });

        $("#all_export").click(function(){
            var baseUrl ='/reports/get?type=fba_inventory_adjustments_report&action=export&'+ $('#update_form').serialize();
            location.href =  baseUrl;
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
});


</script>


@endsection

