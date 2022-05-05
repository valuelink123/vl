<!doctype html>
<html>
<head>
    <title>更新秘钥</title>
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <style type="text/css">
        .common-btn {
            background-color: #63C5D1;
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            width: 70px;
            height: 30px;
            border-radius: 5px !important;
        }

        input {
            height: 20px;
            width: 250px;
        }
    </style>
</head>
<body>
<div align="center">
    <label align="center" style="font-size: 32px">更新秘钥</label>
</div>
<div style="height: 15px;"></div>
<div align="center">
    <input type="hidden" id="urlParam" value="{{$urlParam}}"/>
    <div style="color: #0000ff">实时有效秘钥</div>
    <div>
        <input type="text" name="validatedToken" id="validatedToken" value="{{$token}}" readonly/>
    </div>
    <div style="height: 10px"></div>
    <div>输入当前秘钥</div>
    <div>
        <input type="text" name="token" id="token" value=""/>
    </div>
    <div style="height: 10px"></div>
    <div align="center">
        <button type="button" id="btn-submit" class="common-btn">刷新</button>
    </div>
    <div>
        <label id='msgLabel' style="color:#ff0000; font-size:28px; margin-top:15px"></label>
    </div>
    <div style="height: 10px"></div>

    <div>新的秘钥</div>
    <div>
        <input type="text" name="newToken" id="newToken" value="" readonly/>
    </div>
    <div style="height: 10px"></div>

    <div>一维码</div>
    <div style="height: 5px"></div>
    <div id="barcodeDiv">
    </div>
    <div style="height: 10px"></div>
    <div>二维码</div>
    <div style="height: 5px"></div>
    <div id="qRDiv">
    </div>
    <div align="center" id="divloading" style="display:none">
        <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"
             style="width:24px;height:24px;">
    </div>
</div>
<script type="text/javascript">
    $('#btn-submit').click(function () {
        $urlParam = $('#urlParam').val().trim();
        $token = $('#token').val().trim();
        if ($urlParam == '') {
            alert('网址参数为空');
            return false;
        }
        if ($token == '') {
            alert('秘钥为空');
            return false;
        }
        $.ajax({
            type: 'post',
            url: '/barcode/generateNewToken',
            data: {
                urlParam: $urlParam,
                token: $token,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (res) {
                $('#btn-submit').attr("disabled", false);
                $("#divloading").hide();
                console.log(res)

                if (res) {
                    if (res.flag == 0) {
                        // alert(res.msg);
                        $('#msgLabel').text(res.msg);
                        $('#msgLabel').css('color', 'red');
                    } else if (res.flag == 1) {
                        // alert(res.msg);
                        $('#msgLabel').text(res.msg);
                        $('#msgLabel').css('color', 'green');
                        $('#newToken').val(res.newToken);
                        $('#barcodeDiv').html(res.tokenBarcode);
                        $('#qRDiv').html(res.tokenQR);
                        $('#validatedToken').val(res.newToken);
                    }

                    $('#btn-submit').show();
                    $("#divloading").hide();
                } else {
                    //编辑失败
                    alert('操作失败');
                    $('#btn-submit').show();
                    $("#divloading").hide();
                }
            }
        });

    });
</script>
</body>
</html>
