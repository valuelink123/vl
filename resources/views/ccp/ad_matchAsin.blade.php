@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['ccp adMatchAsin dashboard']])
@endsection

@section('content')
    @include('frank.common')
    <div class="row">
        <div class="top portlet light" style="margin-left:-25px;height: 130px;">
            <form id="search-form" >
                <div class="search portlet light">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" onchange="getAccountBySite()" name="site">
                                @foreach($site as $value)
                                    <option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">ASIN</span>
                            <input class="form-control" value="" id="asin" placeholder="Asin" name="asin">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">SKU</span>
                            <input class="form-control" value="" id="sku" placeholder="sku" name="sku">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group" id="campaign-div">
                            <span class="input-group-addon">Campaign</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon">Campaign Name</span>
                            <input class="form-control" value="" id="campaign_name" placeholder="Campaign Name" name="campaign_name">
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_top" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
{{--                    <div class="col-md-1">--}}
{{--                        <a  href="/ccp/adMatchAsin/add" target="_blank">Add New</a>--}}
{{--                    </div>--}}
                    @permission('ccp-adMatchAsin-export')
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
    </div>

    <div class="row">
        <div class="top portlet light">
            <table class="table table-striped table-bordered" id="datatable">
                <thead>
                <tr>
                    <th>站点</th>
                    <th>店铺</th>
                    <th>Campaign</th>
                    <th>AD Group</th>
                    <th>AD Type</th>
                    <th>ASIN</th>
                    <th>销售员</th>
                    <th>SKU</th>
                    <th>操作</th>
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
            "pageLength": 30, // default record count per page
            "lengthMenu": [
                [30,50,],
                [30,50,] // change per page values here
            ],
            // pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'site',name:'site'},
                {data: 'account_name',name:'account_name'},
                {data: 'campaign_name',name:'campaig_name'},
                {data: 'group_name',name:'group_name'},
                {data: 'ad_type',name:'ad_type'},
                {data: 'asin',name:'asin'},
                {data: 'seller',name:'seller'},
                {data: 'sku',name:'sku'},
                {data: 'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/ccp/adMatchAsin/list',
                data:  {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true)}
            }
        })

        // 点击上面的搜索
        $('#search_top').click(function(){
            // 改变下面表格的数据内容
            dtapi = $('#datatable').dataTable().api();
            //输入框解决中文乱码
            dtapi.settings()[0].ajax.data = {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true)};
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
            //campaign的拼接
            var campaign = '';
            var vv = '';
            $("#campaign-div .active").each(function (index,value) {
                vv = $(this).find('input').val();
                if(campaign != ''){
                    campaign = campaign + ',' + vv
                }else{
                    campaign = campaign + vv
                }
            });
            location.href='/ccp/adMatchAsin/export?'+search+'&account='+accountid+'&campaign='+campaign;
            return false;
        })

        $(function(){
            getAccountBySite();
            // 根据搜索时间区域，调用点击事件，展示上部分的统计数据
            $("#search_top").trigger("click");
        })
        function getAccountBySite(){
            getAccountBySelectedSite();
            $("#account-div").trigger("change");
        }

        function del(id){
            message = '确定删除吗?';
            if(!confirm(message)){return false;};//点击取消，不更改
            $.ajax({
                type: 'post',
                url: '/ccp/adMatchAsin/delete',
                data: {id:id},
                dataType:'json',
                success: function(res) {
                    if(res.status>0){
                        alert('Success');
                        $("#search_top").trigger("click");
                    }else{
                        alert(res.msg);
                    }
                }
            });
        }

        $("#account-div").change(function(){
            getCampaignBySelectedAccount();
        });

    </script>

@endsection