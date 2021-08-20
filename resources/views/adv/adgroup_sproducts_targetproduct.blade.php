@extends('layouts.layout')
@section('label')
<a href="/adv">Advertising</a>  - Campaigns <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'campaignId')}}/setting">{{array_get($adgroup,'campaignName')}}</a>
 - AdGroup <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/setting">{{array_get($adgroup,'name')}}</a>
@endsection
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
    .modal-body{
        padding:0px;
    }
    .DTFC_LeftBodyLiner{
        overflow-x: hidden;
    }
    table.dataTable tbody tr {
        height: 60px !important;
    }
    .editable-input .input-medium {
        width: 100% !important;
        PADDING: 5PX !important;
    }
    .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th{
        vertical-align: middle !important;
    }
</style>
<h1 class="page-title font-red-intense"> Ad Group - {{array_get($adgroup,'name')}}
</h1>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            
            <div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li >
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/setting"> Setting</a>
                </li>
                <li>
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/ad" >Ads</a>
                </li>
                <li >
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/targetkeyword" >Targeting keywords</a>
                </li>
                <li>
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/negkeyword" >Negative keywords</a>
                </li>

                <li class="active">
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/targetproduct" >Targeting products</a>
                </li>

                <li >
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/negproduct" >Negative products</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="table-toolbar">
                    <form role="form" action="{{url('adv')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                        <input type="hidden" name="profile_id" value="{{$profile_id}}">
                        <input type="hidden" name="ad_type" value="{{$ad_type}}">
                        <input type="hidden" name="campaign_id" value="{{array_get($adgroup,'campaignId')}}">
                        <input type="hidden" name="adgroup_id" value="{{array_get($adgroup,'adGroupId')}}">
                        <div class="col-md-2">
                        <select class="form-control" name="stateFilter" id="stateFilter" >
                            <option value="" >All Status</option>
                            @foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
                                <option value="{{$k}}" >{{$v}}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="col-md-2">
                        <div id="reportrange" class="btn default">
                            <i class="fa fa-calendar"></i> &nbsp;
                            <span>{{ date('Y-m-d',strtotime('-29 days')).' - '.date('Y-m-d')}}</span>
                            <b class="fa fa-angle-down"></b>
                            <input type="hidden" name="start_date" id="start_date" value="{{date('Y-m-d',strtotime('-29 days'))}}">
                            <input type="hidden" name="end_date" id="end_date" value="{{date('Y-m-d')}}">
                        </div>
                        </div>
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
                    <div class="row">
                        <div class="col-lg-2 col-md-4 col-xs-12">
                            <div class="mt-element-ribbon bg-grey-steel">
                                <div class="ribbon ribbon-color-default uppercase">Spend</div>
                                <p class="ribbon-content"><span class="text-primary total_spend">0</span><span class="text-success"> TOTAL</span></p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-xs-12">
                            <div class="mt-element-ribbon bg-grey-steel">
                                <div class="ribbon ribbon-color-primary uppercase">Clicks</div>
                                <p class="ribbon-content"><span class="text-primary total_clicks">0</span><span class="text-success"> TOTAL</span></p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-xs-12">
                            <div class="mt-element-ribbon bg-grey-steel">
                                <div class="ribbon ribbon-color-info uppercase">CTR</div>
                                <p class="ribbon-content"><span class="text-primary avg_ctr">0</span><span class="text-success"> AVERAGE</span></p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-xs-12">
                            <div class="mt-element-ribbon bg-grey-steel">
                                <div class="ribbon ribbon-color-success uppercase">Orders</div>
                                <p class="ribbon-content"><span class="text-primary total_orders">0</span><span class="text-success"> TOTAL</span></p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-xs-12">
                            <div class="mt-element-ribbon bg-grey-steel">
                                <div class="ribbon ribbon-color-danger uppercase ">ACOS</div>
                                <p class="ribbon-content"><span class="text-primary avg_acos">0</span><span class="text-success"> AVERAGE</span></p>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-xs-12">
                            <div class="mt-element-ribbon bg-grey-steel">
                                <div class="ribbon ribbon-color-warning uppercase ">ROAS</div>
                                <p class="ribbon-content"><span class="text-primary avg_raos">0</span><span class="text-success"> AVERAGE</span></p>
                            </div>
                        </div>
                    </div>

                    <div id="chartData" >
                        <div class="col-md-12" id="lineChart" style="height:300px;"></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="caption font-dark col-md-12">

                        <div class="btn-group" style="float:right;">
                            <button class="btn green dropdown-toggle" type="button" data-toggle="modal" href="#updateForm"> Create
                            </button>
                        </div>


                        <div class="btn-group batch-update">
                            <div class="table-actions-wrapper" id="table-actions-wrapper">
                                
                                <select id="confirmStatus" class="table-group-action-input form-control input-inline">
                                    <option value="">Select Status</option>
                                    @foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
                                        <option value="{{$k}}" >{{$v}}</option>
                                    @endforeach
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
                                    <th></th>
                                    <th>Status</th>
									<th>Value</th>
                                    <th>Match Type</th>
                                    <th>Type</th>
									<th>Serving Status</th>
                                    <th>Suggested Bid</th>
                                    <th>Bid</th>
									<th>Impressions</th>
									<th>Clicks</th>
									<th>CTR</th>
									<th>Spend</th> 
									<th>CPC</th>
									<th>Orders</th>
                                    <th>Sales</th>
                                    <th>ACOS</th>
                                    <th>ROAS</th>                     
                                </tr>
                                <tr>
                                    <th colspan=8></th>
                                    <th><span class="text-primary total_impressions">0</span></th>
									<th><span class="text-primary total_clicks">0</span></th>
									<th><span class="text-primary avg_ctr">0</span></th>
									<th><span class="text-primary total_spend">0</span></th> 
									<th><span class="text-primary avg_cpc">0</span></th>
									<th><span class="text-primary total_orders">0</span></th>
                                    <th><span class="text-primary total_sales">0</span></th>
                                    <th><span class="text-primary avg_acos">0</span></th>
                                    <th><span class="text-primary avg_raos">0</span></th>
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
<div class="modal fade bs-modal-lg" id="updateForm" tabindex="-1" role="updateForm" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Targets</h4>
            </div>
            
            <div class="modal-body" style="font-size:12px;overflow-y:auto;height:500px;"> 
                <div class="form-group col-md-2" style="margin-top: 10px;">
                    <div class="row" style="font-weight:bold;">
                    <div class="col-md-12">
                    Suggested Asins
                    </div>
                    </div>
                    @foreach ($suggestedProducts as $k=>$v)
                    <div class="row">
                    <div class="col-md-12">
                    {{array_get($v,'recommendedTargetAsin')}}
                    </div>
                    </div>
                    @endforeach
                </div>

                <div class="form-group col-md-4" style="margin-top: 10px;">
                    <div class="row" style="font-weight:bold;">
                    <div class="col-md-6">
                    Suggested Categories
                    </div>
                    <div class="col-md-6">
                    Category ID
                    </div>
                    
                    </div>
                    @foreach ($suggestedCategories as $k=>$v)
                    <div class="row">
                    <div class="col-md-6">
                    {{array_get($v,'name')}}
                    </div>
                    <div class="col-md-6">
                    {{array_get($v,'id')}}
                    </div>
                    
                    </div>
                    @endforeach
                </div>

                <div class="form-group col-md-6" style="margin-top: 10px;">
                    <ul class="nav nav-tabs" id="addType">
						<li class="active">
							<a href="#customize" data-toggle="tab" aria-expanded="true">Customize</a>
						</li>
						<li >
							<a href="#enter_list" data-toggle="tab" aria-expanded="true">Enter List</a>
						</li>
					</ul>
                    <div class="tab-content">
						<div class="tab-pane active" id="customize">
                        <div class="form-group mt-repeater">
							<div data-repeater-list="expressions">
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-4">
											<label class="control-label">Asin&Category</label>
											<input type="text" class="form-control input-sm"  name="value" required>
								        </div>
										<div class="col-md-3">
											<label class="control-label">Match Type</label>
											<select class="form-control input-sm" name="type" required>
                                            @foreach (\App\Models\PpcProfile::EXPRESSION as $k=>$v)
                                                <option value="{{$k}}" >{{$v}}</option>
                                            @endforeach
											</select>
                                        </div>

                                        <div class="col-md-3">
											<label class="control-label">Bid</label>
											<div class="input-group">
                                            <input type="text" class="form-control input-sm"  name="bid" value="0" required>
                                            </div>
                                        </div>
										<div class="col-md-2">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete btn-sm">
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
                            </div>
							<a href="javascript:;" data-repeater-create class="btn btn-sm btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add target</a>
						</div>
                        </div>

                        <div class="tab-pane" id="enter_list">
                            <div class="form-group col-md-12">
                                <label>Match Type *</label>
                                <select class="form-control" name="match_type" id="match_type">
                                @foreach (\App\Models\PpcProfile::EXPRESSION as $k=>$v)
                                    <option value="{{$k}}" >{{$v}}</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Bid Option:</label>
                                <select class="form-control" name="bidOption" id="bidOption">
                                <option value="suggested" >Suggested Bid</option>
                                <option value="customize" >Customize Bid</option>
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label>Bid:</label>
                                <input class="form-control"  name="bid" id="bid" value="{{array_get($adgroup,'defaultBid')}}">
                            </div>


                            <div class="form-group col-md-12">
                                <label>Keywords *</label>
                                <textarea class="form-control" rows="10" name="keyword_text" id="keyword_text"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                        </div>
                        </div>
						<div style="clear:both;height:30px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                <button type="submit" class="btn green">Save changes</button>
                <input type="hidden" name="profile_id" value="{{$profile_id}}">
                <input type="hidden" name="ad_type" value="{{$ad_type}}">
                <input type="hidden" name="defaultBid" value="{{array_get($adgroup,'defaultBid')}}">
                <input type="hidden" name="campaignId" value="{{array_get($adgroup,'campaignId')}}">
                <input type="hidden" name="adGroupId" value="{{array_get($adgroup,'adGroupId')}}">
                <input type="hidden" name="action" value="product_targeting">
                <input type="hidden" name="method" value="createTargetingClauses">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
