@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['inventory cycle count dashboard']])
@endsection
<style>
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

    .date-search-table td{
        border:1px solid #676464;
        padding: 7px 12px;
        margin: 11px 20px 0px 0px;
        text-align:center;
    }

    #datatable th{
        text-align:center;
    }
    .btn-group a{
        color: #FFFFFF;
    }
    .btn-group a:hover{
        color: #FFFFFF;
    }

</style>
@section('content')
    @include('frank.common')
    <div class="top portlet light" >
        <div class="row">
            <div class="col-md-12" style="padding: 0px;margin-bottom: 30px;">
                @permission('inventory-cycle-count-export')
                <div class="col-md-1">
                <button id="export" class="btn sbold blue"> 导出
                    <i class="fa fa-download"></i>
                </button>
                </div>
                @endpermission

                @permission('inventory-cycle-count-addSku')
                <div class="col-md-2">
                    <div class="btn-group ">
                        <div class="col-md-6"  >
                            <button type="submit" class="btn blue" id="download_sku_number"><a href="{{ url('/inventoryCycleCount/downloadSku')}}" >企管下载模板</a></button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn blue" id="data_sku">导入sku</button>
                        </div>
                    </div>
                </div>
                @endpermission

                @permission('inventory-cycle-count-addAccountNumber')
                <div class="col-md-2">
                    <div class="btn-group ">
                        <div class="col-md-6"  >
                            <button type="submit" class="btn blue" id="download_account_number"><a href="{{ url('/inventoryCycleCount/downloadAccountNumber')}}">财务下载模板</a></button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn blue" id="data_account_number">导入账面数量</button>
                        </div>
                    </div>

                </div>
                <div class="col-md-3">
                    <div class="btn-group ">
                        <div class="col-md-6">
                            <button type="submit" class="btn blue" id="download_dispose_after_account_number"><a href="{{ url('/inventoryCycleCount/downloadDisposeAfterAccountNumber')}}">财务下载处理后模板</a></button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn blue" id="data_dispose_after_account_number">导入处理后数量</button>
                        </div>
                    </div>
                </div>
                @endpermission

                @permission('inventory-cycle-count-addActualNumber')
                <div class="col-md-2">
                    <div class="btn-group ">
                        <div class="col-md-6"  >
                            <button type="submit" class="btn blue" id="download_actual_number"><a href="{{ url('/inventoryCycleCount/downloadActualNumber')}}">物流下载模板</a></button>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn blue" id="data_actual_number">导入真实数量</button>
                        </div>
                    </div>
                </div>
                @endpermission
            </div>
            <form id="search-form" >
                <div class="search portlet light">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From Date</span>
                            <input  class="form-control"  value="{!! $start_date !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="from_date" name="from_date"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">To Date</span>
                            <input  class="form-control"  value="{!! $end_date !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="to_date" name="to_date"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon" style="height:34px">SKU</span>
                            <input type="text" id="sku" style="width:100%;height: 34px" name="sku" value="" placeholder="多个以分号分隔">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon" style="height:34px">工厂</span>
                            <input type="text" id="factory" style="width:100%;height: 34px" name="factory" value="" placeholder="多个以分号分隔">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">状态</span>
                            <select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="status" name="status">
                                <option value="">请选择</option>
                                @foreach($status as $key=>$value)
                                    <option value="{{ $key }}">{{ $value }}</option>
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

        <div class="row" style="margin-top:50px;">
            <div class="col-md-2">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm green-meadow">批量操作</button>
                    <button type="button" class="btn btn-sm green-meadow dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><button type="submit" class="btn btn-sm red-sunglo batch-edit-action" value="2">完成盘点</button></li>
                        <li class="divider"> </li>
                        <li><button type="submit" class="btn btn-sm yellow-crusta batch-edit-action" value="3">完成差异处理</button></li>
                        <li class="divider"> </li>
                        <li><button type="submit" class="btn btn-sm blue-hoki batch-edit-action" value="4">确认</button></li>
                        <li class="divider"> </li>
                    </ul>
                </div>
            </div>
            <table class="table table-striped table-bordered" id="datatable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all"/></th>
                        <th>盘点日期</th>
                        <th>SKU</th>
                        <th>工厂</th>
                        <th>库位</th>
                        <th>SAP库存数量</th>
                        <th>账面数量</th>
                        <th>未过账数量</th>
                        <th>实物数量</th>
                        <th>最初差异数量</th>
                        <th>最初差异率</th>
                        <th>处理后SAP数量</th>
                        <th>处理后账面数量</th>
                        <th>处理后未过账数量</th>
                        <th>处理后差异数量</th>
                        <th>处理后差异率</th>
                        <th>状态</th>
                        <th>差异原因</th>
                        <th>完成时间</th>
                        <th>确认时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="import-add-data" style="display:none;">
            <form id="import-form" method="post" action="" enctype="multipart/form-data">
                    <div class="col-md-10">
                        {{ csrf_field() }}
                        <input type="file" name="import_File"  style="width: 90%;"/>
                    </div>
                <br/>
                <br/>
                <div class="col-md-2">
                    <button type="submit" class="btn blue">导入</button>
                </div>
            </form>
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
        $('#from_date_download').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#to_date_download').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });
        // var datatableApi = $('#datatable').dataTable().api();

        $('#datatable').dataTable({
                searching: false,//关闭搜索
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                ordering:false,
                "pageLength": 20, // default record count per page
                "lengthMenu": [
                    [10, 20,50,-1],
                    [10, 20,50,"All"] // change per page values here
                ],
                "scrollX": true,
                // fixedColumns:   {
                //     leftColumns:4
                // },

                // pagingType: 'bootstrap_extended',
                processing: true,
                columns: [
                    {
                        width: "1px",
                        orderable: false,
                        defaultContent: '',
                        // className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                        render:function(data,type,row,meta){
                            return '<input type="checkbox" class="select-checkbox" value="'+row.id+'">';
                        }
                    },
                    // {data: 'date',name:'date'},
                    {data: 'date',name:'date'},
                    {data: 'sku',name:'sku'},
                    {data: 'factory',name:'factory'},
                    {data: 'location',name:'location'},
                    {data: 'dispose_before_number',name:'dispose_before_number'},//处理前SAP库存数量
                    {data: 'account_number',name:'account_number'},//账面数量
                    {data: 'notaccount_number',name:'notaccount_number'},//未过账数量
                    {data: 'actual_number',name:'actual_number'},//实物数量
                    {data: 'difference_before_number',name:'difference_before_number'},//最初差异数量
                    {data: 'difference_before_rate',name:'difference_before_rate'},//最初差异率
                    {data: 'dispose_after_number',name:'dispose_after_number'},//处理后SAP库存数量
                    {data: 'dispose_after_account_number',name:'dispose_after_account_number'},//处理后的账面数量
                    {data: 'dispose_after_notaccount_number',name:'dispose_after_notaccount_number'},//处理后未过账数量
                    {data: 'difference_after_number',name:'difference_after_number'},//处理后差异数量
                    {data: 'difference_after_rate',name:'difference_after_rate'},//处理后差异率
                    {data: 'status',name:'status'},
                    {data: 'reason',name:'reason'},
                    {data: 'dispose_time',name:'dispose_time'},
                    {data: 'confirm_time',name:'confirm_time'},
                    {data: 'action',name:'action'},
                ],
                ajax: {
                    type: 'POST',
                    url: '/inventoryCycleCount/list',
                    data:  {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g," "),true)}
                }
            })

        //企管部点击导入sku
        $('#data_sku').click(function(){
            $("#import-form").attr("action","inventoryCycleCount/importSku");
            art.dialog({
                id: 'art_add_sku',
                title: '导入sku',
                content: document.getElementById('import-form'),
                okVal: false,
                cancel: true,
                cancelVal:'Cancel'
            });
        })

        //点击导入真实数量数据
        $('#data_actual_number').click(function(){
            $("#import-form").attr("action","inventoryCycleCount/importActualNumber");
            art.dialog({
                id: 'art_add_actual_number',
                title: '导入真实数量',
                content: document.getElementById('import-form'),
                okVal: false,
                cancel: true,
                cancelVal:'Cancel'
            });
        })

        //点击导入账面数量数据
        $('#data_account_number').click(function(){
            $("#import-form").attr("action","inventoryCycleCount/importAccountNumber");
            art.dialog({
                id: 'art_add_account_number',
                title: '导入账面数量',
                content: document.getElementById('import-form'),
                okVal: false,
                cancel: true,
                cancelVal:'Cancel'
            });
        })
        //点击导入账面数量数据
        $('#data_dispose_after_account_number').click(function(){
            $("#import-form").attr("action","inventoryCycleCount/importDisposeAfterAccountNumber");
            art.dialog({
                id: 'art_add_dispose_after_account_number',
                title: '导入处理后的账面数量',
                content: document.getElementById('import-form'),
                okVal: false,
                cancel: true,
                cancelVal:'Cancel'
            });
        })

        //点击完成盘点，把状态改为完成盘点状态
        $('table').on('click','.edit-action',function(){
            var id = $(this).parent().attr('data-id');
            var status = $(this).attr('data-after-status');
            $.ajax({
                type: 'post',
                url: '/inventoryCycleCount/edit',
                data: {status:status,id:id},
                dataType:'json',
                success: function(res) {
                    if(res.status>0){
                        $("#search_top").trigger("click");
                        alert('成功');
                    }else{
                        alert('失败');
                    }
                }
            });
        })

        //批量更新状态，batch-edit-action
        $('.batch-edit-action').click(function(){
            var id='';
            $(".select-checkbox").each(function () {
                if (this.checked == true) {
                    if ($(this).val()>0) {
                        id = id + ',' + $(this).val();
                    }
                }
            });
            console.log(id);
            var status = $(this).val();
            $.ajax({
                type: 'post',
                url: '/inventoryCycleCount/edit',
                data: {status:status,id:id},
                dataType:'json',
                success: function(res) {
                    if(res.status>0){
                        $("#search_top").trigger("click");
                        alert(res.msg);
                    }else{
                        alert(res.msg);
                    }
                }
            });
        })

        var dtapi = $('#datatable').dataTable().api();
        //点击上面的搜索
        $('#search_top').click(function(){
            //改变下面表格的数据内容
            // dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g," "),true)};
            dtapi.ajax.reload();
            return false;
        })
        //导出
        $("#export").click(function(){
            location.href='/inventoryCycleCount/export?='+decodeURIComponent($("#search-form").serialize().replace(/\+/g," "),true);
        });

        //点击列表中的单选
        $("#datatable").on('click', '.select-checkbox',function(){
            if ($(this).is(":checked") == false) {
                $("#select_all").prop("checked", false);
                $(this).parent().parent().removeClass('selected');
            } else {
                var flag = true;
                $(this).parent().parent().addClass('selected');
                $("#select_all").prop("checked", true);
                $(".select-checkbox").each(function () {
                    if (this.checked == false) {
                        $("#select_all").prop("checked", false);
                        flag = false;
                        return;
                    }
                });
            }
        });
        //点击表头上的全选
        $('#select_all').on('click',function(){
            if ($(this).is(":checked") == false) {
                //不选中
                $(".select-checkbox").prop("checked", false);
                $(".select-checkbox").parent().parent().removeClass('selected');

            }else{
                //选中
                $(".select-checkbox").prop("checked", true);
                $(".select-checkbox").parent().parent().addClass('selected');
            }
        })

        $(function(){
            // 根据搜索时间区域，调用点击搜索事件
            $("#search_top").trigger("click");
        })
    </script>

@endsection