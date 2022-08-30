<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Classes\SapRfcRequest;
use Illuminate\Support\Facades\Auth;
use App\Accounts;
use App\User;
use App\Models\TrackLog;

class CustomerController extends Controller
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
		if(!Auth::user()->can(['customer-show'])) die('Permission denied -- customer-show');
		$users = User::getUsers();
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
		//获取country,from,brand
		$countrys = $this->queryFields('SELECT DISTINCT country FROM client_info');
		$froms = $this->queryFields('SELECT DISTINCT `from` FROM client_info');
		$brands = $this->queryFields('SELECT DISTINCT brand FROM client_info');
		$date_from=date('Y-m-d',strtotime('-30 days'));
//		$date_from = "2021-09-01";//测试日期
		$date_to=date('Y-m-d');
		return view('customer/index',['date_from'=>$date_from,'date_to'=>$date_to,'bgs'=>$bgs,'bus'=>$bus,'users'=>$users,'countrys'=>$countrys,'froms'=>$froms,'brands'=>$brands]);
	}

	//ajax获取列表数据
	public function get(Request $request)
	{
		$sql = $this->getCrmSql($request);
		$limit = $this->dtLimit($request);
		$sql .= ' LIMIT '.$limit;
		$data = $this->queryRows($sql);

		$recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

//		$fbgroupConfig = getFacebookGroup();
		$rsgStatusArr = getCrmRsgStatusArr();
		$time_bad = strtotime('2020-06-01');
		$time_good = strtotime('2022-09-01');
//		echo $time_good;exit;
		foreach($data as $key=>$val){
//			$action = '';
//			$explain = isset($rsgStatusArr[$val['rsg_status_explain']]) ? $rsgStatusArr[$val['rsg_status_explain']]['vop'] : $val['rsg_status_explain'];
//			$emailHtml = '<a href="'.url('crm/show?id='.$val['id']).'" target="_blank">'.$val['encrypted_email']??$val['email'].'</a>';
			//亚马逊客户是不能邀请做RSG的，不显示红色或绿色圆圈。@amazon.com, @amazon.co, @marketplace.amazon.com
//			if($val['email'] && preg_match("/.+@.*amazon.+/", $val['email'])){
//				$rsgStatusHtml = '';
//			}else{
//				if($val['rsg_status']==1) {
//					//邮箱后面显示红色圆圈
//					$rsgStatusHtml = '<div class="unavailable" title="'.$explain.'"></div>';
//				}else{
//					//邮箱后面显示绿色圆圈
//					$rsgStatusHtml = '<div class="available"></div>';
//				}
//			}
//			$res = substr($val['email'], strripos($val['email'], "@") + 1);
			if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $val['name'], $match)) {
				$data[$key]['name'] = '<div style="color:red;">'.$val['name'].'</div>';
			}
			$data[$key]['email'] = $val['encrypted_email']??$val['email'];
			//加密前（2020.6月）的邮箱重点标记，以备大家谨慎使用。2022.9月份之后的邮箱重新标识，健康。
			if(strtotime($data[$key]['date']) <= $time_bad){
				$data[$key]['date'] = '<div style="color:red !important;">'.$val['date'].'</div>';
			}
			if(strtotime($data[$key]['date']) >= $time_good){
				$data[$key]['date'] = '<div style="color:#0D9812 !important;">'.$val['date'].'</div>';
			}
//			if(!Auth::user()->can(['crm-update'])){
//				$data[$key]['email'] = $emailHtml.$rsgStatusHtml;
//			}else{
//				$data[$key]['email'] = $emailHtml.$rsgStatusHtml.'<br/><a href="'.url('crm/edit?id='.$val['id']).'" target="_blank" class="badge badge-success"> Edit </a>';
//
//				$action = '<a href="'.url('crm/trackLogAdd?id='.$val['id']).'" target="_blank" class="badge badge-success"> Add Activity </a>';
//			}
			//点击Batch Send群发邮件时，提取收件人email。
			$data[$key]['email_hidden'] = $val['encrypted_email']??$val['email'];

			if($val['amazon_profile_page']){
				$amazonPage = str_replace("http://","",$val['amazon_profile_page']);
				$amazonPage = str_replace("https://","",$amazonPage);
//				$action .= '<a href="http://'.$amazonPage.'" target="_blank"><i class="fa fa-share"></i></a>';
			}
