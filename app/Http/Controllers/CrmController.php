<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Classes\SapRfcRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use App\Accounts;
use App\User;
use App\ConfigOption;
use App\Category;
use App\Models\TrackLog;

class CrmController extends Controller
{
	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */

	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		if(!Auth::user()->can(['crm-show'])) die('Permission denied -- crm-show');
		$users = User::getUsers();
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
		//获取country,from,brand
		$countrys = $this->queryFields('SELECT DISTINCT country FROM client_info');
		$froms = $this->queryFields('SELECT DISTINCT `from` FROM client_info');
		$brands = $this->queryFields('SELECT DISTINCT brand FROM client_info');
		$date_from=date('Y-m-d',strtotime('-30 days'));
		$date_to=date('Y-m-d');
		return view('crm/index',['date_from'=>$date_from,'date_to'=>$date_to,'bgs'=>$bgs,'bus'=>$bus,'users'=>$users,'countrys'=>$countrys,'froms'=>$froms,'brands'=>$brands]);
	}

	//ajax获取列表数据
	public function get(Request $request)
	{
		$sql = $this->getCrmSql($request);
		$limit = $this->dtLimit($request);
		$sql .= ' LIMIT '.$limit;
		$data = $this->queryRows($sql);

		$recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

		$fbgroupConfig = getFacebookGroup();
		foreach($data as $key=>$val){
			$action = '';
			if(!Auth::user()->can(['crm-update'])){
                $data[$key]['email'] = '<a href="'.url('crm/show?id='.$val['id']).'" target="_blank">'.$val['email'].'</a>';
			}else{
                $data[$key]['email'] = '<a href="'.url('crm/show?id='.$val['id']).'" target="_blank">'.$val['email'].'</a><br/>'.'<a href="'.url('crm/edit?id='.$val['id']).'" target="_blank" class="badge badge-success"> Edit </a>';

                $action = '<a href="'.url('crm/trackLogAdd?id='.$val['id']).'" target="_blank" class="badge badge-success"> Add Activity </a>';
			}
			if($val['amazon_profile_page']){
				$amazonPage = str_replace("http://","",$val['amazon_profile_page']);
				$amazonPage = str_replace("https://","",$amazonPage);
				$action .= '<a href="http://'.$amazonPage.'" target="_blank"><i class="fa fa-share"></i></a>';
			}
			$data[$key]['action'] = $action;
            //0默认; 1黑名单; 2 Limited Comment by Amazon 数据库中的取值可能为：'1','2','1,2','0'
            //$data[$key]['type'] = getCrmClientType()[$data[$key]['type']];
            $type = $data[$key]['type'];
            if($type != '0'){
                $types_array = explode(",",$type);
                $types_array_text = [];
                foreach($types_array as $value){
                    $types_array_text[] = array_get(getCrmClientType(), $value);
                }
                $type_text = implode(',', $types_array_text);
                $data[$key]['email'] = $data[$key]['email'].'<br/><font color="red">'.$type_text.'</font>';
            }

			//当点击ctg,rsg,Negative Review所属的数字时，可以链接到相对应的客户列表页面，times_ctg，times_rsg，times_negative_review
			$email = $val['email'];
			if($val['times_ctg']>0){
				$data[$key]['times_ctg'] = '<a href="/ctg/list?email='.$email.'" target="_blank">'.$val['times_ctg'].'</a>';
			}
			if($val['times_rsg']>0){
				$data[$key]['times_rsg'] = '<a href="/rsgrequests?email='.$email.'" target="_blank">'.$val['times_rsg'].'</a>';
			}
			if($val['times_negative_review']>0){
				$data[$key]['times_negative_review'] = '<a href="/review?email='.$email.'" target="_blank">'.$val['times_negative_review'].'</a>';
			}
			//显示facebook_group内容
			$data[$key]['facebook_group'] = isset($fbgroupConfig[$val['facebook_group']]) ? $fbgroupConfig[ $val['facebook_group']] : '';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	得到列表搜索的sql语句，导出和列表共用一个sql语句,列表就是再加上限制的条数
	 */
	public function getCrmSql($request)
	{
		$where = $this->dtWhere(
			$request,
			[
				// 'email' => 'c.email',
				// 'name' => 'c.name',
				// 'phone' => 'c.phone',
				// 'order_id' => 't1.amazon_order_id',
			],
			[],
			[
				// WHERE IN
				'processor' => 't1.processor',
				// 'from' => 'c.from',
				// 'brand' => 'c.brand',
				// 'country' => 'c.country',
				// WHERE FIND_IN_SET
				'bg' => 's:b.bg',
				'bu' => 's:b.bu',
			],
			'date'
		);

		$orderby = $this->dtOrderBy($request);
		if($orderby){
			$orderby = ' order by '.$orderby;
		}else{
			$orderby = ' order by id desc';
		}

		$ins = $request->input('search.ins', []);
		$infoFields = array('brand','from','country');
		$whereInfo = ' where 1 = 1 ';
		foreach ($ins as $field => $arr) {
			if(in_array($field,$infoFields)){
				if($arr){
					$values = [];
					foreach ($arr as $value) {
						$values[] = '"' . $value . '"';
					}
					$values = implode(',', $values);
					$whereInfo .= " and `{$field}` IN ({$values})";
				}
			}
		}
		//搜索框输入的内容可以搜索email,name,phone
		$ands = $request->input('search.ands', []);
		foreach ($ands as $field => $value) {
			if (empty($value)) continue;
			$value = addslashes($value);
			if($field=='amazon_order_id'){
				//当为order_id搜索的时候，不能纯粹的限制client_order_info表的订单号(会导致查出来的客户信息order数量只有一条)，而应该先查出该订单号所属的客户id，然后根据client表的id来限制客户id
				//求出这个order_id所在的客户id是多少
				$sql = "select b.client_id as id
					from client_info as b
					join client_order_info as c on c.ci_id = b.id
					where amazon_order_id ='$value' limit 1";
				$idData = $this->queryRows($sql);
				if($idData){
					$where .= " and t1.id = ".$idData[0]['id'];
				}else{
					$where .= ' and 1 !=1 ';
				}
			}elseif($field=='facebook_group'){
				$where .= " and c.facebook_group = ".intval($value);
			}else{
				$whereInfo .= " and {$field}='{$value}'";
			}

		}

		$sql = "select SQL_CALC_FOUND_ROWS t1.id as id,t1.date as date,c.name as name,c.email as email,c.phone as phone,c.remark as remark,c.country as country,c.`from` as `from`,c.brand as brand,
t1.times_ctg as times_ctg,t1.times_rsg as times_rsg,t1.times_sg as times_sg,t1.times_negative_review as times_negative_review,t1.times_positive_review as times_positive_review,t1.type as 'type',if(num>0,num,0) as order_num,b.name as processor,b.bg as bg,b.bu as bu,c.facebook_name as facebook_name,c.facebook_group as facebook_group,c.amazon_profile_page as amazon_profile_page   
			FROM client as t1 
		  	left join(
				select users.id as processor,min(name) as name,min(bg) as bg,min(bu) as bu 
				from users 
				left join asin on users.sap_seller_id = asin.sap_seller_id 
				group by users.id 
				order by users.id desc 
			) as b on b.processor = t1.processor 
			join (
					select count(*) as num,client_id,max(t1.name) as name,max(t1.email) as email,max(t1.phone) as phone,max(t1.remark) as remark,max(t1.country) as country,max(t1.`from`) as `from`,max(t1.brand) as brand,any_value(facebook_name) as facebook_name,any_value(facebook_group) as facebook_group,max(amazon_profile_page) as amazon_profile_page  
					from client_info t1 
					left join client_order_info as t2 on t1.id = t2.ci_id
			  		{$whereInfo}
			  		group by client_id 
			) as c on t1.id = c.client_id 
			where $where 
			{$orderby} ";
		return $sql;
	}

	//导出数据
	public function export(Request $request)
	{
		set_time_limit(0);
		if(!Auth::user()->can(['crm-export'])) die('Permission denied -- crm-export');
		$date_from = $_GET['date_from'];
		$date_to = $_GET['date_to'];

		$where = " and t1.date >= '".$date_from." 00:00:00' and t1.date <= '".$date_to." 23:59:59'";
		$sql = $sql = "select t1.id as id,t1.date as date,c.name as name,c.email as email,c.phone as phone,c.remark as remark,c.country as country,c.`from` as `from`,c.brand as brand,
t1.times_ctg as times_ctg,t1.times_rsg as times_rsg,t1.times_negative_review as times_negative_review,t1.times_positive_review as times_positive_review,if(num>0,num,0) as order_num,b.name as processor,b.bg as bg,b.bu as bu,c.facebook_name as facebook_name,c.facebook_group as facebook_group,c.amazon_profile_page as amazon_profile_page   
			FROM client as t1 
		  	left join(
				select users.id as processor,min(name) as name,min(bg) as bg,min(bu) as bu 
				from users 
				left join asin on users.sap_seller_id = asin.sap_seller_id 
				group by users.id 
				order by users.id desc 
			) as b on b.processor = t1.processor 
			join (
					select count(*) as num,client_id,max(t1.name) as name,max(t1.email) as email,max(t1.phone) as phone,max(t1.remark) as remark,max(t1.country) as country,max(t1.`from`) as `from`,max(t1.brand) as brand,any_value(facebook_name) as facebook_name,any_value(facebook_group) as facebook_group,max(amazon_profile_page) as amazon_profile_page  
					from client_info t1 
					left join client_order_info as t2 on t1.id = t2.ci_id 
			  		group by client_id 
			) as c on t1.id = c.client_id 
			where 1 = 1 $where ";

		$data = $this->queryRows($sql);
		$arrayData = array();
		$headArray = array('ID','Date','Email','Name','Phone','Country','From','Brand','CTG','RSG','Negative Review','Positive Review','Order Number','BG','BU','Remark','Processor');
        $arrayData[] = $headArray;

		foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['id'],
                $val['date'],
                $val['email'],
				$val['name'],
                $val['phone'],
                $val['country'],
                $val['from'],
                $val['brand'],
				strval($val['times_ctg']),//数字转化为字符串，不然整数0导出到excel会显示空白
				strval($val['times_rsg']),
				strval($val['times_negative_review']),
				strval($val['times_positive_review']),
				strval($val['order_num']),
				$val['bg'],
				$val['bu'],
				$val['remark'],
				$val['processor'],
            );
        }
        if($arrayData){
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $arrayData,  // The data to set
                    NULL,        // Array values with this value will not be set
                    'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            header('Content-Disposition: attachment;filename="Export_CRM.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
	}

	//从列表点击edit，进入编辑页面
	public function edit(Request $request)
	{
		if(!Auth::user()->can(['crm-update'])) die('Permission denied -- crm-update');
		$id = $request->input('id');
		$contactInfo = array();
		$contactBasic['id'] = $id;
		if($id){
			$sql = "select b.client_id as id,b.id as info_id,c.id as cid,b.name as name,b.email as email,b.phone as phone,b.remark as remark,b.type as client_info_type, b.country as country,b.`from` as `from`,b.brand as brand,b.facebook_group as facebook_group,b.facebook_name as facebook_name,c.amazon_order_id as amazon_order_id,c.order_type as order_type,c.amazon_profile_page as amazon_profile_page,d.type as type,d.subscribe as subscribe,d.block as block
			FROM client_info as b
			left join client_order_info as c on b.id = c.ci_id
			left join client as d on b.client_id = d.id
			where b.client_id = $id
			order by b.id asc ";
			$_data = $this->queryRows($sql);
			$fbgroupConfig = getFacebookGroup();
			foreach($_data as $key=>$val){
            	//联系人基本信息
				$contactInfo[$val['email']]['info_id'] = $val['info_id'];
				$contactInfo[$val['email']]['email'] = $val['email'];
				$contactInfo[$val['email']]['phone'] = $val['phone'];
                $contactInfo[$val['email']]['remark'] = $val['remark'];

                $client_info_type = $val['client_info_type'];
                $client_info_type_array = [];
                //不为null或者""
                if(!empty($client_info_type)){
                    $client_info_type_array = explode(",",$client_info_type);
                }
                $contactInfo[$val['email']]['client_info_type'] = $client_info_type_array;

				$contactInfo[$val['email']]['cid'][] = $val['cid'];
				$contactInfo[$val['email']][$val['cid']]['amazon_order_id'] = $val['amazon_order_id'];
				$contactInfo[$val['email']][$val['cid']]['order_type'] = $val['order_type'];
				$contactInfo[$val['email']][$val['cid']]['amazon_profile_page'] = $val['amazon_profile_page'];
				$contactBasic['country'] = $val['country'];
				$contactBasic['name'] = $val['name'];
				$contactBasic['brand'] = $val['brand'];
				$contactBasic['from'] = $val['from'];
				$contactBasic['facebook_name'] = $val['facebook_name'];
				$contactBasic['facebook_group'] = isset($fbgroupConfig[$val['facebook_group']]) ? $val['facebook_group'].' | '.$fbgroupConfig[$val['facebook_group']] : $val['facebook_group'];
                $contactBasic['type'] = $val['type'];
                $contactBasic['subscribe'] = $val['subscribe'];
                $contactBasic['block'] = $val['block'];
			}
		}
		if(!isset($contactBasic['name'])){
			$request->session()->flash('error_message','No Data By Id = '.$id);
			return redirect('/crm');
		}

        $co_id_name_pairs = CrmController::get_co_id_name_pairs();

		return view('crm/edit',['contactInfo'=>$contactInfo,'contactBasic'=>$contactBasic,'co_id_name_pairs'=>$co_id_name_pairs]);
	}
	//添加客户信息
	public function create(Request $request)
	{
		if(!Auth::user()->can(['crm-add'])) die('Permission denied -- crm-add');
		$id = DB::table('client')->max('id')+1;

        $co_id_name_pairs = CrmController::get_co_id_name_pairs();

		return view('crm/add',['id'=>$id, 'co_id_name_pairs'=>$co_id_name_pairs]);
	}

    //获取标签类型下的所有二级类目的id=>name,并按照Order倒序排列
	public function get_co_id_name_pairs(){
	    return ConfigOption::where('co_pid','1')->orderBy('co_order', 'DESC')->pluck('co_name','id');
    }

	/*
	 * 在编辑页面保存，更新数据库里的该条数据
	 * 添加客户数据
	 */
	public function update(Request $request)
	{
		$data = $_POST;
		$type = '0';
		//传到后台的值可能为：'', '1','2','1,2','0'('0'是edit页面可能传回的值)
		if(isset($data['type']) && $data['type'] != ''){
		    var_dump($data['type']);
		    $type = $data['type'];
        }
		$old_id = isset($data['old_id']) ? $data['old_id'] : 0;
		//查填写的客户id是否存在，如果不存在就要新添加记录
		$clientData = DB::table('client')->select('id')->where('id',$data['id'])->get()->toArray();
		DB::beginTransaction();
		if(empty($clientData)){
			//添加client表的数据
			if(!DB::table('client')->insert(
				array(
					'id'=>$data['id'],
					'date'=>date('Y-m-d H:i:s'),
					'created_at'=>date('Y-m-d H:i:s'),
					'updated_at'=> date('Y-m-d H:i:s'),
                    'type'=>$type,
                    'subscribe'=>$data['subscribe'],
                    'block'=>$data['block']
					)
				)
			){
				$request->session()->flash('error_message','Add Failed');
				return redirect()->back()->withInput();
			}
		}else{
			//修改client表的更新时间,以及type,subscribe,block 3个字段
			DB::table('client')->where('id', $data['id'])->update(['type'=>$type, 'subscribe'=>$data['subscribe'], 'block'=>$data['block'], 'updated_at'=>date('Y-m-d H:i:s')]);
		}

		$insertInfo = array('client_id'=>$data['id'],'name'=>$data['name'],'country'=>$data['country'],'from'=>$data['from'],'brand'=>$data['brand'],'facebook_name'=>$data['facebook_name'],'facebook_group'=>intval($data['facebook_group']));
		//查出client_info表的id,把之前的该客户的client_info数据删掉
		$_ciids = DB::table('client_info')->select('id')->where('client_id',$old_id)->get()->toArray();
		$ciids = array();
		foreach($_ciids as $key=>$val){
			$ciids[] = $val->id;
		}
		DB::table('client_info')->whereIn('id',$ciids)->delete();//删除掉client_info表里的该条数据
		DB::table('client_order_info')->whereIn('ci_id',$ciids)->delete();//订单信息，删除之前的

		foreach($_POST['group-data'] as $key=>$val){
			//检查是否有相同的邮箱,email要保持唯一性
			$_email = DB::table('client_info')->where('email',$val['email'])->get()->toArray();
			if($_email){
				DB::rollBack();
				$request->session()->flash('error_message','Save Failed,Same Email By '.$val['email']);
				return redirect()->back()->withInput();
			}
			$insertInfo['email'] = $val['email'];
			$insertInfo['phone'] = $val['phone'];
            $insertInfo['remark'] = $val['remark'];

            $tag_types = '';
            //至少选择了一个Tag Type。多个值之间用','隔开。例如： 6 或者 2,4,5
            if(isset($val['tag_types'])){
                $tag_types = implode(',',$val['tag_types']);
            }
            $insertInfo['type'] = $tag_types;

			$insert['ci_id'] = $res =  DB::table('client_info')->insertGetId($insertInfo);
			if(empty($res)){
				DB::rollBack();
				$request->session()->flash('error_message','Save Email Failed by '.$val['email']);
				return redirect()->back()->withInput();
			}

			foreach($val['order-list'] as $v){
				if($v['amazon_order_id']){
					//判断该order_id是否有，order_id是唯一性的
					$_order = DB::table('client_order_info')->where('amazon_order_id',$v['amazon_order_id'])->get()->toArray();
					if($_order){
						DB::rollBack();
						$request->session()->flash('error_message','Save Failed,,Same Order Id By '.$v['amazon_order_id']);
						return redirect()->back()->withInput();
					}
					//添加现在获取到的
					$insert['amazon_order_id'] = $v['amazon_order_id'];
					$insert['order_type'] = $v['order_type'];
					$insert['amazon_profile_page'] = $v['amazon_profile_page'];
					$res = DB::table('client_order_info')->insert($insert);
					if(empty($res)){
						DB::rollBack();
						$request->session()->flash('error_message','Save Order Failed By '.$v['amazon_order_id']);
						return redirect()->back()->withInput();
					}
				}
			}
		}
		if($old_id && $old_id!=$data['id']){
			//如果旧的客户id不等于新填写的客户id,删除旧的客户记录
			DB::table('client')->where('id',$old_id)->delete();
		}
		DB::commit();
		if(empty($old_id)){
			//添加界面之后，跳转到列表页
			return redirect('/crm');
		}
		return redirect('crm/edit?id='.$data['id']);
	}

	//从列表点击show,进入客户详情页
	public function show(Request $request)
	{
		if(!Auth::user()->can(['crm-show'])) die('Permission denied -- crm-show');
		$id = $request->input('id',0);
		if($id){
			$sap = new SapRfcRequest();
			$sql = "select b.name as name,b.email as email,b.phone as phone,b.remark as remark,b.country as country,b.`from` as `from`,c.amazon_order_id as order_id
			FROM client_info as b
			left join client_order_info as c on b.id = c.ci_id
			where b.client_id = $id
			order by b.id desc ";
			$_data = $this->queryRows($sql);
			$orderArr = array();
			$contactInfo = array();
			$emailArr = $emails = array();
			foreach($_data as $key=>$val){
				//获取sap订单信息
				$orderid = $val['order_id'];
				try{
					$orderArr[$key] = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $orderid]));
					$orderArr[$key]['SellerName'] = Accounts::where('account_sellerid', $orderArr[$key]['SellerId'])->first()->account_name ?? 'No Match';
				} catch (\Exception $e) {

                }

            	//联系人基本信息
				$contactInfo[$val['email']] = array('name'=>$val['name'],'email'=>$val['email'],'phone'=>$val['phone'],'remark'=>$val['remark'],'country'=>$val['country']);
				$emails[] = $val['email'];
			}

			$userRows = DB::table('users')->select('id', 'name')->get();

            $users = array();
            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }
			//与联系人的邮箱联系内容
			$emails = DB::table('sendbox')->whereIn('to_address', $emails)->orderBy('date', 'desc')->get();
            $emails = json_decode(json_encode($emails), true); // todo

            $track_log_array = TrackLog::where('record_id',$id)->where('type',2)->orderBy('created_at','desc')->get()->toArray();
            $subject_type =  $this->getSubjectType();

		}
		return view('crm/show',['orderArr'=>$orderArr, 'contactInfo'=> $contactInfo, 'emails' => $emails,'users'=>$users,'track_log_array'=>$track_log_array,'subject_type'=>$subject_type, 'record_id'=>$id]);
	}

    public function getTrackLog(){

        $record_id = $_POST['record_id'];
        $sql = 'select id,channel,email,subject_type,note,created_at,processor from track_log where record_id='.$record_id.' and type=2 order by created_at desc';
        $data = DB::select($sql);
        $data = array_map('get_object_vars', $data);
        $subject_type =  $this->getSubjectType();

        foreach($data as $key=>$val) {
            $data[$key]['channel'] = array_get(getTrackLogChannel(),array_get($val,'channel'));
            $data[$key]['subject_type'] = array_get($subject_type,array_get($val,'subject_type'));
            $data[$key]['processor'] = array_get($this->getUsers(),array_get($val,'processor'));

            $note_complete = array_get($val,'note');
            //删除<br>, <br/>
            $note_simple=preg_replace('/<br[\/]?>/','',$note_complete);
            //<p></p>后面加一个换行符<br/>
            $note_simple = str_replace('</p>','</p><br/>',$note_simple);
            //去掉除<br/>之外的所有html标签，保留标签之内的文字
            $note_simple = preg_replace('/<(?!br\s*\/?)[^>]+>/','',$note_simple);

            $data[$key]['note'] = '<div class="text" style="text-align:left">'.$note_simple.'</div><a href="javascript:see_more('.$val['id'].');" class="pull-right" number="'.$val['id'].'">See More</a><div style="text-align:left; display:none">'.$note_complete.'</div>';

        }

        return compact(['data']);

    }

	/*
	 * 导入excel表格数据到CRM模块
	 */
	public function import( Request $request )
	{
		if(!Auth::user()->can(['crm-import'])) die('Permission denied -- crm-import');
		$addnum = 0;
		set_time_limit(0);
		if($request->isMethod('POST')){
			$file = $request->file('importFile');
			if($file){
				if($file->isValid()){
					$ext = $file->getClientOriginalExtension();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/crm/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);

					if($bool){

						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						//得到的数据中,A=>name,B=>E-email,C=>phone,D=>Amazon_Order_Id,E=>country,F=>brand,G=>from
						$countrys = getCrmCountry();
						$froms = getCrmFrom();
						$brands = getCrmBrand();

						$emailArr = $orderArr = array();
						foreach($importData as $key => $data){
							if($key==1 || empty($data['B']) || empty($data['D'])){
								unset($importData[$key]);
								continue;
							}
							$data['E'] = strtoupper($data['E']);//把国家全部转换成大写
							if($data['E'] && !in_array($data['E'],$countrys)){
								DB::rollBack();
								$request->session()->flash('error_message','Import Data Failed,'.$data['E'].' is not a valid country');
								return redirect()->back()->withInput();
							}
							if($data['F'] && !in_array($data['F'],$brands)){
								DB::rollBack();
								$request->session()->flash('error_message','Import Data Failed,'.$data['F'].' is not a valid brand');
								return redirect()->back()->withInput();
							}
							if($data['G'] && !in_array($data['G'],$froms)){
								DB::rollBack();
								$request->session()->flash('error_message','Import Data Failed,'.$data['G'].' is not a valid from');
								return redirect()->back()->withInput();
							}
							$emailArr[] = $data['B'];
							$orderArr[] = $data['D'];
						}
						$fileNum = count($importData);

						if($fileNum > 600){
							$request->session()->flash('error_message','Import Data Failed,You can only add 600 pieces of data');
							return redirect()->back()->withInput();
						}
						//判断email和oderid是否已经存在，已经存在就提示
						$sql = 'select b.id as id,b.email as email,c.amazon_order_id as amazon_order_id
								FROM client_info as b
								left join client_order_info as c on b.id = c.ci_id 
								where email in("'.join('","',$emailArr).'") and amazon_order_id in ("'.join('","',$orderArr).'")';
						$_data = $this->queryRows($sql);

						//循环得到已存在的邮箱和已存在的订单号
						$sameEmail = $sameOrder = array();
						foreach($_data as $key=>$val){
							$sameEmail[$val['email']] = $val['id'];
							$sameOrder[$val['amazon_order_id']] = $val['amazon_order_id'];
						}

						$insertOrder = array();

						//开始插入数据
						DB::beginTransaction();//开启事务处理
						//循环处理插入数据
						foreach($importData as $key => $data){
							if(isset($sameOrder[$data['D']])){//存在相同的订单的数据就忽略掉
								unset($importData[$key]);
								continue;
							}

							//检查是否有相同的邮箱,email要保持唯一性,得到相同邮箱的client_info的id
							$ci_id = isset($sameEmail[$data['B']]) ? $sameEmail[$data['B']] : 0;
							if(empty($ci_id)){
								$insertInfo = array(
									'name'=>$data['A'],
									'email'=>$data['B'],
									'phone'=>$data['C'],
									'country'=>$data['E'],
									'brand'=>$data['F'],
									'from'=>empty($data['G']) ? 'Chat' : $data['G'],
								);

								$insertInfo['client_id'] = $res = DB::table('client')->insertGetId(array('date'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
								if($res){
									$ci_id = DB::table('client_info')->insertGetId($insertInfo);
									$sameEmail[$data['B']] = $ci_id;//此邮箱添加的数据的client_info的id保存
								}

							}

							if(empty($ci_id)){
								DB::rollBack();
								$request->session()->flash('error_message','Import Data Failed');
								return redirect()->back()->withInput();
							}

							$insertOrder[] = array(
								'amazon_order_id' => $data['D'],
								'ci_id' => $ci_id,
							);

							$addnum = $addnum + 1;

							unset($importData[$key]);
						}
						//添加crm的订单信息表
						if($insertOrder){
							batchInsert('client_order_info',$insertOrder);
						}
						//销毁变量，释放内存
						unset($sameEmail);
						unset($importData);
						unset($file);
						DB::commit();
						$request->session()->flash('success_message','Import '.$addnum.' pieces of Data Success!');
						// return redirect()->back()->withInput();
					}else{
						$request->session()->flash('error_message','Import Data Failed');
						// return redirect()->back()->withInput();
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
					// return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
				// return redirect()->back()->withInput();
			}
		}
		return redirect('crm');
	}

	/*
	 * 下载导入excel表格的模板
	 */
	public function download(Request $request)
	{
		if(!Auth::user()->can(['crm-import'])) die('Permission denied -- crm-import');
		$filepath = 'clients import template.xls';
		$file=fopen($filepath,"r");
		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filepath);
		echo fread($file,filesize($filepath));
		fclose($file);
	}


	/*
     * crm功能的指派任务，可以把某个任务指派给其他成员
     */
	public function batchAssignTask(Request $req) {
		if(!Auth::user()->can(['crm-update'])) die('Permission denied -- crm-update');
		if (empty($rows = $req->input('ctgRows'))) return [true, ''];

		$processor = (int)$req->input('processor');

		$user = User::findOrFail($processor);
		$ids = array();
		foreach ($req->input('ctgRows') as $row) {
			$ids[] =  $row[0];
		}
		DB::table('client')->whereIn('id', $ids)->update(['processor' => $processor,'updated_at'=>date('Y-m-d H:i:s')]);
		return [true, $user->name];
	}

    public function trackLogAdd(Request $req){
        if(!Auth::user()->can(['crm-update'])) die('Permission denied -- crm-add');

        $record_id = $req->input('id');
        $subject_type =  $this->getSubjectType();
        $users = $this->getUsers();

        $emails = DB::select('select email from client_info where client_id='.$record_id.' order by email desc');
        $emails = array_map('get_object_vars', $emails);
        $email = '';
        if(count($emails) > 0){
            $email = $emails[0]['email'];
        }

        return view('crm/trackLogAdd', compact(['record_id', 'subject_type','users', 'email']));
    }

    public function getSubjectType(){
        return Category::where('category_pid',28)->orderBy('created_at','desc')->pluck('category_name','id');
    }

    public function trackLogStore(Request $request)
    {
        if(!Auth::user()->can(['crm-update'])) die('Permission denied -- templates-create');
        $this->validate($request, [
            'email' => 'required|email',
            'note' => 'required|string',
        ]);

        $data = array('type'=>2,'record_id'=>$request->get('record_id'),'channel'=>$request->get('channel'),'email'=>$request->get('email'),'subject_type'=>$request->get('subject_type'),'note'=>$request->get('note'));

        $track_log = new TrackLog();
        $track_log->add($data);

        return redirect('crm');

    }

    public function getUsers(){
        return User::pluck('name','id');
    }
}