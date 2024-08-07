@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['salesAlert SKU']])
@endsection
@section('content')
    @include('frank.common')
    <div class="row">
        <div class="top portlet light" style="margin-left:-25px;">
            <form id="search-form" >
                <div class="search portlet light">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">年</span>
                            <input  class="form-control"  value="{!! $start_date !!}" id="year" name="year"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">月</span>
                            <input  class="form-control"  value="{!! $end_date !!}" id="month" name="month"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">站点</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" name="site">
                                @foreach($site as $value)
                                    <option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="bg" name="bg">
{{--                                <option value="">ALL</option>--}}
                                @foreach($bgs as $val)
                                    <option value="{{$val['bg']}}">{{$val['bg']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_top" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="top portlet light">
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>SKU</th>
                        <th>销售额</th>
                        <th>营销费用</th>
                        <th>占比</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        //日期控件初始化
        $('#year').datepicker({
            format: 'yyyy',
            autoclose: true
        });
        $('#month').datepicker({
            format: 'mm',
            autoclose: true
        });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering: false, // 禁止排序
            "pageLength": 15, // default record count per page
            "lengthMenu": [
                [15, 30,50,],
                [15, 30,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'sku',name:'sku'},
                {data: 'ad_sales',name:'ad_sales'},
                {data: 'ad_cost',name:'ad_cost'},
                {data: 'proportion',name:'proportion'},
            ],
            ajax: {
                type: 'POST',
                url: '/salesAlert/totalSku/list',
                data:  {search: $("#search-form").serialize()}
            }
        })
        // 点击上面的搜索
        $('#search_top').click(function(){
            // 改变下面表格的数据内容
            dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtapi.ajax.reload();
            return false;
        })

        //点击导出
//        $('#export_table').click(function(){
//            var search = $("#search-form").serialize();
//            location.href='/ccp/adTotalBu/export?'+search;
//            return false;
//        })

        $(function(){
            // 根据搜索时间区域，触发点击事件，
            $("#search_top").trigger("click");
        })
    </script>

@endsection