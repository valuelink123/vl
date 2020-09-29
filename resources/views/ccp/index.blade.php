@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['ccp dashboard']])
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
    #datatable th{
        text-align:center;
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
                    <td>SALES</td>
                    <td>UNITS</td>
                    <td>ORDERS</td>
                    <td>AVG.PRICE</td>
                </tr>
                <tr class="weight second">
                    <td><span class="sales">0</span>&nbsp;<span class="danwei"></span></td>
                    <td><span class="units">0</span></td>
                    <td><span class="orders">0</span></td>
                    <td><span class="avgPrice">0</span>&nbsp;<span class="danwei"></span></td>
                </tr>
                <tr class="third">
                    <td>NET REVENUE:<span class="revenue">0</span>&nbsp;<span class="danwei"></span></td>
                    <td>FULL:<span class="unitsFull">0</span> | PROMO:<span class="unitsPromo">0</span></td>
                    <td>FULL:<span class="ordersFull">0</span>|PROMO:<span class="ordersPromo">0</span></td>
                    <td></td>
                </tr>
            </table>

            <table class="date-search date-search-table">
                <tr>
                    <td class="date_type active" data-value="1">TODAY</td>
                    <td class="date_type" data-value="2">YESTERDAY</td>
                    <td class="date_type" data-value="3">LAST 7 DAYS</td>
                    <td class="date_type" data-value="4">WEEK TO DATE</td>
                    <td class="date_type" data-value="5">LAST 30 DAYS</td>
                    <td class="date_type" data-value="6">MONTH TO DATE</td>
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
                        <th>PRODUCT</th>
                        <th>ASIN</th>
                        <th>SALES</th>
                        <th>UNITS</th>
                        <th>ORDERS</th>
                        <th>AVG.UNITS PER DAY</th>
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
                "pageLength": 10, // default record count per page
                "lengthMenu": [
                    [10, 20,50,],
                    [10, 20,50,] // change per page values here
                ],
                // pagingType: 'bootstrap_extended',
                processing: true,
                columns: [
                    {data: 'image',name:'image'},
                    {data: 'title',name:'title'},
                    {data: 'asin',name:'asin'},
                    {data: 'sales',name:'sales'},
                    {data: 'units',name:'units'},
                    {data: 'orders',name:'orders'},
                    {data: 'avg_units',name:'avg_units'},
                ],
                ajax: {
                    type: 'POST',
                    url: '/ccp/list',
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
                url: '/ccp/showTotal',
                data: {search_data:$("#search-form").serialize()},
                dataType:'json',
                success: function(res) {
                    $('.total-data-table .sales').text(res.sales);
                    $('.total-data-table .units').text(res.units);
                    $('.total-data-table .orders').text(res.orders);
                    $('.total-data-table .avgPrice').text(res.avgPrice);
                    $('.total-data-table .revenue').text(res.revenue);
                    $('.total-data-table .unitsFull').text(res.unitsFull);
                    $('.total-data-table .unitsPromo').text(res.unitsPromo);
                    $('.total-data-table .ordersFull').text(res.ordersFull);
                    $('.total-data-table .ordersPromo').text(res.ordersPromo);
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
            $('.search_asin').val($('#asin').val());//asin赋值到搜索表单中
            //改变下面表格的数据内容
            dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtapi.ajax.reload();
            return false;
        })
        times = 1;
        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            $.ajax({
                type: 'post',
                url: '/ccp/showAccountBySite',
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
            // 根据搜索时间区域，调用点击事件，展示上部分的统计数据
            $(".date-search .active").trigger("click");
        })
    </script>

@endsection