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
            <div class="search_table" style="margin-left: -15px;margin-bottom: 50px;">
                <form id="search-form">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-addon">SKU</span>
                            <input  class="form-control"  value="{!! $sku !!}" name="sku" id="sku" placeholder="请输入平台SKU或者SAP_SKU"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <div class="btn-group pull-right">
                                <button id="search_table" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 pull-right text-right">
                        <button type="button" id="addSkuMatch" class="btn sbold blue">新增SKU对照关系</button>
                    </div>
                </form>
            </div>
            <div class="btn-group " style="float:right;margin-top:20px;">
                <div class="col-md-12">
                    <div class="col-md-2 text-right">
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
                        <th>ID</th>
                        <th>平台SKU</th>
                        <th>平台SKU的单位数量</th>
                        <th>SAP SKU</th>
                        <th>SAP SKU的数量</th>
                        <th>仓库</th>
                        <th>工厂</th>
                        <th>实际运输方式</th>
                        <th>操作</th>
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
                {data: 'id', name: 'id'},
                {data: 'ebay_sku', name: 'ebay_sku'},
                {data: 's_qty', name: 's_qty'},
                {data: 'sap_sku', name: 'sap_sku'},
                {data: 't_qty', name: 't_qty'},
                {data: 'warehouse', name: 'warehouse'},
                {data: 'factory', name: 'factory'},
                {data: 'shipment_code', name: 'shipment_code'},
                {data: 'action', name: 'action'},
            ],
            ajax: {
                type: 'POST',
                url: location.href,
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

        $("#export").click(function(){
            var search = $("#search-form").serialize();
            location.href='/ebayOrderList/exportSkuMatchList';
        });

        $(function(){
            $("#search_table").trigger("click");
        });

        $('#addSkuMatch').click(function(){
            window.open('/ebayOrderList/addSkuMatch');
        });

    </script>
@endsection