</form>
<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" >
            <div class="modal-body" >
                Loading...
            </div>
        </div>
    </div>
</div>
<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>

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
            grid.setAjaxParam("stateFilter", $("select[name='stateFilter']").val());
            grid.setAjaxParam("ad_type", $("input[name='ad_type']").val());
            grid.setAjaxParam("campaign_id", $("input[name='campaign_id']").val());
            grid.setAjaxParam("adgroup_id", $("input[name='adgroup_id']").val());
            grid.setAjaxParam("name", $("input[name='name']").val());
            grid.setAjaxParam("start_date", $("input[name='start_date']").val());
            grid.setAjaxParam("end_date", $("input[name='end_date']").val());
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
                   "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,1,2,3,4,5,6,7] }],
                   "order": [
                        [8, "desc"]
                    ],
                    "lengthMenu": [
                        [50, 100, 300, -1],
                        [50, 100, 300, 'All'] 
                    ],
                    "pageLength": 300,
                    "ajax": {
                        "url": "{{ url('adv/listProducts')}}",
                    },
                    scrollY:500,
                    scrollX:true,
					

					fixedColumns:   {
						leftColumns:4
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
                     ],
                     */
                    "drawCallback": function( oSettings ) {
                        var ChatsData=jQuery.parseJSON(oSettings.jqXHR.responseText).recordsForChart;
                        var spend = 0;var clicks =0;var impressions =0;var orders =0;var attributed_sales1d =0;var attributed_units_ordered1d =0;
                        var chartX=[];
                        var chartY=[
                            {
                                name:'Spend',
                                type:'line',
                                data:[]
                            },
                            {
                                name:'Clicks',
                                type:'line',
                                data:[]
                            },
                            {
                                name:'Orders',
                                type:'line',
                                data:[]
                            },
                            {
                                name:'CTR',
                                type:'line',
                                data:[]
                            },
                            {
                                name:'ACOS',
                                type:'line',
                                data:[]
                            },
                            {
                                name:'RAOS',
                                type:'line',
                                data:[]
                            },
                        ];
                        for( var child_i in ChatsData )
                        {
                            chartX.push(child_i);
                            var child_value = ChatsData[child_i];
                            for (var i in chartY){
                                if(chartY[i].name=='Spend') chartY[i].data.push(Number(child_value.cost).toFixed(2));
                                if(chartY[i].name=='Clicks') chartY[i].data.push(Number(child_value.clicks));
                                if(chartY[i].name=='Orders') chartY[i].data.push(Number(child_value.attributed_units_ordered1d));
                                if(chartY[i].name=='CTR') chartY[i].data.push(((Number(child_value.impressions)<=0)?0:Number(child_value.clicks)/Number(child_value.impressions)).toFixed(4));
                                if(chartY[i].name=='ACOS') chartY[i].data.push(((Number(child_value.attributed_sales1d)<=0)?0:Number(child_value.cost)/Number(child_value.attributed_sales1d)).toFixed(4));
                                if(chartY[i].name=='RAOS') chartY[i].data.push(((Number(child_value.spend)<=0)?0:Number(child_value.attributed_sales1d)/Number(child_value.cost)).toFixed(4));
                            }
                            spend+=Number(child_value.cost);
                            clicks+=Number(child_value.clicks);
                            orders+=Number(child_value.attributed_units_ordered1d);
                            impressions+=Number(child_value.impressions);
                            attributed_sales1d+=Number(child_value.attributed_sales1d);
                            attributed_units_ordered1d+=Number(child_value.attributed_sales1d);
                        }
                        $(".total_spend").html(spend.toFixed(2));
                        $(".total_clicks").html(clicks);
                        $(".total_orders").html(orders);
                        $(".total_impressions").html(impressions);
                        $(".total_sales").html(attributed_sales1d.toFixed(2));
                        $(".avg_cpc").html(((clicks<=0)?0:spend/clicks).toFixed(2));
                        $(".avg_ctr").html(((impressions<=0)?0:clicks/impressions*100).toFixed(2)+'%');
                        $(".avg_acos").html(((attributed_sales1d<=0)?0:spend/attributed_sales1d*100).toFixed(2)+'%');
                        $(".avg_raos").html(((spend<=0)?0:attributed_sales1d/spend*100).toFixed(2)+'%');

                        var lineChart = echarts.init(document.getElementById('lineChart'));

                        var option = {
                            tooltip : {
                                trigger: 'axis'
                            },
                            legend: {
                                data:['Spend','Clicks','Orders','CTR','ACOS','RAOS']
                            },
                            grid: {
                                x:40,
                                y:40,
                                x2:40,
                                y2:40,
                            },
                            toolbox: {
                                show : true,
                                feature : {
                                    mark : {show: true},
                                    dataView : {show: true, readOnly: false},
                                    magicType : {show: true, type: ['line']},
                                    restore : {show: true},
                                    saveAsImage : {show: true}
                                }
                            },
                            calculable : true,
                            xAxis : [
                                {
                                    type : 'category',
                                    boundaryGap : false,
                                    data : chartX
                                }
                            ],
                            yAxis : [
                                {
                                    type : 'value'
                                }
                            ],
                            series : chartY
                        };
                        lineChart.setOption(option);
                        $('.ajax_bid').editable({
                            type: 'text',
                            url: '/adv/updateBid',
							showbuttons:false,
                            mode:'inline',
                            params:{
                                'action':'product_targeting',
                                'method':'updateTargetingClauses',
                                'pk_type':'targetId',
                                'profile_id':$("input[name='profile_id']").val(),
                                'ad_type':$("input[name='ad_type']").val(),
                            },
                            validate: function (value) {
                                if (isNaN(value)) {
                                    return 'Must be a number';
                                }
                            },
                            success: function (response) { 
                                var obj = JSON.parse(response);
                                $.each(obj.response,function(index,value){
                                    toastr.success(value.code);
                                });
                            }, 
                            error: function (response) { 
                                return 'remote error'; 
                            }
                        });
                    },
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
                var method = 'updateTargetingClauses';
                if (confirmStatus.val() != "" && grid.getClonedSelectedRowsCount() > 0) {
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                    });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ url('adv/batchUpdate') }}",
                        data: {confirmStatus:confirmStatus.val(),id:grid.getClonedSelectedRows(),profile_id:profile_id,ad_type:ad_type,id_type:id_type,action:action,method:method},
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
                } else if (grid.getClonedSelectedRowsCount() === 0) {
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

        $('#reportrange').daterangepicker({
                opens: (App.isRTL() ? 'left' : 'right'),
                startDate: moment().subtract('days', 29),
                endDate: moment(),
                dateLimit: {
                    days: 60
                },
                showDropdowns: true,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                buttonClasses: ['btn'],
                applyClass: 'green',
                cancelClass: 'default',
                format: 'YYYY-MM-DD',
                separator: ' to ',
                locale: {
                    applyLabel: 'Apply',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom Range',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    firstDay: 1
                }
            },
            function (start, end) {
                $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                $('#reportrange input[name="start_date"]').val(start.format('YYYY-MM-DD'));
                $('#reportrange input[name="end_date"]').val(end.format('YYYY-MM-DD'));
            }
        );

        $('#update_form').submit(function() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ url('adv/createTarget') }}",
                data: $('#update_form').serialize(),
                success: function (data) {
                    if(data.customActionStatus=='OK'){
                        $('#updateForm').modal('hide');
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

        $('#addType>li>a').on('click',function(){
            $($(this).attr('href')+' input,select,textarea').attr('disabled',false);
            $($(this).attr('href')).siblings().find('select,input,textarea').attr('disabled',true);
        });

        $('#ajax').on('hidden.bs.modal', function (e) {
            $('#ajax .modal-content').html('<div class="modal-body" >Loading...</div>');
        });
    });


</script>
@endsection

