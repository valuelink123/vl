@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['ROI Performance']])
@endsection
@section('content')
    @include('frank.common')
    <style>
        table th{
            text-align:center;
        }
        table.dataTable thead th, table.dataTable thead td {
            padding: 10px 0px !important;
        }
        table.dataTable tbody td {
            padding: 8px 5px !important;
        }
        .table td, .table th {
            font-size: 12px !important;
        }
        .table tr td{
            word-wrap:break-word !important;
            /*word-break:break-all !important;*/
            white-space:nowrap !important;
        }
    </style>
    <div class="row">
        <div class="top portlet light">
            <div class="search_table" style="margin-left: -15px;margin-bottom: 50px;">
                <form id="search-form">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From Date</span>
                            <input  class="form-control"  value="{!! $data['fromDate'] !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="from_date" name="from_date"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">To Date</span>
                            <input  class="form-control"  value="{!! $data['toDate'] !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="to_date" name="to_date"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select name="site" id="site" style="width:205px; height:34px">
                                <option value="">select</option>
                                @foreach ($sites as $site)
                                    <option value="{{$site}}">{{$site}}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">type</span>
                            <select name="type" id="type" style="width:205px; height:34px">
                                <option value="">select</option>
                                @foreach ($type as $kt=>$kv)
                                    <option value="{{$kt}}">{{$kv}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Sku</span>
                            <input  class="form-control"  value="" id="sku" name="sku"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">ROI ID</span>
                            <input  class="form-control"  value="" id="roi_id" name="roi_id"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">上线时间开始</span>
                            <input  class="form-control"  value="" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="estimated_launch_time_from" name="estimated_launch_time_from"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">上线时间结束</span>
                            <input  class="form-control"  value="" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="estimated_launch_time_to" name="estimated_launch_time_to"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <div class="btn-group pull-right" >
                                <button id="search_table" class="btn sbold blue">Search</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @permission('roi-performance-export')
            <div class="btn-group " style="float:right;margin-top:20px;">
                <div class="col-md-12">
                    <div class="col-md-2">
                        <a  data-toggle="modal" href="/roiPerformance/export" target="_blank">
                            <button id="export" class="btn sbold blue"> Export
                                <i class="fa"></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>
            @endpermission
            <div class="btn-group" style="margin-top:10px;margin-left:15px;">
                <button id="batch-calculate" class="btn btn-success btn-sm sbold green-meadow">批量计算
                </button>
            </div>

            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                            <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>
                        </th>
                        <th>ROIID</th>
                        <th>上线时间</th>
                        <th>SKU</th>
                        <th>描述</th>
                        <th>站点</th>
                        <th>类型</th>
                        <th>第1月</th>
                        <th>第2月</th>
                        <th>第3月</th>
                        <th>第4月</th>
                        <th>第5月</th>
                        <th>第6月</th>
                        <th>第7月</th>
                        <th>第8月</th>
                        <th>第9月</th>
                        <th>第10月</th>
                        <th>第11月</th>
                        <th>第12月</th>
                        <th>总数</th>
{{--                        <th>创建时间</th>--}}
{{--                        <th>更新时间</th>--}}
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div style="display:none;" id="hidden-roiid" data-id=""></div>
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
        $('#estimated_launch_time_from').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });
        $('#estimated_launch_time_to').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 20, // default record count per page
            "lengthMenu": [
                [20, 30,50,],
                [20, 30,50,] // change per page values here
            ],
            processing: true,
            select: {
                style: 'os',
                info: true, // info N rows selected
                // blurable: true, // unselect on blur
                selector: 'td:first-child', // 指定第一列可以点击选中
            },
            columns: [
                {
                    width: "1px",
                    orderable: false,
                    defaultContent: '',
                    className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                },
                {data: 'roi_id',name:'roi_id'},
                {data: 'estimated_launch_time',name:'estimated_launch_time'},
                {data: 'sku',name:'sku'},
                {data: 'product_name',name:'product_name'},
                {data: 'site',name:'site'},
                {data: 'type',name:'type'},
                {data:'value_month_1',name:'value_month_1'},
                {data:'value_month_2',name:'value_month_2'},
                {data:'value_month_3',name:'value_month_3'},
                {data:'value_month_4',name:'value_month_4'},
                {data:'value_month_5',name:'value_month_5'},
                {data:'value_month_6',name:'value_month_6'},
                {data:'value_month_7',name:'value_month_7'},
                {data:'value_month_8',name:'value_month_8'},
                {data:'value_month_9',name:'value_month_9'},
                {data:'value_month_10',name:'value_month_10'},
                {data:'value_month_11',name:'value_month_11'},
                {data:'value_month_12',name:'value_month_12'},
                {data:'value_total',name:'value_total'},
                // {data:'created_at',name:'created_at'},
                // {data:'updated_at',name:'updated_at'},
                {data:'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/roiPerformance/list',
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

        $("#export").click(function(){
            var search = $("#search-form").serialize();
            location.href='/roiPerformance/export?'+search;
        });
        //点击计算按钮，获取roiid,并触发计算函数
        $("#datatable").delegate(".calculate","click",function(){
            //计算
            var roi_id = $(this).attr('data-id');
            $('#hidden-roiid').attr('data-id',roi_id);
            calculateAjax();
        })
        //ajax计算结果，计算结果，并把结果存到数据库中
        function calculateAjax(){
            roi_id = $('#hidden-roiid').attr('data-id');
            $.ajax({
                type: 'post',
                url: '/roiPerformance/calculate',
                data: {roi_id:roi_id},
                dataType:'json',
                success: function(res) {
                    alert(res.msg);
                    if(res.status==1){
                        //重新加载列表
                        dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                        dtApi.ajax.reload();
                    }

                }
            });


            return false;
        }

        //批量计算
        $('#batch-calculate').click(function(){
            let selectedRows = dtApi.rows({selected: true})
            let ctgRows = selectedRows.data().toArray().map(obj => [obj.roi_id])
            if (!ctgRows.length) {
                toastr.error('Please select some rows first !')
                return
            }
            var roi_id = ctgRows.join(';');
            $('#hidden-roiid').attr('data-id',roi_id);
            calculateAjax();

        })

        $(function(){
            $("#search_table").trigger("click");
        })
    </script>
@endsection