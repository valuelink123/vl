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
		$bgbu= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');//获取bgbu选项
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
		$date_type = isset($_REQUEST['date_type']) ? $_REQUEST['date_type'] : 1;
		$where = $this->getDateWhere($date_type);
		//sales数据，orders数据
		$sql = "select sum(amount) as sales,count(amazon_order_id) as orders
				from orders
				where order_status != 'Canceled' {$where}";
		$orderData = DB::connection('vlz')->select($sql);
		//units数据，promo数据
		$item_sql = "select sum(quantity_ordered) as units,sum(protable.promo_unit) as unitsPromo,sum(protable.promo_order) as ordersPromo,sum(promotion_discount_amount) as promotionAmount
				from order_items
				left join (
				    select id as itemid, if(char_length(promotion_ids)>10,quantity_ordered,0) as promo_unit,if(char_length(promotion_ids)>10,1,0) as promo_order
					from order_items
				    where 1 =1 {$where}
					  ) as protable on id = itemid
				where 1 = 1 {$where}";
		$itemData = DB::connection('vlz')->select($item_sql);
		// echo $sql;
		$array = array(
			'sales' => round($orderData[0]->sales,2),
			'revenue' => round($orderData[0]->sales - $itemData[0]->promotionAmount,2),
			'units' => round($itemData[0]->units,2),
			'unitsFull' => round($itemData[0]->units - $itemData[0]->unitsPromo,2),
			'unitsPromo' => round($itemData[0]->unitsPromo,2),
			'orders' => round($orderData[0]->orders,2),
			'ordersFull' => round($orderData[0]->orders - $itemData[0]->ordersPromo,2),
			'ordersPromo' => round($itemData[0]->ordersPromo,2),
			'avgPrice' => $itemData[0]->units==0 ? 0 : round($orderData[0]->sales/$itemData[0]->units,2),//sales/units
			// 'stockValue' => '0',
		);
		return $array;
	}

	//展示列表数据
	public function list(Request $req)
	{

		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$date_type = isset($search['date_type']) ? $search['date_type'] : '';
		$site = isset($search['site']) ? $search['site'] : '';
		$account = isset($search['account']) ? $search['account'] : '';
		if($account){
			$account = implode(',',$account);
		}

		$dateRange = $this->getDateRange($date_type);
		$startDate = $dateRange['startDate'];
		$endDate = $dateRange['endDate'];
		$where = "and orders.purchase_date >= STR_TO_DATE('".$startDate."','%Y-%m-%d 00:00:00') and orders.purchase_date <= STR_TO_DATE('".$endDate."','%Y-%m-%d 23:59:59')";
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}


		if($account){
			$where .= ' and orders.seller_account_id in('.$account.')';
		}

		$sql = "SELECT asin,
				 sum( item_price_amount * quantity_ordered + item_tax_amount) AS sales,
				sum( quantity_ordered ) AS units,
				count( DISTINCT amazon_order_id ) orders
				FROM
					order_items
				WHERE
					purchase_date BETWEEN STR_TO_DATE( '".$startDate."', '%Y-%m-%d %H:%i:%s' )
					AND STR_TO_DATE( '".$endDate."', '%Y-%m-%d %H:%i:%s' )
					and CONCAT(amazon_order_id,'_',seller_account_id) in (
									select CONCAT(amazon_order_id,'_',seller_account_id)
									from orders
									where order_status in('PendingAvailability','Pending','Unshipped','PartiallyShipped','Shipped','InvoiceUnconfirmed','Unfulfillab')
									and purchase_date BETWEEN STR_TO_DATE( '".$startDate."', '%Y-%m-%d %H:%i:%s' )
									AND STR_TO_DATE( '".$endDate."', '%Y-%m-%d %H:%i:%s' )
						)
				GROUP BY asin order by sales desc {$limit}";
		$itemData = DB::connection('vlz')->select($sql);
		$recordsTotal = $recordsFiltered = DB::connection('vlz')->select('SELECT FOUND_ROWS()');
		$recordsTotal = (array)$recordsTotal[0];
		$recordsFiltered = (array)$recordsFiltered[0];
		$data = array();
		$asins = array();
		foreach($itemData as $key=>$val){
			$data[$val->asin] = (array)$val;
			$data[$val->asin]['avg_units'] = $val->orders == 0 ? 0 : round($val->units/$val->orders,2);
			$data[$val->asin]['title'] = $data[$val->asin]['image'] = '';
			$asins[] = $val->asin;
		}
		if($asins){
			$asins = "'".implode("','",$asins)."'";
			$product_where = '';
			if($account){
				$product_where .= ' and seller_account_id in('.$account.')';
			}
			$product_sql = "select any_value(title) as title,any_value(images) as images,any_value(asin) as asin
						from products
						where asin in({$asins})
						and marketplaceid = '{$site}'
						{$product_where}
						group by asin";
			$productData = DB::connection('vlz')->select($product_sql);
			foreach($productData as $key=>$val){
				if(isset($data[$val->asin])){
					$title = mb_substr($val->title,0,100);
					$data[$val->asin]['title'] = '<span title="'.$val->title.'">'.$title.'</span>';
					$imageArr = explode(',',$val->images);
					if($imageArr){
						$image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
						$data[$val->asin]['image'] = '<image style="width:50px;height:50px;" src="'.$image.'">';
					}
				}
			}
		}
		// echo '<pre>';
		// var_dump($recordsFiltered);exit;
		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	//得到搜索时间的sql
	public function getDateWhere($date_type)
	{
		$dateRange = $this->getDateRange($date_type);
		$startDate = $dateRange['startDate'];
		$endDate = $dateRange['endDate'];
		$where = "and purchase_date >= STR_TO_DATE('".$startDate."','%Y-%m-%d 00:00:00') and purchase_date <= STR_TO_DATE('".$endDate."','%Y-%m-%d 23:59:59')";
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



}