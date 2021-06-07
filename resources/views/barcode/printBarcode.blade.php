<!doctype html>
<html>
<head>
    <title>Print Barcode</title>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet"
          type="text/css"/>

    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"
            type="text/javascript"></script>
</head>
<body>
<div class="col-lg-9">
    <form action="{{ url('/barcode/outputBarcode') }}" method="post" id="printForm" target="_blank">
        {{ csrf_field() }}
        <input type="hidden" id="width" name="width" value=""/>
        <input type="hidden" id="height" name="height" value=""/>
        <input type="hidden" id="chunk" name="chunk" value=""/>
        <input type="hidden" id="preview" name="preview" value=""/>
        <input type="hidden" id="pageNum" name="pageNum" value="1"/>
        <div align="center">
            <label align="center"></label>
        </div>
        <div class="form-group">
            <label>供应商</label>
            <input type="text" class="form-control" name="vendorCode" id="vendorCode" value="" required>
        </div>
        <div class="form-group">
            <label>采购订单号</label>
            <input type="text" class="form-control" name="purchaseOrder" id="purchaseOrder" value=""
                   required>
        </div>
    </form>
{{--    <div class="form-group">--}}
{{--        <label for="" style="font-weight: bold;">Paper/Sticker Type:</label>--}}
{{--        <select id="barcodePagesizeSelect" class="form-control">--}}
{{--            <option value="" width="63.5" height="38.1" chunk="21">21-up labels 63.5 * 38.1 mm on--}}
{{--                A4--}}
{{--            </option>--}}
{{--            <option value="" width="63.5" height="33.9" chunk="24">24-up labels 63.5 * 33.9 mm on--}}
{{--                A4--}}
{{--            </option>--}}
{{--            <option value="" width="64.6" height="33.8" chunk="24">24-up labels 64.6 * 33.8 mm on--}}
{{--                A4--}}
{{--            </option>--}}
{{--            <option value="" width="66" height="33.9" chunk="24">24-up labels 66 * 33.9 mm on A4--}}
{{--            </option>--}}
{{--            <option value="" width="66" height="35" chunk="24">24-up labels 66 * 35 mm on A4--}}
{{--            </option>--}}
{{--            <option value="" width="70" height="36" chunk="24">24-up labels 70 * 36 mm on A4--}}
{{--            </option>--}}
{{--            <option value="" width="70" height="37" chunk="24">24-up labels 70 * 37 mm on A4--}}
{{--            </option>--}}
{{--            <option value="" width="63.5" height="29.6" chunk="27">27-up labels 63.5 * 29.6 mm on--}}
{{--                A4--}}
{{--            </option>--}}
{{--            <option value="" width="52.5" height="29.7" chunk="40">40-up labels 52.5 * 29.7 mm on--}}
{{--                A4--}}
{{--            </option>--}}
{{--            <option value="" width="48.5" height="25.4" chunk="44">44-up labels 48.5 * 25.4 mm on--}}
{{--                A4--}}
{{--            </option>--}}
{{--        </select>--}}
{{--    </div>--}}
    <div align="center">
        <button type="button" class="btn-print" isPreview="0" style="background-color:#63C5D1">打印
        </button>
    </div>
    <div style="clear:both;"></div>
    <div id="msgDiv"></div>
</div>
<script type="text/javascript">
    $('.btn-print').click(function () {
        // $isPreview = $(this).attr('isPreview');
        $vendorCode = $('#vendorCode').val().trim();
        $purchaseOrder = $('#purchaseOrder').val().trim();

        if ($vendorCode == '') {
            alert('请输入供应商代码');
            $('#vendorCode').focus();
            return false;
        }
        if ($purchaseOrder == '') {
            alert('请输入采购订单号');
            $('#purchaseOrder').focus();
            return false;
        }
        // $width = $("#barcodePagesizeSelect").find("option:selected").attr("width");
        // $height = $("#barcodePagesizeSelect").find("option:selected").attr("height");
        // $chunk = $("#barcodePagesizeSelect").find("option:selected").attr("chunk");

        // location.href = "/barcode/downloadPDF?vendorCode=" + $vendorCode + '&purchaseOrder=' + $purchaseOrder + '&width=' + $width + '&height=' + $height + '&chunk=' + $chunk + '&preview=' + $isPreview;

        // $('#width').val($width);
        // $('#height').val($height);
        // $('#chunk').val($chunk);
        // $('#preview').val($isPreview);
        $('#printForm').submit();
    });

</script>
</body>
</html>

