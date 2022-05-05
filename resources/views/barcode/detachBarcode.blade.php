<!doctype html>
<html>
<head>
    <title>解绑条码</title>
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
<form action="{{ url('/barcode/verifyToken') }}" method="POST" id="scanForm" onsubmit="return checkForm()">
    {{ csrf_field() }}
    <input type="hidden" id='urlParam' name='urlParam' value="@if(isset($urlParam)) {{$urlParam}} @else @endif"/>
    <div align="center">
        <label align="center" style="font-size: 32px">解绑条码</label>
    </div>
    <div>秘钥</div>
    <div>
        <input type="password" class="form-control" name="token" id="token"
                   @if(isset($token)) value="{{$token}}" @else value="" @endif required>
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
    $(function () {
        $('#token').focus();
    });

    function checkForm() {
        $token = $('#token').val().trim();
        $urlParam = $('#urlParam').val().trim();

        if ($token == '') {
            $('#token').focus();
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

