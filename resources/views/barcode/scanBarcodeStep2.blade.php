<!doctype html>
<html>
<head>
    <title>Scan Barcode</title>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet"
          type="text/css"/>

    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"
            type="text/javascript"></script>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <style type="text/css">
        input {
            height: 40px;
        }
    </style>

</head>
<body>
<form action="{{ url('/barcode/activateBarcode') }}" method="POST" id="scanForm">
    {{ csrf_field() }}
    <input type="hidden" id='vendorCode' name='vendorCode'
           value="@if(isset($vendorCode)) {{$vendorCode}} @else @endif"/>
    <input type="hidden" id='purchaseOrder' name='purchaseOrder'
           value="@if(isset($purchaseOrder)) {{$purchaseOrder}} @else @endif"/>
    <input type="hidden" id='sku' name='sku' value="@if(isset($sku)) {{$sku}} @else @endif"/>
    <div class="col-lg-9">
        <div class="col-md-12">
            <div>
                <div>
                    <div class="">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label>条码号</label>
                                <input type="text" class="form-control" name="barcodeText" id="barcodeText" value=""
                                       required>
                            </div>
                            <div class="form-group">
                                <label style="color:#ff0000; font-size: x-small">@if(isset($msg)){{$msg}} @else @endif</label>
                            </div>
                        </div>
                        <div style="clear:both;"></div>

                        <div id="msgDiv"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(function () {
        $('#barcodeText').focus();
    });

    $('#barcodeText').keyup(function () {
        $barcodeText = $('#barcodeText').val().trim();

        if ($barcodeText == '') {
            $('#barcodeText').focus();
            return false;
        }
        //条码位数正确，才能提交
        if ($barcodeText.length != 12) {
            $('#barcodeText').focus();
            return false;
        }
        $('#scanForm').submit();
    });

</script>
</body>
</html>

