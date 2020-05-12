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

header('Access-Control-Allow-Origin:*');

class MarketingPlanController extends Controller
{
//判断是否登录
//    public function __construct()
//    {
//        $this->middleware('auth');
//        parent::__construct();
//    }

    public function index()
    {
        $user = Auth::user()->toArray();
        $sap_seller_id = $user['sap_seller_id']>0?$user['sap_seller_id']:0;
        return view('marketingPlan.index', ['sap_seller_id' => $sap_seller_id]);
    }

    public function detail()
    {
        $user = Auth::user()->toArray();
        $sap_seller_id = $user['sap_seller_id']>0?$user['sap_seller_id']:0;
        return view('marketingPlan.detail', ['sap_seller_id' => $sap_seller_id]);
    }

    public function index1(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $sap_seller_id = $request['sap_seller_id'] ? $request['sap_seller_id'] : 351;
        if ($sap_seller_id > 0) {
            $sql = "SELECT sams.asin,asins.marketplaceid,sams.sku_status,sams.sku,asins.reviews,asins.rating 
                    from sap_asin_match_sku as sams LEFT JOIN asins on asins.asin= sams.asin 
                    WHERE sap_seller_id =" . $sap_seller_id . " AND marketplaceid!=''
                    GROUP BY asins.marketplaceid,sams.asin";
            $user_asin_list_obj = DB::connection('vlz')->select($sql);
            $user_asin_list = (json_decode(json_encode($user_asin_list_obj), true));            //asin 站点 suk suk状态
            if (!empty($user_asin_list)) {
                foreach ($user_asin_list as $k => $v) {
                    if (strlen($v['asin']) < 8) {
                        unset($user_asin_list[$k]);
                    }
                    $user_asin_list[$k]['country'] = $DOMIN_MARKETPLACEID_SX[$v['marketplaceid']];
                }
            }

            //查询所有汇率信息
            $currency_rates = DB::connection('vlz')->table('currency_rates')
                ->select('currency', 'rate', 'id', 'updated_at')
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
        }
        return [$user_asin_list, $currency_rates];
        return view('marketingPlan.index', ['user_asin_list' => $user_asin_list, 'currency_rates' => $currency_rates]);
    }

