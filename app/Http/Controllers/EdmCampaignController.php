<?php

namespace App\Http\Controllers;
use DrewM\MailChimp\MailChimp;
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
			$data[$key]['send_status_name'] = $val['send_status']==1 ? 'Yes' : 'NO';

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
			$productData = DB::connection('amazon')->select("select * from asins where marketplaceid='".$marketplaceid."' and asin = '".$asin."' limit 1");
			if($productData){
				$productImages = $productData[0]->images;
				$imagehtml = '';//产品图片
				if($productImages){
					$imageArr = explode(',',$productImages);
					if($imageArr){
						$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
						$imagehtml = '<img  src="'.$image.'">';
					}
				}

				$content = str_replace('*|PRODUCT IMAGE|*',$imagehtml,$content);//替换产品图片
				$content = str_replace('*|ASIN|*',$asin,$content);//替换asin
				$content = str_replace('*|PRODUCT TITLE|*',$productData[0]->title,$content);//替换产品title
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
			$config = array('tag_id','marketplaceid','asin','name','subject','content','template_id');
			$set = $_POST['set_sendtime'];
			$set_sendtime = date('Y-m-d H:i:s');
			$insertData['sendtime_type'] = 0;//立即发送
			if($set==1){
				$set_sendtime = $_POST['senddate'].' 00:00:00';
				$insertData['sendtime_type'] = 1;//选定时间发送
			}
			//content内容
			$content = $_POST['content'];
			$content = str_replace('/uploads/ueditor/php/upload/image/',$_SERVER['SERVER_NAME'].'/uploads/ueditor/php/upload/image/',$content);//图片路径要替换成绝对路径

			//调用添加模板接口
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$list_id = env('MAILCHIMP_LISTID', '');
			$pushTmp = array('name'=>$_POST['name'],'html'=>$content);
			$response = $MailChimp->post("/templates",$pushTmp);//添加一个模板到mailchimp
			if(isset($response['id'])){
				$template_id = $response['id'];//template接口返回的模板id
				//整理标签ID
				$tagIdArray = EdmTag::getEdmMailchimpTag();
				$pushTag = array();
				$tag_id = $_POST['tag_id'];
				foreach($tag_id as $key=>$tag){
					if(isset($tagIdArray[$tag])){
						$pushTag[] = array("field"=>"static_segment","op"=> "static_is","value"=> $tagIdArray[$tag]);
					}
				}

				//调用campaign接口
				$pushCap = array('type'=>'regular','html'=>$_POST['content']);
				$pushCap['recipients'] = array(
					'list_id'=>$list_id,
					'segment_opts'=>array(
						'match'=>'any',
						'c' => $pushTag,
					)
				);

				$from_name = env('MAILCHIMP_FROM_NAME', '');
				$reply_to = env('MAILCHIMP_REPLY_TO', '');
				$pushCap['settings'] = array('subject_line'=>$_POST['subject'],'title'=>$_POST['name'],'template_id'=>$template_id,'from_name'=>$from_name,'reply_to'=>$reply_to);
				$response = $MailChimp->post("/campaigns",$pushCap);//添加一个campaign到mailchimp
				if(isset($response['id'])){
					$campaign_id = $response['id'];
					if($set==0){//立即发送邮件
						$response = $MailChimp->post("/campaigns/$campaign_id/actions/send");//488782，tag6
						if(empty($response)){
							$insertData['send_status'] = 1;
							$insertData['real_sendtime'] = date('y-m-d H:i:s');
						}
					}

					foreach($config as $field){
						if(isset($_POST[$field]) && $_POST[$field]){
							$insertData[$field] = $_POST[$field];
						}
					}
					$insertData['tag_id'] = implode(',',$insertData['tag_id']);
					$insertData['set_sendtime'] = $set_sendtime;
					$insertData['mailchimp_tmpid'] = $template_id;
					$insertData['mailchimp_campid'] = $campaign_id;
					$res = EdmCampaign::insert($insertData);//数据插入表中
					if(empty($res)){
						$request->session()->flash('error_message','Add Failed');
						return redirect()->back()->withInput();
					}
				}else{
					$request->session()->flash('error_message','Add campaigns Failed');
					return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Add templates Failed');
				return redirect()->back()->withInput();
			}
		}
		return redirect('/edm/campaign');
	}

	/*
	 * 更新单个客户数据
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can(['edm-campaign-update'])) die('Permission denied -- edm-campaign-update');
		$tmpData  = EdmTemplate::getEdmTemplateIdName();//获取tmp数据
		$tagData  = EdmTag::getEdmCustomerTag();//获取tag数据
		if($request->isMethod('get')){
			$site = getMarketDomain();//获取站点选项
			$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
			$data = EdmCampaign::where('id',$id)->first();
			if($data){
				$data = $data->toArray();
				$data['tag_ids'] = explode(',',$data['tag_id']);
				$data['set_sendtime'] = substr($data['set_sendtime'], 0,10);
				return view('edm/campaignEdit',['data'=>$data,'tmpData'=>$tmpData,'tagData'=>$tagData,'site'=>$site,'date'=>date('Y-m-d')]);
			}else{
				$request->session()->flash('error_message','ID error');
				return redirect()->back()->withInput();
			}
		}elseif ($request->isMethod('post')){
			$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : '';
			$data = EdmCampaign::where('id',$id)->first();
			$mailchimp_tmpid = $data['mailchimp_tmpid'];
			$mailchimp_campid = $data['mailchimp_campid'];

			$config = array('tag_id','marketplaceid','asin','name','subject','content','template_id');
			$set = $_POST['set_sendtime'];
			$set_sendtime = date('Y-m-d H:i:s');
			$update['sendtime_type'] = 0;//立即发送
			if($set==1){
				$set_sendtime = $_POST['senddate'].' 00:00:00';
				$update['sendtime_type'] = 1;//选定时间发送
			}
			//content内容
			$content = $_POST['content'];
			$content = str_replace('/uploads/ueditor/php/upload/image/',$_SERVER['SERVER_NAME'].'/uploads/ueditor/php/upload/image/',$content);//图片路径要替换成绝对路径

			//整理标签名称
			$tagIdArray = EdmTag::getEdmMailchimpTag();
			$pushTag = array();
			$tag_id = $_POST['tag_id'];
			foreach($tag_id as $key=>$tag){
				if(isset($tagIdArray[$tag])){
					$pushTag[] = array("field"=>"static_segment","op"=> "static_is","value"=> $tagIdArray[$tag]);
				}
			}


			//调用添加模板接口
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$list_id = env('MAILCHIMP_LISTID', '');
			$pushTmp = array('template_id'=>$mailchimp_tmpid,'name'=>$_POST['name'],'html'=>$content);
			$response = $MailChimp->patch("/templates/$mailchimp_tmpid",$pushTmp);//更新一个模板到mailchimp
			if(isset($response['id'])){
				//调用campaign接口
				$pushCap = array('type'=>'regular','html'=>$_POST['content']);
				$pushCap['recipients'] = array(
					'list_id'=>$list_id,
					'segment_opts'=>array(
						'match'=>'any',
						'conditions'=>$pushTag,
					)
				);

				$from_name = env('MAILCHIMP_FROM_NAME', '');
				$reply_to = env('MAILCHIMP_REPLY_TO', '');
				$pushCap['settings'] = array('campaign_id'=>$mailchimp_campid,'subject_line'=>$_POST['subject'],'title'=>$_POST['name'],'template_id'=>$mailchimp_tmpid,'from_name'=>$from_name,'reply_to'=>$reply_to);
				$response = $MailChimp->PATCH('/campaigns/'.$mailchimp_campid,$pushCap);//添加一个campaign到mailchimp

				if(isset($response['id'])){
					if($set==0){//立即发送邮件
						$response = $MailChimp->post("/campaigns/$mailchimp_campid/actions/send");
						if(empty($response)){
							$update['send_status'] = 1;
							$update['real_sendtime'] = date('y-m-d H:i:s');
						}
					}
					foreach($config as $field){
						if(isset($_POST[$field]) && $_POST[$field]){
							$update[$field] = $_POST[$field];
						}
					}
					$update['tag_id'] = implode(',',$update['tag_id']);
					$update['set_sendtime'] = $set_sendtime;
					$update['mailchimp_tmpid'] = $mailchimp_tmpid;
					$update['mailchimp_campid'] = $mailchimp_campid;
					$res = EdmCampaign::where('id',$id)->update($update);
					if(empty($res)){
						$request->session()->flash('error_message','Update Failed');
						return redirect()->back()->withInput();
					}
				}else{
					$request->session()->flash('error_message','Update campaigns Failed');
					return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Update templates Failed');
				return redirect()->back()->withInput();
			}
			return redirect('/edm/campaign');
		}
	}

}