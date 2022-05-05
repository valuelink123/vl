<!doctype html>
<html>
<head>
    <title>QC核对条码</title>
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <style type="text/css">
        .btn-submit {
            background-color: #63C5D1;
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            width: 70px;
            height: 30px;
            border-radius: 5px 5px 5px 5px !important;
            margin-top: 15px;
        }
        input {
            width: 250px;
            height: 20px;
        }
    </style>

</head>
<body>
<div align="center">
    <form action="{{ url('/barcode/checkQc') }}" method="POST" id="scanForm" onsubmit="return checkForm()">
        {{ csrf_field() }}
        <div align="center">
            <label align="center" style="font-size: 32px">QC核对条码</label>
        </div>
        <div>采购订单号</div>
        <div>
            <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder" @if(isset($purchaseOrder)) value="{{$purchaseOrder}}" @else value="" @endif required>
        </div>
        <div>SKU</div>
        <div>
            <input type="text" class="form-control" name="sku" id="sku" @if(isset($sku)) value="{{$sku}}" @else value="" @endif required>
        </div>
        <div align="center">
            <button type="submit" class="btn-submit">提交</button>
        </div>
        <div>
            <label style="color:#ff0000; font-size:28px; margin-top:15px">@if(isset($msg)){{$msg}} @else @endif</label>
        </div>
    </form>
</div>
<script type="text/javascript">

    function checkForm() {
        $purchaseOrder = $('#purchaseOrder').val().trim();
        $sku = $('#sku').val().trim();
        if ($purchaseOrder == '') {
            $('#purchaseOrder').focus();
            return false;
        }

        if ($sku == '') {
            $('#sku').focus();
            return false;
        }
        $('#scanForm').submit();
    }


</script>
</body>
</html>

