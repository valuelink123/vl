<?php
/* Date: 2019.10.15
 * Author: wulanfnag
 * 一些公共数据处理方法
 */
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use DB;
use App\User;
use App\Inbox;
use App\Rule;
use App\Exception;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct()
	{
		//计算倒计时相关天数,Black Friday=>'11.29','Cyber Monday'=>'12.2','Christmas'=>'12.24'
		$this->middleware(function ($request, $next) {
			$configArr = array(
				array('date'=>'2020-01-24','name'=>'Chinese New Year'),//春节
				array('date'=>'2020-02-14','name'=>"Valentine's Day"),//情人节
				array('date'=>'2020-05-14','name'=>"Mother's Day"),//母亲节
				array('date'=>'2020-06-18','name'=>"Father's Day"),//父亲节
				array('date'=>'2020-11-01','name'=>"All Saints' Day"),//万圣节
				array('date'=>'2020-11-23','name'=>'Thanksgiving Day'),//感恩节
				array('date'=>'2020-11-24','name'=>'Black Friday'),//黑色星期五
				array('date'=>'2020-11-27','name'=>'Cyber Monday'),//网络星期一
				array('date'=>'2020-12-25','name'=>'Christmas'),//圣诞节
			);
			$countDown = array();
			foreach($configArr as $key=>$val){
				 $days = (strtotime(date($val['date']))-strtotime(date('Y-m-d')))/86400;
				 if($days >= 0){
					 $countDown[] = array('name'=>$val['name'],'day'=>$days);
				 }
			}
			session()->put('countDown',$countDown);
			return $next($request);
		});
	}

    public function getUserId()
    {
        return Auth::user()->getAuthIdentifier();
    }

	/*
	* 限制数据权限
	* BG领导只能看该BG下的所有asin的相关数据信息
	* BU领导只能看该BU下的所有asin的相关数据信息
	* 只是销售员的话，只能看自己所属的asin相关数据信息
	*/
	public function getAsinWhere($bg = 'asin.bg',$bu = 'asin.bu',$userid = 'processor',$permission='')
	{
		$asinWhere = '';
		if(!Auth::user()->can($permission)){
			if (Auth::user()->seller_rules) {
				$rules = explode("-",Auth::user()->seller_rules);
				if(array_get($rules,0)!='*') $asinWhere .= ' AND '.$bg.' = "'.array_get($rules,0).'"';
				if(array_get($rules,1)!='*') $asinWhere .=  ' AND '.$bu.' = "'.array_get($rules,1).'"';
			} else{
				$asinWhere = ' AND '.$userid.' = '.Auth::user()->id;
			}
		}
		return $asinWhere;
	}

	//得到用户的idName键值对
	public function getUsersIdName(){
		$users = User::get()->toArray();
		$users_array = array();
		foreach($users as $user){
			$users_array[$user['id']] = $user['name'];
		}
		return $users_array;
	}
	//得到所有BG的值
	public function getBgs()
	{
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
		return $bgs;
	}
	//得到所有BU的值
	public function getBus()
	{
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
		return $bus;
	}

	/*
	 * 得到搜索数据内容的键值对
	 */
	public function getSearchData($search)
	{
		$searchData = array();
		foreach($search as $val){
			$sv = explode('=',$val);
			if(isset($sv[1])){
				if(isset($searchData[$sv[0]])){
					$searchData[$sv[0]] .= ','.trim($sv[1]);
				}else{
					$searchData[$sv[0]] = trim($sv[1]);
				}
				if($sv[0]=='date_to'){
					$searchData[$sv[0]] = trim($sv[1]).' 23:59:59';
				}
			}
		}
		return $searchData;
	}

	/*
	 * 得到搜索的where语句
	 * $searchData为搜索内容的键值对，例如array('id'=>1,'name'=>123)
	 * $searchField为搜索字段与查询字段对应关系，例如array('id'=>'a.id','name'=>'a.name')
	 * 如果有起始时间等特殊查询，可用array('date_from'=>array('>='=>'created_at'),'date_to'=>array('<='=>'created_at'));
	 */
	public function getSearchWhereSql($searchData,$searchField)
	{
		$where = '';
		foreach($searchField as $fk=>$fv){
			if(isset($searchData[$fk]) && $searchData[$fk] !=='' ){
				if(is_array($fv)){
					foreach($fv as $vk=>$vv){
						$where .= " and {$vv} {$vk} '".$searchData[$fk]."'";
					}
				}else{
					$where .= " and {$fv} = '".$searchData[$fk]."'";
				}
			}
		}
		return $where;
	}

	/*
	 * 得到item_group的键值对
	 * key为item_group，val为item_group——brand_line
	 */
	public function getItemGroup()
	{
		$data = array();
		$_data = DB::select('SELECT item_group,any_value(brand_line) as brand_line FROM asin group by item_group order by item_group asc');
		foreach($_data as $key=>$val){
			$data[$val->item_group] = $val->item_group.'——'.$val->brand_line;
		}
		return $data;
	}

	/*
	 * 得到自己未回复的邮件个数(未回复的邮件总数和超时的邮件数量)
	 */
	public function getNoreplyData()
	{
		$user_id = intval(Auth::user()->id);
		$unreply = array('inbox'=>0,'timeout'=>0);
		if($user_id){
			// $unreplyNumber = Inbox::selectRaw('count(*) as count')->where('user_id',$user_id)->where('reply',0)->value('count');
			$rules = $this->getRules();
			$unreplyArray = Inbox::where('user_id',$user_id)->where('reply',0)->get(['rule_id','date'])->toArray();
			foreach($unreplyArray as $key=>$val){
				$unreply['inbox'] ++;
				if(isset($rules[$val['rule_id']]) && time()-strtotime('+ '.$rules[$val['rule_id']],strtotime($val['date']))>0){
					$unreply['timeout'] ++;
				}
			}
		}
		return $unreply;
	}
	/*
	 * 得到RR模块自己所属的done的个数(包括done和auto done)和Canceled的数量
	 */
	public function getRRData()
	{
		$user_id = intval(Auth::user()->id);
		$rr = array('done'=>0,'cancel'=>0);
		if($user_id) {
			$rr['calcel'] = Exception::selectRaw('count(*) as count')->where('user_id',$user_id)->where('process_status','cancel')->value('count');
			$rr['done'] = Exception::selectRaw('count(*) as count')->where('user_id',$user_id)->whereIn('process_status',array('done','auto done'))->value('count');
		}
		return $rr;
	}
	/*
	 * 得到各个参数超时配置的时间
	 */
	public function getRules(){
		$rules = Rule::get()->toArray();
		$rules_array = array();
		foreach($rules as $rule){
			$rules_array[$rule['id']] = trim($rule['timeout']);
		}
		return $rules_array;
	}
	
	public function getUserSellerPermissions(){
		$userRole = User::find(Auth::user()->id)->roles->pluck('id')->toArray();
		if(in_array(28,$userRole)) return ['bg'=>Auth::user()->ubg];
		if(in_array(15,$userRole)) return ['bg'=>Auth::user()->ubg,'bu'=>Auth::user()->ubu];
		if(in_array(16,$userRole)) return ['bg'=>Auth::user()->ubg,'bu'=>Auth::user()->ubu];
		if(in_array(11,$userRole)) return ['bg'=>Auth::user()->ubg,'bu'=>Auth::user()->ubu,'sap_seller_id'=>intval(Auth::user()->sap_seller_id)];
		return [];
	}

    //通过站点显示账号，ajax联动
	public function showTheAccountBySite()
	{
		$marketplaceid = isset($_REQUEST['marketplaceid']) ? $_REQUEST['marketplaceid'] : '';
		$return = array('status'=>1,'data'=>array()) ;
		if($marketplaceid){
			$data= DB::connection('vlz')->select("select id,label from seller_accounts where deleted_at is NULL and mws_marketplaceid = '{$marketplaceid}' order by label asc");
			foreach($data as $key=>$val){
				$return['data'][$key] = (array)$val;
			}
		}else{
			$return['status'] = 0;
		}
		return $return;
	}

    //查询该站点的最近一条item_price_amount>0的item_price_amount金额,为了替换掉状态为pending并且item_price_amount=0的金额数据\
	public function insertTheAsinPrice($site)
	{
		//查询该站点的最近一条item_price_amount>0的item_price_amount金额,为了替换掉状态为pending并且item_price_amount=0的金额数据\
		$date = date('Y-m-d');//当前日期
		//查询当前站点今天是否有价格的数据
		$priceData = DB::connection('vlz')->select("select seller_account_id,asin,price from asin_price where marketplace_id = '".$site."' and created_at = '".$date."' group by seller_account_id,asin");
		//当前站点今天还没有数据的话，查询到要插入的数据，更新asin_price表
		if(empty($priceData)) {
			DB::connection('vlz')->table('asin_price')->where('marketplace_id',$site)->delete();//没有此站点今天的数据就把此站点以前的数据删除掉
			$insert_sql = "select a.asin as asin,ROUND((b.item_price_amount/b.quantity_ordered),2) as price,a.seller_account_id as seller_account_id,'" . $site . "' as marketplace_id,'" . $date . "' as created_at  
    from(select asin,max(id) as id,seller_account_id 
                    from order_items
                    where item_price_amount>0 and quantity_ordered>0 
                    and order_items.asin in( select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku   where marketplace_id  = '" . $site . "')
                    group by asin,seller_account_id 
                ) as a,order_items as b
        where a.id = b.id";
			$insertData = DB::connection('vlz')->select($insert_sql);
			$insertData = array_map('get_object_vars', $insertData);//需要插入的数据
			if ($insertData) {
				DB::connection('vlz')->table('asin_price')->insert($insertData);
			}
		}
		return true;
	}
	/*
     * 得到22周日期
     */
	public function get22WeekDate($date='')
	{
		$date = isset($_POST['date']) && $_POST['date'] ? $_POST['date'] : $date;
		$data = array();
		$day = date('w',strtotime($date));
		$monday = strtotime($date) - (($day == 0 ? 7 : $day) - 1) * 24 * 3600;//本周一的日期
		$data[] = date('W',$monday).'周<BR/>'.date('Y-m-d', $monday);//加上本周数据
		for($i=1;$i<=22;$i++){
			$data[] = date('W',strtotime($date.' +'.$i.' week last monday')).'周<BR/>'.date('Y-m-d',strtotime($date.' +'.$i.' week last monday'));
		}
		return $data;
	}
	/*
	 * 销售在页面上填写asin，ajax检测是否属于自己的asin
	 */
	public function checkAsin()
	{
		$userdata = Auth::user();
		$return = array('status'=>0,'msg'=>'Please fill in your own ASIN');
		$asin = isset($_POST['asin']) && $_POST['asin'] ? $_POST['asin'] : '';
		if(empty($asin)){
			return $return;
		}
		$site = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : '';
		$siteUrl = getSiteUrl();
		$url = isset($siteUrl[$site]) && $siteUrl[$site] ? 'www.'.$siteUrl[$site] : $site;
		$where = " where asin = '".$asin."' and site = '".$url."'";
		if ($userdata->seller_rules) {
			$rules = explode("-", $userdata->seller_rules);
			if (array_get($rules, 0) != '*') $where .= " and bg = '".array_get($rules, 0)."'";
			if (array_get($rules, 1) != '*') $where .= " and bu = '".array_get($rules, 1)."'";
		}elseif($userdata->sap_seller_id){
			$where .= " and sap_seller_id = ".$userdata->sap_seller_id;
		}
		$sql = 'select asin from asin '.$where .' limit 1';
		$user_asin_list_obj = DB::select($sql);
		$user_asin_list = (json_decode(json_encode($user_asin_list_obj), true));
		if($user_asin_list){
			$return = array('status'=>1,'msg'=>'success');
		}
		return $return;
	}
}
