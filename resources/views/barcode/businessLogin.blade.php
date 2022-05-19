<!doctype html>
<html>
<head>
    <title>企业登录</title>
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
    </style>
</head>
<body>
<div align="center">
    <label align="center" style="font-size: 32px">企业登录</label>
</div>
<div style="height: 15px;"></div>
<div align="center">
    <button type="button" id="detailBtn" class="common-btn" style="margin-right: 15px">数据库</button>
    <button type="button" id="updateTokenBtn" class="common-btn" style="width: 120px">更改密钥</button>
</div>
<script type="text/javascript">
    $('#detailBtn').click(function () {
        window.location.href="/barcode/purchaseOrderList?p={{$urlParam}}&token={{$token}}";
    });
    $('#updateTokenBtn').click(function () {
        window.location.href="/barcode/updateToken?p={{$urlParam}}";
    });
</script>
</body>
</html>

