@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['productTransfer']])
@endsection
@section('content')
    <style>
        .table-container .green{
            background-color:green;
        }
    </style>


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
                    </div>

                    <div class="btn-group">
                        <button id="search" class="btn sbold blue">Search</button>
                    </div>
                    @permission('productTransfer-edit')
                    <div class="btn-group">
                        <button id="edit" class="btn sbold blue">Edit</button>
                    </div>
                    @endpermission
                    @permission('productTransfer-reply')
                    <div class="btn-group">
                        <button id="reply-list" class="btn sbold blue">ReplyList</button>
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
                        <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                            <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>
                        </th>
                        <th>BG</th>
                        <th>BU</th>
                        <th>Sales</th>
                        <th>Site</th>
                        <th>Seller Name</th>
                        <th>seller-sku</th>
                        <th>ASIN</th>
                        <th>Item No</th>
                        <th>Product Name</th>
                        <th>SKU Status</th>
                        <th>SKU Grade</th>
                        <th>ASIN Level</th>
                        <th title="Last seven days">Avg Sales</th>
                        <th>Available</th>
                        <th>FBA Transfer</th>
                        <th>FBM-FBA Transfer</th>
                        <th>Sorting</th>
                        <th class="green">Safety Days</th>
                        <th class="green">FBM-FBA</th>
                        <th class="green">Shelfing</th>
                        <th title="Days of inventory maintenance">DMI</th>
                        <th>Remind</th>
                        <th>Allocation Quantity</th>
                        <th>FBM</th>
                        <th title="Days of total inventory maintenance">TDMI</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
    {{--批量编辑天数--}}
    <div id="edit-content" style="display:none;">
        <form id="edit-form">
            <input type="hidden" class="form-control" name="ids" id="ids" value="">
        <div class="form-group">
            <label>Safety Days</label>
            <div class="input-group ">
                    <span class="input-group-addon">
                        <i class="fa fa-bookmark"></i>
                    </span>
                <input type="text" class="form-control" name="safety_days" value="">
            </div>
        </div>
        <div class="form-group">
            <label>FBM-FBA</label>
            <div class="input-group ">
                    <span class="input-group-addon">
                        <i class="fa fa-bookmark"></i>
                    </span>
                <input type="text" class="form-control" name="fbmfba_days" value="">
            </div>
        </div>
        <div class="form-group">
            <label>Shelfing</label>
            <div class="input-group ">
                <span class="input-group-addon">
                    <i class="fa fa-bookmark"></i>
                </span>
                <input type="text" class="form-control" name="fbmfba_shelfing" value="">
            </div>
        </div>
        </form>
    </div>
    {{--点击调拨进行调拨申请--}}
    <div id="reply-process-content" style="display:none;">
        <form id="reply-process-form">
            <input type="hidden" class="form-control" name="id" value="">
            <div class="form-group">
                <label>Transfers Number</label>
                <div class="input-group ">
                    <span class="input-group-addon">
                        <i class="fa fa-bookmark"></i>
                    </span>
                    <input type="text" class="form-control" name="reply_number" value="">
                </div>
            </div>
            <div class="form-group">
                <label>Transfers Reason</label>
                <div class="input-group ">
                    <span class="input-group-addon">
                        <i class="fa fa-bookmark"></i>
                    </span>
                    <input type="text" class="form-control" name="reply_reason" value="">
                </div>
            </div>
            <div class="form-group">
                <label>Label</label>
                <div class="input-group ">
                <span class="input-group-addon">
                    <i class="fa fa-bookmark"></i>
                </span>
                    <select class="form-control" name="reply_label_status">
                        <option value="0">Yes</option>
                        <option value="1">No</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Factory</label>
                <div class="input-group ">
                <span class="input-group-addon">
                    <i class="fa fa-bookmark"></i>
                </span>
                    <select class="form-control" id="reply-factory" name="reply_factory">
                        <option value="US01">US01</option>
                        <option value="CA01">CA01</option>
                        <option value="JP01">JP01</option>
                        <option value="ES01">ES01</option>
                        <option value="FR01">FR01</option>
                        <option value="GR01">GR01</option>
                        <option value="IT01">IT01</option>
                        <option value="UK01">UK01</option>
                    </select>

                </div>
            </div>
            <div class="form-group">
                <label>location</label>
                <div class="input-group ">
                <span class="input-group-addon">
                    <i class="fa fa-bookmark"></i>
                </span>
                    <input type="text" class="form-control" id="reply-location" name="reply_location" value="AA1" readonly="readonly">
                </div>
            </div>
        </form>
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
                search: {search: $("#search-form").serialize()},
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                scrollX: 2000,
                fixedColumns:   {
                    leftColumns:12,
                    rightColumns: 2
                },
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  false,
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
                    {data: 'product_name', name: 'product_name'},
                    {data: 'sku_status', name: 'sku_status'},
                    {data: 'sku_grade', name: 'sku_grade'},
                    {data: 'asin_level', name: 'asin_level'},
                    {data: 'avg_sales', name: 'avg_sales'},
                    {data: 'fba_available', name: 'fba_available'},
                    {data: 'fba_transfer', name: 'fba_transfer'},
                    {data: 'fbmfba_transfer', name: 'fbmfba_transfer'},
                    {data: 'fbmfba_sorting', name: 'fbmfba_sorting'},
                    {data: 'safety_days', name: 'safety_days',className: 'safety-days'},
                    {data: 'fbmfba_days', name: 'fbmfba_days',className: 'fbmfba-days'},
                    {data: 'fbmfba_shelfing', name: 'fbmfba_shelfing',className: 'fbmfba-shelfing'},
                    {data: 'dmi', name: 'dmi'},
                    {data: 'remind', name: 'remind'},
                    {data:'allocation_quantity',name:'allocation_quantity'},
                    {data:'fbm_stock',name:'fbm_stock'},
                    {data:'tdmi',name:'tdmi'},
                    {data:'action',name:'action'},
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
            dtApi.ajax.reload();
            return false;
        });

        //点击edit批量编辑列表数据
        $('#edit').click(function () {
            let selectedRows = dtApi.rows({selected: true})

            let dataRows = selectedRows.data().toArray().map(obj => [obj.id])
            let ids = dataRows.join(',');
            $('#ids').val(ids);

            if (dataRows.length==0) {
                toastr.error('Please select some rows first !');
                return false;
            }
            art.dialog({
                id: 'art_edit',
                title: 'edit',
                content: document.getElementById('edit-content'),
                okVal: 'Submit',
                ok: function () {
                    this.title('In the submission…');
                    var data = $("#edit-form").serialize();
                    $.ajax({
                        type: 'post',
                        url: '/productTransfer/updateDays',
                        data: data,
                        dataType: 'json',
                        success: function(res) {
                            if(res){
                                //动态改变已修改的值，不用重新加载数据
                                var safetyDays = $("input[name='safety_days']").val();
                                var fbmfbaDays = $("input[name='fbmfba_days']").val();
                                var fbmfbaShelfing = $("input[name='fbmfba_shelfing']").val();
                                if(safetyDays){
                                    $('.selected .safety-days').text(safetyDays);
                                }
                                if(fbmfbaDays){
                                    $('.selected .fbmfba-days').text(fbmfbaDays);
                                }
                                if(fbmfbaShelfing){
                                    $('.selected .fbmfba-shelfing').text(fbmfbaShelfing);
                                }
                                toastr.success('Saved !');
                            }else{
                                //编辑失败
                                alert('Failed');
                            }
                        }
                    });
                },
                cancel: true,
                cancelVal:'Cancel'
            });
            return false;
        });

        //申请调拨弹窗的工厂库位的联动
        $("#reply-factory").click(function(){
            var factory = $(this).val();
            var arr = { 'US01': 'AA1','CA01':'AC2','JP01':'AJ2','ES01':'AS2','FR01':'AF2','GR01':'AG2','IT01':'AI2','UK01':'AE3'};
            $.each(arr, function(key, value) {
                if(factory==key){
                    $('#reply-location').val(value);
                }
            });
        });

        //点击process，申请调拨操作
        $(".table-container").on('click', '.reply-process',function(){
            var replyId = $(this).attr('data-id');
            $(this).parent().addClass('current');
            art.dialog({
                id: 'art_reply-process',
                title: 'reply process',
                content: document.getElementById('reply-process-content'),
                okVal: 'Submit',
                ok: function () {
                    this.title('In the submission…');
                    $("#reply-process-form input[name='id']").val(replyId);
                    var data = $("#reply-process-form").serialize();
                    $.ajax({
                        type: 'post',
                        url: '/productTransfer/reply',
                        data: data,
                        dataType: 'json',
                        success: function(res) {
                            if(res){
                                $('.current').html('Replyed');
                                window.open('/productTransfer/replyList','_blank');
                            }else{
                                //编辑失败
                                alert('Failed');
                            }
                        }
                    });
                },
                cancel: true,
                cancelVal:'Cancel'
            });
            return false;
        });



        //点击忽略操作
        $(".table-container").on('click', '.reply-ignore',function(){
            var replyId = $(this).attr('data-id');
            $(this).parent().addClass('current');
            if(confirm('Are you sure to ignore it?')){
                $.ajax({
                    type: 'post',
                    url: '/productTransfer/ignore',
                    data: {id:replyId},
                    dataType: 'json',
                    success: function(res) {
                        if(res){
                            $('.current').html('Ignored');
                            toastr.success('Saved !');
                        }else{
                            //编辑失败
                            alert('Failed');
                        }
                    }
                });
            }
            return false;
        });

        //点击申请列表进行跳转
        $('#reply-list').click(function(){
            window.open('/productTransfer/replyList','_blank');
            return false;
        })

    </script>

@endsection