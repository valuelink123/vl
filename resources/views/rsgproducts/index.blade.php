@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Rsg Products']])
@endsection
@section('content')


    <link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
    <script src="/js/chosen/chosen.jquery.min.js"></script>

    @include('frank.common')

    <div class="portlet light bordered">
        <div class="portlet-body">
            <form id="search-form">
            <div class="table-toolbar" id="thetabletoolbar">
                <div class="row">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Date</span>
                            <input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date !!}" id="date" name="date"
                                   autocomplete="off"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Asin</span>
                            <input class="form-control" value="" id="asin" name="asin" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select  style="width:100%;height:35px;" id="bg" name="bg">
                                <option value="">Select</option>
                                @foreach($bgs as $bg)
                                    <option value="{!! $bg !!}">{!! $bg !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">BU</span>
                            <select  style="width:100%;height:35px;" id="bu" name="bu">
                                <option value="">Select</option>
                                @foreach($bus as $bu)
                                    <option value="{!! $bu !!}">{!! $bu !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Item No</span>
                            <input class="form-control" value="" id="item_no" name="item_no" autocomplete="off"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Sites</span>
                            <select  style="width:100%;height:35px;" id="site" name="site">
                                <option value="">Select</option>
                                @foreach(getAsinSites() as $site)
                                    <option value="{!! $site !!}">{!! $site !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Type</span>
                            <select  style="width:100%;height:35px;" id="post_type" name="post_type">
                                <option value="">Select</option>
                                @foreach(getPostType() as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val['name'] !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Status</span>
                            <select  style="width:100%;height:35px;" id="post_status" name="post_status">
                                <option value="">Select</option>
                                @foreach(getPostStatus() as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val['name'] !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button id="search" class="btn sbold blue">Search</button>
                    </div>


                    @permission('rsgproducts-export')
                    <div class="btn-group">
                    <button id="export" class="btn sbold blue"> Export
                        <i class="fa fa-download"></i>
                    </button>
                    </div>
                    @endpermission
                </div>
            </div>

            </form>

            </div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        {{--<th onclick="this===arguments[0].target && this.firstElementChild.click()">--}}
                            {{--<input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>--}}
                        {{--</th>--}}
                        <th>Rank</th>
                        <th>Score</th>
                        <th>Order Status</th>
                        <th>Product</th>
                        <th>Site</th>
                        <th>Asin</th>
                        <th>Item No</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Level</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        <th>BG</th>
                        <th>BU</th>
                        <th>Seller</th>
                        <th>Unfinished</th>
                        <th>Target</th>
                        <th>Achieved</th>
                        <th>Task</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
    <div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" >
                <div class="modal-body" >
                    <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
                    <span>Loading... </span>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("#thetabletoolbar [id^='date']").each(function () {

            let defaults = {
                autoclose: true
            }

            let options = eval(`({${$(this).data('options')}})`)

            $(this).datepicker(Object.assign(defaults, options))
        })

        let $theTable = $(thetable)

        var initTable = function () {
            $theTable.dataTable({
                searching: false,//关闭搜索
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                "lengthMenu": [
                    [20, 50, 100, -1],
                    [20, 50, 100, 'All'] // change per page values here
                ],
                "pageLength": 20, // default record count per page
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  false,
                // aoColumnDefs: [ { "bSortable": true}],
                // order: [[1, 'desc']],
                // select: {
                //     style: 'os',
                //     info: true, // info N rows selected
                //     // blurable: true, // unselect on blur
                //     selector: 'td:first-child', // 指定第一列可以点击选中
                // },
                columns: [
                    // {
                    //     width: "1px",
                    //     defaultContent: '',
                    //     className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                    // },
                    {data: 'rank', name: 'rank'},
                    {data: 'score', name: 'score'},
                    {data: 'order_status', name: 'order_status'},
                    {data: 'product', name: 'product'},
                    {data: 'site', name: 'site'},
                    {data: 'asin', name: 'asin'},
                    {data: 'item_no', name: 'item_no'},
                    {data:'type',name:'type'},
                    {data:'status',name:'status'},
                    {data:'sku_level',name:'level'},
                    {data:'rating',name:'rating'},
                    {data:'review',name:'review'},
                    {data:'bg',name:'bg'},
                    {data:'bu',name:'bu'},
                    {data:'seller',name:'seller'},
                    {data:'unfinished',name:'unfinished'},
                    {data:'target_review',name:'target_review'},
                    {data:'achieved',name:'achieved'},
                    {data:'task',name:'task'},
                    {data:'action',name:'action'},
                ],
                ajax: {
                    type: 'POST',
                    url: location.href,
                    data:  {search: $("#search-form").serialize()}
                }
            })
        }


        initTable();
        let dtApi = $theTable.api();


        //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
        $('#search').click(function () {
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
        });

        //下载数据
        $("#export").click(function(){
            location.href='/rsgproducts/export?date='+$("#date").val();
            return false;

        });

        $(function() {
            $("#ajax").on("hidden.bs.modal",function(){
                $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
            });
        });

    </script>

@endsection