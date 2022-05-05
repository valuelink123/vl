<!doctype html>
<html>
<head>
    <title>扫描条码</title>
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
        <input type="hidden" id='vendorCode' name='vendorCode'
               value="@if(isset($vendorCode)) {{$vendorCode}} @else @endif"/>
        <input type="hidden" id='purchaseOrder' name='purchaseOrder'
               value="@if(isset($purchaseOrder)) {{$purchaseOrder}} @else @endif"/>
        <input type="hidden" id='sku' name='sku' value="@if(isset($sku)) {{$sku}} @else @endif"/>
        <input type="hidden" id='barcode' value=""/>
        <div align="center">
            <label align="center" style="font-size: 32px">激活条码</label>
        </div>
        <div style="width: 300px; text-align: right;">
            <div>SKU: @if(isset($sku)) {{$sku}} @else @endif的总数：
                <input class="qtyInput" type="text" id='skuQty'
                       name='skuQty'
                       value="@if(isset($skuQty)) {{$skuQty}} @else @endif"
                       readonly/></div>

            <div>已激活的SKU数量：
                <input class="qtyInput" type="text" id='activatedCount' name='activatedCount'
                       value="@if(isset($activatedCount)) {{$activatedCount}} @else 0 @endif" readonly/>
            </div>
        </div>
        <div>当前条码</div>
        <div>
            <input class="bartextInput" type="text" name="barcodeText" id="barcodeText" value="" maxlength="12"   required/>
        </div>
        <div>当前重量</div>
        <div>
            <input class="bartextInput" type="text" name="barcodeWeightText" id="barcodeWeightText"  autocomplete="off" required/>
        </div>
        <input type="hidden" name="submitFormReady" id="submitFormReady">
        <div>
            <label id='msgLabel' style="color:#ff0000; font-size:28px; margin-top:15px"></label>
        </div>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('#barcodeText').focus();
    });

    $('#barcodeText').bind('input propertychange', function() {
        if($(this).val().length ==12){
            $('#barcodeWeightText').focus().val();
        }
    })
    let time = null;
    $('#barcodeWeightText').bind('input propertychange', function() {
        console.log($('#barcodeWeightText').val())
        if(time){
            clearTimeout(time);
        }
       time = setTimeout(function (){
           if($('#barcodeWeightText').val().length>0){
               if($('#barcodeText').val().trim().length !== 12){
                   $('#msgLabel').text('条码号必须为12位');
                   return
               };
               $.ajax({
                   type: 'post',
                   url: '/barcode/activateBarcode',
                   data: {
                       vendorCode: $('#vendorCode').val().trim(),
                       purchaseOrder: $('#purchaseOrder').val().trim(),
                       sku: $('#sku').val().trim(),
                       barcodeText: $('#barcodeText').val().trim(),
                       weight: $('#barcodeWeightText').val().trim(),
                       _token: '{{csrf_token()}}',
                   },
                   dataType: 'json',
                   success: function (res) {
                       console.log(res)
                       if (res) {
                           // alert(res.msg);
                           $('#barcodeText').val('').focus();
                           $('#barcodeWeightText').val('');
                           if (res.flag == 0) {
                               // alert(res.msg);
                               $('#msgLabel').text(res.msg);
                               $('#msgLabel').css('color', 'red');
                           } else if (res.flag == 1) {
                               // alert(res.msg);msg
                               $('#msgLabel').text(res.msg);
                               $('#msgLabel').css('color', 'green');
                               $('#activatedCount').val(res.activatedCount);
                           }

                       } else {
                           //编辑失败
                           $('#barcodeText').val('');
                           $('#msgLabel').text('扫描失败，请重试');
                       }
                   }
               });
           }
        },400
    )

    });

</script>
</body>
</html>

