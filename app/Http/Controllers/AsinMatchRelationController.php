<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AsinMatchRelation as Model;
use DB;

class AsinMatchRelationController extends Controller
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
		if(!Auth::user()->can(['asin-match-relation-show'])) die('Permission denied -- asin-match-relation-show');
		$site = getMarketDomain();//获取站点选项
		$source = array('SAP','VOP');
		return view('asinMatchRelation/index',['site'=>$site,'source'=>$source]);
	}

	/*
	 * ajax展示模板列表
	 */
	public function List()
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$asin= isset($search['asin']) ? urldecode($search['asin']) : '';
		$seller_sku= isset($search['seller_sku']) ? urldecode($search['seller_sku']) : '';
		$marketplace_id= isset($search['site']) ? urldecode($search['site']) : '';
		$item_no= isset($search['item_no']) ? urldecode($search['item_no']) : '';
		$account= isset($search['account']) ? urldecode($search['account']) : '';
		$source= isset($search['source']) ? urldecode($search['source']) : '';

		if($source=='VOP'){
			$query = DB::table('asin_match_relation');
		}else{
			$query = DB::connection('amazon')->table('sap_asin_match_sku');
		}

		if($asin){
			$query = $query->where('asin',$asin);
		}
		if($seller_sku){
			$query = $query->where('seller_sku',$seller_sku);
		}
		if($item_no){
			if($source=='VOP'){
				$query = $query->where('item_no',$item_no);
			}else{
				$query = $query->where('sku',$item_no);
			}

		}
		$query = $query->where('marketplace_id',$marketplace_id);
		if($account){
			$query = $query->whereIn('seller_id',explode(',',$account));
		}
		$recordsFiltered = $recordsTotal = $query->count();//计算出总数
		$iDisplayLength = intval($_REQUEST['length']);
		$iDisplayLength = $iDisplayLength < 0 ? $recordsTotal : $iDisplayLength;
		$iDisplayStart = intval($_REQUEST['start']);
		$data =  $query->orderBy('id','desc')->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();//查询数据
		$data = json_decode(json_encode($data),true);

		$site = getMarketDomain();//获取站点选项
		$siteDomain = array();
		foreach($site as $key=>$val){
			$siteDomain[$val->marketplaceid] = $val->domain;
		}
		$seller_user = getUsers('sap_seller');
		$sellerIdName= DB::connection('amazon')->table('seller_accounts')->where('mws_marketplaceid',$marketplace_id)->pluck('label','mws_seller_id');
		$sellerIdName = json_decode(json_encode($sellerIdName),true);

		foreach($data as $key=>$val){
			$data[$key]['site'] = isset($siteDomain[$val['marketplace_id']]) ? $siteDomain[$val['marketplace_id']] : $val['marketplace_id'];
			$data[$key]['user_name'] = isset($seller_user[$val['sap_seller_id']]) ? $seller_user[$val['sap_seller_id']] : $val['sap_seller_id'];
			$data[$key]['source'] = $source;

			if($source=='VOP'){
				$action = '';
				if(Auth::user()->can(['asin-match-relation-edit'])){
					$action .= '<a href="/asinMatchRelation/update?id='.$val['id'].'" class="btn btn-success btn-xs" >Edit</a>';
				}
				if(Auth::user()->can(['asin-match-relation-delete'])){
					$action .= '<a href="javascript:void(0)" data-id="'.$val['id'].'" class="btn btn-success btn-xs delete-action" >Delete</a>';
				}
				if(empty($action)){
					$action = '-';
				}
				$data[$key]['action'] = $action;
			}else{
				//SAP相关数据处理，seller_name，item_no，warehouse，created_at
				$data[$key]['action'] = '-';
				$data[$key]['seller_name'] = isset($sellerIdName[$val['seller_id']]) ? $sellerIdName[$val['seller_id']] : $val['seller_id'];
				$data[$key]['item_no'] = $val['sku'];
				$data[$key]['warehouse'] = $val['sap_warehouse_code'];
				$data[$key]['created_at'] = $val['updated_at'];

			}
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 添加数据
	 */
	public function add(Request $request)
	{
		if(!Auth::user()->can(['asin-match-relation-add'])) die('Permission denied -- asin-match-relation-add');
		$site = getMarketDomain();//获取站点选项
		$source = array('VOP');
		$seller_user = getUsers('sap_seller');
		if($request->isMethod('get')){
			return view('asinMatchRelation/add',['site'=>$site,'source'=>$source,'seller_user'=>$seller_user]);
		}elseif ($request->isMethod('post')){
			$insertData = array();
			$configField = array('marketplace_id','seller_id','asin','seller_sku','item_no','sap_seller_id','source','warehouse','seller_name');
			foreach($configField as $field){
				if(isset($_POST[$field]) && $_POST[$field]){
					$insertData[$field] = $_POST[$field];
				}
			}
			if($insertData){
				$res = Model::insert($insertData);//数据插入表中
				if(empty($res)){
					$request->session()->flash('error_message','Add Failed');
					return redirect()->back()->withInput();
				}
			}
		}
		return redirect('/asinMatchRelation');
	}

	/*
	 * 更新数据
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can(['asin-match-relation-edit'])) die('Permission denied -- asin-match-relation-edit');
		if($request->isMethod('get')){
			$site = getMarketDomain();//获取站点选项
			$source = array('VOP');
			$seller_user = getUsers('sap_seller');
			$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
			$data = Model::where('id',$id)->first();
			if($data){
				$data = $data->toArray();
				return view('/asinMatchRelation/edit',['data'=>$data,'site'=>$site,'source'=>$source,'seller_user'=>$seller_user]);
			}else{
				$request->session()->flash('error_message','ID error');
				return redirect()->back()->withInput();
			}
		}elseif ($request->isMethod('post')){
			$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : '';

			$update = array();
			$configField = array('marketplace_id','seller_id','asin','seller_sku','item_no','sap_seller_id','source','warehouse','seller_name');
			foreach($configField as $field){
				if(isset($_POST[$field]) && $_POST[$field]){
					$update[$field] = $_POST[$field];
				}
			}
			$res = Model::where('id',$id)->update($update);
			if($res){
				return redirect('/asinMatchRelation');
			}else{
				$request->session()->flash('error_message','Update Failed');
				return redirect()->back()->withInput();
			}
		}
	}

	/*
	 * 删除数据
	 */
	public function delete(Request $request)
	{
		if(!Auth::user()->can(['asin-match-relation-add'])){
			return array('status'=>0,'msg'=>'Permission denied -- asin-match-relation-add');
		}
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : 0;
		$res = Model::where('id',$id)->delete();
		return array('status'=>$res);
	}

}