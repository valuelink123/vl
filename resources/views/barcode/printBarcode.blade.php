<!doctype html>
<html>
<head>
    <title>打印条码</title>
    <style>
        .btn-submit {
            background-color: #63C5D1;
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            width: 96px;
            height: 30px;
            border-radius: 5px 5px 5px 5px !important;
            margin-top: 15px;
        }

        input {
            width: 250px;
            height: 20px;
        }

        select {
            width: 258px;
            height: 26px;
        }ov
    </style>

</head>
<body>
<div align="center">
    <form action="{{ url('/barcode/outputBarcode') }}" method="post" id="printForm" target="_blank">
        {{ csrf_field() }}
        <div align="center">
            <label align="center" style="font-size: 32px">打印条码</label>
        </div>
        <div>采购订单号</div>
        <div>
            <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder" value=""
                   required>
        </div>
        <div>选择条码纸规格</div>
        <div>
            <select name="barcodeSizeType" id="barcodeSizeType">
                <option selected="selected" value="1">规格1（中型：31mm*10mm）</option>
            </select>
        </div>
    </form>
    <div align="center">
        <button type="button" id="btn-submit" class="btn-submit">网页浏览
        </button>
<!--        <button type="button" id="btn-export" class="btn-submit" style="width: 114px">下载条码PDF-->
<!--        </button>-->
<!--        <div style="font-size: x-small; color: #ff0000">（PDF文件不是实时生成的，如不能下载，请稍后再试）</div>-->
        <div style="font-size: x-small; color: #ff0000">（网页浏览，页面点击右键，选择: 打印 > 另存为PDF）</div>

    </div>
    <div style="clear:both;"></div>
    <div id="msgDiv"></div>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $('#btn-submit').click(function () {
        $purchaseOrder = $('#purchaseOrder').val().trim();
        if ($purchaseOrder == '') {
            alert('请输入采购订单号');
            $('#purchaseOrder').focus();
            return false;
        }
        $('#printForm').submit();
    });

    $('#btn-export').click(function () {
        // $purchaseOrder = $('#purchaseOrder').val().trim();
        // if ($purchaseOrder == '') {
        //     alert('请输入采购订单号');
        //     $('#purchaseOrder').focus();
        //     return false;
        // }
        // window.open('/barcode/exportBarcodePdf?purchaseOrder=' + $purchaseOrder);
    });

</script>
</body>
</html>

