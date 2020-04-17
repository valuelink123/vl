<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Asin;

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
        echo '<pre>';
        var_dump($marketing_plan);
        exit;
        return view('marketingPlan.detail', ['role' => $role]);
    }

    public function showData()
    {

    }

    /**
     * 修改 计划 包括状态  信息
     * @author DYS
     * @param Request $request
     */
    public function updatePlan(Request $request)
    {
        //$user = Auth::user()->toArray();//todo 打开
        $user['sap_seller_id'] = 351;
        $update = 0;
        if (!empty($request)) {
            $id = $request['id'];
            $plan_status = $request['plan_status'];
            $r_message = [];//更新返回
            //查询当前 plan_status 状态
            $old_m_plan = DB::connection('vlz')->table('marketing_plan')
                ->select('plan_status', 'goal', 'id', 'updated_at')
                ->where('id', $id)
                ->first();
            $old_m_plan = json_decode(json_encode($old_m_plan), true);

            if ($old_m_plan['plan_status'] == 2 && $plan_status == 4) {
                /** 进行中”状态下，只能改为“已中止 */
                $update = 1;
            } else if ($old_m_plan['plan_status'] == 1 && ($plan_status == 2||$plan_status == 4||$plan_status == 5)) {
                /**  待审批 只能改为 进行中 */
                $update = 1;
            }


            if ($update > 0) {
                $result = DB::connection('vlz')->table('marketing_plan')
                    ->where('id', $id)
                    ->update(['plan_status' => $plan_status, 'updated_at' => time(), 'updated_user_id' => $user['sap_seller_id']]);
                if ($result == 1) {
                    $r_message = ['status' => 1, 'msg' => '更新成功'];
                } else {
                    $r_message = ['status' => 0, 'msg' => '更新失败'];
                }
            }

            return $r_message;
        }

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