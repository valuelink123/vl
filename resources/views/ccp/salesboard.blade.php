@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['salesboard']])
@endsection
<style>
    .total-data-table{
        width:100%;
        margin-bottom: 20px;
        /*border-color: #676464;*/
    }
    .top-total-data .weight td{
        font-size:40px;
        font-weight:400;
    }
    .total-data-table td{
        padding: 7px;
        line-height: 15px;
        /*border-left: 1px solid #676464;*/
        /*border-right: 1px solid #676464;*/
        font-size: 18px;
    }
    .date-search-table{
        width:60%;
        margin-bottom: 20px;

    }

    #datatable th{
        text-align:center;
    }
    .date-search-table{
        width:60%;
        margin-bottom: 20px;

    }
    .date-search-table td{
        border:1px solid #676464;
        padding: 7px 12px;
        margin: 11px 20px 0px 0px;
        text-align:center;
    }
    .date-search-table .active{
        background-color:#CD4D00;
        color:#ffffff;
        border:1px solid #CD4D00;
    }
    .second .danwei{
        font-size:15px;
    }
    .third .danwei{
        font-size:10px;
    }
</style>
@section('content')
    @include('frank.common')
    <div class="row">
        <div class="top portlet light" style="margin-left:-25px;">
            <form id="search-form" >
                <input type="hidden" name="date_type" value="">
                <input type="hidden" class="search_asin" name="asin" value="">
                <div class="search portlet light">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select  style="width:100%;height:35px;" id="site" onchange="getAccountBySite()" name="site">
                                @foreach($site as $value)
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
                            <span class="input-group-addon">BGBU</span>
                            <select  style="width:100%;height:35px;" id="bgbu" name="bgbu">
                                <option value="">Select</option>
                                @foreach($bgbu as $value)
                                    <option value="{{ $value->bg }}_{{$value->bu}}">{{ $value->bg }}_{{$value->bu}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Time Type</span>
                            <select  style="width:100%;height:35px;" id="timeType" name="timeType">
                                <option value="1">Local Time</option>
                                <option value="0">BeiJing Time</option>
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
        <div class="top portlet light">
            <table class="top-total-data total-data-table">
                <tr>
                    <td width="20%">SALES</td>
                    <td width="20%">UNITS</td>
                    <td width="20%">ORDERS</td>
                    <td width="20%">Units PER ORDER</td>
                    <td width="20%">AVG.SALES PER UNIT</td>
                </tr>
                <tr class="weight second">
                    <td><span class="sales">0</span>&nbsp;<span class="danwei"></span></td>
                    <td><span class="units">0</span></td>
                    <td><span class="orders">0</span></td>
                    <td><span class="units_per_order">0</span></td>
                    <td><span class="sales_per_unit">0</span>&nbsp;<span class="danwei"></span></td>
                </tr>
                <tr class="third">
                    <td><span>last period:&nbsp;</span><span class="sales">0</span></td>
                    <td><span>last period:&nbsp;</span><span class="units">0</span></td>
                    <td><span>last period:&nbsp;</span><span class="orders">0</span></td>
                    <td><span>last period:&nbsp;</span><span class="units_per_order">0</span></td>
                    <td><span>last period:&nbsp;</span><span class="sales_per_unit">0</span></td>
                </tr>
                <tr class="fourth">
                    <td><span>changed:&nbsp;</span><span class="sales_change"></span></td>
                    <td><span>changed:&nbsp;</span><span class="units_change"></span></td>
                    <td><span>changed:&nbsp;</span><span class="orders_change"></span></td>
                    <td><span>changed:&nbsp;</span><span class="units_per_order_change"></span></td>
                    <td><span>changed:&nbsp;</span><span class="sales_per_unit_change">0</span></td>
                </tr>

            </table>
            <table class="date-search date-search-table">
                <tr>
                    <td class="date_type active" data-value="1">TODAY</td>
                    <td class="date_type" data-value="2">YESTERDAY</td>
                    <td class="date_type" data-value="3">LAST 3 DAYS</td>
                    <td class="date_type" data-value="4">LAST 7 DAYS</td>
                    <td class="date_type" data-value="5">LAST 15 DAYS</td>
                    <td class="date_type" data-value="6">LAST 30 DAYS</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="top portlet light">
            <div class="search_table" style="margin-bottom:50px;margin-left:-15px;">
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">Asin</span>
                        <input class="form-control" value="" id="asin" placeholder="Asin"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <div class="btn-group pull-right" >
                            <button id="search_table" class="btn sbold blue">Search</button>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-striped table-bordered" id="datatable">
                <thead>
                    <tr>
                        <th>IMAGE</th>
                        <th width="350px">PRODUCT</th>
                        <th>ASIN</th>
                        <th class="th_0">SALES</th>
{{--                        <th class="th_0">SALES<br/>Previous Period</th>--}}
{{--                        <th class="th_0">SALES<br/>Change</th>--}}
                        <th class="th_1">UNITS</th>
{{--                        <th class="th_1">UNITS<br/>Previous Period</th>--}}
{{--                        <th class="th_1">UNITS<br/>Change</th>--}}
                        <th class="th_2">ORDERS</th>
{{--                        <th class="th_2">ORDERS<br/>Previous Period</th>--}}
{{--                        <th class="th_2">ORDERS<br/>Change</th>--}}
                        <th class="th_3">Units<br/>PER ORDER</th>
{{--                        <th class="th_3">Units PER ORDER<br/>Previous Period</th>--}}
{{--                        <th class="th_3">Units PER ORDER<br/>Change</th>--}}
                        <th class="th_4">AVG.SALES<br/> PER UNIT</th>
{{--                        <th class="th_4">AVG.SALES PER UNIT<br/>Previous Period</th>--}}
{{--                        <th class="th_4">AVG.SALES PER UNIT<br/>Change</th>--}}
                        <th>AFN_SELLABLE</th>
                        <th>AFN_RESERVED</th>
                        <th>AFN_TRANSFER</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 50, // default record count per page
            "lengthMenu": [
                [10, 20,50],
                [10, 20,50] // change per page values here
            ],
            // pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'image',name:'image'},
                {data: 'title',name:'title'},
                {data: 'asin',name:'asin'},
                {data: 'sales',name:'sales'},
                // {data: 'sales_1',name:'sales_1'},
                // {data: 'sales_2',name:'sales_2'},
                // {data: 'sales_change',name:'sales_change'},
                {data: 'units',name:'units'},
                // {data: 'units_1',name:'units_1'},
                // {data: 'units_2',name:'units_2'},
                // {data: 'units_change',name:'units_change'},
                {data: 'orders',name:'orders'},
                // {data: 'orders_1',name:'orders_1'},
                // {data: 'orders_2',name:'orders_2'},
                // {data: 'orders_change',name:'orders_change'},
                {data: 'units_per_order',name:'units_per_order'},
                // {data: 'units_per_order_1',name:'units_per_order_1'},
                // {data: 'units_per_order_2',name:'units_per_order_2'},
                // {data: 'units_per_order_change',name:'units_per_order_change'},
                {data: 'sales_per_unit',name:'sales_per_unit'},
                // {data: 'sales_per_unit_1',name:'sales_per_unit_1'},
                // {data: 'sales_per_unit_2',name:'sales_per_unit_2'},
                // {data: 'sales_per_unit_change',name:'sales_per_unit_change'},
                {data: 'afn_sellable',name:'afn_sellable'},
                {data: 'afn_reserved',name:'afn_reserved'},
                {data: 'afn_transfer',name:'afn_transfer'},
            ],
            "createdRow": function( row, data, dataIndex ) {
                    $(row).children('td').eq(1).attr('style', 'text-align: left;')
                    $(row).children('td').eq(3).attr('style', 'text-align: right;')
                    $(row).children('td').eq(4).attr('style', 'text-align: right;')
                    $(row).children('td').eq(5).attr('style', 'text-align: right;')
                    $(row).children('td').eq(6).attr('style', 'text-align: right;')
                    $(row).children('td').eq(7).attr('style', 'text-align: right;')
            },

            ajax: {
                type: 'POST',
                url: '/ccp/salesboard/list',
                data:  {search: $("#search-form").serialize()}
            }
        })

        $('.date-search .date_type').click(function(){
            $('.date-search .date_type').removeClass('active');
            $(this).addClass('active');
            var value = $(this).attr('data-value');
            $('input[name="date_type"]').val(value);
            $.ajax({
                type: 'post',
                url: '/ccp/salesboard/showTotal',
                data: {search_data:$("#search-form").serialize()},
                dataType:'json',
                success: function(res) {
                    $('.total-data-table .second .sales').text(res.period_1_sales);
                    $('.total-data-table .second .units').text(res.period_1_units);
                    $('.total-data-table .second .orders').text(res.period_1_orders);
                    $('.total-data-table .second .units_per_order').text(res.period_1_units_per_order);
                    $('.total-data-table .second .sales_per_unit').text(res.period_1_sales_per_unit);

                    $('.total-data-table .third .sales').text(res.period_2_sales);
                    $('.total-data-table .third .units').text(res.period_2_units);
                    $('.total-data-table .third .orders').text(res.period_2_orders);
                    $('.total-data-table .third .units_per_order').text(res.period_2_units_per_order);
                    $('.total-data-table .third .sales_per_unit').text(res.period_2_sales_per_unit);

                    $('.total-data-table .sales_change').html(res.sales_change);
                    $('.total-data-table .units_change').html(res.units_change);
                    $('.total-data-table .orders_change').html(res.orders_change);
                    $('.total-data-table .units_per_order_change').html(res.units_per_order_change);
                    $('.total-data-table .sales_per_unit_change').html(res.sales_per_unit_change);
                    $('.total-data-table .danwei').text(res.danwei);
                }
            });
            //改变下面表格的数据内容,睡眠0.5秒,确保asin_price里面已经插入数据
            setTimeout(function(){
                dtapi = $('#datatable').dataTable().api();
                dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                dtapi.ajax.reload();
            },500)
        })

        //点击上面的搜索
        $('#search_top').click(function(){
            $(".date-search .active").trigger("click");
            return false;
        })
        //点击下面的搜索只改变下面表格的数据
        $('#search_table').click(function(){
            $('.search_asin').val($('#asin').val().trim());//asin赋值到搜索表单中
            //改变下面表格的数据内容
            dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtapi.ajax.reload();
            return false;
        })

        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            $.ajax({
                type: 'post',
                url: '/ccp/salesboard/showAccountBySite',
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
            getAccountBySite(); //触发当前选的站点得到该站点所有的账号
            $(".date-search .active").trigger("click");
        })
    </script>

@endsection