@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Mcf Order List']])
@endsection
@section('content')
    @include('frank.common')
    <style>
        table th{
            text-align:center;
        }
        .table td, .table th {
            font-size: 12px !important;
        }
        table.dataTable thead th, table.dataTable thead td {
            padding: 10px 0px !important;
        }
        table.dataTable tbody td {
            padding: 8px 0px !important;
        }
        .table td, .table th {
            font-size: 12px !important;
        }
        .table{
            table-layout:fixed;
        }
        .table tr .data_seller_sku{
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .table tr .data_seller_sku{
            width: 230px !important
        }
        .table tr .data_country{
            width: 77px !important
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
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                            <select class="mt-multiselect btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">
                                @foreach($data['account'] as $value)
                                    <option value="{{$value['id']}}">{{$value['label']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Order ID</span>
                            <input  class="form-control"  value="" id="amazon_order_id" name="amazon_order_id"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Status</span>
                            <select class="form-control btn btn-default" name="status" id="status">
                                <option value="">SELECT</option>
                                @foreach(getMcfOrderStatus() as $key=>$val)
                                    <option value="{{$val}}">{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Seller SKU</span>
                            <input  class="form-control"  value="" id="seller_sku" name="seller_sku"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Customer Name</span>
                            <input  class="form-control"  value="" id="customer_name" name="customer_name"/>
                        </div>
                        <br>

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
            {{--            @permission('refund-export')--}}
            <div class="btn-group " style="float:right;margin-top:20px;">
                <div class="col-md-12">
                    <div class="col-md-2">
                        <a  data-toggle="modal" href="/McfOrderList/export" target="_blank">
                            <button id="export" class="btn sbold blue"> Export
                                <i class="fa "></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>
            {{--            @endpermission--}}

            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Amazon Order ID</th>
                        <th>Date</th>
                        <th>Seller SKU</th>
                        <th>Order Status</th>
                        <th>Customer Name</th>
                        <th>Country</th>
                        <th>Shipping Speed</th>
{{--                        <th>Tracking No.</th>--}}
{{--                        <th>Carrier Code</th>--}}
                        <th>Settlement ID</th>
                        <th>Settlement Date</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
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

        // $('#settlement_date').datepicker({
        //     rtl: App.isRTL(),
        //     autoclose: true
        // });

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 15, // default record count per page
            "lengthMenu": [
                [15, 30,50,],
                [15, 30,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'id',name:'id'},
                {data: 'account',name:'account'},
                {data: 'amazon_order_id',name:'amazon_order_id'},
                {data: 'date',name:'date'},
                {data: 'seller_sku',name:'seller_sku',class:'data_seller_sku'},
                {data: 'order_status',name:'order_status'},
                {data: 'customer_name',name:'customer_name'},
                {data: 'country',name:'country',class:'data_country'},
                {data: 'shipping_speed',name:'shipping_speed'},
                // {data: 'tracking_no',name:'tracking_no'},
                // {data: 'carrier_code',name:'carrier_code'},
                {data: 'settlement_id',name:'settlement_id'},
                {data: 'settlement_date',name:'settlement_date'},
            ],
            ajax: {
                type: 'POST',
                url: '/McfOrderList/list',
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
            location.href='/McfOrderList/export?'+search+'&account='+accountid;
        });

        $(function(){
            $("#search_table").trigger("click");
        })
    </script>
@endsection