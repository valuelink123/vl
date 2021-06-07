<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class BarcodeScanController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function scanBarcode(Request $request)
    {
        $urlParam = $request->input("p");
        return view('barcode/scanBarcode', ['urlParam' => $urlParam]);
    }

    public function checkToken(Request $request)
    {
        $token = $request->input("token");
        $purchaseOrder = $request->input("purchaseOrder");
        $sku = strtoupper($request->input("sku"));
        $urlParam = $request->input("urlParam");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $msg = 'Token和网址参数不匹配';
            return view('barcode/scanBarcode', compact('token', 'purchaseOrder', 'sku', 'urlParam', 'msg'));
        }
        $vendorCode = $barcodeVendorInfo['vendor_code'];
        $vendorInfo = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->first();
        $vendorInfo = json_decode(json_encode($vendorInfo), true);
        if (!$vendorInfo) {
            $msg = '系统中不存在此采购订单号';
            return view('barcode/scanBarcode', compact('token', 'purchaseOrder', 'sku', 'urlParam', 'msg'));
        }

        return view('barcode/scanBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'sku', 'urlParam'));
    }

    public function activateBarcode(Request $request)
    {
        $vendorCode = $request->input("vendorCode");
        $purchaseOrder = strtoupper($request->input("purchaseOrder"));
        $sku = strtoupper($request->input("sku"));
        $barcodeText = $request->input("barcodeText");
        //验证条形码是否符合规则，
        //供应商4位+年月2位+校准1位+流水号4位+校准1位=12位
        $vendorYearMonth = substr($barcodeText, 0, 6);
        $sn = substr($barcodeText, -5, 4);
        $v1 = substr($barcodeText, -6, 1);
        $v0 = substr($barcodeText, -1, 1);
        $validationCode = $this->getValidationCode($vendorYearMonth . $sn);
        if ($validationCode != $v0 . $v1) {
            $msg = '条码号不符合规则';
            return view('barcode/scanBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'sku', 'msg'));
        }
        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText)->first();
        $barcodeScanRecord = json_decode(json_encode($barcodeScanRecord), true);
        if (!$barcodeScanRecord) {
            $msg = '该采购订单号下面没有此条码';
            return view('barcode/scanBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'sku', 'msg'));
        }

        if ($barcodeScanRecord['current_status'] == 1) {
            $msg = '条码已经被激活';
            return view('barcode/scanBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'sku', 'msg'));
        }
        $updateArray = array(
            'sku' => $sku,
            'current_status' => 1,
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
        $msg = '条码激活成功';

        return view('barcode/scanBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'sku', 'msg'));
    }


    public function detachBarcode(Request $request)
    {
        $urlParam = $request->input("p");
        return view('barcode/detachBarcode', ['urlParam' => $urlParam]);
    }

    public function verifyToken(Request $request)
    {
        $token = $request->input("token");
        $purchaseOrder = $request->input("purchaseOrder");
        $urlParam = $request->input("urlParam");
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('token', $token)->where('url_param', $urlParam)->first();
        $barcodeVendorInfo = json_decode(json_encode($barcodeVendorInfo), true);
        if (!$barcodeVendorInfo) {
            $msg = 'Token和网址参数不匹配';
            return view('barcode/detachBarcode', compact('token', 'purchaseOrder', 'urlParam', 'msg'));
        }
        $vendorCode = $barcodeVendorInfo['vendor_code'];
        $vendorInfo = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->first();
        $vendorInfo = json_decode(json_encode($vendorInfo), true);
        if (!$vendorInfo) {
            $msg = '系统中不存在此采购订单号';
            return view('barcode/detachBarcode', compact('token', 'purchaseOrder', 'urlParam', 'msg'));
        }

        return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'urlParam'));
    }

    public function deactivateBarcode(Request $request)
    {
        $vendorCode = $request->input("vendorCode");
        $purchaseOrder = $request->input("purchaseOrder");
        $barcodeText = $request->input("barcodeText");

        //验证条形码是否符合规则，
        //供应商4位+年月2位+校准1位+流水号4位+校准1位=12位
        $vendorYearMonth = substr($barcodeText, 0, 6);
        $sn = substr($barcodeText, -5, 4);
        $v1 = substr($barcodeText, -6, 1);
        $v0 = substr($barcodeText, -1, 1);
        $validationCode = $this->getValidationCode($vendorYearMonth . $sn);

        if ($validationCode != $v0 . $v1) {
            $msg = '条码号不符合规则';
            return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'msg'));
        }

        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText)->first();
        $barcodeScanRecord = json_decode(json_encode($barcodeScanRecord), true);
        if (!$barcodeScanRecord) {
            $msg = '该采购订单号下面没有此条码';
            return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'msg'));
        }

        if ($barcodeScanRecord['current_status'] == 0) {
            $msg = '条码尚未被激活';
            return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'msg'));
        }
        if ($barcodeScanRecord['current_status'] == 2) {
            $msg = '条码已经处于解绑状态';
            return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'msg'));
        }

        $updateArray = array(
            'sku' => '',
            'current_status' => 2,
            'status_updated_at' => date('Y-m-d H:i:s', time())
        );

        //激活成功，则会在status_history后面添加,1
        $updateArray['status_history'] = $barcodeScanRecord['status_history'] . ',2';
        $barcodeScanRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('barcode_text', $barcodeText);
        $barcodeScanRecord->update($updateArray);
        $msg = '条码解绑成功';

        return view('barcode/detachBarcodeStep2', compact('vendorCode', 'purchaseOrder', 'msg'));
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
