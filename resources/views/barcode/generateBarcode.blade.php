<!doctype html>
<html>
<head>
    <title>生成条码</title>
    <style>
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
            height: 20px;
            width: 250px;
        }
    </style>

</head>
<body>
<div align="center">
    <form id="theForm" method="post" action="/barcode/saveBarcode">
        {{ csrf_field() }}
        <div align="center">
            <label align="center" style="font-size: 32px">生成条码</label>
        </div>
        <div>采购订单号</div>
        <div>
            <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder" value=""/>
        </div>
        <div align="center">
            <button type="button" id="btn-submit" class="btn-submit">提交</button>
        </div>
    </form>
    <div align="center" id="divloading" style="display:none">
        <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"
             style="width:24px;height:24px;">
    </div>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $('#btn-submit').click(function () {
        $purchaseOrder = $('#purchaseOrder').val().trim();
        if ($purchaseOrder == '') {
            alert('没有输入采购订单号');
            $('#purchaseOrder').focus();
            return false;
        }
        if ($purchaseOrder.length < 5) {
            alert('采购订单号需不少于5位');
            $('#purchaseOrder').focus();
            return false;
        }
        //防止重复提交
        $('#btn-submit').attr("disabled", true);
        $("#divloading").show();

        $.ajax({
            type: 'post',
            url: '/barcode/saveBarcode',
            data: {
                // vendorCode: $vendorCode,
                purchaseOrder: $purchaseOrder,
                // codeCount: $codeCount,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (res) {
                $('#btn-submit').attr("disabled", false);
                $("#divloading").hide();
                console.log(res)

                if (res) {
                    alert(res.msg);
                    $('#btn-submit').show();
                    $("#divloading").hide();
                    $('#purchaseOrder').val('');
                    // $('#codeCount').val('');
                } else {
                    //编辑失败
                    alert('操作失败');
                    $('#btn-submit').show();
                    $("#divloading").hide();
                    $('#purchaseOrder').val('');
                    // $('#codeCount').val('');
                }
            }
        });
    });

</script>
</body>
</html>

