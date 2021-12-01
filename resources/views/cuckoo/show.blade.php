@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Cuckoo']])
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
                            <span class="input-group-addon">Site</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" onchange="getAccountBySite()" name="site">
                                @foreach($site as $value)
                                    <option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                            <select class="btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">StartDate From</span>
                            <input  class="form-control search_date"  value="{!! $startDateFrom !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="startDate_from" name="startDate_from"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">StartDate To</span>
                            <input  class="form-control search_date"  value="{!! $startDateTo !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="startDate_to" name="startDate_to"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">EndDate From</span>
                            <input  class="form-control search_date"  value="{!! $endDateFrom !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="endDate_from" name="endDate_from"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">EndDate To</span>
                            <input  class="form-control search_date"  value="{!! $endDateTo !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="endDate_to" name="endDate_to"/>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_table" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div style="margin-top:110px;">
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>Asin</th>
                        <th>Type</th>
                        <th>Site</th>
                        <th>Account Name</th>
                        <th>Min Price</th>
                        <th>Max Price</th>
                        <th>Customers</th>
                        <th>Discount Type</th>
                        <th>Discount</th>
                        <th>Save</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
{{--                        <th>Create Date</th>--}}
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        //日期控件初始化
        $('.search_date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 10, // default record count per page
            "lengthMenu": [
                [10, 20,50,],
                [10, 20,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'asin',name:'asin'},
                {data: 'type',name:'type'},
                {data: 'site',name:'site'},
                {data: 'account_name',name:'account_name'},
                {data: 'minPrice',name:'minPrice'},
                {data: 'maxPrice',name:'maxPrice'},
                {data: 'customers',name:'customers'},
                {data: 'discountType',name:'discountType'},
                {data: 'discount',name:'discount'},
                {data: 'save',name:'save'},
                {data: 'budget',name:'budget'},
                {data: 'status',name:'status'},
                {data: 'startDate',name:'startDate'},
                {data: 'endDate',name:'endDate'},
                // {data: 'created_at',name:'created_at'},
                {data: 'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/cuckoo/showList',
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

        $(function(){
            getAccountBySite()//触发当前选的站点得到该站点所有的账号
            $("#search_table").trigger("click");
        })
    </script>
@endsection