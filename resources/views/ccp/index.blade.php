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
        width:100%;
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
                <input type="hidden" name="start_date" value="">
                <input type="hidden" name="end_date" value="">
                <input type="hidden" class="search_asin" name="asin" value="">
                <div class="search portlet light">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" onchange="getAccountBySite()" name="site">
                                @foreach($site as $value)
                                    <option data-date="{!! $siteDate[$value->marketplaceid] !!}" value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
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
                    <div class="col-md-1">
                        <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select  style="width:100%;height:35px;" id="bg" name="bg">
                                <option value="">Select</option>
                                @foreach($bgs as $value)
                                    <option value="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="input-group">
                            <span class="input-group-addon">BU</span>
                            <select  style="width:100%;height:35px;" id="bu" name="bu">
                                <option value="">Select</option>
                                @foreach($bus as $value)
                                    <option value="{{$value}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Time Type</span>
                            <select  style="width:100%;height:35px;" id="timeType" name="timeType" onchange="getDateBySite()">
                                <option value="1">Local Time</option>
                                <option value="0">BeiJing Time</option>
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
                    @permission('ccp-export')
                    <div class="col-md-1">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="export_table" class="btn sbold blue">Export</button>
                            </div>
                        </div>
                    </div>
                    @endpermission
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
            <div class="search-date light portlet">
                <div class="col-md-6" style="margin-left: -28px;">
                <table class="date-search date-search-table">
                    <tr>
                        <td class="date_type active" data-value="1">TODAY</td>
                        <td class="date_type" data-value="2">YESTERDAY</td>
                        <td class="date_type" data-value="3">LAST 3 DAYS</td>
                        <td class="date_type" data-value="4">LAST 7 DAYS</td>
                        <td class="date_type" data-value="5">LAST 15 DAYS</td>
                        <td class="date_type" data-value="6">LAST 30 DAYS</td>
                        <td class="date_type" data-value="7">Other</td>
                    </tr>
                </table>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">From Date</span>
                        <input  class="form-control"  value="{!! $date !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="from_date" name="from_date" disabled="disabled"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">Date</span>
                        <input  class="form-control"  value="{!! $date !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="to_date" name="to_date" disabled="disabled"/>
                    </div>
                </div>
                <div class="col-md-1 confirm" style="display:none;">
                    <div class="input-group">
                        <div class="btn-group pull-right" >
                            <button id="confirm" class="btn sbold green">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="top portlet light">
            <div class="search_table" style="margin-bottom:50px;margin-left:-15px;">
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">Asin/SKU</span>
                        <input class="form-control" value="" id="asin" placeholder="Asin/SKU"/>
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
                        <th>Item No.</th>
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
        //日期控件初始化
        $('#to_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#from_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });


        $('.date-search .date_type').click(function(){
            $('.date-search .date_type').removeClass('active');
            $(this).addClass('active');
            value = $(this).attr('data-value');
            getDateBySite();//通过选择的站点和时间类型和得到对应的正确的时间范围
            $('input[name="date_type"]').val(value);
            $('.confirm').css('display','none');
            //date_type的值不为7的时候，隐藏确认键，直接触发搜索，让其显示结果，
            // 为7的时候显示确认键，需要点击右侧的确认键，让其确认时间才触发搜索，让其显示结果
            if(value==7){
                $('.confirm').css('display','block');
            }else{
                $("#search_top").trigger("click");
            }
            return true;
        })
        $('.confirm').click(function(){
            $("#search_top").trigger("click");
        })
        //点击上面的搜索
        $('#search_top').click(function(){
            $('.total-data-table .sales').text('-');
            $('.total-data-table .units').text('-');
            $('.total-data-table .orders').text('-');
            $('.total-data-table .avgPrice').text('-');
            $('.total-data-table .revenue').text('-');
            $('.total-data-table .unitsFull').text('-');
            $('.total-data-table .unitsPromo').text('-');
            $('.total-data-table .ordersFull').text('-');
            $('.total-data-table .ordersPromo').text('-');
            $('.total-data-table .danwei').text('-');
            $('input[name="start_date"]').val($('input[name="from_date"]').val());
            $('input[name="end_date"]').val($('input[name="to_date"]').val());
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
            dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtapi.ajax.reload();
            
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
            getDateBySite();
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
		//通过选择的站点和时间类型和得到对应的正确的时间范围
        function getDateBySite()
		{
		    var value = $('.date-search .active').attr('data-value');//选中时间区间的菜单
            var timeType = $('#timeType option:selected').val();//选择的时间类型，是当地站点时间还是北京时间，timeType
            var today = new Date();
            if(timeType==1){//站点本地时间的时候，取出当前选中站点今天的日期
                var date = $('#site option:selected').attr('data-date');
                today = new Date(date);
            }
            var oneday = 1000*60*60*24;//一天时间的秒数
            var from_time = today;
            var to_time = today;
            $('input[name="from_date"]').attr("disabled", "disabled");
            $('input[name="to_date"]').attr("disabled", "disabled");
            if(value==2){//选的是昨天,开始和结束都是昨天
                from_time = to_time = new Date(today- oneday);
            }else if(value==3){//最近3天，开始是前天，结束是今天
                from_time = new Date(today- oneday*2);
            }else if(value==4){//最近7天
                from_time = new Date(today- oneday*6);
            }else if(value==5){//最近15天
                from_time = new Date(today- oneday*14);
            }else if(value==6){//最近30天
                from_time = new Date(today- oneday*29);
            }if(value==7){
                $('input[name="from_date"]').removeAttr("disabled");
                $('input[name="to_date"]').removeAttr("disabled");
                return true;//选中的是其他的时候,不更改开始时间和结束时间
            }
            var from_month = from_time.getMonth() + 1;
            var to_month = to_time.getMonth() + 1;
            from_month = from_month<10 ? '0'+from_month : from_month;
            to_month = to_month<10 ? '0'+to_month : to_month;
            var from_day = from_time.getDate();
            var to_day = to_time.getDate();
            from_day = from_day<10 ? '0'+from_day : from_day;
            to_day = to_day<10 ? '0'+to_day : to_day;
            var from_date = from_time.getFullYear() + '-'+ from_month + '-'+ from_day;
            var to_date = to_time.getFullYear() + '-'+ to_month+ '-'+ to_day;
            $('input[name="from_date"]').val(from_date);
            $('input[name="to_date"]').val(to_date);
            $('input[name="start_date"]').val(from_date);
            $('input[name="end_date"]').val(to_date);
            return true;
		}

        $(function(){
            getAccountBySite()//触发当前选的站点得到该站点所有的账号
            // 根据搜索时间区域，调用点击事件，展示上部分的统计数据
            $('input[name="start_date"]').val($('input[name="from_date"]').val());
            $('input[name="end_date"]').val($('input[name="to_date"]').val());
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

            $('#datatable').dataTable({
                searching: false,//关闭搜索
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                ordering:false,
                "pageLength": 50, // default record count per page
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
                    {data: 'item_no',name:'item_no'},
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

            
            //$("#search_top").trigger("click");
        })

        //点击导出
        $('#export_table').click(function(){
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
            location.href='/ccp/export?'+search+'&account='+accountid;
            return false;
        })
    </script>
@endsection