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
        $asinList = [];
        if (!empty(Auth::user()->toArray())) {
            $user = Auth::user()->toArray(); //当前用户信息
            $sql = "SELECT sams.asin,asins.marketplaceid,sams.sku_status,sams.sku,asins.reviews,asins.rating from sap_asin_match_sku as sams LEFT JOIN asins on asins.asin= sams.asin WHERE sap_seller_id =" . $user['sap_seller_id'] . " GROUP BY
	                sams.marketplace_id,sams.asin;";
            $user_asin_list_obj = DB::connection('vlz')->select($sql);
            $user_asin_list = (json_decode(json_encode($user_asin_list_obj), true));            //asin 站点 suk suk状态
            if (!empty($user_asin_list)) {
                foreach ($user_asin_list as $k => $v) {
                    if (strlen($v['asin']) < 8) {
                        unset($user_asin_list[$k]);
                    }
                }
            }
            //查询所有汇率信息
            $currency_rates = DB::connection('vlz')->table('currency_rates')
                ->select('currency', 'rate', 'id', 'updated_at')
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
        }
        echo '<pre>';
        var_dump($user_asin_list);
        exit;
        return view('marketingPlan.index', ['user_asin_list' => $user_asin_list, 'currency_rates' => $currency_rates]);
    }

    /**
     * 编辑修改 rsg plan
     * @author DYS
     * @copyright 2020年4月17日
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(Request $request)
    {
        /** 超级权限*/
        $admin = array("charlie@valuelinkcorp.com", "zouyuanxun@valuelinkcorp.com", "zanhaifang@valuelinkcorp.com", "huzaoli@valuelinkcorp.com", 'fanlinxi@valuelinkcorp.com');
        $role = 0;//角色
        $user = Auth::user()->toArray();
        if (!empty($user)) {
            if (!empty($user['email']) && in_array($user['email'], $admin)) {
                /**  特殊权限着 查询所有用户 */
                $bool_admin = 1;
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

            } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                $role = 3;
                if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**查询所有BG下面员工*/
                    $role = 2;
                } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                    /**此条件为 普通销售*/
                    $role = 0;
                } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**  BU 负责人  */
                    $role = 1;
                }
            }
        }

        if ($request['id'] > 0) {
            $sql = "SELECT
                    mp.sap_seller_id,
                    mp.goal,
                    mp.plan_status,
                    mp.asin,
                    mp.marketplaceid,
                    mp.sku,
                    mp.sku_status,
                    mp.sku_price,
                    asins.reviews,
                    asins.rating
                FROM
                    marketing_plan AS mp
                LEFT JOIN asins ON asins.asin = mp.asin
                AND asins.marketplaceid = mp.marketplaceid
                WHERE
                    mp.id =" . $request['id'];
            $marketing_plan = json_decode(json_encode(DB::connection('vlz')->select($sql)), true);
        }
        if($user['sap_seller_id']!=$marketing_plan[0]['sap_seller_id']&&$role==0){
            $marketing_plan=NULL;
        }
        echo '<pre>';
        var_dump($marketing_plan);
        exit;
        return view('marketingPlan.detail', ['role' => $role,'marketing_plan'=>$marketing_plan]);
    }

    public function showData()
    {

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
        $user['sap_seller_id'] = 351;
        $update = 0;
        if (!empty($request)) {
            $id = $request['id'];
            $notes=isset($request['notes'])?$request['notes']:'';//备注
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
            } else if ($old_m_plan['plan_status'] == 1 && ($plan_status == 2 || $plan_status == 4 || $plan_status == 5)) {
                /**  待审批 只能改为 进行中 */
                $update = 1;
            }

            if ($update > 0) {
                $result = DB::connection('vlz')->table('marketing_plan')
                    ->where('id', $id)
                    ->update(['plan_status' => $plan_status,
                        'updated_at' => time(),
                        'updated_user_id' => @$user['sap_seller_id'],
                        'notes'=>$notes
                    ]);
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
            return $r_message;
        }
    }

    /**
     * 定时任务
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
                $result = DB::connection('vlz')->table('marketing_plan')
                    ->whereIn('id', $idList)
                    ->update(['plan_status' => 3, 'updated_at' => time()]);
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

    /**
     * 上传文件接口
     * @param Request $request
     * @return mixed
     */
    public function uploadfiles(Request $request)
    {
        $file = $request->file('files');
        if ($file) {
            try {
                $file_name = $file[0]->getClientOriginalName();
                $file_size = $file[0]->getSize();
                $file_ex = $file[0]->getClientOriginalExtension();
                $newname = $file_name;
                $newpath = '/uploads/' . date('Ym') . '/' . date('d') . '/' . date('His') . rand(100, 999) . intval(Auth::user()->id) . '/';
                $file[0]->move(public_path() . $newpath, $newname);
            } catch (\Exception $exception) {
                $error = array(
                    'name' => $file[0]->getClientOriginalName(),
                    'size' => $file[0]->getSize(),
                    'error' => $exception->getMessage(),
                );
                // Return error
                return \Response::json($error, 400);
            }

            // If it now has an id, it should have been successful.
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
                return \Response::json(array('files' => array($success)), 200);
            } else {
                return \Response::json('Error', 400);
            }
            return \Response::json('Error', 400);
        }

    }


}