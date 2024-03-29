<!doctype html>
<html>
<head>
    <title>解绑条码</title>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <style type="text/css">
        input {
            height: 30px;
            width: 250px;
        }
    </style>
</head>
<body>
<div align="center">
        <input type="hidden" id='vendorCode' name='vendorCode'
               value="@if(isset($vendorCode)) {{$vendorCode}} @else @endif"/>
        <input type="hidden" id='purchaseOrder' name='purchaseOrder'
               value="@if(isset($purchaseOrder)) {{$purchaseOrder}} @else @endif"/>
        <div align="center">
            <label align="center" style="font-size: 32px">解绑条码</label>
        </div>
        <div>解绑条码</div>
        <div>
            <input class="bartextInput" type="text" name="barcodeText" id="barcodeText" value="" autocomplete="off" required/>
        </div>
        <div>
            <label id='msgLabel' style="color:#ff0000; font-size:28px; margin-top:15px"></label>
        </div>
</div>

<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('#barcodeText').focus();
    });

    $('#barcodeText').on('input', function () {
        $barcodeText = $('#barcodeText').val().trim();
        if ($barcodeText == '') {
            $('#barcodeText').focus();
            return false;
        }
        //条码位数正确，才能提交。不然会提交很多次（不固定）
        if ($barcodeText.length != 12) {
            return false;
        }

        $.ajax({
            type: 'post',
            url: '/barcode/deactivateBarcode',
            data: {
                vendorCode: $('#vendorCode').val().trim(),
                purchaseOrder: $('#purchaseOrder').val().trim(),
                barcodeText: $barcodeText,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (res) {
                console.log(res)
                if (res) {
                    $('#barcodeText').val('');
                    if (res.flag == 0) {
                        $('#msgLabel').text('');
                        setTimeout("$('#msgLabel').text('" + res.msg +"')", 200)
                        // $('#msgLabel').text(res.msg);
                        $('#msgLabel').css('color', 'red');
                    } else if (res.flag == 1) {
                        // $('#msgLabel').text(res.msg);
                        $('#msgLabel').text('');
                        setTimeout("$('#msgLabel').text('" + res.msg +"')", 200)
                        $('#msgLabel').css('color', 'green');
                    }

                } else {
                    //编辑失败
                    $('#barcodeText').val('');
                    $('#msgLabel').text('解绑失败，请重试');
                }
            }
        });

    });

</script>
</body>
</html>

