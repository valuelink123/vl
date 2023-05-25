@extends('layouts.layout')
@section('label', 'Da Transfer List')
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
    table.dataTable thead th, table.dataTable thead td, .table td, .table th {padding:8px; white-space: nowrap;word-break:break-all;}
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
						<input type="text" class="form-control" name="da_order_id" placeholder="Da Order ID">
						</div>
                        
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Ship Status" name="tstatus[]" id="tstatus[]">
                            @foreach (\App\Models\TransferPlan::SHIPMENTSTATUS as $k=>$v)
                            <option value="{{$k}}">{{$v}}</option>
                            @endforeach 
                        </select>
						</div>

                        <div class="col-md-3">
							<div class="col-md-6">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="ship_start" placeholder="Ship Date Start" >
                                <span class="input-group-btn">
                                    <button class="btn  default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="ship_end" placeholder="Ship Date End">
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
                            <button type="button" class="btn blue" id="data_search">Search</button> 
                        </div>
					    </div>

                    </form>
					
					
					
					
					
					
                </div>
				
                <div class="portlet-title" style="padding-bottom:10px;">
                    <div class="col-md-4">
                    </div>

                    <div class="col-md-8" style="text-align:right;">
                        <?php 
                        $color_arr=['0'=>'red-sunglo','1'=>'yellow-crusta','2'=>'purple-plum','3'=>'blue-hoki','4'=>'blue-madison','5'=>'green-meadow'];
                        $i=0;
                        foreach (\App\Models\TransferPlan::DASHIPMENTSTATUS as $k=>$v){
                        ?>
                        <button type="button" class="btn {{array_get($color_arr,(($i>=7)?$i-7:$i))}}">{{$v}} : {{array_get($statusList,$k,0)}}</button>
                        <?php 
                        $i++;
                        } ?>
                    </div>

                    <div class="col-md-4 batchU" style="float:left;padding:0px;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                <option value="">Select Ship Status</option>
                                <?php
                                foreach(\App\Models\TransferPlan::DASHIPMENTSTATUS as $k=>$v){
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> Batch Update
                            </button>
                        </div>
                    </div>

                    <div class="col-md-8" style="text-align:right;">
                        <input id="importFile" name="importFile" type="file" style="display:none">
                        {{ csrf_field() }}
                        <input id="importFileTxt" name="importFileTxt" type="text" class="form-control input-inline">
                        <a id="importButton" class="btn red input-inline" >Browse</a>

                        <button id="importSubmit" class="btn blue input-inline">Upload</button>
    
                        <a href="{{ url('/uploads/da/da.xls')}}" class="help-inline" style="margin-top:8px;margin-left:10px;">Template </a>
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
                                    <th ><div style="width:150px;">Submit Date</div></th>
                                    <th><div style="width:150px;">DA OrderID</div></th>
			    	    <th>Account</th>
					<th >Ship Status</th>
                                    <th >Actual Ship Date</th>
                                    <th >Reservation Date</th>
                                    <th > <div style="width:500px;">Request Details</div></th>
                                    <th><div style="width:500px;">Actual Ship Details</div></th>
                                    <th >Shipment ID</th>
                                    <th >Reservation ID</th>  
                                    <th >ShipMethod</th>
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
            grid.setAjaxParam("ship_start", $("input[name='ship_start']").val());
			grid.setAjaxParam("ship_end", $("input[name='ship_end']").val());
			grid.setAjaxParam("keyword", $("input[name='keyword']").val());
            grid.setAjaxParam("da_order_id", $("input[name='da_order_id']").val());
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
                        "url": "{{ url('daPlan/get')}}",
                    },
                    "scrollX": true,
                    dom: 'Blrtip',
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: 'Export Current Page',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 1,2,3,4,5,6,7,8,9,10,11 ]
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
                        url: "{{ url('daPlan/batchUpdate') }}",
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
			url: "/daPlan/upload",
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

	$('#datatable_ajax').on('dblclick', 'td:not(:has(input),:has(button))', function (e) {
        e.preventDefault();
        var planId = $(this).closest('tr').find('.checkboxes').prop('value');
        $('#ajax').modal({
            remote: '/daPlan/'+planId+'/edit'
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

