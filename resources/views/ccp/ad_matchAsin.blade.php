@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['ccp adMatchAsin dashboard']])
@endsection

@section('content')
    @include('frank.common')
    <div class="row">
        <div class="top portlet light" style="margin-left:-25px;">
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
                    </div>
                    <div class="col-md-3">
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="input-group" id="campaign-div">
                            <span class="input-group-addon">Campaign</span>
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
                {data: 'campaign',name:'campaign'},
                {data: 'ad_group',name:'ad_group'},
                {data: 'ad_type',name:'ad_type'},
                {data: 'asin',name:'asin'},
                {data: 'seller',name:'seller'},
                {data: 'sku',name:'sku'},
                {data: 'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/ccp/adMatchAsin/list',
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