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
        $date_s = $request['date_s'] ? $request['date_s'] : '';
        $date_e = $request['date_e'] ? $request['date_e'] : '';
        $status = $request['status'] ? $request['status'] : '';
        $role = 0;//角色
        $sap_seller_id_list = $ulist = $allotIdList = $seller = $labelList = $statusList = [];
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
        $sql = 'SELECT
                sh.id,
                sh.sap_seller_id,
                sh.sku,
                sh.asin,
                sh.label,
                sh.marketplace_id,
                sh.seller_sku,
                sh.sap_warehouse_code,
                sh.sap_factory_code,
                sh.quantity,
                sh.adjustment_quantity,
                sh.rms,
                sh.rms_sku,
                sh.received_date,
                sh.package,
                sh.`status`,
                sh.remark,
                sh.out_warehouse,
                sh.created_at,
                sh.stock_day_num,
                sh.FBA_Stock,
                sh.FBA_keepday_num,
                sh.transfer_num,
                sh.adjustreceived_date,
                sh.allor_status,
                sh.cargo_data,
                asins.images,
                asins.id as asin_id
            FROM
                shipment_requests AS sh
            LEFT JOIN asins ON asins.asin = sh.asin
            AND asins.marketplaceid = sh.marketplace_id 
            Where 1=1 ';

        if (!empty($condition)) {
            $sql .= ' AND sh.asin LIKE "%' . $condition . '%" OR sh.sku LIKE "%' . $condition . '%"';
        }
        if (!empty($date_s) && !empty($date_e)) {
            $sql .= ' AND sh.created_at >= "' . $date_s . '" AND sh.created_at<= "' . $date_e . '"';
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
                if (!in_array($value['label'], $labelList)) {
                    $labelList[] = $value['label'];
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
        $sql2 = 'SELECT id,shipment_requests_id FROM allot_progress';
        $allot_progress = DB::connection('vlz')->select($sql2);
        $allot_progress = (json_decode(json_encode($allot_progress), true));
        if (!empty($allot_progress)) {
            foreach ($allot_progress as $ak => $av) {
                $allotIdList[$av['shipment_requests_id']] = $av['id'];
            }
        }
        foreach ($shipmentList as $key => $value) {
            $shipmentList[$key]['name'] = $ulist[$value['sap_seller_id']]['name'];
            $shipmentList[$key]['email'] = $ulist[$value['sap_seller_id']]['email'];
            $shipmentList[$key]['ubu'] = $ulist[$value['sap_seller_id']]['ubu'];
            $shipmentList[$key]['ubg'] = $ulist[$value['sap_seller_id']]['ubg'];
            $shipmentList[$key]['domin_sx'] = $DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
            $shipmentList[$key]['warehouse'] = $value['sap_warehouse_code'] . '-' . $value['sap_factory_code'];
            $shipmentList[$key]['image'] = explode(',', $value['images'])[0];
            $shipmentList[$key]['allot'] = @$allotIdList[$value['id']] ? $allotIdList[$value['id']] : 0;
            if (!in_array($ulist[$value['sap_seller_id']]['name'], $seller)) {
                $seller[] = $ulist[$value['sap_seller_id']]['name'];
            }

        }
        $sql_group = 'SELECT status,COUNT(id) as count_num from shipment_requests GROUP BY status=0,status=1,status=2,status=3,status=4';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        if (!empty($status_group)) {
            foreach ($status_group as $sk => $sv) {
                $statusList['status' . $sv['status']] = $sv['count_num'];
            }
        }
        return [$shipmentList, $statusList, $seller, $labelList];
    }

    /**
     * 保存大货资料
     * @param Request $request
     */
    public function upCargoData(Request $request)
    {
        $data = [
            'cargo_data' => $request['cargo_data']
        ];
        $id = $request['id'];
        if (!empty($data) && !empty($id)) {
            $result = DB::connection('vlz')->table('shipment_requests')
                ->where('id', $id)
                ->update($data);
            if ($result > 0) {
                $r_message = ['status' => 1, 'msg' => '保存成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '保存失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少数据'];
        }
        return $r_message;
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
            'sap_seller_id' => '358'
        ];//todo 只用于测试  删除
        /** 超级权限*/
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $r_message = $seller_skus = $seller_accounts = $asins = [];
        $label = $batch_num = $stock_day_num = $transfer_num = '';
        if (!empty($request['asin'])) {
            if (!empty($request['asin']) && !empty($request['sku']) && !empty($request['seller_sku']) && !empty($request['warehouse']) && !empty($request['quantity']) && !empty($request['received_date'])) {
                $warehouse = explode('-', $request['warehouse']);
                //查询亚马逊 label
                $seller_skus = DB::connection('vlz')->table('seller_skus')
                    ->select('id', 'seller_account_id')
                    ->where('asin', $request['asin'])
                    ->where('seller_sku', $request['seller_sku'])
                    ->where('marketplaceid', $request['marketplace_id'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($seller_skus)) {
                    $seller_account_id = $seller_skus[0]['seller_account_id'];
                    if ($seller_account_id > 0) {
                        $seller_accounts = DB::connection('vlz')->table('seller_accounts')
                            ->select('id', 'label', 'mws_seller_id')
                            ->where('id', $seller_account_id)
                            ->get()->map(function ($value) {
                                return (array)$value;
                            })->toArray();
                        if (!empty($seller_accounts)) {
                            $label = $seller_accounts[0]['label'];
                        }
                    }
                }

                if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
                    $sql = "SELECT
                            afn_sellable,
                            afn_reserved,
                            (
                                sales_4_weeks / 28 * 0.5 + sales_2_weeks / 14 * 0.3 + sales_1_weeks / 7 * 0.2
                            ) AS daily_sales,
                            id 
                            FROM
                            asins
                            WHERE
                            asin = '" . $request['asin'] . "'
                            AND marketplaceid = '" . $request['marketplace_id'] . "'";
                    $asins = DB::connection('vlz')->select($sql);
                    $asins = (json_decode(json_encode($asins), true));
                }
                if (!empty($asins)) {
                    $FBA_Stock = $asins[0]['afn_sellable'] + $asins[0]['afn_reserved'];
                    if (@$asins[0]['daily_sales'] > 0) {
                        $stock_day_num = $FBA_Stock + $request['quantity'] / @$asins[0]['daily_sales'];
                    }
                    $FBA_keepday_num = (round($asins[0]['daily_sales'], 2) == 0) ? '∞' : date('Y-m-d', strtotime('+' . intval(($asins[0]['afn_sellable'] + $asins[0]['afn_reserved']) / round($asins[0]['daily_sales'], 2)) . 'days'));
                    $transfer_num = $request['quantity'];
                }

                $data = [
                    'sap_seller_id' => @$user['sap_seller_id'],
                    'sku' => $request['sku'],
                    'asin' => $request['asin'],
                    'status' => @$request['status'] ? $request['status'] : 0,
                    'seller_sku' => $request['seller_sku'],
                    'label' => $label,
                    'sap_warehouse_code' => $warehouse[0],
                    'sap_factory_code' => $warehouse[1],
                    'out_warehouse' => $request['out_warehouse'],
                    'quantity' => $request['quantity'],
                    'received_date' => $request['received_date'],
                    'adjustreceived_date' => @$request['adjustreceived_date'] ? $request['adjustreceived_date'] : $request['received_date'],
                    'adjustment_quantity' => @$request['quantity'],
                    'request_date' => $request['request_date'],
                    'rms' => @$request['rms'],
                    'rms_sku' => @$request['rms_sku'],
                    'package' => $request['package'],
                    'marketplace_id' => $request['marketplace_id'],
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'FBA_Stock' => $FBA_Stock,
                    'stock_day_num' => $stock_day_num,
                    'FBA_keepday_num' => $FBA_keepday_num,
                    'transfer_num' => $transfer_num
                ];
                $result_id = DB::connection('vlz')->table('shipment_requests')->insertGetId($data);
                if ($result_id > 0) {
                    $sx = $DOMIN_MARKETPLACEID_SX[$request['marketplace_id']];
                    $batch_num = $sx . date('Ymd', time()) . $result_id;
                    if (!empty($batch_num)) {
                        $updata = ['batch_num' => $batch_num];
                        $result = DB::connection('vlz')->table('shipment_requests')
                            ->where('id', $result_id)
                            ->update($updata);
                        if ($result > 0) {
                            $r_message = ['status' => 1, 'msg' => '新增成功'];
                        }
                    }
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
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $sku=null;
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
            $sql = "SELECT marketplace_id,out_warehouse,id,`status`,sku,asin,seller_sku,sap_warehouse_code,sap_factory_code,quantity,received_date,rms,rms_sku,package,remark,adjustment_quantity,adjustreceived_date from shipment_requests WHERE id =" . $request['id'];
            $shipment = DB::connection('vlz')->select($sql);
            $shipment = (json_decode(json_encode($shipment), true));
            if (!empty($shipment[0]['sap_warehouse_code']) && !empty($shipment[0]['sap_factory_code'])) {
                //$shipment[0]['sap_warehouse_code'] . '-' .
                $shipment[0]['warehouse'] =  $shipment[0]['sap_factory_code'];
                $shipment[0]['marketplace_id_sx'] = $DOMIN_MARKETPLACEID_SX[$shipment[0]['marketplace_id']];
            }
            $shipment_new = $shipment[0];
            $shipment_new['role'] = $role;
        }
        if (!empty($shipment_new['asin']) && !empty($shipment_new['marketplace_id'])) {
            $sql = "SELECT sku,seller_sku from sap_asin_match_sku WHERE asin='".$shipment_new['asin']."' AND marketplace_id='".$shipment_new['marketplace_id']."' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            if(!empty($data)){
                $sku=$data[0]['sku'];
            }
            $sql2 = "SELECT sap_factory_code from sap_asin_match_sku WHERE  marketplace_id='".$shipment_new['marketplace_id']."' AND sap_factory_code != '' GROUP BY sap_factory_code;";
            $sellersku2 = DB::connection('vlz')->select($sql2);
            $data2 = (json_decode(json_encode($sellersku2), true));
        }
        return ['shipment'=>$shipment_new,'seller_sku_list'=>$data,'factoryList'=>$data2,'sku'=>$sku];


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
                'out_warehouse' => @$request['out_warehouse'],
                'received_date' => @$request['received_date'],
                'rms' => @$request['rms'],
                'rms_sku' => @$request['rms_sku'],
                'package' => @$request['package'],
                'remark' => @$request['remark'] ? $request['remark'] : '',
                'adjustment_quantity' => @$request['adjustment_quantity'],
                'adjustreceived_date' => @$request['adjustreceived_date'],
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            if (!empty($data)) {
                $result = DB::connection('vlz')->table('shipment_requests')
                    ->where('id', $id)
                    ->update($data);
                if ($result > 0) {
                    //已确认 状态 添加到 调拨进度表 allot_progress 表
                    if (@$request['status'] == 4 && $id > 0) {
                        $data = ['shipment_requests_id' => $id, 'created_at' => date('Y-m-d H:i:s', time())];
                        DB::connection('vlz')->table('allot_progress')->insert($data);
                    }
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
        if (!empty($idList)) {
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
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
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

    /**
     * 获取sku seller_sku列表、仓库、sku
     * @param Request $request
     * @return array
     */
    public function getSellerSku(Request $request)
    {
        $data = [];
        $sx=$sku=null;
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        if (!empty($request['marketplace_id'])) {
            $sx = array_search($request['marketplace_id'], $DOMIN_MARKETPLACEID_SX);
        }
        if (!empty($request['asin']) && !empty($sx)) {
            $sql = "SELECT sku,seller_sku from sap_asin_match_sku WHERE asin='".$request['asin']."' AND marketplace_id='".$sx."' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            if(!empty($data)){
                $sku=$data[0]['sku'];
            }
            $sql2 = "SELECT sap_factory_code from sap_asin_match_sku WHERE  marketplace_id='".$sx."' AND sap_factory_code != '' GROUP BY sap_factory_code;";
            $sellersku2 = DB::connection('vlz')->select($sql2);
            $data2 = (json_decode(json_encode($sellersku2), true));
        }
        return ['seller_sku_list'=>$data,'factoryList'=>$data2,'sku'=>$sku];
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
        $date_s = $request['date_s'] ? $request['date_s'] : '';
        $date_e = $request['date_e'] ? $request['date_e'] : '';
        $status = @$request['status'] ? $request['status'] : '';
        $role = 0;//角色
        $sap_seller_id_list = $ulist = $seller = $statusList = [];
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
                pr.overseas_stock,
                pr.backlog_order,
                pr.day_sales,
                pr.PCS,
                pr.profit_margin,
                pr.planning_name,
                pr.MOQ,
                pr.received_factory,
                pr.sap_shipment_code,
                pr.confirmed_quantity,
                pr.estimated_delivery_date,
                pr.order_number,
                pr.created_at,
                pr.complete_status,
                asins.images
            FROM
                purchase_requests AS pr
            LEFT JOIN asins ON asins.asin = pr.asin
            AND asins.marketplaceid = pr.marketplace_id
            Where 1=1 ';

        if (!empty($condition)) {
            $sql .= ' AND pr.asin LIKE "%' . $condition . '%" OR pr.sku LIKE "%' . $condition . '%"';
        }
        if (!empty($date_s) && !empty($date_e)) {
            $sql .= ' AND pr.created_at >= "' . $date_s . '" AND pr.created_at <= "' . $date_e . '"';
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
            $purchase_requests[$key]['name'] = @$ulist[$value['sap_seller_id']]['name'];
            $purchase_requests[$key]['email'] = @$ulist[$value['sap_seller_id']]['email'];
            $purchase_requests[$key]['ubu'] = @$ulist[$value['sap_seller_id']]['ubu'];
            $purchase_requests[$key]['ubg'] = @$ulist[$value['sap_seller_id']]['ubg'];
            $purchase_requests[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
            //$purchase_requests[$key]['warehouse'] = $value['sap_warehouse_code'] . '-' . $value['sap_factory_code'];
            $purchase_requests[$key]['image'] = explode(',', $value['images'])[0];
            if (!in_array(@$ulist[$value['sap_seller_id']]['name'], $seller)) {
                $seller[] = @$ulist[$value['sap_seller_id']]['name'];
            }
        }
        $sql_group = 'SELECT status,COUNT(id) as count_status from purchase_requests GROUP BY status=0,status=1,status=2,status=3,status=4';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        if (!empty($status_group)) {
            foreach ($status_group as $sk => $sv) {
                $statusList['status' . $sv['status']] = $sv['count_status'];
            }
        }
        return [$purchase_requests, $statusList, $seller];
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
        if ((@$request['status'] >= 0) && !empty($idList)) {
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
        $FBA_Stock = $overseas_stock = $day_sales = $profit_margin = $planning_name = '';
        $profit_margin = $amount_income = $amount_refund = $cost = $backlog_order = $MOQ = $PCS = 0;
        if (!empty($request['asin'])) {
            if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
                $sql = "SELECT
                            afn_sellable,
                            afn_reserved,
                            (
                                sales_4_weeks / 28 * 0.5 + sales_2_weeks / 14 * 0.3 + sales_1_weeks / 7 * 0.2
                            ) AS daily_sales,
                            id,mfn_sellable
                            FROM
                            asins
                            WHERE
                            asin = '" . $request['asin'] . "'
                            AND marketplaceid = '" . $request['marketplace_id'] . "'";
                $asins = DB::connection('vlz')->select($sql);
                $asins = (json_decode(json_encode($asins), true));
                /** 查询28天内的 数据*/
                $sql1 = "SELECT id,date,amount_income,amount_refund,cost from daily_statistics WHERE asin ='" . $request['asin'] . "' and marketplace_id='" . $request['marketplace_id'] . "'  AND date_sub(CURDATE(),INTERVAL 28 DAY) <= date";
                $daily_statistics = DB::connection('vlz')->select($sql1);
                $daily_statistics = (json_decode(json_encode($daily_statistics), true));
                if (!empty($daily_statistics)) {

                    foreach ($daily_statistics as $dk => $dv) {
                        $amount_income += $dv['amount_income'];
                        $amount_refund += $dv['amount_refund'];
                        $cost += $dv['cost'];
                    }
                    /** @var  $profit_margin 利润率 */
                    $profit_margin = ($amount_income + $amount_refund - $cost) / $amount_income;
                }
            }
            if (!empty($asins)) {
                $FBA_Stock = $asins[0]['afn_sellable'] + $asins[0]['afn_reserved'];
                $overseas_stock = $FBA_Stock + $request['quantity'] + $asins[0]['mfn_sellable'];//海外库存
                $day_sales = @$asins[0]['daily_sales'];//加权日均
            }
            if (!empty($request['sku']) && !empty($request['marketplace_id']) && !empty($request['request_date']) && !empty($request['asin'])) {
                $sql2 = "SELECT id,actual_delivery_date,sum(quantity) as sum_quantity FROM sap_purchases WHERE sku = '" . $request['sku'] . "' AND  actual_delivery_date is NULL";
                $sap_purchases = DB::connection('vlz')->select($sql2);
                $sap_purchases = (json_decode(json_encode($sap_purchases), true));
                if (!empty($sap_purchases)) {
                    /** 未交订单 */
                    $backlog_order = @$sap_purchases[0]['sum_quantity'];
                }
                $sql3 = "SELECT id,sap_seller_id FROM sap_asin_match_sku  WHERE sku='" . $request['sku'] . "' and marketplace_id ='" . $request['marketplace_id'] . "'";
                $sap_asin_match_sku = DB::connection('vlz')->select($sql3);
                $sap_asin_match_sku = (json_decode(json_encode($sap_asin_match_sku), true));
                if (!empty($sap_asin_match_sku)) {
                    $sap_seller_id = $sap_asin_match_sku[0]['sap_seller_id'];
                }
                if ($sap_seller_id > 0) {
                    $Users = DB::table('users')->select('id', 'name')
                        ->where('sap_seller_id', $sap_seller_id)
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->first();
                    if (!empty($Users)) {
                        /**  计划员名字  */
                        $planning_name = $Users['name'];
                    }
                }
                /**  查询 最小起订量 MOQ */
                $sql4 = "SELECT id,min_purchase_quantity FROM sap_purchase_records WHERE sku ='" . $request['sku'] . "' ORDER BY id DESC";
                $sap_purchase_records = DB::connection('vlz')->select($sql4);
                $sap_purchase_records = (json_decode(json_encode($sap_purchase_records), true));
                if (!empty($sap_purchase_records)) {
                    $MOQ = $sap_purchase_records[0]['min_purchase_quantity'];
                }
                /** 到货后7天  销售计划总和*/
                $sql5 = "SELECT id,quantity_last,date,sum(quantity_last) as sum_quantity from asin_sales_plans WHERE asin='" . $request['asin'] . "'  AND marketplace_id = '" . $request['marketplace_id'] . "' AND date > NOW() AND  date <= date_add(now(), interval 7 day)";
                $asin_sales_plans = DB::connection('vlz')->select($sql5);
                $asin_sales_plans = (json_decode(json_encode($asin_sales_plans), true));
                if (!empty($asin_sales_plans)) {
                    $PCS = $asin_sales_plans[0]['sum_quantity'];
                }
            } else {
                $r_message = ['status' => 0, 'msg' => 'asin/marketplace_id/request_date/sku 不可缺少'];
                return $r_message;
            }
            if (!empty($request['request_date']) && !empty($request['sku']) && !empty($request['status']) && !empty($request['seller_sku']) && !empty($request['quantity']) &&
                !empty($request['asin']) && !empty($request['package'])) {
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
                    'request_date' => $request['request_date'],
                    'remark' => @$request['remark'],
                    'received_factory' => @$request['received_factory'],
                    'sap_shipment_code' => @$request['sap_shipment_code'],
                    'confirmed_quantity' => @$request['confirmed_quantity'],
                    'estimated_delivery_date' => @$request['estimated_delivery_date'] ? @$request['estimated_delivery_date'] : @$request['request_date'],
                    'marketplace_id' => @$request['marketplace_id'],
                    'overseas_stock' => $overseas_stock,
                    'day_sales' => $day_sales,
                    'MOQ' => $MOQ,
                    'PCS' => $PCS,
                    'planning_name' => $planning_name,
                    'backlog_order' => $backlog_order,
                    'profit_margin' => $profit_margin,
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
     * @copyright  2020年5月22日
     * 更新 采购
     */
    public function upPurchase(Request $request)
    {
        $r_message = [];
        $id = @$request['id'];
        if ($id > 0 && !empty($request['warehouse']) && !empty($request['sku']) && !empty($request['status']) && !empty($request['asin']) && !empty($request['seller_sku']) && !empty($request['quantity'])) {
            $warehouse = explode('-', $request['warehouse']);
            $data = [
                'status' => $request['status'],
                'sku' => $request['sku'],
                'asin' => $request['asin'],
                'seller_sku' => $request['seller_sku'],
                'sap_warehouse_code' => @$warehouse[0],
                'sap_factory_code' => @$warehouse[1],
                'quantity' => $request['quantity'],
                'request_date' => @$request['request_date'],
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
    /**======================================调拨进度 接口===================================*/
    /**
     * 调拨进度
     * @param Request $request
     */
    public function allotProgress(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $condition = @$request['condition'] ? $request['condition'] : '';
        $date_s = $request['date_s'] ? $request['date_s'] : '';
        $date_e = $request['date_e'] ? $request['date_e'] : '';
        $sap_seller_id_list = $seller = [];
        $sql = "SELECT
                a.`status`,
                a.barcode,
                a.shipping_method,
                a.cargo_data,
                a.shippment_id,
                a.receipts_num,
                a.updated_at,
                s.created_at,
                s.sap_seller_id,
                s.batch_num,
                s.out_warehouse,
                s.label,
                s.sku,
                s.quantity,
                s.rms_sku,
                s.asin,
                s.sku
            FROM
                allot_progress AS a
            LEFT JOIN shipment_requests AS s ON s.id = a.shipment_requests_id where 1=1 ";
        if (!empty($date_s) && !empty($date_e)) {
            $sql .= ' AND s.created_at >= "' . $date_s . '" AND s.created_at <= "' . $date_e . '"';
        }
        if (!empty($condition)) {
            $sql .= ' AND s.asin LIKE "%' . $condition . '%" OR s.sku LIKE "%' . $condition . '%"';
        }
        $allot_progress = DB::connection('vlz')->select($sql);
        $allot_progress = (json_decode(json_encode($allot_progress), true));
        if (!empty($allot_progress)) {
            foreach ($allot_progress as $k => $v) {
                $sap_seller_id_list[] = $v['sap_seller_id'];
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
        foreach ($allot_progress as $key => $value) {
            $allot_progress[$key]['name'] = @$ulist[$value['sap_seller_id']]['name'];
            $allot_progress[$key]['email'] = @$ulist[$value['sap_seller_id']]['email'];
            $allot_progress[$key]['ubu'] = @$ulist[$value['sap_seller_id']]['ubu'];
            $allot_progress[$key]['ubg'] = @$ulist[$value['sap_seller_id']]['ubg'];
            $allot_progress[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
            if (!in_array(@$ulist[$value['sap_seller_id']]['name'], $seller)) {
                $seller[] = @$ulist[$value['sap_seller_id']]['name'];
            }
        }
        return [$allot_progress, $seller];
    }

}