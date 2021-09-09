<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EBayOrderListController extends Controller
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
        $sql = "SELECT max(t.sku) as sku, max(t.ebay_sku) as ebay_sku
                FROM
                ( SELECT e.sku, f.ebay_sku
                  FROM 
                      ebay_order_line_item e
                  LEFT JOIN ebay_sku_sap_sku f ON e.sku = f.ebay_sku and f.active_status = 1
                ) AS t
                GROUP BY t.sku";
        $data = DB::connection('ebay')->select($sql);
        $data = json_decode(json_encode($data), true);
        $ebaySkuArray = array();
        foreach ($data as $v) {
            if ($v['ebay_sku'] == null) {
                $ebaySkuArray[] = $v['sku'];
            }
        }

        if (count($ebaySkuArray) > 0) {
            $ebaySKuString = implode(',', $ebaySkuArray);
            $inactiveSkuMatch = DB::connection('ebay')->table('ebay_sku_sap_sku')->where('active_status', 0)->orderBy('id', 'desc')->get()->toArray();
            $inactiveSkuMatch = json_decode(json_encode($inactiveSkuMatch), true);
            return view('ebay/addSkuMatch', compact('ebaySKuString', 'inactiveSkuMatch'));
        }

        $data['fromDate'] = date('Y-m-d', time() - 364 * 86400);
        $data['toDate'] = date('Y-m-d');//结束日期
        return view('ebay/orderIndex', ['data' => $data]);
    }

    public function refreshSkuMatchTable(Request $req)
    {
        $ebay_sku = $req->input('ebay_sku');
        $s_qty = $req->input('s_qty');
        $sap_sku = $req->input('sap_sku');
        $t_qty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipment_code = $req->input('shipment_code');
        DB::connection('ebay')->table('ebay_sku_sap_sku')->insert(compact('ebay_sku', 's_qty', 'sap_sku', 't_qty', 'warehouse', 'factory', 'shipment_code'));

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
        $inactiveSkuMatch = DB::connection('ebay')->table('ebay_sku_sap_sku')->where('active_status', 0)->orderBy('id', 'desc')->get()->toArray();
        $inactiveSkuMatch = json_decode(json_encode($inactiveSkuMatch), true);
        foreach ($inactiveSkuMatch as $val) {
            $tableHtml .= "<tr>
                                <td>{$val['ebay_sku']}</td>
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
        $inactiveSkuMatch = DB::connection('ebay')->table('ebay_sku_sap_sku')->where('active_status', 0)->orderBy('id', 'desc')->get()->toArray();
        $inactiveSkuMatch = json_decode(json_encode($inactiveSkuMatch), true);
        if (count($inactiveSkuMatch) > 0) {
            return view('ebay/verifySku', compact('inactiveSkuMatch'));
        } else {
            return redirect('/eBayOrderList');
        }
    }


    public function updateSkuTable(Request $req)
    {
        $skuId = $req->input('skuId');
        $updateMethod = $req->input('updateMethod');
        if ($updateMethod == 'confirm') {
            DB::connection('ebay')->table('ebay_sku_sap_sku')->where('id', $skuId)->update(array('active_status' => 1));
        } else {
            DB::connection('ebay')->table('ebay_sku_sap_sku')->where('id', $skuId)->delete();
        }

        return redirect('/eBayOrderList/verifySkuTable');
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
        $data = DB::connection('ebay')->select($sql);
        $data = json_decode(json_encode($data), true);
        $recordsTotal = $recordsFiltered = (DB::connection('ebay')->select('SELECT FOUND_ROWS() as count'))[0]->count;
        $currentOrderId = '';
        $currentLineNumber = 10;
        foreach ($data as $key => $val) {
            $data[$key]['ebay_sap_id'] = $val['sap_code'];
            $data[$key]['site'] = $val['site'];
            $data[$key]['ebay_order_id'] = $val['order_id'];
            $data[$key]['sold_to_party'] = $val['sold_to_party'];
            $data[$key]['order_type'] = 'ZPSO';
            $data[$key]['order_transaction_id'] = $val['order_id'];
            $data[$key]['creation_date'] = empty($val['creation_date']) ? $val['creation_date'] : substr($val['creation_date'], 0, 10);
            $data[$key]['payment_date'] = empty($val['payment_date']) ? $val['payment_date'] : substr($val['payment_date'], 0, 10);
            $data[$key]['payment_transaction_id'] = $val['order_id'];
            $data[$key]['user_id'] = $val['user_email'];
            $data[$key]['user_name'] = $val['username'];
            $data[$key]['country_code'] = $val['country_code'];
            $data[$key]['city'] = $val['city'];
            $data[$key]['state_or_province'] = $val['state_or_province'];
            $data[$key]['consignee_addr1'] = '';
            $data[$key]['consignee_addr2'] = '';
            $data[$key]['postal_code'] = $val['postal_code'];
            $data[$key]['user_email'] = $val['user_email'];
            $data[$key]['consignee_phone'] = '';
            $data[$key]['transaction_fee'] = '';
            $data[$key]['transaction_fee_currency'] = 'USD';
            $data[$key]['commission'] = $val['order_commission'];
            $data[$key]['commission_currency'] = 'USD';
            $data[$key]['order_total_amount'] = $val['order_total_amount'];
            $data[$key]['order_total_amount_currency'] = 'USD';
            $data[$key]['shipment_code'] = $val['shipment_code'];
            $data[$key]['ebay_order_id'] = $val['order_id'];
            $data[$key]['site'] = $val['site'];

            if ($currentOrderId == $val['order_id']) {
                $currentLineNumber += 10;
            } else {
                $currentLineNumber = 10;
            }
            $data[$key]['line_number'] = $currentLineNumber;
            $currentOrderId = $val['order_id'];

            $data[$key]['ebay_sku'] = $val['sku'];
            $data[$key]['ebay_sku_qty'] = $val['quantity'];
            $data[$key]['sap_sku'] = $val['sap_sku'];
            $data[$key]['sap_sku_qty'] = empty($val['quantity']) ? null : $sapSkuQty = intval(($val['t_qty'] / $val['s_qty']) * $val['quantity']);
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
        $data = DB::connection('ebay')->select($sql);
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
                empty($val['payment_date']) ? $val['payment_date'] : substr($val['payment_date'], 0, 10),
                $val['order_id'],
                $val['user_email'],
                $val['username'],
                $val['country_code'],
                $val['city'],
                $val['state_or_province'],
                '',
                '',
                $val['postal_code'],
                $val['user_email'],
                '',
                '',
                'USD',
                $val['order_commission'],
                'USD',
                $val['order_total_amount'],
                'USD',
                $val['shipment_code'],
                $val['order_id'],
                $val['site'],
                $currentLineNumber,
                $val['sap_sku'],
                empty($val['quantity']) ? null : $sapSkuQty = intval(($val['t_qty'] / $val['s_qty']) * $val['quantity']),
                $val['factory'],
                $val['warehouse'],
                '',
                '',
                '',
                '' . $val['sap_seller_id'], //数字前置0被默认删除
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
            header('Content-Disposition: attachment;filename="Export_eBay_Order_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

    //获得搜索条件并且返回对应的sql语句
    public function getSql($search)
    {
        $where = " where a.creation_date >= '" . $search['from_date'] . " 00:00:00' and a.creation_date <= '" . $search['to_date'] . " 23:59:59'";
        if (isset($search['order_id']) && $search['order_id']) {
            $where .= " and a.order_id = '" . $search['order_id'] . "'";
        }
        $where .= " and c.payment_status = 'PAID'";
        $where .= " and ( (g.amount_type = 'total' and g.line_item_id is NULL) or g.amount_type = 'totalMarketplaceFee' )";
        $sql = "SELECT SQL_CALC_FOUND_ROWS
                    max(t.order_id) AS order_id,
                    max(t.creation_date) AS creation_date,
                    max(t.seller_id) AS seller_id,
                    max(t.user_email) AS user_email,
                    max(t.username) AS username,
                    max(t.payment_date) AS payment_date,
                    max(t.city) AS city,
                    max(t.country_code) AS country_code,
                    max(t.postal_code) AS postal_code,
                    max(t.state_or_province) AS state_or_province,
                    max(t.quantity) AS quantity,
                    max(t.sku) AS sku,
                    max(t.s_qty) AS s_qty,
                    max(t.sap_sku) AS sap_sku,
                    max(t.t_qty) AS t_qty,
                    max(t.warehouse) AS warehouse,
                    max(t.factory) AS factory,
                    max(t.shipment_code) AS shipment_code,
                    max(t.order_total_amount) AS order_total_amount,
                    max(t.order_commission) AS order_commission,
                    max(t.sap_code) AS sap_code,
                    max(t.site) AS site,
                    max(t.sold_to_party) AS sold_to_party,
                    max(t.sap_seller_id) AS sap_seller_id
                FROM
                ( SELECT
                        a.order_id, a.creation_date, a.seller_id, b.email AS user_email, b.username, c.payment_date, d.city, d.country_code, d.postal_code, d.state_or_province, e.quantity, e.sku, f.s_qty, f.sap_sku, f.t_qty, f.warehouse, f.factory, f.shipment_code, h.sap_code, h.site, h.sold_to_party, h.sap_seller_id,
                        ( CASE WHEN g.line_item_id IS NULL AND g.amount_type = 'total' THEN g.`value` ELSE 0 END ) AS order_total_amount,
                        ( CASE WHEN g.line_item_id IS NULL AND g.amount_type = 'totalMarketplaceFee' THEN g.`value` ELSE 0 END ) AS order_commission
                    FROM
                        ebay_order a
                    LEFT JOIN ebay_order_buyer b ON a.order_id = b.order_id
                    LEFT JOIN ebay_order_payment c ON a.order_id = c.order_id
                    LEFT JOIN ebay_order_tax_address d ON a.order_id = d.order_id
                    LEFT JOIN ebay_order_line_item e ON a.order_id = e.order_id
                    LEFT JOIN ebay_sku_sap_sku f ON e.sku = f.ebay_sku
                    LEFT JOIN ebay_order_line_item_amount g ON a.order_id = g.order_id
                    LEFT JOIN ebay_sap_info h ON a.seller_id = h.seller_id
                    {$where}
                    ORDER BY
                        a.order_id DESC
                ) AS t
                GROUP BY t.order_id, t.sku, t.sap_sku 
                ORDER BY order_id desc";

        return $sql;
    }

}