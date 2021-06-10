<!doctype html>
<html>
<head>
    <title>Detach Barcode</title>
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
<form action="{{ url('/barcode/verifyToken') }}" method="POST" id="scanForm" onsubmit="return checkForm()">
    {{ csrf_field() }}
    <input type="hidden" id='urlParam' name='urlParam' value="@if(isset($urlParam)) {{$urlParam}} @else @endif"/>
    <div class="col-lg-9">
        <div class="col-md-12">
            <div>
                <div>
                    <div class="">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label>Token</label>
                                @if(!isset($token))
                                    <input type="text" class="form-control" name="token" id="token" value=""
                                           required>
                                @else
                                    <input type="text" class="form-control" name="token" id="token"
                                           value="{{$token}}" required>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>采购订单号</label>
                                @if(!isset($purchaseOrder))
                                    <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder"
                                           value=""
                                           required>
                                @else
                                    <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder"
                                           value="{{$purchaseOrder}}" required>
                                @endif
                            </div>
                            <div align="center">
                                <button type="submit" class="btn-submit" style="background-color:#63C5D1">提交</button>
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
        $('#token').focus();
    });

    function checkForm() {
        $token = $('#token').val().trim();
        $purchaseOrder = $('#purchaseOrder').val().trim();

        $urlParam = $('#urlParam').val().trim();

        if ($token == '') {
            $('#token').focus();
            return false;
        }
        if ($purchaseOrder == '') {
            $('#purchaseOrder').focus();
            return false;
        }
        if ($urlParam == '') {
            alert('网址参数为空');
            return false;
        }
        $('#scanForm').submit();
    }

</script>
</body>
</html>