//			$data[$key]['action'] = $action;
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
			$email = $val['encrypted_email']??$val['email'];
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
//			$data[$key]['facebook_group'] = isset($fbgroupConfig[$val['facebook_group']]) ? $fbgroupConfig[ $val['facebook_group']] : '';
			$data[$key]['email_suffix'] = substr($val['email'], strripos($val['email'], "@") + 1);
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
			],
			[],
			[
				// WHERE IN
				'processor' => 't1.processor',
				'type' => 's:t1.type',
				'bg' => 's:b.bg',
				'bu' => 's:b.bu',
			],
			'date'
		);
		$where.= " and t1.times_ctg+t1.times_rsg+t1.times_sg+num<3 and t1.times_negative_review<1";

//		$orderby = $this->dtOrderBy($request);
//		if($orderby){
//			$orderby = ' order by '.$orderby;
//		}else{
			$orderby = ' order by id desc';
//		}

		$ins = $request->input('search.ins', []);
//		$infoFields = array('brand','from','country');
		$whereInfo = " where 1 = 1 and t1.email not like '%@qq%' and t1.email not like '%@163%' and t1.email not like '%@126%' and t1.email not like '%@amazon.com%' and brush_single_enails.email is null ";
//		foreach ($ins as $field => $arr) {
//			if(in_array($field,$infoFields)){
//				if($arr){
//					$values = [];
//					foreach ($arr as $value) {
//						$values[] = '"' . $value . '"';
//					}
//					$values = implode(',', $values);
//					$whereInfo .= " and `{$field}` IN ({$values})";
//				}
//			}
//		}
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
//			}elseif($field=='facebook_group'){
//				$where .= " and c.facebook_group = ".intval($value);
			}elseif($field=='email'){
				$where .= " and (c.email like '%".trim($value)."%' or c.encrypted_email like '%".trim($value)."%')";
			}else{
				$whereInfo .= " and {$field}='{$value}'";
			}

		}

		$sql = "select SQL_CALC_FOUND_ROWS t1.id as id,t1.date as date,c.name as name,c.email as email,encrypted_email,c.phone as phone,c.remark as remark,c.country as country,c.`from` as `from`,c.brand as brand,
t1.times_ctg as times_ctg,t1.times_rsg as times_rsg,t1.times_sg as times_sg,t1.times_negative_review as times_negative_review,t1.times_positive_review as times_positive_review,t1.type as 'type', t1.rsg_status as rsg_status,t1.rsg_status_explain as rsg_status_explain,if(num>0,num,0) as order_num,b.name as processor,b.bg as bg,b.bu as bu,c.facebook_name as facebook_name,c.facebook_group as facebook_group,c.amazon_profile_page as amazon_profile_page   
			FROM client as t1 
		  	left join(
				select users.id as processor,min(name) as name,min(bg) as bg,min(bu) as bu 
				from users 
				left join asin on users.sap_seller_id = asin.sap_seller_id 
				group by users.id 
				order by users.id desc 
			) as b on b.processor = t1.processor 
			join (
					select count(*) as num,client_id,max(t1.name) as name,max(t1.email) as email,max(t1.encrypted_email) as encrypted_email,max(t1.phone) as phone,max(t1.remark) as remark,max(t1.country) as country,max(t1.`from`) as `from`,max(t1.brand) as brand,any_value(facebook_name) as facebook_name,any_value(facebook_group) as facebook_group,max(amazon_profile_page) as amazon_profile_page  
					from client_info t1 
					left join client_order_info as t2 on t1.id = t2.ci_id 
			    left join brush_single_enails on t1.email = brush_single_enails.email
			  		{$whereInfo}
			  		group by client_id 
			) as c on t1.id = c.client_id 
			where $where 
			{$orderby} ";
		return $sql;
	}
}
