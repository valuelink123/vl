@extends('layouts.layout')
@section('label', '财务发货事件列表')
@section('content')
<style type="text/css">
.portlet.light .dataTables_wrapper .dt-buttons {
	margin-top: 0px !important;
}
.table thead tr th,.table thead tr td,.table td, .table th{
	font-size:11px;
	white-space: nowrap;
	text-align:left;
}
table.dataTable thead th, table.dataTable thead td {
    padding: 8px 10px;
}
table.dataTable.fixedHeader-floating {
    margin-top: 50px!important;
}
</style>

    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('financeshipment')}}" method="GET" id="search-form">
                        {{ csrf_field() }}
                        <div class="row">
							<div class="col-md-2">
								<div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
									<span class="input-group-addon">Date From</span>
									<input  class="form-control" value="{{date('Y-m').'-01'}}" data-options="format:'yyyy-mm-dd'" id="date_from" name="date_from" autocomplete="off"/>
								</div>
							</div>
							
							<div class="col-md-2">
								<div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
									<span class="input-group-addon">Date To</span>
									<input  class="form-control" value="{{date('Y-m-d')}}" data-options="format:'yyyy-mm-dd'" id="date_to" name="date_to" autocomplete="off"/>
								</div>
							</div>
					
							<div class="col-md-2">
								<div class="input-group">
									<span class="input-group-addon">Accounts</span>
									<select  class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true"  id="seller_account_id" name="seller_account_id[]">
										@foreach($accounts as $key=>$val)
											<option value="{!! $key !!}">{!! $val !!}</option>
										@endforeach
									</select>	
								</div>
							</div>
                            <div class="col-md-2">
							<div class="input-group">
							<span class="input-group-addon">Keyword</span>
                            <input type="text" class="form-control" name="keyword" placeholder="keyword">
							</div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn blue" id="data_search">Search</button> 
								<button id="export" class="btn sbold blue"> 导出
										<i class="fa fa-download"></i>
									</button>
                            </div>
                            
                       

					    </div>

                    </form>
					
					
					
					
					
					
                </div>

				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover " id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th>Account</th>
									<th>AmazonOrderID</th>
									<th>SellerOrderID</th>
                                    <th>MarketPlace</th>
									<th>Date</th>
									<th>OrderItemID</th>
									<th>SellerSku</th>
									<th>Quantity</th>
                                    <th>Type</th>
									<th>Amount</th>
									<th>Currency</th>
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
        var initTable = function () {
			
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            var grid = new Datatable();
			grid.setAjaxParam("date_from", $("input[name='date_from']").val());
			grid.setAjaxParam("date_to", $("input[name='date_to']").val());
			grid.setAjaxParam("seller_account_id", $("select[name='seller_account_id[]']").val());
			grid.setAjaxParam("keyword", $("input[name='keyword']").val());
            grid.init({
                src: $("#datatable_ajax"),
                loadingMessage: 'Loading...',
                dataTable: {
                   //"serverSide":false,
                   "autoWidth":false,
				   "fixedHeader": true,
                   "ordering": false,
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] 
                    ],
                    "bFilter":false,
                    "pageLength": 20,
                    "ajax": {
                        "url": "{{ url('financeshipment/get')}}",
                    },
                 },
                 
            });

        }


        return {
            init: function () {
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
	
	$("#export").click(function(){
		location.href='/financeshipmentExport?'+$("#search-form").serialize();
		return false;
	});
	
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
});


</script>



@endsection

