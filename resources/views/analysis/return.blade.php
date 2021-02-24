@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['return analysis']])
@endsection
@section('content')
    @include('frank.common')
    <style>
        table th{
            text-align:center;
        }
    </style>
    <div class="row">
        <div class="top portlet light">
            <div class="search_table" style="margin-left: -15px;margin-bottom: 50px;">
                <form id="search-form">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From Date</span>
                            <input  class="form-control"  value="{!! $data['fromDate'] !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="from_date" name="from_date"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">To Date</span>
                            <input  class="form-control"  value="{!! $data['toDate'] !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="to_date" name="to_date"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_table" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>sku</th>
                        <th>产品缺陷</th>
                        <th>品质问题</th>
                        <th>产品损坏</th>
                        <th>缺少配件</th>
                        <th>不想要了</th>
                        <th>和描述不相符</th>
                        <th>下错订单</th>
                        <th>未收到货</th>
                        <th>有更好的价格</th>
                        <th>交期超时</th>
                        <th>未经授权购买</th>
                        <th>不适合</th>
                        <th>未知原因</th>
                        <th>发错货</th>
                        <th>买多了</th>
                        <th>小计</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        //日期控件初始化
        $('#to_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });
        $('#from_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 20, // default record count per page
            "lengthMenu": [
                [10, 20,50,],
                [10, 20,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'id',name:'id'},
                {data: 'account',name:'account'},
                {data: 'amazon_order_id',name:'amazon_order_id'},
                {data: 'date',name:'date'},
                {data: 'asin',name:'asin'},
                {data: 'seller_sku',name:'seller_sku'},
                {data: 'quantity',name:'quantity'},
                {data: 'status',name:'status'},
                {data: 'reason',name:'reason'},
                {data: 'condition',name:'condition'},
                {data: 'customer_comments',name:'customer_comments'},
                {data: 'settlement_id',name:'settlement_id'},
                {data: 'settlement_date',name:'settlement_date'},
            ],
            ajax: {
                type: 'POST',
                url: '/returnAnalysis/returnAnalysis',
                data:  {search: $("#search-form").serialize()}
            }
        })
        dtApi = $('#datatable').dataTable().api();
        //点击上面的搜索
        $('#search_table').click(function(){
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
        })

        $(function(){
            $("#search_table").trigger("click");
        })
    </script>
@endsection