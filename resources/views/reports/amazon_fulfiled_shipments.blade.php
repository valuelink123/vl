@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['amazon Fulfiled Shipments']])
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
                    <div class="col-md-2" id="account-div">
                        <select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" data-none-selected-text="Select Accounts" name="seller_account_id[]" id="seller_account_id[]">
                            @foreach ($accounts_data as $id=>$name)
                                <option value="{{$id}}">{{$name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_table" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="input-group">
{{--                            <a href="/amazonFulfiledShipments/export" target="_blank">--}}
                                <button id="to_report" class="btn sbold blue"> 生成报表
                                    <i class="fa"></i>
                                </button>
{{--                            </a>--}}
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>查询起始时间</th>
                        <th>查询结束时间</th>
                        <th>账号</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>更新时间</th>
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
                {data: 'id',name:'id'},
                {data: 'from_date',name:'from_date'},
                {data: 'to_date',name:'to_date'},
                {data: 'account',name:'account'},
                {data: 'status',name:'status',class:'status'},
                {data: 'created_at',name:'created_at'},
                {data:'updated_at',name:'updated_at'},
                {data:'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/amazonFulfiledShipments/list',
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

        $("#to_report").click(function(){
            var search = $("#search-form").serialize();
            var accountid = '';
            var vv = '';
            $("#account-div .active").each(function (index,value) {
                vv = $(this).find('input').val();
                if(accountid != ''){
                    accountid = accountid + ',' + vv
                }else{
                    accountid = accountid + vv
                }
            });
            $.ajax({
                type: "POST",
                url: "/amazonFulfiledShipments/export",
                data: {
                    search: search,
                    account: accountid
                },
                success: function (res) {
                    if(res.status == 0){
                        alert(res.msg);
                    }else if(res.status == 1){
                        alert(res.msg);
                        dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                        dtApi.ajax.reload();
                    }
                },
            });
            return false;
        });

        $(function(){
            // ComponentsBootstrapMultiselect.init();//处理account的多选显示样式
            $("#search_table").trigger("click");
        })
    </script>
@endsection