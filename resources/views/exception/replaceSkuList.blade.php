@extends('layouts.layout')
@section('label', '设置重发单替代SKU')
@section('content')
<style type="text/css">

    .portlet.light .dataTables_wrapper .dt-buttons {
        margin-top: 0px !important;
    }
</style>

    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('replaceSku')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						
                            <div class="col-md-2">
                            <input type="text" class="form-control" name="keyword" placeholder="keyword">
                            </div>
                            
                            
                            
                            <div class="col-md-2">
                                <button type="button" class="btn blue" id="data_search">Search</button> 
                                <a data-target="#ajax" data-toggle="modal" href="/replaceSku/0/edit"> 					   
                                <button class="btn sbold red"> 新建
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a> 
                            </div>



					    </div>

                    </form>
					
					
					
					
					
					
                </div>

				
                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover " id="datatable_ajax">
                            <thead>
                                <tr role="row" class="heading">
                                    <th>SKU</th>
                                    <th>Replace SKU</th>
									<th>Updated At</th>
									<th>User</th>
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
			grid.setAjaxParam("keyword", $("input[name='keyword']").val());
            grid.init({
                src: $("#datatable_ajax"),
                loadingMessage: 'Loading...',
                dataTable: {
                   //"serverSide":false,
                   "autoWidth":false,
                   "ordering": false,
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] 
                    ],
                    "bFilter":false,
                    "pageLength": 20,
                    "ajax": {
                        "url": "{{ url('replaceSku/get')}}",
                    },
                    dom: 'Blrtip',
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: '导出当前页',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 0,1 ]
                            }
                        },
                    ],
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

	$('#datatable_ajax').on('dblclick', 'td:not(:has(input),:has(button))', function (e) {
        e.preventDefault();
        var planId = $(this).closest('tr').find('.checkboxes').prop('value');
        $('#ajax').modal({
            remote: '/replaceSku/'+planId+'/edit'
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

