@extends('layouts.layout')
@section('label', '调拨计划列表')
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
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择审核状态" name="status[]" id="status[]">
                            @foreach (\App\Models\TransferPlan::STATUS as $k=>$v)
                            <option value="{{$k}}">{{$v}}</option>
                            @endforeach 
                        </select>
						</div>
                        </div>
                        <div class="row" style="margin-top:20px;">
                        <div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="选择调拨状态" name="tstatus[]" id="tstatus[]">
                            @foreach (\App\Models\TransferPlan::SHIPMENTSTATUS as $k=>$v)
                            <option value="{{$k}}">{{$v}}</option>
                            @endforeach 
                        </select>
						</div>

                        <div class="col-md-3">
							<div class="col-md-6">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="created_start" placeholder="创建日期起" >
                                <span class="input-group-btn">
                                    <button class="btn  default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="created_end" placeholder="创建日期止">
                                <span class="input-group-btn">
                                    <button class="btn  default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
						</div>


                        <div class="col-md-3">
							<div class="col-md-6">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" readonly name="received_start" placeholder="到货日期起" >
                                <span class="input-group-btn">
                                    <button class="btn default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="received_end" placeholder="到货日期止">
                                <span class="input-group-btn">
                                    <button class="btn default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control" name="keyword" value="{{\Request::get('keyword')}}" placeholder="keyword">
						</div>
						
						
						
                        <div class="col-md-2">
                        
                            <button type="button" class="btn blue" id="data_search">搜索</button>
                            @if(\Auth::user()->sap_seller_id)
                            <a data-target="#ajax" data-toggle="modal" href="/transferPlan/0/edit"> 					   
                            <button class="btn sbold red"> 新建调拨计划
                                <i class="fa fa-plus"></i>
                            </button>
                            </a> 
                            @endif
                        </div>
					    </div>

                    </form>
					
					
					
					
					
					
                </div>
				
                <div class="portlet-title">
                    @if(getTransferForRole())
					<div class="col-md-4 batchU" style="float:left;padding:0px;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
							
                            <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                <option value="">选择更新状态</option>
                                <?php
                                foreach(getTransferForRole() as $k=>$v){
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> 执行批量更新
                            </button>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-8">
                        <?php 
                        $color_arr=['0'=>'red-sunglo','1'=>'yellow-crusta','2'=>'purple-plum','3'=>'blue-hoki','4'=>'blue-madison','5'=>'green-meadow'];
                        $i=0;
                        foreach (\App\Models\TransferPlan::STATUS as $k=>$v){
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
                                    <th>
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                    </th>
                                    <th><div style="width:50px;">站点</div></th>
                                    <th><div style="width:150px;">销售员</div></th>
                                    <th >账号</th>
                                    <th>Shipment ID</th>
                                    <th >运输方式</th>
                                    <th >运费</th>
                                    <th><div style="width:500px;">调拨详情</div></th>
                                    <th>预计到货日期</th>
                                    <th>实际发货日期</th>
                                    <th>审核状态</th>
                                    <th>调拨状态</th>
                                    <th>调入工厂</th>
                                    <th>调出工厂</th>
                                    <th>大货资料附件</th>                        
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
			grid.setAjaxParam("received_start", $("input[name='received_start']").val());
			grid.setAjaxParam("received_end", $("input[name='received_end']").val());
			grid.setAjaxParam("keyword", $("input[name='keyword']").val());
            grid.setAjaxParam("marketplace_id", $("select[name='marketplace_id[]']").val());
			grid.setAjaxParam("bg", $("select[name='bg[]']").val());
            grid.setAjaxParam("bu", $("select[name='bu[]']").val());
            grid.setAjaxParam("status", $("select[name='status[]']").val());
            grid.setAjaxParam("tstatus", $("select[name='tstatus[]']").val());
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
                   "autoWidth":true,
                   "ordering": false,
                    "lengthMenu": [
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] 
                    ],
                    "bFilter":false,
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ url('transferPlan/get')}}",
                    },
                    "scrollX": true,
                    dom: 'Blrtip',
                    buttons: [ 
                        {
                            extend: 'excelHtml5',
                            text: '导出当前页',
                            title: 'Data export',
                            exportOptions: {
                                columns: [ 1,2,3,4,5,6,7,8,9,10,11,12,13 ]
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
                    "drawCallback": function( oSettings ) {
                        $(".uploadDataBtn").on("click",function(){
                            
                            var id = $(this).val();
                            $('.fileId').val(id);
                            $('.mask_upload_box').show();
                            $('.mask_upload_box #filesTable').html("");
                            $('.mask_upload_box .file_adress').html("Loading...");
                            $.ajax({
                                type:"post",
                                url:'/transferPlan/getUploadData',
                                data:{
                                    id: id,
                                },
                                error:function(err){
                                    console.log(err);
                                },
                                success:function(res){
                                    $('.mask_upload_box .file_adress').html("");
                                    let fileAddress1="" ,fileAddress2="";
                                    for(var i=0;i<res.length;i++){
                                        let reg = /\.(png|jpg|gif|jpeg|webp|pdf)$/;
                                        if(reg.test(res[i].url)){
                                            fileAddress1 += '<div><a class="titleHidden" href="' + res[i].url + '" target="_blank">' + res[i].title + '</a><a style="float:right" href="' + res[i].url + '" class="button" download="' + res[i].title + '">下载</a></div>';
                                        }else{
                                            fileAddress2 += '<div><span class="titleHidden">' + res[i].title + '</span><a style="float:right" href="' + res[i].url + '" download="' + res[i].title + '" class="button">下载</a></div>';
                                        }
                                    }
                                    $('.file_adress').append(fileAddress1 + fileAddress2 );
                                }
                            });
                        })
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
                        url: "{{ url('transferPlan/batchUpdate') }}",
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
	$('#datatable_ajax').on('dblclick', 'td:not(:has(input),:has(button))', function (e) {
        e.preventDefault();
        var planId = $(this).closest('tr').find('.checkboxes').prop('value');
        $('#ajax').modal({
            remote: '/transferPlan/'+planId+'/edit'
        });
    } );
	$('#ajax').on('hidden.bs.modal', function (e) {
        $('#ajax .modal-content').html('<div class="modal-body" >Loading...</div>');
    });

    //上传大货资料弹窗隐藏
    $('.cancelUpload').on('click',function(){
        $('.mask_upload_box').hide();
    })
    
    //上传大货资料
    $('#confirmUpload').on('click',function(){
        
        let fileList1 = '';
        let fileLists = " ";
        let str1 = $('.file_adress div').find('.button')
        for(var i=0;i<str1.length;i++){
            if(fileList1 != ''){
                fileList1 = fileList1 + ',' + str1[i].href;
            }else{
                fileList1 = fileList1 + str1[i].href;
            }
        }
        let fileList = '';
        let str = $('.table-striped tbody tr td').find('.filesUrl');
        for(var i=0;i<str.length;i++){
            if(fileList != ''){
                fileList = fileList + ',' + str[i].defaultValue;
            }else{
                fileList = fileList + str[i].defaultValue;
            }
        }
        if(fileList1 != "" && fileList != ""){
            fileLists = fileList1 + ',' + fileList
        }else if(fileList1 != "" && fileList == ""){
            fileLists = fileList1
        }else if(fileList1 == "" && fileList != ""){
            fileLists = fileList
        }
        $.ajax({
            type: "POST",
            url: "/transferPlan/updateFiles",
            data: {
                id: $('.fileId').val(),
                files: decodeURI(fileLists)
            },
            success: function (res) {
                if(res.status == 0){
                    toastr.error(res.msg);
                }else if(res.status == 1){
                    toastr.success(res.msg);
                    $('.mask_upload_box').hide();
                }
            },
            error: function(err) {
                console.log(err)
            }
        });
    })
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


<div class="mask_upload_box">
    <div class="mask_upload_dialog">
        <div style="overflow: auto; height: 395px;">
            <div class="file_adress"></div>
            <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
            <form id="fileupload" action="{{ url('send') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="warn" id="warn" value="0">
                <input type="hidden" name="inbox_id" id="inbox_id" value="0">
                <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
                                
                <div style="margin-top: 20px;">
                    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                    <div class="fileupload-buttonbar">
                        <div class="col-lg-12" style="text-align: center;">
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <span class="btn green fileinput-button">
                                <i class="fa fa-plus"></i>
                                <span>添加文件</span>
                                <input type="file" name="files[]" multiple=""> 
                            </span>
                            <button type="submit" class="btn blue start">
                                <i class="fa fa-upload"></i>
                                <span>开始上传</span>
                            </button>
                            <button type="reset" class="btn warning cancel">
                                <i class="fa fa-ban-circle"></i>
                                <span>取消上传 </span>
                            </button>
            
                            <button type="button" class="btn red delete">
                                <i class="fa fa-trash"></i>
                                <span>删除</span>
                            </button>
                            <!-- <input type="checkbox" class="toggle"> -->
                            <!-- The global file processing state -->
                            <span class="fileupload-process"> </span>
                        </div>
                        <!-- The global progress information -->
                        <div class="col-lg-12 fileupload-progress fade">
                            <!-- The global progress bar -->
                            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-success" style="width:0%;"> </div>
                            </div>
                            <!-- The extended global progress information -->
                            <div class="progress-extended"> &nbsp; </div>
                        </div>
                    </div>
                    <!-- The table listing the files available for upload/download -->
                    <table role="presentation" class="table table-striped clearfix" id="table-striped" style="margin-bottom: 0;">
                        <tbody class="files" id="filesTable"> </tbody>
                    </table>
                    <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
                        <div class="slides"> </div>
                        <h3 class="title"></h3>
                        <a class="prev"> ‹ </a>
                        <a class="next"> › </a>
                        <a class="close white"> </a>
                        <a class="play-pause"> </a>
                        <ol class="indicator"> </ol>
                    </div>
                    <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
                    <script id="template-upload" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
                    <tr class="template-upload fade">
                        <td>
                            <p class="name" style="margin: 0;">{%=file.name%}</p>
                            <strong class="error text-danger label label-danger" style="padding: 0 6px;"></strong>
                        </td>
                        <!-- <td>
                            <p class="size">Processing...</p>
                            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                            </div>
                        </td> -->
                        <td> {% if (!i && !o.options.autoUpload) { %}
                            <button class="btn blue start" disabled>
                                <i class="fa fa-upload"></i>
                                <span>开始</span>
                            </button> {% } %} {% if (!i) { %}
                            <button class="btn red cancel">
                                <i class="fa fa-ban"></i>
                                <span>取消</span>
                            </button> {% } %} </td>
                    </tr> {% } %} </script>
                    <!-- The template to display files available for download -->
                    <script id="template-download" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
                    <tr class="template-download fade">
                        <td>
                            <p class="name" style="margin: 0;"> {% if (file.url) { %}
                                <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl? 'data-gallery': ''%}>{%=file.name%}</a> {% } else { %}
                                <span>{%=file.name%}</span> {% } %}
                                {% if (file.name) { %}
                                    <input type="hidden" name="fileid[]" class="filesUrl" value="{%=file.url%}">
                                {% } %}
            
                                </p> {% if (file.error) { %}
                            <div>
                                <span class="label label-danger">Error</span> {%=file.error%}</div> {% } %} </td>
                        <!-- <td>
                            <span class="size">{%=o.formatFileSize(file.size)%}</span>
                        </td> -->
                        <td> {% if (file.deleteUrl) { %}
                            <button class="btn red delete btn-sm" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}" {% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}' {% } %}>
                                <i class="fa fa-trash-o"></i>
                                <span>删除</span>
                            </button>
                            <!-- <input type="checkbox" name="delete" value="1" class="toggle"> --> {% } else { %}
                            <button class="btn yellow cancel btn-sm">
                                <i class="fa fa-ban"></i>
                                <span>取消</span>
                            </button> {% } %} </td>
                    </tr> {% } %} </script>
                    <div style="clear:both;"></div>
                </div>
            </form>	
            
        </div>
        <div style="text-align: center;">
            <input type="hidden" class="fileId">
            <button class="btn warning cancel cancelUpload" style="width: 80px;border: 1px solid #ccc;">取消</button>
            <button class="btn blue start" id="confirmUpload">确认上传</button>
        </div>
    </div>
</div>
@endsection

