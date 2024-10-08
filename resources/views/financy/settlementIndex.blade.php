@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Amazon Settlement List']])
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
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                            <select class="mt-multiselect btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">
                                @foreach($data['account'] as $value)
                                    <option value="{{$value['id']}}">{{$value['label']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Settlement ID</span>
                            <input  class="form-control"  value="" id="settlement_id" name="settlement_id"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Currency</span>
                            <select class="form-control btn btn-default" name="currency" id="currency">
                                <option value="">select</option>
                                @foreach(getCurrency() as $key=>$val)
                                    <option value="{{$val}}">{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
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
                        <a  data-toggle="modal" href="/settlement/export" target="_blank">
                            <button id="export" class="btn sbold blue"> Export
                                <i class="fa "></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>


            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Settlement ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Deposit Date</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Detail</th>
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
                {data: 'settlement_id',name:'settlement_id'},
                {data: 'settlement_start_date',name:'settlement_start_date'},
                {data: 'settlement_end_date',name:'settlement_end_date'},
                {data: 'deposit_date',name:'deposit_date'},
                {data: 'total_amount',name:'total_amount'},
                {data: 'currency',name:'currency'},
                {data: 'detail',name:'detail'},
            ],
            ajax: {
                type: 'POST',
                url: '/settlement/list',
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
            location.href='/settlement/export?'+search+'&account='+accountid;
        });

        $(function(){
            $("#search_table").trigger("click");
        })
    </script>
@endsection