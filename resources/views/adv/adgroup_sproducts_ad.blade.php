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
                <li class="active">
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/ad" >Ads</a>
                </li>
                <li >
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/targetkeyword" >Targeting keywords</a>
                </li>
                <li >
                    <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/negkeyword" >Negative keywords</a>
                </li>

                <li>
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
                                    <th>
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax .checkboxes" />
                                    </th>
									<th>Asin</th>
                                    <th>Seller Sku</th>
									<th>Status</th>
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
                                    <th colspan=4></th>
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
<div class="modal fade" id="updateForm" tabindex="-1" role="updateForm" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Seller Sku</h4>
            </div>
            
            <div class="modal-body"> 
                <div class="form-group col-md-12">
                    <label>Seller Sku *</label>
                    <textarea class="form-control" rows="10" name="asins" id="asins"
                    placeholder="Enter your list and separate each item whith a new line."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                <button type="submit" class="btn green">Save changes</button>
                <input type="hidden" name="profile_id" value="{{$profile_id}}">
                <input type="hidden" name="ad_type" value="{{$ad_type}}">
                <input type="hidden" name="campaignId" value="{{array_get($adgroup,'campaignId')}}">
                <input type="hidden" name="adGroupId" value="{{array_get($adgroup,'adGroupId')}}">
                <input type="hidden" name="action" value="product_ads">
                <input type="hidden" name="method" value="createProductAds">
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
                   "autoWidth":false,
                   "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,1,2,3] }],
                   "order": [
                        [4, "desc"]
                    ],
                    "lengthMenu": [
                        [50, 100, 300, -1],
                        [50, 100, 300, 'All'] 
                    ],
                    "pageLength": 300,
                    "ajax": {
                        "url": "{{ url('adv/listAds')}}",
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
                    },
                 }
            });


            //批量更改状态操作
            $(".batch-update").unbind("click").on('click', '.table-status-action-submit', function (e) {
                e.preventDefault();
                var confirmStatus = $("#confirmStatus", $("#table-actions-wrapper"));
                var profile_id = $("input[name='profile_id']").val();
                var ad_type = $("input[name='ad_type']").val();
                var id_type = 'adId';
                var action = 'product_ads';
                var method = 'updateProductAds';
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
                url: "{{ url('adv/createAds') }}",
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
    });


</script>
@endsection

