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
	 *
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

		$where = $orderwhere = $this->getDateWhere($date_type);
		//$account搜索两个表的字段都为seller_account_id
		if($account){
			$where = $orderwhere .= ' and seller_account_id in('.$account.')';
		}
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$orderwhere .= " and sales_channel = '".$domain."'";
		//用户权限sap_asin_match_sku
		$userwhere = $this->getUserWhere($site,$bgbu);

		//sales数据，orders数据
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
					(SUM(item_price_amount) )AS c_sales,
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

		$where = $orderwhere = $this->getDateWhere($date_type);
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

		$sql = "SELECT SQL_CALC_FOUND_ROWS
					order_items.asin,
					COUNT( DISTINCT amazon_order_id ) AS orders,
					SUM(case WHEN CHAR_LENGTH(promotion_ids)>10 THEN 1 ELSE 0 END) AS ordersPromo,
					SUM(quantity_ordered) AS units,
					SUM(case WHEN CHAR_LENGTH(promotion_ids)>10 THEN quantity_ordered ELSE 0 END) AS unitsPromo,
					(SUM(item_price_amount) )AS sales,
					SUM(item_tax_amount) as c_taxs,
				sum(promotion_discount_amount) as promotionAmount
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
				GROUP BY asin order by sales desc {$limit} ";

		$itemData = DB::connection('vlz')->select($sql);
		$recordsTotal = $recordsFiltered = DB::connection('vlz')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = array();
		$asins = array();
		$day = 1;
        if($date_type == 3){//最近7天数据
            $day = 7;
        }elseif($date_type == 4){//本周数据
            //获取今天是周几
            $week = date('w');
            //0为周天，1为周一，以此类推
            $day==0 ? 7 : $week;//周天的时候查询了7天的数据，不然周几就是查询了几天的数据
        }elseif($date_type == 5){//最近30天数据
            $day = 30;
        }elseif($date_type == 6){//本月数据
            $day = date('m',time());
        }
		foreach($itemData as $key=>$val){
			$data[$val->asin] = (array)$val;
			$data[$val->asin]['avg_units'] = round($val->units/$day,2);
			$data[$val->asin]['title'] = $data[$val->asin]['image'] = 'N/A';
			$asins[] = $val->asin;
		}
		if($asins){
			$asins = "'".implode("','",$asins)."'";
			$product_where = '';
			if($account){
				$product_where .= ' and seller_account_id in('.$account.')';
			}
			$product_sql = "select max(title) as title,max(images) as images,max(asin) as asin
						from products
						where asin in({$asins})
						and marketplaceid = '{$site}'
						{$product_where}
						group by asin";
			$productData = DB::connection('vlz')->select($product_sql);
			foreach($productData as $key=>$val){
				if(isset($data[$val->asin])){
					$title = mb_substr($val->title,0,50);
					$data[$val->asin]['title'] = '<span title="'.$val->title.'">'.$title.'</span>';
					$imageArr = explode(',',$val->images);
					if($imageArr){
						$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
						$data[$val->asin]['image'] = '<image style="width:50px;height:50px;" src="'.$image.'">';
					}
				}
			}
		}

		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	//得到搜索时间的sql
	public function getDateWhere($date_type)
	{
		$dateRange = $this->getDateRange($date_type);
		$startDate = $dateRange['startDate'];
		$endDate = $dateRange['endDate'];
		$where = " and purchase_date BETWEEN STR_TO_DATE( '".$startDate."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$endDate."', '%Y-%m-%d %H:%i:%s' )";
		return $where;
	}
	//得到时间的区间
	public function getDateRange($date_type)
	{
		$startDate = date('Y-m-d 00:00:00');
		$endDate = date('Y-m-d 23:59:59');
		if($date_type == 2){//昨天日期
			$startDate = date("Y-m-d 00:00:00",strtotime("-1 day"));
			$endDate = date('Y-m-d 23:59:59',strtotime("-1 day"));
		}elseif($date_type == 3){//最近7天数据
			$startDate = date("Y-m-d 00:00:00",strtotime("-6 day"));
		}elseif($date_type == 4){//本周数据
			//获取今天是周几
			$day = date('w');
			//0为周天，1为周一，以此类推
			if($day==0){
				$lastday = 6;
			}else{
				$lastday = $day - 1;
			}
			$startDate = date("Y-m-d 00:00:00",strtotime("-".$lastday." day"));
		}elseif($date_type == 5){//最近30天数据
			$startDate = date("Y-m-d 00:00:00",strtotime("-29 day"));
		}elseif($date_type == 6){//本月数据
			$startDate = date('Y-m-1 00:00:00');
		}
		return array('startDate'=>$startDate,'endDate'=>$endDate);
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


}