    /**
     * 根据asin marketplace_id查询 日常报告
     * @param Request $request
     */
    public function getAsinDailyReport(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $SKU_STATUS_KV= Asin::SKU_STATUS_KV;
        $seller_sku = '';
        $single_economic = $avg_day_sales = $cost = 0;
        if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
            $asin_daily_report = DB::connection('vlz')->table('asin_daily_report')
                ->select('date', 'ranking', 'id', 'conversion', 'fba_stock')
                ->where('asin', $request['asin'])
                ->where('marketplace_id', $request['marketplace_id'])
                ->orderBy('date', 'desc')
                ->first();
            $sql = "SELECT asins.fulfillment,asins.commission,sams.asin,asins.marketplaceid,sams.sku_status,sams.sku,asins.reviews,asins.rating,sams.seller_sku  
                    from sap_asin_match_sku as sams LEFT JOIN asins on asins.asin= sams.asin 
                    WHERE asins.asin ='" . $request['asin'] . "' 
                    AND asins.marketplaceid = '" . $request['marketplace_id'] . "' GROUP BY asins.marketplaceid,sams.asin";
            $user_asin_list_obj = DB::connection('vlz')->select($sql);
            $user_asin_list = (json_decode(json_encode($user_asin_list_obj), true));
            if (!empty($user_asin_list)) {
                $user_asin_list = $user_asin_list[0];
                $seller_sku = $user_asin_list['seller_sku'];
                $sku = $user_asin_list['sku'];
                $marketplaceid = $user_asin_list['marketplaceid'];
                $country = $DOMIN_MARKETPLACEID_SX[$marketplaceid];
                if (!empty($seller_sku)) {
                    $daily_statistics = DB::connection('vlz')->table('daily_statistics')
                        ->select('afn_sellable', 'id', 'afn_reserved')
                        ->where('seller_sku', $seller_sku)
                        ->orderBy('date', 'desc')
                        ->first();
                }
                if (!empty($marketplaceid) && !empty($sku)) {
                    $view_cost_of_skus = DB::connection('vlz')->table('view_cost_of_skus')
                        ->select('cost', 'id')
                        ->where('sku', $sku)
                        ->where('marketplace_id', $marketplaceid)
                        ->first();
                }
                $view_cost_of_skus = (json_decode(json_encode($view_cost_of_skus), true));
                $cost = $view_cost_of_skus['cost'];
            }
            $daily_statistics = (json_decode(json_encode($daily_statistics), true));
            $FBA_Stock = $daily_statistics['afn_sellable'] + $daily_statistics['afn_reserved'];
            $asin_daily_report = (json_decode(json_encode($asin_daily_report), true));
            //查询当前日均  当前经济效益/个
            $sql1 = "SELECT
                        seller_id,
                        marketplace_id,
                        seller_sku,
                        asin,
                        IFNULL(
                            sum(
                                amount_income + amount_refund - cost
                            ) / sum(
                                quantity_shipped - quantity_returned
                            ),
                            0
                        ) AS single_economic,
                        sum(
                            quantity_shipped - quantity_returned
                        ) / 7 AS avg_day_sales
                    FROM
                        daily_statistics
                    WHERE
                        date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    AND marketplace_id='" . $request['marketplace_id'] . "'
                    AND asin = '" . $request['asin'] . "'
                    GROUP BY
                        seller_id,
                        marketplace_id,
                        seller_sku";
            $statistics_o = DB::connection('vlz')->select($sql1);
            $statistics = (json_decode(json_encode($statistics_o), true));
            if (!empty($statistics)) {
                $statistics = $statistics[0];
                $single_economic = $statistics['single_economic'];
                $avg_day_sales = $statistics['avg_day_sales'];
            }
            $user_asin_list['fba_stock'] = $FBA_Stock;
            $user_asin_list['country'] = $country;
            $user_asin_list['ranking'] = $asin_daily_report['ranking'];
            $conversion = $asin_daily_report['conversion'] > 0 ? $asin_daily_report['conversion'] * 100 : 0;
            $user_asin_list['conversion'] = $conversion . '%';
            $user_asin_list['single_economic'] = $single_economic==0?0:$single_economic;
            $user_asin_list['avg_day_sales'] = $avg_day_sales==0?0:$avg_day_sales;
            $user_asin_list['cost'] = $cost;
            $user_asin_list['sku_status'] = $SKU_STATUS_KV[$user_asin_list['sku_status']];

            if (!empty($user_asin_list)) {
                return $user_asin_list;
            }
        } else {
            return '参数不够';
        }
        exit;
    }

    /**
     * 编辑修改 rsg plan
     * @author DYS
     * @copyright 2020年4月17日
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detailEdit(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        /** 超级权限*/
        $ADMIN_EMAIL=Asin::ADMIN_EMAIL;
        $role = 0;//角色
        if ($request['sap_seller_id']) {
            $user = DB::table('users')->select('sap_seller_id', 'id', 'name', 'email', 'seller_rules', 'ubg', 'ubu')
                ->where('sap_seller_id', $request['sap_seller_id'])
                ->first();

            $user = (json_decode(json_encode($user), true));
        }
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

        if ($request['id'] > 0) {
            $sql = "SELECT * FROM marketing_plan WHERE id =" . $request['id'];
            $marketing_plan = json_decode(json_encode(DB::connection('vlz')->select($sql)), true);
            $marketing_plan = $marketing_plan[0];
            $marketing_plan['from_time'] = date('Y-m-d', $marketing_plan['from_time']);
            $marketing_plan['to_time'] = date('Y-m-d', $marketing_plan['to_time']);
            $marketing_plan['complete_at'] = date('Y-m-d', $marketing_plan['complete_at']);
            $marketing_plan['country'] = $DOMIN_MARKETPLACEID_SX[$marketing_plan['marketplaceid']];
        } else {
            return '缺少参数';
        }
        if ($user['sap_seller_id'] != @$marketing_plan['sap_seller_id'] && $role == 1) {
            $role = 0;
        }

        return ['role' => $role, 'marketing_plan' => $marketing_plan];
        //return view('marketingPlan.detail', ['role' => $role, 'marketing_plan' => $marketing_plan]);
    }

    /**
     * 修改 计划 包括状态  信息
     * @author DYS
     * @param id  marketing_plan id
     * @param  plan_status
     * @param Request $request
     */
    public function updatePlan(Request $request)
    {
        //$user = Auth::user()->toArray();//todo 打开
        $DOMIN_MARKETPLACEID_RUL = Asin::DOMIN_MARKETPLACEID_URL;
        $sap_seller_id = $request['sap_seller_id'] ? $request['sap_seller_id'] : 352;
        $update = 0;
        if (!empty($request)) {
            $id = $request['id'];
            //  $notes = isset($request['notes']) ? $request['notes'] : '';//备注
            $plan_status = $request['plan_status'];
            $r_message = $resProductIds = [];//更新返回
            //查询当前 plan_status 状态
            $old_m_plan = DB::connection('vlz')->table('marketing_plan')
                ->select('rsg_d_target', 'from_time', 'to_time', 'plan_status', 'goal', 'id', 'updated_at', 'asin', 'marketplaceid')
                ->where('id', $id)
                ->first();
            $old_m_plan = json_decode(json_encode($old_m_plan), true);

            if ($old_m_plan['plan_status'] == 2 && $plan_status == 4) {
                /** 进行中”状态下，只能改为“已中止 */
                $update = 1;
            } else if ($old_m_plan['plan_status'] == 1 && ($plan_status == 2 || $plan_status == 5)) {
                /**  待审批 只能改为 进行中 或者 拒绝 */
                $update = 1;
            } else if ($old_m_plan['plan_status'] == 3 || $old_m_plan['plan_status'] == 4 || $old_m_plan['plan_status'] == 5) {
                /** 已完结 已终止 已拒绝 不能在修改*/
                $update = 0;
            }
            if ($update > 0) {
                $up_data = [
                    'plan_status' => $plan_status,
                    'updated_at' => time(),
                    'updated_user_id' => $sap_seller_id
                ];
                //修改实际开始时间 为预计开始时间
                if ($plan_status == 2) {
                    $up_data = array_merge($up_data, ['reality_start' => $old_m_plan['from_time']]);
                } elseif ($plan_status == 4) {
                    /**
                     * 状态为4 已终止
                     * 修改实际结束时间 为 预计结束
                     */
                    $up_data = array_merge($up_data, ['reality_end' => $old_m_plan['to_time']]);
                }
                $result = DB::connection('vlz')->table('marketing_plan')
                    ->where('id', $id)
                    ->update($up_data);
                if ($result == 1) {
                    $r_message = ['status' => 1, 'msg' => '更新成功'];
                    //获取regproduct 列  对应数据
                    $site = $DOMIN_MARKETPLACEID_RUL[$old_m_plan['marketplaceid']];
                    $regProduct = RsgProduct::select('id', 'asin', 'created_at')
                        ->where('site', '=', $site)
                        ->where('asin', '=', $old_m_plan['asin'])
                        ->where('created_at', '=', date('Y-m-d', time()))
                        ->get()->toArray();
                    foreach ($regProduct as $rk => $rv) {
                        $resProductIds[] = $rv['id'];
                    }
                    //进行中 更新rsgproduct 列表中的target
                    if ($old_m_plan['from_time'] <= time() && $old_m_plan['to_time'] >= time()) {
                        if ($plan_status == 2) {
                            if (!empty($resProductIds)) {
                                RsgProduct::whereIn('id', $resProductIds)->update(['sales_target_reviews' => $old_m_plan['rsg_d_target']]);
                            }
                        } elseif ($plan_status == 3 || $plan_status == 4) {
                            /**  已终止或  已完结 更新 target 为 0 */
                            if (!empty($resProductIds)) {
                                RsgProduct::whereIn('id', $resProductIds)->update(['sales_target_reviews' => 0]);
                            }
                        }
                    } else {
                        echo $old_m_plan['from_time'] . '----' . time() . '----' . $old_m_plan['to_time'];
                    }
                } else {
                    $r_message = ['status' => 0, 'msg' => '更新失败'];
                }
            } else {
                $r_message = ['status' => 0, 'msg' => '条件不符'];
            }

            return $r_message;
        }

    }

    /**
     * @param $ 删除某张图片
     */
    public function delfiles(Request $request)
    {
        if (!empty($request['files_url']) && !empty($request['id'])) {
            $marketing_plan = DB::connection('vlz')->table('marketing_plan')
                ->select('files', 'id')
                ->where('id', $request['id'])
                ->first();
            $marketing_plan = (json_decode(json_encode($marketing_plan), true));
            if (!empty($marketing_plan)) {
                $files = explode(',', $marketing_plan['files']);
                $key = array_search($request['files_url'], $files);
                if ($key > 0) {
                    unset($files[$key]);
                    $result = DB::connection('vlz')->table('marketing_plan')
                        ->where('id', $request['id'])
                        ->update(['files' => implode($files, ',')]);
                    if ($result > 0) {
                        $r_message = ['status' => 1, 'msg' => '删除成功'];
                    } else {
                        $r_message = ['status' => 0, 'msg' => '删除失败'];
                    }

                } elseif ($marketing_plan['files'] == $request['files_url']) {
                    $result = DB::connection('vlz')->table('marketing_plan')
                        ->where('id', $request['id'])
                        ->update(['files' => '']);
                    if ($result > 0) {
                        $r_message = ['status' => 1, 'msg' => '删除成功'];
                    } else {
                        $r_message = ['status' => 0, 'msg' => '删除失败'];
                    }
                } else {
                    $r_message = ['status' => 0, 'msg' => '没有此图片(url不存在)'];
                }
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '需要文件url，以及id两个参数'];
        }
        return $r_message;
    }

    /**
     * rsg 任务列表
     * @author DYS
     * @param Request $request
     */
    public function rsgList(Request $request)
    {
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        $ADMIN_EMAIL=Asin::ADMIN_EMAIL;
        $sapSellerIdList=[];
        if ($request['sap_seller_id']) {
            $user = DB::table('users')->select('sap_seller_id', 'id', 'name', 'email', 'seller_rules', 'ubg', 'ubu')
                ->where('sap_seller_id', $request['sap_seller_id'])
                ->first();
            $user = (json_decode(json_encode($user), true));
        }
        if (!empty($user)) {
            if (!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) {
                /**  特殊权限着 查询所有用户 */
                $allUsers = DB::table('users')->select('id', 'name', 'sap_seller_id')
                    ->where('ubu', '!=', "")
                    ->orwhere('ubg', '!=', "")
                    ->orwhere('seller_rules', '!=', "")
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($allUsers)) {
                    foreach ($allUsers as $auk => $auv) {
                        $sapSellerIdList[$auv['sap_seller_id']] = $auv['name'];
                    }
                }
            } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                //判断是否是销售 及 对应领导角色
                $allUsers = DB::table('users')->select('id', 'sap_seller_id', 'ubg', 'ubu', 'name')
                    ->where('ubg', $user['ubg'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**查询所有BG下面员工*/
                    if (!empty($allUsers)) {
                        foreach ($allUsers as $auk => $auv) {
                            $sapSellerIdList[$auv['sap_seller_id']] = $auv['name'];
                        }
                    }
                } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                    /**此条件为 普通销售*/
                    $sapSellerIdList[$user['sap_seller_id']] = $user['name'];
                } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**  bu 负责人 及所有下属 */
                    if (!empty($allUsers)) {
                        foreach ($allUsers as $auk => $auv) {
                            if ($auv['ubu'] == $user['ubu']) {
                                $sapSellerIdList[$auv['sap_seller_id']] = $auv['name'];
                            }
                        }
                    }
                }
            } else {
                $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                return $err_message;
            }
        }
        $sql = 'SELECT
                marketing_plan.id,
                images,
                goal,
                rsg_total,
                est_spend,
                actual_spend,
                current_60romi,
                actual_60romi,
                marketing_plan.sap_seller_id,
                marketing_plan.created_at,
                from_time,
                to_time,
                marketing_plan.asin,
                marketing_plan.updated_at,
                plan_status,
                marketing_plan.marketplaceid,
                marketing_plan.sku,
                sku_price,
                marketing_plan.sku_status,
                sams.sap_seller_bg as bg,
                sams.sap_seller_bu as bu
                FROM marketing_plan
                LEFT JOIN sap_asin_match_sku as sams ON marketing_plan.asin = sams.asin AND marketing_plan.marketplaceid = sams.marketplace_id
                WHERE 1 = 1  ';
        if(!empty($sapSellerIdList)){
            $sql = $sql .  ' AND marketing_plan.sap_seller_id in (' . implode(array_keys($sapSellerIdList), ',') . ')';
        }
        //搜索   创建时间 范围
        if (strtotime(@$request['created_at_s']) > 0 && strtotime(@$request['created_at_e']) > 0 && strtotime(@$request['created_at_e']) > strtotime(@$request['created_at_s'])) {
            $sql = $sql . ' AND created_at >=' . strtotime($request['created_at_s']) . ' AND  created_at <=' . strtotime($request['created_at_e']);
        }
        //搜索   预计开始、结束时间  范围
        $from_time=strtotime(@$request['from_time']);
        $to_time=strtotime(@$request['to_time']);
        if (@$from_time > 0 && @$to_time > 0 && @$to_time > @$from_time) {
            $sql = $sql .' AND (('.$from_time.' <=from_time AND '.$to_time.'>=to_time) OR ('.$from_time.' >=from_time AND '.$to_time.'<= to_time) OR ('.$to_time.'>=from_time AND '.$to_time.'<= to_time) or ('.$from_time.' >=from_time AND '.$from_time.'<=to_time ))';
        }
        //搜索   实际开始、结束时间  范围
        $reality_start=strtotime(@$request['reality_start']);
        $reality_end=strtotime(@$request['reality_end']);
        if (@$reality_start > 0 && @$reality_end > 0 && @$reality_end > @$reality_start) {
            $sql = $sql .' AND (('.$reality_start.' <=reality_start AND '.$reality_end.'>=reality_end) OR ('.$reality_start.' >=reality_start AND '.$reality_end.'<= reality_end) OR ('.$reality_end.'>=reality_start AND '.$reality_end.'<= reality_end) or ('.$reality_start.' >=reality_start AND '.$reality_start.'<=reality_end ))';
        }
        //搜索 asin sku
        if (!empty($request['condition'])) {
            $sql = $sql .' AND ( marketing_plan.asin LIKE "%'.$request['condition'].'%" or marketing_plan.sku LIKE "%'.$request['condition'].'%")';
        }
        $sql = $sql . ' GROUP BY marketing_plan.id ' ;
        //排序 顺序
        if (!empty($request['rank']) && !empty($request['order'])) {
            $sql = $sql . ' ORDER BY ' . $request['rank'] . ' ' . $request['order'];
        }

        $rsgList = DB::connection('vlz')->select($sql);
        $rsgList = (json_decode(json_encode($rsgList), true));
        $planStatus=['0','Pending','Ongoing','Completed','Paused','Rejected'];
        if(!empty($rsgList)&&!empty($sapSellerIdList)){
            foreach ($rsgList as $k=>$v){
                $rsgList[$k]['Seller']=$sapSellerIdList[$v['sap_seller_id']];
                $rsgList[$k]['updated_at']=date('Y-m-d',$v['updated_at']);
                $rsgList[$k]['plan_status']=$planStatus[$v['plan_status']];
                $rsgList[$k]['current_60romi']=($v['current_60romi']*100).'%';
                $rsgList[$k]['actual_60romi']=($v['actual_60romi']*100).'%';
                $rsgList[$k]['actual_spend']=$v['actual_spend'];
                $rsgList[$k]['applied']=0;// todo 暂时预留  没有数据
                $rsgList[$k]['reivews']=0;// todo 暂时预留  没有数据
                $rsgList[$k]['type']='RSG';
                $rsgList[$k]['toUrl']=$DOMIN_MARKETPLACEID_URL[$v['marketplaceid']];
                $rsgList[$k]['station']=$DOMIN_MARKETPLACEID_SX[$v['marketplaceid']];

                if(!empty($v['images'])){
                    $rsgList[$k]['image']=explode(',',$v['images'])[0];
                }else{
                    $rsgList[$k]['image']='';
                }
            }
            return ['status'=>1,$rsgList,$sapSellerIdList];
        }else{
            return ['status'=>0,'msg'=>'没有数据'];
        }
