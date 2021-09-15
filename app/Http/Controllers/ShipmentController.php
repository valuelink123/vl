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
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        // $user = Auth::user()->toArray();
//        if (!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) {
//            /**  特殊权限着 查询所有用户 */
//            $role = 4;
//        } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
//            if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
//                /**查询所有BG下面员工*/
//                $role = 3;
//            } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
//                /**此条件为 普通销售*/
//                $role = 1;
//            } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
//                /**  BU 负责人  */
//                $role = 5;
//            }
//        }

        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        $condition = $request['condition'] ? $request['condition'] : '';
        $date_s = $request['date_s'] ? $request['date_s'] : '';//创建时间开始
        $date_e = $request['date_e'] ? $request['date_e'] : '';//创建时间结束
        $received_date_s = $request['received_date_s'] ? $request['received_date_s'] : '';//预计到货时间开始
        $received_date_e = $request['received_date_e'] ? $request['received_date_e'] : '';//预计到货时间结束
        $status = $request['status'] ? $request['status'] : '';
        $sx = $request['sx'] ? $request['sx'] : '';//站点缩写
        $bg = $request['ubg'] ? $request['ubg'] : '';
        $bu = $request['ubu'] ? $request['ubu'] : '';
        $name = $request['name'] ? $request['name'] : '';
        $downLoad = $request['downLoad'] ? $request['downLoad'] : 0;//是否下载
        $allor_status = $request['allor_status'] ? $request['allor_status'] : '';//调拨状态
        $label = $request['label'] ? $request['label'] : '';
        $ids = $request['ids'] ? $request['ids'] : '';
        $role = 0;//角色
        $sap_seller_id_list = $ulist = $allotIdList = $seller = $labelList = $statusList = $planer = [];
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
                asins.id as asin_id,
                sh.planning_name
            FROM
                shipment_requests AS sh
            LEFT JOIN asins ON asins.asin = sh.asin
            AND asins.marketplaceid = sh.marketplace_id 
            Where 1=1 ';

        if (!empty($condition)) {
            $sql .= ' AND sh.asin LIKE "%' . $condition . '%" OR sh.sku LIKE "%' . $condition . '%"';
        }
        if (!empty($date_s) && !empty($date_e)) {
            $sql .= ' AND sh.created_at >= "' . $date_s . '" AND sh.created_at<= "' . $date_e . ' 24:00:00' . '"';
        }
        if (!empty($received_date_s) && !empty($received_date_e)) {
            $sql .= ' AND sh.adjustreceived_date >= "' . $received_date_s . '" AND sh.adjustreceived_date <= "' . $received_date_e . '"';
        }
        if (!empty($status)) {
            $sql .= ' AND sh.status = ' . $status;
        }
        if (!empty($label)) {
            $sql .= ' AND sh.label = "' . $label . '"';
        }
        if (!empty($allor_status)) {
            $sql .= ' AND sh.allor_status = ' . $allor_status;
        }
        if (!empty($sx)) {
            $m_id = array_search($sx, $DOMIN_MARKETPLACEID_SX);
            $sql .= " AND sh.marketplace_id = '" . $m_id . "'";
        }
        if (!empty($ids)) {
            $sql .= " AND sh.id in (" . $ids . ")";
        }
        $sql .= ' ORDER BY sh.created_at DESC';
        $shipmentList = DB::connection('vlz')->select($sql);
        $shipmentList = (json_decode(json_encode($shipmentList), true));
        if (!empty($shipmentList)) {
            foreach ($shipmentList as $key => $value) {
                if (!in_array($value['sap_seller_id'], $sap_seller_id_list)) {
                    if ($value['sap_seller_id'] > 0) {
                        $sap_seller_id_list[] = $value['sap_seller_id'];
                    }
                }
                if (!in_array($value['label'], $labelList)) {
                    if (!empty($value['label'])) {
                        $labelList[] = $value['label'];
                    }
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
            $shipmentList[$key]['name'] = @$ulist[$value['sap_seller_id']]['name'];
            $shipmentList[$key]['ubu'] = @$ulist[$value['sap_seller_id']]['ubu'];
            $shipmentList[$key]['ubg'] = @$ulist[$value['sap_seller_id']]['ubg'];
            $shipmentList[$key]['rms_sku'] = !empty($value['rms_sku']) ? $value['rms_sku'] : '';
            $shipmentList[$key]['remark'] = !empty($value['remark']) ? $value['remark'] : '';
            $shipmentList[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
            //$value['sap_warehouse_code'] . '-' .
            $shipmentList[$key]['warehouse'] = $value['sap_factory_code'];
            $shipmentList[$key]['image'] = explode(',', $value['images'])[0];
            //大货资料是否存在
            $shipmentList[$key]['allot'] = !empty($value['cargo_data']) ? 1 : 0;
            if (!in_array(@$ulist[$value['sap_seller_id']]['name'], $seller)) {
                if (!empty($ulist[$value['sap_seller_id']]['name'])) {
                    if (!empty($ulist[$value['sap_seller_id']]['name'])) {
                        if (!in_array($ulist[$value['sap_seller_id']]['name'], $seller)) {
                            $seller[] = $ulist[$value['sap_seller_id']]['name'];
                        }
                    }
                }
            }
            $shipmentList[$key]['toUrl'] = @$DOMIN_MARKETPLACEID_URL[$value['marketplace_id']];
        }

        $sql_group = 'SELECT status,COUNT(id) as count_num from shipment_requests GROUP BY status=0,status=1,status=2,status=3,status=4,status=5';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        if (!empty($status_group)) {
            foreach ($status_group as $sk => $sv) {
                $statusList['status' . $sv['status']] = $sv['count_num'];
            }
        }
        foreach ($shipmentList as $sk => $v) {
            if (!in_array($v['planning_name'], $planer)) {
                if (!empty($v['planning_name'])) {
                    $planer[] = $v['planning_name'];
                }
            }
            if ((!empty($bg) && $v['ubg'] != $bg) || (!empty($bu) && $v['ubu'] != $bu) || (!empty($name) && $v['name'] != $name)) {
                unset($shipmentList[$sk]);
            }
        }
        /** 下载判断 */
        if ($downLoad > 0) {
            echo
                '提交日期,' .
                '销售员,' .
                '计划员,' .
                '账号,' .
                'Seller SKU,' .
                'ASIN SKU,' .
                '调入工厂仓库,' .
                '需求数量,' .
                '期望到货时间,' .
                '是否贴标签,' .
                '其它需求,' .
                '可维持天数,' .
                'FBA在库,' .
                'FBA可维持天数,' .
                '调拨在途,' .
                '审核状态,' .
                '调整需求数量,' .
                '预计到货时间,' .
                '调出仓库库位,' .
                '调拨状态,' .
                "\r\n" . "\r\n";
            if (!empty($shipmentList)) {
                $allor_status_arr = ['资料提供中', '换标中', '待出库', '已发货', '取消发货'];
                $status_arr = ['待确认', 'bu审核', 'bg审核', '调拨取消', '已确认'];
                foreach ($shipmentList as $ak => $av) {
                    echo
                        '"' . @$av['created_at'] . '",' .
                        '"' . @$av['name'] . '",' .
                        '"' . @$av['planning_name'] . '",' .
                        '"' . @$av['label'] . '",' .
                        '"' . @$av['seller_sku'] . '",' .
                        '"' . @$av['asin'] . @$av['sku'] . '",' .
                        '"' . @$av['warehouse'] . '",' .
                        '"' . @$av['quantity'] . '",' .
                        '"' . @$av['received_date'] . '",' .
                        '"' . @$av['rms_sku'] . '",' .
                        '"' . @$av['remark'] . '",' .
                        '"' . @$av['stock_day_num'] . '",' .
                        '"' . @$av['FBA_Stock'] . '",' .
                        '"' . @$av['FBA_keepday_num'] . '",' .
                        '"' . @$av['transfer_num'] . '",' .
                        '"' . @$status_arr[$av['status']] . '",' .
                        '"' . @$av['adjustment_quantity'] . '",' .
                        '"' . @$av['adjustreceived_date'] . '",' .
                        '"' . @$av['out_warehouse'] . '",' .
                        '"' . @$allor_status_arr[$av['allor_status']] . '",' .
                        "\r\n";
                }
                exit;
            }
        }
        return [$shipmentList, $statusList, $seller, $labelList, $planer];
    }

    /**
     * 保存大货资料
     * @param Request $request
     */
    public function upCargoData(Request $request)
    {

        $old_cargo_data = $cargo_data = '';
        $id = $request['id'];
        if (!empty($request['cargo_data']) && !empty($id)) {
            $old_shipment = DB::connection('vlz')->table('shipment_requests')
                ->select('cargo_data')
                ->where('id', $id)
                ->get()->map(function ($value) {
                    return (array)$value;
                })->first();
            if (!empty($old_shipment)) {
                $old_cargo_data = $old_shipment['cargo_data'];
            }
//            if (!empty($old_cargo_data)) {
//                $cargo_data = $old_cargo_data . ',' . $request['cargo_data'];
//            }
            $data = [
                'cargo_data' => $request['cargo_data']
            ];
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

        /** 超级权限*/
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $r_message = $seller_skus = $seller_accounts = $asins = [];
        $label = $batch_num = $stock_day_num = $transfer_num = $planning_name = $seller_name = $sap_seller_id = $planer = '';
        $FBA_keepday_num = $FBA_Stock = 0;
        if (!empty($request['asin'])) {
            if (!empty($request['asin']) && !empty($request['sku']) && !empty($request['seller_sku']) && !empty($request['warehouse']) && !empty($request['quantity']) && !empty($request['received_date'])) {
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
                    $FBA_Stock = @$asins[0]['afn_sellable'] + @$asins[0]['afn_reserved'];
                    if (@$asins[0]['daily_sales'] > 0) {
                        $stock_day_num = $FBA_Stock + $request['quantity'] / @$asins[0]['daily_sales'];
                    }
                    $FBA_keepday_num = (round($asins[0]['daily_sales'], 2) == 0) ? '∞' : date('Y-m-d', strtotime('+' . intval(($asins[0]['afn_sellable'] + $asins[0]['afn_reserved']) / round($asins[0]['daily_sales'], 2)) . 'days'));

                }
                /**调拨在途查询*/
                $sql2 = "
                        SELECT
                            adjustment_quantity
                        FROM
                            shipment_requests
                        WHERE
                            seller_sku = '" . $request['seller_sku'] . "'
                        AND label = '" . $label . "'
                        AND marketplace_id = '" . $request['marketplace_id'] . "'
                        AND `status` = 4
                        AND allor_status != 4;";
                $shipment_r = DB::connection('vlz')->select($sql2);
                $shipment_r = (json_decode(json_encode($shipment_r), true));
                if (!empty($shipment_r)) {
                    $transfer_num = $shipment_r[0]['adjustment_quantity'];
                }
                //销售员 保存
                $sql3 = "SELECT id,sap_seller_id FROM sap_asin_match_sku  WHERE sku='" . $request['sku'] . "' and marketplace_id ='" . $request['marketplace_id'] . "'";
                $sap_asin_match_sku = DB::connection('vlz')->select($sql3);
                $sap_asin_match_sku = (json_decode(json_encode($sap_asin_match_sku), true));
                if (!empty($sap_asin_match_sku)) {
                    $sap_seller_id = $sap_asin_match_sku[0]['sap_seller_id'];
                }
                /** 获取计划员*/
                if (!empty($request['marketplace_id'])) {
                    //查询计划员
                    $market = getMarketplaceCode();
                    $fba_factory_warehouse = $market[$request['marketplace_id']]['fba_factory_warehouse'];
                    if (!empty($fba_factory_warehouse)) {
                        $sap_factory_code = $fba_factory_warehouse[0]['sap_factory_code'];
                        $sap_warehouse_code = $fba_factory_warehouse[0]['sap_warehouse_code'];
                        $sql = "SELECT planer FROM sap_sku_sites WHERE sku='" . $request['sku'] . "' AND  marketplace_id='" . $request['marketplace_id'] . "' AND sap_factory_code='" . $sap_factory_code . "' AND sap_warehouse_code='" . $sap_warehouse_code . "'";
                        $sap_sku_sites = DB::connection('vlz')->select($sql);
                        $sap_sku_sites = (json_decode(json_encode($sap_sku_sites), true));
                        if (!empty($sap_sku_sites)) {
                            $planer = $sap_sku_sites[0]['planer'];
                        }
                    }
                }

                $data = [
                    'sap_seller_id' => $sap_seller_id,
                    'sku' => $request['sku'],
                    'asin' => $request['asin'],
                    'status' => @$request['status'] ? $request['status'] : 0,
                    'seller_sku' => $request['seller_sku'],
                    'label' => $label,
                    'sap_factory_code' => $request['warehouse'],
                    'out_warehouse' => $request['out_warehouse'],
                    'quantity' => $request['quantity'],
                    'received_date' => $request['received_date'],
                    'adjustreceived_date' => @$request['adjustreceived_date'] ? $request['adjustreceived_date'] : $request['received_date'],
                    'adjustment_quantity' => @$request['adjustment_quantity'] ? $request['adjustment_quantity'] : $request['quantity'],
                    'request_date' => $request['request_date'],
                    'rms' => @$request['rms'],
                    'rms_sku' => @$request['rms_sku'],
                    'remark' => @$request['remark'],
                    'marketplace_id' => $request['marketplace_id'],
                    'created_at' => date('Y-m-d H:i:s', time()),
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'FBA_Stock' => $FBA_Stock,
                    'stock_day_num' => $stock_day_num,
                    'FBA_keepday_num' => $FBA_keepday_num,
                    'transfer_num' => $transfer_num,
                    'planning_name' => $planer
                ];
                $result_id = DB::connection('vlz')->table('shipment_requests')->insertGetId($data);
                if ($result_id > 0) {
                    $sx = $DOMIN_MARKETPLACEID_SX[$request['marketplace_id']];
                    //$batch_num = $sx . date('Ymd', time()) . $result_id;
                    /** 查询 发货批号*/
                    $sql = 'SELECT batch_num from shipment_requests GROUP BY id DESC';
                    $shipment_requests_batch = DB::connection('vlz')->select($sql);
                    $shipment_requests_batch = (json_decode(json_encode($shipment_requests_batch), true));
                    if (!empty($shipment_requests_batch)) {
                        $batch_num = $shipment_requests_batch[0]['batch_num'];
                    }
                    $now_date = substr($batch_num, 0, 8);
                    if ($now_date == date('Ymd', time())) {
                        $batch_num = $batch_num + 1;
                    } else {
                        $batch_num = date('Ymd', time()) . '001';
                    }

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
        if ($sap_seller_id > 0) {
            $sql .= ' AND sap_seller_id=' . $sap_seller_id;
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
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $sku = null;
        $role = 0;
        // $user = Auth::user()->toArray();  //todo 打开
        $role = 7;//todo
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
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
                    $role = 5;
                }
            }
            $roleUser = DB::table('role_user')->select('user_id', 'role_id')
                ->where('user_id', @$user['id'])
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($roleUser)) {
                $role_id = $roleUser[0]['role_id'];
                if ($role_id == 23) {
                    /** 计划员角色  */
                    $role = 2;
                } else if ($role_id == 31) {
                    /** 计划 经理 */
                    $role = 6;
                } else if ($role_id == 20) {
                    /** 物流操作员 */
                    $role = 7;
                }
            }
        }

        if (!empty($request['id']) && $request['id'] > 0) {
            $sql = "SELECT allor_status,marketplace_id,out_warehouse,id,`status`,sku,asin,seller_sku,sap_warehouse_code,sap_factory_code,quantity,received_date,rms,rms_sku,package,remark,adjustment_quantity,adjustreceived_date from shipment_requests WHERE id =" . $request['id'];
            $shipment = DB::connection('vlz')->select($sql);
            $shipment = (json_decode(json_encode($shipment), true));
            if (!empty($shipment[0]['sap_warehouse_code']) && !empty($shipment[0]['sap_factory_code'])) {
                //$shipment[0]['sap_warehouse_code'] . '-' .
                $shipment[0]['warehouse'] = $shipment[0]['sap_factory_code'];
                $shipment[0]['marketplace_id_sx'] = $DOMIN_MARKETPLACEID_SX[$shipment[0]['marketplace_id']];
            }
            $shipment_new = $shipment[0];
            $shipment_new['role'] = $role;
        }
        if (!empty($shipment_new['asin']) && !empty($shipment_new['marketplace_id'])) {
            $sql = "SELECT sku,seller_sku from sap_asin_match_sku WHERE asin='" . $shipment_new['asin'] . "' AND marketplace_id='" . $shipment_new['marketplace_id'] . "' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            if (!empty($data)) {
                $sku = $data[0]['sku'];
            }
            $sql2 = "SELECT sap_factory_code from sap_asin_match_sku WHERE  marketplace_id='" . $shipment_new['marketplace_id'] . "' AND sap_factory_code != '' GROUP BY sap_factory_code;";
            $sellersku2 = DB::connection('vlz')->select($sql2);
            $data2 = (json_decode(json_encode($sellersku2), true));
        }
        return ['shipment' => $shipment_new, 'seller_sku_list' => $data, 'factoryList' => $data2, 'sku' => $sku];


    }

    /**
     * @param Request $request
     * 修改 补货
     */
    public function upShipment(Request $request)
    {
        $boole = false;
        $id = @$request['id'];
        $r_message = $old_shipment = [];
        $old_status = $old_sap_seller_id = $new_status = $role_id = $marketplace_id = $planer = '';
        if ($id > 0) {
            $old_shipment = DB::connection('vlz')->table('shipment_requests')
                ->select('status', 'sap_seller_id', 'marketplace_id')
                ->where('id', $id)
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
        } else {
            return ['status' => 0, 'msg' => '缺少ID'];
        }
        if (!empty($old_shipment)) {
            $marketplace_id = $old_shipment[0]['marketplace_id'];
            $old_status = $old_shipment[0]['status'];
            $old_sap_seller_id = $old_shipment[0]['sap_seller_id'];
            $new_status = @$request['status'] >= 0 ? $request['status'] : $old_status;
        }
        $user = Auth::user()->toArray();
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
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
                    $role = 5;
                }
            } else {
                $roleUser = DB::table('role_user')->select('user_id', 'role_id')
                    ->where('user_id', @$user['id'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($roleUser)) {
                    $role_id = $roleUser[0]['role_id'];
                    if ($role_id == 23) {
                        /** 计划员角色  */
                        $role = 2;
                    } else if ($role_id == 31) {
                        /** 计划 经理 */
                        $role = 6;
                    }
                }
            }

        }
        if ($id > 0) {
            if (($old_status == 0) && ($old_status == $new_status || $new_status == 5)) {
                //状态不变 只是内容修改  或者   取消
                $boole = true;
            } else if (($old_status == 0) && (($new_status == 1 && ($role == 5 || $role == 3)) || ($new_status == 5 && ($role == 5 || $role == 3)))) {
                //状态修改为1 或者 取消
                $boole = true;
            } else if (($old_status == 1 && $new_status == 2 && $role = 3) || ($old_status == 1 && $new_status == 1 && $role = 3) || ($old_status == 1 && $new_status == 5 && $role = 3)) {
                //状态修改为2 或 状态不变   或 取消
                $boole = true;
            } else if (($old_status == 2 && $new_status == 3 && $role = 2) || ($old_status == 2 && $new_status == 2 && $role = 2) || ($old_status == 2 && $new_status == 5 && $role = 2)) {
                //状态修改为3  或 状态不变 或 取消
                $boole = true;
            } else if (($old_status == 3 && $new_status == 4 && $role = 6) || ($old_status == 3 && $new_status == 3 && $role = 6) || ($old_status == 3 && $new_status == 5 && $role = 6)) {
                //状态修改为4  或 状态不变 或 取消
                $boole = true;
            } else if ($role == 4) {
                //最大权限 任何操作
                $boole = true;
            }
            /** 获取计划员*/
            if (!empty($marketplace_id)) {
                //查询计划员
                $market = getMarketplaceCode();
                $fba_factory_warehouse = $market[$marketplace_id]['fba_factory_warehouse'];
                if (!empty($fba_factory_warehouse)) {
                    $sap_factory_code = $fba_factory_warehouse[0]['sap_factory_code'];
                    $sap_warehouse_code = $fba_factory_warehouse[0]['sap_warehouse_code'];
                    $sql = "SELECT planer FROM sap_sku_sites WHERE sku='" . $request['sku'] . "' AND  marketplace_id='" . $marketplace_id . "' AND sap_factory_code='" . $sap_factory_code . "' AND sap_warehouse_code='" . $sap_warehouse_code . "'";
                    $sap_sku_sites = DB::connection('vlz')->select($sql);
                    $sap_sku_sites = (json_decode(json_encode($sap_sku_sites), true));
                    if (!empty($sap_sku_sites)) {
                        $planer = $sap_sku_sites[0]['planer'];
                    }
                }
            }
            /** 销售员 */
            $sql3 = "SELECT id,sap_seller_id FROM sap_asin_match_sku  WHERE sku='" . $request['sku'] . "' and marketplace_id ='" . $marketplace_id . "'";
            $sap_asin_match_sku = DB::connection('vlz')->select($sql3);
            $sap_asin_match_sku = (json_decode(json_encode($sap_asin_match_sku), true));
            if (!empty($sap_asin_match_sku)) {
                $sap_seller_id = $sap_asin_match_sku[0]['sap_seller_id'];
            }

            if ($boole == true) {
                $data = [
                    'status' => $new_status,
                    'sku' => @$request['sku'],
                    'asin' => @$request['asin'],
                    'seller_sku' => @$request['seller_sku'],
                    'sap_factory_code' => @$request['sap_factory_code'],
                    'quantity' => @$request['quantity'],
                    'out_warehouse' => @$request['out_warehouse'],
                    'received_date' => @$request['received_date'],
                    'rms' => @$request['rms'],
                    'rms_sku' => @$request['rms_sku'],
                    'package' => @$request['package'],
                    'remark' => @$request['remark'] ? $request['remark'] : '',
                    'adjustment_quantity' => @$request['adjustment_quantity'],
                    'adjustreceived_date' => @$request['adjustreceived_date'],
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'planning_name' => $planer,
                    'sap_seller_id' => $sap_seller_id
                ];
                if (!empty($data)) {
                    $result = DB::connection('vlz')->table('shipment_requests')
                        ->where('id', $id)
                        ->update($data);
                    if ($result > 0) {
                        /** 已审核状态添加到 调拨进度表allot_progress 表 */
                        if ($new_status == 4 && $id > 0) {
                            $data = [
                                'shipment_requests_id' => $id,
                                'created_at' => date('Y-m-d H:i:s', time())
                            ];
                            DB::connection('amazon')->table('allot_progress')->insert($data);
                        }
                        $r_message = ['status' => 1, 'msg' => '更新成功'];
                    } else {
                        $r_message = ['status' => 0, 'msg' => '更新失败'];
                    }
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '该角色权限不够'];
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
        $boole = false;
        $idList = explode(',', $request['idList']);
        $data = [];
        $r_message = $old_shipment = [];
        $old_status = $old_sap_seller_id = $new_status = $role_id = '';
        if (!empty($idList)) {
            $old_shipment = DB::connection('vlz')->table('shipment_requests')
                ->select('status', 'sap_seller_id')
                ->whereIn('id', $idList)
                ->groupBy('status')
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($old_shipment)) {
                if (count($old_shipment) > 1) {
                    return ['status' => 0, 'msg' => '更新失败,被修改信息状态不一致'];
                } else {
                    $old_status = $old_shipment[0]['status'];
                    $old_sap_seller_id = $old_shipment[0]['sap_seller_id'];
                    $new_status = @$request['status'];
                }
            }
        } else {
            return ['status' => 0, 'msg' => '缺少ID'];
        }

        $user = Auth::user()->toArray();
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
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
                    $role = 5;
                }
            } else {
                $roleUser = DB::table('role_user')->select('user_id', 'role_id')
                    ->where('user_id', @$user['id'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($roleUser)) {
                    $role_id = $roleUser[0]['role_id'];
                    if ($role_id == 23) {
                        /** 计划员角色  */
                        $role = 2;
                    } else if ($role_id == 31) {
                        /** 计划 经理 */
                        $role = 6;
                    }
                }
            }

        }


        if (!empty($idList)) {
            if (($old_status == 0 && $old_status == $new_status) || ($old_status == 0 && $new_status == 5)) {
                //状态不变 只是内容修改  或者   取消
                $boole = true;
            } else if (($old_status == 0 && $new_status == 1 && $role == 5) || ($old_status == 0 && $new_status == 5 && $role = 5)) {
                //状态修改为1 或者 取消
                $boole = true;
            } else if (($old_status == 1 && $new_status == 2 && $role = 3) || ($old_status == 1 && $new_status == 1 && $role = 3) || ($old_status == 1 && $new_status == 5 && $role = 3)) {
                //状态修改为2 或 状态不变   或 取消
                $boole = true;
            } else if (($old_status == 2 && $new_status == 3 && $role = 2) || ($old_status == 2 && $new_status == 2 && $role = 2) || ($old_status == 2 && $new_status == 5 && $role = 2)) {
                //状态修改为3  或 状态不变 或 取消
                $boole = true;
            } else if (($old_status == 3 && $new_status == 4 && $role = 6) || ($old_status == 3 && $new_status == 3 && $role = 6) || ($old_status == 3 && $new_status == 5 && $role = 6)) {
                //状态修改为4  或 状态不变 或 取消
                $boole = true;
            } else if ($role == 4) {
                //最大权限 任何操作
                $boole = true;
            }
            if ($boole == true) {
                $data = ['status' => $new_status];
                $result = DB::connection('vlz')->table('shipment_requests')
                    ->whereIn('id', $idList)
                    ->update($data);
                if ($result > 0) {
                    /** 已审核状态添加到 调拨进度表allot_progress 表 */
                    if ($new_status == 4 && !empty($idList)) {
                        foreach ($idList as $k => $v) {
                            $data1 [] = ['shipment_requests_id' => $v, 'created_at' => date('Y-m-d H:i:s', time())];
                        }
                        DB::connection('amazon')->table('allot_progress')->insert($data1);
                    }
                    $r_message = ['status' => 1, 'msg' => '全部更新成功'];
                } else {
                    $r_message = ['status' => 0, 'msg' => '更新失败'];
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '该角色权限不够'];
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
        }
        return $r_message;
    }

    /**
     * 用于采购新增页面
     * @param Request $request
     * 根据sku 查询 asin seller_sku sap_warehouse_code 列表
     * @author DYS
     * @return  Array
     */
    public function getNextData(Request $request)
    {
        $data = [];
        if (!empty($request['sku']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT asin FROM sap_asin_match_sku WHERE sku='" . $request['sku'] . "' AND marketplace_id='" . $request['marketplace_id'] . "' GROUP BY asin";
            $asinList = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($asinList), true));
            return $data;
        }
        if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT seller_sku from sap_asin_match_sku WHERE asin ='" . $request['asin'] . "' AND marketplace_id ='" . $request['marketplace_id'] . "' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            $sql2 = "SELECT sap_factory_code from sap_asin_match_sku WHERE  marketplace_id='" . $request['marketplace_id'] . "' AND sap_factory_code != '' GROUP BY sap_factory_code;";
            $sellersku2 = DB::connection('vlz')->select($sql2);
            $data2 = (json_decode(json_encode($sellersku2), true));
            return [$data, $data2];
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
            return $data;
        }

    }

    /**
     * 获取sku seller_sku列表、仓库、sku
     * @param Request $request
     * @return array
     */
    public function getSellerSku(Request $request)
    {
        $data = [];
        $sku = null;
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;

        if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT sku,seller_sku from sap_asin_match_sku WHERE asin='" . $request['asin'] . "' AND marketplace_id='" . $request['marketplace_id'] . "' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            if (!empty($data)) {
                $sku = $data[0]['sku'];
            }
            $sql2 = "SELECT sap_factory_code from sap_asin_match_sku WHERE  marketplace_id='" . $request['marketplace_id'] . "' AND sap_factory_code != '' GROUP BY sap_factory_code;";
            $sellersku2 = DB::connection('vlz')->select($sql2);
            $data2 = (json_decode(json_encode($sellersku2), true));
        }
        return ['seller_sku_list' => $data, 'factoryList' => $data2, 'sku' => $sku];
    }
    /** ==========================采购页面 方法===============================================================*/

    /**
     * 采购 列表页 (下载)
     * @copyright  2020年5月19日
     * @author DYS
     * return array
     */
    public function purchaseList(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        //$user = Auth::user()->toArray();// todo
        $sap_seller_id_list = $ulist = $seller = $statusList = [];
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $condition = @$request['condition'] ? $request['condition'] : '';
        $date_s = $request['date_s'] ? $request['date_s'] : '';
        $date_e = $request['date_e'] ? $request['date_e'] : '';
        $status = $request['status'] ? $request['status'] : '';
        $sx = $request['sx'] ? $request['sx'] : '';//站点缩写
        $bg = $request['bg'] ? $request['bg'] : '';
        $bu = $request['bu'] ? $request['bu'] : '';
        $name = $request['name'] ? $request['name'] : '';
        $downLoad = $request['downLoad'] ? $request['downLoad'] : 0;//是否下载
        $ids = $request['ids'] ? $request['ids'] : '';


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
                pr.remark,
                pr.`status`,
                pr.audit_status,
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
            $sql .= ' AND pr.created_at >= "' . $date_s . '" AND pr.created_at <= "' . $date_e . ' 24:00:00' . '"';
        }
        if (!empty($status)) {
            $sql .= ' AND pr.status = ' . $status;
        }
        if (!empty($sx)) {
            $m_id = array_search($sx, $DOMIN_MARKETPLACEID_SX);
            $sql .= " AND pr.marketplace_id = '" . $m_id . "'";
        }
        if (!empty($ids)) {
            $sql .= " AND pr.id in (" . $ids . ")";
        }
        $sql .= ' ORDER BY pr.created_at DESC ';
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
                    $ulist[$v['sap_seller_id']]['ubu'] = @$v['ubu'] ? @$v['ubu'] : '';
                    $ulist[$v['sap_seller_id']]['ubg'] = @$v['ubg'] ? @$v['ubg'] : '';
                }
            }
        }
        foreach ($purchase_requests as $key => $value) {
            $purchase_requests[$key]['name'] = @$ulist[$value['sap_seller_id']]['name'];
            $purchase_requests[$key]['ubu'] = @$ulist[$value['sap_seller_id']]['ubu'];
            $purchase_requests[$key]['ubg'] = @$ulist[$value['sap_seller_id']]['ubg'];
            $purchase_requests[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];
            //$purchase_requests[$key]['warehouse'] = $value['sap_warehouse_code'] . '-' . $value['sap_factory_code'];
            $purchase_requests[$key]['image'] = explode(',', $value['images'])[0];
            $purchase_requests[$key]['sap_shipment_code'] =  !empty($value['sap_shipment_code'])?$value['sap_shipment_code']:'';
            $purchase_requests[$key]['confirmed_quantity'] =  $value['confirmed_quantity']>0?$value['confirmed_quantity']:'';
            //
            if (!in_array(@$ulist[$value['sap_seller_id']]['name'], $seller)) {
                $seller[] = @$ulist[$value['sap_seller_id']]['name'];
            }
            $purchase_requests[$key]['toUrl'] = $DOMIN_MARKETPLACEID_URL[$value['marketplace_id']];
        }
        $sql_group = 'SELECT status,COUNT(id) as count_status from purchase_requests GROUP BY status=0,status=1,status=2,status=3,status=4,status=5';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        if (!empty($status_group)) {
            foreach ($status_group as $sk => $sv) {
                $statusList['status' . $sv['status']] = $sv['count_status'];
            }
        }
        foreach ($purchase_requests as $sk => $v) {
            if ((!empty($bg) && $v['ubg'] != $bg) || (!empty($bu) && $v['ubu'] != $bu) || (!empty($name) && $v['name'] != $name)) {
                unset($purchase_requests[$sk]);
            }
        }
        /** 下载判断 */
        if ($downLoad > 0) {

            echo
                '提交日期,' .
                '销售员,' .
                'ASIN,' .
                'SKU,' .
                '需求数量,' .
                '期望到货时间,' .
                '海外库存,' .
                '未交订单,' .
                '加权日均,' .
                '到货后预计日销(PCS),' .
                '利润率,' .
                '审核结果,' .
                '计划员,' .
                'MOQ,' .
                '收货工厂,' .
                '运输方式,' .
                '计划确认数量,' .
                '预计交货时间	,' .
                '采购订单号	,' .
                "\r\n" . "\r\n";
            if (!empty($purchase_requests)) {
                $audit_status_arr = ['待确认', 'bu审核', 'bg审核', '调拨取消', '已确认'];
                foreach ($purchase_requests as $ak => $av) {
                    echo
                        '"' . @$av['created_at'] . '",' .
                        '"' . @$av['name'] . '",' .
                        '"' . @$av['asin'] . '",' .
                        '"' . @$av['sku'] . '",' .
                        '"' . @$av['quantity'] . '",' .
                        '"' . @$av['request_date'] . '",' .
                        '"' . @$av['overseas_stock'] . '",' .
                        '"' . @$av['backlog_order'] . '",' .
                        '"' . @$av['day_sales'] . '",' .
                        '"' . @$av['PCS'] . '",' .
                        '"' . @$av['profit_margin'] . '",' .
                        '"' . @$audit_status_arr[$av['audit_status']] . '",' .
                        '"' . @$av['planning_name'] . '",' .
                        '"' . @$av['MOQ'] . '",' .
                        '"' . @$av['received_factory'] . '",' .
                        '"' . @$av['sap_shipment_code'] . '",' .
                        '"' . @$av['confirmed_quantity'] . '",' .
                        '"' . @$av['estimated_delivery_date'] . '",' .
                        '"' . @$av['order_number'] . '",' .
                        "\r\n";
                }
                exit;
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
        $asinList = $sellersku = $factory_code = [];
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
                    pr.audit_status,
                    pr.sku,
                    pr.asin,
                    pr.seller_sku,
                    pr.sap_warehouse_code,
                    pr.sap_factory_code,
                    pr.quantity,
                    pr.remark,
                    pr.received_factory,
                    pr.sap_shipment_code,
                    pr.confirmed_quantity,
                    pr.estimated_delivery_date,
                    pr.marketplace_id,
                    pr.request_date
                FROM
                    purchase_requests AS pr
                WHERE  pr.id =" . $request['id'];
            $Purchase = DB::connection('vlz')->select($sql);
            $Purchase = (json_decode(json_encode($Purchase), true));
//            if (!empty($Purchase[0]['sap_warehouse_code']) && !empty($Purchase[0]['sap_factory_code'])) {
//                $Purchase[0]['warehouse'] = $Purchase[0]['sap_warehouse_code'] . '-' . $Purchase[0]['sap_factory_code'];
//            }
        }
        if (!empty($Purchase[0]['sku']) && !empty($Purchase[0]['marketplace_id']) && !empty($Purchase[0]['asin'])) {
            $sql = "SELECT asin FROM sap_asin_match_sku WHERE sku='" . $Purchase[0]['sku'] . "' AND marketplace_id='" . $Purchase[0]['marketplace_id'] . "' GROUP BY asin";
            $asinList = DB::connection('vlz')->select($sql);
            $asinList = (json_decode(json_encode($asinList), true));

            $sql3 = "SELECT seller_sku from sap_asin_match_sku WHERE asin ='" . $Purchase[0]['asin'] . "' AND marketplace_id ='" . $Purchase[0]['marketplace_id'] . "' GROUP BY seller_sku;";
            $sellersku = DB::connection('vlz')->select($sql3);
            $sellersku = (json_decode(json_encode($sellersku), true));
            $sql2 = "SELECT sap_factory_code from sap_asin_match_sku WHERE  marketplace_id='" . $Purchase[0]['marketplace_id'] . "' AND sap_factory_code != '' GROUP BY sap_factory_code;";
            $factory_code = DB::connection('vlz')->select($sql2);
            $factory_code = (json_decode(json_encode($factory_code), true));
            return ['role' => $role, 'detail' => $Purchase[0], 'asinList' => $asinList, 'sellersku' => $sellersku, 'factory_code' => $factory_code];
        } else {
            return [];
        }
    }

    /**
     * 新增采购 接口
     * @param Request $request
     */
    public function addPurchase(Request $request)
    {
        // $user = Auth::user()->toArray();// todo
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $r_message = [];
        $FBA_Stock = $overseas_stock = $day_sales = $profit_margin = $planning_name = $sap_seller_id = $planer = '';
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
                /** 获取计划员*/
                if (!empty($request['marketplace_id'])) {
                    //查询计划员
                    $market = getMarketplaceCode();
                    $fba_factory_warehouse = $market[$request['marketplace_id']]['fba_factory_warehouse'];
                    if (!empty($fba_factory_warehouse)) {
                        $sap_factory_code = $fba_factory_warehouse[0]['sap_factory_code'];
                        $sap_warehouse_code = $fba_factory_warehouse[0]['sap_warehouse_code'];
                        $sql = "SELECT planer FROM sap_sku_sites WHERE sku='" . $request['sku'] . "' AND  marketplace_id='" . $request['marketplace_id'] . "' AND sap_factory_code='" . $sap_factory_code . "' AND sap_warehouse_code='" . $sap_warehouse_code . "'";
                        $sap_sku_sites = DB::connection('vlz')->select($sql);
                        $sap_sku_sites = (json_decode(json_encode($sap_sku_sites), true));
                        if (!empty($sap_sku_sites)) {
                            $planer = $sap_sku_sites[0]['planer'];
                        }
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
            if (!empty($request['request_date']) && !empty($request['sku']) && !empty($request['seller_sku'])
                && !empty($request['quantity']) && !empty($request['asin']) && !empty($request['marketplace_id'])) {
                $warehouse = explode('-', $request['warehouse']);

                $data = [
                    'sap_seller_id' => $sap_seller_id,
                    'audit_status' => @$request['audit_status'],
                    'sku' => $request['sku'],
                    'asin' => $request['asin'],
                    'seller_sku' => $request['seller_sku'],
                    // 'sap_warehouse_code' => @$warehouse[0],
                    'sap_factory_code' => $request['sap_factory_code'],
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
                    'planning_name' => $planer,
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
        if ($sap_seller_id > 0) {
            $sql .= ' AND sap_seller_id=' . $sap_seller_id;
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
        if ($id > 0 && !empty($request['sap_factory_code']) && !empty($request['sku']) && !empty($request['asin']) &&
            !empty($request['seller_sku']) && !empty($request['quantity'])) {
            // $warehouse = explode('-', $request['warehouse']);
            $data = [
                'audit_status' => $request['audit_status'],
                'sku' => $request['sku'],
                'asin' => $request['asin'],
                'seller_sku' => $request['seller_sku'],
                // 'sap_warehouse_code' => @$warehouse[0],
                'sap_factory_code' => @$request['sap_factory_code'],
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
            $r_message = ['status' => 0, 'msg' => '缺少参数'];
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
        $sap_seller_id_list = $seller = $label = $factoryList = $statusarr = [];
        $condition = @$request['condition'] ? $request['condition'] : '';
        $date_s = $request['date_s'] ? $request['date_s'] : '';
        $date_e = $request['date_e'] ? $request['date_e'] : '';
        $label_s = $request['label'] ? $request['label'] : '';
        $bg = $request['bg'] ? $request['bg'] : '';
        $bu = $request['bu'] ? $request['bu'] : '';
        $name = $request['name'] ? $request['name'] : '';
        $status = $request['status'] ? $request['status'] : '';
        $out_warehouse = $request['out_warehouse'] ? $request['out_warehouse'] : '';
        $marketplace_id = $request['marketplace_id'] ? $request['marketplace_id'] : '';
        $sap_factory_code = $request['sap_factory_code'] ? $request['sap_factory_code'] : '';
        $shipment_requests_id = $request['shipment_id'] ? $request['shipment_id'] : 0;
        $sr_id_list = $request['shipment_id_list'] ? $request['shipment_id_list'] : '';
        $statusList = ['资料提供中', '换标中', '待出库', '已发货', '取消发货'];
        $downLoad = $request['downLoad'] ? $request['downLoad'] : 0;//是否下载
        $sql = "SELECT
                a.id,
                a.`status`,
                a.barcode,
                a.shipping_method,
                a.cargo_data,
                a.shippment_id,
                a.receipts_num,
                a.updated_at,
                a.created_at,
                s.sap_seller_id,
                s.batch_num,
                s.out_warehouse,
                s.sap_factory_code,
                s.label,
                s.sku,
                s.quantity,
                s.rms_sku,
                s.asin,
                s.cargo_data,
                s.marketplace_id,
                a.shipment_requests_id,
               	a.width,
                a.height,
                a.transportation,
                a.pallets,
                a.pallets_size,
                a.length,
                a.box_num,
                a.pcs_box,
                a.pcs,
                a.weight_box
            FROM
                allot_progress AS a
            LEFT JOIN shipment_requests AS s ON s.id = a.shipment_requests_id where 1=1 ";
        if (!empty($date_s) && !empty($date_e)) {
            $sql .= ' AND a.created_at >= "' . $date_s . '" AND a.created_at <= "' . $date_e . ' 24:00:00' . '"';
        }
        if (!empty($condition)) {
            $sql .= ' AND s.asin LIKE "%' . $condition . '%" OR s.sku LIKE "%' . $condition . '%"';
        }
        if ($shipment_requests_id > 0) {
            $sql .= ' AND a.shipment_requests_id = ' . $shipment_requests_id;
        }
        if (!empty($sr_id_list)) {
            $sql .= " AND a.shipment_requests_id in (" . $sr_id_list . ")";
        }
        if (!empty($label_s)) {
            $sql .= " AND s.label = '" . $label_s . "'";
        }
        if (!empty($status)) {
            $sql .= " AND  a.`status` = '" . $status . "'";
        }
        if (!empty($out_warehouse)) {
            $sql .= " AND s.out_warehouse = '" . $out_warehouse . "'";
        }
        if (!empty($marketplace_id)) {
            $sql .= " AND s.marketplace_id = '" . $marketplace_id . "'";
        }
        if (!empty($sap_factory_code)) {
            $sql .= " AND s.sap_factory_code = '" . $sap_factory_code . "'";
        }
        // if ($downLoad == 0){
        $sql .= ' GROUP BY a.shipment_requests_id ';
        //  }
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
                    $ulist[$v['sap_seller_id']]['ubu'] = @$v['ubu'] ? @$v['ubu'] : '';
                    $ulist[$v['sap_seller_id']]['ubg'] = @$v['ubg'] ? @$v['ubg'] : '';
                }
            }
        }
        foreach ($allot_progress as $key => $value) {
            $allot_progress[$key]['name'] = @$ulist[$value['sap_seller_id']]['name'];
            $allot_progress[$key]['bu'] = @$ulist[$value['sap_seller_id']]['ubu'];
            $allot_progress[$key]['bg'] = @$ulist[$value['sap_seller_id']]['ubg'];

            $allot_progress[$key]['shippment_id'] = $value['shippment_id']  ? $value['shippment_id'] : '';
            $allot_progress[$key]['receipts_num'] = !empty($value['receipts_num']) ? $value['receipts_num'] : '';
            $allot_progress[$key]['shipping_method'] = !empty($value['shipping_method']) ? $value['shipping_method'] : '';
            $allot_progress[$key]['rms_sku'] = !empty($value['rms_sku']) ? $value['rms_sku'] : '';
            $allot_progress[$key]['label'] = !empty($value['label']) ? $value['label'] : '';
            //
            $allot_progress[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];

            if (!in_array(@$ulist[$value['sap_seller_id']]['name'], $seller)) {
                $seller[] = @$ulist[$value['sap_seller_id']]['name'];
            }
            if (!in_array(@$value['label'], $label)) {
                $label[] = $value['label'];
            }
        }
        foreach ($allot_progress as $key => $value) {
            if ((!empty($bg) && $value['bg'] != $bg) || (!empty($bu) && $value['bu'] != $bu) || (!empty($name) && $value['name'] != $name)) {
                unset($allot_progress[$key]);
            }
        }
        $sql_f = "SELECT sap_factory_code FROM sap_asin_match_sku WHERE sap_factory_code != ''  GROUP BY sap_factory_code";
        $factoryList = DB::connection('vlz')->select($sql_f);
        $factoryList = (json_decode(json_encode($factoryList), true));
        //状态分组 统计
        $sql_group = 'SELECT status,COUNT(id) as count_num from ( SELECT * FROM allot_progress GROUP BY shipment_requests_id) as a  GROUP BY status=0,status=1,status=2,status=3,status=4';
        $status_group = DB::connection('vlz')->select($sql_group);
        $status_group = (json_decode(json_encode($status_group), true));
        if (!empty($status_group)) {
            foreach ($status_group as $sk => $sv) {
                $statusarr['status' . $sv['status']] = $sv['count_num'];
            }
        }
        //下载判断
        if ($downLoad > 0) {
            echo
                'shipment_requests_id,' .
                '登记时间,' .
                '调拨状态,' .
                '发货批号,' .
                'Shippment ID,' .
                '亚马逊账号,' .
                'SKU,' .
                'Qty调拔数量,' .
                'New Barcode,' .
                '箱数,' .
                '单箱数量,' .
                '总数量,' .
                '单箱重量,' .
                '长（IN）,' .
                '宽（IN）,' .
                '高(IN）,' .
                '卡板号pallets,' .
                '打板尺寸(in)pallets size,' .
                '发货方式,' .
                '跟踪号/单据号,' .
                "\r\n" . "\r\n";
            if (!empty($allot_progress)) {
                foreach ($allot_progress as $ak => $av) {
                    echo
                        '"' . @$av['shipment_requests_id'] . '",' .
                        '"' . @$av['created_at'] . '",' .
                        '"' . $statusList[@$av['status']] . '",' .
                        '"' . @$av['batch_num'] . '",' .
                        '"' . @$av['shippment_id'] . '",' .
                        '"' . @$av['label'] . '",' .
                        '"' . @$av['sku'] . '",' .
                        '"' . @$av['quantity'] . '",' .
                        '"' . @$av['barcode'] . '",' .
                        '"' . @$av['box_num'] . '",' .
                        '"' . @$av['pcs_box'] . '",' .
                        '"' . @$av['pcs'] . '",' .
                        '"' . @$av['weight_box'] . '",' .
                        '"' . @$av['length'] . '",' .
                        '"' . @$av['width'] . '",' .
                        '"' . @$av['height'] . '",' .
                        '"' . @$av['pallets'] . '",' .
                        '"' . @$av['pallets_size'] . '",' .
                        '"' . @$av['shipping_method'] . '",' .
                        '"' . @$av['receipts_num'] . '",' .
                        "\r\n";
                }
                exit;
            }
        }
        return [$allot_progress, $seller, $label, $factoryList, $statusarr];
    }

    /**
     * 导入Excel
     * @param Request $request
     * @param int $sheet
     * @return mixed
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function importExecl(Request $request, $sheet = 0)
    {
        header("content-type:text/html;charset=utf-8");
        //Auth::user()->id //todo
        $file = $request->file('files');
        $sr_id_list = [];
        $r_message = $msg = '';
        if ($file) {
            try {
                $file_name = $file->getClientOriginalName();
                $file_size = $file->getSize();
                $file_ex = $file->getClientOriginalExtension();
                $newname = $file_name;
                $newpath = '/uploads/' . date('Ym') . '/' . date('d') . '/' . date('His') . rand(100, 999) . intval(132) . '/';
                $file->move(public_path() . $newpath, $newname);
            } catch (\Exception $exception) {
                $error = array(
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'error' => $exception->getMessage(),
                );
                // Return error
                return \Response::json($error, 400);
            }
            if (file_exists(public_path() . $newpath . $newname)) {
                $newurl = $newpath . $newname;
                $success = new \stdClass();
                $success->name = $newname;
                $success->size = $file_size;
                $success->url = $newurl;
                $success->thumbnailUrl = $newurl;
                $success->deleteUrl = url('send/deletefile/' . base64_encode($newpath . $newname));
                $success->deleteType = 'get';
                $success->fileID = md5($newpath . $newname);
                //判断哪种类型
                if ($file_ex == "xlsx") {
                    $reader = PHPExcel_IOFactory::createReader('Excel2007');
                } else {
                    $reader = PHPExcel_IOFactory::createReader('Excel5');
                }
                $excel = $reader->load(public_path() . $newpath . $newname, $encode = 'utf-8');
                //读取第一张表
                $sheet = $excel->getSheet(0);
                //获取总行数
                $row_num = $sheet->getHighestRow();
                //获取总列数
                $col_num = $sheet->getHighestColumn();

                $data = []; //数组形式获取表格数据
                for ($i = 2; $i <= $row_num; $i++) {
                    $data[$i]['shipment_requests_id'] = $sheet->getCell("A" . $i)->getValue();
                    // $data[$i]['status']  = $sheet->getCell("B".$i)->getValue();
                    // $data[$i]['name']  = $sheet->getCell("C".$i)->getValue();
                    //  $data[$i]['batch_num']  = $sheet->getCell("D".$i)->getValue();
                    //  $data[$i]['out_warehouse']  = $sheet->getCell("E".$i)->getValue();
                    //  $data[$i]['label']  = $sheet->getCell("F".$i)->getValue();
                    //  $data[$i]['sku']  = $sheet->getCell("G".$i)->getValue();
                    //  $data[$i]['quantity']  = $sheet->getCell("H".$i)->getValue();
                    //  $data[$i]['rms_sku']  = $sheet->getCell("I".$i)->getValue();
                    $data[$i]['receipts_num'] = $sheet->getCell("M" . $i)->getValue();
                    $data[$i]['shippment_id'] = $sheet->getCell("L" . $i)->getValue();
                    $data[$i]['shipment_requests_id'] = $sheet->getCell("A" . $i)->getValue();
                    $data[$i]['updated_at'] = date('Y-m-d H:i:s', time());
                    $sr_id = $sheet->getCell("A" . $i)->getValue();
                    if (!in_array($sr_id, $sr_id_list)) {
                        $sr_id_list[] = $sr_id;
                    }
                }
                if (!empty($data) && !empty($sr_id_list)) {
                    $srids = implode($sr_id_list, ',');
                    $sql = 'delete from allot_progress WHERE shipment_requests_id in (' . $srids . ')';
                    $res = DB::connection('vlz')->delete($sql);
                    $result = DB::connection('vlz')->table('allot_progress')->insert($data);
                    if ($result) {
                        $r_message = ['status' => 1, 'msg' => '保存成功'];
                        $msg = '成功';
                        //  return \Response::json(array('files' => array($success)), 200);
                    }
                } else {
                    $msg = '失败';
                    $r_message = ['status' => 0, 'msg' => '数据格式不对'];
                }

//                return \Response::json(array('files' => array($success)), 200);
            } else {
                $msg = '失败';
                $r_message = ['status' => 0, 'msg' => '文件打开失败'];
            }
            //  return $r_message;
            //return view('cpfr/allocationProgress', ['msg' => $msg]);
            return redirect('cpfr/allocationProgress');

        }
    }

    /**
     * 装箱数据详情
     * @param Request $request
     */
    public function getBoxDetail(Request $request)
    {
        $sr_id = $request['shipment_requests_id'] ? $request['shipment_requests_id'] : 0;
        $BoxDetail = [];
        if ($sr_id > 0) {
            $sql = 'SELECT id,width,height,transportation,pallets,pallets_size,`length`,box_num,pcs_box,pcs,weight_box from allot_progress where shipment_requests_id = ' . $sr_id;
            $BoxDetail = DB::connection('vlz')->select($sql);
            $BoxDetail = (json_decode(json_encode($BoxDetail), true));
            if (!empty($BoxDetail)) {
                foreach ($BoxDetail as $bk => $bv) {
                    if (empty($bv['width']) || $bv['width'] <= 0) {
                        $BoxDetail[$bk]['width'] = '';
                    }
                    if (empty($bv['height']) || $bv['height'] <= 0) {
                        $BoxDetail[$bk]['height'] = '';
                    }
                    if (empty($bv['length']) || $bv['length '] <= 0) {
                        $BoxDetail[$bk]['length'] = '';
                    }
                    if (empty($bv['transportation'])) {
                        $BoxDetail[$bk]['transportation'] = '';
                    }
                    if (empty($bv['pallets'])) {
                        $BoxDetail[$bk]['pallets'] = '';
                    }
                    if (empty($bv['pallets_size'])) {
                        $BoxDetail[$bk]['pallets_size'] = '';
                    }
                }
            }
            return $BoxDetail;
        } else {
            echo '缺少shipment_requests_id';
            exit;
        }
    }

    /**
     * 发货方式修改
     * @param Request $request
     * @return array|mixed
     */
    public function upShippingMethod(Request $request)
    {
        $id = $request['id'] ? $request['id'] : 0;
        $shippingMethod = $request['shippingMethod'] ? $request['shippingMethod'] : '';
        $r_message = [];
        if ($id > 0 && !empty($shippingMethod)) {
            $data = ['shipping_method' => $shippingMethod];
            $result = DB::connection('vlz')->table('allot_progress')
                ->where('id', $id)
                ->update($data);
            if ($result) {
                $r_message = ['status' => 1, 'msg' => '修改成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '修改失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少参数或发货方式'];
        }
        return $r_message;
    }

    /**
     * ShippmentID 修改
     * @param Request $request
     * @return array|mixed
     */
    public function upShippmentID_old(Request $request)
    {
        $id = $request['id'] ? $request['id'] : 0;
        $shippment_id = $request['shippment_id'] ? $request['shippment_id'] : '';
        $r_message = [];
        if ($id > 0 && !empty($shippment_id)) {
            $data = ['shippment_id' => $shippment_id];
            $result = DB::connection('vlz')->table('allot_progress')
                ->where('id', $id)
                ->update($data);
            if ($result) {
                $r_message = ['status' => 1, 'msg' => '修改成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '修改失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少shippment_id或id'];
        }
        return $r_message;
    }

    /**
     * 修改 跟踪号/单据号
     * @param Request $request
     * @return array
     */
    public function upReceiptsNum_old(Request $request)
    {
        $id = $request['id'] ? $request['id'] : 0;
        $receipts_num = $request['receipts_num'] ? $request['receipts_num'] : '';
        $r_message = [];
        if ($id > 0 && !empty($receipts_num)) {
            $data = ['receipts_num' => $receipts_num];
            $result = DB::connection('vlz')->table('allot_progress')
                ->where('id', $id)
                ->update($data);
            if ($result) {
                $r_message = ['status' => 1, 'msg' => '修改成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '修改失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少receipts_num或id'];
        }
        return $r_message;
    }


    /**
     * @param Request $request
     * @return array
     * 批量更新 调拨 状态
     */
    public function upAllAllot(Request $request)
    {
        $r_message = [];
        $s_r_idList = explode(',', $request['shipment_requests_id_list']);
        if ((@$request['status'] >= 0) && !empty($s_r_idList)) {
            $data = ['status' => $request['status']];
            $result = DB::connection('vlz')->table('allot_progress')
                ->whereIn('shipment_requests_id', $s_r_idList)
                ->update($data);
            if ($result > 0) {
                $data2 = [
                    'allor_status' => $request['status'],
                    'updated_at' => date('Y-m-d H:i:s', time())
                ];
                $result2 = DB::connection('vlz')->table('shipment_requests')
                    ->whereIn('id', $s_r_idList)
                    ->update($data2);
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
     * 调拨进度 导出所有列表信息
     * @param Request $request
     */
    public function exportExecl(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $condition = @$request['condition'] ? $request['condition'] : '';
        $date_s = $request['date_s'] ? $request['date_s'] : '';
        $date_e = $request['date_e'] ? $request['date_e'] : '';
        $label_s = $request['label'] ? $request['label'] : '';
        $bg = $request['bg'] ? $request['bg'] : '';
        $bu = $request['bu'] ? $request['bu'] : '';
        $name = $request['name'] ? $request['name'] : '';
        $status = $request['status'] ? $request['status'] : '';
        $out_warehouse = $request['out_warehouse'] ? $request['out_warehouse'] : '';
        $marketplace_id = $request['marketplace_id'] ? $request['marketplace_id'] : '';
        $sap_factory_code = $request['sap_factory_code'] ? $request['sap_factory_code'] : '';
        $shipment_requests_id = $request['shipment_id'] ? $request['shipment_id'] : 0;
        $sr_id_list = $request['shipment_id_list'] ? $request['shipment_id_list'] : '';
        $sap_seller_id_list = [];
        $statusList = ['资料提供中', '换标中', '待出库', '已发货', '取消发货'];
        $sql = "SELECT
                a.id,
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
                s.sap_factory_code,
                s.label,
                s.sku,
                s.quantity,
                s.rms_sku,
                s.asin,
                s.cargo_data,
                s.marketplace_id,
                a.shipment_requests_id,
               	a.width,
                a.height,
                a.transportation,
                a.pallets,
                a.pallets_size,
                a.length,
                a.box_num,
                a.pcs_box,
                a.pcs,
                a.weight_box
            FROM
                allot_progress AS a
            LEFT JOIN shipment_requests AS s ON s.id = a.shipment_requests_id where 1=1 ";
        if (!empty($date_s) && !empty($date_e)) {
            $sql .= ' AND s.created_at >= "' . $date_s . '" AND s.created_at <= "' . $date_e . '"';
        }
        if (!empty($condition)) {
            $sql .= ' AND s.asin LIKE "%' . $condition . '%" OR s.sku LIKE "%' . $condition . '%"';
        }
        if ($shipment_requests_id > 0) {
            $sql .= ' AND a.shipment_requests_id = ' . $shipment_requests_id;
        }
        if (!empty($sr_id_list)) {
            $sql .= " AND a.shipment_requests_id in (" . $sr_id_list . ")";
        }
        if (!empty($label_s)) {
            $sql .= " AND s.label = '" . $label_s . "'";
        }
        if (!empty($status)) {
            $sql .= " AND  a.`status` = '" . $status . "'";
        }
        if (!empty($out_warehouse)) {
            $sql .= " AND s.out_warehouse = '" . $out_warehouse . "'";
        }
        if (!empty($marketplace_id)) {
            $sql .= " AND s.marketplace_id = '" . $marketplace_id . "'";
        }
        if (!empty($sap_factory_code)) {
            $sql .= " AND s.sap_factory_code = '" . $sap_factory_code . "'";
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
                    $ulist[$v['sap_seller_id']]['ubu'] = @$v['ubu'] ? @$v['ubu'] : '';
                    $ulist[$v['sap_seller_id']]['ubg'] = @$v['ubg'] ? @$v['ubg'] : '';
                }
            }
        }
        foreach ($allot_progress as $key => $value) {
            $allot_progress[$key]['name'] = @$ulist[$value['sap_seller_id']]['name'];
            $allot_progress[$key]['bu'] = @$ulist[$value['sap_seller_id']]['ubu'];
            $allot_progress[$key]['bg'] = @$ulist[$value['sap_seller_id']]['ubg'];
            $allot_progress[$key]['domin_sx'] = @$DOMIN_MARKETPLACEID_SX[$value['marketplace_id']];

        }
        foreach ($allot_progress as $key => $value) {
            if (!empty($bg) && $value['bg'] != $bg || !empty($bu) && $value['bu'] != $bu || !empty($name) && $value['name'] != $name) {
                unset($allot_progress[$key]);
            }
        }
        echo
            'shipment_requests_id,' .
            '登记时间,' .
            '调拨状态,' .
            '发货批号,' .
            'Shippment ID,' .
            '亚马逊账号,' .
            'SKU,' .
            'Qty调拔数量,' .
            'New Barcode,' .
            '箱数,' .
            '单箱数量,' .
            '总数量,' .
            '单箱重量,' .
            '长（IN）,' .
            '宽（IN）,' .
            '高(IN）,' .
            '卡板号pallets,' .
            '打板尺寸(in)pallets size,' .
            '发货方式,' .
            '跟踪号/单据号,' .
            "\r\n" . "\r\n";
        if (!empty($allot_progress)) {
            foreach ($allot_progress as $ak => $av) {
                echo
                    '"' . @$av['shipment_requests_id'] . '",' .
                    '"' . @$av['created_at'] . '",' .
                    '"' . $statusList[@$av['status']] . '",' .
                    '"' . @$av['batch_num'] . '",' .
                    '"' . @$av['shippment_id'] . '",' .
                    '"' . @$av['label'] . '",' .
                    '"' . @$av['sku'] . '",' .
                    '"' . @$av['quantity'] . '",' .
                    '"' . @$av['barcode'] . '",' .
                    '"' . @$av['box_num'] . '",' .
                    '"' . @$av['pcs_box'] . '",' .
                    '"' . @$av['pcs'] . '",' .
                    '"' . @$av['weight_box'] . '",' .
                    '"' . @$av['length'] . '",' .
                    '"' . @$av['width'] . '",' .
                    '"' . @$av['height'] . '",' .
                    '"' . @$av['pallets'] . '",' .
                    '"' . @$av['pallets_size'] . '",' .
                    '"' . @$av['shipping_method'] . '",' .
                    '"' . @$av['receipts_num'] . '",' .
                    "\r\n";
            }
            exit;
        }
    }

    /**
     * @param Request $request
     * 调拨进度页面修改调拨计划
     */
    public function upShipment2(Request $request)
    {
        $boole = false;
        $id = @$request['id'];
        $r_message = $old_shipment = [];
        $role_id = $role = '';
        $user = Auth::user()->toArray();
        // $user['id']=49;
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        if (!empty($user)) {
            $roleUser = DB::table('role_user')->select('user_id', 'role_id')
                ->where('user_id', @$user['id'])
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($roleUser)) {
                $role_id = $roleUser[0]['role_id'];
                if ($role_id == 23) {
                    /** 计划员角色  */
                    $role = 2;
                } else if ($role_id == 31) {
                    /** 计划 经理 */
                    $role = 6;
                } else if ($role_id == 20) {
                    /** 物流操作员 */
                    $role = 7;
                }
            }
        }
        /** 只有物流操作员 有修改权限*/
        if ($id > 0) {
            if ($role == 4 || $role == 6 || $role == 7 || $role == 2) {
                //最大权限 任何操作
                $boole = true;
            }
            if ($boole == true) {
                $data = [
                    'out_warehouse' => @$request['out_warehouse'],
                    'adjustment_quantity' => @$request['adjustment_quantity'],
                    'adjustreceived_date' => @$request['adjustreceived_date'],
                    'updated_at' => date('Y-m-d H:i:s', time())
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
                $r_message = ['status' => 0, 'msg' => '该角色权限不够'];
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
        }
        return $r_message;
    }

    /**
     * @param Request $request
     * 修改计划数量
     */
    public function upAdjustmentQquantity(Request $request)
    {
        $boole = false;
        $id = @$request['id'];
        $r_message = $old_shipment = [];
        $role_id = $role = '';
        $user = Auth::user()->toArray();
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
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
                    $role = 5;
                }
            } else {
                $roleUser = DB::table('role_user')->select('user_id', 'role_id')
                    ->where('user_id', @$user['id'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($roleUser)) {
                    $role_id = $roleUser[0]['role_id'];
                    if ($role_id == 23) {
                        /** 计划员角色  */
                        $role = 2;
                    } else if ($role_id == 31) {
                        /** 计划 经理 */
                        $role = 6;
                    } else if ($role_id == 20) {
                        /** 物流操作员 */
                        $role = 7;
                    }
                }
            }
        }
        if ($id > 0) {
            if ($role == 4 || $role == 6 || $role == 7 || $role == 2) {
                //最大权限 任何操作
                $boole = true;
            }
            if ($boole == true) {
                $data = [
                    'adjustment_quantity' => @$request['adjustment_quantity'],
                    'updated_at' => date('Y-m-d H:i:s', time())
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
                $r_message = ['status' => 0, 'msg' => '该角色权限不够'];
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
        }
        return $r_message;
    }

    /**
     * @param Request $request
     * 调拨状态修改
     */
    public function upAlltoStatus(Request $request)
    {
        $boole = false;
        $shipment_requests_id = @$request['shipment_requests_id'];
        $r_message = $old_shipment = [];
        $user = Auth::user()->toArray();
        /** 超级权限*/
        $role = $this->getRole();
        if ($shipment_requests_id > 0) {
            if ($role == 4 || $role == 6 || $role == 7 || $role == 2) {
                //最大权限 计划员 计划经理 物流操作 可修改
                $boole = true;
            }
            if ($boole == true) {
                $data = [
                    'status' => @$request['status'],
                    'updated_at' => date('Y-m-d H:i:s', time())
                ];
                if (!empty($data)) {
                    $result = DB::connection('vlz')->table('allot_progress')
                        ->where('shipment_requests_id', $shipment_requests_id)
                        ->update($data);
                    if ($result > 0) {
                        $data2 = [
                            'allor_status' => $request['status'],
                            'updated_at' => date('Y-m-d H:i:s', time())
                        ];
                        $result2 = DB::connection('vlz')->table('shipment_requests')
                            ->where('id', $shipment_requests_id)
                            ->update($data2);
                        $r_message = ['status' => 1, 'msg' => '更新成功'];
                    } else {
                        $r_message = ['status' => 0, 'msg' => '更新失败'];
                    }
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '该角色权限不够'];
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '缺少ID'];
        }
        return $r_message;
    }

    /**
     * 查询二维码信息
     * @param Request $request
     */
    public function getBarcodepub(Request $request)
    {
        $id = @$request['shipment_requests_id'];
        $quantity = $title = $asin = $marketplace_id = $seller_sku = $fnsku = NULL;
        $sql = 'SELECT
                sh.id,
                sh.asin,
                sh.marketplace_id,
                sh.seller_sku,
                sh.quantity,
                asins.title
                FROM
                 shipment_requests AS sh
                LEFT JOIN asins ON asins.asin = sh.asin
                AND asins.marketplaceid = sh.marketplace_id
                WHERE
                  1 = 1
                AND sh.id = ' . $id;
        $allot_progress = DB::connection('vlz')->select($sql);
        $allot_progress = (json_decode(json_encode($allot_progress), true));
        if (!empty($allot_progress)) {
            $title = $allot_progress[0]['title'];
            $asin = $allot_progress[0]['asin'];
            $quantity = $allot_progress[0]['quantity'];
            $marketplace_id = $allot_progress[0]['marketplace_id'];
            $seller_sku = $allot_progress[0]['seller_sku'];
            if (!empty($asin) && !empty($marketplace_id) && !empty($seller_sku)) {
                $sql2 = "SELECT id,fnsku FROM seller_skus WHERE asin='" . $asin . "' AND seller_sku='" . $seller_sku . "' AND marketplaceid='" . $marketplace_id . "'";
                $seller_skus = DB::connection('vlz')->select($sql2);
                $seller_skus = (json_decode(json_encode($seller_skus), true));
                if (!empty($seller_skus)) {
                    $fnsku = $seller_skus[0]['fnsku'];
                }
            }

        }
        return ['title' => $title, 'fnsku' => $fnsku, 'quantity' => $quantity];
    }

    /**
     * 获取大货资料 地址
     * @param Request $request
     */
    public function getCargoData(Request $request)
    {
        $id = @$request['shipment_requests_id'];
        $cargo_data = NULL;
        $cargo_data_arr = $cargo_data_arr_new = [];
        if ($id > 0) {
            $sql = "SELECT id,cargo_data FROM shipment_requests WHERE id =" . $id;
            $shipment_requests = DB::connection('vlz')->select($sql);
            $shipment_requests = (json_decode(json_encode($shipment_requests), true));
            if (!empty($shipment_requests)) {
                $cargo_data = $shipment_requests[0]['cargo_data'];
            }
            if (!empty($cargo_data)) {
                $cargo_data_arr = explode(',', $cargo_data);
                if (!empty($cargo_data_arr)) {
                    foreach ($cargo_data_arr as $ck => $cv) {
                        $last = strripos($cv, '/');
                        $fileName = substr($cv, $last + 1);
                        $cargo_data_arr_new[$ck]['title'] = $fileName;
                        $cargo_data_arr_new[$ck]['url'] = $cv;
                    }
                }
            }
            return $cargo_data_arr_new;
        } else {
            return ['status' => 0, 'msg' => '缺少shipment_requests_id'];
        }

    }

    /**
     * @param Request $request
     * 条形码PDF
     */
    public function downloadPDF(Request $request)
    {
        $width = @$request['width'] ? $request['width'] : 63.5;
        $height = @$request['height'] ? $request['height'] : 38.1;
        $fnsku = @$request['fnsku'] ? $request['fnsku'] : 'STHRT556623';
        $title = @$request['title'] ? $request['title'] : 'title';
        $num = @$request['num'] ? $request['num'] : 21;
        if (strlen($title) > 30) {
            $title = substr($title, 0, 13) . '...' . substr($title, -13, 13);
        }
        if ($width > 0 && $height > 0 && !empty($fnsku) && !empty($title) && $num > 0) {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcode = $generator->getBarcode($fnsku, $generator::TYPE_CODE_128, $widthFactor = 2, $height = 30);
            $barcode = base64_encode($barcode);
            $width = $width . 'mm';
            $height = $height . 'mm';
            // echo ' <img src="data:image/png;base64,' . $barcode . '"/>';
            $mpdf = new \Mpdf\Mpdf(['mode' => 'zh-cn', 'format' => 'A4', 'margin_top' => 0, 'margin_left' => 0, 'margin_right' => 0, 'margin_bottom' => 0]);
            $html = '<div style="width: 210mm; height: 297mm;">
                    <div style="transform-origin: 0px 0px; background-color: white; margin-left: 5mm;padding-top:16px">';
            for ($i = 1; $i <= $num; $i++) {
                $html .= '<div class="small_a4" style="width: ' . $width . '; height: ' . $height . '; display: inline-block; position: relative; float: left; padding-left: 1mm;padding-top:9px">
            <div style="width: 100%; display: flex; align-items: center; padding-left: 18px">
            <img   alt="" src="data:image/png;base64,' . $barcode . '" style="margin:auto;max-width: 100%; max-height: 100%;text-align: center">
            <div style="margin-top: 5px;text-align: center">' . $fnsku . '</div>
            <div style="margin-top: 4px">' . $title . '</div>
            <div style="margin-top: 0px">' . "New" . '</div>
            </div>
            </div>';
            }
            $html .= '</div></div>';
            //echo $html;exit;
            //创建pdf文件
            $mpdf->WriteHTML($html);
            $time = date("Y-m-d") . time() . rand(1, 99999);
            $mpdf->Output($time . ".pdf", "D");
        } else {
            return ['status' => 0, 'message' => '缺少参数'];
        }

    }

    /**
     * 获取shippment_id 及 跟踪单号/单据号
     * @param Request $request
     */
    public function getShippmentIDList(Request $request)
    {
        // $role = $this->getRole(); //todo 打开
        $role = 4;//todo 删除
        $shippment_request_id = @$request['shippment_request_id'];
        $data = $data2 = [];
        if ($shippment_request_id > 0) {
            $sql = "SELECT * FROM shippment WHERE shippment_request_id =" . $shippment_request_id . " GROUP BY shippmentID";
            $shippment = DB::connection('vlz')->select($sql);
            $shippment = (json_decode(json_encode($shippment), true));
            if (!empty($shippment)) {
                foreach ($shippment as $k => $v) {
                    $data[$k]['shippmentID'] = $v['shippmentID'];
                    $data[$k]['shippment_request_id'] = $shippment_request_id;
                }
            }
            $sql2 = "SELECT * FROM shippment WHERE shippment_request_id =" . $shippment_request_id;
            $shippment2 = DB::connection('vlz')->select($sql2);
            $shippment2 = (json_decode(json_encode($shippment2), true));
            if (!empty($shippment2)) {
                foreach ($data as $dk => $dv) {
                    foreach ($shippment2 as $sk => $sv) {
                        if ($sv['shippmentID'] == $dv['shippmentID']) {
                            $data[$dk]['receipts_num'][] = $sv['receipts_num'];
                        }
                    }
                }
            }
            return ['data' => $data, 'role' => $role];
        } else {
            return ['status' => 0, 'msg' => '缺少shippment_request_id'];
        }
    }

    /**
     * 增加shippmentid or 跟踪单号/单据号
     * @param Request $request
     * @return array
     */
    public function addShippments(Request $request)
    {
        $shippment_request_id = $receipts_num = $shippmentID = '';
        $dataArr = $newData = $newData2 = $newData3 =$receipts_num_list= $shippmentIDList=[];
        $dataArr = $request['data'];

        //todo 测试数据 删除

//        $data = [
//            [
//                'shippmentID' => 'shippment_id3',
//                'receipts_num' => ['ttt', '333', 'cctttc4', 'ddd', 'eee'],
//                'shippment_request_id' => 15
//            ],
//            [
//                'shippmentID' => 'shippment_id32',
//                'receipts_num' => ['444', '888', 'ccc5', 'ddd', 'eee'],
//                'shippment_request_id' => 15
//            ],
//            [
//                'shippmentID' => 'shippment_id33',
//                'shippment_request_id' => 15
//            ]
//        ];
//        $dataArr = json_encode($data);
        //  $dataArr = (json_decode($dataArr, true));
        //todo 测试数据 删除 end

        if (!empty($dataArr)) {
                $shippment_request_id = $dataArr[0]['shippment_request_id'];
                if ($shippment_request_id > 0) {
                    //删除旧数据
                    $sql = "DELETE  FROM shippment WHERE shippment_request_id ='" . $shippment_request_id . "'";
                    $res = DB::connection('vlz')->delete($sql);
                }
                foreach ($dataArr as $k => $v) {
                    $shippmentIDList[]=$v['shippmentID'];
                    if (!empty($v['receipts_num'])) {
                        foreach ($v['receipts_num'] as $vk => $vv) {
                            $receipts_num_list[]=$vv;
                            $newData[] = [
                                'shippment_request_id' => $shippment_request_id,
                                'shippmentID' => $v['shippmentID'],
                                'receipts_num' => $vv,
                                'created_at' => date('Y-m-d H:i:s', time())];
                        }
                    } else {
                        $newData2[] = [
                            'shippment_request_id' => $shippment_request_id,
                            'shippmentID' => $v['shippmentID'],
                            'receipts_num' => '',
                            'created_at' => date('Y-m-d H:i:s', time())];
                    }
                }
            $newData3 = array_merge($newData, $newData2);
            if (!empty($newData3)) {
                $result = DB::connection('vlz')->table('shippment')->insert($newData3);
                if ($result > 0) {
                    //$dataallot=['shippment_id'=>implode($shippmentIDList,','),'receipts_num'=>implode(@$receipts_num_list,',')];
                    $dataallot=['shippment_id'=>$shippmentIDList[0],'receipts_num'=>@$receipts_num_list[0]];
                    $result = DB::connection('vlz')->table('allot_progress')
                        ->where('shipment_requests_id', $shippment_request_id)
                        ->update($dataallot);
                    return ['status' => 1, 'msg' => '新增成功'];
                } else {
                    return ['status' => 0, 'msg' => '新增失败'];
                }
            } else {
                return ['status' => 0, 'msg' => '缺少数据'];
            }
        } else {
            return ['status' => 0, 'msg' => '缺少data数据'];
        }
    }

    /**
     * 删除 shippmentid 或 单据号
     * @param Request $request
     * @return array
     */
    public
    function delShippments(Request $request)
    {
        // $role = $this->getRole(); //todo 打开
        $role = 4;
        $id = @$request['id'];
        $shippmentID = @$request['shippmentID'];
        if ($role == 2 || $role == 4 || $role == 6 || $role == 7) {
            if ($id > 0) {
                $sql = 'DELETE  FROM shippment WHERE id =' . $id;
                $res = DB::connection('vlz')->delete($sql);
                if ($res > 0) {
                    return ['status' => 1, 'msg' => '删除成功'];
                } else {
                    return ['status' => 0, 'msg' => '删除失败'];
                }
            } else if (!empty($shippmentID)) {
                $sql = "DELETE  FROM shippment WHERE shippmentID ='" . $shippmentID . "'";
                $res = DB::connection('vlz')->delete($sql);
                if ($res > 0) {
                    return ['status' => 1, 'msg' => 'shippmentID删除成功'];
                } else {
                    return ['status' => 0, 'msg' => 'shippmentID删除失败'];
                }
            } else {
                return ['status' => 0, 'msg' => '缺少参数id或shippmentID'];
            }
        } else {
            return ['status' => 0, 'msg' => '权限不够'];
        }

    }

    /**
     * 修改跟踪单号/单据号
     * @param Request $request
     */
    public
    function upReceiptsNum(Request $request)
    {
        // $role = $this->getRole();//正式 打开 todo
        $id = @$request['id'];
        $receipts_num = @$request['receipts_num'];
        $r_message = [];
        if ($id > 0 && !empty($receipts_num)) {
            $data = ['receipts_num' => $receipts_num];
            $result = DB::connection('vlz')->table('shippment')
                ->where('id', $id)
                ->update($data);
            if ($result) {
                $r_message = ['status' => 1, 'msg' => '修改成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '修改失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少shippment_request_id'];
        }
        return $r_message;

    }

    /**
     * 修改shippmentID
     * @param Request $request
     */
    public
    function upShippmentID(Request $request)
    {
        $id = $request['id'] ? $request['id'] : 0;
        $shippment_id = $request['shippment_id'] ? $request['shippment_id'] : '';
        $shippment_id_old = '';
        $r_message = [];
        if ($id > 0 && !empty($shippment_id)) {
            $sql = "SELECT shippmentID FROM shippment WHERE id =" . $id;
            $shippment = DB::connection('vlz')->select($sql);
            $shippment = (json_decode(json_encode($shippment), true));
            if (!empty($shippment)) {
                $shippment_id_old = $shippment[0]['shippmentID'];
            }
            if (!empty($shippment_id_old)) {
                $data = ['shippmentID' => $shippment_id, 'updated_at' => date('Y-m-d H:i:s', time())];
                $result = DB::connection('vlz')->table('shippment')
                    ->where('shippmentID', $shippment_id_old)
                    ->update($data);
                if ($result) {
                    $r_message = ['status' => 1, 'msg' => '修改成功'];
                } else {
                    $r_message = ['status' => 0, 'msg' => '修改失败'];
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '参数ID错误'];
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '缺少shippment_id或id'];
        }
        return $r_message;
    }

    /**================================公用方法=====================================*/
    /**
     * 获取用户role
     * @param Request $request
     */
    public
    function getRole()
    {
        $user = Auth::user()->toArray();
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $role = 0;
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
                    $role = 5;
                }
            } else {
                $roleUser = DB::table('role_user')->select('user_id', 'role_id')
                    ->where('user_id', @$user['id'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($roleUser)) {
                    $role_id = $roleUser[0]['role_id'];
                    if ($role_id == 23) {
                        /** 计划员角色  */
                        $role = 2;
                    } else if ($role_id == 31) {
                        /** 计划 经理 */
                        $role = 6;
                    } else if ($role_id == 20) {
                        /** 物流操作员 */
                        $role = 7;
                    }
                }
            }
        }
        return $role;
    }

}