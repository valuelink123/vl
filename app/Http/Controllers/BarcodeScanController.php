<?php

namespace App\Http\Controllers;

use common\libs\RedisHandle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Cache;
use Picqer\Barcode\BarcodeGeneratorPNG;
use qrcode\QRcode;

class BarcodeScanController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function businessLogin(Request $request)
    {
        $urlParam = $request->input("p");
        $vendor = DB::table('barcode_vendor_info')->where('url_param', $urlParam)->first();
        $urlParam='';
        $token='';
        if($vendor){
            $urlParam=$vendor['url_param'];
            $token = $vendor['token'];
        }
        return view('barcode/businessLogin', compact('urlParam','token'));
    }

    public function scanDetach(Request $request)
    {
        $urlParam = $request->input("p");
        return view('barcode/scanDetach', ['urlParam' => $urlParam]);
    }

    public function scanBarcode(Request $request)
    {
        $urlParam = $request->input("p");
        return view('barcode/scanBarcode', ['urlParam' => $urlParam]);
    }

    public function checkToken(Request $request)
    {
        $token = $request->input("token");
        $urlParam = $request->input("urlParam");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $msg = 'FAIL: 秘钥和网址参数不匹配';
            return view('barcode/scanBarcode', compact('token', 'urlParam', 'msg'));
        }
        return view('barcode/scanBarcodeStep1', compact('token', 'urlParam'));
    }

    public function checkPoSku(Request $request)
    {


        $token = $request->input("token");
        $purchaseOrder = $request->input("purchaseOrder");
        $sku = strtoupper($request->input("sku"));
        $urlParam = $request->input("urlParam");
        $min = $request->input("min");
        $max = $request->input("max");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $msg = 'FAIL: 秘钥和网址参数不匹配';
            return view('barcode/scanBarcode', compact('token', 'urlParam', 'msg'));
        }
        $vendorCode = $barcodeVendorInfo['vendor_code'];

        $vendorInfo = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->first();
        $vendorInfo = json_decode(json_encode($vendorInfo), true);
        if (!$vendorInfo) {
            $msg = 'FAIL: 系统中不存在此采购订单号';
            return view('barcode/scanBarcodeStep1', compact('token', 'purchaseOrder', 'sku', 'urlParam', 'msg'));
        }
        $poDetails = DB::table('barcode_po_details')->where('purchase_order', $purchaseOrder)->where('sku', $sku)->get()->toArray();
        $poDetails = json_decode(json_encode($poDetails), true);

        if (!$poDetails) {
            $msg = 'FAIL: 采购订单号下没有该SKU';
            return view('barcode/scanBarcodeStep1', compact('token', 'purchaseOrder', 'sku', 'urlParam', 'msg'));
        }
        $skuQty = 0;
        foreach ($poDetails as $poDetail) {
            $skuQty += intval($poDetail['quantity']);
        }
        //$cache = Cache('key', 'value');
        Cache::store('file')->put($purchaseOrder.'-'.$sku, $min.'-'.$max, 3600);
        $range = Cache::store('file')->get($purchaseOrder.'-'.$sku);