//        echo '<pre>';
//        var_dump($rsgList);
    }

    /**
     * 新增(编辑) 接口
     * @param Request $request
     */
    public function addMarketingPlan(Request $request)
    {
        //goal plan_status currency_rates_id 类型 数字
        $fileUrl = $images = '';
        if (!empty($request['files'])) {
            $fileUrl = implode(',', $request['files']);
        }

        if (@$request['id'] > 0) {
            $update_data = [
                'updated_user_id' => $request['sap_seller_id'],
                'plan_status' => @$request['plan_status'],
                'notes' => @$request['notes'],
                'actual_spend' => @$request['actual_spend'],
                'updated_at' => time(),
                'files' => $fileUrl
            ];
            $result = DB::connection('vlz')->table('marketing_plan')
                ->where('id', $request['id'])
                ->update($update_data);
            if ($result > 0) {
                $r_message = ['status' => 1, 'msg' => '编辑成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '编辑失败'];
            }
        } else {
            $tomorrow_t = strtotime(date('Y-m-d')) + 3600 * 24;
            if ($request['from_time'] >= $tomorrow_t && $request['to_time'] >= $tomorrow_t) {
                if (!empty($request['asin']) && !empty($request['marketplaceid']) && !empty($request['sap_seller_id']) && !empty($request['sku'])) {
                    $asins = DB::connection('vlz')->table('asins')
                        ->select('id', 'images')
                        ->where('asin', $request['asin'])
                        ->where('marketplaceid', $request['marketplaceid'])
                        ->first();
                    if (!empty($asins)) {
                        $images = $asins->images;
                    }
                    $data = [
                        'sap_seller_id' => $request['sap_seller_id'],
                        'goal' => @$request['goal'],
                        'plan_status' => @$request['plan_status'],
                        'asin' => $request['asin'],
                        'marketplaceid' => $request['marketplaceid'],
                        'sku' => $request['sku'],
                        'sku_status' => @$request['sku_status'],
                        'sku_price' => @$request['sku_price'],
                        'currency_rates_id' => @$request['currency_rates_id'],
                        'rating' => @$request['rating'],
                        'reviews' => @$request['reviews'],
                        'fba_stock' => @$request['fba_stock'],
                        'target_rating' => @$request['target_rating'],
                        'target_reviews' => @$request['target_reviews'],
                        'from_time' => @$request['from_time'],
                        'to_time' => @$request['to_time'],
                        'rsg_price' => @$request['rsg_price'], 'rsg_d_target' => @$request['rsg_d_target'],
                        'rsg_total' => @$request['rsg_total'], 'est_spend' => @$request['est_spend'],
                        'current_rank' => @$request['current_rank'], 'current_cr' => @$request['current_cr'],
                        'current_units_day' => @$request['current_units_day'], 'current_e_val' => @$request['current_e_val'], 'sap_seller_id' => @$request['sap_seller_id'],
                        'current_60romi' => @$request['current_60romi'], 'actual_rank' => @$request['actual_rank'],
                        'actual_cr' => @$request['actual_cr'], 'actual_units_day' => @$request['actual_units_day'] ? @$request['actual_units_day'] : 0,
                        'actual_e_val' => @$request['actual_e_val'], 'actual_60romi' => @$request['actual_60romi'],
                        'est_rank' => @$request['est_rank'], 'est_cr' => @$request['est_cr'],
                        'est_units_day' => @$request['est_units_day'] ? @$request['est_units_day'] : 0, 'est_val' => @$request['est_val'] ? @$request['est_val'] : 0,
                        'est_120d_romi' => @$request['est_120d_romi'] ? @$request['est_120d_romi'] : 0, 'cr_increase' => @$request['cr_increase'] ? @$request['cr_increase'] : 0,
                        'units_d_increase' => @$request['units_d_increase'], 'val_d_increase' => @$request['val_d_increase'],
                        'investment_return_d' => @$request['investment_return_d'], 'actual_spend' => @$request['actual_spend'] ? $request['actual_spend'] : 0,
                        'cr_complete' => @$request['cr_complete'] ? $request['cr_complete'] : 0, 'units_d_complete' => @$request['units_d_complete'],
                        'e_val_complete' => @$request['e_val_complete'], 'investment_return_c' => @$request['investment_return_c'],
                        'cr_complete' => @$request['cr_complete'], 'created_at' => time(),'updated_at' => time(),
                        'files' => $fileUrl, 'notes' => @$request['notes'] ? @$request['notes'] : '',
                        'images' => $images,
                    ];
                    $result = DB::connection('vlz')->table('marketing_plan')->insert($data);
                    if ($result > 0) {
                        $new_mp = DB::connection('vlz')->table('marketing_plan')
                            ->select('id')
                            ->orderBy('id', 'desc')
                            ->first();
                        $new_mp = (json_decode(json_encode($new_mp), true));
                        if ($new_mp) {
                            $r_message = ['status' => 1, 'msg' => '新增成功', 'id' => $new_mp['id']];
                        }

                    } else {
                        $r_message = ['status' => 0, 'msg' => '新增失败'];
                    }
                } else {
                    $r_message = ['status' => 0, 'msg' => 'asin/sku/marketplaceid/sap_seller_id 等参数不能为空'];
                }

            } else {
                $r_message = ['status' => 0, 'msg' => '开始、结束时间必须大于今天'];
            }
        }

        return $r_message;
    }

    /**
     * 定时任务
     *
     * 完成目标
     * 更新 完成时间
     */
    public function achieveGoals()
    {
        /** 查询所有未完成的 信息 状态 非 已拒绝*/
        $m_p = DB::connection('vlz')->table('marketing_plan')
            ->select('plan_status', 'id', 'to_time', 'marketplaceid', 'asin', 'target_rating', 'target_reviews')
            ->where('plan_status', '!=', 5)
            ->where('complete_at', 0)
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        $r_message = [];
        if (!empty($m_p)) {
            foreach ($m_p as $mk => $mv) {
                $id = $mv['id'];
                $asin = $mv['asin'];
                $marketplaceid = $mv['marketplaceid'];
                $target_reviews = $mv['target_reviews'];
                $target_rating = $mv['target_rating'];
                if (isset($asin) && isset($marketplaceid) && $target_reviews > 0 && $target_rating > 0 && $id > 0) {
                    $asin = DB::connection('vlz')->table('asins')
                        ->select('id')
                        ->where('asin', $asin)
                        ->where('marketplaceid', $marketplaceid)
                        ->where('reviews', '>=', $target_reviews)
                        ->where('rating', '>=', $target_rating)
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                    if (!empty($asin) && count($asin) > 0) {
                        //更新 达成时间
                        $result = DB::connection('vlz')->table('marketing_plan')
                            ->where('id', $id)
                            ->update(['complete_at' => time(), 'updated_at' => time()]);
                        if ($result > 0) {
                            $r_message = ['status' => 1, 'msg' => $id . '已完成目标，并更新完成时间'];
                        }
                    }
                }
            }
            print_r($r_message);
        }
        /**  查询已完结但是没有更新数据的--------达成目标后1周 更新数据 */
        $m_p_6 = DB::connection('vlz')->table('marketing_plan')
            ->select('complete_at', 'id', 'marketplaceid', 'asin')
            ->where('plan_status', '!=', 5)
            ->where('plan_status', '!=', 6)
            ->where('plan_status', '!=', 1)
            ->where('plan_status', '!=', 4)
            ->where('complete_at', '>', 0)
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (!empty($m_p_6)) {
            foreach ($m_p_6 as $mk => $mv) {
                $id = $mv['id'];
                $asin = $mv['asin'];
                $marketplaceid = $mv['marketplaceid'];
                $complete_at = $mv['complete_at'];
                if ($complete_at <= time() - 3600 * 24 * 7) {
                    $sql1 = "SELECT
                        seller_id,
                        marketplace_id,
                        seller_sku,
                        asin,
                        IFNULL(
                            sum(
                                amount_income + amount_refund - cost
                            ) / sum(
                                quantity_shipped - quantity_returned
                            ),
                            0
                        ) AS single_economic,
                        sum(
                            quantity_shipped - quantity_returned
                        ) / 7 AS avg_day_sales
                    FROM
                        daily_statistics
                    WHERE
                        date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    AND marketplace_id='" . $marketplaceid . "'
                    AND asin = '" . $asin . "'
                    GROUP BY
                        seller_id,
                        marketplace_id,
                        seller_sku";
                    $statistics_o = DB::connection('vlz')->select($sql1);
                    $statistics = (json_decode(json_encode($statistics_o), true));
                    //查询排名 和 转化率
                    $asin_daily_report = DB::connection('vlz')->table('asin_daily_report')
                        ->select('date', 'ranking', 'id', 'conversion', 'fba_stock')
                        ->where('asin', $asin)
                        ->where('marketplace_id', $marketplaceid)
                        ->orderBy('date', 'desc')
                        ->first();
                    $asin_daily_report = (json_decode(json_encode($asin_daily_report), true));
                    if (!empty($statistics) || !empty($asin_daily_report)) {
                        $marketing_plan['single_economic'] = @$statistics[0]['single_economic'];
                        $marketing_plan['avg_day_sales'] = @$statistics[0]['avg_day_sales'];
                        $marketing_plan['ranking'] = @$asin_daily_report['ranking'];
                        $marketing_plan['conversion'] = @$asin_daily_report['conversion'];
                        $marketing_plan['date'] = @$asin_daily_report['date'];
                        $data = [
                            'actual_rank' => @$asin_daily_report['ranking'],
                            'actual_cr' => @$asin_daily_report['conversion'],
                            'actual_units_day' => @$statistics[0]['avg_day_sales'],
                            'actual_e_val' => @$statistics[0]['single_economic'],
                            'updated_at' => time(), 'plan_status' => 6
                        ];
                        $result = DB::connection('vlz')->table('marketing_plan')
                            ->where('id', $id)
                            ->update($data);
                        if ($result > 0) {
                            $r_message = ['status' => 1, 'msg' => $id . '达成目标后1周 更新成功'];
                        }

                    }
                }
            }

        } else {
            $r_message = ['status' => 0, 'msg' => '没有符合的数据'];
        }
        return $r_message;
    }

    /**
     * 定时任务
     *
     * 查询到达 结束时间 更新状态为  已完结
     */
    public function timingUpdate()
    {
        $DOMIN_MARKETPLACEID_RUL = Asin::DOMIN_MARKETPLACEID_URL;
        $end_time = strtotime(date('Y-m-d', time()));
        $idList = $resProductIds = $r_message = [];
        $m_p = DB::connection('vlz')->table('marketing_plan')
            ->select('plan_status', 'id', 'to_time', 'marketplaceid', 'asin')
            ->where('plan_status', 2)
            ->where('to_time', '<', $end_time)
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (!empty($m_p)) {
            foreach ($m_p as $k => $v) {
                $idList[] = $v['id'];
                //获取regproduct 列  对应数据
                $site = $DOMIN_MARKETPLACEID_RUL[$v['marketplaceid']];
                $regProduct = RsgProduct::select('id', 'asin', 'created_at')
                    ->where('site', '=', $site)
                    ->where('asin', '=', $v['asin'])
                    ->where('created_at', '=', date('Y-m-d', time()))
                    ->get()->toArray();
                //进行中 更新rsgproduct 列表中的target
                //已终止或  已完结 更新 target 为 0
                if (!empty($regProduct)) {
                    foreach ($regProduct as $rk => $rv) {
                        $resProductIds[] = $rv['id'];
                    }
                }
            }
            if (!empty($idList)) {
                /**  修改更新时间为、实际完结时间 为当前时间 */
                $result = DB::connection('vlz')->table('marketing_plan')
                    ->whereIn('id', $idList)
                    ->update(['plan_status' => 3, 'updated_at' => time(), 'reality_end' => time() - 3600 * 24]);
                if ($result > 0 && !empty($resProductIds)) {
                    RsgProduct::whereIn('id', $resProductIds)->update(['sales_target_reviews' => 0]);
                    $r_message = ['status' => 1, 'msg' => '更新成功'];
                }
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '没有到达结束时间的任务'];
        }
        return $r_message;

    }

}