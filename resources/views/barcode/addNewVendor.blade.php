<!doctype html>
<html>
<head>
    <title>新增供应商</title>
    <style>
        .btn-highlight {
            background-color: #63C5D1;
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
    <form id="theForm" method="post" action="/barcode/saveNewVendor">
        {{ csrf_field() }}
        <div align="center">
            <label align="center" style="font-size: 32px">新增供应商</label>
        </div>
        <div>输入供应商代码(VOP)</div>
        <div>
            <input type="text" name="vendorCode" id="vendorCode" value="" oninput="value=value.replace(/[^A-Z]/g,'')" maxlength="4" />
        </div>
        <div>输入供应商代码(SAP)</div>
        <div>
            <input type="text" name="vendorCodeFromSAP" id="vendorCodeFromSAP" value="" oninput="value=value.replace(/[^\d]/g,'')" maxlength="10" />
        </div>
        <div>输入供应商名称</div>
        <div style="font-size: x-small; color: #ff0000">（地区+简称，例如：深圳价之链）</div>
        <div>
            <input type="text" class="form-control" name="vendorName" id="vendorName" value=""/>
        </div>
        <div>采购人员</div>
        <select   class="form-control"
                style="width: 258px;height: 26px"
                data-live-search="true">
            <option value="">请选择</option>
            @foreach ($operators as $operator_id=>$operator_name)
                <option id="operatorId" name="operatorId" value="{{$operator_id}}" >{{$operator_name}}</option>
            @endforeach
        </select>
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
        $vendorCodeFromSAP = $('#vendorCodeFromSAP').val().trim();
        $vendorName = $('#vendorName').val().trim();
        $operatorId = $('select.form-control').val();
        if ($vendorCode == '') {
            alert('没有输入供应商代码(VOP)');
            $('#vendorCode').focus();
            return false;
        }
        if ($vendorCodeFromSAP == '') {
            alert('没有输入供应商代码(SAP)');
            $('#vendorCodeFromSAP').focus();
            return false;
        }
        if ($vendorName == '') {
            alert('没有输入供应商名称');
            $('#vendorName').focus();
            return false;
        }
        if ($operatorId == '') {
            alert('没有选择采购人员');
            $('#operatorId').focus();
            return false;
        }

        if ($vendorCode.length != 4) {
            alert('供应商代码(VOP)必须为4位');
            $('#vendorCode').focus();
            return false;
        }
        if ($vendorCodeFromSAP.length != 10) {
            alert('供应商代码(SAP)必须为10位，请重新输入');
            $('#vendorCode').focus();
            return false;
        }
        //防止重复提交
        $('#btn-submit').attr("disabled", true);
        $("#divloading").show();

        $.ajax({
            type: 'post',
            url: '/barcode/saveNewVendor',
            data: {
                vendorCode: $vendorCode,
                vendorCodeFromSAP: $vendorCodeFromSAP,
                vendorName: $vendorName,
                operatorId: $operatorId,
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
                    if (res.flag == 1) {
                        $('#vendorCode').val('');
                        $('#vendorCodeFromSAP').val('');
                        $('#vendorName').val('');
                    }
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

