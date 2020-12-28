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
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    //上线 需打开 todo
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index()
    {
		if(!Auth::user()->can(['reselling-show'])) die('Permission denied -- reselling-show');
		$_users = getUsers('sap_seller');
		$users = array();
		foreach($_users as $uk=>$uv){
			$users[$uk] = $uv;
		}
		$bgs = $this->getBgs();
		$bus = $this->getBus();
		$site = getSiteShort();
		$sku_status = Asin::SKU_STATUS_KV;
        return view('hijack.index',compact('bgs','bus','users','site','sku_status'));
    }

    public function detail(Request $request)
    {
		$id = isset($request['id']) && $request['id'] ? $request['id'] : '';
		$_asinInfo = DB::connection('vlz')->table('tbl_reselling_asin')
			->select('domain','asin')
			->where('id', $id)
			->first();
		if($_asinInfo){
			$asinInfo['asin'] = $_asinInfo->asin;
			$asinInfo['domain'] = $_asinInfo->domain;
			return view('hijack.detail',['asinInfo'=>$asinInfo]);
		}else{
			return redirect('/hijack/index');
		}
    }

    /**
     * 首页接口请求
     * 目前使用
     * @return mixed
     */
    public function index1(Request $request)
    {
        $SKU_STATUS_KV = Asin::SKU_STATUS_KV;
        /** 超级权限*/
        $ADMIN_EMAIL = Asin::ADMIN_EMAIL;

        //bg,bu,sap_seller_id,status,marketplace,switchSelect
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));

        $user = Auth::user(); //todo  打开
		$userWhere = ' where 1 = 1';//$userWhere是用于限制可查的asin
		if(!in_array($user->email, $ADMIN_EMAIL)){//不是超级管理员数组
			if ($user->seller_rules) {
				$rules = explode("-", $user->seller_rules);
				if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '".array_get($rules, 0)."'";
				if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '".array_get($rules, 1)."'";
			}elseif($user->sap_seller_id){
				$userWhere .= " and sap_seller_id = ".$user->sap_seller_id;
			}
		}
		if(isset($search['bg']) && $search['bg']!=''){
			$userWhere .= " and sap_seller_bg = '".$search['bg']."'";
		}
		if(isset($search['bu']) && $search['bu']!=''){
			$userWhere .= " and sap_seller_bu = '".$search['bu']."'";
		}
		if(isset($search['sap_seller_id']) && $search['sap_seller_id']!=''){
			$userWhere .= " and sap_seller_id = '".$search['sap_seller_id']."'";
		}
		if(isset($search['asin']) && $search['asin']!=''){
			$userWhere .= " and asin = '".$search['asin']."'";
		}
		if(isset($search['sku_status']) && $search['sku_status']!=''){
			$userWhere .= " and sku_status = '".$search['sku_status']."'";
		}
		$asin_sql = " select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku  {$userWhere}";

		$userasin = array();
		$_userasin = DB::connection('vlz')->select($asin_sql);
		foreach($_userasin as $uk=>$uv){
			if(strlen($uv->asin)==10){//限制只查询有效的asin
				$userasin[$uv->asin] = $uv->asin;
			}
		}
		$userasin = array_values($userasin);
		if(empty($userasin)){
			$err_message = ['status' => '-1', 'message' => 'No matching records found'];
			return $err_message;
		}

        //查询所有 asin 信息
        $ago_time = time() - 3600 * 24;//当前时间 前3小时 todo
        $sql_s = 'SELECT SQL_CALC_FOUND_ROWS 
				  a.id,
					rl_asin.asin,
					a.images,
					a.marketplaceid,
					rl_asin.marketplaceid as mid,
					a.title,
					a.listed_at,
					a.mpn,
					a.seller_count,
					a.updated_at,
					rl_asin.reselling AS reselling_switch,
					rl_asin.id AS rla_id,
					rl_asin.reselling_num,
					rl_task.reselling_time,
					rl_asin.domain AS `domain` 
				FROM
				(
				SELECT
					tbl_reselling_asin.*,
					marketplaces.marketplaceid
				FROM
					tbl_reselling_asin LEFT JOIN marketplaces ON tbl_reselling_asin.domain=marketplaces.domain	
				)	 as rl_asin
				
				LEFT JOIN asins as a ON rl_asin.asin=a.asin AND rl_asin.marketplaceid=a.marketplaceid
				
				LEFT JOIN ( 
			SELECT max( reselling_time ) AS reselling_time, reselling_asin_id FROM tbl_reselling_task WHERE reselling_time >= '.$ago_time.' GROUP BY reselling_asin_id 
			) AS rl_task ON rl_asin.id = rl_task.reselling_asin_id 
			 where 1 = 1 ';
        //默认1开启跟卖，2.全部; 3. 关闭跟卖
		$where = '';
        if(isset($search['switchSelect']) && $search['switchSelect']==1){
			$where .= ' AND rl_asin.reselling=1 ';
        } else if (isset($search['switchSelect']) && $search['switchSelect']==3) {
			$where .= ' AND rl_asin.reselling=0 ';
        }
		if (isset($search['site']) && $search['site']!='') {
			$where .= " AND `domain` = '".$search['site']."'";
		}
        $where .= ' AND rl_asin.asin in ("' . implode($userasin, '","') . '")';

		if($request['length'] != '-1'){//等于-1时为查看全部的数据
			$limit = $this->dtLimit($request);
			$limit = " LIMIT {$limit} ";
		}

		//点击表头排序表格内容相关处理
		$field = ' rl_asin.reselling_num';//默认排序字段跟卖数量
		$sort = ' DESC';//默认倒序排序
		if(isset($_REQUEST['order'][0])){
			//配置排序的字段，点击表格表头某一列对应的要排序的字段
			$configOrder = array(
				4 => 'rl_task.reselling_time',//last updated
				5 => 'rl_asin.reselling_num',//hijackers
			);
			foreach($configOrder as $ok=>$ov){
				if($_REQUEST['order'][0]['column']==$ok) $field = $ov;
			}
			$sort = $_REQUEST['order'][0]['dir'];//排序的类别
		}
		$order = '  ORDER BY '.$field .' '.$sort;

		$sql =  $sql_s.$where.$order.$limit;

        $productList_obj = DB::connection('vlz')->select($sql);
        $productList = (json_decode(json_encode($productList_obj), true));
		$recordsTotal = $recordsFiltered = (DB::connection('vlz')->select('SELECT FOUND_ROWS() as total'))[0]->total;

        //asin相关的对应关系数据,//marketplace_id,sap_seller_id,asin,sap_seller_bg,sap_seller_bu
		$sap_asin_match_sku = array();
        $_sap_asin_match_sku = DB::connection('vlz')->table('sap_asin_match_sku')
            ->select('marketplace_id', 'sap_seller_id', 'asin', 'sap_seller_bg', 'sap_seller_bu', 'id', 'status', 'updated_at', 'sku_status', 'sku')
             ->whereIn('asin', $userasin)
            ->groupBy('asin')
            ->get()->toArray();
        foreach($_sap_asin_match_sku as $sk=>$sv){
			$sap_asin_match_sku[$sv->asin] = (array)$sv;
		}

        //所有的后台用户数据sap_seller_id=>array()
		$userList = array();
		$_userList = DB::table('users')->select('id', 'name', 'email', 'sap_seller_id')->get()->toArray();
        foreach($_userList as $uk=>$uv){
			$userList[$uv->sap_seller_id] = (array)$uv;
		}

		$siteShort = getSiteShort();
        $rla_ids = [];//全部的rla_id，用于查总共有过多少的跟卖账号
		foreach ($productList as $pk => $pv) {
			$rla_ids[] = $pv['rla_id'];
			$productList[$pk]['title'] = $productList[$pk]['title']==NULL ? 'N/A' : $productList[$pk]['title'];
			$productList[$pk]['siteShort'] =  isset($siteShort[$pv['domain']]) ? $siteShort[$pv['domain']] : $pv['domain'];

			$productList[$pk]['reselling_time'] = date('Y-m-d H:i:s',$productList[$pk]['reselling_time']);
			if (isset($sap_asin_match_sku[$pv['asin']])) {
				$productList[$pk]['sap_seller_id'] = $sap_asin_match_sku[$pv['asin']]['sap_seller_id'];
				$productList[$pk]['BG'] = $sap_asin_match_sku[$pv['asin']]['sap_seller_bg'];
				$productList[$pk]['BU'] = $sap_asin_match_sku[$pv['asin']]['sap_seller_bu'];
				$productList[$pk]['sku'] = $sap_asin_match_sku[$pv['asin']]['sku'];
				$productList[$pk]['sap_updated_at'] = $sap_asin_match_sku[$pv['asin']]['updated_at'];
				$productList[$pk]['sku_status'] = $SKU_STATUS_KV[$sap_asin_match_sku[$pv['asin']]['sku_status']];

				if (isset($userList[$productList[$pk]['sap_seller_id']])) {//销售员姓名
					$productList[$pk]['userName'] = $userList[$productList[$pk]['sap_seller_id']]['name'];
				}
			}
		}
		//查每一个asin,历史总共有多少账号被跟卖
		$sql_t = 'select reselling_asin_id,count(distinct account) as total_num 
				from tbl_reselling_task as task 
				left JOIN tbl_reselling_detail as detail on task.id=detail.task_id 
				where task.reselling_asin_id in("' . implode($rla_ids, '","') . '") and white=0 
				group by reselling_asin_id ';
		$_totnum = DB::connection('vlz')->select($sql_t);
		$totnum = array();
		foreach($_totnum as $tk=>$tv){
			$totnum[$tv->reselling_asin_id] = $tv->total_num;
		}
		foreach ($productList as $pk => $pv) {
			$productList[$pk]['hijachers'] = '当前:'.$pv['reselling_num'];
			$productList[$pk]['hijachers'] .= '<br/>历史:'.(isset($totnum[$pv['rla_id']]) ? $totnum[$pv['rla_id']] : '0');
		}

		$data = array_values($productList);
		return compact('data', 'recordsTotal', 'recordsFiltered');
        return $returnDate;
    }

    /**
     * 修改 开启关闭
     * @param Request $request
     * @return string
     */
    public function updateAsinSta(Request $request)
    {
    	if (!empty($_POST['id'])) {
            $toup = 0;
            if (@$_POST['reselling_switch'] == 1) {
                $toup = 1;
            }
            $arr_id = explode(',', $_POST['id']);
            $update = array('reselling'=>$toup);//更新状态值
			$result = DB::connection('vlz')->table('tbl_reselling_asin')->whereIn('product_id', $arr_id)->update($update);//删除1条
            if ($result > 0) {
                $r_message = ['status' => 1, 'msg' => '更新成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '更新失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少参数'];
        }
        return $r_message;
    }
    /**
     * 左侧跟卖列表 顶部产品信息
	 * 传过来的蚕食id为：tbl_reselling_task.reselling_asin_id/tbl_reselling_asin.id
     */
    public function resellingList(Request $request)
    {
        //查询跟卖数据
		$id = isset($request['id']) && $request['id'] ? $request['id'] : '';
		$switch_type = isset($request['switch_type']) && $request['switch_type'] ? $request['switch_type'] : '';
		$result['status'] = 1;
        if ($id){
        	$where = '';
        	if($switch_type==1){
        		$where .= ' and tbl_reselling_asin.reselling = 1';
			}
			$sql = 'select tbl_reselling_task.id as task_id,reselling_time,tbl_reselling_task.reselling_num as reselling_num,product_id  
					from tbl_reselling_task  
					left join tbl_reselling_asin on reselling_asin_id = tbl_reselling_asin.id 
					where reselling_asin_id = '.$id.$where.' 
					order by reselling_time desc';
        	$_data = DB::connection('vlz')->select($sql);
			$taskList = array();
			foreach($_data as $key=>$val) {
				$taskList[] = array(
					'date' => date('Y-m-d H:i:s',$val->reselling_time),
					'reselling_num' => $val->reselling_num,
					'task_id' => $val->task_id,
					'product_id' => $val->product_id,
				);
			}
			$result['data'] = $taskList;
        }else{
			$result = ['status'=>0,'msg'=>'参数异常'];
		}
        return $result;
    }

    /**
     * @param Request $request
     * @return array
     * 查询 detail详情
     */
    public function resellingDetail(Request $request)
    {
		$taskId = isset($request['taskId']) && $request['taskId'] ? $request['taskId'] : '';
//		$product_id = isset($request['product_id']) && $request['product_id'] ? $request['product_id'] : '';//asins.id
		$result['status'] = 1;
        if ($taskId > 0) {
			//每个任务的具体账号信息
			$_taskDetail = DB::connection('vlz')->table('tbl_reselling_detail')
				->select('id', 'price', 'task_id', 'shipping_fee', 'account', 'white', 'sellerid', 'created_at', 'reselling_remark')
				->where('task_id', $taskId)
				->where('white', 0)
				->get()->toArray();
			$account = DB::table('accounts')->select('account_sellerid','account_name')->get()->keyBy('account_sellerid')->toArray();


			$taskDetail = array();
			foreach ($_taskDetail as $k => $v) {
				$taskDetail[$k]['account'] = $v->account;
				if(isset($account[$v->sellerid])){
					$taskDetail[$k]['remark'] = '(是公司账号)';
				}else{
					$taskDetail[$k]['remark'] = '(非公司账号)';
				}
				$taskDetail[$k]['sellerid'] = $v->sellerid;
				$taskDetail[$k]['price'] = $v->price / 100;
				$taskDetail[$k]['shipping_fee'] = $v->shipping_fee / 100;
			}
			$result['data'] = $taskDetail;
		}else{
			$result = ['status'=>0,'msg'=>'参数异常'];
		}
        return $result;
    }

}
