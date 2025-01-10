@extends('layouts.layout')
@section('label', 'Listings')
@section('content')
<style type="text/css">
    table.dataTable thead th, table.dataTable thead td, .table td, .table th {padding:8px; white-space: nowrap;word-break:break-all;}
    .portlet.light .dataTables_wrapper .dt-buttons {
        margin-top: 
        0px !important;
    }
    .DTFC_Cloned{
        margin-top:1px !important;
    }
    .DTFC_Cloned td{
        vertical-align: middle !important;
    }
    .DTFC_LeftBodyLiner{overflow: hidden !important;}
	.mask_upload_box{
		display: none;
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background: rgb(0,0,0,.3);
		z-index: 999;
	}
    .mask_upload_dialog{
		width: 500px;
		height: 500px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 40px;
		margin-top: -250px;
		margin-left: -250px;
	}
	
	
	.form_btn{
		text-align: right;
		margin: 20px 0;
	}
	.form_btn button{
		width: 75px;
		height: 32px;
		outline: none;
		color: #fff;
		border-radius: 4px !important;
	}
	.form_btn button:first-child{
		background-color: #909399;
		border: 1px solid #909399;	
	}
	.form_btn button:last-child{
		margin-left: 10px;
		background-color: #3598dc;
		border: 1px solid #3598dc;
	}
	.cancel_upload_btn{
		position: absolute;
		top: 20px;
		right: 20px;
		cursor: pointer;
		width: 30px;
		padding: 8px;
		height: 30px;
		z-index: 999;
	}
	.cancel_upload_btn{
		top: 10px!important;
		right: 12px !important;
	}
	
	.nav_list{
		overflow: hidden;
		height: 45px;
		line-height: 45px;
		border-bottom: 2px solid #fff;
		padding: 0;
		margin: 0;
	}
	.nav_list li{
		float: left;
		line-height: 36px;
		padding: 5px 10px 0 10px;
		margin: 0 10px 0 0;
		list-style: none;
	}
	.nav_list li a{
		text-decoration: none;
		color: #666;
	}
	.nav_active{
		border-bottom: 2px solid #4B8DF8;
	}
	.nav_active a{
		color: #4B8DF8 !important;
	}
	
	.file_adress{
		margin: 10px;
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
                            @foreach (array_flip(getSiteCode()) as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-1">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择BG" name="bg[]" id="bg[]">
                            @foreach (getUsers('sap_bg') as $k=>$v)
                                <option value="{{$v->bg}}">{{$v->bg}}</option>
                            @endforeach
                        </select>
						</div>
                        <div class="col-md-1">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择BU" name="bu[]" id="bu[]">
                            @foreach (getUsers('sap_bu') as $k=>$v)
                                <option value="{{$v->bu}}">{{$v->bu}}</option>
                            @endforeach
                        </select>
						</div>

                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择销售员" name="sap_seller_id[]" id="sap_seller_id[]">
                            @foreach (getUsers('sap_seller') as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>

                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择账号" name="seller_id[]" id="seller_id[]">
                            @foreach (getSellerAccount() as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control" name="keyword" value="{{\Request::get('keyword')}}" placeholder="keyword">
						</div>
                        <div class="col-md-1">
                            <button type="button" class="btn blue" id="data_search">搜索</button>
                        </div>
					    </div>

                    </form>
					
					
					
					
					
					
                </div>
				
				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th>主图</th>
                                    <th width="50%">信息</th>
                                    <th >可售库存</th>
                                    <th >价格</th>
                                    <th >销售员</th>                   
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
            
			grid.setAjaxParam("keyword", $("input[name='keyword']").val());
            grid.setAjaxParam("marketplace_id", $("select[name='marketplace_id[]']").val());
			grid.setAjaxParam("bg", $("select[name='bg[]']").val());
            grid.setAjaxParam("bu", $("select[name='bu[]']").val());
            grid.setAjaxParam("seller_id", $("select[name='seller_id[]']").val());
            grid.setAjaxParam("sap_seller_id", $("select[name='sap_seller_id[]']").val());
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
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] 
                    ],
                    "bFilter":false,
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ url('listing/get')}}",
                    },
                    "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(1).attr('style', 'text-align: left;white-space: normal; ');
						$(row).children('td').eq(3).attr('style', 'text-align: left;').addClass('priceDiv');
                    },
                 },
                 
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

	$('#datatable_ajax').on('click', '.priceDiv', function (e) {
        e.preventDefault();
        var planId = $(this).closest('tr').find('img').attr('id');
        $('#ajax').modal({
            remote: '/listing/'+planId+'/edit'
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

