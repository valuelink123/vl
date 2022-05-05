<!doctype html>
<html>
<head>
    <title>扫描条码</title>
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
    <form action="{{ url('/barcode/checkPoSku') }}" method="POST" id="scanForm" onsubmit="return checkForm()">
        {{ csrf_field() }}
        <input type="hidden" id='token' name='token' value="@if(isset($token)) {{$token}} @else @endif"/>
        <input type="hidden" id='urlParam' name='urlParam' value="@if(isset($urlParam)) {{$urlParam}} @else @endif"/>
        <div align="center">
            <label align="center" style="font-size: 32px">扫描条码</label>
        </div>
        <div>采购订单号</div>
        <div>
            <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder"
                   value="@if(isset($purchaseOrder)) {{$purchaseOrder}} @else @endif" required>
        </div>
        <div>SKU</div>
        <div>
            <input type="text" class="form-control" name="sku" id="sku"
                   value="@if(isset($sku)) {{$sku}} @else @endif" required>
        </div>
        <div>重量(单位g)</div>
        <div>
            <input style="width: 114px" type="text" class="form-control" placeholder="min" name="min" id="min"
                   value="@if(isset($min)) {{$min}} @else @endif" required>
            -
            <input style="width: 114px" type="text" class="form-control" placeholder="max" name="max" id="max"
                   value="@if(isset($max)) {{$max}} @else @endif" required>
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

