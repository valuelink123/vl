<?php

namespace App\Http\Controllers\Hijack;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Asin;

header('Access-Control-Allow-Origin:*');

class HijackController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    //上线 需打开 todo
    public function __construct()
    {
//        $this->middleware('auth');
//        parent::__construct();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index()
    {
        return view('hijack.index');
    }

    public function detail()
    {
        return view('hijack.detail');
    }

    /**
     * 首页接口请求
     * 目前使用
     * @return mixed
     */
    public function index1(Request $request)
    {
        $isOpen = @$request['isOpen'] >= 2 ? $request['isOpen'] : 1;
        $opensql = '';//是否查询今天开启跟卖的产品
        $SKU_STATUS_KV = Asin::SKU_STATUS_KV;
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $userasinL = $sapSellerIdList = [];
        $bool_admin = 0;//是否是管理员
        $user = Auth::user()->toArray(); //todo  打开
        if (!empty($user)) {
            if ((!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) || @$user['seller_rules'] == '*-*-*') {
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
                        if ($auv['sap_seller_id'] > 0) {
                            $sapSellerIdList[] = $auv['sap_seller_id'];
                        }
                    }
                }
            } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                //判断是否是销售 及 对应领导角色
                $allUsers = DB::table('users')->select('id', 'sap_seller_id', 'ubg', 'ubu')
                    ->where('ubg', $user['ubg'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**查询所有BG下面员工*/
                    if (!empty($allUsers)) {
                        foreach ($allUsers as $auk => $auv) {
                            $sapSellerIdList[] = $auv['sap_seller_id'];
                        }
                    }
                } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                    /**此条件为 普通销售*/
                    $sapSellerIdList[] = $user['sap_seller_id'];
                } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                    /**  bu 负责人 及所有下属 */
                    if (!empty($allUsers)) {
                        foreach ($allUsers as $auk => $auv) {
                            if ($auv['ubu'] == $user['ubu']) {
                                $sapSellerIdList[] = $auv['sap_seller_id'];
                            }
                        }
                    }
                } else {
                    $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                    return $err_message;
                }
            } else {
                $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                return $err_message;
            }
            if (!empty($sapSellerIdList)) {
                $user_asin_list = DB::connection('vlz')->table('sap_asin_match_sku')
                    ->select('asin', 'marketplace_id')
                    ->whereIn('sap_seller_id', $sapSellerIdList)
                    ->groupBy('asin')
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($user_asin_list)) {
                    foreach ($user_asin_list as $uslk => $uslv) {
                        if (strlen($uslv['asin']) > 8) {
                            $userasinL[] = $uslv['asin'];
                            $marketplaceid[] = $uslv['marketplace_id'];
                        }
                    }
                }
            } else if ($bool_admin == 0) {
                $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                return $err_message;
            }
        }
        //查询所有 asin 信息
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $DOMIN_MARKETPLACEID_RUL = Asin::DOMIN_MARKETPLACEID_URL;
        $ago_time = time() - 3600 * 3;//当前时间 前3小时 todo
        $sql_s = 'SELECT
            a.id,
            a.asin,
            a.images,
            a.marketplaceid,
            a.title,
            a.listed_at,
            a.mpn,
            a.seller_count,
            a.updated_at,
            a.reselling_switch,
            rl_asin.id AS rla_id,
            rl_task.id AS rlk_id,
            rl_task.reselling_num,
            rl_task.reselling_time,
            rl_task.created_at,
            rl_task.reselling_asin_id
            FROM(asins AS a LEFT JOIN tbl_reselling_asin AS rl_asin ON a.id = rl_asin.product_id)
            LEFT JOIN tbl_reselling_task AS rl_task ON rl_asin.id = rl_task.reselling_asin_id AND  rl_task.created_at >=' . $ago_time . '
            where a.title !="" ';
        //GROUP BY a.asin
        //默认1开启跟卖，2.全部; 3. 关闭跟卖
        if ($isOpen == 1) {
            $opensql = ' AND rl_task.created_at >=' . $ago_time;
        } else if ($isOpen == 3) {
            $opensql = ' AND a.reselling_switch=0 ';
        }
        $sql_g = $opensql . '  ORDER BY rl_task.reselling_time DESC, a.reselling_switch DESC ,rl_task.reselling_num DESC';
        /**  判断对应用户 以及对应管理人员 所有下属ID */
        if (!empty($userasinL)) {
            $sql_as = 'AND a.asin in ("' . implode($userasinL, '","') . '")';
            $sql_marketplaceid = ' AND a.marketplaceid in ("' . implode($marketplaceid, '","') . '")';
            $sql = $sql_s . $sql_as . $sql_g;
        } else {
            $sql = $sql_s . $sql_g;
        }
        $productList_obj = DB::connection('vlz')->select($sql);
        $productList = (json_decode(json_encode($productList_obj), true));
        $asinList = [];
        if (!empty($productList)) {
            foreach ($productList as $key => $value) {
                //这里过滤重复 原因是 上面sql 由于排序导致无法分组， L309
                if (!in_array($value['asin'], $asinList)) {
                    $asinList[] = $value['asin'];
                    $productList[$key]['domin_sx'] = $DOMIN_MARKETPLACEID_SX[$value['marketplaceid']];
                    $productList[$key]['toUrl'] = $DOMIN_MARKETPLACEID_RUL[$value['marketplaceid']];
                    $productList[$key]['reselling_time'] = $value['reselling_time'] ? date('Y/m/d H:i:s', $value['reselling_time']) : '';
                } else {
                    unset($productList[$key]);
                }
            }
        }
        //中间对应关系数据
        $sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
            ->select('marketplace_id', 'sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'id', 'status', 'updated_at', 'sku_status', 'sku')
            // ->whereIn('asin', $asinList)
            ->whereIn('sap_seller_id', $sapSellerIdList)
            ->groupBy('asin')
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (!empty($sap_asin_match_sku)) {
            foreach ($sap_asin_match_sku as $k => $v) {
                foreach ($productList as $pk => $pv) {
                    //&& $pv['marketplaceid'] == $v['marketplace_id']  //todo 不清楚是否需要
                    if ($pv['asin'] == $v['asin']) {
                        $productList[$pk]['sap_seller_id'] = $v['sap_seller_id'];
                        $productList[$pk]['BG'] = $v['sap_seller_bg'];
                        $productList[$pk]['BU'] = $v['sap_seller_bu'];
                        $productList[$pk]['sku'] = $v['sku'];
                        $productList[$pk]['sap_updated_at'] = $v['updated_at'];
                        $productList[$pk]['sku_status'] = $SKU_STATUS_KV[$v['sku_status']];
                    }
                }
            }
        }

        $userList = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')
            ->whereIn('sap_seller_id', $sapSellerIdList)
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        $new_productList = [];
        if (!empty($userList)) {
            foreach ($productList as $pk => $pv) {
                foreach ($userList as $ulk => $ulv) {
                    if (!empty($pv['sap_seller_id'])) {
                        if ($pv['sap_seller_id'] == $ulv['sap_seller_id']) {
                            $productList[$pk]['userName'] = $ulv['name'];
                            $productList[$pk]['email'] = $ulv['email'];
                        }
                    }
                }
            }

            foreach ($productList as $pk => $pv) {
                if (in_array($pv['sap_seller_id'], $sapSellerIdList)) {
                    $new_productList[] = $pv;
                }
            }
        }
        $returnDate['userList'] = $userList;
        $returnDate['productList'] = $new_productList;
        return $returnDate;
    }

    /**
     * 接受post 请求 返回  ======= 暂未用到
     * @param Request $request
     * @return string
     */
    public function asinSearch(Request $request)
    {
        $param = $_REQUEST['param'] ? $_REQUEST['param'] : '';
        if (!empty($param)) {
            header('Access-Control-Allow-Origin:*');
            //得到登录用户信息

            //查询用户列表
            $users = User::select('name', 'email')->where('locked', '=', '0')->get()->toArray();
//查询 like title or asin
            $productList = DB::connection('vlz')->table('asins')
                ->select('id', 'asin', 'images', 'marketplaceid', 'title', 'images', 'listed_at', 'mpn', 'seller_count', 'updated_at', 'reselling_switch')
                ->where('title', 'like', '%' . $param . '%')
                ->orwhere('asin', 'like', '%' . $param . '%')
                ->groupBy('asin')
                ->orderBy('updated_at', 'desc')
                ->get(['asin'])->map(function ($value) {
                    return (array)$value;
                })->toArray();
            $asinList = [];
            $asinIdList = [];
            if (!empty($productList)) {
                foreach ($productList as $key => $value) {
                    $asinList[$value['id']] = $value['asin'];
                    $asinIdList[] = $value['id'];
                }
            }

            //查询跟卖数据
            $resellingidList = [];
            if (!empty($asinIdList)) {
                $resellingList = DB::connection('vlz')->table('tbl_reselling_asin')
                    ->select('id', 'asin', 'product_id')
                    ->whereIn('product_id', array_unique($asinIdList))
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
            }
            if (!empty($resellingList)) {
                foreach ($resellingList as $rlk => $rlv) {
                    $resellingidList[] = $rlv['id'];
                }
                //查询对应的asin 下面 跟卖数量
                $taskList = DB::connection('vlz')->table('tbl_reselling_task')
                    ->select('id', 'reselling_num', 'reselling_time', 'created_at', 'reselling_asin_id')
                    ->whereIn('reselling_asin_id', array_unique($resellingidList))
                    ->groupBy('reselling_asin_id')
                    ->orderBy('reselling_time', 'desc')
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                foreach ($resellingList as $rltk => $rltv) {
                    foreach ($taskList as $tlk => $tlv) {
                        if ($rltv['id'] == $tlv['reselling_asin_id']) {
                            $resellingList[$rltk]['reselling_num'] = $tlv['reselling_num'];
                            $resellingList[$rltk]['reselling_time'] = $tlv['reselling_time'];
                        }
                    }
                }
            }


            //中间对应关系数据
            $sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
                ->select('marketplace_id', 'sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'id', 'status', 'updated_at', 'sku_status', 'sku')
                ->whereIn('asin', array_unique($asinList))
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            $sap_seller_id_list = [];
            if (!empty($sap_asin_match_sku)) {
                foreach ($sap_asin_match_sku as $k => $v) {
                    if (!in_array($v['sap_seller_id'], $sap_seller_id_list)) {
                        $sap_seller_id_list[$v['sap_seller_id']]['asin'] = $v['asin'];
                        $sap_seller_id_list[$v['sap_seller_id']]['BG'] = $v['sap_seller_bg'];
                        $sap_seller_id_list[$v['sap_seller_id']]['BU'] = $v['sap_seller_bu'];
                        $sap_seller_id_list[$v['sap_seller_id']]['sku'] = $v['sku'];
                        $sap_seller_id_list[$v['sap_seller_id']]['sap_updated_at'] = $v['updated_at'];
                        $sap_seller_id_list[$v['sap_seller_id']]['sku_status'] = $v['sku_status'];

                    }
                }

            }
            var_dump($productList);
            exit;
            $userList = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')->whereIn('sap_seller_id', array_keys($sap_seller_id_list))->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
            if (!empty($userList)) {
                foreach ($userList as $uk => $uv) {
                    foreach ($sap_seller_id_list as $sk => $sv) {
                        if ($uv['sap_seller_id'] == $sk) {
                            $userList[$uk]['asin'] = $sv['asin'];
                            $userList[$uk]['BG'] = $sv['BG'];
                            $userList[$uk]['BU'] = $sv['BU'];
                            $userList[$uk]['sku'] = $sv['sku'];
                            $userList[$uk]['sku_status'] = $sv['sku_status'];
                            $userList[$uk]['sap_updated_at'] = $sv['sap_updated_at'];
                        }
                    }

                }
            }
            //  $userList2 = User::whereIn('sap_seller_id', $sap_seller_id_list)->groupBy(['email'])->get()->toArray();
            foreach ($productList as $pk => $pv) {
                foreach ($userList as $ulk => $ulv) {
                    if ($pv['asin'] == $ulv['asin']) {
                        $productList[$pk]['userName'] = $ulv['name'];
                        $productList[$pk]['email'] = $ulv['email'];
                        $productList[$pk]['BG'] = $ulv['BG'];
                        $productList[$pk]['BU'] = $ulv['BU'];
                        $productList[$pk]['sku'] = $ulv['sku'];
                        $productList[$pk]['sku_status'] = $ulv['sku_status'];
                        $productList[$pk]['sap_updated_at'] = $ulv['sap_updated_at'];
                    } else {
                        $productList[$pk]['userName'] = '';
                        $productList[$pk]['email'] = '';
                        $productList[$pk]['BG'] = '';
                        $productList[$pk]['BU'] = '';
                        $productList[$pk]['sku'] = '';
                        $productList[$pk]['sku_status'] = '';
                        $productList[$pk]['sap_updated_at'] = '';
                    }
                }
                foreach ($resellingList as $resk => $resv) {
                    if ($pv['id'] == $resv['product_id']) {
                        $productList[$pk]['reselling_num'] = @$resv['reselling_num'];
                        $productList[$pk]['reselling_time'] = @$resv['reselling_time'];
                    }
                }
            }


            //==================== 查询 like sku ===================================//
            $asinList = [];
            //中间对应关系数据
            $sap_asin_match_sku2 = DB::connection('vlz')->table('sap_asin_match_sku')
                ->select('sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'id', 'status', 'updated_at', 'sku_status', 'sku')
                ->where('sku', 'like', '%' . $param . '%')
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            $sap_seller_id_list2 = [];
            if (!empty($sap_asin_match_sku2)) {
                foreach ($sap_asin_match_sku2 as $k => $v) {
                    $asinList[$v['id']] = $v['asin'];
                    if (!in_array($v['sap_seller_id'], $sap_seller_id_list2)) {
                        $sap_seller_id_list2[$v['sap_seller_id']]['asin'] = $v['asin'];
                        $sap_seller_id_list2[$v['sap_seller_id']]['BG'] = $v['sap_seller_bg'];
                        $sap_seller_id_list2[$v['sap_seller_id']]['BU'] = $v['sap_seller_bu'];
                        $sap_seller_id_list2[$v['sap_seller_id']]['sku'] = $v['sku'];
                        $sap_seller_id_list2[$v['sap_seller_id']]['sap_updated_at'] = $v['updated_at'];
                        $sap_seller_id_list2[$v['sap_seller_id']]['sku_status'] = $v['sku_status'];

                    }
                }
                $productList2 = DB::connection('vlz')->table('asins')
                    ->select('id', 'asin', 'images', 'marketplaceid', 'title', 'images', 'listed_at', 'mpn', 'seller_count', 'updated_at', 'reselling_switch')
                    ->whereNotNull('title')
                    ->whereIn('asin', array_unique($asinList))
                    ->groupBy('asin')
                    ->orderBy('updated_at', 'desc')
                    ->get(['asin'])->map(function ($value) {
                        return (array)$value;
                    })->toArray();

                if (!empty($productList2)) {
                    foreach ($productList2 as $key => $value) {
                        $asinList[$value['id']] = $value['asin'];
                        $asinIdList[] = $value['id'];
                    }
                }

                //查询跟卖数据
                $resellingidList = [];
                $resellingList = DB::connection('vlz')->table('tbl_reselling_asin')
                    ->select('id', 'asin', 'product_id')
                    ->whereIn('product_id', array_unique($asinIdList))
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($resellingList)) {
                    foreach ($resellingList as $rlk => $rlv) {
                        $resellingidList[] = $rlv['id'];
                    }
                    //查询对应的asin 下面 跟卖数量
                    $taskList = DB::connection('vlz')->table('tbl_reselling_task')
                        ->select('id', 'reselling_num', 'reselling_time', 'created_at', 'reselling_asin_id')
                        ->whereIn('reselling_asin_id', array_unique($resellingidList))
                        ->groupBy('reselling_asin_id')
                        ->orderBy('reselling_time', 'desc')
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                    foreach ($resellingList as $rltk => $rltv) {
                        foreach ($taskList as $tlk => $tlv) {
                            if ($rltv['id'] == $tlv['reselling_asin_id']) {
                                $resellingList[$rltk]['reselling_num'] = $tlv['reselling_num'];
                                $resellingList[$rltk]['reselling_time'] = $tlv['reselling_time'];
                            }
                        }
                    }
                }


                $userList2 = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')->whereIn('sap_seller_id', array_keys($sap_seller_id_list2))->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
                if (!empty($userList2)) {
                    foreach ($userList2 as $uk => $uv) {
                        foreach ($sap_seller_id_list2 as $sk => $sv) {
                            if ($uv['sap_seller_id'] == $sk) {
                                $userList2[$uk]['asin'] = $sv['asin'];
                                $userList2[$uk]['BG'] = $sv['BG'];
                                $userList2[$uk]['BU'] = $sv['BU'];
                                $userList2[$uk]['sku'] = $sv['sku'];
                                $userList2[$uk]['sku_status'] = $sv['sku_status'];
                                $userList2[$uk]['sap_updated_at'] = $sv['sap_updated_at'];
                            }
                        }

                    }
                }//  $userList2 = User::whereIn('sap_seller_id', $sap_seller_id_list2)->groupBy(['email'])->get()->toArray();
                foreach ($productList2 as $pk => $pv) {
                    foreach ($userList2 as $ulk => $ulv) {
                        if ($pv['asin'] == $ulv['asin']) {
                            $productList2[$pk]['userName'] = $ulv['name'];
                            $productList2[$pk]['email'] = $ulv['email'];
                            $productList2[$pk]['BG'] = $ulv['BG'];
                            $productList2[$pk]['BU'] = $ulv['BU'];
                            $productList2[$pk]['sku'] = $ulv['sku'];
                            $productList2[$pk]['sku_status'] = $ulv['sku_status'];
                            $productList2[$pk]['sap_updated_at'] = $ulv['sap_updated_at'];
                        } else {
                            $productList2[$pk]['userName'] = '';
                            $productList2[$pk]['email'] = '';
                            $productList2[$pk]['BG'] = '';
                            $productList2[$pk]['BU'] = '';
                            $productList2[$pk]['sku'] = '';
                            $productList2[$pk]['sku_status'] = '';
                            $productList2[$pk]['sap_updated_at'] = '';
                        }
                    }
                    foreach ($resellingList as $resk => $resv) {
                        if ($pv['id'] == $resv['product_id']) {
                            $productList2[$pk]['reselling_num'] = $resv['reselling_num'];
                            $productList2[$pk]['reselling_time'] = $resv['reselling_time'];
                        }
                    }
                }
            } else {
                $productList2 = [];
                $userList2 = [];
            }


            $returnDate['userList'] = array_merge($userList, $userList2);
            $returnDate['productList'] = array_merge($productList2, $productList);
        }

        return $returnDate;

    }

    /**
     * 修改 开启关闭
     * @param Request $request
     * @return string
     */
    public function updateAsinSta(Request $request)
    {
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        if (!empty($_POST['id'])) {
            $toup = 0;
            if (@$_POST['reselling_switch'] == 1) {
                $toup = 1;
            }
            $arr_id = explode(',', $_POST['id']);
            $result = DB::connection('vlz')->table('asins')
                ->whereIn('id', $arr_id)
                ->update(['reselling_switch' => $toup]);
            $asinOne = DB::connection('vlz')->table('asins')
                ->select('id', 'asin', 'marketplaceid', 'listed_at')
                ->whereIn('id', $arr_id)
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();

            if ($result > 0) {
                if ($toup == 1) {
                    //防止添加重复数据，所以先删除后增加
                    DB::connection('vlz')->table('tbl_reselling_asin')->whereIn('product_id', $arr_id)->delete();//删除1条
                    foreach ($asinOne as $k => $v) {
                        $data = [
                            'product_id' => $v['id'],
                            'domain' => $DOMIN_MARKETPLACEID_URL[$v['marketplaceid']],
                            'asin' => $v['asin'],
                            'reselling' => 1
                        ];
                        //新增tbl_reselling_asin
                        DB::connection('vlz')->table('tbl_reselling_asin')->insert($data);
                    }
                } else {
                    //防止添加重复数据，所以先删除后增加
                    DB::connection('vlz')->table('tbl_reselling_asin')->whereIn('product_id', $arr_id)->delete();//删除1条
                }
                $r_message = ['status' => 1, 'msg' => '更新成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '更新失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少参数'];
        }
        return $r_message;
        exit;
    }

    /**
     * @param Request $request
     * 批量更新跟卖数据
     */
    public function updateAsinStaAll(Request $request)
    {
        $asinsIdList = [];
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        $asinsList = DB::connection('vlz')->table('asins')
            ->select('id')
            ->where('reselling_switch', 0)
            ->limit(300)
            ->get()
            ->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (!empty($asinsList)) {
            foreach ($asinsList as $key => $value) {
                $asinsIdList[] = $value['id'];
            }
        }

        $toup = 1;  //TODO 0
        $arr_id = array_unique($asinsIdList);
        $result = DB::connection('vlz')->table('asins')
            ->whereIn('id', $arr_id)
            ->update(['reselling_switch' => $toup]);

        $asinOne = DB::connection('vlz')->table('asins')
            ->select('id', 'asin', 'marketplaceid', 'listed_at')
            ->whereIn('id', $arr_id)
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();

        if ($result > 0) {
            echo '更新成功';
            if ($toup == 1) {
                //防止添加重复数据，所以先删除后增加
                DB::connection('vlz')->table('tbl_reselling_asin')->whereIn('product_id', $arr_id)->delete();//删除1条
                foreach ($asinOne as $k => $v) {
                    $data = [
                        'product_id' => $v['id'],
                        'domain' => $DOMIN_MARKETPLACEID_URL[$v['marketplaceid']],
                        'asin' => $v['asin'],
                        'reselling' => 1
                    ];
                    //新增tbl_reselling_asin
                    DB::connection('vlz')->table('tbl_reselling_asin')->insert($data);
                }
            } else {
                //防止添加重复数据，所以先删除后增加
                DB::connection('vlz')->table('tbl_reselling_asin')->whereIn('product_id', $arr_id)->delete();//删除1条
            }

        } else {
            echo '更新失败';
        }
        exit;
    }

    /**
     * 数据导出
     * @param Request $request
     */
    public function hijackExport(Request $request)
    {
        header('Access-Control-Allow-Origin:*');
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        $idList = isset($request['idList']) ? $request['idList'] : '';
        //是否打开跟卖
        $isOpen = @$request['isOpen'] >= 2 ? $request['isOpen'] : 1;
        $bg = isset($request['bg']) ? $request['bg'] : '';
        $bu = isset($request['bu']) ? $request['bu'] : '';
        $sku_status = isset($request['sku_status']) ? $request['sku_status'] : '';
        $domain = isset($request['domain']) ? $request['domain'] : '';
        $userName = isset($request['userName']) ? $request['userName'] : '';
        $condition = isset($request['condition']) ? $request['condition'] : '';//查询条件
        //传入下载的 asinID
        if (!empty($request['startTime'] && !empty($request['endTime']))) {
            //查询跟卖数据 根据开始时间 结束时间默认增加1天
            $startTime = $request['startTime'];
            $endTime = $request['endTime'] + 3600 * 24;
            $resellingidList = [];
            if (!empty($idList) && isset($idList) && $idList != '-1') {
                /** 存在下载的IDS*/
                $idList = explode(',', $idList);
                $productList = DB::connection('vlz')->table('asins')
                    ->select('id', 'asin', 'marketplaceid', 'title', 'listed_at', 'seller_count', 'reselling_switch')
                    ->whereIn('id', $idList)
                    ->get(['asin'])->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                $asinList = [];
                if (!empty($productList)) {
                    foreach ($productList as $key => $value) {
                        $asinList[$value['id']] = $value['asin'];
                    }
                }
                //中间对应关系数据
                $sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
                    ->select('sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'marketplace_id', 'status', 'updated_at', 'sku_status', 'sku')
                    ->whereIn('asin', array_unique($asinList))
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
            } else {
                /** 不存在存在下载的IDS ，需要根据权限查询关联*/
                $productIdList = [];
                $userasinL = [];
                $marketplaceid = [];
                $sapSellerIdList = [];

                $user = Auth::user()->toArray(); //todo  打开
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

                    } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                        if ((!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) || @$user['seller_rules'] == '*-*-*') {
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
                                    if ($auv['sap_seller_id'] > 0) {
                                        $sapSellerIdList[] = $auv['sap_seller_id'];
                                    }
                                }
                            }
                        } else {
                            //判断是否是销售 及 对应领导角色
                            $allUsers = DB::table('users')->select('id', 'sap_seller_id', 'ubg', 'ubu')
                                ->where('ubg', $user['ubg'])
                                ->get()->map(function ($value) {
                                    return (array)$value;
                                })->toArray();
                            if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                                /**查询所有BG下面员工*/
                                if (!empty($allUsers)) {
                                    foreach ($allUsers as $auk => $auv) {
                                        $sapSellerIdList[] = $auv['sap_seller_id'];
                                    }
                                }
                            } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                                /**此条件为 普通销售*/
                                $sapSellerIdList[] = $user['sap_seller_id'];
                            } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                                /**  bu 负责人 及所有下属 */
                                if (!empty($allUsers)) {
                                    foreach ($allUsers as $auk => $auv) {
                                        if ($auv['ubu'] == $user['ubu']) {
                                            $sapSellerIdList[] = $auv['sap_seller_id'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($sapSellerIdList)) {
                        $user_asin_list = DB::connection('vlz')->table('sap_asin_match_sku')
                            ->select('asin', 'marketplace_id')
                            ->whereIn('sap_seller_id', $sapSellerIdList)
                            ->groupBy('asin')
                            ->get()->map(function ($value) {
                                return (array)$value;
                            })->toArray();
                        if (!empty($user_asin_list)) {
                            foreach ($user_asin_list as $uslk => $uslv) {
                                if (strlen($uslv['asin']) > 8) {
                                    $userasinL[] = $uslv['asin'];
                                    $marketplaceid[] = $uslv['marketplace_id'];
                                }
                            }
                        }
                    }
                }
                //查询所有 asin 信息
                $sql_s = 'SELECT `id`,`asin`,`reselling_switch`,`marketplaceid`,`title` FROM asins where title !="" ';
                //默认1开启跟卖，2.全部; 3. 关闭跟卖
                if ($isOpen == 1) {
                    $sql_s .= ' AND reselling_switch=1 ';
                } else if ($isOpen == 3) {
                    $sql_s .= ' AND reselling_switch=0 ';
                }

                $sql_g = ' GROUP BY asin ORDER BY reselling_switch DESC ';
                /**  判断对应用户 以及对应管理人员 所有下属ID */
                if (!empty($userasinL)) {
                    $sql_as = 'AND asin in ("' . implode($userasinL, '","') . '")';
                    $sql_marketplaceid = ' AND  marketplaceid in ("' . implode($marketplaceid, '","') . '")';
                    $sql = $sql_s . $sql_as . $sql_g;
                } else {
                    $sql = $sql_s . $sql_g;
                }
                $productList_obj = DB::connection('vlz')->select($sql);
                $productList = (json_decode(json_encode($productList_obj), true));
                $asinList = [];
                if (!empty($productList)) {
                    foreach ($productList as $key => $value) {
                        $asinList[] = $value['asin'];
                        $productIdList[] = $value['id'];
                    }
                }
                if (!empty($productIdList)) {
                    $idList = array_unique($productIdList);
                }
                //  SAP 中间对应关系数据
                if (!empty($condition)) {
                    $sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
                        ->select('marketplace_id', 'sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'id', 'status', 'updated_at', 'sku_status', 'sku')
                        ->whereIn('sap_seller_id', $sapSellerIdList)
                        ->where('sku', 'like', '%' . $condition . '%')
                        ->orwhere('asin', 'like', '%' . $condition . '%')
                        ->groupBy('asin')
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                } else {
                    $sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
                        ->select('marketplace_id', 'sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'id', 'status', 'updated_at', 'sku_status', 'sku')
                        ->whereIn('sap_seller_id', $sapSellerIdList)
                        ->groupBy('asin')
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                }
            }
        }

        $sap_seller_id_list = [];
        if (!empty($sap_asin_match_sku)) {
            foreach ($sap_asin_match_sku as $k => $v) {
                $sap_seller_id_list[] = $v['sap_seller_id'];
                foreach ($productList as $pk => $pv) {
                    if ($pv['asin'] == $v['asin'] && $pv['marketplaceid'] == $v['marketplace_id']) {
                        $productList[$pk]['sap_seller_id'] = $v['sap_seller_id'];
                        $productList[$pk]['BG'] = $v['sap_seller_bg'];
                        $productList[$pk]['BU'] = $v['sap_seller_bu'];
                        $productList[$pk]['sku'] = $v['sku'];
                        $productList[$pk]['sap_updated_at'] = $v['updated_at'];
                        $productList[$pk]['sku_status'] = $v['sku_status'];
                    }
                }
            }
        }
        $userList = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')
            ->whereIn('sap_seller_id', $sap_seller_id_list)->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        if (!empty($userList)) {
            foreach ($productList as $pk => $pv) {
                foreach ($userList as $ulk => $ulv) {
                    if (!empty($pv['sap_seller_id'])) {
                        if ($pv['sap_seller_id'] == $ulv['sap_seller_id']) {
                            $productList[$pk]['userName'] = $ulv['name'];
                            $productList[$pk]['email'] = $ulv['email'];
                        }
                    }
                }
            }
        }

        $resellingList = DB::connection('vlz')->table('tbl_reselling_asin')
            ->select('id', 'asin', 'product_id')
            ->whereIn('product_id', array_unique($idList))
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();
        foreach ($resellingList as $rlk => $rlv) {
            $resellingidList[] = $rlv['id'];
        }
        //查询对应的asin 下面 跟卖数量
        $taskList = DB::connection('vlz')->table('tbl_reselling_task')
            ->select('id', 'reselling_num', 'reselling_time', 'created_at', 'reselling_asin_id')
            ->where('reselling_time', '>=', $startTime)
            ->where('reselling_time', '<=', $endTime)
            ->whereIn('reselling_asin_id', $resellingidList)
            ->orderBy('reselling_time', 'desc')
            ->get()->map(function ($value) {
                return (array)$value;
            })->toArray();

        /** 查询跟卖信息 */
        if (!empty($taskList)) {
            foreach ($taskList as $tlK => $tlv) {
                $taskIdList[] = $tlv['id'];
            }
            /** 查询detail **/
            $taskDetail = DB::connection('vlz')->table('tbl_reselling_detail')
                ->select('id', 'task_id', 'price', 'shipping_fee', 'account', 'white', 'sellerid', 'created_at', 'reselling_remark')
                ->whereIn('task_id', array_unique($taskIdList))
                ->where('white', 0)//增加白名单
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($taskDetail)) {
                foreach ($taskDetail as $tlk => $tlv) {
                    $taskDetail[$tlk]['count'] = 0;
                    $created_at = 0;
                    $reselling_count = 0;
                    foreach ($taskDetail as $tk => $tv) {
                        if ($tlv['sellerid'] == $tv['sellerid']) {
                            if ($tv['created_at'] - $created_at > 3600 && $reselling_count == 0) {
                            } elseif ($tv['created_at'] - $created_at > 3600) {
                            } elseif ($tv['created_at'] - $created_at < 3600) {
                                $reselling_count++;
                            }
                            $created_at = $tv['created_at'];
                        }
                    }
                    $taskDetail[$tlk]['count'] = $reselling_count + 1;
                    foreach ($taskList as $taK => $tav) {
                        if ($tlv['task_id'] == $tav['id']) {
                            $taskDetail[$tlk]['reselling_asin_id'] = $tav['reselling_asin_id'];
                        }
                    }
                }
                foreach ($taskDetail as $tdkey => $tdval) {
                    foreach ($resellingList as $rlk => $rlv) {
                        if ($tdval['reselling_asin_id'] == $rlv['id']) {
                            $taskDetail[$tdkey]['product_id'] = $rlv['product_id'];
                        }
                    }
                }
                foreach ($taskDetail as $tdkey => $tdval) {
                    foreach ($productList as $pkey => $pval) {
                        if ($tdval['product_id'] == $pval['id']) {
                            $taskDetail[$tdkey]['title'] = $pval['title'];
                            $taskDetail[$tdkey]['asin'] = $pval['asin'];
                            $taskDetail[$tdkey]['sku'] = $pval['sku'];
                            $taskDetail[$tdkey]['userName'] = $pval['userName'];
                            $taskDetail[$tdkey]['marketplaceid'] = $DOMIN_MARKETPLACEID_URL[$pval['marketplaceid']];

                        }
                    }
                }
                echo
                    'ASIN,' .
                    'Marketplace,' .
                    'SKU,' .
                    'Seller,' .
                    'Notes,' .
                    'Title,' .
                    'Seller ID,' .
                    'Seller Name,' .
                    'Price,' .
                    'Shipping,' .
                    'Date,' .
                    'Duration(h)' . "\r\n" . "\r\n";

                if (!empty($taskDetail)) {
                    foreach ($taskDetail as $key => $dv) {
                        if ((!empty($bg) && $bg != $dv['bg']) || (!empty($bu) && $bu != $dv['bu']) || (!empty($userName) && $userName != $dv['userName']) || (!empty($sku_status) && $sku_status != $dv['sku_status']) || (!empty($domain) && $domain != $dv['marketplaceid'])) {

                        } else {
                            $title='';
                            if(!empty($dv['title'])){
                                $title= str_replace(",","，",@$dv['title']);
                            }

                            if (!empty($dv['asin'])) {
                                $price = @$dv['price'] > 0 ? $dv['price'] / 100 : 0;
                                $shipping_fee = @$dv['shipping_fee'] > 0 ? $dv['shipping_fee'] / 100 : 0;
                                echo '"' . @$dv['asin'] . '",' .
                                    '"' . @$dv['marketplaceid'] . '",' .
                                    '"' . @$dv['sku'] . '",' .
                                    '"' . @$dv['userName'] . '",' .
                                    '"' . @$dv['reselling_remark'] . '",' .
                                    '"' . $title . '",' .
                                    '"' . @$dv['sellerid'] . '",' .
                                    '"' . @$dv['account'] . '",' .
                                    '"' . $price . '",' .
                                    '"' . $shipping_fee . '",' .
                                    '"' . date('Y-m-d H:i', @$dv['created_at']) . '",' .
                                    '"' . @$dv['count'] . '"' .
                                    "\r\n";
                            }
                        }

                    }
                }

            }
            /** detail * end*/
        }
        /**  查询根脉信息  END  **/
        exit;
    }

    /**
     * 数据导出
     * 根据筛选条件 导出
     * @copyright 2020/05/18
     * @param Request $request
     */
    public function hijackExport_0518(Request $request)
    {
        header('Access-Control-Allow-Origin:*');
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        //选中的 产品id
        $idList = isset($request['idList']) ? $request['idList'] : '';
        //是否打开跟卖
        $isOpen = @$request['isOpen'] >= 2 ? $request['isOpen'] : 1;
        $bg = isset($request['bg']) ? $request['bg'] : '';
        $bu = isset($request['bu']) ? $request['bu'] : '';
        $sku_status = isset($request['sku_status']) ? $request['sku_status'] : '';
        $domain = isset($request['domain']) ? $request['domain'] : '';
        $condition = isset($request['condition']) ? $request['condition'] : '';//查询条件
        //传入下载的 asinID
        if (!empty($request['startTime'] && !empty($request['endTime']))) {
            $startTime = $request['startTime'];
            $endTime = $request['endTime'] + 3600 * 24;
            $opensql = '';//是否查询今天开启跟卖的产品
            $SKU_STATUS_KV = Asin::SKU_STATUS_KV;
            /** 超级权限*/
            $userasinL = $sapSellerIdList = $resellingidList = [];
            $bool_admin = 0;//是否是管理员
            // $user = Auth::user()->toArray(); //todo  打开
            $user = ['email' => 'test@qq.com', 'ubg' => 'BG1', 'ubu' => 'BU2', 'seller_rules' => 'BG1-BU2-*'];
            if (!empty($user)) {
                if ((!empty($user['email']) && in_array($user['email'], $ADMIN_EMAIL)) || @$user['seller_rules'] == '*-*-*') {
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
                            if ($auv['sap_seller_id'] > 0) {
                                $sapSellerIdList[] = $auv['sap_seller_id'];
                            }
                        }
                    }
                } else if ($user['ubu'] != '' || $user['ubg'] != '' || $user['seller_rules'] != '') {
                    //判断是否是销售 及 对应领导角色
                    $allUsers = DB::table('users')->select('id', 'sap_seller_id', 'ubg', 'ubu')
                        ->where('ubg', $user['ubg'])
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                    if ($user['ubu'] == '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                        /**查询所有BG下面员工*/
                        if (!empty($allUsers)) {
                            foreach ($allUsers as $auk => $auv) {
                                $sapSellerIdList[] = $auv['sap_seller_id'];
                            }
                        }
                    } else if ($user['ubu'] != '' && $user['seller_rules'] == '') {
                        /**此条件为 普通销售*/
                        $sapSellerIdList[] = $user['sap_seller_id'];
                    } else if ($user['ubu'] != '' && $user['ubg'] != '' && $user['seller_rules'] != '') {
                        /**  bu 负责人 及所有下属 */
                        if (!empty($allUsers)) {
                            foreach ($allUsers as $auk => $auv) {
                                if ($auv['ubu'] == $user['ubu']) {
                                    $sapSellerIdList[] = $auv['sap_seller_id'];
                                }
                            }
                        }
                    } else {
                        $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                        return $err_message;
                    }
                } else {
                    $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                    return $err_message;
                }
                if (!empty($sapSellerIdList)) {
                    $user_asin_list = DB::connection('vlz')->table('sap_asin_match_sku')
                        ->select('asin', 'marketplace_id')
                        ->whereIn('sap_seller_id', $sapSellerIdList)
                        ->groupBy('asin')
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                    if (!empty($user_asin_list)) {
                        foreach ($user_asin_list as $uslk => $uslv) {
                            if (strlen($uslv['asin']) > 8) {
                                $userasinL[] = $uslv['asin'];
                                $marketplaceid[] = $uslv['marketplace_id'];
                            }
                        }
                    }
                } else if ($bool_admin == 0) {
                    $err_message = ['status' => '-1', 'message' => 'No matching records found'];
                    return $err_message;
                }
            }
            //查询所有 asin 信息
            $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
            $ago_time = time() - 3600 * 3;//当前时间 前3小时 todo
            $sql_s = 'SELECT
            a.id,
            a.asin,
            a.images,
            a.marketplaceid,
            a.title,
            a.listed_at,
            a.mpn,
            a.seller_count,
            a.updated_at,
            a.reselling_switch,
            rl_asin.id AS rla_id,
            rl_task.id AS rlk_id,
            rl_task.reselling_num,
            rl_task.reselling_time,
            rl_task.created_at,
            rl_task.reselling_asin_id
            FROM(asins AS a LEFT JOIN tbl_reselling_asin AS rl_asin ON a.id = rl_asin.product_id)
            LEFT JOIN tbl_reselling_task AS rl_task ON rl_asin.id = rl_task.reselling_asin_id AND  rl_task.created_at >=' . $ago_time . '
            where a.title !="" ';
            // AND rl_task.reselling_time >='.$startTime. ' AND rl_task.reselling_time <= '.$endTime
            //GROUP BY a.asin
            if (!empty($idList)) {
                $sql_s = $sql_s . ' AND a.id in (' . $idList . ')';
            }
            if (!empty($domain)) {
                $domain_mid = array_search($domain, $DOMIN_MARKETPLACEID_URL);
                $sql_s = $sql_s . ' AND  a.marketplaceid = "' . $domain_mid . '"';
            }
            //默认1开启跟卖，2.全部; 3. 关闭跟卖
            if ($isOpen == 1) {
                $opensql = ' AND rl_task.created_at >=' . $ago_time;
            } else if ($isOpen == 3) {
                $opensql = ' AND a.reselling_switch=0 ';
            }
            $sql_g = $opensql . '  ORDER BY rl_task.reselling_time DESC, a.reselling_switch DESC ,rl_task.reselling_num DESC';
            /**  判断对应用户 以及对应管理人员 所有下属ID */
            if (!empty($userasinL)) {
                $sql_as = ' AND a.asin in ("' . implode($userasinL, '","') . '")';
                $sql_marketplaceid = ' AND a.marketplaceid in ("' . implode($marketplaceid, '","') . '")';
                $sql = $sql_s . $sql_as . $sql_g;
            } else {
                $sql = $sql_s . $sql_g;
            }
            $productList_obj = DB::connection('vlz')->select($sql);
            $productList = (json_decode(json_encode($productList_obj), true));
            $asinList = [];
            if (!empty($productList)) {
                foreach ($productList as $key => $value) {
                    //这里过滤重复 原因是 上面sql 由于排序导致无法分组， L309
                    if (!in_array($value['asin'], $asinList)) {
                        $asinList[] = $value['asin'];
                        $taskIdList[] = $value['rlk_id'];
                        $productList[$key]['domin_sx'] = $DOMIN_MARKETPLACEID_SX[$value['marketplaceid']];
                        $productList[$key]['toUrl'] = $DOMIN_MARKETPLACEID_URL[$value['marketplaceid']];
                        $productList[$key]['reselling_time'] = $value['reselling_time'] ? date('Y/m/d H:i:s', $value['reselling_time']) : '';
                    } else {
                        unset($productList[$key]);
                    }
                }
            }
            //中间对应关系数据
            $sap_sql = 'SELECT
                        `marketplace_id`,
                        `sap_seller_id`,
                        `asin`,
                        `sap_seller_bg`,
                        `sap_seller_bu`,
                        `id`,
                        `status`,
                        `updated_at`,
                        `sku_status`,
                        `sku`
                    FROM
                        `sap_asin_match_sku`
                    WHERE
                        `sap_seller_id` IN (' . implode($sapSellerIdList, ",") . ')';
            if (!empty($bg)) {
                $sap_sql .= ' AND sap_seller_bg = "' . $bg . '"';
            }
            if (!empty($bu)) {
                $sap_sql .= ' AND sap_seller_bu = "' . $bu . '"';
            }
            if (!empty($condition)) {
                $sap_sql .= ' AND (asin like "%' . $condition . '%" or sku LIKE "' . $condition . '")';
            }
            if (!empty($sku_status)) {
                $sku_status_k = array_search($sku_status, $SKU_STATUS_KV);
                $sap_sql .= ' AND sku_status = ' . $sku_status_k;
            }
            $sap_sql = $sap_sql . ' GROUP BY  `asin`';
            $sap_asin_match_sku = DB::connection('vlz')->select($sap_sql);
            $sap_asin_match_sku = (json_decode(json_encode($sap_asin_match_sku), true));
            if (!empty($sap_asin_match_sku)) {
                foreach ($sap_asin_match_sku as $k => $v) {
                    foreach ($productList as $pk => $pv) {
                        //&& $pv['marketplaceid'] == $v['marketplace_id']  //todo 不清楚是否需要
                        if ($pv['asin'] == $v['asin']) {
                            $productList[$pk]['sap_seller_id'] = $v['sap_seller_id'];
                            $productList[$pk]['BG'] = $v['sap_seller_bg'];
                            $productList[$pk]['BU'] = $v['sap_seller_bu'];
                            $productList[$pk]['sku'] = $v['sku'];
                            $productList[$pk]['sap_updated_at'] = $v['updated_at'];
                            $productList[$pk]['sku_status'] = $SKU_STATUS_KV[$v['sku_status']];
                        }
                    }
                }
            }
            //根据用户名查询
            if (!empty($request['userName'])) {
                $userList = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')
                    ->whereIn('sap_seller_id', $sapSellerIdList)
                    ->where('name', $request['userName'])
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
            } else {
                $userList = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')
                    ->whereIn('sap_seller_id', $sapSellerIdList)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
            }
            //根据用户姓名查询
            $new_productList = [];
            if (!empty($userList)) {
                foreach ($productList as $pk => $pv) {
                    foreach ($userList as $ulk => $ulv) {
                        if (!empty($pv['sap_seller_id'])) {
                            if ($pv['sap_seller_id'] == $ulv['sap_seller_id']) {
                                $productList[$pk]['userName'] = $ulv['name'];
                                $productList[$pk]['email'] = $ulv['email'];
                            }
                        }
                    }
                }
                $productIdList = [];
                foreach ($productList as $pk => $pv) {
                    if (!empty($pv['sap_seller_id'])) {
                        if (in_array($pv['sap_seller_id'], $sapSellerIdList)) {
                            $new_productList[] = $pv;
                            $productIdList[] = $pv['id'];
                        }
                    }
                }
            }
            $returnDate['userList'] = $userList;
            $returnDate['productList'] = $new_productList;

            $resellingList = DB::connection('vlz')->table('tbl_reselling_asin')
                ->select('id', 'asin', 'product_id')
                ->whereIn('product_id', array_unique($productIdList))
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            foreach ($resellingList as $rlk => $rlv) {
                $resellingidList[] = $rlv['id'];
            }
            if (!empty($resellingidList)) {
                $taskList = DB::connection('vlz')->table('tbl_reselling_task')
                    ->select('id', 'reselling_num', 'reselling_time', 'created_at', 'reselling_asin_id')
                    ->whereIn('reselling_asin_id', $resellingidList)
                    ->orderBy('reselling_time', 'desc')
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                /** 查询detail **/
                $taskDetail = DB::connection('vlz')->table('tbl_reselling_detail')
                    ->select('id', 'task_id', 'price', 'shipping_fee', 'account', 'white', 'sellerid', 'created_at', 'reselling_remark')
                    ->whereIn('task_id', array_unique($taskIdList))
                    ->where('white', 0)//增加白名单
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
            }
            echo
                'ASIN,' .
                'Marketplace,' .
                'SKU,' .
                'Seller,' .
                'Notes,' .
                'Title,' .
                'Seller ID,' .
                'Seller Name,' .
                'Price,' .
                'Shipping,' .
                'Date,' .
                'Duration(h)' . "\r\n" . "\r\n";
            if (!empty($taskDetail)) {
                foreach ($taskDetail as $tlk => $tlv) {
                    $taskDetail[$tlk]['count'] = 0;
                    $created_at = 0;
                    $reselling_count = 0;
                    foreach ($taskDetail as $tk => $tv) {
                        if ($tlv['sellerid'] == $tv['sellerid']) {
                            if ($tv['created_at'] - $created_at > 3600 && $reselling_count == 0) {
                            } elseif ($tv['created_at'] - $created_at > 3600) {
                            } elseif ($tv['created_at'] - $created_at < 3600) {
                                $reselling_count++;
                            }
                            $created_at = $tv['created_at'];
                        }
                    }
                    $taskDetail[$tlk]['count'] = $reselling_count + 1;
                    foreach ($taskList as $taK => $tav) {
                        if ($tlv['task_id'] == $tav['id']) {
                            $taskDetail[$tlk]['reselling_asin_id'] = $tav['reselling_asin_id'];
                        }
                    }
                }
                foreach ($taskDetail as $tdkey => $tdval) {
                    foreach ($resellingList as $rlk => $rlv) {
                        if ($tdval['reselling_asin_id'] == $rlv['id']) {
                            $taskDetail[$tdkey]['product_id'] = $rlv['product_id'];
                        }
                    }
                }
                foreach ($taskDetail as $tdkey => $tdval) {
                    foreach ($new_productList as $pkey => $pval) {
                        if ($tdval['product_id'] == $pval['id']) {
                            $taskDetail[$tdkey]['title'] = $pval['title'];
                            $taskDetail[$tdkey]['asin'] = $pval['asin'];
                            $taskDetail[$tdkey]['sku'] = $pval['sku'];
                            $taskDetail[$tdkey]['userName'] = $pval['userName'];
                            $taskDetail[$tdkey]['marketplaceid'] = $DOMIN_MARKETPLACEID_URL[$pval['marketplaceid']];

                        }
                    }
                }


                if (!empty($taskDetail)) {
                    foreach ($taskDetail as $key => $dv) {
                        if (!empty($dv['asin'])) {
                            $price = @$dv['price'] > 0 ? $dv['price'] / 100 : 0;
                            $shipping_fee = @$dv['shipping_fee'] > 0 ? $dv['shipping_fee'] / 100 : 0;
                            echo '"' . @$dv['asin'] . '",' .
                                '"' . @$dv['marketplaceid'] . '",' .
                                '"' . @$dv['sku'] . '",' .
                                '"' . @$dv['userName'] . '",' .
                                '"' . @$dv['reselling_remark'] . '",' .
                                '"' . @$dv['title'] . '",' .
                                '"' . @$dv['sellerid'] . '",' .
                                '"' . @$dv['account'] . '",' .
                                '"' . $price . '",' .
                                '"' . $shipping_fee . '",' .
                                '"' . date('Y-m-d H:i', @$dv['created_at']) . '",' .
                                '"' . @$dv['count'] . '"' .
                                "\r\n";
                        }
                    }
                }

            }
        }
        exit;
    }

    /**
     * 左侧跟卖列表 顶部产品信息
     */
    public function resellingList(Request $request)
    {
        $SKU_STATUS_KV = Asin::SKU_STATUS_KV;
        $DOMIN_MARKETPLACEID = Asin::DOMIN_MARKETPLACEID;
        $DOMIN_MARKETPLACEID_SX = Asin::DOMIN_MARKETPLACEID_SX;
        $DOMIN_MARKETPLACEID_URL = Asin::DOMIN_MARKETPLACEID_URL;
        //查询跟卖数据 根据开始时间 结束时间
        $startTime = isset($request['startTime']) ? $request['startTime'] : 0;
        $endTime = isset($request['endTime']) ? $request['endTime'] + 3600 * 24 : 0;
        //根据ID 查询asins 信息
        if ($request['id']) {
            $asins = DB::connection('vlz')->table('asins')
                ->select('id', 'asin', 'images', 'marketplaceid', 'title', 'images', 'listed_at', 'mpn', 'updated_at')
                ->where('id', $request['id'])
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            $taskList = [];
            if (!empty($asins)) {
                $as = $asins[0];
                $marketplaceid = $asins[0]['marketplaceid'];
                $sku = '';
                $sku_status = '';
                $domainUrl = $DOMIN_MARKETPLACEID[isset($as['marketplaceid']) ? $as['marketplaceid'] : ''];
                //中间对应关系数据
                $sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
                    ->select('sap_seller_id', 'updated_at', 'sku_status', 'sku')
                    ->where('asin', $as['asin'])
                    ->where('marketplace_id', $marketplaceid)
                    ->groupBy('asin')
                    ->first();
                if (!empty($sap_asin_match_sku)) {
                    $sku = $sap_asin_match_sku->sku;
                    $sku_status = $sap_asin_match_sku->sku_status;
                }

                if (!empty($domainUrl) && !empty($as['asin'])) {
                    $asins[0]['domin_sx'] = $DOMIN_MARKETPLACEID_SX[isset($asins[0]['marketplaceid']) ? $asins[0]['marketplaceid'] : ''];
                    $asins[0]['domin_url'] = $DOMIN_MARKETPLACEID_URL[isset($asins[0]['marketplaceid']) ? $asins[0]['marketplaceid'] : ''];
                    $resellingList = DB::connection('vlz')->table('tbl_reselling_asin')
                        ->select('reselling_num', 'updated_at', 'created_at', 'reselling_remark', 'id', 'asin')
                        ->where('asin', $as['asin'])
                        ->where('product_id', $as['id'])
                        ->get()->map(function ($value) {
                            return (array)$value;
                        })->toArray();
                    if (!empty($resellingList)) {
                        $reselling_asin_id_l = [];
                        foreach ($resellingList as $rlkey => $rlvalue) {
                            $reselling_asin_id_l[] = $rlvalue['id'];
                        }
                        if (!empty($reselling_asin_id_l)) {
                            //查询跟卖数据
                            //时间范围 筛选条件
                            if ($startTime > 0 && $endTime > 0) {
                                $taskList = DB::connection('vlz')->table('tbl_reselling_task')
                                    ->select('id', 'reselling_num', 'reselling_time', 'created_at')
                                    ->whereIn('reselling_asin_id', array_unique($reselling_asin_id_l))
                                    ->where('reselling_time', '>=', $startTime)
                                    ->where('reselling_time', '<=', $endTime)
                                    ->orderBy('reselling_time', 'desc')
                                    ->limit(720)
                                    ->get()->map(function ($value) {
                                        return (array)$value;
                                    })->toArray();

                            } else {
                                //没有时间范围 正常查询
                                $taskList = DB::connection('vlz')->table('tbl_reselling_task')
                                    ->select('id', 'reselling_num', 'reselling_time', 'created_at')
                                    ->whereIn('reselling_asin_id', array_unique($reselling_asin_id_l))
                                    ->orderBy('reselling_time', 'desc')
                                    ->limit(720)
                                    ->get()->map(function ($value) {
                                        return (array)$value;
                                    })->toArray();
                            }
                            if (!empty($taskList)) {

                                $asins[0]['asin_reselling_num'] = $taskList[0]['reselling_num'];
                                $asins[0]['asin_reselling_time'] = date('Y/m/d H:i:s', $taskList[0]['reselling_time']);
                                $asins[0]['sku'] = $sku;
                                $asins[0]['sku_status'] = $SKU_STATUS_KV[$sku_status];
                                $asins[0]['user_name'] = $request['name'] ? $request['name'] : '';

                                foreach ($taskList as $tk => $tv) {
                                    $taskList[$tk]['reselling_time'] = date('Y/m/d H:i:s', $tv['reselling_time']);
                                }
                            }
                        }
                    }
                }

            }
            return [$asins, $taskList];
        }

    }

    /**
     * @param Request $request
     * @return array
     * 查询 detail详情
     */
    public function resellingDetail(Request $request)
    {
        $taskId = $request['taskId'];
        $taskDetail = $reselling_count_list = $taskIdList = [];
        $reselling_asin_id = 0;
        if ($taskId > 0) {
            $tasktaskOne = DB::connection('vlz')->table('tbl_reselling_task')
                ->select('id', 'reselling_asin_id')
                ->where('id', $taskId)
                ->first();
            if (!empty($tasktaskOne)) {
                $reselling_asin_id = $tasktaskOne->reselling_asin_id;
            }
            if ($reselling_asin_id > 0) {
                $tasktaskAll = DB::connection('vlz')->table('tbl_reselling_task')
                    ->select('id', 'reselling_asin_id')
                    ->where('reselling_asin_id', $reselling_asin_id)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($tasktaskAll)) {
                    foreach ($tasktaskAll as $tk => $tv) {
                        $taskIdList[] = $tv['id'];
                    }
                }
            }
            if (!empty($taskIdList)) {
                $taskDetail = DB::connection('vlz')->table('tbl_reselling_detail')
                    ->select('id', 'price', 'task_id', 'shipping_fee', 'account', 'white', 'sellerid', 'created_at', 'reselling_remark')
                    ->where('task_id', $taskId)
                    ->where('white', 0)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                $taskDetail_list = DB::connection('vlz')->table('tbl_reselling_detail')
                    ->select('id', 'task_id', 'sellerid', 'created_at')
                    ->whereIn('task_id', $taskIdList)
                    ->where('white', 0)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
            }
            if (!empty($taskDetail_list)) {
                foreach ($taskDetail_list as $tlk => $tlv) {
                    $taskDetail_list[$tlk]['count'] = 0;
                    $created_at = 0;
                    $reselling_count = 0;
                    foreach ($taskDetail_list as $tk => $tv) {
                        if ($tlv['sellerid'] == $tv['sellerid']) {
                            if ($tv['created_at'] - $created_at > 3600 && $reselling_count == 0) {
                            } elseif ($tv['created_at'] - $created_at > 3600) {
                            } elseif ($tv['created_at'] - $created_at < 3600) {
                                $reselling_count++;
                            }
                            $created_at = $tv['created_at'];
                        }
                    }
                    $taskDetail_list[$tlk]['count'] = $reselling_count;
                    $reselling_count_list[$tlv['sellerid']] = $reselling_count;
                }

            }
            if (!empty($taskDetail)) {
                foreach ($taskDetail as $k => $v) {
                    $taskDetail[$k]['timecount'] = $reselling_count_list[$v['sellerid']];
                    $taskDetail[$k]['price'] = $v['price'] / 100;
                    $taskDetail[$k]['shipping_fee'] = $v['shipping_fee'] / 100;

                }
            }
        }
        return $taskDetail;
    }

    /**
     * 修改 detail remark  备注
     * @param Request $request
     */
    public function upResellingDetail(Request $request)
    {
        $sellerid = $task_id = $reselling_asin_id = $task_id_list = NULL;
        if (!empty($request['id']) && $request['id'] > 0 && $request['remark']) {
            $taskDetail = DB::connection('vlz')->table('tbl_reselling_detail')
                ->select('id', 'task_id', 'sellerid')
                ->where('id', $request['id'])
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
            if (!empty($taskDetail)) {
                $task_id = $taskDetail[0]['task_id'];
                $sellerid = $taskDetail[0]['sellerid'];
            }
            if (!empty($task_id)) {
                $reselling_task = DB::connection('vlz')->table('tbl_reselling_task')
                    ->select('id', 'reselling_asin_id')
                    ->where('id', $task_id)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($reselling_task)) {
                    $reselling_asin_id = $reselling_task[0]['reselling_asin_id'];
                }
            }
            if (!empty($reselling_asin_id)) {
                $reselling_task_list = DB::connection('vlz')->table('tbl_reselling_task')
                    ->select('id')
                    ->where('reselling_asin_id',  $reselling_asin_id)
                    ->where('reselling_num', '>', 0)
                    ->get()->map(function ($value) {
                        return (array)$value;
                    })->toArray();
                if (!empty($reselling_task_list)) {
                    foreach ($reselling_task_list as $k => $v) {
                        $task_id_list[] = $v['id'];
                    }
                }
            }
            if (!empty($task_id_list)) {
                $result = DB::connection('vlz')->table('tbl_reselling_detail')
                    ->whereIn('task_id', $task_id_list)
                    ->where('sellerid', $sellerid)
                    ->update(['reselling_remark' => $request['remark']]);
                if ($result > 0) {
                    echo '更新成功';
                } else {
                    echo '更新失败';
                }
            }else {
                echo '缺少参数';
            }
        } else {
            echo '缺少参数';
        }
        exit;
    }

}
