@extends('layouts.layout')
@section('label', 'Gift Cards')
@section('content')
<style type="text/css">
	th, td { white-space: nowrap;word-break:break-all; }
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('giftcard')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                        

						
                        <div class="col-md-2">
						<input type="text" class="form-control " name="bg" placeholder="BG">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control " name="bu" placeholder="BU">
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control" name="code" placeholder="卡号">
						</div>
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择状态" name="status[]" id="status[]" >
                            @foreach (\App\Models\GiftCard::STATUS as $k=>$v)
                                <option value="{{$k}}" >{{$v}}</option>
                            @endforeach
                        </select>
						</div>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择用户" name="user_id[]" id="user_id[]">
                            @foreach (getUsers() as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
						</div>
						
						
						    <div class="col-md-2">
							
							<button type="button" class="btn blue" id="data_search">搜索</button>
									
                            </div>
					    </div>

                    </form>

                    <div class="row">
                    <div class="col-md-12">
                        <input id="importFile" name="importFile" type="file" style="display:none">
                        {{ csrf_field() }}
                        <input id="importFileTxt" name="importFileTxt" type="text" class="form-control input-inline">
                        <a id="importButton" class="btn red input-inline" >Browse</a>

                        <button id="importSubmit" class="btn blue input-inline">Upload</button>
    
                        <a href="{{ url('/uploads/giftcard/giftcard.xls')}}" class="help-inline" style="margin-top:8px;margin-left:10px;">Template </a>

                       
                        <button id="vl_list_export" class="btn blue input-inline"> Export
                            <i class="fa fa-download"></i>
                        </button>
                        
                    </div>
                    </div>
					
					
					
					
					
					
                </div>
				
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Gift Cards</span>
                        <a data-target="#ajax" data-toggle="modal" href="/giftcard/create"> 
                                   
                        <button class="btn  green ">
                                <i class="fa fa-plus"></i> 添加记录
                        </button>
                        </a>
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
                                    <th>BG</th>
									<th>BU</th>
									<th>卡号</th>
									<th>金额</th>
									<th>货币</th>
                                    <th>人民币价格</th>
                                    <th>用户</th>
                                    <th>状态</th>
									<th>订单号</th>  
									<th>创建时间</th>
									<th>编辑时间</th>                              
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
            grid.setAjaxParam("bg", $("input[name='bg']").val());
			grid.setAjaxParam("bu", $("input[name='bu']").val());
			grid.setAjaxParam("code", $("input[name='code']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
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
                        "url": "{{ url('giftcard/get')}}",
                    },
                    //"scrollX": true,
                    //"autoWidth":true
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
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                    });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ url('platformsku/batchUpdate') }}",
                        data: {confirmStatus:confirmStatus.val(),id:grid.getSelectedRows()},
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
                } else if (grid.getSelectedRowsCount() === 0) {
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
	$('#datatable_ajax').on('click', 'td:not(:has(input))', function (e) {
        e.preventDefault();
        var recordId = $(this).closest('tr').find('.checkboxes').prop('value');
        $('#ajax').modal({
            remote: '/giftcard/'+recordId+'/edit'
        });
    } );
	$('#ajax').on('hidden.bs.modal', function (e) {
        $('#ajax .modal-content').html('<div class="modal-body" >Loading...</div>');
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
			url: "/giftcard/upload",
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


    $("#vl_list_export").click(function(){
		location.href='/giftcardexport?bg='+$("input[name='bg']").val()+'&bu='+$("input[name='bu']").val()+'&code='+$("input[name='code']").val()+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&status='+(($("select[name='status[]']").val())?$("select[name='status[]']").val():'');
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

