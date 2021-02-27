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
        .table tr .title{
            width: 380px !important
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
                            <span class="input-group-addon">Asin</span>
                            <input  class="form-control"  value="" id="asin" name="asin"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" onchange="getAccountBySite()" name="site">
                                @foreach(getMarketDomain() as $value)
                                    <option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                            <select class="btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">

                            </select>
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
                        <th>Asin</th>
                        <th>Title</th>
                        <th>Account</th>
                        <th>REFUND QUANTITY</th>
                        <th>RETURN QUANTITY</th>
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
                {data: 'asin',name:'asin'},
                {data: 'title',name:'title',class:'title'},
                {data: 'account',name:'account'},
                {data: 'refund_quantity',name:'refund_quantity'},
                {data: 'return_quantity',name:'return_quantity'},
            ],
            ajax: {
                type: 'POST',
                url: '/returnAnalysis/asinAnalysis',
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

        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            $.ajax({
                type: 'post',
                url: '/showAccountBySite',
                data: {marketplaceid:marketplaceid},
                dataType:'json',
                success: function(res) {
                    if(res.status==1){
                        var html = '';
                        $.each(res.data,function(i,item) {
                            html += '<option value="'+item.id+'">'+item.label+'</option>';
                        })
                        var str = '<span class="input-group-addon">Account</span>\n' +
                            '\t\t\t\t\t\t\t<select class="mt-multiselect btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">\n' +
                            '\n' +html+
                            '\t\t\t\t\t\t\t</select>';
                        $('#account-div').html(str);
                        ComponentsBootstrapMultiselect.init();//处理account的多选显示样式
                    }else{
                        alert('请先选择站点');
                    }
                }
            });

        }

        $(function(){
            getAccountBySite()//触发当前选的站点得到该站点所有的账号
            $("#search_table").trigger("click");
        })
    </script>
@endsection