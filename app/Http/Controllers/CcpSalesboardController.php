<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use DB;


class CcpSalesBoardController extends Controller
{
    use \App\Traits\Mysqli;
	use \App\Traits\DataTables;

    public $ccpAdmin = array("xumeiling@valuelinkcorp.com");

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
		return view('ccp/salesboard',['bgbu'=>$bgbu,'site'=>$site]);
    }

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
        $domain = substr(getDomainBySite($site), 4);//orders.sales_channel
        $siteCur = getSiteCur();
		$currency_code = isset($siteCur[$domain]) ? $siteCur[$domain] : '';
		$dateRange = $this->getDateRange($date_type,$site,$timeType);
		$dateField = $this->getDateField($site,$timeType);

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

        $sql ="SELECT SUM( item_price_amount ) AS sales,SUM( quantity_ordered ) AS units,COUNT( DISTINCT amazon_order_id ) AS orders, period
			FROM
			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount,
                (case  
					 WHEN {$dateField} between '{$dateRange[0]['startDate']}' and '{$dateRange[0]['endDate']}' then 'period_1'
					 WHEN {$dateField} between '{$dateRange[1]['startDate']}' and '{$dateRange[1]['endDate']}' then 'period_2'
					 ELSE 'period_others' END
                ) as period
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
			) AS kk 
			GROUP BY period";

		$orderData = DB::connection('amazon')->select($sql);
        $result = array();
        $periods = array('period_1','period_2');
        $items = array('sales','units','orders');
        //$orderData中得到的数据，可能某一天的数据为空，先给所有天数的数据赋值为0
        foreach($periods as $period){
            $result[$period] = array();
            foreach($items as $item){
               $result[$period][$item] = 0;
            }
        }
        foreach($orderData as $data){
            foreach($items as $item){
               $result[$data->period][$item] = $data->$item;
            }
        }

        $params = array();
        //订单量,订单金额,销售数量,每单平均销量,平均单品净销售额
        foreach($periods as $period){
            foreach($items as $item){
               $params[$period.'_'.$item] = round($result[$period][$item],2);
            }
            $params[$period.'_units_per_order'] = $result[$period]['orders'] == 0 ? 0 : sprintf('%.2f',$result[$period]['units']/$result[$period]['orders']);
            $params[$period.'_sales_per_unit'] = $result[$period]['units'] == 0 ? 0 : sprintf('%.2f',$result[$period]['sales']/$result[$period]['units']);
        }
        $showItems = array('sales','units','orders','units_per_order','sales_per_unit');
        foreach ($showItems as $s){
            $params[$s.'_change'] = $params['period_2_'.$s] == 0 ? '-' : $this->toPercentage($params['period_1_'.$s]/$params['period_2_'.$s]-1);
            if($params['period_1_'.$s] - $params['period_2_'.$s] >= 0 && $params['period_2_'.$s] != 0){
                $params[$s.'_change'] = '<span style="font-size:14px; color:#f36a5a">+'.$params[$s.'_change'].'</span>';
            }else{
                $params[$s.'_change'] = '<span style="font-size:14px; color:#158f76">'.$params[$s.'_change'].'</span>';
            }
        }
        $params['danwei'] = $currency_code;

        return $params;
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
		$dateRange = $this->getDateRange($date_type,$site,$timeType);
		$dateField = $this->getDateField($site,$timeType);


		$where = $orderwhere = $this->getDateWhere($date_type,$site,$timeType);
		$wherePeriodOne = $orderwherePeriodOne = $this->getDateWherePeriodOne($date_type,$site,$timeType);
		$wherePeriodTwo = $orderwherePeriodTwo = $this->getDateWherePeriodTwo($date_type,$site,$timeType);

		//$account搜索两个表的字段都为seller_account_id
		if($account){
			$where = $orderwhere .= ' and order_items.seller_account_id in('.$account.')';
			$wherePeriodOne = $orderwherePeriodOne .= ' and order_items.seller_account_id in('.$account.')';
			$wherePeriodTwo = $orderwherePeriodTwo .= ' and order_items.seller_account_id in('.$account.')';

		}
		$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
		$orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
		$orderwherePeriodOne .= " and sales_channel = '".ucfirst($domain)."'";
		$orderwherePeriodTwo .= " and sales_channel = '".ucfirst($domain)."'";

		//用户权限sap_asin_match_sku
		$userwhere = $this->getUserWhere($site,$bgbu);
		if($asin){
			$where .= " and order_items.asin = '".$asin."'";
			$wherePeriodOne .= " and order_items.asin = '".$asin."'";
			$wherePeriodTwo .= " and order_items.asin = '".$asin."'";
		}

		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM
             (SELECT asin, SUM( item_price_amount ) AS sales_1,SUM( quantity_ordered ) AS units_1,COUNT( DISTINCT amazon_order_id ) AS orders_1
			  FROM
			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount
			  	FROM order_items 
				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."'
			  	WHERE order_items.amazon_order_id IN 
				  (
					SELECT amazon_order_id 
					FROM orders 
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwherePeriodOne}
				  )
			  	{$wherePeriodOne} 
			  	and order_items.asin in({$userwhere}) 
			) AS t1 GROUP BY asin) AS tt1
			
			LEFT JOIN
			
			(SELECT asin as asin_2, SUM( item_price_amount ) AS sales_2,SUM( quantity_ordered ) AS units_2,COUNT( DISTINCT amazon_order_id ) AS orders_2
			 FROM
			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount
			  	FROM order_items 
				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."'
			  	WHERE order_items.amazon_order_id IN 
				  (
					SELECT amazon_order_id 
					FROM orders 
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwherePeriodTwo}
				  )
			  	{$wherePeriodTwo} 
			  	and order_items.asin in({$userwhere}) 
			) AS t2 GROUP BY asin) AS tt2
			ON tt1.asin = tt2.asin_2
            order by sales_1 desc {$limit}
			";

		$itemData = DB::connection('amazon')->select($sql);
		$recordsTotal = $recordsFiltered = DB::connection('amazon')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = array();
		$asins = array();

		$showItems = array('sales','units','orders','units_per_order','sales_per_unit');
		$periods = array('_1','_2');
		foreach($itemData as $key=>$val){
			$data[$val->asin] = (array)$val;
            foreach ($showItems as $s){
                foreach($periods as $p){
                    if(array_get($data[$val->asin], $s.$p) == null){
                        $data[$val->asin][$s.$p] = 0;
                    }
                }
            }
            foreach($periods as $p){
                $data[$val->asin]['units_per_order'.$p] = $data[$val->asin]['orders'.$p] == 0 ? 0 : sprintf('%.2f',$data[$val->asin]['units'.$p]/$data[$val->asin]['orders'.$p]);
                $data[$val->asin]['sales_per_unit'.$p] = $data[$val->asin]['units'.$p] == 0 ? 0 : sprintf('%.2f',$data[$val->asin]['sales'.$p]/$data[$val->asin]['units'.$p]);
            }

            foreach ($showItems as $s){
                $data[$val->asin][$s.'_change'] = $data[$val->asin][$s.'_2'] == 0 ? '-' : $this->toPercentage($data[$val->asin][$s.'_1']/$data[$val->asin][$s.'_2']-1);
                if($data[$val->asin][$s.'_1'] - $data[$val->asin][$s.'_2'] >= 0 && $data[$val->asin][$s.'_2'] != 0){
                    $data[$val->asin][$s.'_change'] = '<span style="font-size:14px; color:#f36a5a">+'.$data[$val->asin][$s.'_change'].'</span>';
                }else{
                    $data[$val->asin][$s.'_change'] = '<span style="font-size:14px; color:#158f76">'.$data[$val->asin][$s.'_change'].'</span>';
                }
            }
            foreach($showItems as $s){
                $data[$val->asin][$s] = $data[$val->asin][$s.'_1'].'<br/>'.$data[$val->asin][$s.'_2'].'<br/>'.$data[$val->asin][$s.'_change'];
            }

			$data[$val->asin]['title'] = $data[$val->asin]['image'] = $data[$val->asin]['item_no'] = 'N/A';
			$asins[] = $val->asin;
		}

		if($asins){
			$asins = "'".implode("','",$asins)."'";
			$product_where = '';
			// if($account){
			// 	$product_where .= ' and seller_account_id in('.$account.')';
			// }

            $sellerSkusWhere = '';
            //if($account){
            //    $sellerSkusWhere = ' and seller_account_id in('.$account.')';
            //}
            //if($site){
            //    $sellerSkusWhere .= " and marketplaceid='".$site."'";
            //}

			$product_sql = "select max(asins.title) as title,max(asins.images) as images,asins.asin,max(asins.sku) as item_no, max(c_seller_skus.afn_sellable) as afn_sellable, max(c_seller_skus.afn_reserved) as afn_reserved, max(c_seller_skus.afn_transfer) as afn_transfer
						from asins
						LEFT JOIN
                        (SELECT max(asin) as asin, sum(afn_sellable) as afn_sellable, sum(afn_reserved) as afn_reserved, sum(afn_transfer) as afn_transfer
                        FROM seller_skus
                        WHERE 1 = 1 {$sellerSkusWhere}
                        GROUP BY asin) as c_seller_skus
                        ON asins.asin = c_seller_skus.asin
						where asins.asin in({$asins})
						and asins.marketplaceid = '{$site}'
						{$product_where}
						group by asins.asin ";
			$productData = DB::connection('amazon')->select($product_sql);
			foreach($productData as $key=>$val){
				if(isset($data[$val->asin])){
					$title = mb_substr($val->title,0,50);
					$data[$val->asin]['title'] = '<span title="'.$val->title.'">'.$title.'</span>';
					$data[$val->asin]['item_no'] = $val->item_no ? $val->item_no : $data[$val->asin]['item_no'];
                    $data[$val->asin]['afn_sellable'] = (int)$val->afn_sellable;
                    $data[$val->asin]['afn_reserved'] = (int)$val->afn_reserved;
                    $data[$val->asin]['afn_transfer'] = (int)$val->afn_transfer;
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

    //得到搜索时间的sql
	public function getDateWhere($date_type,$site,$timeType)
    {
        $dateRange = $this->getDateRange($date_type,$site,$timeType);
        $date_field = $this->getDateField($site,$timeType);
		$between = array();
		foreach($dateRange as $k=>$v){
		    $between[] = "({$date_field} BETWEEN STR_TO_DATE( '".$v['startDate']."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$v['endDate']."', '%Y-%m-%d %H:%i:%s' ))";
        }
		$where = " and (".implode(' or ', $between).')';
		return $where;
    }

    //下面的表格只用显示当前时期的数据（不显示前一时期的数据）
	public function getDateWherePeriodOne($date_type,$site,$timeType)
    {
        $dateRange = $this->getDateRange($date_type,$site,$timeType);
        $date_field = $this->getDateField($site,$timeType);
		$where = " and {$date_field} BETWEEN STR_TO_DATE( '".$dateRange[0]['startDate']."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$dateRange[0]['endDate']."', '%Y-%m-%d %H:%i:%s' )";
		return $where;
    }

    //下面的表格只用显示当前时期的数据（不显示前一时期的数据）
	public function getDateWherePeriodTwo($date_type,$site,$timeType)
    {
        $dateRange = $this->getDateRange($date_type,$site,$timeType);
        $date_field = $this->getDateField($site,$timeType);
		$where = " and {$date_field} BETWEEN STR_TO_DATE( '".$dateRange[1]['startDate']."', '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE('".$dateRange[1]['endDate']."', '%Y-%m-%d %H:%i:%s' )";
		return $where;
    }

    public function getDateField($site,$timeType){
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
		return $date_field;
    }

    // 1=>TODAY,2=>YESTERDAY,3=>LAST 3 DAYS,4=>LAST 7 DAYS,5=>LAST 15 DAYS,6=>LAST 30 DAYS
    public function getDateRange($date_type,$site,$timeType)
	{
	    if(!$date_type) $date_type = 1;
        //如果选的时间类型是后台当地时间，时间要做转化
		$configDays = array(1=>1,2=>1,3=>3,4=>7,5=>15,6=>30);
		$time = $this->getCurrentTime($site,$timeType);//获取当前时间戳
        $startDate = date('Y-m-d 00:00:00',$time);//默认的开始时间
        $endDate = date('Y-m-d H:i:s',$time);//默认的结束时间
		$dateRange = array();
        for($i=0; $i<2;$i++){
            $dateRange[] = array();
        }
		if($date_type == 1){
            $dateRange[0]['startDate'] = $startDate;
            $dateRange[0]['endDate'] = $endDate;
            $dateRange[1]['startDate'] = date("Y-m-d 00:00:00",strtotime("-1 day",$time));
            $dateRange[1]['endDate'] = date('Y-m-d 23:59:59',strtotime("-1 day",$time));
        }
		else if($date_type == 2){
            $dateRange[0]['startDate'] = date("Y-m-d 00:00:00",strtotime("-1 day",$time));
            $dateRange[0]['endDate'] = date('Y-m-d 23:59:59',strtotime("-1 day",$time));
            $dateRange[1]['startDate'] = date("Y-m-d 00:00:00",strtotime("-2 day",$time));
            $dateRange[1]['endDate'] = date('Y-m-d 23:59:59',strtotime("-2 day",$time));
        }
		else if($date_type > 2){
		    $days = isset($configDays[$date_type]) ? $configDays[$date_type] : 1;
            $dateRange[0]['startDate'] = date("Y-m-d 00:00:00",strtotime("-".($days-1)." day",$time));
            $dateRange[0]['endDate'] = $endDate;
            $dateRange[1]['startDate'] = date("Y-m-d 00:00:00",strtotime("-".(2*$days-1)." day",$time));
            $dateRange[1]['endDate'] = date('Y-m-d 23:59:59',strtotime("-".$days." day",$time));
        }
		return $dateRange;
	}

    public function showAccountBySite(){
	    return $this->showTheAccountBySite();
    }

    public function insertAsinPrice($site){
	    return $this->insertTheAsinPrice($site);
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

    //小数转成百分数，保留2位小数
    public function toPercentage($num){
        return sprintf("%.2f",$num*100).'%';
    }


}