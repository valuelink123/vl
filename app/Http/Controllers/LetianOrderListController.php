<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LetianOrderListController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */
    use \App\Traits\DataTables;
    use \App\Traits\Mysqli;

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Show the application dashboard
     */
    public function index()
    {
        $letianSKuString = $this->getNotMatchedSkuString();
        $data['fromDate'] = date('Y-m-d', time() - 364 * 86400);
        $data['toDate'] = date('Y-m-d');//结束日期
        return view('letian/orderIndex', ['data' => $data, 'letianSKuString' => $letianSKuString]);
    }

    /*
     * ajax展示订单列表
     */
    public function list(Request $req)
    {
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        $sql = $this->getSql($search);

        if ($req['length'] != '-1') {//等于-1时为查看全部的数据
            $limit = $this->dtLimit($req);
            $sql .= " LIMIT {$limit} ";
        }
        $data = DB::connection('letian')->select($sql);
        $data = json_decode(json_encode($data), true);
        $recordsTotal = $recordsFiltered = (DB::connection('letian')->select('SELECT FOUND_ROWS() as count'))[0]->count;
        $currentOrderId = '';
        $currentLineNumber = 10;
        foreach ($data as $key => $val) {
            $data[$key]['letian_sap_id'] = $val['sap_code'];
            $data[$key]['site'] = $val['site'];
            $data[$key]['letian_order_id'] = $val['order_number'];
            $data[$key]['sold_to_party'] = $val['sold_to_party'];
            $data[$key]['order_type'] = '';
            $data[$key]['order_transaction_id'] = $val['order_number'];
            $data[$key]['creation_date'] = empty($val['order_datetime']) ? $val['order_datetime'] : substr($val['order_datetime'], 0, 10);
            $data[$key]['payment_date'] = empty($val['payment_date']) ? $val['payment_date'] : substr($val['payment_date'], 0, 10);
            $data[$key]['payment_transaction_id'] = $val['order_number'];
            $data[$key]['user_id'] = $val['email_address'];
            $data[$key]['user_name'] = $val['user_name'];
            $data[$key]['country_code'] = '';
            $data[$key]['city'] = $val['city'];
            $data[$key]['state_or_province'] = '';
            $data[$key]['consignee_addr1'] = '';
            $data[$key]['consignee_addr2'] = '';
            $data[$key]['postal_code'] = $val['zip_code1'];
            $data[$key]['user_email'] = $val['email_address'];
            $data[$key]['consignee_phone'] = $val['phone_number1'];
            $data[$key]['transaction_fee'] = '';
            $data[$key]['transaction_fee_currency'] = 'JPY';
            $data[$key]['commission'] = '';
            $data[$key]['commission_currency'] = 'JPY';
            $data[$key]['order_total_amount'] = $val['total_price'];
            $data[$key]['order_total_amount_currency'] = 'JPY';
            $data[$key]['shipment_code'] = $val['shipment_code'];
            $data[$key]['letian_order_id'] = $val['order_number'];
            $data[$key]['site'] = $val['site'];

            if ($currentOrderId == $val['order_number']) {
                $currentLineNumber += 10;
            } else {
                $currentLineNumber = 10;
            }
            $data[$key]['line_number'] = $currentLineNumber;
            $currentOrderId = $val['order_number'];

            $data[$key]['letian_sku'] = $val['letian_sku'];
            $data[$key]['letian_sku_qty'] = $val['units'];
            $data[$key]['sap_sku'] = $val['sap_sku'];
            $data[$key]['sap_sku_qty'] = empty($val['s_qty']) ? null : intval(($val['t_qty'] / $val['s_qty']) * $val['units']);
            $data[$key]['factory'] = $val['factory'];
            $data[$key]['warehouse'] = $val['warehouse'];
            $data[$key]['line_item_id'] = '';
            $data[$key]['post_id'] = '';
            $data[$key]['post_title'] = '';
            $data[$key]['seller_id'] = $val['sap_seller_name'];
            $data[$key]['line_transaction_id'] = '';
            $data[$key]['mark_complete'] = '';
        }
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    /*
     * 导出功能
     */
    public function export()
    {
        $sql = $this->getSql($_GET);
        $data = DB::connection('letian')->select($sql);
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('平台编号', '站点', '平台订单号', '售达方', '订单类型', '订单交易号', '付款日期', '付款交易ID', '买家ID', '买家姓名', '国家代码', '城市名', '州/省', '街道1', '街道2', '邮编', '邮箱', '电话1', '成交费', '货币', '佣金', '货币', '订单总价', '货币', '实际运输方式', '平台订单号', '站点', '行号', 'SAP物料号', '数量', '工厂', '仓库', '行项目ID', '帖子ID', '帖子标题', '销售员编号', '行交易ID', '标记完成');

        $arrayData[] = $headArray;
        $currentOrderId = '';
        $currentLineNumber = 10;
        foreach ($data as $key => $val) {
            if ($currentOrderId == $val['order_id']) {
                $currentLineNumber += 10;
            } else {
                $currentLineNumber = 10;
            }
            $currentOrderId = $val['order_id'];

            $arrayData[] = array(
                $val['sap_code'],
                $val['site'],
                $val['order_number'],
                $val['sold_to_party'],
                '',
                $val['order_number'],
                empty($val['payment_date']) ? $val['payment_date'] : substr($val['payment_date'], 0, 10),
                $val['order_number'],
                $val['email_address'],
                $val['user_name'],
                '',
                $val['city'],
                '',
                '',
                '',
                $val['zip_code1'],
                $val['email_address'],
                $val['phone_number1'],
                '',
                'JPY',
                '',
                'JPY',
                $val['total_price'],
                'JPY',
                $val['shipment_code'],
                $val['order_number'],
                $val['site'],
                $currentLineNumber,
                $val['sap_sku'],
                empty($val['s_qty']) ? null : intval(($val['t_qty'] / $val['s_qty']) * $val['units']),
                $val['factory'],
                $val['warehouse'],
                '',
                '',
                '',
                '' . $val['sap_seller_id'],
                '',
                '',
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
            header('Content-Disposition: attachment;filename="Export_letian_Order_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

    //获得搜索条件并且返回对应的sql语句
    public function getSql($search)
    {
        $where = " where a.order_datetime >= '" . $search['from_date'] . " 00:00:00' and a.order_datetime <= '" . $search['to_date'] . " 23:59:59'";
        if (isset($search['order_id']) && $search['order_id']) {
            $where .= " and a.order_number = '" . $search['order_id'] . "'";
        }
        // $where .= " and f.payment_status = '30600' ";
        // seller_id 数据库表里面没有这一字段
        // LEFT JOIN letian_payment表，因为letian_payment的payment_method有多种（用户用不同的方式分开付款），可能会有多行。所以用了Group By
        $sql = "SELECT SQL_CALC_FOUND_ROWS
                max(order_number) as order_number,
                max(order_datetime) as order_datetime,
                max(total_price) as total_price,
                max(city) as city,
                max(email_address) as email_address,
                max(user_name) as user_name,
                max(phone_number1) as phone_number1,
                max(zip_code1) as zip_code1,
                max(letian_sku) as letian_sku,
                max(units) as units,
                max(s_qty) as s_qty,
                max(sap_sku) as sap_sku,
                max(t_qty) as t_qty,
                max(warehouse) as warehouse,
                max(factory) as factory,
                max(shipment_code) as shipment_code,
                max(sap_code) as sap_code,
                max(site) as site,
                max(sold_to_party) as sold_to_party,
                max(sap_seller_id) as sap_seller_id,
                max(sap_seller_name) as sap_seller_name,
                max(payment_date) as payment_date
                FROM
                (SELECT a.order_number, a.order_datetime, a.total_price, b.city, b.email_address, CONCAT(b.family_name, b.first_name) AS user_name, b.phone_number1, b.zip_code1, c.item_number AS letian_sku, c.units, d.s_qty, d.sap_sku, d.t_qty, d.warehouse, d.factory, d.shipment_code, e.sap_code, e.site, e.sold_to_party, e.sap_seller_id, e.sap_seller_name, f.payment_proc_cmpl_datetime AS payment_date
                FROM
                    letian_order a
                LEFT JOIN letian_order_orderer b ON a.order_number = b.order_number
                LEFT JOIN letian_order_item c ON a.order_number = c.order_number
                LEFT JOIN letian_sku_sap_sku d ON c.item_number = d.letian_sku
                LEFT JOIN letian_sap_info e ON e.seller_id = 0
                LEFT JOIN letian_payment f ON a.order_number = f.order_number
                {$where}
                ORDER BY order_number desc
                ) AS t
                GROUP BY t.order_number, t.letian_sku, t.sap_sku 
                ORDER BY order_number desc
                ";

        return $sql;
    }

    public function addSkuMatch(Request $req)
    {
        $letianSKuString = $this->getNotMatchedSkuString();
        if ($letianSKuString) {
            return view('letian/addSkuMatch', ['letianSKuString' => $letianSKuString]);
        } else {
            echo '平台SKU和SAP SKU已全部匹配';
        }
    }

    public function skuMatchList(Request $req)
    {
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&', $search));
        $sku = array_get($search, 'sku') ?? '';
        //去除tab,空格,换行. 本身的+转成了%2B：  AP3127_B + BHG1136
        $sku = str_replace('%09', '', $sku);
        $sku = str_replace('+', ' ', $sku);
        $sku = str_replace('%2B', '+', $sku);
        $sku = trim($sku);

        if ($req->isMethod('GET')) {
            return view('letian/skuMatchList', compact('sku'));
        }
        $where = '';
        if ($sku) {
            $where = " where letian_sku = '{$sku}' or sap_sku = '{$sku}'";
        }
        $sql = "select * from letian_sku_sap_sku {$where} order by id desc";
        $recordsTotal = $recordsFiltered = count(DB::connection('letian')->select($sql));
        if ($req['length'] != '-1') {
            $limit = $this->dtLimit($req);
            $sql .= " LIMIT {$limit} ";
        }

        $data = DB::connection('letian')->select($sql);
        $data = json_decode(json_encode($data), true);
        foreach ($data as $key => $val) {
            $data[$key]['action'] = '<a href="/letianOrderList/skuMatchEdit?sku_id=' . $val['id'] . '" target="_blank">编辑</a>';
        }
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function skuMatchEdit(Request $req)
    {
        $skuId = $req->input('sku_id');
        $data = DB::connection('letian')->table('letian_sku_sap_sku')->where('id', $skuId)->first();
        $data = json_decode(json_encode($data), true);
        return view('letian/skuMatchEdit', compact('data'));
    }

    public function skuMatchUpdate(Request $req)
    {
        $skuId = $req->input('sku_id');
        $letianSku = $req->input('letian_sku');
        $sQty = $req->input('s_qty');
        $sapSku = $req->input('sap_sku');
        $tQty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipmentCode = $req->input('shipment_code');
        $updateArray = array('letian_sku' => $letianSku, 's_qty' => $sQty, 'sap_sku' => $sapSku, 't_qty' => $tQty, 'warehouse' => $warehouse, 'factory' => $factory, 'shipment_code' => $shipmentCode);

        DB::beginTransaction();
        try {
            DB::connection('letian')->table('letian_sku_sap_sku')->where('id', $skuId)->update($updateArray);
            DB::commit();
            $req->session()->flash('success_message', 'Update SKU match successfully');
            return redirect('letianOrderList/skuMatchList');
        } catch (\Exception $e) {
            DB::rollBack();
            $req->session()->flash('error_message', 'Failed to update SKU match');
            return redirect()->back()->withInput();
        }
    }


    public function getNotMatchedSkuString()
    {
        $sql = "SELECT max(t.item_number) as sku, max(t.letian_sku) as letian_sku
                FROM
                ( SELECT c.item_number, d.letian_sku
                  FROM 
                      letian_order_item c
                  LEFT JOIN letian_sku_sap_sku d ON c.item_number = d.letian_sku
                ) AS t
                GROUP BY t.item_number";
        $data = DB::connection('letian')->select($sql);
        $data = json_decode(json_encode($data), true);
        $letianSkuArray = array();
        foreach ($data as $v) {
            if ($v['letian_sku'] == null) {
                $letianSkuArray[] = $v['sku'];
            }
        }
        $letianSKuString = "";
        if (count($letianSkuArray) > 0) {
            $letianSKuString = implode(',', $letianSkuArray);
        }
        return $letianSKuString;
    }

    public function refreshSkuMatchTable(Request $req)
    {
        $letian_sku = $req->input('letian_sku');
        $s_qty = $req->input('s_qty');
        $sap_sku = $req->input('sap_sku');
        $t_qty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipment_code = $req->input('shipment_code');

        $sku = DB::connection('letian')->table('letian_sku_sap_sku')->where('letian_sku', $letian_sku)->first();
        if ($sku) {
            echo json_encode(array('flag' => 0, 'msg' => '平台SKU已经存在'));
        }
        try {
            DB::connection('letian')->table('letian_sku_sap_sku')->insert(compact('letian_sku', 's_qty', 'sap_sku', 't_qty', 'warehouse', 'factory', 'shipment_code'));
            $insertTableRow = "<tr><td>{$letian_sku}</td><td>{$s_qty}</td><td>{$sap_sku}</td><td>{$t_qty}</td><td>{$warehouse}</td><td>{$factory}</td><td>{$shipment_code}</td></tr>";
            $letianSKuString = $this->getNotMatchedSkuString();
            if ($letianSKuString) {
                echo json_encode(array('flag' => 1, 'msg' => $insertTableRow));
            } else {
                echo json_encode(array('flag' => 2, 'msg' => $insertTableRow));
            }

        } catch (\Exception $e) {
            echo json_encode(array('flag' => 0, 'msg' => '插入数据失败'));
        }
    }

    public function exportSkuMatchList(Request $req)
    {
        $data = DB::connection('letian')->table('letian_sku_sap_sku')->get()->toArray();
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('ID', '平台SKU', '平台SKU的单位数量', 'SAP SKU', 'SAP SKU的数量', '仓库', '工厂', '实际运输方式');
        $arrayData[] = $headArray;
        foreach ($data as $key => $val) {
            $arrayData[] = array(
                $val['id'],
                $val['letian_sku'],
                $val['s_qty'],
                $val['sap_sku'],
                $val['t_qty'],
                $val['warehouse'],
                $val['factory'],
                $val['shipment_code'],
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
            header('Content-Disposition: attachment;filename="Export_letian_SKU_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();

    }

}