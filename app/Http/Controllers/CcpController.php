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
		if ($userdata->seller_rules) {
			$rules = explode("-", $userdata->seller_rules);
			if (array_get($rules, 0) != '*') $where .= " and bg = '".array_get($rules, 0)."'";
			if (array_get($rules, 1) != '*') $where .= " and bu = '".array_get($rules, 1)."'";
		}elseif($userdata->ubg && $userdata->ubu){
			$where .= " and bg = '".$userdata->ubg."' and bu = '".$userdata->ubu."'";
		}
		$bgbu= DB::select('select bg,bu from asin '.$where.' group by bg,bu ORDER BY BG ASC,BU ASC');//获取bgbu选项
		$site = getMarketDomain();//获取站点选项
		return view('ccp/index',['bgbu'=>$bgbu,'site'=>$site]);
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

		$where = $orderwhere = $this->getDateWhere($date_type,$site,$timeType);
		//$account搜索两个表的字段都为seller_account_id
		if($account){
			$where = $orderwhere .= ' and seller_account_id in('.$account.')';
		}
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$orderwhere .= " and sales_channel = '".$domain."'";
		//用户权限sap_asin_match_sku
		$userwhere = $this->getUserWhere($site,$bgbu);
		//保证asin_price此站点今天有数据
		$this->insertAsinPrice($site);

		//sales数据，orders数据
		$date = date('Y-m-d');//当前日期
		$sql = "SELECT SUM(c_order.c_orders) AS orders, SUM(c_order.c_proOrders) AS ordersPromo,SUM(c_order.c_proUnits) AS unitsPromo, 
			SUM(c_order.c_sales) AS sales ,SUM(c_order.c_taxs) AS _taxs ,sum(c_order.c_units) as units,sum(c_order.c_promotionAmount) as promotionAmount,max(code) as currency_code  
			FROM (
				SELECT
					order_items.asin,
					seller_account_id,
				    max(item_price_currency_code) as code,
					COUNT( DISTINCT amazon_order_id ) AS c_orders,
					SUM(case WHEN CHAR_LENGTH(promotion_ids)>10 THEN 1 ELSE 0 END) AS c_proOrders,
					SUM(quantity_ordered) AS c_units,
					SUM(case WHEN CHAR_LENGTH(promotion_ids)>10 THEN quantity_ordered ELSE 0 END) AS c_proUnits,
					(SUM(case WHEN item_price_amount = 0 THEN 
								(select tm.price*quantity_ordered from asin_price as tm where tm.asin = asin and tm.seller_account_id = seller_account_id and marketplace_id = '".$site."' and created_at='".$date."' limit 1) 
								ELSE item_price_amount END) )AS c_sales,
					SUM(item_tax_amount) as c_taxs,
				sum(promotion_discount_amount) as c_promotionAmount 
				FROM order_items 
				WHERE 1 = 1 {$where} 
				and CONCAT(amazon_order_id,'_',seller_account_id) in (
							select CONCAT(amazon_order_id,'_',seller_account_id)
							from orders
							where order_status in('PendingAvailability','Pending','Unshipped','PartiallyShipped','Shipped','InvoiceUnconfirmed','Unfulfillab')
							{$orderwhere}
				)
				AND quantity_ordered>0 
				and order_items.asin in({$userwhere})
				GROUP BY asin,seller_account_id 
			) AS c_order";

		$orderData = DB::connection('vlz')->select($sql);
		$array = array(
			'sales' => round($orderData[0]->sales,2),
			'revenue' => round($orderData[0]->sales - $orderData[0]->promotionAmount,2),
			'units' => round($orderData[0]->units,2),
			'unitsFull' => round($orderData[0]->units - $orderData[0]->unitsPromo,2),
			'unitsPromo' => round($orderData[0]->unitsPromo,2),
			'orders' => round($orderData[0]->orders,2),
			'ordersFull' => round($orderData[0]->orders - $orderData[0]->ordersPromo,2),
			'ordersPromo' => round($orderData[0]->ordersPromo,2),
			'avgPrice' => $orderData[0]->units==0 ? 0 : round($orderData[0]->sales/$orderData[0]->units,2),//sales/units
			'danwei' => $orderData[0]->currency_code ? $orderData[0]->currency_code : '',
			// 'stockValue' => '0',
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
        $asin = isset($search['asin']) ? $search['asin'] : '';//asin输入框的值
		$timeType = isset($search['timeType']) ? $search['timeType'] : '';//时间类型，默认是0为北京时间，1为亚马逊后台当地时间

		$where = $orderwhere = $this->getDateWhere($date_type,$site,$timeType);
		//$account搜索两个表的字段都为seller_account_id
		if($account){
			$where = $orderwhere .= ' and seller_account_id in('.$account.')';
		}
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$orderwhere .= " and sales_channel = '".$domain."'";
		//用户权限sap_asin_match_sku
		$userwhere = $this->getUserWhere($site,$bgbu);
		if($asin){
			$where .= " and order_items.asin = '".$asin."'";
		}

		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		//保证asin_price此站点今天有数据
		$this->insertAsinPrice($site);

		$date = date('Y-m-d');//当前日期
		$sql = "SELECT SQL_CALC_FOUND_ROWS asin, SUM(c_order.c_orders) AS orders, SUM(c_order.c_proOrders) AS ordersPromo,SUM(c_order.c_proUnits) AS unitsPromo, 
			SUM(c_order.c_sales) AS sales ,SUM(c_order.c_taxs) AS _taxs ,sum(c_order.c_units) as units,sum(c_order.c_promotionAmount) as promotionAmount,max(code) as currency_code  
			FROM (
				SELECT
					order_items.asin,
					seller_account_id,
				    max(item_price_currency_code) as code,
					COUNT( DISTINCT amazon_order_id ) AS c_orders,
					SUM(case WHEN CHAR_LENGTH(promotion_ids)>10 THEN 1 ELSE 0 END) AS c_proOrders,
					SUM(quantity_ordered) AS c_units,
					SUM(case WHEN CHAR_LENGTH(promotion_ids)>10 THEN quantity_ordered ELSE 0 END) AS c_proUnits,
					(SUM(case WHEN item_price_amount = 0 THEN 
								(select tm.price*quantity_ordered from asin_price as tm where tm.asin = asin and tm.seller_account_id = seller_account_id and marketplace_id = '".$site."' and created_at='".$date."' limit 1) 
								ELSE item_price_amount END) )AS c_sales,
					SUM(item_tax_amount) as c_taxs,
				sum(promotion_discount_amount) as c_promotionAmount
				FROM order_items 
				WHERE 1 = 1 {$where} 
				and CONCAT(amazon_order_id,'_',seller_account_id) in (
							select CONCAT(amazon_order_id,'_',seller_account_id)
							from orders
							where order_status in('PendingAvailability','Pending','Unshipped','PartiallyShipped','Shipped','InvoiceUnconfirmed','Unfulfillab')
							{$orderwhere}
				)
				AND quantity_ordered>0 
				and order_items.asin in({$userwhere})
				GROUP BY asin,seller_account_id 
			) AS c_order GROUP BY asin order by sales desc {$limit}";

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
			$data[$val->asin]['title'] = $data[$val->asin]['image'] = 'N/A';
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
			$product_sql = "select max(title) as title,max(images) as images,asin
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
					if($val->images){
						$imageArr = explode(',',$val->images);
						if($imageArr){
							$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
							$data[$val->asin]['image'] = '<image style="width:50px;height:50px;" src="'.$image.'">';
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
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//日本站点，date字段+9hour
				$date_field = 'date_add(purchase_date,INTERVAL 9 hour) ';
			}else{//其他站点，date字段-7hour
				$date_field = 'date_sub(purchase_date,INTERVAL 7 hour) ';
			}
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
	 */
	public function getDateRange($date_type,$site,$timeType)
	{
		//如果选的时间类型是后台当地时间，时间要做转化
		$time = time();//北京时间当前时间戳
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//时间范围+1小时,日本站点
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("+1 hour", $time)));//日本站后台当前时间;
			}else{//时间范围-15小时
				$time =  strtotime(date('Y-m-d H:i:s', strtotime ("-15 hour", $time)));//美国站后台当前时间
			}
		}
		$startDate = date('Y-m-d 00:00:00',$time);//默认的开始时间
		$endDate = date('Y-m-d H:i:s',$time);//默认的结束时间
		if($date_type == 2){//昨天日期
			$startDate = date("Y-m-d 00:00:00",strtotime("-1 day",$time));
			$endDate = date('Y-m-d 23:59:59',strtotime("-1 day",$time));
		}elseif($date_type == 3){//最近7天数据
			$startDate = date("Y-m-d 00:00:00",strtotime("-6 day",$time));
		}elseif($date_type == 4){//本周数据
			//获取今天是周几
			$day = date('w',$time);
			//0为周天，1为周一，以此类推
			$lastday = $day==0 ? 6 : $day - 1;
			$startDate = date("Y-m-d 00:00:00",strtotime("-".$lastday." day",$time));
		}elseif($date_type == 5){//最近30天数据
			$startDate = date("Y-m-d 00:00:00",strtotime("-29 day",$time));
		}elseif($date_type == 6){//本月数据
			$startDate = date('Y-m-01 00:00:00',$time);
		}
		return array('startDate'=>$startDate,'endDate'=>$endDate);
	}

	/*
	 * 获取查询的时间范围有几天
	 *
	 */
	public function getdays($date_type,$site,$timeType)
	{
		//如果选的时间类型是后台当地时间，时间要做转化
		$time = time();//北京时间当前时间戳
		if($timeType==1){//选的是后台当地时间
			if($site=='A1VC38T7YXB528'){//时间范围+1小时,日本站点
				$time = strtotime(date('Y-m-d H:i:s', strtotime ("+1 hour", $time)));//日本站后台当前时间;
			}else{//时间范围-15小时
				$time =  strtotime(date('Y-m-d H:i:s', strtotime ("-15 hour", $time)));//美国站后台当前时间
			}
		}
		$day = 1;
		if($date_type == 3){//最近7天数据
			$day = 7;
		}elseif($date_type == 4){//本周数据
			//获取今天是周几
			$week = date('w',$time);
			//0为周天，1为周一，以此类推
			$day = $week==0 ? 7 : $week;//周天的时候查询了7天的数据，不然周几就是查询了几天的数据
		}elseif($date_type == 5){//最近30天数据
			$day = 30;
		}elseif($date_type == 6){//本月数据
			$day = date('m',$time);
		}
		return $day;
	}
	//通过站点显示账号，ajax联动
	public function showAccountBySite()
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
	//得到用户的权限数据查询语句，根据sap_asin_match_sku去查数据
	public function getUserWhere($site,$bgbu)
	{
		$userdata = Auth::user();
		$userWhere = " where marketplace_id  = '".$site."'";
		if ($userdata->seller_rules) {
			$rules = explode("-", $userdata->seller_rules);
			if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '".array_get($rules, 0)."'";
			if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '".array_get($rules, 1)."'";
		}elseif($userdata->sap_seller_id){
			$userWhere .= " and sap_seller_id = ".$userdata->sap_seller_id;
		}
		if($bgbu){
			$userWhere .= " and CONCAT(sap_seller_bg,'_',sap_seller_bu) = '".$bgbu."'";
		}
		$userWhere = " select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku  {$userWhere}";
		return $userWhere;
	}

	//查询该站点的最近一条item_price_amount>0的item_price_amount金额,为了替换掉状态为pending并且item_price_amount=0的金额数据\
	public function insertAsinPrice($site)
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


}