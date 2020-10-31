<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Asin;
use App\User;
use App\Review;
use App\Accounts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use App\Classes\CurlRequest;
use DB;


class CcpController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

	public $ccpAdmin = array("xumeiling@valuelinkcorp.com");
	public $date = '';

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
		if(!Auth::user()->can(['ccp-show'])) die('Permission denied -- ccp show');
		$where = ' where 1 = 1';
		$userdata = Auth::user();
		if (!in_array($userdata->email, $this->ccpAdmin)) {
			if ($userdata->seller_rules) {
				$rules = explode("-", $userdata->seller_rules);
				if (array_get($rules, 0) != '*') $where .= " and bg = '" . array_get($rules, 0) . "'";
				if (array_get($rules, 1) != '*') $where .= " and bu = '" . array_get($rules, 1) . "'";
			} elseif ($userdata->ubg && $userdata->ubu) {
				$where .= " and bg = '" . $userdata->ubg . "' and bu = '" . $userdata->ubu . "'";
			}
		}
		$bgbu= DB::select('select bg,bu from asin '.$where.' group by bg,bu ORDER BY BG ASC,BU ASC');//获取bgbu选项
		$site = getMarketDomain();//获取站点选项
		$date = date('Y-m-d');
		return view('ccp/index',['bgbu'=>$bgbu,'site'=>$site,'date'=>$date]);
	}
	/*
	*获取mws后台总统计数据的方法
	 * 各字段说明
	 * sales=>时间段内总的销售额(orders.amount)
	 * revenue=>销售额-促销金额(order_items.promotion_discount_amount)
	 * units=>产品的单位（order_items.quantity_ordered)相加
	 * units-full=>全价产品数量
	 * units-promo=>促销产品数量order_items.promotion_ids
	 * orders=>订单数量
	 * orders-full=>全价订单的数量
	 * orders-promo=>促销订单的数量
	 * avg-price=>平均价格,sales/units
	 * stock-value=>
	 */
	public function showTotal()
	{
	    //搜索条件，统计数据不受下面的asin搜索的影响
        $search = isset($_REQUEST['search_data']) ? $_REQUEST['search_data'] : '';
        $search = $this->getSearchData(explode('&',$search));
        $date_type = isset($search['date_type']) ? $search['date_type'] : '';//选的时间类型
        $site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
        $account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
        $bgbu = isset($search['bgbu']) ? $search['bgbu'] : '';//bgbu,例如BG1_BU4
		$timeType = isset($search['timeType']) ? $search['timeType'] : '';//时间类型，默认是0为北京时间，1为亚马逊后台当地时间
		$this->date = isset($search['date']) ? $search['date'] : '';//date搜索框的值
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$siteCur = getSiteCur();
		$currency_code = isset($siteCur[$domain]) ? $siteCur[$domain] : '';

		$where = $orderwhere = $this->getDateWhere($date_type,$site,$timeType);
		//$account搜索两个表的字段都为seller_account_id
		if($account){
			$where = $orderwhere .= ' and order_items.seller_account_id in('.$account.')';
		}
		$orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
		//用户权限sap_asin_match_sku
		$userwhere = $this->getUserWhere($site,$bgbu);
		//保证asin_price此站点今天有数据
		$this->insertTheAsinPrice($site);

		//sales数据，orders数据
		$sql ="SELECT SUM( item_price_amount ) AS sales,SUM( quantity_ordered ) AS units,SUM( quantity_ordered * PROMO ) AS unitsPromo,COUNT( DISTINCT amazon_order_id ) AS orders,COUNT(DISTINCT PROMO_ORDER_ID) AS ordersPromo,sum(c_promotionAmount) as promotionAmount 
			FROM
			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount,
			   		LENGTH( order_items.promotion_ids )> 10 AS PROMO,promotion_discount_amount as c_promotionAmount,
			  		CASE WHEN LENGTH( order_items.promotion_ids )> 10 THEN amazon_order_id ELSE '' END AS PROMO_ORDER_ID  
			  	FROM order_items 
				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."' 
			  	WHERE order_items.amazon_order_id IN 
				  (
					SELECT amazon_order_id 
					FROM orders 
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwhere}
				  )
			  	{$where} 
			  	and order_items.asin in({$userwhere})
			) AS kk";

		$orderData = DB::connection('vlz')->select($sql);
		$array = array(
			'sales' => round($orderData[0]->sales,2),
			'revenue' => round($orderData[0]->sales - $orderData[0]->promotionAmount,2),
			'units' => round($orderData[0]->units,2),
			'unitsFull' => round($orderData[0]->units - $orderData[0]->unitsPromo,2),
			'unitsPromo' => round($orderData[0]->unitsPromo,2),
			'orders' => round($orderData[0]->orders,2),
			'ordersFull' => round($orderData[0]->orders - $orderData[0]->ordersPromo,2),
			'ordersPromo' => $orderData[0]->unitsPromo == $orderData[0]->units ? $orderData[0]->ordersPromo : $orderData[0]->ordersPromo - 1,//两个不相等的时候要减掉1个（''值时）
			'avgPrice' => $orderData[0]->units==0 ? 0 : round($orderData[0]->sales/$orderData[0]->units,2),//sales/units
			'danwei' => $currency_code,
		);
		return $array;
	}

	//展示列表数据
	public function list(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
        $date_type = isset($search['date_type']) ? $search['date_type'] : '';//选的时间类型
        $site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
        $account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
        $bgbu = isset($search['bgbu']) ? $search['bgbu'] : '';//bgbu,例如BG1_BU4
        $asin = isset($search['asin']) ? trim($search['asin'],'+') : '';//asin输入框的值
		$timeType = isset($search['timeType']) ? $search['timeType'] : '';//时间类型，默认是0为北京时间，1为亚马逊后台当地时间
		$this->date = isset($search['date']) ? $search['date'] : '';//date搜索框的值
		$where = $orderwhere = $this->getDateWhere($date_type,$site,$timeType);
		//$account搜索两个表的字段都为seller_account_id
		if($account){
			$where = $orderwhere .= ' and order_items.seller_account_id in('.$account.')';
		}
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
		//用户权限sap_asin_match_sku
		$userwhere = $this->getUserWhere($site,$bgbu);
		if($asin){
			$where .= " and order_items.asin = '".$asin."'";
		}

		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}

		$sql ="SELECT SQL_CALC_FOUND_ROWS kk.asin,SUM( item_price_amount ) AS sales,SUM( quantity_ordered ) AS units,SUM( quantity_ordered * PROMO ) AS unitsPromo,COUNT( DISTINCT amazon_order_id ) AS orders,COUNT(DISTINCT PROMO_ORDER_ID) AS ordersPromo,sum(c_promotionAmount) as promotionAmount 
			FROM
			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount,
			   		LENGTH( order_items.promotion_ids )> 10 AS PROMO,promotion_discount_amount as c_promotionAmount,
			  		CASE WHEN LENGTH( order_items.promotion_ids )> 10 THEN amazon_order_id ELSE '' END AS PROMO_ORDER_ID  
			  	FROM order_items 
				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."'
			  	WHERE order_items.amazon_order_id IN 
				  (
					SELECT amazon_order_id 
					FROM orders 
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwhere}
				  )
			  	{$where} 
			  	and order_items.asin in({$userwhere}) 
			) AS kk GROUP BY kk.asin order by sales desc {$limit}";

		$itemData = DB::connection('vlz')->select($sql);
		$recordsTotal = $recordsFiltered = DB::connection('vlz')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = array();
		$asins = array();
        $day = $this->getdays($date_type,$site,$timeType);//获取查询的时间范围有几天

		$showOrder = Auth::user()->can(['ccp-showOrderList']) ? 1 : 0;//是否有查看详情权限
		foreach($itemData as $key=>$val){
			$data[$val->asin] = (array)$val;
			$data[$val->asin]['avg_units'] = round($val->units/$day,2);
			$data[$val->asin]['title'] = $data[$val->asin]['image'] = $data[$val->asin]['item_no'] = 'N/A';
			if($showOrder==1) {
				$data[$val->asin]['orders'] = '<a target="_blank" href="/ccp/showOrderList?asin=' . $val->asin . '&' . $_REQUEST['search'] . '">' . $val->orders . '</a>';
			}
			$asins[] = $val->asin;
		}
		if($asins){
			$asins = "'".implode("','",$asins)."'";
			$product_where = '';
			// if($account){
			// 	$product_where .= ' and seller_account_id in('.$account.')';
			// }
			$product_sql = "select max(title) as title,max(images) as images,asin,max(sku) as item_no
						from asins
						where asin in({$asins})
						and marketplaceid = '{$site}'
						{$product_where}
						group by asin ";

			$productData = DB::connection('vlz')->select($product_sql);
			foreach($productData as $key=>$val){
				if(isset($data[$val->asin])){
					$title = mb_substr($val->title,0,50);
					$data[$val->asin]['title'] = '<span title="'.$val->title.'">'.$title.'</span>';
					$data[$val->asin]['item_no'] = $val->item_no ? $val->item_no : $data[$val->asin]['item_no'];
					if($val->images){
						$imageArr = explode(',',$val->images);
						if($imageArr){
							$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
							$data[$val->asin]['image'] = '<a href="https://www.' .$domain. '/dp/' . $val->asin .'" target="_blank" rel="noreferrer"><image style="width:50px;height:50px;" src="'.$image.'"></a>';
						}
					}
				}
			}
		}

		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}
	//ccp功能的列表中点击订单数查看改asin在查询条件内所有订单列表
	public function showOrderList(Request $req)
	{
		if(!Auth::user()->can(['ccp-show'])) die('Permission denied -- ccp show');
		$search = $this->getSearchData(explode('&',$_SERVER["QUERY_STRING"]));
		$date_type = isset($search['date_type']) ? $search['date_type'] : '';//选的时间类型
		$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
		$account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
		$bgbu = isset($search['bgbu']) ? $search['bgbu'] : '';//bgbu,例如BG1_BU4
		$asin = isset($search['asin']) ? current(explode(',',$search['asin'])) : '';//asin输入框的值

	}

	//得到搜索时间的sql
	public function getDateWhere($date_type,$site,$timeType)
	{
		$dateRange = $this->getDateRange($date_type,$site,$timeType);
		$startDate = $dateRange['startDate'];
		$endDate = $dateRange['endDate'];
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
	/*
	 * 得到查询时间的范围
	 * 						美国站       日本站
	 * 亚马逊后台显示时间为  UTC-7:00    UTC+9:00
	 * API/数据库存储时间  UTC+0:00    UTC+0:00
	 * 北京时间            UTC+8:00
	 * 所以查询时间为   date - 7 hour  / date + 9 hour
	 * 查询时间范围为  时间 - 15 hour  / 时间 + 1 hour
	 * $date_type变量的键值对应关系如下
	 * 1=>TODAY,2=>YESTERDAY,3=>LAST 3 DAYS,4=>LAST 7 DAYS,5=>LAST 15 DAYS,6=>LAST 30 DAYS
	 */
	public function getDateRange($date_type,$site,$timeType)
	{
		//如果选的时间类型是后台当地时间，时间要做转化
		$configDays = array(1=>1,2=>1,3=>3,4=>7,5=>15,6=>30);
		$time = $this->getCurrentTime($site,$timeType);//获取当前时间戳
		$startDate = date('Y-m-d 00:00:00',$time);//默认的开始时间
		$endDate = date('Y-m-d H:i:s',$time);//默认的结束时间
		if($date_type == 2){//昨天日期
			$startDate = date("Y-m-d 00:00:00",strtotime("-1 day",$time));
			$endDate = date('Y-m-d 23:59:59',strtotime("-1 day",$time));
		}elseif($date_type > 2){
			$lastday = isset($configDays[$date_type]) ? $configDays[$date_type] : 1;
			$startDate = date("Y-m-d 00:00:00",strtotime("-".($lastday-1)." day",$time));
		}
		return array('startDate'=>$startDate,'endDate'=>$endDate);
	}

	/*
	 * 获取查询的时间范围有几天
	 * $date_type变量的键值对应关系如下
	 * 1=>TODAY,2=>YESTERDAY,3=>LAST 3 DAYS,4=>LAST 7 DAYS,5=>LAST 15 DAYS,6=>LAST 30 DAYS
	 */
	public function getdays($date_type,$site,$timeType)
	{
		$configDays = array(1=>1,2=>1,3=>3,4=>7,5=>15,6=>30);
		$day = isset($configDays[$date_type]) ? $configDays[$date_type] : 1;
		return $day;
	}
	//得到当前时间戳
	public function getCurrentTime($site,$timeType)
	{
		//如果选的时间类型是后台当地时间，时间要做转化
		$dateconfig = array('A1PA6795UKMFR9','A1RKKUPIHCS9HS','A13V1IB3VIYZZH','APJ6JRA9NG5V4');//utc+2:00
		// $time = time();//北京时间当前时间戳
		$time = strtotime($this->date);//测试日期
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//时间范围+1小时,日本站点,-8+9
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("+1 hour", $time)));//日本站后台当前时间;
			}elseif($site=='A1F83G8C2ARO7P'){//英国站点+1小时，uTc+1:00,-8+1
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("-7 hour", $time)));//英国站后台当前时间;
			}elseif(in_array($site,$dateconfig)){//utc+2:00,-8+2
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("-6 hour", $time)));//几个特殊的站点
			}else{//时间范围-15小时,-8-7
				$time =  strtotime(date('Y-m-d H:i:s', strtotime ("-15 hour", $time)));//美国站后台当前时间;
			}
		}
		return $time;
	}
	//得到用户的权限数据查询语句，根据sap_asin_match_sku去查数据
	public function getUserWhere($site,$bgbu)
	{
		$userdata = Auth::user();
		$userWhere = " where marketplace_id  = '".$site."'";
		if (!in_array(Auth::user()->email, $this->ccpAdmin)) {
			if ($userdata->seller_rules) {
				$rules = explode("-", $userdata->seller_rules);
				if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '".array_get($rules, 0)."'";
				if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '".array_get($rules, 1)."'";
			}elseif($userdata->sap_seller_id){
				$userWhere .= " and sap_seller_id = ".$userdata->sap_seller_id;
			}
		}

		if($bgbu){
			$userWhere .= " and CONCAT(sap_seller_bg,'_',sap_seller_bu) = '".$bgbu."'";
		}
		$userWhere = " select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku  {$userWhere}";
		return $userWhere;
	}
}