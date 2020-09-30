<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EdmCampaign;
use App\Models\EdmTemplate;
use App\Models\EdmTag;
use DB;

class EdmCampaignController extends Controller
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
		if(!Auth::user()->can(['edm-campaign-show'])) die('Permission denied -- edm-campaign-show');
		return view('edm/CampaignIndex');
	}

	/*
	 * ajax展示campaign列表
	 */
	public function List()
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$name = isset($search['name']) ? urldecode($search['name']) : '';
		$asin = isset($search['asin']) ? urldecode($search['asin']) : '';

		$query = new EdmCampaign();
		if($name){
			$query = $query->where('name',$name);
		}
		if($asin){
			$query = $query->where('asin',$asin);
		}

		$tmpData  = EdmTemplate::getEdmTemplateIdName();//获取tmp数据
		$tagData  = EdmTag::getEdmCustomerTag();//获取tag数据

		$recordsFiltered = $recordsTotal = $query->count();//计算出总数
		$iDisplayLength = intval($_REQUEST['length']);
		$iDisplayLength = $iDisplayLength < 0 ? $recordsTotal : $iDisplayLength;
		$iDisplayStart = intval($_REQUEST['start']);
		$data =  $query->orderBy('id','desc')->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();//查询数据
		foreach($data as $key=>$val){
			//tsg_id转换为tag_name
			$tag_ids = explode(',',$val['tag_id']);
			$data[$key]['tag_name'] = '';
			foreach($tag_ids as $k=>$tagid){
				if(isset($tagData[$tagid])){
					$data[$key]['tag_name'].= $tagData[$tagid].',';
				}
			}
			$data[$key]['tag_name'] = rtrim($data[$key]['tag_name'],",");
			$data[$key]['template_name'] = isset($tmpData[$val['template_id']]) && $tmpData[$val['template_id']] ? $tmpData[$val['template_id']] : $val['template_id'];

			if(!Auth::user()->can(['edm-campaign-update'])){
				$action = 'NO';
			}else{
				$action = '<br><a href="/edm/campaign/update?id='.$val['id'].'" class="btn btn-success btn-xs" >Edit</a>';
			}
			$data[$key]['action'] = $action;
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 添加campaign活动时,通过模板和asin得到发送content的内容
	 */
	public function getContentByTmpAsin()
	{
		$asin = isset($_POST['asin']) && $_POST['asin'] ? trim($_POST['asin']) : '';
		$template_id = isset($_POST['template_id']) && $_POST['template_id'] ? $_POST['template_id'] : '';
		$marketplaceid = isset($_POST['marketplaceid']) && $_POST['marketplaceid'] ? $_POST['marketplaceid'] : '';
		$return['status'] = 0;
		if($asin && $template_id && $marketplaceid){
			//通过template_id获取内容
			$tmpdata = EdmTemplate::where('id',$template_id)->first()->toArray();
			$content = $tmpdata['content'];
			//获取asin相关产品数据，替换模板里的内容
			$productData = DB::connection('vlz')->select("select * from asins where marketplaceid='".$marketplaceid."' and asin = '".$asin."' limit 1");
			if($productData){
				$productImages = $productData[0]->images;
				$return['content'] = $content;
				$return['status'] = 1;
			}else{
				$return['msg'] = 'Asin Error';
			}
		}else{
			$return['msg'] = 'Asin and Template,Marketplaceid cannot be empty';
		}
		return $return;
	}

	/*
	 * 添加campaign活动
	 */
	public function add(Request $request)
	{
		if(!Auth::user()->can(['edm-campaign-add'])) die('Permission denied -- edm-campaign-add');
		$tmpData  = EdmTemplate::getEdmTemplateIdName();//获取tmp数据
		$tagData  = EdmTag::getEdmCustomerTag();//获取tag数据
		if($request->isMethod('get')){
			$site = getMarketDomain();//获取站点选项
			return view('edm/campaignAdd',['tmpData'=>$tmpData,'tagData'=>$tagData,'site'=>$site,'date'=>date('Y-m-d')]);
		}elseif ($request->isMethod('post')){
			$insertData = array();
			$config = array('tag_id','asin','name','subject','content','template_id','set_sendtime');
			foreach($config as $field){
				if(isset($_POST[$field]) && $_POST[$field]){
					$insertData[$field] = $_POST[$field];
				}
			}

			if($insertData){
				$res = EdmCampaign::insert($insertData);//数据插入表中
				if(empty($res)){
					$request->session()->flash('error_message','Add Failed');
					return redirect()->back()->withInput();
				}
			}
		}
		return redirect('/edm/template');
	}

	/*
	 * 更新单个客户数据
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can(['edm-template-update'])) die('Permission denied -- edm-template-update');
		if($request->isMethod('get')){
			$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
			$data = EdmTemplate::where('id',$id)->first();
			if($data){
				$data = $data->toArray();
				return view('edm/templateEdit',['data'=>$data]);
			}else{
				$request->session()->flash('error_message','ID error');
				return redirect()->back()->withInput();
			}
		}elseif ($request->isMethod('post')){
			$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : '';
			$update = array();
			if(isset($_POST['name']) && $_POST['name']){
				$update['name'] = $_POST['name'];
			}
			if(isset($_POST['abstract']) && $_POST['abstract']){
				$update['abstract'] = $_POST['abstract'];
			}
			if(isset($_POST['content']) && $_POST['content']){
				$update['content'] = $_POST['content'];
			}
			$res = EdmTemplate::where('id',$id)->update($update);
			if($res){
				return redirect('/edm/template');
			}else{
				$request->session()->flash('error_message','Update Failed');
				return redirect()->back()->withInput();
			}
		}
	}

}