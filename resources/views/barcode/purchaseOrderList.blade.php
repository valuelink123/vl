<!doctype html>
<html>
<head>
    <title></title>
    <link href="/assets/global/css/components.css" rel="stylesheet" id="style_components" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet"
          type="text/css"/>
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/css/components.css" rel="stylesheet" id="style_components" type="text/css"/>

    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"
            type="text/javascript"></script>
    <style>
        .search-btn {
            background-color: #63C5D1;
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            width: 70px;
            height: 30px;
            border-radius: 5px 5px 5px 5px !important;
        }

        .common-btn {
            background-color: #63C5D1;
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            width: 70px;
            height: 30px;
            border-radius: 5px !important;
        }

    </style>
</head>
<body>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div style="height: 15px;"></div>

            <div class="portlet-title">
                <div>供应商代码(VOP)：{{$vendorCode}}</div>
                <div>供应商代码(SAP)：{{$vendorCodeFromSAP}}</div>
                <div>密钥：{{$token}}</div>
                <div>扫描&解绑URL：{{$scanDetachUrl}}</div>
                <div>企业登陆：{{$updateTokenUrl}}</div>
                <div style="height: 20px"></div>
                <form id="search-form">
                    <input type="hidden" name='vendorCode' id="vendorCode" value="{{$vendorCode}}"/>
                    <input type="hidden" name='poHidden' id="poHidden" value=""/>
                    <input type="hidden" name='p' id="p" value="{{$p}}"/>
                    <div class="table-toolbar" id="thetabletoolbar">
                        <div class="input-group">
                            <input type="text" name="po" id="po"
                                   style="width: 300px; height: 29px; border: 1px solid #dddddd;"
                                   placeholder="输入采购订单号"/>
                            <button id="search" type="button" class="search-btn">搜索
                            </button>
                        </div>
                    </div>
                </form>
                <div>
                    <button type="button" class="common-btn" id="export-btn" style="width: 80px"><span><i
                                    class="fa fa-sign-out"></i></span> 导出
                    </button>
                </div>
            </div>
            <div style="height: 20px;"></div>
            <div class="portlet-body">
                <div class="table-container">

                    <div style="overflow:auto;width: 100%;">
                        <table class="table table-striped table-bordered table-hover table-checkable" id="thetable">
                            <thead>
                            <tr role="row" class="heading">
{{--                                <th>供应商代码</th>--}}
                                <th>采购订单号</th>
                                <th>订单生成时间</th>
                                <th>生成的条码总数</th>
                                <th>实际需要的条码数</th>
                                <th>已激活的条码数</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
                    <span>Loading... </span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>


    let $theTable = $(thetable)

    var initTable = function () {
        $theTable.dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            "lengthMenu": [
                [10, 50, 100, -1],
                [10, 50, 100, 'All'] // change per page values here
            ],
            "pageLength": 50, // default record count per page
            pagingType: 'bootstrap_extended',
            processing: true,
            scrollX: false,
            ordering: true,
            aoColumnDefs: [{"bSortable": false, "aTargets": []}],
            order: [],
            columns: [
                // {data: 'vendor_code', name: 'vendor_code'},
                {data: 'purchase_order', name: 'purchase_order'},
                {data: 'purchase_date', name: 'purchase_date'},
                {data: 'total_barcodes', name: 'total_barcodes'},
                {data: 'actual_needed_barcodes', name: 'actual_needed_barcodes'},
                {data: 'activated_barcodes', name: 'activated_barcodes'},
                {data: 'details', name: 'details'},
            ],
            ajax: {
                type: 'POST',
                url: "{{ url('barcode/getPurchaseOrderList')}}",
                data: {
                    search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true),
                    '_token': '{{csrf_token()}}'
                },
            }
        });
    }

    initTable();
    let dtApi = $theTable.api();

    //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
    $('#search').click(function () {
        $po = $('#po').val();
        $('#poHidden').val($po);
        dtApi.settings()[0].ajax.data = {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true)};
        dtApi.ajax.reload();
        return false;
    });

    $(function () {
        $("#ajax").on("hidden.bs.modal", function () {
            $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
        });
    });
    //下载数据
    $("#export-btn").click(function () {
        location.href = '/barcodePoListExport?vendorCode=' + $("#vendorCode").val() + '&po=' + $("#poHidden").val();
        return false;
    });

</script>
</body>
</html>

