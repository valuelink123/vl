@extends('layouts.layout')
@section('label', 'Campaigns')
@section('content')
<style type="text/css">
	th, td { white-space: nowrap;word-break:break-all; }
    .unavailable{
        background-color: transparent;
        float: none;
    }
    .available{
        background-color: transparent;
        float: none;
    }
    .row {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .portlet.light   .portlet-title   .caption {
        color: #666;
        padding: 10px 0;
    }
</style>
<h1 class="page-title font-red-intense"> Campaign - {{array_get($campaign,'name')}}
</h1>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            
            <div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li >
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting"> Setting</a>
                </li>
                <li >
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/adgroup" >Ad Groups</a>
                </li>
                <li >
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/negkeyword" >Negative keywords</a>
                </li>
                <li class="active">
                    <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/negproduct" >Negative products</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="table-toolbar">
                    <form role="form" action="{{url('adv')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                        <input type="hidden" name="profile_id" value="{{$profile_id}}">
                        <input type="hidden" name="ad_type" value="{{$ad_type}}">
                        <input type="hidden" name="campaign_id" value="{{array_get($campaign,'campaignId')}}">
                        <div class="col-md-2">
                        <input type="text" class="form-control" name="name" placeholder="keyword">
                        </div>
                            <div class="col-md-2">
                            <button type="button" class="btn blue" id="data_search">Search</button>		
                            </div>
                        </div>

                    </form>	
                </div>

                <div class="portlet-title">
                    <div class="caption font-dark col-md-12">
                        <div class="btn-group" style="float:right;">
                            <button class="btn green dropdown-toggle" type="button" data-toggle="modal" href="#negkeywordform"> Create Negative products
                            </button>
                            
                        </div>


                        <div class="btn-group batch-update">
                            <div class="table-actions-wrapper" id="table-actions-wrapper">
                                <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                    <option value="">Select Status</option>
                                    <option value="archived" >archived</option>
                                </select>
                                <button class="btn  green table-status-action-submit">
                                    <i class="fa fa-check"></i> Batch Update
                                </button>
                                    
                            </div>
                        </div>
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
									<th>Asins</th>
                                    <th>Match Type</th>            
                                </tr>
                            </thead>
                            <tbody>	
                            </tbody>
                        </table>
					</div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<form id="update_form"  name="update_form" >
{{ csrf_field() }}
<div class="modal fade" id="negkeywordform" tabindex="-1" role="negkeywordform" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Negative products</h4>
            </div>
            
            <div class="modal-body"> 
                <div class="form-group col-md-12">
                    <label>Asins *</label>
                    <textarea class="form-control" rows="10" name="asins" id="asins"
                    placeholder="Enter your list and separate each item whith a new line."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                <button type="submit" class="btn green">Save changes</button>
                <input type="hidden" name="profile_id" value="{{$profile_id}}">
                <input type="hidden" name="ad_type" value="{{$ad_type}}">
                <input type="hidden" name="campaignId" value="{{array_get($campaign,'campaignId')}}">
                <input type="hidden" name="action" value="product_targeting">
                <input type="hidden" name="method" value="createCampaignNegativeTargets">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
</form>
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
            grid.setAjaxParam("profile_id", $("input[name='profile_id']").val());
            grid.setAjaxParam("ad_type", $("input[name='ad_type']").val());
            grid.setAjaxParam("campaign_id", $("input[name='campaign_id']").val());
            grid.setAjaxParam("name", $("input[name='name']").val());
            grid.setAjaxParam("action", "product_targeting");
            grid.setAjaxParam("method", "listCampaignNegativeTargets");
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
                        [50, 100, 300, -1],
                        [50, 100, 300, 'All'] 
                    ],
                    "pageLength": 300,
                    "ajax": {
                        "url": "{{ url('adv/listNegproducts')}}",
                    },
                    scrollY:        380,
                    scrollX:        true,
					
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
                     ],
                     */
                    
                 }
            });


            //批量更改状态操作
            $(".batch-update").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();
                var confirmStatus = $("#confirmStatus", $("#table-actions-wrapper"));
                var profile_id = $("input[name='profile_id']").val();
                var ad_type = $("input[name='ad_type']").val();
                var id_type = 'targetId';
                var action = 'product_targeting';
                var method = 'updateCampaignNegativeTargets';
                if (confirmStatus.val() != "" && grid.getSelectedRowsCount() > 0) {
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                    });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ url('adv/batchUpdate') }}",
                        data: {confirmStatus:confirmStatus.val(),id:grid.getSelectedRows(),profile_id:profile_id,ad_type:ad_type,id_type:id_type,action:action,method:method},
                        success: function (data) {
                            if(data.customActionStatus=='OK'){
                                toastr.success(data.customActionMessage);
                                grid.getDataTable().draw(false);
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

        $('#update_form').submit(function() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ url('adv/storeNegproducts') }}",
                data: $('#update_form').serialize(),
                success: function (data) {
                    if(data.customActionStatus=='OK'){
                        $('#negkeywordform').modal('hide');
                        $('.modal-backdrop').remove();
                        toastr.success(data.customActionMessage);
                        var dttable = $('#datatable_ajax').dataTable();
                        dttable.api().ajax.reload(null, false);
                    }else{
                        toastr.error(data.customActionMessage);
                    }
                },
                error: function(data) {
                    toastr.error(data.responseText);
                }
            });
            return false;
        });
    });


</script>
@endsection

