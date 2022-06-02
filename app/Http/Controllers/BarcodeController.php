<?php

namespace App\Http\Controllers;

use http\Header;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Picqer\Barcode\BarcodeGeneratorPNG;
use DB;

class BarcodeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */
    public function __construct()
    {
//        $this->middleware('auth');
        parent::__construct();
    }

    public function index()
    {
        if (!Auth::user()->can(['barcode-show'])) die('Permission denied');
        $userId = Auth::user()->id;
        $canChangeOperator = false;
        if (in_array($userId, $this->getPurchasingDirectorIds())) {
            $canChangeOperator = true;
        }

        $isPurchaseEmployee=false;
        if(!$canChangeOperator){
            $userCount = (DB::select("SELECT FOUND_ROWS() as count FROM role_user,roles WHERE role_user.role_id=roles.id AND roles.name='Purchase Employee' AND role_user.user_id=".$userId));
            if($userCount && $userCount[0]->count>0) {
                $isPurchaseEmployee = true;
            }
        }


        return view('barcode/index', compact('canChangeOperator','isPurchaseEmployee'));
    }

    public function getVendorList()
    {
        if (!Auth::user()->can(['barcode-show-vendor'])) die('Permission denied');
        $userId = Auth::user()->id;

        $canChangeOperator = false;
        if (in_array($userId, $this->getPurchasingDirectorIds())) {
            $canChangeOperator = true;
        }

        $data = DB::table('barcode_vendor_info');
        if(!$canChangeOperator){
            $userCount = (DB::select("SELECT FOUND_ROWS() as count FROM role_user,roles WHERE role_user.role_id=roles.id AND roles.name='Purchase Employee' AND role_user.user_id=".$userId));
            if($userCount && $userCount[0]->count>0) {
                $data = DB::table('barcode_vendor_info')->where('operator_id', $userId);
            }
        }
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        if (isset($search['vendorText']) && $search['vendorText']) {
            $vendorText = strtoupper($search['vendorText']);
            $data = $data->where(function ($query) use ($vendorText) {
                $query->where('vendor_code', 'like', '%' . $vendorText . '%')
                    ->orWhere('vendor_code_from_sap', 'like', '%' . $vendorText . '%')
                    ->orWhere('vendor_name', 'like', '%' . $vendorText . '%');
            });
        }

//        if (!in_array($userId, $this->getPurchasingDirectorIds())) {
//            $data = $data->where('operator_id', $userId);
//        }

        $iTotalRecords = $data->get()->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $lists = $data->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $lists = json_decode(json_encode($lists), true);
        $userIdNames = $this->getUserIdNames();
        foreach ($lists as $key => $list) {
            $lists[$key]['id'] = $list['id'];
            $lists[$key]['vendor_code'] = $list['vendor_code'];
            $lists[$key]['vendor_code_from_sap'] = $list['vendor_code_from_sap'];
            $lists[$key]['vendor_name'] = $list['vendor_name'];
            $lists[$key]['operator'] = array_get($userIdNames, intval($list['operator_id'])); //导入的数据，operator_id值为null，所以用intval()
            $enter = '<a href="/barcode/purchaseOrderList?vendorCode=' . $list['vendor_code'] . '" target="_blank">订单列表</a>';
            if (in_array($userId, $this->getPurchasingDirectorIds())) {
                $enter .= '&nbsp;&nbsp;<button style="background-color: #63C5D1"><a href="/barcode/editVendor?vendorId=' . $list['id'] . '" target="_blank">编辑</a></button>';
            }
            $lists[$key]['enter'] = $enter;
        }
        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;

        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function generateBarcode(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-genBarcode'])) die('Permission denied');
        return view('barcode/generateBarcode');
    }

    public function saveBarcode(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-genBarcode'])) die('Permission denied');
        $purchaseOrder = $request->input('purchaseOrder');
        $array_detail['appid'] = env("SAP_KEY");
        $array_detail['method'] = 'getPurchaseOrderDetail';
        $array_detail['gt_table'] = json_encode(array(array('EBELN' => $purchaseOrder)));

        $poDetailsRow = DB::table('barcode_po_details')->where('purchase_order', $purchaseOrder)->get()->count();
        if ($poDetailsRow) {
            $flag = 0;
            $msg = 'FAIL：采购订单号重复，已生成条码';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

        $sign = $this->getSapApiSign($array_detail);
        try {
            $res = file_get_contents('http://' . env("SAP_RFC") . '/rfc_sap_api.php?appid=' . env("SAP_KEY") . '&method=' . $array_detail['method'] . '&gt_table=' . $array_detail['gt_table'] . '&sign=' . $sign);
            //$res = file_get_contents('http://' . env("SAP_RFC") . '/rfc_sap_api.php?appid=' . env("SAP_KEY") . '&method=' . $array_detail['method'] . '&sign=' . $sign);   getPurchaseOrderList
            //
            $result = json_decode($res, true);

            if (!array_get($result, 'RESULT_TABLE')) {
                $flag = 0;
                $msg = 'FAIL： 该采购订单号不存在或者没有数据';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                die($returnData);
            }
            $data = array_get($result, 'RESULT_TABLE');
            $codeCount = 0;
            $poIndex = 0;
            $InsertData = array();
            $vendorCodeFromSap = '';
            //----------------------------------------------------
            $poRow = DB::table('sap_purchase')->where('EBELN', $purchaseOrder)->first();
            $poRow = json_decode(json_encode($poRow), true);
            //---------------------------------------------------
            if($poRow) {
                foreach ($data as $poDetail) {
                    //从PO单中用EBELN匹配数据查询FRGKE判断Z
                    if($poRow['FRGKE'] == 'Z'){
                        $vendorCodeFromSap = $poRow['LIFNR'];
                        $InsertData[$poIndex++] = array(
                            'purchase_order' => $poDetail['EBELN'],
                            'line_number' => $poDetail['EBELP'],
                            'sku' => $poDetail['MATNR'],
                            'quantity' => intval($poDetail['MENGE']),
                            'vendor_code_from_sap' => $poDetail['LIFNR'],
                            'purchase_date' => date('Y-m-d', strtotime($poDetail['BEDAT']))  //20140722 -> 2014-07-22
                        );
                        $codeCount += intval($poDetail['MENGE']);
                    }
                }
            }
            foreach($InsertData as $k => $v){
                $InsertData[$k]['sum_quantity'] = $codeCount;
            }
            if ($poIndex == 0) {
                $flag = 0;
                $msg = 'FAIL： 该采购订单号还没有审批完成';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                die($returnData);
            }

            $user = Auth::user();
            $userId = $user->id;
            $vendor = DB::table('barcode_vendor_info')->where('vendor_code_from_sap', $vendorCodeFromSap)->first();
            if (!$vendor) {
                $flag = 0;
                $msg = 'FAIL： 此供应商已不在活跃列表中';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                die($returnData);
            }
            $vendor = json_decode(json_encode($vendor), true);
            $vendorCode = $vendor['vendor_code'];

            $totalCodeCount = intval($codeCount * 1.1); //总共生成的条码个数
            //检查数据库中是否已存在由（供应商，采购订单号）组合生成的条码，如果有，则不会向数据库中写入数据，并返回提示信息。
            $barcodeRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->first();
            if ($barcodeRecord) {
                $flag = 0;
                $msg = 'FAIL： 基于该供应商和采购订单号的条码已经存在';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                die($returnData);
            }

            $time = date('Y-m-d H:i:s', time());
            $year = substr($time, 0, 4);
            $month = substr($time, 5, 2);
            $yearMonth = substr($time, 0, 7);
            $maxSN = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->whereRaw("SUBSTR(`generated_at`,1,7)='$yearMonth'")->max('sn');
            $nextSN = intval($maxSN) + 1;
            DB::beginTransaction();
            $serial = range($nextSN, $nextSN + $totalCodeCount - 1);

            try {
                DB::table('barcode_po_details')->insert($InsertData);
                $serialChunk = array_chunk($serial, 500);
                $yearCode = $this->getYearCode($year);
                $monthCode = $this->getMonthCode($month);
                $baseDec = '0123456789';
                $base34 = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';

                foreach ($serialChunk as $chunk) {
                    $insertData = array();
                    foreach ($chunk as $k => $v) {
                        $sn = $this->convBase((string)$v, $baseDec, $base34);
                        $sn = strtoupper(sprintf("%04s", $sn));
                        $validationCode = $this->getValidationCode($vendorCode . $yearCode . $monthCode . $sn);
                        $barcodeText = $vendorCode . $yearCode . $monthCode . $validationCode[1] . $sn . $validationCode[0];
                        $insertData[$k] = array(
                            'vendor_code' => $vendorCode,
                            'purchase_order' => $purchaseOrder,
                            'barcode_text' => $barcodeText,
                            'generated_by' => $userId,
                            'generated_at' => $time,
                            'sn' => $v
                        );
                    }
                    DB::table('barcode_scan_record')->insert($insertData);
                    unset($insertData);
                }
                DB::commit();
                $flag = 1;
                $msg = 'PASS： 生成条码成功. 供应商SAP代码：' . $vendorCodeFromSap;
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                die($returnData);

            } catch (\Exception $e) {
                DB::rollBack();
                $flag = 0;
                $msg = 'FAIL： 生成条码失败';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                die($returnData);
            }


        } catch (\Exception $e) {
            $flag = 0;
            $msg = 'FAIL： 从SAP中读取数据失败';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

    }

    //作废
//    public function test(Request $request){
//
//        $purchaseOrder = 4100000012;
//        $poRow = DB::table('sap_purchase')->where('EBELN', $purchaseOrder)->first();
//        $poRow = json_decode(json_encode($poRow), true);
//        dd($poRow['FRGKE']);
//
//
//
//    $array_detail['method'] = 'getPurchaseOrderList';
//    $sign = $this->getSapApiSign($array_detail);
//    $start_date = '20220101';
//    $end_date = '20220401';
//    //$res = file_get_contents('http://' . env("SAP_RFC") . '/rfc_sap_api.php?appid=' . env("SAP_KEY") . '&method=' . $array_detail['method'] . '&gt_table=' . $array_detail['gt_table'] . '&sign=' . $sign);
//    $res = file_get_contents('http://' . env("SAP_RFC") . '/rfc_sap_api.php?appid=' . env("SAP_KEY") . '&method=' . $array_detail['method'] . '&sign=' . $sign. '&start_date=' . $start_date. '&end_date=' . $end_date);
//    //
//    $result = json_decode($res, true);
//    if (!array_get($result, 'RESULT_TABLE')) {
//        $flag = 0;
//        $msg = 'FAIL： 该采购订单号不存在或者没有数据';
//        $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
//        die($returnData);
//    }
//    $data = array_get($result, 'RESULT_TABLE');
//    $dataPo = [];
//    foreach ($data as $value){
//        $data[] = [
//            'EBELN' => isset($value['EBELN']) ? $value['EBELN'] : '',
//            'FRGKE' => isset($value['FRGKE']) ? $value['FRGKE'] : '',
//            'LIFNR' => isset($value['LIFNR']) ? $value['LIFNR'] : '',
//            'EBELP' => isset($value['EBELP']) ? $value['EBELP'] : '',
//            'MATNR' => isset($value['MATNR']) ? $value['MATNR'] : '',
//            'MENGE' => isset($value['MENGE']) ? $value['MENGE'] : '',
//            'BEDAT' => isset($value['BEDAT']) ? $value['BEDAT'] : '',
//        ];
//    }
//    DB::table('po_check')->insert($data);
//    }

    public function printBarcode(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-printBarcode'])) die('Permission denied');
        return view('barcode/printBarcode');
    }

    public function outputBarcode(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $purchaseOrder = $request->input('purchaseOrder');
        //$barcodeSizeType = $request->input('barcocheckPoSkudeSizeType');
        $barcodeSizeType = $request->input('barcodeSizeType');
        $bt = DB::table('barcode_scan_record')->where('purchase_order', $purchaseOrder)->first();
        if (!$bt) {
            die('基于该采购订单号的条码不存在');
        }
        $bt = json_decode(json_encode($bt), true);
        $vendorCode = $bt['vendor_code'];
        $bt = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->orderBy('id', 'asc')->pluck('barcode_text');
        $bt = json_decode(json_encode($bt), true);
        $generator = new BarcodeGeneratorPNG();
        if ($barcodeSizeType == 1) {
            $btChunk = array_chunk($bt, 5); //每行打印的条码个数
            $html = '<html><style type="text/css">.border{float:left;margin-left:9.45px;margin-right:9.45px;margin-top:9.64px;margin-bottom:9.64px;border:1px solid #000;padding-left:7.53px;padding-right:7.53px;padding-top:5.55px;padding-bottom:5.55px;} .bTextDiv{text-align: center;width:102px; height:11px;} div span{font-size:10px;margin-left:-2px;-webkit-transform:scale(0.8);display:block;}</style><body style="margin-top:0px; margin-left:10px;font-family: arial;">';
            echo $html;
            $row = 0;
            $loop = count($btChunk);
            foreach ($btChunk as $chunk) {
                $row++;
                $html = '<div>';
                foreach ($chunk as $barcodeText) {
                    $barcode = $generator->getBarcode($barcodeText, $generator::TYPE_CODE_93, 5, 20, array(0, 0, 0));
                    $barcode = base64_encode($barcode);
                    $html .= '<div class="border">
                                  <div>
                                      <img style="width: 102px; height:14px" src="data:image/png;base64,' . $barcode . '"/>
                                      <div class="bTextDiv"><span>SN:' . $barcodeText . '</span></div>
                                  </div>
                               </div>';
                }
                $html .= '<div style="clear:both"></div></div>';
                if ($row % 17 == 0) {
                    $html .= '<div style="width: 690px;text-align: center;margin:10px 0px;">po: '.$purchaseOrder.'</div><div style="page-break-after: always"></div>';
                }else{
                    if($row == $loop){
                        $html .= '<div style="width: 690px;text-align: center;margin:10px 0px;">po: '.$purchaseOrder.'</div><div style="page-break-after: always"></div>';
                    }
                }
                echo $html;
            }
            $html = '</body></html>';
            echo $html;
        }

    }

    public function exportBarcodePdf(Request $request)
    {
        $purchaseOrder = $request->input('purchaseOrder');
        $barcodeFilePath = storage_path('app/public/barcode');
        $filePath = $barcodeFilePath . '/' . $purchaseOrder . '.pdf';
        if(file_exists($filePath)){
            return response()->download($filePath, $purchaseOrder . '.pdf');
        }else{
            echo '条码pdf文件尚未生成，请稍后再试';
        }

    }

    public function purchaseOrderList(Request $req)
    {
        $p = $req->input('p');
        $token=$req->input('token');
        $sign = $req->input('sign');

        if($sign){
            if(!$p || !$token){
                die('请确认密钥');
            }
            $__sign=md5($p.$token.'vlerp');
            if($sign!=$__sign){
                die('请确认密钥');
            }
        }else{
            if (!Auth::user()->can(['barcode-show-po-list'])) die('Permission denied');
        }



        $vendorCode = $req->input('vendorCode');


        if(!$sign) {
            if (!Auth::user()->can(['barcode-show-po-list'])) die('Permission denied');
        }

        if(!$sign){
            $vendor = DB::table('barcode_vendor_info')->where('vendor_code', $vendorCode)->first();
            $vendor = json_decode(json_encode($vendor), true);
            if(!$vendor){
                die('请选择供应商');
            }
        }else{
            $vendor = DB::table('barcode_vendor_info')->where('url_param', $p)->where('token',$token)->first();
            $vendor = json_decode(json_encode($vendor), true);
            if(!$vendor){
                die('请确认密钥');
            }
        }


        $vendorCode = $vendor['vendor_code'];
        $vendorCodeFromSAP = $vendor['vendor_code_from_sap'];


        $url_param = '************************';
        $scanDetachUrl = url('https://www.baidu.com/s?wd=' . $url_param);
        $updateTokenUrl = url('https://www.baidu.com/s?wd=' . $url_param);
        if ($sign) {
            $token = $vendor['token'];
            $url_param = $vendor['url_param'];
            $scanDetachUrl = url('/barcode/scanDetach?p=' . $url_param.'&sign='.$sign);
            $updateTokenUrl = url('/barcode/businessLogin?p=' . $url_param.'&sign='.$sign);

        }else{
            $token = '*********************************';
            $sign = '********************************';
            if(Auth::user()->can(['barcode-show-vendor-info'])){
                $token = $vendor['token'];
                $url_param = $vendor['url_param'];
                $sign = md5($url_param.$token.'vlerp');
                $scanDetachUrl = url('/barcode/scanDetach?p=' . $url_param.'&sign='.$sign);
                $updateTokenUrl = url('/barcode/businessLogin?p=' . $url_param.'&sign='.$sign);
                $sign='';
            }
        }

        return view('barcode/purchaseOrderList', compact('vendorCode', 'token', 'url_param', 'vendorCodeFromSAP', 'scanDetachUrl', 'updateTokenUrl', 'p','sign'));

    }

    public function getPurchaseOrderList(Request $request)
    {
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        $vendorCode = $search['vendorCode'];
        $p = $search['p'];
        $token = $search['token'];
        $sign = $search['sign'];

        if($sign){
            if(!$p || !$token){
                die('请确认密钥');
            }
            $__sign=md5($p.$token.'vlerp');
            if($sign != $__sign){
                die('请确认密钥');
            }

            $vendor = DB::table('barcode_vendor_info')->where('url_param', $p)->where('token',$token)->first();
            $vendor = json_decode(json_encode($vendor), true);
            if($vendor){
                $vendorCode=$vendor['vendor_code'];
            }

        }else{
            if (!Auth::user()->can(['barcode-show-po-list'])) die('Permission denied');
            if (!(isset($search['vendorCode']) && $search['vendorCode'])) {
                die('没有选择供应商');
            }
        }


        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode);
        if (isset($search['po']) && $search['po']) {
            $po = $search['po'];
            $data = $data->where(function ($query) use ($po) {
                $query->where('purchase_order', '=', $po);
            });
        }
        $data = $data->selectRaw('max(vendor_code) as vendor_code, purchase_order, count(id) as total_barcodes,
                                    count(
                                        CASE current_status
                                          When 1 Then 1
                                          ELSE NULL
                                        END
                                    ) as activated_barcodes')->groupBy('purchase_order');
        $orderby = 'purchase_order';
        $order_column = $request->input('order.0.column', '0');
        if ($order_column == 1 || $order_column == 2) {
            $orderby = 'total_barcodes';
        } else if ($order_column == 3) {
            $orderby = 'activated_barcodes';
        }
        $sort = $request->input('order.0.dir', 'desc');

        $iTotalRecords = $data->get()->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $lists = $data->orderBy($orderby, $sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $lists = json_decode(json_encode($lists), true);
        foreach ($lists as $key => $list) {
            $lists[$key]['purchase_order'] = $list['purchase_order'];
            $poDetail = DB::table('barcode_po_details')->where('purchase_order', $list['purchase_order'])->first();
            $poDetail = json_decode(json_encode($poDetail), true);
            $lists[$key]['purchase_date'] = array_get($poDetail, 'purchase_date');
            $lists[$key]['total_barcodes'] = $list['total_barcodes'];
            $lists[$key]['actual_needed_barcodes'] = $list['total_barcodes'] - intval($list['total_barcodes'] / 11);
            $lists[$key]['activated_barcodes'] = $list['activated_barcodes'];
            if($sign){
                $lists[$key]['details'] = '<a href = "/barcode/purchaseOrderDetails?purchaseOrder=' . $list['purchase_order'] . '&p=' . $search['p'] . '&token=' . $search['token'] .'&sign='.$search['sign'].'" target = "_blank">详情</a > ';
            }else {
                $lists[$key]['details'] = '<a href = "/barcode/purchaseOrderDetails?vendorCode=' . $list['vendor_code'] . '&purchaseOrder=' . $list['purchase_order'] . '" target = "_blank">详情</a > ';
            }
        }
        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;

        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function exportPoList(Request $request)
    {
        $vendorCode = $request->input('vendorCode');
        $po = $request->input('po');
        if (!$vendorCode) {
            die('没有选择供应商');
        }
        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode);
        if ($po) {
            $data = $data->where(function ($query) use ($po) {
                $query->where('purchase_order', '=', $po);
            });
        }
        $data = $data->selectRaw('max(vendor_code) as vendor_code, purchase_order, count(id) as total_barcodes,
                                    sum(
                                        CASE current_status
                                          When 1 Then 1
                                          ELSE 0
                                        END
                                    ) as activated_barcodes')->groupBy('purchase_order')->orderBy('purchase_order', 'desc');

        $data = $data->get()->toArray();
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('供应商代码', '采购订单号', '生成的条码总数', '已激活的条码数');
        $arrayData[] = $headArray;
        foreach ($data as $key => $val) {
            $arrayData[] = array(
                $val['vendor_code'],
                $val['purchase_order'],
                $val['total_barcodes'],
                $val['activated_barcodes'],
            );
        }
        if ($arrayData) {
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $arrayData,  // The data to set
                    NULL,        // Array values with this value will not be set
                    'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $vendorCode . '.xlsx"');
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();

    }

    public function purchaseOrderDetails(Request $req)
    {

        $vendorCode = $req->input('vendorCode');
        $purchaseOrder = $req->input('purchaseOrder');


        $p = $req->input('p');
        $token = $req->input('token');
        $sign = $req->input('sign');

        if (!$purchaseOrder) {
            die('没有选择采购订单号');
        }

        if($sign){
            if(!$p || !$token){
                die('请确认密钥');
            }
            $__sign=md5($p.$token.'vlerp');
            if($sign != $__sign){
                die('请确认密钥');
            }
            $vendor = DB::table('barcode_vendor_info')->where('url_param', $p)->where('token',$token)->first();
            $vendor = json_decode(json_encode($vendor), true);
            if(!$vendor){
                die('请确认密钥');
            }else{
                $vendorCode = $vendor['vendor_code'];
            }

        }else{
            if (!Auth::user()->can(['barcode-show-po-detail'])) die('Permission denied');
        }




        $dateOption = date("Y-m-d");

//        $activatedCount = 0;

        $activatedCount = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('current_status', 1)->whereRAW("SUBSTR(`status_updated_at`,1,10)='".$dateOption."'")->count();

        if($sign){
            return view('barcode/vendorDetails', compact('vendorCode', 'dateOption', 'activatedCount'));
        }else {
            return view('barcode/purchaseOrderDetails', compact('vendorCode', 'dateOption', 'activatedCount', 'purchaseOrder'));
        }
    }

    public function getPurchaseOrderDetails(Request $request)
    {


        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        $p=$search['p'];
        $token=$search['token'];
        $sign=$search['sign'];
        $vendorCode=$search['vendorCode'];
        $purchaseOrder=$search['purchaseOrder'];
        $sku=$search['sku'];
        if (!$purchaseOrder) {
            die('没有选择采购订单号');
        }
        if($sign){
            if(!$p || !$token){
                die('请确认密钥');
            }
            $__sign=md5($p.$token.'vlerp');
            if($sign != $__sign){
                die('请确认密钥');
            }

            $vendor = DB::table('barcode_vendor_info')->where('url_param', $p)->where('token',$token)->first();
            $vendor = json_decode(json_encode($vendor), true);
            if($vendor){
                $vendorCode=$vendor['vendor_code'];
            }
        }else{
            if (!Auth::user()->can(['barcode-show-po-detail'])) die('Permission denied');
        }

        if (!$vendorCode) {
            die('没有选择供应商');
        }

        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder);

        if ($sku ) {
            $data = $data->where(function ($query) use ($sku) {
                $query->where('sku', '=', $sku);
            });
        }



        $orderby = 'status_updated_at';
        $order_column = $request->input('order.0.column', '4');
        if ($order_column == 0) {
            $orderby = 'sku';
        } else if ($order_column == 1) {
            $orderby = 'sn';
        } else if ($order_column == 2) {
            $orderby = 'current_status';
        } else if ($order_column == 4) {
            $orderby = 'status_updated_at';
        }
        $sort = $request->input('order.0.dir', 'desc');
        $iTotalRecords = $data->get()->count();


        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $lists = $data->orderBy($orderby, $sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();


        $lists = json_decode(json_encode($lists), true);


        $userIdNames = $this->getUserIdNames();

        foreach ($lists as $key => $list) {
            $lists[$key]['sku'] = $list['sku'];
            $lists[$key]['barcode_text'] = $list['barcode_text'];
            $lists[$key]['current_status'] = $list['current_status'];
            $lists[$key]['status_history'] = $list['status_history'];
            $lists[$key]['status_updated_at'] = $list['status_updated_at'];
            $lists[$key]['generated_by'] = array_get($userIdNames, $list['generated_by']);
            $lists[$key]['generated_at'] = $list['generated_at'];
            $lists[$key]['printed_by'] = array_get($userIdNames, $list['printed_by']);
            $lists[$key]['qc'] = array_get($userIdNames, $list['qc']);
            if ($list['qc_history']) {
                $qcArray = explode(',', $list['qc_history']);
                $qcNameArray = [];
                foreach ($qcArray as $k => $v) {
                    $qcNameArray[$k] = array_get($userIdNames, intval($v));
                }
                $qcHistory = implode(',', $qcNameArray);
                $lists[$key]['qc_history'] = $qcHistory;
            } else {
                $lists[$key]['qc_history'] = '';
            }
            $lists[$key]['qc_updated_at'] = $list['qc_updated_at'];
        }
        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function getVendorOrderDetails(Request $request)
    {
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        if (!(isset($search['vendorCode']) && $search['vendorCode'])) {
            die('没有选择供应商');
        }
        $vendorCode = $search['vendorCode'];
        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode);
        if (isset($search['sku']) && $search['sku']) {
            $sku = $search['sku'];
            $data = $data->where(function ($query) use ($sku) {
                $query->where('sku', '=', $sku);
            });
        }

        $orderby = 'status_updated_at';
        $order_column = $request->input('order.0.column', '4');
        if ($order_column == 0) {
            $orderby = 'sku';
        } else if ($order_column == 1) {
            $orderby = 'sn';
        } else if ($order_column == 2) {
            $orderby = 'current_status';
        } else if ($order_column == 4) {
            $orderby = 'status_updated_at';
        }
        $sort = $request->input('order.0.dir', 'desc');
        $iTotalRecords = $data->get()->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $lists = $data->orderBy($orderby, $sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $lists = json_decode(json_encode($lists), true);

        foreach ($lists as $key => $list) {
            $lists[$key]['sku'] = $list['sku'];
            $lists[$key]['barcode_text'] = $list['barcode_text'];
            $lists[$key]['current_status'] = $list['current_status'];
            $lists[$key]['status_history'] = $list['status_history'];
            $lists[$key]['status_updated_at'] = $list['status_updated_at'];

        }
        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function exportPoDetails(Request $request)
    {
        $vendorCode = $request->input('vendorCode');
        $purchaseOrder = $request->input('purchaseOrder');
        $sku = $request->input('sku');
        if (!$vendorCode) {
            die('没有选择供应商');
        }
        if (!$purchaseOrder) {
            die('没有选择采购订单号');
        }
        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder);
        if ($sku) {
            $data = $data->where(function ($query) use ($sku) {
                $query->where('sku', '=', $sku);
            });
        }

        $userIdNames = $this->getUserIdNames();
        $data = $data->get()->toArray();
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('sku', '条码', '条码当前状态', '条码历史状态', '条码最后更新时间', '条码生成人', '条码生成时间', '条码打印人', 'QC');
        $arrayData[] = $headArray;
        foreach ($data as $key => $val) {
            $arrayData[] = array(
                $val['sku'],
                $val['barcode_text'],
                $val['current_status'],
                $val['status_history'],
                $val['status_updated_at'],
                array_get($userIdNames, $val['generated_by']),
                $val['generated_at'],
                array_get($userIdNames, $val['printed_by']),
                array_get($userIdNames, $val['qc'])
            );
        }
        if ($arrayData) {
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $arrayData,  // The data to set
                    NULL,        // Array values with this value will not be set
                    'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            header('Content-Disposition: attachment;filename="' . $vendorCode . '_' . $purchaseOrder . '_' . $sku . '.xlsx"');
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();

    }

    public function getSkuInfo(Request $request)
    {
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        $vendorCode = $search['vendorCode'];
        $purchaseOrder = $search['purchaseOrder'];

        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder);

        if (isset($search['sku'])) {
            $sku = $search['sku'];
            $totalNum = 0; //从SAP中获取
            $poDetails = DB::table('barcode_po_details')->where('purchase_order', $purchaseOrder)->where('sku', $sku)->get()->toArray();
            $poDetails = json_decode(json_encode($poDetails), true);
            foreach ($poDetails as $poDetail) {
                $totalNum += intval($poDetail['quantity']);
            }
            $activatedCount = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('sku', $sku)->count();

            if ($sku) {
                $data = $data->where(function ($query) use ($sku) {
                    $query->where('sku', '=', $sku);
                });
                $flag = 1;
                $msg = 'SKU（' . $sku . '）总数:' . $totalNum . '  激活条码:' . $activatedCount;
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                echo $returnData;
            } else {
                $flag = 0;
                $msg = '';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                echo $returnData;
            }
        } else {
            $flag = 0;
            $msg = 'sku未设置';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;
        }
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

    public function getVendorCodeNameArray()
    {
        return DB::table('barcode_vendor_info')->pluck('vendor_name', 'vendor_code');
    }

    public function addNewVendor(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-add-vendor'])) die('Permission denied');
        $userId = Auth::user()->id;
        $canChangeOperator = false;
        if (in_array($userId, $this->getPurchasingDirectorIds())) {
            $canChangeOperator = true;
        }
        if (!$canChangeOperator) die('Permission denied');

        $role = DB::table('roles')->where('name', 'Purchase Employee')->first();
        $roleIdForOperators = 0;
        if ($role) {
            $roleIdForOperators = $role->id;
        }
        $operatorIdArray = DB::table('role_user')->where('role_id', $roleIdForOperators)->pluck('user_id');
        //易林腾不出现在采购人员列表中
//        $yltUserIds = DB::table('users')->where('email', 'like', 'yilinteng@valuelink%')->where('locked', 0)->pluck('id');
//        $yltUserId = $yltUserIds[0];
        $operators = array();
        $users = $this->getUserIdNames();
        foreach ($operatorIdArray as $v) {
//            if($v == $yltUserId) continue;
            $operators[$v] = $users[$v];
        }
        return view('barcode/addNewVendor', ['operators' => $operators]);
    }

    public function saveNewVendor(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-add-vendor'])) die('Permission denied');
        $userId = Auth::user()->id;
        //如果输入的供应商代码是小写，自动转成大写。
        $vendorCode = strtoupper($request->input('vendorCode'));
        $vendorCodeFromSAP = sprintf('%010s', $request->input('vendorCodeFromSAP'));//保留10位
        $vendorName = $request->input('vendorName');
        $operatorId = $request->input('operatorId');
        $vendor = DB::table('barcode_vendor_info')->where('vendor_code', $vendorCode)->first();
        $vendor = json_decode(json_encode($vendor), true);
        if ($vendor) {
            $flag = 0;
            $msg = '该供应商代码(VOP)已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }
        $vendor = DB::table('barcode_vendor_info')->where('vendor_code_from_sap', $vendorCodeFromSAP)->first();
        $vendor = json_decode(json_encode($vendor), true);
        if ($vendor) {
            $flag = 0;
            $msg = '该供应商代码(SAP)已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }
        $vendor = DB::table('barcode_vendor_info')->where('vendor_name', $vendorName)->first();
        $vendor = json_decode(json_encode($vendor), true);
        if ($vendor) {
            $flag = 0;
            $msg = '该供应商名称已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

        DB::beginTransaction();
        try {
            $updateArray = array(
                'vendor_code' => $vendorCode,
                'vendor_code_from_sap' => $vendorCodeFromSAP,
                'vendor_name' => $vendorName,
                'token' => md5($vendorCode . '176'),
                'url_param' => substr(md5($vendorCode . '199'), 0, 10),
                'operator_id' => $operatorId
            );
            DB::table('barcode_vendor_info')->insert($updateArray);
            DB::commit();
            $flag = 1;
            $msg = '增加供应商成功';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;
        } catch (\Exception $e) {
            DB::rollback();
            $flag = 0;
            $msg = '增加供应商失败';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;
        }
    }

    public function editVendor(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-add-vendor'])) die('Permission denied');
        $userId = Auth::user()->id;
        //if (!in_array($userId, $this->getPurchasingDirectorIds())) die('Permission denied');

        $vendorId = $request->input('vendorId');
        $vendor = DB::table('barcode_vendor_info')->where('id', $vendorId)->first();
        $vendor = json_decode(json_encode($vendor), true);
        return view('barcode/editVendor', ['vendor' => $vendor]);
    }

    public function modifyVendor(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-add-vendor'])) die('Permission denied');
        $vendorId = $request->input('vendorId');
        //如果输入的供应商代码是小写，自动转成大写。
        $vendorCode = strtoupper($request->input('vendorCode'));
        $vendorCodeFromSAP = sprintf('%010s', $request->input('vendorCodeFromSAP'));//保留10位
        $vendorName = $request->input('vendorName');
        $vendor = DB::table('barcode_vendor_info')->where('vendor_code', $vendorCode)->first();
        $vendor = json_decode(json_encode($vendor), true);
        if ($vendor && $vendorId != $vendor['id']) {
            $flag = 0;
            $msg = '该供应商代码(VOP)已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }
        $vendor = DB::table('barcode_vendor_info')->where('vendor_code_from_sap', $vendorCodeFromSAP)->first();
        $vendor = json_decode(json_encode($vendor), true);
        if ($vendor && $vendorId != $vendor['id']) {
            $flag = 0;
            $msg = '该供应商代码(SAP)已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }
        $vendor = DB::table('barcode_vendor_info')->where('vendor_name', $vendorName)->first();
        $vendor = json_decode(json_encode($vendor), true);
        if ($vendor && $vendorId != $vendor['id']) {
            $flag = 0;
            $msg = '该供应商名称已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

        $vendor = DB::table('barcode_vendor_info')->where('id', $vendorId)->first();
        $vendor = json_decode(json_encode($vendor), true);

        DB::beginTransaction();
        try {
            //修改了供应商代码(VOP)
//            if ($vendor['vendor_code'] != $vendorCode) {
//                $updateArray = array(
//                    'vendor_code' => $vendorCode,
//                    'vendor_code_from_sap' => $vendorCodeFromSAP,
//                    'vendor_name' => $vendorName,
//                    'token' => md5($vendorCode . '176'),
//                    'url_param' => substr(md5($vendorCode . '199'), 0, 10)
//                );
//                DB::table('barcode_vendor_info')->where('id', $vendorId)->update($updateArray);
//                DB::commit();
//                $flag = 1;
//                $msg = '修改供应商信息成功. 修改了供应商代码（VOP），秘钥和网址参数也改变了';
//                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
//                echo $returnData;
//            } else {
                $updateArray = array(
//                    'vendor_code_from_sap' => $vendorCodeFromSAP,
                    'vendor_name' => $vendorName,
                );
                DB::table('barcode_vendor_info')->where('id', $vendorId)->update($updateArray);
                DB::commit();
                $flag = 1;
                $msg = '修改供应商信息成功. 未修改供应商代码（VOP）';
                $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
                echo $returnData;
//            }

        } catch (\Exception $e) {
            DB::rollback();
            $flag = 0;
            $msg = '修改供应商信息失败';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;
        }
    }

    //get the year code
    public function getYearCode($year)
    {
        //2020:0; 2021:1; 2022:2...; 2053,Z
        $base34 = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        return $base34[$year - 2020];
    }

    //get the month code
    public function getMonthCode($month)
    {
        //1:1; 2:2; 3:3...; 12:C
        $base12 = '123456789ABC';
        return $base12[$month - 1];
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

    //数据库表中批量生成token和url_param
    public function makeTokenUrlParam(Request $request)
    {
        $vendorCodes = DB::table('barcode_vendor_info')->whereNULL('token')->pluck('vendor_code', 'id');
        $vendorCodes = json_decode(json_encode($vendorCodes), true);
        if (!$vendorCodes) {
            die('没有新的供应商需要生成秘钥');
        }
        foreach ($vendorCodes as $k => $v) {
            $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('id', $k);
            $vendor = DB::table('barcode_vendor_info')->where('id', $k)->first();
            $vendor = json_decode(json_encode($vendor), true);
            $vendorCodeFromSap = sprintf('%010s', $vendor['vendor_code_from_sap']);
            $updateArray = array(
                'token' => md5($v . '176'),
                'url_param' => substr(md5($v . '199'), 0, 10),
                'vendor_code_from_sap' => $vendorCodeFromSap
            );
            $barcodeVendorInfo->update($updateArray);
        }
        echo '生成token成功';
    }

    public function changeOperator(Request $request)
    {
        $userId = Auth::user()->id;
        $canChangeOperator = false;
        if (in_array($userId, $this->getPurchasingDirectorIds())) {
            $canChangeOperator = true;
        }
        if (!$canChangeOperator) die('Permission denied');

        $role = DB::table('roles')->where('name', 'Purchase Employee')->first();
        $roleIdForOperators = 0;
        if ($role) {
            $roleIdForOperators = $role->id;
        }
        $operatorIdArray = DB::table('role_user')->where('role_id', $roleIdForOperators)->pluck('user_id');
        //易林腾不出现在采购人员列表中
//        $yltUserIds = DB::table('users')->where('email', 'like', 'yilinteng@valuelink%')->orwhere('email', 'like', 'chenguancan@valuelink%')->orwhere('email', 'like', 'sunhanshan@valuelink%')->orwhere('email', 'like', 'zhangjianqun@valuelink%')->where('locked', 0)->pluck('id');
//        $yltUserIds = json_decode(json_encode($yltUserIds), true);

//        $yltUserId = $yltUserIds[0];
        $operators = array();
        $users = $this->getUserIdNames();
        foreach ($operatorIdArray as $v) {
            $operators[$v] = $users[$v];
//            if(!in_array($v, $yltUserIds)){
//                $operators[$v] = $users[$v];
//            }
//            if($v == $yltUserId) continue;
//            $operators[$v] = $users[$v];
        }

        return view('barcode/changeOperator', ['operators' => $operators]);
    }

    public function getVendorTable(Request $request)
    {
        if (!Auth::user()->can(['barcode-show'])) die('Permission denied');
        $data = DB::table('barcode_vendor_info');
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        if (isset($search['operatorId']) && $search['operatorId']) {
            $operatorId = $search['operatorId'];
            $data = $data->where('operator_id', $operatorId);
        }
        if (isset($search['vendorText']) && $search['vendorText']) {
            $vendorText = $search['vendorText'];
            $data = $data->where(function ($query) use ($vendorText) {
                $query->where('vendor_code', 'like', '%' . $vendorText . '%')
                    ->orWhere('vendor_name', 'like', '%' . $vendorText . '%');
            });
        }

        $iTotalRecords = $data->get()->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $lists = $data->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $lists = json_decode(json_encode($lists), true);
        $userIdNames = $this->getUserIdNames();
        foreach ($lists as $key => $list) {
            $lists[$key]['checkbox_id'] = '<input type="checkbox" name="checkedInput" value="'.$list['id'].'" />';
            $lists[$key]['id'] = $list['id'];
            $lists[$key]['vendor_code'] = $list['vendor_code'];
            $lists[$key]['vendor_name'] = $list['vendor_name'];
            $lists[$key]['operator'] = array_get($userIdNames, intval($list['operator_id']));
        }
        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;

        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function modifyOperator(Request $request)
    {
        if (!Auth::user()->can(['barcode-show-change-employee'])) die('Permission denied');
        $newOperatorId = intval($request->input('newOperatorId'));
        $vendorIdRows = $request->input('vendorIdRows');
//        var_dump($vendorIdRows); exit;

//        $vendorIdArray = array();
//        for ($i = 0; $i < count($vendorIdRows); $i++) {
//            $vendorIdArray[$i] = $vendorIdRows[$i][0];
//        }
        $vendorIdArray = explode(',', $vendorIdRows);
        DB::beginTransaction();
        try {
            $updateArray = array('operator_id' => $newOperatorId);
            DB::table('barcode_vendor_info')->whereIn('id', $vendorIdArray)->update($updateArray);
            DB::commit();
            $flag = 1;
            $msg = '更新操作者成功';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;

        } catch (\Exception $e) {
            DB::rollback();
            $flag = 0;
            $msg = '更新操作者失败';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;
        }

    }

    public function getSapApiSign($array)
    {
        ksort($array);
        $authstr = "";
        foreach ($array as $k => $v) {
            $authstr = $authstr . $k . $v;
        }
        $authstr = $authstr . env("SAP_SECRET");
        $sign = strtoupper(sha1($authstr));
        return $sign;
    }

    public function getActivatedCountInADay(Request $request)
    {
        $vendorCode = $request->input('vendorCode');
        $purchaseOrder = $request->input('purchaseOrder');
        $dateOption = $request->input('dateOption', date('Y-m-d'));
        $activatedCount = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->where('current_status', 1)->whereRaw("SUBSTR(`status_updated_at`,1,10)='$dateOption'")->count();
        echo json_encode(array('activatedCount' => $activatedCount));
    }

    //获取采购总监的id
    public function getPurchasingDirectorIds()
    {
        $userIds = DB::table('users')->where('email', 'like', 'wangshuang@valuelink%')->orWhere('email', 'like', 'yilinteng@valuelink%')->orWhere('email', 'like', 'chenguancan@valuelink%')->orWhere('email', 'like', 'zhangjianqun@valuelink%')->orWhere('email', 'like', 'sunhanshan@valuelink%')->where('locked', 0)->pluck('id');
        $userIds = json_decode(json_encode($userIds),true);
        return $userIds;
    }

    public function qc(Request $request)
    {
        if (!Auth::user()->can(['barcode-qc'])) die('Permission denied');
        $urlParam = $request->input("p");
        if ($urlParam != 'ec93a64741') {
            die('网址参数不正确');
        }
        return view('barcode/qc');
    }

    public function checkQc(Request $request)
    {
        $purchaseOrder = $request->input("purchaseOrder");
        $sku = $request->input("sku");
        $poDetail = DB::table('barcode_po_details')->where('purchase_order', $purchaseOrder)->first();
        if (!$poDetail) {
            $msg = 'FAIL: 该采购订单号在系统中不存在';
            return view('barcode/qc', compact('purchaseOrder', 'sku', 'msg'));
        }
        $poDetail = DB::table('barcode_po_details')->where('purchase_order', $purchaseOrder)->where('sku', $sku)->first();
        if (!$poDetail) {
            $msg = 'FAIL: 该SKU不在当前采购订单号中';
            return view('barcode/check', compact('purchaseOrder', 'sku', 'msg'));
        }

        return view('barcode/qcStep2', compact('purchaseOrder', 'sku'));
    }

    public function verifyQc(Request $request)
    {
        $userId = Auth()->id();
        $purchaseOrder = $request->input("purchaseOrder");
        $sku = $request->input("sku");
        $barcodeText = $request->input("barcodeText");

        $bt = DB::table('barcode_scan_record')->where('purchase_order', $purchaseOrder)->where('sku', $sku)->where('barcode_text', $barcodeText)->first();
        if ($bt) {
            $bt = json_decode(json_encode($bt), true);
            $flag = 1;
            $msg = 'PASS: 条码核对成功';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;

            $updateArray = array(
                'qc' => $userId,
                'qc_updated_at' => date('Y-m-d H:i:s', time())
            );
            $qcHistory = $bt['qc_history'];
            if ($qcHistory) {
                $qcHistory .= ',' . $userId;
            } else {
                $qcHistory = $userId;
            }
            $updateArray['qc_history'] = $qcHistory;
            DB::table('barcode_scan_record')->where('purchase_order', $purchaseOrder)->where('sku', $sku)->where('barcode_text', $barcodeText)->update($updateArray);
        } else {
            $flag = 0;
            $msg = 'FAIL: 条码核对失败';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            echo $returnData;
        }
    }

    //获取所有用户的ID，Name，包括离职的
    public function getUserIdNames()
    {
        return DB::table('users')->pluck('name', 'id');
    }

}