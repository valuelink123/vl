<!doctype html>
<html>
<head>
    <title>QC核对条码</title>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <style type="text/css">
        .bartextInput {
            height: 30px;
            width: 290px;
        }

        .qtyInput {
            height: 20px;
            width: 40px;
            border: none;
            text-align: right;
        }
    </style>
</head>
<body>
<div align="center">
        <input type="hidden" id='purchaseOrder' name='purchaseOrder' @if(isset($purchaseOrder)) value="{{$purchaseOrder}}" @else value="" @endif />
        <input type="hidden" id='sku' name='sku' @if(isset($sku)) value="{{$sku}}" @else value="" @endif />
        <div align="center">
            <label align="center" style="font-size: 32px">QC核对条码</label>
        </div>
        <div>扫描条码</div>
        <div>
            <input class="bartextInput" type="text" name="barcodeText" id="barcodeText" value="" autocomplete="off" required/>
        </div>
        <div>
            <label id='msgLabel' style="color:#ff0000; font-size:28px; margin-top:15px"></label>
        </div>
{{--    </form>--}}
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('#barcodeText').focus();
    });

    $('#barcodeText').on('input', function (event) {
        $('#msgLabel').text('');
        $barcodeText = $('#barcodeText').val().trim();
        if ($barcodeText == '') {
            $('#barcodeText').focus();
            return false;
        }
        //条码位数正确，才能提交
        if ($barcodeText.length != 12) {
            return false;
        }
        $.ajax({
            type: 'post',
            url: '/barcode/verifyQc',
            data: {
                purchaseOrder: $('#purchaseOrder').val().trim(),
                sku: $('#sku').val().trim(),
                barcodeText: $barcodeText,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (res) {
                console.log(res)
                if (res) {
                    // alert(res.msg);
                    $('#barcodeText').val('');
                    if (res.flag == 0) {
                        // alert(res.msg);
                        $('#msgLabel').text(res.msg);
                        $('#msgLabel').css('color', 'red');
                    } else if (res.flag == 1) {
                        // alert(res.msg);msg
                        $('#msgLabel').text(res.msg);
                        $('#msgLabel').css('color', 'green');
                    }

                } else {
                    //编辑失败
                    $('#barcodeText').val('');
                    $('#msgLabel').text('扫描失败，请重试');
                }
            }
        });
    });

</script>
</body>
</html>

