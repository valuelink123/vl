<!doctype html>
<html>
<head>
    <title>编辑供应商</title>
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
        <input type="hidden" name="vendorId" id="vendorId" value="{{$vendor['id']}}">
        <input type="hidden" id="vendorCodeOld" value="{{$vendor['vendor_code']}}">
        <input type="hidden" id="vendorCodeFromSAPOld" value="{{$vendor['vendor_code_from_sap']}}">
        <input type="hidden" id="vendorNameOld" value="{{$vendor['vendor_name']}}">
        <div align="center">
            <label align="center" style="font-size: 32px">编辑供应商</label>
        </div>
        <div>输入供应商代码(VOP)</div>
        <div>
            <input type="text" name="vendorCode" id="vendorCode" value="{{$vendor['vendor_code']}}" oninput="value=value.replace(/[^A-Z]/g,'')" maxlength="4" readonly/>
        </div>
        <div>输入供应商代码(SAP)</div>
        <div>
            <input type="text" name="vendorCodeFromSAP" id="vendorCodeFromSAP" value="{{$vendor['vendor_code_from_sap']}}" oninput="value=value.replace(/[^\d]/g,'')" maxlength="10" readonly/>
        </div>
        <div>输入供应商名称</div>
        <div style="font-size: x-small; color: #ff0000">（地区+简称，例如：深圳纽尚）</div>
        <div>
            <input type="text" class="form-control" name="vendorName" id="vendorName" value="{{$vendor['vendor_name']}}"/>
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
        $vendorId = $('#vendorId').val();
        //虽然输入框的属性已设置为required，用户如果只输入多个空格，仍然可以提交，所以需做以下处理：
        $vendorCode = $('#vendorCode').val().trim();
        $vendorCodeFromSAP = $('#vendorCodeFromSAP').val().trim();
        $vendorName = $('#vendorName').val().trim();
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

        if ($('#vendorCodeOld').val().trim() == $vendorCode && $('#vendorCodeFromSAPOld').val().trim() == $vendorCodeFromSAP && $('#vendorNameOld').val().trim() == $vendorName) {
            alert('没有做任何修改');
            return false;
        }

        //防止重复提交
        $('#btn-submit').attr("disabled", true);
        $("#divloading").show();

        $.ajax({
            type: 'post',
            url: '/barcode/modifyVendor',
            data: {
                vendorId: $vendorId,
                vendorCode: $vendorCode,
                vendorCodeFromSAP: $vendorCodeFromSAP,
                vendorName: $vendorName,
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

