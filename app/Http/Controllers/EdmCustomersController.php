<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EdmCustomer;
use DB;
use \DrewM\MailChimp\MailChimp;
use \App\Models\EdmTag;

class EdmCustomersController extends Controller
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
		if(!Auth::user()->can(['edm-customers-show'])) die('Permission denied -- edm-customers-show');
		$tag = EdmTag::getEdmCustomerTag();
		$status = array(0=>'active',1=>'inactive');
		$mailchimp_status = array(0=>'exist',1=>'inexist');
		return view('edm/customerIndex',['tag'=>$tag,'status'=>$status,'mailchimp_status'=>$mailchimp_status]);
	}

	/*
	 * ajax展示客户列表
	 */
	public function List()
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$email = isset($search['email']) ? urldecode($search['email']) : '';
		$status = isset($search['status']) ? $search['status'] : '';
		$mailchimp_status = isset($search['mailchimp_status']) ? $search['mailchimp_status'] : '';
		$tag_id = isset($search['tag_id']) ? $search['tag_id'] : '';

		$tagData = EdmTag::getEdmCustomerTag();
		$query = new EdmCustomer();
		if($email){
			$query = $query->where('email',$email);
		}

		if($mailchimp_status !== ''){
			$query = $query->where('mailchimp_status',$mailchimp_status);
		}
		if($status !== ''){
			$query = $query->where('status',$status);
		}
		if($tag_id){
			$query = $query->where(DB::raw("FIND_IN_SET({$tag_id},tag_id)"));
		}

		$recordsFiltered = $recordsTotal = $query->count();//计算出总数
		$iDisplayLength = intval($_REQUEST['length']);
		$iDisplayLength = $iDisplayLength < 0 ? $recordsTotal : $iDisplayLength;
		$iDisplayStart = intval($_REQUEST['start']);
		$data =  $query->orderBy('id','desc')->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();//查询数据
		foreach($data as $key=>$val){
			$tag_id = $val['tag_id'];
			$tag_ids = explode(',',$tag_id);
			$data[$key]['tag_name'] = '';
			foreach($tag_ids as $k=>$tagid){
				if(isset($tagData[$tagid])){
					$data[$key]['tag_name'].= $tagData[$tagid].',';
				}
			}
			$data[$key]['tag_name'] = rtrim($data[$key]['tag_name'],",");
			$data[$key]['mailchimp_status'] = 'exist';
			$data[$key]['status'] = 'active';
			$action = '';
			//type为1,push数据到mailchimp(mailchimp_status,1=>0);type=2,客户状态由inactive改为active(status,1=>0);type=3,客户状态由active改为inactive(status,0=>1)
			if($val['mailchimp_status']==1){
				$data[$key]['mailchimp_status'] = 'inexist';
				$action .= '<button type="button" class="status_btn btn btn-success btn-xs" data-type="1" data-id="'.$val['id'].'">Push</button><br>';
			}
			if($val['status']==1){
				$data[$key]['status'] = 'inactive';
				$action .= '<button type="button" class="status_btn btn btn-success btn-xs" data-type="2" data-id="'.$val['id'].'">Active</button>';
			}else{
				$action .= '<button type="button" class="status_btn btn btn-danger btn-xs" data-type="3" data-id="'.$val['id'].'">InActive</button>';
			}
			if(!Auth::user()->can(['edm-customers-update'])){
				$action = 'NO';
			}else{
				$action .= '<br><a href="/edm/customers/update?id='.$val['id'].'" class="btn btn-success btn-xs" >Edit</a>';
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
		if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		if($request->isMethod('get')){
			$tag = EdmTag::getEdmCustomerTag();
			return view('edm/customerAdd',['tag'=>$tag]);
		}elseif ($request->isMethod('post')){
			//push客户信息到mailchimp后台
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$list_id = env('MAILCHIMP_LISTID', '');
			$customerData = EdmCustomer::where('email',$_POST['email'])->first();//判断此用户是否已经存在
			if($customerData){
				$request->session()->flash('error_message','此用户已存在');
				return redirect()->back()->withInput();
			}
			$configfield = array('email'=>'email_address','first_name'=>'first_name','last_name'=>'last_name','address'=>'address','phone'=>'phone_number','tag_id'=>'tag_id');

			$pushdata['status'] = 'subscribed';
			$insertData = array();
			foreach($configfield as $key=>$field){
				if(isset($_POST[$key]) && $_POST[$key]){
					if($key=='tag_id'){
						$insertData[$key] = implode(',',$_POST[$key]);
					}else{
						$insertData[$key] = $_POST[$key];
						$pushdata[$field] = $_POST[$key];
					}
				}
			}
			//处理发送给接口的地址数据
			$pushAddress = '';
			if(isset($insertData['address']) && $insertData['address']){
				$addressArray = explode(',',$insertData['address']);
				if(count($addressArray)==6){
					$pushAddress = array(
						"addr1"=> $addressArray[0],
						"addr2"=>$addressArray[1],
						"city"=> $addressArray[2],
						"state"=> $addressArray[3],
						"zip"=> $addressArray[4],
						"country"=> $addressArray[5]
					);
				}
			}
			$pushdata['merge_fields'] = array('FNAME'=>$_POST['first_name'],'LNAME'=>$_POST['last_name'],'PHONE'=>$_POST['phone']);
			if($pushAddress){
				$pushdata['merge_fields']['ADDRESS'] = $pushAddress;
			}

			//整理标签名称
			$tagIdArray = EdmTag::getEdmCustomerTag();
			$pushTag = array();
			$tag_id = explode(',',$insertData['tag_id']);
			foreach($tag_id as $key=>$tag){
				if(isset($tagIdArray[$tag])){
					$pushTag[] = $tagIdArray[$tag];
				}
			}
			$pushdata['tags'] =  $pushTag;
			$response = $MailChimp->post("lists/$list_id/members",$pushdata);//添加一个客户到mailchimp
			if(isset($response['tags'])){
				$updateTag = array();
				foreach($response['tags'] as $k=>$v){
					$tag_id = array_search($v['name'], $tagIdArray);
					if($tag_id){
						$updateTag[] = array('id'=>$tag_id,'mailchimp_tagid'=>$v['id']);
					}
				}
				if($updateTag){
					EdmTag::insertOnDuplicateKey($updateTag);
				}
			}

			if($response){
				if(isset($response['detail'])){
					$insertData['error_info'] = $response['detail'];
					$insertData['mailchimp_status'] = 1;
				}
			}else{
				$insertData['mailchimp_status'] = 1;
			}
			EdmCustomer::insertOnDuplicateWithDeadlockCatching($insertData,['email']);//数据插入edm_customer表中
		}
		return redirect('/edm/customers');
	}

	/*
	 * 更新单个客户数据
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can(['edm-customers-update'])) die('Permission denied -- edm-customers-update');
		if($request->isMethod('get')){
			$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
			$tag = EdmTag::getEdmCustomerTag();
			$customerData = EdmCustomer::where('id',$id)->first();
			if($customerData){
				$customerData = $customerData->toArray();
				$customerData['tag_ids'] = explode(',',$customerData['tag_id']);
				return view('edm/customerEdit',['tag'=>$tag,'customerData'=>$customerData]);
			}else{
				$request->session()->flash('error_message','ID error');
				return redirect()->back()->withInput();
			}
		}elseif ($request->isMethod('post')){
			$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : '';
			$customerData = EdmCustomer::where('id',$id)->first();
			$oldTagId = explode(',',$customerData['tag_id']);
			if(isset($_POST['tag_id']) && $_POST['tag_id']){
				$update['tag_id'] = implode(',',$_POST['tag_id']);
			}else{
				$update['tag_id'] = '';
			}
			//更新到mailchimp
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$list_id = env('MAILCHIMP_LISTID', '');
			//整理标签名称
			$tagIdArray = EdmTag::getEdmCustomerTag();
			$pushTag = array();
			$tag_id = explode(',',$update['tag_id']);
			foreach($oldTagId as $key=>$tag){
				$pushTag[$tag] = array('name'=>$tagIdArray[$tag],'status'=>'inactive');
			}
			foreach($tag_id as $key=>$tag){
				if(isset($tagIdArray[$tag])){
					$pushTag[$tag] = array('name'=>$tagIdArray[$tag],'status'=>'active');
				}
			}
			$pushTag = array_values($pushTag);//数组格式化
			$subscriber_hash = $MailChimp->subscriberHash($_POST['email']);
			$response = $MailChimp->post("lists/$list_id/members/$subscriber_hash/tags", [
				'tags'=>$pushTag
			]);
			if(!isset($response['detail'])){
				//更新数据库表
				$res = EdmCustomer::where('id',$id)->update($update);
				if($res){
					return redirect('/edm/customers');
				}else{
					$request->session()->flash('error_message','Update Failed');
					return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Update Tag Failed');
				return redirect()->back()->withInput();
			}
		}
	}

	/*
	 * 导入excel表格数据到CRM模块
	 */
	public function import( Request $request )
	{
		if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		$addnum = 0;
		set_time_limit(0);
		if($request->isMethod('POST')){
			$file = $request->file('importFile');
			if($file){
				if($file->isValid()){
					$ext = $file->getClientOriginalExtension();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/edm_customer/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);

					if($bool){
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						//得到的数据中,A=>email,B=>first_name,C=>last_name,D=>address,E=>phone,F=>tag_id
						$insertData = $mailchimp_data = array();
						$tagIdArray = EdmTag::getEdmCustomerTag();

						foreach($importData as $key => $val){
							if($key==1 || empty($val['A'])){
								unset($importData[$key]);
								continue;
							}
							//整理标签名称
							$pushTag = array();
							$tag_id = explode(',',$val['F']);
							foreach($tag_id as $key=>$tag){
								if(isset($tagIdArray[$tag])){
									$pushTag[] = $tagIdArray[$tag];
								}
							}
							//插入数据库数据
							$insertData[$val['A']] = array(
								'email' => $val['A'],
								'first_name' => $val['B'],
								'last_name' => $val['C'],
								'address' => $val['D'],
								'phone' => $val['E'],
								'tag_id' => $val['F'],
								'error_info' => '',
								'mailchimp_status' => 0,
							);
							//处理发送给接口的地址数据
							$pushAddress = '';
							if(isset($val['D']) && $val['D']){
								$addressArray = explode(',',$val['D']);
								if(count($addressArray)==6){
									$pushAddress = array(
										"addr1"=> $addressArray[0],
										"addr2"=>$addressArray[1],
										"city"=> $addressArray[2],
										"state"=> $addressArray[3],
										"zip"=> $addressArray[4],
										"country"=> $addressArray[5]
									);
								}
							}
							//发送到mailchimp接口数据
							$mailchimp_data[] = array(
								'email_address' => $val['A'],
								'status' => 'subscribed',
								'tags'=> $pushTag,
								'merge_fields' => array('FNAME'=>$val['B'],'LNAME'=>$val['C'],'ADDRESS'=>$pushAddress,'PHONE'=>$val['E']),
							);
						}
						//把客户信息数据推送到mailchimp中
						$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
						$list_id = env('MAILCHIMP_LISTID', '');
						$mailchimp_data = array_chunk($mailchimp_data,500);//把数组分成每500个一组,分批处理
						foreach($mailchimp_data as $key=>$val){
							$pushdata['members'] = $val;//推送到mailchimp中的数据要以members为键
							 $response = $MailChimp->post("lists/$list_id",$pushdata);//批量添加/更新成员列表，每次最多添加/更新500个
							//处理mailchimp接口返回的tagid
							$this->insertMailchimpTagId($response);
							//错误代码为ERROR_CONTACT_EXISTS时,表示mailchimp列表中已存在
							$errorInfo = isset($response['errors']) ? $response['errors'] : '';//API接口返回的错误信息
							if(is_array($errorInfo)){
								foreach($errorInfo as $k=>$v){
									if($v['error_code']!='ERROR_CONTACT_EXISTS'){
										$insertData[$v['email_address']]['email'] = $v['email_address'];
										$insertData[$v['email_address']]['error_info'] = $v['error'];
										$insertData[$v['email_address']]['mailchimp_status'] = 1;
									}
								}
							}
						}

						$insertData = array_values($insertData);
						EdmCustomer::insertOnDuplicateWithDeadlockCatching($insertData,['email']);//数据插入edm_customer表中

						if (!$MailChimp->success()) {
							print_r($MailChimp->getLastError());
							print_r($MailChimp->getLastResponse());
							die();
						}
						$request->session()->flash('success_message','Import Data Success!');
					}else{
						$request->session()->flash('error_message','Import Data Failed');
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
					return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
				return redirect()->back()->withInput();
			}
		}
		return redirect('/edm/customers');
	}

	/*
	 * 下载导入excel表格的模板
	 */
	public function download(Request $request)
	{
		if(!Auth::user()->can(['edm-customers-add'])) die('Permission denied -- edm-customers-add');
		$filepath = 'edm_customer_import_template.xls';
		$file=fopen($filepath,"r");
		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filepath);
		echo fread($file,filesize($filepath));
		fclose($file);
	}

	//type为1,push数据到mailchimp(mailchimp_status,1=>0);type=2,客户状态由inactive改为active(status,1=>0);type=3,客户状态由active改为inactive(status,0=>1)
	public function action()
	{
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
		$return['status'] = 0;
		$return['msg'] = '更新异常';
		$update['updated_at'] = date('Y-m-d H:i:s');
		if($type==1){
			//push客户信息到mailchimp后台
			$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
			$list_id = env('MAILCHIMP_LISTID', '');
			$customerData = EdmCustomer::where('id',$id)->first()->toArray();

			//整理标签名称
			$tagIdArray = EdmTag::getEdmCustomerTag();
			$pushTag = array();
			$tag_id = explode(',',$customerData['tag_id']);
			foreach($tag_id as $key=>$tag){
				if(isset($tagIdArray[$tag])){
					$pushTag[] = $tagIdArray[$tag];
				}
			}

			$pushdata = array(
				'email_address' => $customerData['email'],
				'status' => 'subscribed',
				'first_name' => $customerData['first_name'],
				'last_name' => $customerData['last_name'],
				'address' => $customerData['address'],
				'phone_number' => $customerData['phone'],
				'tags'=>$pushTag,
			);
			//推送到mailchimp中的数据要以members为键
			$response = $MailChimp->post("lists/$list_id/members",$pushdata);//更新成员列表
			if($response){
				if(isset($response['id'])){//请求成功
					$update['mailchimp_status'] = 0;
				}elseif($response['detail']){
					$update['error_info'] = $response['detail'];
					$return['msg'] =  $response['detail'];
					$res = EdmCustomer::where('id',$id)->update($update);
					if($res){
						$return['msg'] = 'Push 异常';
					}
					return $return;
				}
			}else{
				$return['msg'] = 'Push 异常';
				return $return;
			}
		}elseif($type==2){
			$update['status'] = 0;
		}elseif($type==3){
			$update['status'] = 1;
		}
		if($type && $id){
			$res = EdmCustomer::where('id',$id)->update($update);
			if($res){
				$return['status'] = 1;
			}
		}
		return $return;
	}

	//处理mailchimp接口返回的tagid
	public function insertMailchimpTagId($response)
	{
		$updateTag = array();
		$tagIdArray = EdmTag::getEdmCustomerTag();
		if(isset($response['new_members'])){
			foreach($response['new_members'] as $key=>$val){
				foreach($val['tags'] as $k=>$v){
					$tag_id = array_search($v['name'], $tagIdArray);
					if($tag_id){
						$updateTag[] = array('id'=>$tag_id,'mailchimp_tagid'=>$v['id']);
					}
				}
			}
		}
		if(isset($response['updated_members'])){
			foreach($response['updated_members'] as $key=>$val){
				foreach($val['tags'] as $k=>$v){
					$tag_id = array_search($v['name'], $tagIdArray);
					if($tag_id){
						$updateTag[] = array('id'=>$tag_id,'mailchimp_tagid'=>$v['id']);
					}
				}
			}
		}
		if($updateTag){
			EdmTag::insertOnDuplicateKey($updateTag);
		}
		return true;
	}
	//从mailchimp拉取数据
	public function pullByMailchimp($members=array(),$page=0)
	{
		//push客户信息到mailchimp后台
		$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
		$list_id = env('MAILCHIMP_LISTID', '');
		$numPerPage = 1000;
		$offset = $numPerPage * $page;
		$args = array(
			'count' => $numPerPage,
			'status' => 'subscribed',
			'fields' => 'members.merge_fields,members.email_address,total_items,members.tags',//查只需要的字段
			'offset' => $offset,
		);

		$response = $MailChimp->get("/lists/$list_id/members",$args);//更新成员列表

		if(isset($response['members']) && $response['members']){
			$members = isset($members) ? array_merge($members,$response['members']) : $response['members'];
			$total = $response['total_items'];
			$page = $page + 1;
			$offset = $numPerPage * $page;
			if($offset < $total){
				$this->pullByMailchimp($members,$page);
			}

			$insertData = array();
			$tagData = EdmTag::getEdmMailchimpTag();//ID=>mailchimp_tagid
			foreach($members as $key=>$val){
				$address = $val['merge_fields']['ADDRESS'] ? implode(',',array_values($val['merge_fields']['ADDRESS'])) : '';
				$memberTags = $val['tags'];
				$push_tag = array();
				if($memberTags){
					foreach($memberTags as $tk=>$tv){//返回的成员
						if(!in_array($tv['id'],$tagData)){
							$insertTag = array(
								'mailchimp_tagid' => $tv['id'],
								'name' => $tv['name'],
							);
							$tag_id = DB::table('edm_tag')->insertGetId($insertTag);//插入数据库
						}else{
							$tag_id = array_search($tv['id'], $tagData);
						}
						$push_tag[] = $tag_id;//多个标签id数组(数据库自增id)
						$tagData[$tag_id] = $tv['id'];//插入成功后添加到$tagData数组中
					}
				}
				$push_tagstr = $push_tag ? implode(',',$push_tag) : '';//多个tag的话，逗号分隔存到数据库中
				$insertData[] = array(
					'email' => $val['email_address'],
					'first_name' => $val['merge_fields']['FNAME'],
					'last_name' => $val['merge_fields']['LNAME'],
					'address' => $address,
					'phone' => $val['merge_fields']['PHONE'],
					'tag_id' => $push_tagstr,
				);
			}
			EdmCustomer::insertOnDuplicateWithDeadlockCatching($insertData,['email']);//数据插入edm_customer表中
			return ['status'=>1,'msg'=>'pull success'];
		}
		return ['status'=>0,'msg'=>'pull failed'];
	}




}