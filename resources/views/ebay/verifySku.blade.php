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

    button {
        width: 80px;
    }
</style>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>

<div style="height: 40px;"></div>
<form id="skuForm" method="post" action="/eBayOrderList/updateSkuTable">
    {{ csrf_field() }}
    <input type="hidden" id="skuId" name="skuId" value="">
    <input type="hidden" id="updateMethod" name="updateMethod" value="">
@if(count($inactiveSkuMatch) > 0)
<table align="center" id="inactiveSKuMatchTable">
    <tr>
        <th>平台SKU</th>
        <th>平台SKU的单位数量</th>
        <th>SAP SKU</th>
        <th>SAP SKU的数量</th>
        <th>仓库</th>
        <th>工厂</th>
        <th>实际运输方式</th>
        <th>操作</th>
    </tr>
    @foreach($inactiveSkuMatch as $val)
    <tr>
        <td>{{$val['ebay_sku']}}</td>
        <td>{{$val['s_qty']}}</td>
        <td>{{$val['sap_sku']}}</td>
        <td>{{$val['t_qty']}}</td>
        <td>{{$val['warehouse']}}</td>
        <td>{{$val['factory']}}</td>
        <td>{{$val['shipment_code']}}</td>
        <td><button class="btn-confirm" valueId="{{$val['id']}}">审核通过</button>&nbsp;&nbsp;<button class="btn-delete" valueId="{{$val['id']}}">删除</button></td>
    </tr>
    @endforeach
</table>
@endif
</form>

<script type="text/javascript">
    $('.btn-confirm').click(function () {
        $skuId = $(this).attr('valueId');
        $('#skuId').val($skuId);
        $('#updateMethod').val('confirm');
        $('#skuForm').submit();

    });

    $('.btn-delete').click(function () {
        $skuId = $(this).attr('valueId');
        $('#skuId').val($skuId);
        $('#updateMethod').val('delete');
        $('#skuForm').submit();
    });

</script>
