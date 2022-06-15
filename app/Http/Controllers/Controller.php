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
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
	 * $permission为ctg-show-all，non-ctg-show-all权限,有查看所有数据权限的人就不做限制，
	*/
	public function getAsinWhere($bg = 'asin.bg',$bu = 'asin.bu',$userid = 'processor',$permission='')
	{
		$asinWhere = '';
		if(!Auth::user()->can($permission) || $permission==''){
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
		$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : 'id';
		$return = array('status'=>1,'data'=>array()) ;
		if($marketplaceid){
			$data= DB::connection('amazon')->select("select {$field} as id,label from seller_accounts where deleted_at is NULL and mws_marketplaceid = '{$marketplaceid}' order by label asc");
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
		$priceData = DB::connection('amazon')->select("select seller_account_id,asin,max(price) AS price from asin_price where marketplace_id = '".$site."' and created_at = '".$date."' group by seller_account_id,asin");
		//当前站点今天还没有数据的话，查询到要插入的数据，更新asin_price表
		if(empty($priceData)) {
			DB::connection('amazon')->table('asin_price')->where('marketplace_id',$site)->delete();//没有此站点今天的数据就把此站点以前的数据删除掉
			$insert_sql = "select a.asin as asin,ROUND((b.item_price_amount/b.quantity_ordered),2) as price,a.seller_account_id as seller_account_id,'" . $site . "' as marketplace_id,'" . $date . "' as created_at  
    from(select asin,max(id) as id,seller_account_id 
                    from order_items
                    where item_price_amount>0 and quantity_ordered>0 
                    and order_items.asin in( select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku   where marketplace_id  = '" . $site . "')
                    group by asin,seller_account_id 
                ) as a,order_items as b
        where a.id = b.id";

			$_insertData = DB::connection('amazon')->select($insert_sql);
			$insertData = array();
			foreach($_insertData as $key=>$val){
				$insertData[$val->seller_account_id.'_'.$val->asin] = (array)$val;
			}

			//查询listing的价格
			$today = date('Y-m-d');
//			$today = '2021-12-03';//测试数据
			//$insertData里面可能有缺了asin的价格，所以再查listing价格表，没有的asin价格给他补上
			$listing_price_sql = "SELECT ASIN as asin,asin_offer_lowest.listing_price as price,asin_offer_summary.marketplace_id AS marketplace_id,'" . $date . "' as created_at,seller_accounts.id AS  seller_account_id
FROM asin_offer_summary
LEFT JOIN asin_offer_lowest ON asin_offer_summary.id = asin_offer_lowest.asin_offer_summary_id 
LEFT JOIN asin_offers ON asin_offer_summary.id = asin_offers.asin_offer_summary_id 
LEFT JOIN seller_accounts ON seller_accounts.mws_seller_id = asin_offers.seller_id AND mws_marketplaceid = '".$site."' 
where asin_offer_lowest.fulfillment_channel ='Amazon' AND asin_offer_summary.date = '".$today."' and asin_offer_summary.marketplace_id = '".$site."' 
AND asin_offers.seller_id IS NOT NULL AND seller_accounts.id IS NOT NULL 
ORDER BY asin_offer_summary.asin DESC ";
			$_price_data = DB::connection('amazon')->select($listing_price_sql);
			$_price_data = json_decode(json_encode($_price_data),true);
			foreach($_price_data as $key=>$val){
				if(!isset($insertData[$val['seller_account_id'].'_'.$val['asin']])){
					$insertData[$val['seller_account_id'].'_'.$val['asin']] = $val;
				}
			}

			if ($insertData) {
				$insertData = array_values($insertData);
				DB::connection('amazon')->table('asin_price')->insert($insertData);
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
	//得到当前时间戳
	public function getCurrentTime($site,$timeType)
	{
		//如果选的时间类型是后台当地时间，时间要做转化
		$dateconfig = array('A1PA6795UKMFR9','A1RKKUPIHCS9HS','A13V1IB3VIYZZH','APJ6JRA9NG5V4');//utc+2:00
		$time = time();//北京时间当前时间戳
//		 $time = strtotime('2020-10-2 23:01:00');//测试日期
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//时间范围+1小时,日本站点,-8+9
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("+1 hour", $time)));//日本站后台当前时间;
			}elseif($site=='A1F83G8C2ARO7P'){//英国站点+1小时，uTc+1:00,-8+1
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("-7 hour", $time)));//英国站后台当前时间;
			}elseif(in_array($site,$dateconfig)){//utc+2:00,-8+2
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("-6 hour", $time)));//几个特殊的站点
			}else{//时间范围-15小时,-8-7
				$time =  strtotime(date('Y-m-d H:i:s', strtotime ("-15 hour", $time)));//美国站后台当前时间;
				$res = $this->isWinterTime($time);//判断美国是否冬令制时间
				//美国Amazon时间冬令制时间和北京时间相差16个小时。夏令制相差15个小时
				if($res==1){//冬令制时间
					$time =  strtotime(date('Y-m-d H:i:s', strtotime ("-1 hour", $time)));
				}
			}
		}
		return $time;
	}

	//判断是否进入冬令制时间,   夏令时（3月第二个星期天至11月第一个星期天），冬令时（11月8日至次年3月11日(包含)）
	function isWinterTime($time){
		$WinterMonth = array(12,1,2);//冬令时月份
		$SummberMonth = array(4,5,6,7,8,9,10);//夏令时月份
		$oneDay = date('Y-m-01', $time);//本月第一天
		$toDay = date('d', $time);//今天是多少号，是本月的第几天
		#$tolDay = date('d', strtotime("$oneDay +1 month -1 day"));//本月天数
		$week = date('w',strtotime($oneDay));//本月第一天是星期几
		$month = date('m',$time);//今天的月份

		if(in_array($month,$WinterMonth)){//冬令时
			return 1;
		}
		if(in_array($month,$SummberMonth)){//夏令时
			return 0;
		}

		if($month==11){
			if($week==0){//本月第一天是星期天
				if($toDay > 1){
					return 1;
				}
			}else{
				if((8-$week) < $toDay){
					return 1;
				}
			}
		}

		if($month==3){
			if($week==0){
				if((8-$week) >= $toDay){
					return 1;
				}
			}else{
				if((15-$week) >= $toDay){
					return 1;
				}
			}
		}
		return 0;
	}

	/*
	 * 通过订单号得到mcf的订单信息，sap接口需要的参数为$order和$orderitems两个数组
	 * 参数：$mcfOrderId   amazon_mcf_orders表中的seller_fulfillment_order_id字段
	 */
	function getMcfOrderByMcfOrderId($mcfOrderId){
		$marketplaceCode =	getMarketplaceCode();
		$MarketplaceSiteCode = matchMarketplaceSiteCode();//marketplace_id跟亚马逊平台id的对应关系
		//得到asin,sku,bg,bu,seller信息的数据处理
		$detail_sql = "select CONCAT(seller_accounts.id,'_',sap_asin_match_sku.seller_sku) as sai_ss,sap_asin_match_sku.asin as asin, sap_asin_match_sku.sku as sku,marketplace_id,sap_seller_id,sap_warehouse_code,sap_factory_code,sap_shipment_code,asins.title as title,sap_asin_match_sku.seller_id 
                FROM sap_asin_match_sku 
                left join asins on asins.asin = sap_asin_match_sku.asin and asins.marketplaceid = sap_asin_match_sku.marketplace_id
                left join seller_accounts on seller_accounts.mws_seller_id = sap_asin_match_sku.seller_id and seller_accounts.mws_marketplaceid=sap_asin_match_sku.marketplace_id";
		$_detailData = DB::connection('amazon')->select($detail_sql);
		$_detailData=json_decode(json_encode($_detailData), true);
		$detailData = array();
		foreach($_detailData as $dk=>$dv){
			$detailData[$dv['sai_ss']] = $dv;
		}
		$order = $orderitems = array();
		$_orderData = DB::connection('amazon')->table('amazon_mcf_orders')->where('seller_fulfillment_order_id',$mcfOrderId)->first();
		$_orderitemsData = DB::connection('amazon')->table('amazon_mcf_orders_item')->where('seller_fulfillment_order_id',$mcfOrderId)->get();
		$data['status'] = 0;
		$data['message'] = '';
		if($_orderData && $_orderitemsData) {
			$data['status'] = 1;
			//获取sap_seller_id
			$sap_seller_id = $sku = $sap_factory_code = $sap_warehouse_code = $title = $currency = $sap_shipment_code = $site = $seller_id = '';
			$i = 0;
			foreach ($_orderitemsData as $_orderitem) {
				$sai_ss = $_orderitem->seller_account_id.'_'.$_orderitem->seller_sku;
				if(isset($detailData[$sai_ss])){
					$sap_seller_id = $detailData[$sai_ss]['sap_seller_id'];
					$sku = $detailData[$sai_ss]['sku'];
					$sap_factory_code = $detailData[$sai_ss]['sap_factory_code'];

					$sap_warehouse_code = $detailData[$sai_ss]['sap_warehouse_code'];
					$title = $detailData[$sai_ss]['title'];
					$currency = isset($marketplaceCode[$detailData[$sai_ss]['marketplace_id']]['currency_code']) ? $marketplaceCode[$detailData[$sai_ss]['marketplace_id']]['currency_code'] : '';
					$sap_shipment_code = $detailData[$sai_ss]['sap_shipment_code'];
					$site = isset($MarketplaceSiteCode[$detailData[$sai_ss]['marketplace_id']]) ? $MarketplaceSiteCode[$detailData[$sai_ss]['marketplace_id']] : '';//交易站点
					$seller_id = $detailData[$sai_ss]['seller_id'];
				}
				$i++;
				$orderitems[] = array(
					'BSTKD' => $_orderData->amazon_order_id,//平台订单号
					'PARVW_SE' => $sap_seller_id,//人员编号，sap_seller_id
					'POSNR' => $i,//项目
					'ORDERLINEITEMID' => 'VOP',//平台行项目号
					'MATNR' => $sku,//物料号
					'KWMENG' => $_orderitem->quantity,////订单数量
					'WERKS' => $sap_factory_code,////工厂
					'LGORT' => $sap_warehouse_code,//库存地点
					'ZPR1' => '0.00',//产品单价
					'ZKF2' => '0.00',//二程运费
					'ZKF1' => '0.00',//项目运费
					'ZJY1' => '0.00',//佣金
					'ZPJ1' => '0.00',//成交费
					'ZMWI' => '0.00',//平台税款
					'ZCZ3' => '0.00',//操作费
					'SELLER_SKU' => $_orderitem->seller_sku,//平台sku
					'TITLE' => $title,//帖子标题
					'WAERK' => $currency,//凭证货币
					'WAERSF' => $currency,//货币
				);
			}
			$order[] = array(
				'BSTKD' => $_orderData->amazon_order_id,//平台订单号
				'PARVW_SE' => $sap_seller_id,//sap_seller_id人员编号
				'BSTDK' => date('Ymd',strtotime($_orderData->displayable_order_date_time)),//平台订单创建日期
				'AUART' => 'ZRR1',//销售凭证类型
				'VKORG' => '2000',//销售组织
				'VTWEG' => '11',//分销渠道
				'SPART' => '00',//产品组
				'VKBUR' => $site,//交易站点
				'KUNNR' => $seller_id,//售达方
				'KUNWE' => 'AAAAAA',//客户
				'ZCJRQ' => date('Ymd'),//创建日期
				'SDABW' => $sap_shipment_code,//实际运输方式
				'UNAME' => 'VOP',//用户名
				'ORDERID' => $_orderData->seller_fulfillment_order_id,//平台订单号（重发单）
				'PAYMENTID' => $_orderData->seller_fulfillment_order_id,//付款交易ID
				'NAME1' => $_orderData->name,//客户姓名
				'LAND1' => $_orderData->country_code,//国家
				'LAND2' => $_orderData->country_code,//国家
				'CITY1' => $_orderData->city,//城市
				'STATEORPROVINCE' => $_orderData->state_or_region,//州/省
				'STREET' => $_orderData->address_line_1,//地址
				'STREET2' => $_orderData->address_line_2 . ' ' . $_orderData->address_line_3,//地址2
				'POSTALCODE' => $_orderData->postal_code,//邮编
				'PHONE1' => $_orderData->phone//电话
			);

			$data['data'] = array('order' => json_encode($order), 'orderitems' => json_encode($orderitems));
		}else{
			$data['message'] = 'Data Empty';
		}
		return $data;
	}

	function getAccountInfo()
	{
		$account = array();
		$_account= DB::connection('amazon')->select("select id,label from seller_accounts where deleted_at is NULL order by label asc");
		foreach($_account as $key=>$val){
			$account[$val->id] = (array)$val;
		}
		return $account;
	}

	//得到ppc模块搜索时间的sql
	public function getPpcDateWhere()
	{
		$startDate = date('Y-m-d',strtotime($this->start_date));//开始时间
		$endDate = date('Y-m-d',strtotime($this->end_date));//结束时间
		$where = " and ppc_report_datas.date >= '".$startDate."' and ppc_report_datas.date <= '".$endDate."'";
		return $where;
	}
	/*
	 * 通过选择的站点得到该站点下的所有账号信息
	 */
	public function getPpcAccountByMarketplace($marketplace)
	{
		$sql = "select accounts.id,accounts.seller_id 
				from accounts
				left join marketplaces on accounts.marketplace_id = marketplaces.id 
    			where user_id = 8566 and marketplaces.marketplace = '".$marketplace."'";
		$_account = DB::connection('ad')->select($sql);
		$account = array();
		foreach($_account as $key=>$val){
			$account[$val->id] = $val->seller_id;
		}
		return $account;
	}

	/*
	 * 通过sku和日期，从sap接口处获得这些sku的库存数据
	 */
	public function getSkuInventoryBySapApi($skus,$start_date,$end_date)
	{
		//获取sap接口
		$skus_str = json_encode($skus);
		$array_detail['appid'] = env("SAP_KEY");
		$array_detail['method'] = 'getSkusStock';
		$array_detail['gt_table'] = $skus_str;

		$sign = $this->getSapApiSign($array_detail);
		$_sap_data = file_get_contents('http://' . env("SAP_RFC") . '/rfc_sap_api.php?appid=' . env("SAP_KEY") . '&method='.$array_detail['method'].'&skus='.$array_detail['gt_table'].'&start_date='.$start_date.'&end_date='.$end_date.'&sign=' . $sign);
		$_sap_data = json_decode($_sap_data,true);
		$sap_inventory_data = array();
		foreach($_sap_data['RESULT_TABLE'] as $key=>$val){
			$sap_inventory_data[$val['MATNR']][$val['WERKS'].'_'.$val['LGORT']] = $val;
		}
		return $sap_inventory_data;
	}

	/*
	 * 通过选择的站点账号得到Campaign,ajax联动
	 */
	public function getCampaignBySiteAccount()
	{
		$marketplaceid = isset($_REQUEST['marketplaceid']) ? $_REQUEST['marketplaceid'] : '';
		$account = isset($_REQUEST['account']) ? $_REQUEST['account'] : '';
		$return = array('status'=>1,'data'=>array());
		if($marketplaceid){
			$sql_profileId = "SELECT profile_id FROM ppc_profiles WHERE marketplace_id = '".$marketplaceid."'";
			if($account){
				if(is_array($account)){
					$account_str = "'".implode("','", $account)."'";
				}else{
					$account_str = "'".$account."'";
				}
				$sql_profileId .= " AND seller_id IN(".$account_str.")";
			}
			$sql = "SELECT campaign_id,name FROM (
						SELECT campaign_id,name,profile_id FROM ppc_sbrands_campaigns 
						UNION ALL 
						SELECT campaign_id,name,profile_id FROM ppc_sdisplay_campaigns 
						UNION ALL 
						SELECT campaign_id,NAME,profile_id FROM ppc_sproducts_campaigns 
					) AS campaigns WHERE profile_id IN (".$sql_profileId.") order by name asc";
			$data= DB::select($sql);
			foreach($data as $key=>$val){
				$return['data'][$key] = (array)$val;
			}
		}else{
			$return['status'] = 0;
		}
		return $return;
	}

	/*
	 * 通过选择的campaign得到griup,ajax联动
	 */
	public function getGroupBySiteCampaign()
	{
		$marketplaceid = isset($_REQUEST['marketplaceid']) ? $_REQUEST['marketplaceid'] : '';
		$account = isset($_REQUEST['account']) ? $_REQUEST['account'] : '';
		$campaign = isset($_REQUEST['campaign']) ? $_REQUEST['campaign'] : '';
		$return = array('status'=>1,'data'=>array());
		if($campaign){
			$sql = "SELECT ad_group_id,name FROM (
						SELECT ad_group_id,name,campaign_id FROM ppc_sbrands_ad_groups 
						UNION ALL 
						SELECT ad_group_id,name,campaign_id FROM ppc_sdisplay_ad_groups 
						UNION ALL 
						SELECT ad_group_id,NAME,campaign_id FROM ppc_sproducts_ad_groups 
					) AS ad_group WHERE campaign_id = {$campaign} order by name asc";
			$data= DB::select($sql);
			foreach($data as $key=>$val){
				$return['data'][$key] = (array)$val;
			}
		}else{
			$return['status'] = 0;
		}
		return $return;
	}
	/*
	 *
	 */
	public function getDataBySiteCampaign()
	{
		$campaign = isset($_REQUEST['campaign']) ? $_REQUEST['campaign'] : '';
		$return = array('status'=>1,'data'=>array());
		if($campaign){
			$sql = "SELECT type,name,profile_id FROM (
						SELECT 'sbrands' as type,'sbrands' as name,campaign_id,profile_id FROM ppc_sbrands_campaigns 
						UNION ALL 
						SELECT 'sdisplay' as type,'sdisplay' as name,campaign_id,profile_id FROM ppc_sdisplay_campaigns 
						UNION ALL 
						SELECT 'sproducts' as type,'sproducts' as name,campaign_id,profile_id FROM ppc_sproducts_campaigns 
					) AS ad_type WHERE campaign_id = {$campaign} order by name asc";
			$data= DB::select($sql);
			foreach($data as $key=>$val){
				$return['data'][$key] = (array)$val;
			}
		}else{
			$return['status'] = 0;
		}
		return $return;
	}

	/*
	 * 通过选中的站点得到某个asin的销售员
	 */
	public function getAsinInfoBySite($site)
	{
		$asin_sql = "select * from sap_asin_match_sku where marketplace_id = '{$site}'";
		$_asinData = DB::connection('vlz')->select($asin_sql);
		foreach($_asinData as $key=>$val){
			$asinData[$val->seller_id.'_'.$val->asin] = (array)$val;
		}
		return $asinData;
	}
	/*
	 * 通过asin得到相对应的asin信息，例如sku，所属的销售员等等
	 */
	public function  asinMatchSkuDataByAsin()
	{
		$sap_seller = getUsers('sap_seller');
		$sku = $sellers = array();

		if ($_POST['marketplace_id'] && $_POST['seller_id'] && $_POST['seller_id']){
			$asinMatchSkuData = DB::connection('amazon')->table('sap_asin_match_sku')->where('marketplace_id', trim($_POST['marketplace_id']))->where('seller_id', trim($_POST['seller_id']))->where('asin', trim($_POST['asin']))->get();
			foreach ($asinMatchSkuData as $key => $val) {
				$sku[$key] = $val->sku;
				$sellers[$val->sap_seller_id] = isset($sap_seller[$val->sap_seller_id]) && $sap_seller[$val->sap_seller_id] ? $sap_seller[$val->sap_seller_id] : 'N/A';
			}
		}
		return json_encode(array('sku'=>$sku,'sellers'=>$sellers));
	}

	/*
	 * 得到物料对照关系表里的信息
	 */
	public function getSapAsinMatchSkuInfo($site='',$bg='',$bu='')
	{
		$userWhere = ' where 1=1 ';
		if($site){
			$userWhere .= " and marketplace_id='".$site."'";
		}
		if($bg){
			$userWhere .= " and sap_seller_bg='".$bg."'";
		}
		if($bu){
			$userWhere .= " and sap_seller_bu='".$bu."'";
		}
		$asin_sql = "select marketplace_id,seller_id,asin,sap_seller_bg,sap_seller_bu,sap_seller_id,sku from sap_asin_match_sku {$userWhere}
					UNION ALL 
					select marketplace_id,seller_id,asin,sap_seller_bg,sap_seller_bu,sap_seller_id,sku from asin_match_relation {$userWhere}";
		$_asinData = DB::connection('vlz')->select($asin_sql);
		foreach($_asinData as $key=>$val){
			$asinData[$val->marketplace_id.'_'.$val->seller_id.'_'.$val->asin] = (array)$val;
		}
		return $asinData;
	}

	/*
	 * 得到物料对照关系表里的信息
	 */
	public function getSapAsinMatchSkuInfoTo($site='',$bg='',$bu='')
	{
		$userWhere = ' where 1=1 ';
		if($site){
			$userWhere .= " and marketplace_id='".$site."'";
		}
		if($bg){
			$userWhere .= " and sap_seller_bg='".$bg."'";
		}
		if($bu){
			$userWhere .= " and sap_seller_bu='".$bu."'";
		}
		$sku_sql = "select marketplace_id,seller_id,asin,sap_seller_bg,sap_seller_bu,sap_seller_id,sku from sap_asin_match_sku {$userWhere}
					UNION ALL
					select marketplace_id,seller_id,asin,sap_seller_bg,sap_seller_bu,sap_seller_id,sku from asin_match_relation {$userWhere}";
		$_skuData = DB::connection('vlz')->select($sku_sql);
		foreach($_skuData as $key=>$val){
			$skuData[$val->marketplace_id.'_'.$val->seller_id.'_'.$val->sku] = (array)$val;
		}
		return $skuData;
	}

	public function getBg($bg='')
	{
		if($bg){
			$sql = "SELECT DISTINCT sap_seller_bg as bg FROM sap_asin_match_sku where sap_seller_bg='".$bg."' order By sap_seller_bg asc";
		}else{
			$sql = 'SELECT DISTINCT sap_seller_bg as bg FROM sap_asin_match_sku order By sap_seller_bg asc';
		}
		$bgs = DB::connection('vlz')->select($sql);
		$bgs = json_decode(json_encode($bgs),true);
		return $bgs;
	}
	public function getBu($bg='',$bu = '')
	{
		$sql  ="SELECT sap_seller_bg AS bg, sap_seller_bu as bu FROM sap_asin_match_sku where 1=1";
		if($bg){
			$sql .= " and sap_seller_bg='".$bg."'";
		}
		if($bu){
			$sql .= " and sap_seller_bu='".$bu."'";
		}
		$sql .= " GROUP BY sap_seller_bg,sap_seller_bu order By sap_seller_bu asc";
		$bus = DB::connection('vlz')->select($sql);
		$bus = json_decode(json_encode($bus),true);
		return $bus;
	}

	//得到搜索时间的sql,ccp相关模块
	public function getCcpDateWhere($site,$timeType,$start_date,$end_date)
	{
//		$dateRange = $this->getDateRange();
		$startDate = date('Y-m-d 00:00:00',strtotime($start_date));//开始时间
		$endDate = date('Y-m-d 23:59:59',strtotime($end_date));//结束时间
//		$startDate = $dateRange['startDate'];
//		$endDate = $dateRange['endDate'];
		$date_field = 'purchase_date';
		$dateconfig = array('A1PA6795UKMFR9','A1RKKUPIHCS9HS','A13V1IB3VIYZZH','APJ6JRA9NG5V4');//utc+2:00
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//日本站点，date字段+9hour
				$date_field = 'date_add(purchase_date,INTERVAL 9 hour) ';
			}elseif($site=='A1F83G8C2ARO7P'){//英国站点+1小时，uTc+1:00
				$date_field = 'date_add(purchase_date,INTERVAL 1 hour) ';
			}elseif(in_array($site,$dateconfig)){//站点+2小时，utc+2:00
				$date_field = 'date_add(purchase_date,INTERVAL 2 hour) ';
			}else{//其他站点，date字段-7hour
				$date_field = 'date_sub(purchase_date,INTERVAL 7 hour) ';
			}
		}else{//北京时间加上8小时
			$date_field = 'date_add(purchase_date,INTERVAL 8 hour) ';
		}
		$where = " and {$date_field} BETWEEN STR_TO_DATE( '".$startDate."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$endDate."', '%Y-%m-%d %H:%i:%s' )";
		return $where;
	}

	//得到用户的权限数据查询语句，根据sap_asin_match_sku去查数据
	public function getUserAsin($site,$bg,$bu,$field='asin_sku')
	{
		$ccpAdmin = $this->getccpAdmin();
		$userdata = Auth::user();
		$userWhere = " where marketplace_id  = '".$site."'";
		if (!in_array($userdata->email, $ccpAdmin)) {
			if ($userdata->seller_rules) {
				//bg总监或者bu经理
				$rules = explode("-", $userdata->seller_rules);
				if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '" . array_get($rules, 0) . "'";
				if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '" . array_get($rules, 1) . "'";
			} elseif ($userdata->sap_seller_id) {//普通销售员
				$userWhere .= " and sap_seller_id = " . $userdata->sap_seller_id;
			}
		}

		if($bg){
			$userWhere .= " and sap_seller_bg = '".$bg."'";
		}
		if($bu){
			$userWhere .= " and sap_seller_bu = '".$bu."'";
		}
		$sql = "select DISTINCT sap_asin_match_sku.asin as asin,CONCAT(sap_asin_match_sku.asin,'_',sap_asin_match_sku.seller_sku) as asin_sku from sap_asin_match_sku {$userWhere}
					UNION ALL 
					select DISTINCT asin_match_relation.asin as asin,CONCAT(asin_match_relation.asin,'_',asin_match_relation.seller_sku) as asin_sku from asin_match_relation {$userWhere}";
		$_asin = DB::connection('vlz')->select($sql);
		$asin = array();
		foreach($_asin as $key=>$val){
			if(strlen($val->asin)==10){
				$asin[] = $val->$field;
			}

		}
		return $asin;
	}

	public function getccpAdmin()
	{
		$ccpAdmin = array("zhangjianqun@valuelinkcorp.com","sunhanshan@valuelinkcorp.com","lixiaojian@valuelinkltd.com","wulanfang@valuelinkcorp.com","chenguancan@valuelinkcorp.com");
		return $ccpAdmin;
	}

	/*
     * 客服系统添加异常单同步到VOP系统
	 * 接口数据
     */
	public function interfaceAddException(){
		$params = isset($_POST['params']) && $_POST['params'] ? $_POST['params'] : '';
		Log::info('interfaceAddException-data:'.(isset($params)?$params:""));
		$data = json_decode($params,true);
		$configField = array('name','date','sellerid','process_date','amazon_order_id','refund','gift_card_amount','currency','customer_email','replacement','user_id','group_id','process_user_id','process_status','request_content','process_content','order_sku','process_attach','replacement_order_id','descrip','score','auto_create_mcf','auto_create_mcf_result','last_auto_create_mcf_date','last_auto_create_mcf_log','comment','update_status_log','saleschannel','asin','auto_create_sap','auto_create_sap_result','last_auto_create_sap_date','last_auto_create_sap_log','amount','file_url');
		//service_system_id
		$return['status'] = 0;
		$return['id'] = 0;
		$return['msg'] = '';
		if(!(isset($data['id']) && $data['id'])){
			$return['msg'] = '请传必填参数id';
			return json_encode($return);
		}
		if(!(isset($data['type']) && $data['type'])){
			$return['msg'] = '请传必填参数type';
			return json_encode($return);
		}
		if(!(isset($data['process_status']) && $data['process_status'])){
			$return['msg'] = '请传必填参数process_status';
			return json_encode($return);
		}
		$insertData = array();
		foreach($configField as $field){
			if(isset($data[$field]) && $data[$field]){
				$insertData[$field] = $data[$field];
			}
		}
		if($insertData){
			$res = Exception::updateOrCreate(
					['service_system_id' => $data['id'],'type'=>$data['type']],
					$insertData
				);
			if(!empty($res)){
				$return['status'] = 1;
				$return['id'] = $res->id;
				$return['msg'] = '数据对接成功';
			}
		}
		return json_encode($return);
	}

	/*
	 * 当VOP系统中的异常单是客服系统那边对接过来的话(service_system_id这个字段有数据说明是客服系统对接过来的异常单)，在VOP系统更改异常单的时候，调用客服系统的接口，由客服系统去更改客服系统异常单的数据表
	 * 1，客服系统接口名：http://16.163.26.169/api/exception/webhook，此为线上的客服系统地址，本地测试的时候，请关闭此对接
	 * 2，参数名：params
	 * 3，参数数据格式：为exception表中所有字段的json字符串，例如：{"id":101,"type":2,"name":"aa"}
	 * 4，提交方式：POST提交
	 *
	 */
	public function exceptionPushServiceSystem($exceptionId)
	{
//		return true;
		$data = Exception::findOrFail($exceptionId);
		//if($data['service_system_id']>0) {
			$params = json_encode($data);
			curl_request('http://www.onecustomerme.com/api/exception/webhook',['params'=>$params]);
			//file_get_contents('http://www.onecustomerme.com/api/exception/webhook?params=' . $params);
		//}
		
		return true;
	}

	/*
	 * 导出excel表格函数
	 */
	public function exportExcel($arrayData,$fileName)
	{
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
			header('Content-Disposition: attachment;filename="'.$fileName.'"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

}
