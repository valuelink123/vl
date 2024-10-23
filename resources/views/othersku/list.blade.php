@extends('layouts.layout')
@section('label', 'OTO Inventory')
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
                    <form role="form" action="{{url('otherSku')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						
                            <div class="col-md-2">
                            <input type="text" class="form-control" name="keyword" placeholder="keyword">
                            </div>
                            
                            
                            
                            <div class="col-md-2">
                                <button type="button" class="btn blue" id="data_search">Search</button> 
                                <a data-target="#ajax" data-toggle="modal" href="/otherSku/0/edit"> 					   
                                <button class="btn sbold red"> 新建
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a> 
                            </div>

                            <div class="col-md-8" style="text-align:right;">
                                <input id="importFile" name="importFile" type="file" style="display:none">
                                {{ csrf_field() }}
                                <input id="importFileTxt" name="importFileTxt" type="text" class="form-control input-inline">
                                <a id="importButton" class="btn red input-inline" >Browse</a>

                                <button id="importSubmit" class="btn blue input-inline">Upload</button>
            
                                <a href="{{ url('/uploads/othersku/otherSku.xls')}}" class="help-inline" style="margin-top:8px;margin-left:10px;">Template </a>
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
									<th>产品名称</th>
									<th>供应商未交</th>
									<th>预计交期</th>
                                    <th>货好未提</th>
									<th>东莞仓库存</th>
									<th>已申请调拨</th>
									<th>在途</th>
									<th>SBN库存</th>
                                    <th>HC仓库存</th>
									<th>DH仓库存</th>
									<th>TEMU仓库存</th>
									<th>富皇仓库存</th>
									<th>合计</th>
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
                   "ordering": true,
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] 
                    ],
		    fixedHeader: true,
                    "bFilter":false,
                    "pageLength": 20,
                    "ajax": {
                        "url": "{{ url('otherSku/get')}}",
                    },
					"aoColumnDefs": [ { "bSortable": false, "aTargets": [1,3 ] }],
					 "order": [
                        [13, "desc"]
                    ],
                    dom: 'Blrtip',
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: '导出当前页',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13 ]
                            }
                        },
                    ],
					"drawCallback":function( oSettings ) {
						
					}
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
	
    $("#importButton,#importFileTxt").click(function(){
		$("#importFile").trigger("click");
	});

	$('input[id=importFile]').change(function() {
		$('#importFileTxt').val($(this).val());
	});

	$("#importSubmit").click(function () {
		var fileObj = document.getElementById("importFile").files[0];
		if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
			alert("Please Select File!");
			return false;
		}
		var formFile = new FormData();
		formFile.append("file", fileObj);
		var data = formFile;
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			url: "/otherSku/upload",
			data: data,
			type: "Post",
			dataType: "json",
			cache: false,
			processData: false,
			contentType: false,
			success: function (result) {
				if(result.customActionStatus=='OK'){  
				toastr.success(result.customActionMessage);
                    var dttable = $('#datatable_ajax').dataTable();
					dttable.api().ajax.reload(null, false);	
				}else{
					toastr.error(result.customActionMessage);
				}
			},
			error: function(result) {
                toastr.error(result.responseText);
			}
		});
	});

	$('#datatable_ajax').on('click', '.editData', function (e) {
        e.preventDefault();
        var planId = $(this).closest('tr').find('.checkboxes').prop('value');
        $('#ajax').modal({
            remote: '/otherSku/'+planId+'/edit'
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

