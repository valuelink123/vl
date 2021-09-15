<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JoybuyOrderListController extends Controller
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
        $joybuySKuString = $this->getNotMatchedSkuString();
        $data['fromDate'] = date('Y-m-d', time() - 364 * 86400);
        $data['toDate'] = date('Y-m-d');//结束日期
        return view('joybuy/orderIndex', ['data' => $data, 'joybuySKuString' => $joybuySKuString]);
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
        $datas = DB::connection('joybuy')->select($sql);
        $data = json_decode(json_encode($datas), true);
        $recordsTotal = $recordsFiltered = (DB::connection('joybuy')->select('SELECT FOUND_ROWS() as count'))[0]->count;

        $currentOrderId = '';
        $currentLineNumber = 10;
        foreach ($data as $key => $val) {
            $data[$key]['joybuy_sap_id'] = $val['sap_code'];
            $data[$key]['site'] = $val['site'];
            $data[$key]['joybuy_order_id'] = $val['order_id'];
            $data[$key]['sold_to_party'] = $val['sold_to_party'];
            $data[$key]['order_type'] = 'ZPSO';
            $data[$key]['order_transaction_id'] = $val['order_id'];
            $data[$key]['creation_date'] = empty($val['book_time']) ? $val['book_time'] : substr($val['book_time'], 0, 10);
            $data[$key]['payment_date'] = empty($val['pay_time']) ? $val['pay_time'] : substr($val['pay_time'], 0, 10);
            $data[$key]['payment_transaction_id'] = $val['order_id'];
            $data[$key]['user_id'] = $val['consignee_email'];
            $data[$key]['user_name'] = $val['consignee'];
            $data[$key]['country_code'] = $val['consignee_country_id'];
            $data[$key]['city'] = $val['consignee_city'];
            $data[$key]['state_or_province'] = $val['consignee_state'];
            $data[$key]['consignee_addr1'] = $val['consignee_addr1'];
            $data[$key]['consignee_addr2'] = $val['consignee_addr1'];
            $data[$key]['postal_code'] = $val['consignee_postcode'];
            $data[$key]['user_email'] = $val['consignee_email'];
            $data[$key]['consignee_phone'] = $val['consignee_phone'];
            $data[$key]['transaction_fee'] = '';
            $data[$key]['transaction_fee_currency'] = 'USD';
            $data[$key]['commission'] = sprintf("%.2f", 0.13 * $val['prd_total_usd']);
            $data[$key]['commission_currency'] = 'USD';
            $data[$key]['order_total_amount'] = $val['prd_total_usd'];
            $data[$key]['order_total_amount_currency'] = 'USD';
            $data[$key]['shipment_code'] = $val['shipment_code'];
            $data[$key]['joybuy_order_id'] = $val['order_id'];
            $data[$key]['site'] = $val['site'];

            if ($currentOrderId == $val['order_id']) {
                $currentLineNumber += 10;
            } else {
                $currentLineNumber = 10;
            }
            $data[$key]['line_number'] = $currentLineNumber;
            $currentOrderId = $val['order_id'];

            $data[$key]['joybuy_sku'] = $val['sku_id'];
            $data[$key]['joybuy_sku_qty'] = $val['quantity'];
            $data[$key]['sap_sku'] = $val['sap_sku'];
            $sapSkuQty = empty($val['s_qty']) ? null : intval(($val['t_qty'] / $val['s_qty']) * $val['quantity']);
            $data[$key]['sap_sku_qty'] = $sapSkuQty;
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
        $data = DB::connection('joybuy')->select($sql);
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
                $val['order_id'],
                $val['sold_to_party'],
                'ZPSO',
                $val['order_id'],
                empty($val['pay_time']) ? $val['pay_time'] : substr($val['pay_time'], 0, 10),
                $val['order_id'],
                $val['consignee_email'],
                $val['consignee'],
                $val['consignee_country_id'],
                $val['consignee_city'],
                $val['consignee_state'],
                $val['consignee_addr1'],
                $val['consignee_addr1'],
                $val['consignee_postcode'],
                $val['consignee_email'],
                $val['consignee_phone'],
                '',
                'USD',
                sprintf("%.2f", 0.13 * $val['prd_total_usd']),
                'USD',
                $val['prd_total_usd'],
                'USD',
                $val['shipment_code'],
                $val['order_id'],
                $val['site'],
                $currentLineNumber,
                $val['sap_sku'],
                empty($val['s_qty']) ? null : intval(($val['t_qty'] / $val['s_qty']) * $val['quantity']),
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
            header('Content-Disposition: attachment;filename="Export_Joybuy_Order_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

    //获得搜索条件并且返回对应的sql语句
    public function getSql($search)
    {
        $where = " where a.book_time >= '" . $search['from_date'] . " 00:00:00' and a.book_time <= '" . $search['to_date'] . " 23:59:59'";
        $where .= " and a.order_status in (4,5,6)"; //页面中只显示已支付的，只导出已支付的？？？
        if (isset($search['order_id']) && $search['order_id']) {
            $where .= " and a.order_id = '" . $search['order_id'] . "'";
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS a.order_id, a.book_time, a.pay_time, a.consignee, a.consignee_country_id, a.consignee_city, a.consignee_state, a.consignee_addr1, a.consignee_addr2, a.consignee_postcode, a.consignee_email, a.consignee_phone, a.prd_total_usd, a.site, b.sku_id, c.quantity, d.s_qty, d.sap_sku, d.t_qty, d.warehouse, d.factory, d.shipment_code, e.sap_code, e.site, e.sold_to_party, e.sap_seller_id, e.sap_seller_name
				FROM joybuy_order a
				LEFT JOIN joybuy_sku b
				ON a.order_id = b.order_id
                LEFT JOIN joybuy_order_sku c
                ON b.id = c.joybuy_sku_id and a.order_id = c.order_id
                LEFT JOIN joybuy_sku_sap_sku d
                ON b.sku_id = d.joybuy_sku
                LEFT JOIN joybuy_sap_info e
                ON a.vender_id = e.seller_id
				{$where}
				order by a.order_id desc";
        return $sql;
    }

    public function addSkuMatch(Request $req)
    {
        $joybuySKuString = $this->getNotMatchedSkuString();
        if ($joybuySKuString) {
            return view('joybuy/addSkuMatch', ['joybuySKuString' => $joybuySKuString]);
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
            return view('joybuy/skuMatchList', compact('sku'));
        }
        $where = '';
        if ($sku) {
            $where = " where joybuy_sku = '{$sku}' or sap_sku = '{$sku}'";
        }
        $sql = "select * from joybuy_sku_sap_sku {$where} order by id desc";
        $recordsTotal = $recordsFiltered = count(DB::connection('joybuy')->select($sql));
        if ($req['length'] != '-1') {
            $limit = $this->dtLimit($req);
            $sql .= " LIMIT {$limit} ";
        }

        $data = DB::connection('joybuy')->select($sql);
        $data = json_decode(json_encode($data), true);
        foreach ($data as $key => $val) {
            $data[$key]['action'] = '<a href="/joybuyOrderList/skuMatchEdit?sku_id=' . $val['id'] . '" target="_blank">编辑</a>';
        }
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function skuMatchEdit(Request $req)
    {
        $skuId = $req->input('sku_id');
        $data = DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('id', $skuId)->first();
        $data = json_decode(json_encode($data), true);
        return view('joybuy/skuMatchEdit', compact('data'));
    }

    public function skuMatchUpdate(Request $req)
    {
        $skuId = $req->input('sku_id');
        $joybuySku = $req->input('joybuy_sku');
        $sQty = $req->input('s_qty');
        $sapSku = $req->input('sap_sku');
        $tQty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipmentCode = $req->input('shipment_code');
        $updateArray = array('joybuy_sku' => $joybuySku, 's_qty' => $sQty, 'sap_sku' => $sapSku, 't_qty' => $tQty, 'warehouse' => $warehouse, 'factory' => $factory, 'shipment_code' => $shipmentCode);

        DB::beginTransaction();
        try {
            DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('id', $skuId)->update($updateArray);
            DB::commit();
            $req->session()->flash('success_message', 'Update SKU match successfully');
            return redirect('joybuyOrderList/skuMatchList');
        } catch (\Exception $e) {
            DB::rollBack();
            $req->session()->flash('error_message', 'Failed to update SKU match');
            return redirect()->back()->withInput();
        }
    }


    public function getNotMatchedSkuString()
    {
        $sql = "SELECT max(t.sku) AS sku, max(t.joybuy_sku) AS joybuy_sku
                FROM
                ( SELECT b.sku_id as sku, d.joybuy_sku
                  FROM 
                      joybuy_sku b
                  LEFT JOIN joybuy_sku_sap_sku d ON b.sku_id = d.joybuy_sku
                ) AS t
                GROUP BY t.sku";
        $data = DB::connection('joybuy')->select($sql);
        $data = json_decode(json_encode($data), true);
        $joybuySkuArray = array();
        foreach ($data as $v) {
            if ($v['joybuy_sku'] == null) {
                $joybuySkuArray[] = $v['sku'];
            }
        }
        $joybuySKuString = "";
        if (count($joybuySkuArray) > 0) {
            $joybuySKuString = implode(',', $joybuySkuArray);
        }
        return $joybuySKuString;
    }

    public function refreshSkuMatchTable(Request $req)
    {
        $joybuy_sku = $req->input('joybuy_sku');
        $s_qty = $req->input('s_qty');
        $sap_sku = $req->input('sap_sku');
        $t_qty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipment_code = $req->input('shipment_code');

        $sku = DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('joybuy_sku', $joybuy_sku)->first();
        if ($sku) {
            echo json_encode(array('flag' => 0, 'msg' => '平台SKU已经存在'));
        }
        try {
            DB::connection('joybuy')->table('joybuy_sku_sap_sku')->insert(compact('joybuy_sku', 's_qty', 'sap_sku', 't_qty', 'warehouse', 'factory', 'shipment_code'));
            $insertTableRow = "<tr><td>{$joybuy_sku}</td><td>{$s_qty}</td><td>{$sap_sku}</td><td>{$t_qty}</td><td>{$warehouse}</td><td>{$factory}</td><td>{$shipment_code}</td></tr>";
            $joybuySKuString = $this->getNotMatchedSkuString();
            if ($joybuySKuString) {
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
        $data = DB::connection('joybuy')->table('joybuy_sku_sap_sku')->get()->toArray();
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('ID', '平台SKU', '平台SKU的单位数量', 'SAP SKU', 'SAP SKU的数量', '仓库', '工厂', '实际运输方式');
        $arrayData[] = $headArray;
        foreach ($data as $key => $val) {
            $arrayData[] = array(
                $val['id'],
                $val['joybuy_sku'],
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
            header('Content-Disposition: attachment;filename="Export_joybuy_SKU_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();

    }

}