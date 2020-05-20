<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Asin;
use App\RsgProduct;
use phpDocumentor\Reflection\Types\Array_;
use PHPExcel_IOFactory;
use PHPExcel;


header('Access-Control-Allow-Origin:*');

class ShipmentController extends Controller
{
    //判断是否登录  todo 上线需打开
    public function __construct()
    {
//        $this->middleware('auth');
//        parent::__construct();
    }

    /**
     * 列表页
     * @copyright  2020年5月19日
     * @author DYS
     * return array
     */
    public function List(Request $request)
    {
        //$user = Auth::user()->toArray();
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $condition = $request['condition'] ? $request['condition'] : '';
        $date = $request['date'] ? $request['date'] : '';
        $role = 0;//角色
        if (!empty($user)) {
            if (!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) {
                /**  特殊权限着 查询所有用户 */
                $allUsers = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id', 'seller_rules', 'ubg', 'ubu')
                    ->where('ubu', '!=', "")
                    ->orwhere('ubg', '!=', "")
                    ->orwhere('seller_rules', '!=', "")
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($allUsers)) {
                    foreach ($allUsers as $auk => $auv) {
                        $sapSellerIdList[] = $auv['sap_seller_id'];
                    }
                }
                $role = 4;
            } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**查询所有BG下面员工*/
                    $role = 3;
                } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                    /**此条件为 普通销售*/
                    $role = 1;
                } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**  BU 负责人  */
                    $role = 2;
                }
            }
        }
        $sql = 'SELECT * from shipment_requests WHERE 1=1 ';
        if (!empty($condition)) {
            $sql .= ' AND asin LIKE "%' . $condition . '%" OR sku LIKE "%' . $condition . '%"';
        }
        if (!empty($date)) {
            $sql .= ' AND created_at >= "' . $date . '" AND created_at<= "' . $date . '"';
        }
        $shipmentList = DB::connection('vlz')->select($sql);
        $shipmentList = (json_decode(json_encode($shipmentList), true));
        $sql_group = 'SELECT status,COUNT(id) as count_status from shipment_requests GROUP BY status=0,status=1,status=2,status=3,status=4';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        echo $role . '-------------';

        echo '<pre>';
        var_dump($shipmentList);
        exit;
    }

    /**
     * 新增(编辑) 接口
     * @param Request $request
     */
    public function addShipment(Request $request)
    {
        $user = Auth::user()->toArray();
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $role = 0;//角色
        if(!empty($request['sku'])&&!empty($request['asin'])&&!empty($request['seller_sku'])&&!empty($request['warehouse'])&&!empty($request['quantity'])&&!empty($request['received_date'])&&!empty($request['rms'])&&!empty($request['package'])){
            $data=[
                'sku'=>$request['sku'],
                'asin'=>$request['asin'],
                ''=>$request['seller_sku'],
                ''=>$request['warehouse'],
                ''=>$request['quantity'],
                ''=>$request['received_date'],
                ''=>$request['rms'],
                ''=>$request['package'],
                'marketplace_id'=>$request['marketplace_id'],
            ];

        }
        if (!empty($user)) {
            if (!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) {
                /**  特殊权限着 查询所有用户 */
                $role = 4;
            } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**查询所有BG下面员工*/
                    $role = 3;
                } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                    /**此条件为 普通销售*/
                    $role = 1;
                } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**  BU 负责人  */
                    $role = 2;
                }
            }
        }
        $sql = 'SELECT marketplace_id,sku,sap_seller_id from sap_asin_match_sku WHERE 1=1 ';
//        if ($role == 3) {
//            $sql .= ' AND sap_seller_bg ="' . $user['ubg'] . '" ';
//        } else if ($role == 2) {
//            $sql .= ' AND sap_seller_bg ="' . $user['ubg'] . '" AND sap_seller_bu ="' . $user['ubu'] . '" ';
//        } else if ($role == 1) {
//            if (@$user['sap_seller_id'] > 0) {
//                $sql .= ' AND sap_seller_id=' . $user['sap_seller_id'];
//            }
//        }
        //改为根据sap_seller_id 查询
        if (@$user['sap_seller_id'] > 0) {
            $sql .= ' AND sap_seller_id=' . $user['sap_seller_id'];
        }
        $sql .= ' GROUP BY sku ';
        $SKUList = DB::connection('vlz')->select($sql);
        $SKUList = (json_decode(json_encode($SKUList), true));
        $data = [
            'SKUList' => $SKUList,
        ];
        return view('add.test', $data);
    }

    /**
     * @param Request $request
     * 根据sku 查询 asin seller_sku sap_warehouse_code 列表
     * @author DYS
     * @return  Array_
     */
    public function getNextData(Request $request)
    {
        $data = [];
        if (!empty($request['sku']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT asin,sku,marketplace_id FROM sap_asin_match_sku WHERE sku='" . $request['sku'] . "' AND marketplace_id='" . $request['marketplace_id'] . "' GROUP BY asin";
            $asinList = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($asinList), true));
        }
        if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT marketplace_id,seller_sku from sap_asin_match_sku WHERE asin ='" . $request['asin'] . "' AND marketplace_id ='" . $request['marketplace_id'] . "' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
        }
        if (!empty($request['seller_sku']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT seller_sku,sap_warehouse_code,sap_factory_code from sap_asin_match_sku WHERE seller_sku ='" . $request['seller_sku'] . "' AND marketplace_id ='" . $request['marketplace_id'] . "' GROUP BY sap_warehouse_code;";
            $warehouse = DB::connection('vlz')->select($sql);
            $warehouse = (json_decode(json_encode($warehouse), true));
            if (!empty($warehouse)) {
                foreach ($warehouse as $key => $v) {
                    $data[$key]['warehouse'] = $v['sap_warehouse_code'] . '-' . $v['sap_factory_code'];
                }
            }
        }

        return $data;
    }
}