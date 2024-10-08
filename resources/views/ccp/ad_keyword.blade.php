@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['ccp ad keyword dashboard']])
@endsection
<style>
    .total-data-table{
        width:50%;
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

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Type</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="type" name="type">
                                @foreach($type as $key=>$value)
                                    <option value="{{ $key }}">{{ $value }}</option>
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
                    @permission('ccp-ad-keyword-export')
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
                    <td>COST</td>
                    <td>SALES</td>
                </tr>
                <tr class="weight second">
                    <td><span class="cost">0</span><span class="danwei"></span></td>
                    <td><span class="sales">0</span>&nbsp;<span class="danwei"></span></td>
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
            <table class="table table-striped table-bordered" id="datatable">
                <thead>
                    <tr>
                        <th>KEYWORD TEXT</th>
                        <th>MATCH TYPE</th>
                        <th>STATE</th>
                        <th>AD COST</th>
                        <th>SALES</th>
                        <th>ORDERS</th>
                        <th>ACOS</th>
                        <th>IMPRESSIONS</th>
                        <th>CLICKS</th>
                        <th>CTR</th>
                        <th>CPC</th>
                        <th>CR</th>
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
                    {data: 'keyword_text',name:'keyword_text'},
                    {data: 'match_type',name:'match_type'},
                    {data: 'state',name:'state'},
                    {data: 'cost',name:'cost'},
                    {data: 'sales',name:'sales'},
                    {data: 'orders',name:'orders'},
                    {data: 'acos',name:'acos'},
                    {data: 'impressions',name:'impressions'},
                    {data: 'clicks',name:'clicks'},
                    {data: 'ctr',name:'ctr'},
                    {data: 'cpc',name:'cpc'},
                    {data: 'cr',name:'cr'},
                ],
                ajax: {
                    type: 'POST',
                    url: '/ccp/adKeyword/list',
                    data:  {search: $("#search-form").serialize()}
                }
            })

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
            $('input[name="start_date"]').val($('input[name="from_date"]').val());
            $('input[name="end_date"]').val($('input[name="to_date"]').val());
            $.ajax({
                type: 'post',
                url: '/ccp/adKeyword/showTotal',
                data: {search_data:$("#search-form").serialize()},
                dataType:'json',
                success: function(res) {
                    $('.total-data-table .sales').text(res.sales);
                    $('.total-data-table .cost').text(res.cost);
                    $('.total-data-table .danwei').text(res.danwei);
                }
            });
            //改变下面表格的数据内容
            dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtapi.ajax.reload();

            return false;
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
            location.href='/ccp/adKeyword/export?'+search+'&account='+accountid;
            return false;
        })
        times = 1;
        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            getDateBySite();
            $.ajax({
                type: 'post',
                url: '/showAccountBySite',
                data: {marketplaceid:marketplaceid,field:'mws_seller_id'},
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
            // var timeType = $('#timeType option:selected').val();//选择的时间类型，是当地站点时间还是北京时间，timeType
            var today = new Date();
            // if(timeType==1){//站点本地时间的时候，取出当前选中站点今天的日期
                var date = $('#site option:selected').attr('data-date');
                today = new Date(date);
            // }
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
            $("#search_top").trigger("click");
        })
    </script>

@endsection