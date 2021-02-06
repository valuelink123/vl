@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Amazon Settlement Detail']])
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
                            <span class="input-group-addon">From Date</span>
                            <input  class="form-control"  value="" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="from_date" name="from_date" placeholder="Purchase Date From"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Order ID</span>
                            <input  class="form-control"  value="" id="amazon_order_id" name="amazon_order_id"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon" title="Purchase Date To">To Date</span>
                            <input  class="form-control" placeholder="Purchase Date To"  value="" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="to_date" name="to_date"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Seller SKU</span>
                            <input  class="form-control"  value="" id="seller_sku" name="seller_sku"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group" id="account-div">
                            <span class="input-group-addon">Account</span>
                            <select class="mt-multiselect btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]" disabled="disabled">
                                @foreach($data['account'] as $value)
                                    <option value="{{$value['id']}}" @if($data['settlementInfo']['seller_account_id']==$value['id']) selected @endif>{{$value['label']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Currency</span>
                            <select class="form-control btn btn-default" name="currency" id="currency" disabled="disabled">
                                <option value="">select</option>
                                @foreach(getCurrency() as $key=>$val)
                                    <option value="{{$val}}" @if($data['settlementInfo']['currency']==$val) selected @endif>{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Settlement ID</span>
                            <input  class="form-control"  value="{{$data['settlementInfo']['settlement_id']}}" id="settlement_id" name="settlement_id" readonly="readonly">
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

            <div class="btn-group " style="float:right;margin-top:20px;">
                <div class="col-md-12">
                    <div class="col-md-2">
                        <a  data-toggle="modal" href="/settlement/detailExport" target="_blank">
                            <button id="export" class="btn sbold blue"> Export
                                <i class="fa "></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>


            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Settlement ID</th>
                        <th>Transaction Type</th>
                        <th>Amazon Order ID</th>
                        <th>Merchant Order ID</th>
                        <th>Fulfillment</th>
                        <th>Seller SKU</th>
                        <th>Shipping Fee</th>
                        <th>Other Fee</th>
                        <th>Price Type</th>
                        <th>Price</th>
                        <th>Item Related Fee Type</th>
                        <th>Item Related Fee</th>
                        <th>Misc Fee</th>
                        <th>Promotion Fee</th>
                        <th>BG</th>
                        <th>BU</th>
                        <th>Seller</th>
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

        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 20, // default record count per page
            "lengthMenu": [
                [10, 20,50,],
                [10, 20,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'id',name:'id'},
                {data: 'account',name:'account'},
                {data: 'settlement_id',name:'settlement_id'},
                {data: 'transaction_type',name:'transaction_type'},
                {data: 'order_id',name:'order_id'},
                {data: 'merchant_order_id',name:'merchant_order_id'},
                {data: 'fulfillment',name:'fulfillment'},
                {data: 'seller_sku',name:'seller_sku'},
                {data: 'shipment_fee_amount',name:'shipment_fee_amount'},
                {data: 'other_fee_amount',name:'other_fee_amount'},
                {data: 'price_type',name:'price_type'},
                {data: 'price_amount',name:'price_amount'},
                {data: 'item_related_fee_type',name:'item_related_fee_type'},
                {data: 'item_related_fee_amount',name:'item_related_fee_amount'},
                {data: 'misc_fee_amount',name:'misc_fee_amount'},
                {data: 'promotion_amount',name:'promotion_amount'},
                {data: 'bg',name:'bg'},
                {data: 'bu',name:'bu'},
                {data: 'seller',name:'seller'},
            ],
            ajax: {
                type: 'POST',
                url: '/settlement/detailList',
                data:  {search: $("#search-form").serialize()}
            }
        })
        dtApi = $('#datatable').dataTable().api();
        //点击上面的搜索
        $('#search_table').click(function(){
            $("#account").removeAttr("disabled");
            $("#currency").removeAttr("disabled");
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            $("#account").attr("disabled","disabled");
            $("#currency").attr("disabled","disabled");
            return false;
        })

        $("#export").click(function(){
            $("#account").removeAttr("disabled");
            $("#currency").removeAttr("disabled");
            var search = $("#search-form").serialize();
            var accountid = '';
            var vv = '';
            $("#account").attr("disabled","disabled");
            $("#currency").attr("disabled","disabled");
            $("#account-div .active").each(function (index,value) {
                vv = $(this).find('input').val();
                if(accountid != ''){
                    accountid = accountid + ',' + vv
                }else{
                    accountid = accountid + vv
                }
            });
            location.href='/settlement/detailExport?'+search+'&account='+accountid;
        });

        $(function(){
            $("#search_table").trigger("click");
        })
    </script>
@endsection