//        $cache = Cache::store('file')->get('key');
//        dd($cache);die;
//        RedisHandle::hSet($purchaseOrder.'-'.$sku, $min.'-'.$max);
        $activatedCount = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('sku', $sku)->count();
        return view('barcode/scanBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'sku', 'skuQty', 'activatedCount', 'urlParam'));
    }


    public function activateBarcode(Request $request)
    {
        $vendorCode = $request->input("vendorCode");
        $purchaseOrder = strtoupper($request->input("purchaseOrder"));
        $sku = strtoupper($request->input("sku"));
        $barcodeText = $request->input("barcodeText");
        $weight = $request->input("weight");

        if (strlen($barcodeText) != 12) {
            $flag = 0;
            $msg = 'FAIL: 条码号必须为12位';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        $skuQty = 0;
        $poDetails = DB::table('barcode_po_details')->where('purchase_order', $purchaseOrder)->where('sku', $sku)->get()->toArray();
        $poDetails = json_decode(json_encode($poDetails), true);
        foreach ($poDetails as $poDetail) {
            $skuQty += intval($poDetail['quantity']);
        }
        $activatedCount = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('sku', $sku)->count();
        if ($activatedCount >= $skuQty) {
            $flag = 0;
            $msg = 'FAIL: ' . $sku . '已全部激活';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
       }
        //验证条形码是否符合规则，
        //供应商4位+年月2位+校准1位+流水号4位+校准1位=12位
        $vendorYearMonth = substr($barcodeText, 0, 6);
        $sn = substr($barcodeText, -5, 4);
        $v1 = substr($barcodeText, -6, 1);
        $v0 = substr($barcodeText, -1, 1);
        $validationCode = $this->getValidationCode($vendorYearMonth . $sn);
        if ($validationCode != $v0 . $v1) {
            $flag = 0;
            $msg = 'FAIL: 条码号不符合规则';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }
        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText)->first();
        $barcodeScanRecord = json_decode(json_encode($barcodeScanRecord), true);
        if (!$barcodeScanRecord) {
            $flag = 0;
            $msg = 'FAIL: 该采购订单号下面没有此条码';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        if ($barcodeScanRecord['current_status'] == 1) {
            $flag = 0;
            $msg = 'FAIL: 条码已经被激活';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        //增加重量区间判断
        if($weight){
            $range = Cache::store('file')->get($purchaseOrder.'-'.$sku);
            $rangeArr = explode('-', $range);
            if(!isset($rangeArr[0]) || !isset($rangeArr[1]) || $weight < (int)$rangeArr[0] || $weight > (int)$rangeArr[1]){
                $flag = 0;
                $msg = 'FAIL: 重量不符合要求';
                die(json_encode(array('flag' => $flag, 'msg' => $msg)));
            }
        }


        $updateArray = array(
            'sku' => $sku,
            'current_status' => 1,
            'weight' => $weight,
            'status_updated_at' => date('Y-m-d H:i:s', time())
        );
        //第一次扫描激活时，status_history为空，将其值设置为0
        $statusHistory = '0';
        if ($barcodeScanRecord['status_history']) {
            $statusHistory = $barcodeScanRecord['status_history'];
        }

        //激活成功，则会在status_history后面添加,1
        $updateArray['status_history'] = $statusHistory . ',1';
        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText);
        $barcodeScanRecord->update($updateArray);

        $activatedCount++;
        $flag = 1;
        $msg = 'PASS: 条码激活成功';
        die(json_encode(array('flag' => $flag, 'msg' => $msg, 'activatedCount' => $activatedCount)));
    }

    public function detachBarcode(Request $request)
    {
        $urlParam = $request->input("p");
        return view('barcode/detachBarcode', ['urlParam' => $urlParam]);
    }

    public function verifyToken(Request $request)
    {
        $token = $request->input("token");
        $urlParam = $request->input("urlParam");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $msg = 'FAIL: 秘钥和网址参数不匹配';
            return view('barcode/detachBarcode', compact('token', 'urlParam', 'msg'));
        }

        return view('barcode/detachBarcodeStep1', compact('token', 'urlParam'));
    }

    public function verifyPo(Request $request)
    {
        $token = $request->input("token");
        $purchaseOrder = $request->input("purchaseOrder");
        $urlParam = $request->input("urlParam");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $msg = 'FAIL: 秘钥和网址参数不匹配';
            return view('barcode/detachBarcode', compact('token', 'urlParam', 'msg'));
        }
        $vendorCode = $barcodeVendorInfo['vendor_code'];
        $vendorInfo = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->first();
        $vendorInfo = json_decode(json_encode($vendorInfo), true);
        if (!$vendorInfo) {
            $msg = 'FAIL: 系统中不存在此采购订单号';
            return view('barcode/detachBarcodeStep1', compact('token', 'purchaseOrder', 'urlParam', 'msg'));
        }

        return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder'));
    }

    public function deactivateBarcode(Request $request)
    {
        $vendorCode = $request->input("vendorCode");
        $purchaseOrder = $request->input("purchaseOrder");
        $barcodeText = $request->input("barcodeText");

        if(strlen($barcodeText) != 12){
            $flag = 0;
            $msg = 'FAIL: 条码号必须为12位';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        //验证条形码是否符合规则，
        //供应商4位+年月2位+校准1位+流水号4位+校准1位=12位
        $vendorYearMonth = substr($barcodeText, 0, 6);
        $sn = substr($barcodeText, -5, 4);
        $v1 = substr($barcodeText, -6, 1);
        $v0 = substr($barcodeText, -1, 1);
        $validationCode = $this->getValidationCode($vendorYearMonth . $sn);

        if ($validationCode != $v0 . $v1) {
            $flag = 0;
            $msg = 'FAIL: 条码号不符合规则';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText)->first();
        $barcodeScanRecord = json_decode(json_encode($barcodeScanRecord), true);
        if (!$barcodeScanRecord) {
            $flag = 0;
            $msg = 'FAIL: 该采购订单号下面没有此条码';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        if ($barcodeScanRecord['current_status'] == 0) {
            $flag = 0;
            $msg = 'FAIL: 条码尚未被激活';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }
        if ($barcodeScanRecord['current_status'] == 2) {
            $flag = 0;
            $msg = 'FAIL: 条码已经处于解绑状态';
            die(json_encode(array('flag' => $flag, 'msg' => $msg)));
        }

        $updateArray = array(
            'sku' => '',
            'current_status' => 2,
            'status_updated_at' => date('Y-m-d H:i:s', time())
        );

        //解绑成功，则会在status_history后面添加,2
        $updateArray['status_history'] = $barcodeScanRecord['status_history'] . ',2';
        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText);
        $barcodeScanRecord->update($updateArray);
        $flag = 1;
        $msg = 'PASS: 条码解绑成功';
        die(json_encode(array('flag' => $flag, 'msg' => $msg)));
    }

    public function updateToken(Request $request)
    {
        $urlParam = $request->input("p");
        $vendor = DB::table('barcode_vendor_info')->where('url_param', $urlParam)->first();
        if (!$vendor) {
            die('网址参数不正确');
        }
        $token = $vendor->token;
        return view('barcode/updateToken', compact('urlParam', 'token'));
    }

    public function generateNewToken(Request $request)
    {
        $token = $request->input("token");
        $urlParam = $request->input("urlParam");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $flag = 0;
            $msg = 'FAIL: 秘钥和网址参数不匹配';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

        $newToken = md5($token);
        //一维码
        $generator = new BarcodeGeneratorPNG();
        $tokenBarcode = $generator->getBarcode($newToken, $generator::TYPE_CODE_93, 2, 40);
        $tokenBarcode = base64_encode($tokenBarcode);
        $tokenBarcode = '<img src="data:image/png;base64,' . $tokenBarcode . '"/>';
        //二维码
//        ob_start();
//        QRCode::png($newToken, false, 'M', 5, 2);
//       $imageString = base64_encode(ob_get_contents());
//        ob_end_clean();
//        $tokenQR = '<img src="data:image/png;base64,' . $imageString . '"/>';

        $flag = 1;
        $msg = 'PASS: 更新成功';
        $returnData = json_encode(array('flag' => $flag, 'msg' => $msg, 'newToken' => $newToken, 'tokenBarcode' => $tokenBarcode, 'tokenQR' => ''));
        echo $returnData;
        //数据库中更新token
        DB::table('barcode_vendor_info')->where('token', $token)->update(array('token' => $newToken));
    }

    //得到2位验证码
    public function getValidationCode($str)
    {
        $baseDec = '0123456789';
        $base34 = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $crcCode = sprintf('%u', crc32($str)); //生成校验码
        $validationCode = $this->convBase($crcCode, $baseDec, $base34);
        $validationCode = substr((string)$validationCode, -2, 2); //得到校验码后两位,校验码为数字
        return $validationCode;
    }

    //Convert an arbitrarily large number from any base to any base.
    public function convBase($numberInput, $fromBaseInput, $toBaseInput)
    {
        if ($fromBaseInput == $toBaseInput) return $numberInput;
        $fromBase = str_split($fromBaseInput, 1);
        $toBase = str_split($toBaseInput, 1);
        $number = str_split($numberInput, 1);
        $fromLen = strlen($fromBaseInput);
        $toLen = strlen($toBaseInput);
        $numberLen = strlen($numberInput);
        $retval = '';
        if ($toBaseInput == '0123456789') {
            $retval = 0;
            for ($i = 1; $i <= $numberLen; $i++)
                $retval = bcadd($retval, bcmul(array_search($number[$i - 1], $fromBase), bcpow($fromLen, $numberLen - $i)));
            return $retval;
        }
        if ($fromBaseInput != '0123456789')
            $base10 = convBase($numberInput, $fromBaseInput, '0123456789');
        else
            $base10 = $numberInput;
        if ($base10 < strlen($toBaseInput))
            return $toBase[$base10];
        while ($base10 != '0') {
            $retval = $toBase[bcmod($base10, $toLen)] . $retval;
            $base10 = bcdiv($base10, $toLen, 0);
        }
        return $retval;
    }

}
