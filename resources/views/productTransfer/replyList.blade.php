@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['replyList']])
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
                                <span class="input-group-addon">From</span>
                                <input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date_from !!}" id="date_from" name="date_from"
                                        autocomplete="off"/>
                            </div>
                            <br>
                            <div class="input-group">
                                <span class="input-group-addon">To</span>
                                <input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date_to !!}" id="date_to" name="date_to"
                                        autocomplete="off"/>
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
                                <span class="input-group-addon">Sales</span>
                                <select style="width:100%;height:35px;" id="sales" name="sales">
                                    <option value="">Select</option>
                                    @foreach($users as $id=>$name)
                                        <option value="{!! $id !!}">{!! $name !!}</option>
                                    @endforeach
                                </select>
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
                                <span class="input-group-addon">seller-sku</span>
                                <input class="form-control" value="" id="sellersku" name="sellersku" autocomplete="off"/>
                            </div>
                            <br>
                            <div class="input-group">
                                <span class="input-group-addon">Item No</span>
                                <input class="form-control" value="" id="item_no" name="item_no" autocomplete="off"/>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-addon">SKU Status</span>
                                <select class="form-control" name="sku_status">
                                    <option value="">Select</option>
                                    <option value="1">Eliminate</option>
                                    <option value="2">Reserved</option>
                                </select>
                            </div>
                            <br>
                            <div class="input-group">
                                <span class="input-group-addon">Asin</span>
                                <input class="form-control" value="" id="asin" name="asin" autocomplete="off"/>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button id="search" class="btn sbold blue">Search</button>
                        </div>
                        @if($dataApproveType)
                        <div class="btn-group">
                            <button id="approve" class="btn sbold blue approve-reject" data-audit="1" data-type="{{$dataApproveType}}" data-content="{{$dataApproContent}}">Approve</button>
                        </div>
                        <div class="btn-group">
                            <button id="reject" class="btn sbold blue approve-reject" data-audit="2" data-type="{{$dataRejectType}}" data-content="{{$dataRejectContent}}">Reject</button>
                        </div>
                        @endif
                    </div>
                </div>

            </form>

        </div>
        <div class="table-container" style="">
            <table class="table table-striped table-bordered" id="thetable">
                <thead>
                <tr>
                    <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                        <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>
                    </th>
                    <th>ID</th>
                    <th>Date</th>
                    <th>BG</th>
                    <th>BU</th>
                    <th>Sales</th>
                    <th>Site</th>
                    <th>Seller Name</th>
                    <th>seller-sku</th>
                    <th>ASIN</th>
                    <th>Item No</th>
                    <th>SKU Status</th>
                    <th>SKU Grade</th>
                    <th>ASIN Level</th>
                    <th>Suggest Num</th>
                    <th>Reply Number</th>
                    <th>Reply Reason</th>
                    <th>Label Status</th>
                    <th>Label Type</th>
                    <th>Reply Factory</th>
                    <th>Reply Location</th>
                    <th>Reply Processor</th>
                    <th>Audit date</th>
                    <th>Opera Log</th>
                    <th>Process</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
    </div>
    </div>

    <script>

        XFormHelper.initByQuery('[data-init-by-query]')

        $("#thetabletoolbar [id^='date_']").each(function () {

            let defaults = {
                autoclose: true
            }

            let options = eval(`({${$(this).data('options')}})`)

            $(this).datepicker(Object.assign(defaults, options))
        })

        // $(thetabletoolbar).change(e => {
        //     dtApi.ajax.reload()
        // })

        let $theTable = $(thetable)

        //计划部点击列表中的申请数量可修改申请的数量
        $(".table-container").on('blur', '.reply-number',function(){
            var replyId = $(this).attr('data-id');
            var number = $(this).val();
            $.ajax({
                type: 'post',
                url: '/productTransfer/updateReply',
                data: {id:replyId,reply_number:number,type:0},
                dataType: 'json',
                success: function(res) {
                    if(res){
                        toastr.success('Success !');
                    }else{
                        //编辑失败
                        alert('Failed');
                    }
                }
            });
        });
        //点击log-detail弹窗出现该条数据的操作日志
        $(".table-container").on('click', '.log-detail',function(){
            var replyId = $(this).attr('data-id');
            $.ajax({
                type: 'post',
                url: '/productTransfer/showLog',
                data: {id:replyId},
                dataType: 'json',
                success: function(res) {
                    if(res){
                        art.dialog({
                            id: 'art_opera_log',
                            title: 'opera log_'+replyId,
                            content: res['opera_log'],
                        });
                    }else{
                        //编辑失败
                        alert('Failed');
                    }
                }
            });

        });

        var initTable = function () {
            $theTable.dataTable({
                searching: false,//关闭搜索
                search: {search: $("#search-form").serialize()},
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                scrollX: 2000,
                fixedColumns: {
                    leftColumns: 3,
                    rightColumns: 3
                },
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering: false,
                // order: [[1, 'desc']],
                select: {
                    style: 'os',
                    info: true, // info N rows selected
                    // blurable: true, // unselect on blur
                    selector: 'td:first-child', // 指定第一列可以点击选中
                },
                // "aoColumnDefs": [ { "bSortable": true, "aTargets": [] }],

                columns: [
                    {
                        width: "1px",
                        defaultContent: '',
                        className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                    },
                    {data: 'id', name: 'id'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'bg', name: 'bg'},
                    {data: 'bu', name: 'bu'},
                    {data: 'sales', name: 'sales'},
                    {data: 'site', name: 'site'},
                    {data: 'seller_name', name: 'seller_name'},
                    {data: 'sellersku', name: 'sellersku'},
                    {
                        data: 'asin',
                        name: 'asin',
                        render(data, type, row) {
                            if (!data) return ''
                            let asins = data.split(',')
                            return asins.map(asin => {
                                return `<a href="https://${row.site}/dp/${asin}" target="_blank" rel="noreferrer">${asin}</a>`
                            }).join('<br/>')
                        }
                    },
                    {data: 'item_no', name: 'item_no'},
                    {data: 'sku_status', name: 'sku_status'},
                    {data: 'sku_grade', name: 'sku_grade'},
                    {data: 'asin_level', name: 'asin_level'},
                    {data: 'suggest_num', name: 'suggest_num'},
                    {data: 'reply_number', name: 'reply_number'},
                    {data: 'reply_reason', name: 'reply_reason'},
                    {data: 'reply_label_status', name: 'reply_label_status'},
                    {data: 'reply_label_type', name: 'reply_label_type'},
                    {data: 'reply_factory', name: 'reply_factory'},
                    {data: 'reply_location', name: 'reply_location'},
                    {data: 'reply_processor', name: 'reply_processor'},
                    {data: 'audit_date', name: 'audit_date',class:'aduit-date'},
                    {data: 'opera_log', name: 'opera_log'},
                    {data: 'process', name: 'process'},
                ],
                ajax: {
                    type: 'POST',
                    url: location.href
                }
            })
        }

        initTable();
        let dtApi = $theTable.api();

        //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
        $('#search').click(function () {
            $theTable.fnClearTable(false); //清空一下table
            $theTable.fnDestroy(); //还原初始化了的datatable
            initTable();
            return false;
        });

        //批量批准或者批量拒绝操作
        $('.approve-reject').click(function () {
            let selectedRows = dtApi.rows({selected: true})

            let dataRows = selectedRows.data().toArray().map(obj => [obj.id])
            let ids = dataRows.join(',');

            console.log(dataRows);
            if (dataRows.length==0) {
                toastr.error('Please select some rows first !');
                return false;
            }

            var content = $(this).attr('data-content');
            var type = $(this).attr('data-type');
            var audit = $(this).attr('data-audit');//1为批准，2为拒绝
            var confirmMsg = 'Are you sure to approve it?';
            if(audit==2){
                confirmMsg = 'Are you sure to reject it?';
            }

            if(confirm(confirmMsg)) {
                $.ajax({
                    type: 'post',
                    url: '/productTransfer/replyAudit',
                    data: {id: ids, type: type},
                    dataType: 'json',
                    success: function (res) {
                        if (res) {
                            //动态改变已修改的值，不用重新加载数据
                            $('.selected .process').text(content);
                            $('.selected .aduit-date').text(getCurrentDate());
                            toastr.success('Saved !');
                        } else {
                            //操作失败
                            alert('Failed');
                        }
                    }
                });
            }
            return false;
        });

        //单条数据的批准或者拒绝操作
        $(".table-container").on('click', '.process-one',function(){
            var ids = $(this).parent().attr('data-id');
            var content = $(this).attr('data-content');
            var type = $(this).attr('data-type');
            var audit = $(this).attr('data-audit');//1为批准，2为拒绝
            var confirmMsg = 'Are you sure to approve it?';
            if(audit==2){
                confirmMsg = 'Are you sure to reject it?';
            }
            //移除上一次的类名
            $('.process').removeClass('current-one');
            $('tr').removeClass('current-tr');

            //添加当前的类名
            $(this).parent().addClass('current-one');
            $(this).parent().parent().parent().addClass('current-tr');


            if(confirm(confirmMsg)) {
                $.ajax({
                    type: 'post',
                    url: '/productTransfer/replyAudit',
                    data: {id: ids, type: type},
                    dataType: 'json',
                    success: function (res) {
                        if (res) {
                            //动态改变已修改的值，不用重新加载数据
                            $('.current-one').text(content);
                            $('.current-tr .aduit-date').text(getCurrentDate());
                            toastr.success('Saved !');
                        } else {
                            //操作失败
                            alert('Failed');
                        }
                    }
                });
            }
            return false;
        });

        function getCurrentDate()
        {
            var date=new Date();
            var year=date.getFullYear(); //获取当前年份
            var mon=date.getMonth()+1; //获取当前月份
            var da=date.getDate(); //获取当前日
            var day=date.getDay(); //获取当前星期几
            var h=date.getHours(); //获取小时
            var m=date.getMinutes(); //获取分钟
            var s=date.getSeconds(); //获取秒
            var d=document.getElementById('Date');
            var currentDate=year+'-'+mon+'-'+da+' '+h+':'+m+':'+s;
            return currentDate;
        }

    </script>

@endsection