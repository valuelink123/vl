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
     * 补货 列表页
     * @copyright  2020年5月19日
     * @author DYS
     * return array
     */
    public function index(Request $request)
    {
        //$user = Auth::user()->toArray();// todo
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $condition = $request['condition'] ? $request['condition'] : '';
        $date = $request['date'] ? $request['date'] : '';
        $status = $request['status'] ? $request['status'] : '';
        $role = 0;//角色
        $sap_seller_id_list = $ulist = [];
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
        $sql = 'SELECT
                sh.id,
                sh.sap_seller_id,
                sh.sku,
                sh.asin,
                sh.marketplace_id,
                sh.seller_sku,
                sh.sap_warehouse_code,
                sh.sap_factory_code,
                sh.quantity,
                sh.adjustment_quantity,
                sh.rms,
                sh.received_date,
                sh.package,
                sh.`status`,
                sh.remark,
                asins.images,
                asins.title
            FROM
                shipment_requests AS sh
            LEFT JOIN asins ON asins.asin = sh.asin
            AND asins.marketplaceid = sh.marketplace_id 
            Where 1=1 ';

        if (!empty($condition)) {
            $sql .= ' AND sh.asin LIKE "%' . $condition . '%" OR sh.sku LIKE "%' . $condition . '%"';
        }
        if (!empty($date)) {
            $sql .= ' AND sh.created_at >= "' . $date . '" AND sh.created_at<= "' . $date . '"';
        }
        if (!empty($status)) {
            $sql .= ' AND sh.status = ' . $status;
        }
        $shipmentList = DB::connection('vlz')->select($sql);
        $shipmentList = (json_decode(json_encode($shipmentList), true));
        if (!empty($shipmentList)) {
            foreach ($shipmentList as $key => $value) {
                if (!in_array($value['sap_seller_id'], $sap_seller_id_list)) {
                    $sap_seller_id_list[] = $value['sap_seller_id'];
                }

            }
        }
        if (!empty($sap_seller_id_list)) {
            $userList = DB::table('users')->select('name', 'email', 'sap_seller_id', 'ubu', 'ubg')
                ->whereIn('sap_seller_id', $sap_seller_id_list)
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($userList)) {
                foreach ($userList as $k => $v) {
                    $ulist[$v['sap_seller_id']]['name'] = $v['name'];
                    $ulist[$v['sap_seller_id']]['email'] = $v['email'];
                    $ulist[$v['sap_seller_id']]['ubu'] = @$v['ubu'] ? @$v['ubu'] : '';
                    $ulist[$v['sap_seller_id']]['ubg'] = @$v['ubg'] ? @$v['ubg'] : '';
                }
            }
        }
        foreach ($shipmentList as $key => $value) {
            $shipmentList[$key]['name'] = $ulist[$value['sap_seller_id']]['name'];
            $shipmentList[$key]['email'] = $ulist[$value['sap_seller_id']]['email'];
            $shipmentList[$key]['ubu'] = $ulist[$value['sap_seller_id']]['ubu'];
            $shipmentList[$key]['ubg'] = $ulist[$value['sap_seller_id']]['ubg'];
            $shipmentList[$key]['domin_sx'] = $DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
        }
        $sql_group = 'SELECT status,COUNT(id) as count_status from shipment_requests GROUP BY status=0,status=1,status=2,status=3,status=4';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        echo $role . '-------------';

        echo '<pre>';
        var_dump($shipmentList);
        exit;
    }

    /**
     * 新增 补货 接口
     * @param Request $request
     */
    public function addShipment(Request $request)
    {
        // $user = Auth::user()->toArray();// todo
        $user = [
            'email' => 'test@qq.com',
            'id' => '159',
            'sap_seller_id' => ''
        ];//todo 只用于测试  删除
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $r_message = [];
        if (!empty($request['asin'])) {
            if (!empty($request['sku']) && !empty($request['seller_sku']) && !empty($request['warehouse']) && !empty($request['quantity']) && !empty($request['received_date']) && !empty($request['rms']) && !empty($request['package'])) {
                $warehouse = explode('-', $request['warehouse']);
                $data = [
                    'sap_seller_id' => $user['sap_seller_id'],
                    'sku' => $request['sku'],
                    'asin' => $request['asin'],
                    'seller_sku' => $request['seller_sku'],
                    'sap_warehouse_code' => $warehouse[0],
                    'sap_factory_code' => $warehouse[1],
                    'quantity' => $request['quantity'],
                    'received_date' => $request['received_date'],
                    'adjustreceived_date' => @$request['adjustreceived_date'],
                    'adjustment_quantity' => @$request['adjustment_quantity'],
                    'request_date' => date('Y-m-d', time()),
                    'rms' => $request['rms'],
                    'package' => $request['package'],
                    'marketplace_id' => $request['marketplace_id'],
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'updated_at' => date('Y-m-d H:i:s', time())
                ];
                $result = DB::connection('vlz')->table('shipment_requests')->insert($data);
                if ($result > 0) {
                    $r_message = ['status' => 1, 'msg' => '新增成功'];
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '缺少参数'];
            }
            return $r_message;
        }

        $sql = 'SELECT marketplace_id,sku from sap_asin_match_sku WHERE 1=1 ';
        //改为根据sap_seller_id 查询
        if (@$user['sap_seller_id'] > 0) {
            $sql .= ' AND sap_seller_id=' . $user['sap_seller_id'];
        }
        $sql .= ' GROUP BY sku ';
        $SKUList = DB::connection('vlz')->select($sql);
        $SKUList = (json_decode(json_encode($SKUList), true));
        return $SKUList;
    }

    /**
     * @param Request $request
     * 补货 详情页面
     * @param id
     * @author DYS
     */
    public function detailShipment(Request $request)
    {
        //$user = Auth::user()->toArray();// todo
        $role = 0;
        $user = [
            'email' => 'test@qq.com',
            'id' => '159',
            'sap_seller_id' => ''
        ];//todo 只用于测试  删除
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        if (!empty($user['email']) && !empty($user)) {
            if (in_array($user['email'], $ADMIN_EMAIL) || $user['sap_seller_id'] > 0) {
                /**  销售角色 */
                $role = 1;
            } else {
                //role_id = 23 代表 计划员
                $roleUser = DB::table('role_user')->select('user_id')
                    ->where('user_id', $user['id'])
                    ->where('role_id', 23)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($roleUser)) {
                    /** 计划员角色  */
                    $role = 2;
                }
            }
        }
        if (!empty($request['id']) && $request['id'] > 0) {
            $sql = "SELECT id,`status`,sku,asin,seller_sku,sap_warehouse_code,sap_factory_code,quantity,received_date,rms,package,remark,adjustment_quantity,adjustreceived_date from shipment_requests WHERE id =" . $request['id'];
            $shipment = DB::connection('vlz')->select($sql);
            $shipment = (json_decode(json_encode($shipment), true));
            if (!empty($shipment[0]['sap_warehouse_code']) && !empty($shipment[0]['sap_factory_code'])) {
                $shipment[0]['warehouse'] = $shipment[0]['sap_warehouse_code'] . '-' . $shipment[0]['sap_factory_code'];
            }

        }
        return [$role, $shipment];
    }

    /**
     * @param Request $request
     * 修改 补货
     */
    public function upShipment(Request $request)
    {
        $r_message = [];
        $id = @$request['id'];
        if ($id > 0 && !empty($request['warehouse'])) {
            $warehouse = explode('-', $request['warehouse']);
            $data = [
                'status' => @$request['status'],
                'sku' => @$request['sku'],
                'asin' => @$request['asin'],
                'seller_sku' => @$request['seller_sku'],
                'sap_warehouse_code' => $warehouse[0],
                'sap_factory_code' => $warehouse[1],
                'quantity' => @$request['quantity'],
                'received_date' => @$request['received_date'],
                'rms' => @$request['rms'],
                'package' => @$request['package'],
                'remark' => @$request['remark'] ? $request['remark'] : '',
                'adjustment_quantity' => @$request['adjustment_quantity'],
                'adjustreceived_date' => @$request['adjustreceived_date']
            ];
            if (!empty($data)) {
                $result = DB::connection('vlz')->table('shipment_requests')
                    ->where('id', $id)
                    ->update($data);
                if ($result > 0) {
                    $r_message = ['status' => 1, 'msg' => '更新成功'];
                } else {
                    $r_message = ['status' => 0, 'msg' => '更新失败'];
                }
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
        }
        return $r_message;
    }

    /**
     * @param Request $request
     * @return array
     * 批量更新 调拨计划  状态
     */
    public function upAllStatus(Request $request)
    {
        //$user = Auth::user()->toArray();// todo
        $r_message = [];
        $idList = explode(',', $request['idList']);
        if (!empty($request['status']) && !empty($idList)) {
            $data = ['status' => $request['status']];
            $result = DB::connection('vlz')->table('shipment_requests')
                ->whereIn('id', $idList)
                ->update($data);
            if ($result > 0) {
                $r_message = ['status' => 1, 'msg' => '全部更新成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '更新失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID或状态'];
        }
        return $r_message;
    }

    /**
     * @param Request $request
     * 根据sku 查询 asin seller_sku sap_warehouse_code 列表
     * @author DYS
     * @return  Array
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
    /** ==========================采购页面 方法===============================================================*/

    /**
     * 采购 列表页
     * @copyright  2020年5月19日
     * @author DYS
     * return array
     */
    public function purchaseList(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        //$user = Auth::user()->toArray();// todo
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $condition = @$request['condition'] ? $request['condition'] : '';
        $date = @$request['date'] ? $request['date'] : '';
        $status = @$request['status'] ? $request['status'] : '';
        $role = 0;//角色
        $sap_seller_id_list = $ulist = [];
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
        $sql = 'SELECT
                pr.id,
                pr.sap_seller_id,
                pr.sku,
                pr.asin,
                pr.marketplace_id,
                pr.seller_sku,
                pr.sap_warehouse_code,
                pr.sap_factory_code,
                pr.quantity,
                pr.request_date,
                pr.received_date,
                pr.remark,
                pr.`status`,
                pr.received_factory,
                pr.sap_shipment_code,
                pr.confirmed_quantity,
                pr.estimated_delivery_date,
                pr.order_number,
                pr.created_at,
                asins.images,
                asins.title
            FROM
                purchase_requests AS pr
            LEFT JOIN asins ON asins.asin = pr.asin
            AND asins.marketplaceid = pr.marketplace_id
            Where 1=1 ';

        if (!empty($condition)) {
            $sql .= ' AND pr.asin LIKE "%' . $condition . '%" OR pr.sku LIKE "%' . $condition . '%"';
        }
        if (!empty($date)) {
            $sql .= ' AND pr.created_at >= "' . $date . '" AND pr.created_at <= "' . $date . '"';
        }
        if (!empty($status)) {
            $sql .= ' AND pr.status = ' . $status;
        }
        $purchase_requests = DB::connection('vlz')->select($sql);
        $purchase_requests = (json_decode(json_encode($purchase_requests), true));
        if (!empty($purchase_requests)) {
            foreach ($purchase_requests as $key => $value) {
                if (!in_array($value['sap_seller_id'], $sap_seller_id_list)) {
                    $sap_seller_id_list[] = $value['sap_seller_id'];
                }

            }
        }
        if (!empty($sap_seller_id_list)) {
            $userList = DB::table('users')->select('name', 'email', 'sap_seller_id', 'ubu', 'ubg')
                ->whereIn('sap_seller_id', $sap_seller_id_list)
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($userList)) {
                foreach ($userList as $k => $v) {
                    $ulist[$v['sap_seller_id']]['name'] = $v['name'];
                    $ulist[$v['sap_seller_id']]['email'] = $v['email'];
                    $ulist[$v['sap_seller_id']]['ubu'] = @$v['ubu'] ? @$v['ubu'] : '';
                    $ulist[$v['sap_seller_id']]['ubg'] = @$v['ubg'] ? @$v['ubg'] : '';
                }
            }
        }
        foreach ($purchase_requests as $key => $value) {
            $purchase_requests[$key]['name'] = $ulist[$value['sap_seller_id']]['name'];
            $purchase_requests[$key]['email'] = $ulist[$value['sap_seller_id']]['email'];
            $purchase_requests[$key]['ubu'] = $ulist[$value['sap_seller_id']]['ubu'];
            $purchase_requests[$key]['ubg'] = $ulist[$value['sap_seller_id']]['ubg'];
            $purchase_requests[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
        }
        $sql_group = 'SELECT status,COUNT(id) as count_status from purchase_requests GROUP BY status=0,status=1,status=2,status=3,status=4';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        return [$role, $status_group, $purchase_requests];
    }

    /**
     * @param Request $request
     * @return array
     * 批量更新 采购计划 状态
     */
    public function upAllPurchase(Request $request)
    {
        $r_message = [];
        $idList = explode(',', $request['idList']);
        if (!empty($request['status']) && !empty($idList)) {
            $data = ['status' => $request['status']];
            $result = DB::connection('vlz')->table('purchase_requests')
                ->whereIn('id', $idList)
                ->update($data);
            if ($result > 0) {
                $r_message = ['status' => 1, 'msg' => '全部更新成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '更新失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID或状态'];
        }
        return $r_message;
    }

    /**
     * @param Request $request
     * 采购 详情页面
     * @param id
     * @author DYS
     */
    public function detailPurchase(Request $request)
    {
        //$user = Auth::user()->toArray();// todo
        $role = 0;
        $user = [
            'email' => 'test@qq.com',
            'id' => '159',
            'sap_seller_id' => ''
        ];//todo 只用于测试  删除
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        if (!empty($user['email']) && !empty($user)) {
            if (in_array($user['email'], $ADMIN_EMAIL) || $user['sap_seller_id'] > 0) {
                /**  销售角色 */
                $role = 1;
            } else {
                //role_id = 23 代表 计划员
                $roleUser = DB::table('role_user')->select('user_id')
                    ->where('user_id', $user['id'])
                    ->where('role_id', 23)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($roleUser)) {
                    /** 计划员角色  */
                    $role = 2;
                }
            }
        }
        if (!empty($request['id']) && $request['id'] > 0) {
            $sql = "SELECT
                    pr.id,
                    pr.`status`,
                    pr.sku,
                    pr.asin,
                    pr.seller_sku,
                    pr.sap_warehouse_code,
                    pr.sap_factory_code,
                    pr.quantity,
                    pr.received_date,
                    pr.remark,
                    pr.received_factory,
                    pr.sap_shipment_code,
                    pr.confirmed_quantity,
                    pr.estimated_delivery_date
                FROM
                    purchase_requests AS pr
                WHERE  pr.id =" . $request['id'];
            $Purchase = DB::connection('vlz')->select($sql);
            $Purchase = (json_decode(json_encode($Purchase), true));
            if (!empty($Purchase[0]['sap_warehouse_code']) && !empty($Purchase[0]['sap_factory_code'])) {
                $Purchase[0]['warehouse'] = $Purchase[0]['sap_warehouse_code'] . '-' . $Purchase[0]['sap_factory_code'];
            }
        }
        return [$role, $Purchase];
    }

    /**
     * 新增采购 接口
     * @param Request $request
     */
    public function addPurchase(Request $request)
    {
        // $user = Auth::user()->toArray();// todo
        $user = [
            'email' => 'test@qq.com',
            'id' => '159',
            'sap_seller_id' => 279
        ];//todo 只用于测试  删除
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $r_message = [];
        if (!empty($request['asin'])) {
            if (!empty($request['sku']) && !empty($request['seller_sku']) && !empty($request['quantity']) && !empty($request['received_date']) && !empty($request['asin']) && !empty($request['package'])) {
                $warehouse = explode('-', $request['warehouse']);
                $data = [
                    'sap_seller_id' => $user['sap_seller_id'],
                    'status' => $request['status'],
                    'sku' => $request['sku'],
                    'asin' => $request['asin'],
                    'seller_sku' => $request['seller_sku'],
                    'sap_warehouse_code' => @$warehouse[0],
                    'sap_factory_code' => @$warehouse[1],
                    'quantity' => $request['quantity'],
                    'received_date' => @$request['received_date'],
                    'remark' => @$request['remark'],
                    'received_factory' => @$request['received_factory'],
                    'sap_shipment_code' => @$request['sap_shipment_code'],
                    'confirmed_quantity' => @$request['confirmed_quantity'],
                    'estimated_delivery_date' => @$request['estimated_delivery_date'],
                    'marketplace_id' => @$request['marketplace_id'],
                    'created_at' => date('Y-m-d H:i:s', time())
                ];
                $result = DB::connection('vlz')->table('purchase_requests')->insert($data);
                if ($result > 0) {
                    $r_message = ['status' => 1, 'msg' => '新增成功'];
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '缺少参数'];
            }
            return $r_message;
        }
        //查询asinlist
        $sql = 'SELECT marketplace_id,sku from sap_asin_match_sku WHERE 1=1 ';
        //改为根据sap_seller_id 查询
        if (@$user['sap_seller_id'] > 0) {
            $sql .= ' AND sap_seller_id=' . $user['sap_seller_id'];
        }
        $sql .= ' GROUP BY sku ';
        $SKUList = DB::connection('vlz')->select($sql);
        $SKUList = (json_decode(json_encode($SKUList), true));
        return $SKUList;
    }
    /**
     * @param Request $request
     * 更新 采购
     */
    public function upPurchase(Request $request)
    {
        $r_message = [];
        $id = @$request['id'];
        if ($id > 0 && !empty($request['warehouse'])) {
            $warehouse = explode('-', $request['warehouse']);
            $data = [
                'status' => $request['status'],
                'sku' => $request['sku'],
                'asin' => $request['asin'],
                'seller_sku' => $request['seller_sku'],
                'sap_warehouse_code' => @$warehouse[0],
                'sap_factory_code' => @$warehouse[1],
                'quantity' => $request['quantity'],
                'received_date' => @$request['received_date'],
                'remark' => @$request['remark'],
                'received_factory' => @$request['received_factory'],
                'sap_shipment_code' => @$request['sap_shipment_code'],
                'confirmed_quantity' => @$request['confirmed_quantity'],
                'estimated_delivery_date' => @$request['estimated_delivery_date'],
                'marketplace_id' => @$request['marketplace_id']
            ];
            if (!empty($data)) {
                $result = DB::connection('vlz')->table('purchase_requests')
                    ->where('id', $id)
                    ->update($data);
                if ($result > 0) {
                    $r_message = ['status' => 1, 'msg' => '更新成功'];
                } else {
                    $r_message = ['status' => 0, 'msg' => '更新失败'];
                }
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
        }
        return $r_message;
    }

}