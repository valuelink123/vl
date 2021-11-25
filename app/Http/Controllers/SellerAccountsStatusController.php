<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SellerAccountsStatusRecord as Model;
use DB;

class SellerAccountsStatusController extends Controller
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
		if(!Auth::user()->can(['seller-accounts-status-show'])) die('Permission denied -- seller-accounts-status-show');
		$site = getMarketDomain();//获取站点选项
		return view('sellerAccountsStatus/index',['site'=>$site]);
	}

	/*
	 * ajax展示模板列表
	 */
	public function List(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$marketplace_id= isset($search['site']) ? urldecode($search['site']) : '';
		$account= isset($search['account']) ? urldecode($search['account']) : '';
		//先检测是否每个账号都有一条默认的状态记录数据
		$sql_check = "select a.mws_marketplaceid as mws_marketplaceid,a.mws_seller_id as mws_seller_id,a.label as label 
from seller_accounts as a
left join seller_accounts_status_record as b on a.mws_marketplaceid = b.mws_marketplaceid and a.mws_seller_id = b.mws_seller_id 
where b.id is NULL";
		$data_check = DB::connection('vlz')->select($sql_check);
		if($data_check){
			$data_check = json_decode(json_encode($data_check),true);
			$insert = array();
			foreach($data_check as $key=>$val){
				$val['account_status'] = $val['record_status'] = 'ENABLE';
				$val['user_id'] = 1;
				$insert[] = $val;
			}
			DB::connection('vlz')->table('seller_accounts_status_record')->insert($insert);
		}

		$where = "where record_status = 'ENABLE' and a.mws_marketplaceid = '$marketplace_id'";
		if($account){
			$account = implode("','",explode(",",$account));
			$account = "'".$account."'";
			$where .= ' and a.mws_seller_id in('.$account.')';
		}
		$limit = '';
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql ="select SQL_CALC_FOUND_ROWS a.id as id,a.mws_marketplaceid as mws_marketplaceid,a.mws_seller_id as mws_seller_id,a.label as label,b.account_status as account_status,b.record_status as record_status,b.created_at as created_at,b.updated_at as updated_at  
from seller_accounts as a
left join (SELECT * FROM seller_accounts_status_record WHERE ID IN (select max(id) as id from seller_accounts_status_record where record_status='ENABLE' group by mws_marketplaceid,mws_seller_id)) as b on a.mws_marketplaceid = b.mws_marketplaceid and a.mws_seller_id = b.mws_seller_id 
{$where} {$limit}";

		$data = DB::connection('vlz')->select($sql);
		$recordsTotal = DB::connection('vlz')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = json_decode(json_encode($data),true);
		$site = getMarketDomain();//获取站点选项
		$siteDomain = array();
		foreach($site as $key=>$val){
			$siteDomain[$val->marketplaceid] = $val->domain;
		}
		foreach($data as $key=>$val){
			$data[$key]['site'] = isset($siteDomain[$val['mws_marketplaceid']]) ? $siteDomain[$val['mws_marketplaceid']] : $val['mws_marketplaceid'];
			$data[$key]['action'] = '<a href="sellerAccountsStatus/view?id='.$val['id'].'" class="btn btn-success btn-xs" >View</a>';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}
	/*
	 * 添加数据
	 */
	public function add(Request $request)
	{
		if(!Auth::user()->can(['seller-accounts-status-add'])) die('Permission denied -- seller-accounts-status-add');
		$site = getMarketDomain();//获取站点选项
		$account_status = array('DISABLE','ENABLE');
		$record_status = array('ENABLE');
		if($request->isMethod('get')){
			return view('sellerAccountsStatus/add',['site'=>$site,'account_status'=>$account_status,'record_status'=>$record_status]);
		}elseif ($request->isMethod('post')){
			$insertData = array();
			$configField = array('mws_marketplaceid','mws_seller_id','label','account_status','record_status','remark');
			foreach($configField as $field){
				if(isset($_POST[$field]) && $_POST[$field]){
					$insertData[$field] = $_POST[$field];
				}
			}
			if($insertData){
				$insertData['user_id'] = Auth::user()->id;
				DB::connection('vlz')->beginTransaction();
				$res = DB::connection('vlz')->table('seller_accounts_status_record')->where('mws_marketplaceid',$insertData['mws_marketplaceid'])->where('mws_seller_id',$insertData['mws_seller_id'])->update(['record_status'=>'DISABLE']);
				if($res){
					$res = Model::insert($insertData);//数据插入表中
					if($res){
						DB::connection('vlz')->commit();
					}else{
						DB::connection('vlz')->rollBack();
						$request->session()->flash('error_message','Add Failed');
						return redirect()->back()->withInput();
					}
				}else{
					DB::connection('vlz')->rollBack();
					$request->session()->flash('error_message','Add Failed');
					return redirect()->back()->withInput();
				}
			}
		}
		return redirect('/sellerAccountsStatus');
	}

	/*
	 * 查看明细
	 */
	public function view(Request $request)
	{
		$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
		$_data = DB::connection('vlz')->table('seller_accounts')->where('id',$id)->first();
		if($_data){
			$data = DB::connection('vlz')->table('seller_accounts_status_record')->where('mws_marketplaceid',$_data->mws_marketplaceid)->where('mws_seller_id',$_data->mws_seller_id)->orderBy('id','desc')->get();
			$data = json_decode(json_encode($data),true);
			$site = getMarketDomain();//获取站点选项
			$siteDomain = array();
			foreach($site as $key=>$val){
				$siteDomain[$val->marketplaceid] = $val->domain;
			}
			$user = getUsers();
			foreach($data as $key=>$val){
				$data[$key]['site'] = isset($siteDomain[$val['mws_marketplaceid']]) ? $siteDomain[$val['mws_marketplaceid']] : $val['mws_marketplaceid'];
				$data[$key]['user_name'] = isset($user[$val['user_id']]) ? $user[$val['user_id']] : $val['user_id'];
			}
			return view('/sellerAccountsStatus/view',['data'=>$data]);
		}else{
			$request->session()->flash('error_message','ID error');
			return redirect()->back()->withInput();
		}

	}

}