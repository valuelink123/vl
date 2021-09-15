@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['']])
@endsection
@section('content')
    @include('frank.common')
    <style>
        table th{
            text-align:center;
        }
        table.dataTable thead th, table.dataTable thead td {
            padding: 10px 0px !important;
        }
        table.dataTable tbody td {
            padding: 8px 5px !important;
        }
        .table td, .table th {
            font-size: 12px !important;
        }
        .table tr td{
            word-wrap:break-word !important;
            /*word-break:break-all !important;*/
            white-space:nowrap !important;
        }
    </style>
    <div class="row">
        <div class="top portlet light">
            <div style="margin-left: -15px;">
                <div class="col-md-11">
                    @if(!empty($neweggSKuString))
                    <div align="left">没有建立有效匹配的平台SKU如下：<span style="color:#ff0000;font-weight: bold">（请添加平台SKU和SAP_SKU对照，否则有的订单会缺失SKU）</span>
                    </div>
                    <div align="left">{{$neweggSKuString}}</div>
                    @else
                    @endif
                </div>
                <div class="col-md-1 pull-right">
                    <button type="button" id="skuMatchList" class="btn sbold blue">SKU对照关系表</button>
                </div>
            </div>

            <div style="clear: both"></div>
            <div style="height:10px"></div>
<!--            <div>-->
<!--                <button id="addSkuMatch" class="btn sbold blue">添加SKU对照</button> &nbsp;&nbsp;-->
<!--                <button id="skuMatchList" class="btn sbold blue">更新SKU对照</button>-->
<!--            </div>-->
            <div style="height:10px"></div>
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
                            <span class="input-group-addon">Order ID</span>
                            <input  class="form-control"  value="" id="order_id" name="order_id"/>
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
            <div class="btn-group " style="float:right;margin-top:20px;">
                <div class="col-md-12">
                    <div class="col-md-2">
                            <button id="export" class="btn sbold blue"> Export
                                <i class="fa"></i>
                            </button>
                    </div>
                </div>
            </div>
            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
<!--                        <th>平台编号</th>-->
<!--                        <th>站点</th>-->
                        <th>平台订单号</th>
<!--                        <th>售达方</th>-->
<!--                        <th>订单类型</th>-->
<!--                        <th>订单交易号</th>-->
                        <th>下单日期</th> <!--不用导出-->
                        <th>付款日期</th>
<!--                        <th>付款交易ID</th>-->
                        <th>买家ID</th>
<!--                        <th>买家姓名</th>-->
                        <th>国家代码</th>
<!--                        <th>城市名</th>-->
<!--                        <th>州/省</th>-->
<!--                        <th>街道1</th>-->
<!--                        <th>街道2</th>-->
<!--                        <th>邮编</th>-->
<!--                        <th>邮箱</th>-->
<!--                        <th>电话1</th>-->
<!--                        <th>成交费</th>-->
<!--                        <th>货币</th>-->
                        <th>佣金</th>
                        <th>货币</th>
                        <th>订单总价</th>
                        <th>货币</th>
<!--                        <th>实际运输方式</th>-->
<!--                        <th>平台订单号</th>-->
<!--                        <th>站点</th>-->
<!--                        <th>行号</th>-->
                        <th>平台SKU</th>  <!--不用导出-->
                        <th>平台SKU数量</th>  <!--不用导出-->
                        <th>SAP物料号</th>
                        <th>数量</th>
<!--                        <th>工厂</th>-->
<!--                        <th>仓库</th>-->
<!--                        <th>行项目ID</th>-->
<!--                        <th>帖子ID</th>-->
<!--                        <th>帖子标题</th>-->
                        <th>销售员</th>
<!--                        <th>行交易ID</th>-->
<!--                        <th>标记完成</th>-->
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

        $('#settlement_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 15, // default record count per page
            "lengthMenu": [
                [15, 30,50,],
                [15, 30,50,] // change per page values here
            ],
            processing: true,
            columns: [
                // {data: 'newegg_sap_id', name: 'newegg_sap_id'},
                // {data: 'site', name: 'site'},
                {data: 'newegg_order_id', name: 'newegg_order_id'},
                // {data: 'sold_to_party', name: 'sold_to_party'},
                // {data: 'order_type', name: 'order_type'},
                // {data: 'order_transaction_id', name: 'order_transaction_id'},
                {data: 'creation_date', name: 'creation_date'},
                {data: 'payment_date', name: 'payment_date'},
                // {data: 'payment_transaction_id', name: 'payment_transaction_id'},
                {data: 'user_id', name: 'user_id'},
                // {data: 'user_name', name: 'user_name'},
                {data: 'country_code', name: 'country_code'},
                // {data: 'city', name: 'city'},
                // {data: 'state_or_province', name: 'state_or_province'},
                // {data: 'consignee_addr1', name: 'consignee_addr1'},
                // {data: 'consignee_addr2', name: 'consignee_addr2'},
                // {data: 'postal_code', name: 'postal_code'},
                // {data: 'user_email', name: 'user_email'},
                // {data: 'consignee_phone', name: 'consignee_phone'},
                // {data: 'transaction_fee', name: 'transaction_fee'},
                // {data: 'transaction_fee_currency', name: 'transaction_fee_currency'},
                {data: 'commission', name: 'commission'},
                {data: 'commission_currency', name: 'commission_currency'},
                {data: 'order_total_amount', name: 'order_total_amount'},
                {data: 'order_total_amount_currency', name: 'order_total_amount_currency'},
                // {data: 'shipment_code', name: 'shipment_code'},
                // {data: 'newegg_order_id', name: 'newegg_order_id'},
                // {data: 'site', name: 'site'},
                // {data: 'line_number', name: 'line_number'},
                {data: 'newegg_sku', name: 'newegg_sku'},
                {data: 'newegg_sku_qty', name: 'newegg_sku_qty'},
                {data: 'sap_sku', name: 'sap_sku'},
                {data: 'sap_sku_qty', name: 'sap_sku_qty'},
                // {data: 'factory', name: 'factory'},
                // {data: 'warehouse', name: 'warehouse'},
                // {data: 'line_item_id', name: 'line_item_id'},
                // {data: 'post_id', name: 'post_id'},
                // {data: 'post_title', name: 'post_title'},
                {data: 'seller_id', name: 'seller_id'},
                // {data: 'line_transaction_id', name: 'line_transaction_id'},
                // {data: 'mark_complete', name: 'mark_complete'},
            ],
            ajax: {
                type: 'POST',
                url: '/neweggOrderList/list',
                data:  {search: $("#search-form").serialize()}
            }
        })
        dtApi = $('#datatable').dataTable().api();
        //点击上面的搜索
        $('#search_table').click(function(){
            // 页面上输入的order_id, 序列化后的空格和换行分别变成了+，%09
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize().replace(/%09|\+/g, '')};
            dtApi.ajax.reload();
            return false;
        })

        $("#export").click(function(){
            var search = $("#search-form").serialize();
            location.href='/neweggOrderList/export?'+search;
        });

        $(function(){
            $("#search_table").trigger("click");
        });

        $('#skuMatchList').click(function(){
            window.open('/neweggOrderList/skuMatchList');
        });

    </script>
@endsection