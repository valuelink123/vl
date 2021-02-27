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
        table.dataTable thead th, table.dataTable thead td {
            padding: 10px 0px !important;
        }
        .table thead tr th {
            font-size: 12px !important;
            font-weight: 600;
        }
        .table td, .table th {
            font-size: 12px;
        }
        .table tr .title{
            width: 280px !important
        }
        .table tr .sku{
            width: 110px !important
        }
        .table{
            table-layout:fixed;
        }
        .table tr .title{
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
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
                            <span class="input-group-addon">Sku</span>
                            <input  class="form-control"  value="" id="sku" name="sku"/>
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
                        <th>SKU</th>
                        <th>Title</th>
                        @foreach($data['reasonType'] as $key=>$val)
                            <th>{{$val['name']}}</th>
                        @endforeach
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
            ordering:true,
            "pageLength": 15, // default record count per page
            "lengthMenu": [
                [15, 30,50,],
                [15, 30,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'sku',name:'sku',class:'sku'},
                {data: 'title',name:'title',class:'title'},
                {data: 'type_0',name:'type_0'},
                {data: 'type_1',name:'type_1'},
                {data: 'type_2',name:'type_2'},
                {data: 'type_3',name:'type_3'},
                {data: 'type_4',name:'type_4'},
                {data: 'type_5',name:'type_5'},
                {data: 'type_6',name:'type_6'},
                {data: 'type_7',name:'type_7'},
                {data: 'type_8',name:'type_8'},
                {data: 'type_9',name:'type_9'},
                {data: 'type_10',name:'type_10'},
                {data: 'type_11',name:'type_11'},
                {data: 'type_12',name:'type_12'},
                {data: 'type_13',name:'type_13'},
                {data: 'type_14',name:'type_14'},
                {data: 'type_15',name:'type_15'},
                {data: 'type_16',name:'type_16'},
                {data: 'type_17',name:'type_17'},
                {data: 'type_18',name:'type_18'},
                {data: 'total',name:'total'},
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