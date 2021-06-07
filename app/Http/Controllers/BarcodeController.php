<?php

namespace App\Http\Controllers;

use http\Header;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $this->middleware('auth');
        parent::__construct();
    }

    public function index()
    {
        return view('barcode/index');
    }

    public function getVendorList()
    {
        $data = DB::table('barcode_vendor_info');
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
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
        foreach ($lists as $key => $list) {
            $lists[$key]['vendor_code'] = $list['vendor_code'];
            $lists[$key]['token'] = $list['token'];
            $lists[$key]['url_param'] = $list['url_param'];
            $lists[$key]['vendor_name'] = $list['vendor_name'];
            $lists[$key]['enter'] = '<a href="/barcode/purchaseOrderList?vendorCode=' . $list['vendor_code'] . '" target="_blank">进入</a>';
        }
        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;

        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function generateBarcode(Request $request)
    {
        return view('barcode/generateBarcode');
    }

    public function saveBarcode(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $vendorCode = strtoupper($request->input("vendorCode"));
        $purchaseOrder = strtoupper($request->input("purchaseOrder"));
        $codeCount = $request->input("codeCount");
        //检查供应商代码是否存在
        $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('vendor_code', $vendorCode)->first();
        if (!$barcodeVendorInfo) {
            $flag = 1;
            $msg = '该供应商代码不存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }
        //检查数据库中是否已存在由（供应商，采购订单号）组合生成的条码，如果有，则不会向数据库中写入数据，并返回提示信息。
        $barcodeRecord = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->first();
        if ($barcodeRecord) {
            $flag = 2;
            $msg = '基于该供应商和采购订单号的条码已经存在';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

        $time = date('Y-m-d H:i:s', time());
        $year = substr($time, 0, 4);
        $month = substr($time, 5, 2);
        $yearMonth = substr($time, 0, 7);
        $maxSN = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->whereRaw("SUBSTR(`generated_at`,1,7)='$yearMonth'")->max('sn');
        $nextSN = intVal($maxSN) + 1;

        DB::beginTransaction();
        $serial = range($nextSN, $nextSN + $codeCount - 1);
        try {
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
            $flag = 3;
            $msg = '生成条码成功';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);

        } catch (\Exception $e) {
            DB::rollBack();
            $flag = 4;
            $msg = '生成条码失败';
            $returnData = json_encode(array('flag' => $flag, 'msg' => $msg));
            die($returnData);
        }

    }

    public function printBarcode(Request $request)
    {
        return view('barcode/printBarcode');
    }

    public function outputBarcode(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $vendorCode = strtoupper($request->input('vendorCode'));
        $purchaseOrder = strtoupper($request->input('purchaseOrder'));
        $bt = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->orderBy('id', 'asc')->pluck('barcode_text');
        $bt = json_decode(json_encode($bt), true);
        if (!$bt) {
            die('基于该供应商和采购订单号的条码不存在');
        }

        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $btChunk = array_chunk($bt, 4); //每行打印的条码个数
        $html = '';
        foreach ($btChunk as $chunk) {
            $html .= '<div>';
            foreach ($chunk as $barcodeText) {
                $barcode = $generator->getBarcode($barcodeText, $generator::TYPE_CODE_93, 1, 20);
                $barcode = base64_encode($barcode);
                $html .= '<div style="float:left; margin: 15px">
                              <div>
                                  <img src="data:image/png;base64,' . $barcode . '"/>
                                  <div style="margin-top: 2px;text-align: center;font-size:10px;font-family: arial;">SN:' . $barcodeText . '</div>
                              </div>
                           </div>';
            }
            $html .= '<div style="clear:both"></div></div>';
        }
        echo $html;

        DB::beginTransaction();
        $updateData = array(
            'printed_by' => $userId,
        );
        try {
            DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder)->update($updateData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

    }

    public function purchaseOrderList(Request $req)
    {
        $vendorCode = $req->input('vendorCode');
        if (!$vendorCode) {
            die('没有选择供应商');
        }

        return view('barcode/purchaseOrderList', ['vendorCode' => $vendorCode]);
    }

    public function getPurchaseOrderList(Request $request)
    {
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));

        if (!(isset($search['vendorCode']) && $search['vendorCode'])) {
            die('没有选择供应商');
        }
        $vendorCode = $search['vendorCode'];
        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode);
        if (isset($search['po']) && $search['po']) {
            $po = $search['po'];
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
                                    ) as activated_barcodes')->groupBy('purchase_order');
        $orderby = 'purchase_order';
        $order_column = $request->input('order.0.column', '1');
        if ($order_column == 2) {
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
            $lists[$key]['vendor_code'] = $list['vendor_code'];
            $lists[$key]['purchase_order'] = $list['purchase_order'];
            $lists[$key]['total_barcodes'] = $list['total_barcodes'];
            $lists[$key]['activated_barcodes'] = $list['activated_barcodes'];
            $lists[$key]['details'] = '<a href = "/barcode/purchaseOrderDetails?vendorCode=' . $list['vendor_code'] . '&purchaseOrder=' . $list['purchase_order'] . '" target = "_blank">详情</a > ';
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
        if (!$vendorCode) {
            die('没有选择供应商');
        }
        if (!$purchaseOrder) {
            die('没有选择采购订单号');
        }

        return view('barcode/purchaseOrderDetails', compact('vendorCode', 'purchaseOrder'));
    }

    public function getPurchaseOrderDetails(Request $request)
    {

        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        if (!(isset($search['vendorCode']) && $search['vendorCode'])) {
            die('没有选择供应商');
        }
        if (!(isset($search['purchaseOrder']) && $search['purchaseOrder'])) {
            die('没有选择采购订单号');
        }
        $vendorCode = $search['vendorCode'];
        $purchaseOrder = $search['purchaseOrder'];
        $data = DB::table('barcode_scan_record')->where('vendor_code', $vendorCode)->where('purchase_order', $purchaseOrder);
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
        $userIdNames = getUsers();

        foreach ($lists as $key => $list) {
            $lists[$key]['sku'] = $list['sku'];
            $lists[$key]['barcode_text'] = $list['barcode_text'];
            $lists[$key]['current_status'] = $list['current_status'];
            $lists[$key]['status_history'] = $list['status_history'];
            $lists[$key]['status_updated_at'] = $list['status_updated_at'];
            $lists[$key]['generated_by'] = array_get($userIdNames, $list['generated_by']);
            $lists[$key]['generated_at'] = $list['generated_at'];
            $lists[$key]['printed_by'] = array_get($userIdNames, $list['printed_by']);
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

        $userIdNames = getUsers();
        $data = $data->get()->toArray();
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('sku', '条码', '条码当前状态', '条码历史状态', '条码最后更新时间', '条码生成人', '条码生成时间', '条码打印人');
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
                array_get($userIdNames, $val['printed_by'])
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

    public function makeToken(Request $request)
    {
        $vendorCodes = DB::table('barcode_vendor_info')->whereNULL('token')->pluck('vendor_code', 'id');
        $vendorCodes = json_decode(json_encode($vendorCodes), true);
        if (!$vendorCodes) {
            die('没有新的供应商需要生成token');
        }
        foreach ($vendorCodes as $k => $v) {
            $barcodeVendorInfo = DB::table('barcode_vendor_info')->where('id', $k);
            $updateArray = array(
                'token' => md5($v . '176'),
                'url_param' => substr(md5($v . '199'), 0, 10)
            );
            $barcodeVendorInfo->update($updateArray);
        }
        echo '生成token成功';
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


}