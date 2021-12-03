@extends('layouts.layout')
@section('label', 'User Accounts Manage')
@section('content')
<style type="text/css">
	th, td { white-space: nowrap;word-break:break-all; }
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						<div class="col-md-2">
						<input type="text" class="form-control" name="keyword" placeholder="Keyword">
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
                        <span class="caption-subject bold uppercase">Create</span>
                        <a data-target="#ajax" data-toggle="modal" href="/budgetSku/create"> 
                                   
                        <button class="btn  green ">
                                <i class="fa fa-plus"></i> 添加
                        </button>
                        </a>
                    </div>
                    
                </div>
				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th>物料号</th>
                                    <th>站点</th>
									<th>产品名称</th>
									<th>状态</th>
									<th>等级</th>
									<th>期初库存</th> 
                                    <th>体积</th>
                                    <th>体积标准</th> 
                                    <th>成本</th>
                                    <th>佣金比率</th>
                                    <th>拣配费金额（外币）</th>
                                    <th>退货率</th>
                                    <th>销售员</th>
                                    <th>计划员</th>
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
                   "autoWidth":false,
                   "ordering": false,
                    "lengthMenu": [
                        [10, 20, 50, -1],
                        [10, 20, 50, 'All'] 
                    ],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ url('budgetSku/get')}}",
                    },
                    
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
            remote: '/budgetSku/'+recordId+'/edit'
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
