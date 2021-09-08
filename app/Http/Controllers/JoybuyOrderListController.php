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
        $sql = "SELECT max(t.sku) AS sku, max(t.joybuy_sku) AS joybuy_sku
                FROM
                ( SELECT b.sku_id as sku, d.joybuy_sku
                  FROM joybuy_order a
                  LEFT JOIN joybuy_sku b ON a.order_id = b.order_id
                  LEFT JOIN joybuy_order_sku c ON b.id = c.joybuy_sku_id AND a.order_id = c.order_id 
                  LEFT JOIN joybuy_sku_sap_sku d ON b.sku_id = d.joybuy_sku and d.active_status = 1
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

        if (count($joybuySkuArray) > 0) {
            $joybuySKuString = implode(',', $joybuySkuArray);
            $inactiveSkuMatch = DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('active_status', 0)->orderBy('id', 'desc')->get()->toArray();
            $inactiveSkuMatch = json_decode(json_encode($inactiveSkuMatch), true);
            return view('joybuy/addSkuMatch', compact('joybuySKuString', 'inactiveSkuMatch'));
        }

        $data['fromDate'] = date('Y-m-d', time() - 364 * 86400);
        $data['toDate'] = date('Y-m-d');//结束日期
        return view('joybuy/orderIndex', ['data' => $data]);

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
        DB::connection('joybuy')->table('joybuy_sku_sap_sku')->insert(compact('joybuy_sku', 's_qty', 'sap_sku', 't_qty', 'warehouse', 'factory', 'shipment_code'));

        $tableHtml = '<table align="center" id="inactiveSKuMatchTable">
                        <tr>
                            <th>平台SKU</th>
                            <th>平台SKU的单位数量</th>
                            <th>SAP SKU</th>
                            <th>SAP SKU的数量</th>
                            <th>仓库</th>
                            <th>工厂</th>
                            <th>实际运输方式</th>
                        </tr>';
        $inactiveSkuMatch = DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('active_status', 0)->orderBy('id', 'desc')->get()->toArray();
        $inactiveSkuMatch = json_decode(json_encode($inactiveSkuMatch), true);
        foreach ($inactiveSkuMatch as $val) {
            $tableHtml .= "<tr>
                                <td>{$val['joybuy_sku']}</td>
                                <td>{$val['s_qty']}</td>
                                <td>{$val['sap_sku']}</td>
                                <td>{$val['t_qty']}</td>
                                <td>{$val['warehouse']}</td>
                                <td>{$val['factory']}</td>
                                <td>{$val['shipment_code']}</td>
                            </tr>";
        }
        $tableHtml .= '</table>';

        echo $tableHtml;
    }

    public function verifySkuTable(Request $req)
    {
        $inactiveSkuMatch = DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('active_status', 0)->orderBy('id', 'desc')->get()->toArray();
        $inactiveSkuMatch = json_decode(json_encode($inactiveSkuMatch), true);
        if (count($inactiveSkuMatch) > 0) {
            return view('joybuy/verifySku', compact('inactiveSkuMatch'));
        } else {
            return redirect('/joybuyOrderList');
        }
    }

    public function updateSkuTable(Request $req)
    {
        $skuId = $req->input('skuId');
        $updateMethod = $req->input('updateMethod');
        if ($updateMethod == 'confirm') {
            DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('id', $skuId)->update(array('active_status' => 1));
        } else {
            DB::connection('joybuy')->table('joybuy_sku_sap_sku')->where('id', $skuId)->delete();
        }

        return redirect('/joybuyOrderList/verifySkuTable');
    }

    /*
     * ajax展示订单列表
     */
    public function List(Request $req)
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
            $sapSkuQty = intval(($val['t_qty'] / $val['s_qty']) * $val['quantity']);
            $data[$key]['sap_sku_qty'] = $sapSkuQty;
            $data[$key]['factory'] = $val['factory'];
            $data[$key]['warehouse'] = $val['warehouse'];
            $data[$key]['line_item_id'] = '';
            $data[$key]['post_id'] = '';
            $data[$key]['post_title'] = '';
            $data[$key]['seller_id'] = $val['sap_seller_id'];
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
                intval(($val['t_qty'] / $val['s_qty']) * $val['quantity']),
                $val['factory'],
                $val['warehouse'],
                '',
                '',
                '',
                $val['sap_seller_id'],
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
        $sql = "SELECT SQL_CALC_FOUND_ROWS a.order_id, a.book_time, a.pay_time, a.consignee, a.consignee_country_id, a.consignee_city, a.consignee_state, a.consignee_addr1, a.consignee_addr2, a.consignee_postcode, a.consignee_email, a.consignee_phone, a.prd_total_usd, a.site, b.sku_id, c.quantity, d.s_qty, d.sap_sku, d.t_qty, d.warehouse, d.factory, d.shipment_code, e.sap_code, e.site, e.sold_to_party, e.sap_seller_id
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

}