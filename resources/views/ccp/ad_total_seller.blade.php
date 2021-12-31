@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['ccp adTotalBu dashboard']])
@endsection
@section('content')
    @include('frank.common')
    <div class="row">
        <div class="top portlet light" style="margin-left:-25px;">
            <form id="search-form" >
                <div class="search portlet light">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">起始日期</span>
                            <input  class="form-control"  value="{!! $start_date !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="start_date" name="start_date"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">截止日期</span>
                            <input  class="form-control"  value="{!! $end_date !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="end_date" name="end_date"/>
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
                                @foreach($bgs as $val)
                                    <option value="{{$val['bg']}}">{{$val['bg']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">BU</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="bu" name="bu">
                                @foreach($bus as $val)
                                    <option value="{{$val}}">{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
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
                        <th>人员</th>
                        <th>SKU</th>
                        <th>站内纯广告费用</th>
                        <th>站内纯广告销售额</th>
                        <th>站内纯广告费用/纯广告销售额比例</th>
                        <th>站内总销售额</th>
                        <th>总销售额扣除15%（退货10%和VAT的5%)</th>
                        <th>站内纯广告费用/总销售额</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        //日期控件初始化
        $('#start_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });
        $('#end_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            paging: false,
            processing: true,
            ordering: false, // 禁止排序
            columns: [
                {data: 'seller',name:'seller'},
                {data: 'sku',name:'sku'},
                {data: 'ad_cost',name:'ad_cost'},
                {data: 'ad_sales',name:'ad_sales'},
                {data: 'ad_acos',name:'ad_acos'},
                {data: 'total_sales',name:'total_sales'},
                {data: 'actual_sales',name:'actual_sales'},
                {data: 'acos',name:'acos'},
            ],
            ajax: {
                type: 'POST',
                url: '/ccp/adTotalSeller/list',
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

        $(function(){
            // 根据搜索时间区域，触发点击事件，
            $("#search_top").trigger("click");
        })
    </script>
@endsection