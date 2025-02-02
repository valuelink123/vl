<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NeweggOrderListController extends Controller
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
        $neweggSKuString = $this->getNotMatchedSkuString();
        $data['fromDate'] = date('Y-m-d', time() - 364 * 86400);
        $data['toDate'] = date('Y-m-d');//结束日期
        return view('newegg/orderIndex', ['data' => $data, 'neweggSKuString' => $neweggSKuString]);
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
        $data = DB::connection('newegg')->select($sql);
        $data = json_decode(json_encode($data), true);
        $recordsTotal = $recordsFiltered = (DB::connection('newegg')->select('SELECT FOUND_ROWS() as count'))[0]->count;
        $currentOrderId = '';
        $currentLineNumber = 10;
        foreach ($data as $key => $val) {
            $data[$key]['newegg_sap_id'] = $val['sap_code'];
            $data[$key]['site'] = $val['site'];
            $data[$key]['newegg_order_id'] = $val['order_number'];
            $data[$key]['sold_to_party'] = $val['sold_to_party'];
            $data[$key]['order_type'] = 'ZPSO';
            $data[$key]['order_transaction_id'] = $val['order_number'];
            $data[$key]['creation_date'] = empty($val['order_date']) ? $val['order_date'] : substr($val['order_date'], 0, 10);
            $data[$key]['payment_date'] = '';
            $data[$key]['payment_transaction_id'] = $val['order_number'];
            $data[$key]['user_id'] = $val['customer_email_address'];
            $data[$key]['user_name'] = $val['customer_name'];
            $data[$key]['country_code'] = $val['ship_to_country_code'];
            $data[$key]['city'] = $val['ship_to_city_name'];
            $data[$key]['state_or_province'] = $val['ship_to_state_code'];
            $data[$key]['consignee_addr1'] = $val['ship_to_address1'];
            $data[$key]['consignee_addr2'] = $val['ship_to_address2'];
            $data[$key]['postal_code'] = $val['ship_to_zip_code'];
            $data[$key]['user_email'] = $val['customer_email_address'];
            $data[$key]['consignee_phone'] = $val['customer_phone_number'];
            $data[$key]['transaction_fee'] = '';
            $data[$key]['transaction_fee_currency'] = 'USD';
            $data[$key]['commission'] = '';
            $data[$key]['commission_currency'] = 'USD';
            $data[$key]['order_total_amount'] = $val['order_total_amount'];
            $data[$key]['order_total_amount_currency'] = 'USD';
            $data[$key]['shipment_code'] = $val['shipment_code'];
            $data[$key]['newegg_order_id'] = $val['order_number'];
            $data[$key]['site'] = $val['site'];

            if ($currentOrderId == $val['order_number']) {
                $currentLineNumber += 10;
            } else {
                $currentLineNumber = 10;
            }
            $data[$key]['line_number'] = $currentLineNumber;
            $currentOrderId = $val['order_number'];
            $data[$key]['newegg_sku'] = $val['newegg_item_number'];
            $data[$key]['newegg_sku_qty'] = $val['ordered_qty'];
            $data[$key]['sap_sku'] = $val['sap_sku'];
            $sapSkuQty = empty($val['s_qty']) ? null : intval(($val['t_qty'] / $val['s_qty']) * $val['ordered_qty']);
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
        $data = DB::connection('newegg')->select($sql);
        $data = json_decode(json_encode($data), true);
        $arrayData = array();
        $headArray = array('平台编号', '站点', '平台订单号', '售达方', '订单类型', '订单交易号', '付款日期', '付款交易ID', '买家ID', '买家姓名', '国家代码', '城市名', '州/省', '街道1', '街道2', '邮编', '邮箱', '电话1', '成交费', '货币', '佣金', '货币', '订单总价', '货币', '实际运输方式', '平台订单号', '站点', '行号', 'SAP物料号', '数量', '工厂', '仓库', '行项目ID', '帖子ID', '帖子标题', '销售员编号', '行交易ID', '标记完成');
        $arrayData[] = $headArray;
        $currentOrderId = '';
        $currentLineNumber = 10;
        foreach ($data as $key => $val) {
            if ($currentOrderId == $val['order_number']) {
                $currentLineNumber += 10;
            } else {
                $currentLineNumber = 10;
            }
            $currentOrderId = $val['order_number'];

            $arrayData[] = array(
                $val['sap_code'],
                $val['site'],
                $val['order_number'],
                $val['sold_to_party'],
                'ZPSO',
                $val['order_number'],
                '',
                $val['order_number'],
                $val['customer_email_address'],
                $val['customer_name'],
                $val['ship_to_country_code'],
                $val['ship_to_city_name'],
                $val['ship_to_state_code'],
                $val['ship_to_address1'],
                $val['ship_to_address2'],
                $val['ship_to_zip_code'],
                $val['customer_email_address'],
                $val['customer_phone_number'],
                '',
                'USD',
                '',
                'USD',
                $val['order_total_amount'],
                'USD',
                $val['shipment_code'],
                $val['order_number'],
                $val['site'],
                $currentLineNumber,
                $val['sap_sku'],
                empty($val['s_qty']) ? null : intval(($val['t_qty'] / $val['s_qty']) * $val['ordered_qty']),
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
            header('Content-Disposition: attachment;filename="Export_Newegg_Order_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

    //获得搜索条件并且返回对应的sql语句
    public function getSql($search)
    {
        $where = " where a.order_date >= '" . $search['from_date'] . " 00:00:00' and a.order_date <= '" . $search['to_date'] . " 23:59:59'";
        if (isset($search['order_number']) && $search['order_number']) {
            $where .= " and a.order_number = '" . $search['order_number'] . "'";
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS a.order_number, a.order_date, a.customer_email_address, a.customer_name, a.ship_to_country_code, a.ship_to_city_name, a.ship_to_state_code, a.ship_to_address1, a.ship_to_address2, a.ship_to_zip_code, a.customer_phone_number, a.order_total_amount, b.newegg_item_number, b.ordered_qty, c.s_qty, c.sap_sku, c.t_qty, c.warehouse, c.factory, c.shipment_code, d.sap_code, d.site, d.sold_to_party, d.sap_seller_id, d.sap_seller_name
				FROM newegg_order a
				JOIN newegg_item_info b ON a.order_number = b.order_number
				LEFT JOIN newegg_sku_sap_sku c ON b.newegg_item_number = c.newegg_sku
				LEFT JOIN newegg_sap_info d ON a.seller_id = d.seller_id
				{$where}
				order by a.order_number desc";

        return $sql;
    }

    public function addSkuMatch(Request $req)
    {
        $neweggSKuString = $this->getNotMatchedSkuString();
        if ($neweggSKuString) {
            return view('newegg/addSkuMatch', ['neweggSKuString' => $neweggSKuString]);
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
            return view('newegg/skuMatchList', compact('sku'));
        }
        $where = '';
        if ($sku) {
            $where = " where newegg_sku = '{$sku}' or sap_sku = '{$sku}'";
        }
        $sql = "select * from newegg_sku_sap_sku {$where} order by id desc";
        $recordsTotal = $recordsFiltered = count(DB::connection('newegg')->select($sql));
        if ($req['length'] != '-1') {
            $limit = $this->dtLimit($req);
            $sql .= " LIMIT {$limit} ";
        }

        $data = DB::connection('newegg')->select($sql);
        $data = json_decode(json_encode($data), true);
        foreach ($data as $key => $val) {
            $data[$key]['action'] = '<a href="/neweggOrderList/skuMatchEdit?sku_id=' . $val['id'] . '" target="_blank">编辑</a>';
        }
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function skuMatchEdit(Request $req)
    {
        $skuId = $req->input('sku_id');
        $data = DB::connection('newegg')->table('newegg_sku_sap_sku')->where('id', $skuId)->first();
        $data = json_decode(json_encode($data), true);
        return view('newegg/skuMatchEdit', compact('data'));
    }

    public function skuMatchUpdate(Request $req)
    {
        $skuId = $req->input('sku_id');
        $neweggSku = $req->input('newegg_sku');
        $sQty = $req->input('s_qty');
        $sapSku = $req->input('sap_sku');
        $tQty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipmentCode = $req->input('shipment_code');
        $updateArray = array('newegg_sku' => $neweggSku, 's_qty' => $sQty, 'sap_sku' => $sapSku, 't_qty' => $tQty, 'warehouse' => $warehouse, 'factory' => $factory, 'shipment_code' => $shipmentCode);

        DB::beginTransaction();
        try {
            DB::connection('newegg')->table('newegg_sku_sap_sku')->where('id', $skuId)->update($updateArray);
            DB::commit();
            $req->session()->flash('success_message', 'Update SKU match successfully');
            return redirect('neweggOrderList/skuMatchList');
        } catch (\Exception $e) {
            DB::rollBack();
            $req->session()->flash('error_message', 'Failed to update SKU match');
            return redirect()->back()->withInput();
        }
    }


    public function getNotMatchedSkuString()
    {
        $sql = "SELECT max(t.sku) AS sku, max(t.newegg_sku) AS newegg_sku
                FROM
                ( SELECT b.newegg_item_number as sku, c.newegg_sku
                  FROM newegg_item_info b
                  LEFT JOIN newegg_sku_sap_sku c ON b.newegg_item_number = c.newegg_sku
                ) AS t
                GROUP BY t.sku";
        $data = DB::connection('newegg')->select($sql);
        $data = json_decode(json_encode($data), true);
        $neweggSkuArray = array();
        foreach ($data as $v) {
            if ($v['newegg_sku'] == null) {
                $neweggSkuArray[] = $v['sku'];
            }
        }

        $neweggSKuString = "";
        if (count($neweggSkuArray) > 0) {
            $neweggSKuString = implode(',', $neweggSkuArray);
        }
        return $neweggSKuString;
    }

    public function refreshSkuMatchTable(Request $req)
    {
        $newegg_sku = $req->input('newegg_sku');
        $s_qty = $req->input('s_qty');
        $sap_sku = $req->input('sap_sku');
        $t_qty = $req->input('t_qty');
        $warehouse = $req->input('warehouse');
        $factory = $req->input('factory');
        $shipment_code = $req->input('shipment_code');

        $sku = DB::connection('newegg')->table('newegg_sku_sap_sku')->where('newegg_sku', $newegg_sku)->first();
        if ($sku) {
            echo json_encode(array('flag' => 0, 'msg' => '平台SKU已经存在'));
        }
        try {
            DB::connection('newegg')->table('newegg_sku_sap_sku')->insert(compact('newegg_sku', 's_qty', 'sap_sku', 't_qty', 'warehouse', 'factory', 'shipment_code'));
            $insertTableRow = "<tr><td>{$newegg_sku}</td><td>{$s_qty}</td><td>{$sap_sku}</td><td>{$t_qty}</td><td>{$warehouse}</td><td>{$factory}</td><td>{$shipment_code}</td></tr>";
            $neweggSKuString = $this->getNotMatchedSkuString();
            if ($neweggSKuString) {
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
        $data = DB::connection('newegg')->table('newegg_sku_sap_sku')->get()->toArray();
        $data = json_decode(json_encode($data), true);

        $arrayData = array();
        $headArray = array('ID', '平台SKU', '平台SKU的单位数量', 'SAP SKU', 'SAP SKU的数量', '仓库', '工厂', '实际运输方式');
        $arrayData[] = $headArray;
        foreach ($data as $key => $val) {
            $arrayData[] = array(
                $val['id'],
                $val['newegg_sku'],
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
            header('Content-Disposition: attachment;filename="Export_newegg_SKU_List.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

}