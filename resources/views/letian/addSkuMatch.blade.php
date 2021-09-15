<style>
    table, th, td {
        border: 1px solid black;
        text-align: center;
    }

    table {
        width: 1200px;
        border-collapse: collapse;
    }

    td input {
        width: 100%;
        height: 22px;
        border: 1px solid #dddddd;
    }

    div {
        text-align: center;
    }

    .btn-common{
        width: 80px;
        height: 30px;
        background-color: #217ebd;
        color:#FFFFFF;
    }

</style>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>

<div style="height: 20px;"></div>
            <div>
                @if(!empty($letianSKuString))
                <div align="left">没有建立有效匹配的平台SKU如下：<span style="color:#ff0000;font-weight: bold">（请添加平台SKU和SAP_SKU对照，否则有的订单会缺失SKU）</span></div>
                <div align="left">{{$letianSKuString}}</div>
                @else
                @endif
            </div>
            <div style="height:10px"></div>

<div style="height: 20px;"></div>
<div style="font-size: x-small; color: #ff0000">如果是捆绑销售，则分为多行显示，比如AP3126_B + AP3128:
    <table align="center" style="width:400px">
        <tr style="font-size: x-small;"><td>AP3126_B + AP3128</td><td>1</td><td>AP3126_B</td><td>1</td></tr>
        <tr style="font-size: x-small;"><td>AP3126_B + AP3128</td><td>1</td><td>AP3128</td><td>1</td></tr>
    </table>
</div>
<div style="height: 5px;"></div>
<div align="center">新增平台SKU和SAP SKU的对应关系：</div>
<table align="center">
    <tr>
        <th>平台SKU</th>
        <th>平台SKU的单位数量</th>
        <th>SAP SKU</th>
        <th>SAP SKU的数量</th>
        <th>仓库</th>
        <th>工厂</th>
        <th>实际运输方式</th>
    </tr>
    <tr>
        <td><input type="text" class="formElement" name="letian_sku" id="letian_sku"/></td>
        <td><input type="number" class="formElement" name="s_qty" id="s_qty" value="1"/></td>
        <td><input type="text" class="formElement" name="sap_sku" id="sap_sku"/></td>
        <td><input type="number" class="formElement" name="t_qty" id="t_qty"/></td>
        <td><input type="text" class="formElement" name="warehouse" id="warehouse"/></td>
        <td><input type="text" class="formElement" name="factory" id="factory"/></td>
        <td><input type="text" class="formElement" name="shipment_code" id="shipment_code"/></td>
    </tr>
</table>
<div style="height: 10px;"></div>
<div align="center">
    <button id="btn-submit" class="btn-common">提交</button>
</div>
<div style="height: 5px;"></div>
<div style="font-size: x-small; color: #ff0000">（提交后，新增的记录将会出现在下表中：）</div>
<div style="height: 20px;"></div>
<div align="center">新增的SKU对照：</div>
<div style="height: 5px;"></div>

<table align="center" id="inactiveSKuMatchTable">
    <tr>
        <th width="15%">平台SKU</th>
        <th width="14%">平台SKU的单位数量</th>
        <th width="14%">SAP SKU</th>
        <th width="14%">SAP SKU的数量</th>
        <th width="14%">仓库</th>
        <th width="14%">工厂</th>
        <th width="15%">实际运输方式</th>
    </tr>
</table>

<script type="text/javascript">
    $('#btn-submit').click(function () {
        $letian_sku = $('#letian_sku').val().trim();
        $s_qty = $('#s_qty').val().trim();
        $sap_sku = $('#sap_sku').val().trim();
        $t_qty = $('#t_qty').val().trim();
        $warehouse = $('#warehouse').val().trim();
        $factory = $('#factory').val().trim();
        $shipment_code = $('#shipment_code').val().trim();
        if ($letian_sku == '' || $s_qty == '' || $sap_sku == '' || $t_qty == '' || $warehouse == '' || $factory == '' || $shipment_code == '') {
            alert('请填写所有内容');
            return false;
        }
        if ($s_qty < 1 || $t_qty < 1) {
            alert('平台SKU的单位数量 和 SAP SKU的数量 都必须大于0');
            return false;
        }
        $('#btn-submit').attr("disabled", true);
        $.ajax({
            type: 'post',
            url: '/letianOrderList/refreshSkuMatchTable',
            data: {
                letian_sku: $letian_sku,
                s_qty: $s_qty,
                sap_sku: $sap_sku,
                t_qty: $t_qty,
                warehouse: $warehouse,
                factory: $factory,
                shipment_code: $shipment_code,
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success: function (res) {
                $('#btn-submit').attr("disabled", false);
                if (res) {
                    if (res.flag == 1 || res.flag == 2) {
                        $('.formElement').val('');
                        $('#inactiveSKuMatchTable').append(res.msg);
                        if (res.flag == 2) {
                            location.href = '/letianOrderList/skuMatchList';
                        }
                    } else {
                        alert(res.msg);
                    }
                } else {

                }
            }
        });
    });

</script>