<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EdmTag;
use DB;

class EdmTagController extends Controller
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
		if(!Auth::user()->can(['edm-tag-show'])) die('Permission denied -- edm-tag-show');
		return view('edm/tagIndex');
	}

	/*
	 * ajax展示模板列表
	 */
	public function List()
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$name = isset($search['name']) ? urldecode($search['name']) : '';

		$query = new EdmTag();
		if($name){
			$query = $query->where('name',$name);
		}
		$recordsFiltered = $recordsTotal = $query->count();//计算出总数
		$iDisplayLength = intval($_REQUEST['length']);
		$iDisplayLength = $iDisplayLength < 0 ? $recordsTotal : $iDisplayLength;
		$iDisplayStart = intval($_REQUEST['start']);
		$data =  $query->orderBy('id','desc')->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();//查询数据
		foreach($data as $key=>$val){
			if(!Auth::user()->can(['edm-tag-update'])){
				$action = 'NO';
			}else{
				$action = '<br><a href="/edm/tag/update?id='.$val['id'].'" class="btn btn-success btn-xs" >Edit</a>';
			}
			$data[$key]['action'] = $action;
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 添加单个客户数据
	 */
	public function add(Request $request)
	{
		if(!Auth::user()->can(['edm-tag-add'])) die('Permission denied -- edm-tag-add');
		if($request->isMethod('get')){
			return view('edm/tagAdd');
		}elseif ($request->isMethod('post')){
			$insertData = array();
			if(isset($_POST['name']) && $_POST['name']){
				$insertData['name'] = $_POST['name'];
			}
			if(isset($_POST['describe']) && $_POST['describe']){
				$insertData['describe'] = $_POST['describe'];
			}
			if($insertData){
				$res = EdmTag::insert($insertData);//数据插入表中
				if(empty($res)){
					$request->session()->flash('error_message','Add Failed');
					return redirect()->back()->withInput();
				}
			}
		}
		return redirect('/edm/tag');
	}

	/*
	 * 更新单个客户数据
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can(['edm-tag-update'])) die('Permission denied -- edm-tag-update');
		if($request->isMethod('get')){
			$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
			$data = EdmTag::where('id',$id)->first();
			if($data){
				$data = $data->toArray();
				return view('edm/tagEdit',['data'=>$data]);
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
			if(isset($_POST['describe']) && $_POST['describe']){
				$update['describe'] = $_POST['describe'];
			}
			$res = EdmTag::where('id',$id)->update($update);
			if($res){
				return redirect('/edm/tag');
			}else{
				$request->session()->flash('error_message','Update Failed');
				return redirect()->back()->withInput();
			}
		}
	}

}