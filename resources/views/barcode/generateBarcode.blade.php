<!doctype html>
<html>
<head>
    <title>Generate Barcode</title>
    <style>
        .btn-highlight {
            background-color: #63C5D1;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div align="center">
    <form id="theForm" method="post" action="/barcode/saveBarcode">
        {{ csrf_field() }}
        <div align="center">
            <label align="center">Generate Barcode</label>
        </div>
        <div>供应商代码</div>
        <div>
            <input type="text" name="vendorCode" id="vendorCode" value=""/>
        </div>

        <div>采购订单号</div>
        <div>
            <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder" value=""/>
        </div>
        <div>条码数量</div>
        <div>
            <input type="number" class="form-control" name="codeCount" id="codeCount" value=""/>
        </div>
        <div align="center">
            <button type="button" id="btn-submit" class="btn-submit btn-highlight">提交</button>
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
        //虽然输入框的属性已设置为required，用户如果只输入多个空格，仍然可以提交，所以需做以下处理：
        $vendorCode = $('#vendorCode').val().trim();
        $purchaseOrder = $('#purchaseOrder').val().trim();
        $codeCount = $('#codeCount').val().trim();

        if ($vendorCode == '') {
            alert('没有输入供应商代码');
            $('#vendorCode').focus();
            return false;
        }
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
        if ($codeCount == '') {
            alert('没有输入要生成的条码数量');
            $('#codeCount').focus();
            return false;
        }
        if ($codeCount <= 0 || $codeCount > 20000) {
            alert('条码数量必须大于0，且不超过20000');
            $('#codeCount').focus();
            return false;
        }
        //防止重复提交
        $('#btn-submit').attr("disabled", true);
        $("#divloading").show();

        $.ajax({
            type: 'post',
            url: '/barcode/saveBarcode',
            data: {
                vendorCode: $vendorCode,
                purchaseOrder: $purchaseOrder,
                codeCount: $codeCount,
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
                    $('#codeCount').val('');
                } else {
                    //编辑失败
                    alert('操作失败');
                    $('#btn-submit').show();
                    $("#divloading").hide();
                    $('#btn-submit').show();
                    $("#divloading").hide();
                    $('#purchaseOrder').val('');
                    $('#codeCount').val('');
                }
            }
        });
    });

</script>
</body>
</html>

