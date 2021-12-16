<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class CuckooController extends Controller
{


	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;
    /**
     * CuckooController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
	/*
	 * 系统中显示列表数据
	 */
	public function show()
	{
		if(!Auth::user()->can(['cuckoo-show'])) die('Permission denied -- cuckoo-show');
		$site = getMarketDomain();//获取站点选项
		$startDateFrom = $endDateFrom =  date('Y-m-d',strtotime("-7 day"));
		$startDateTo = $endDateTo =  date('Y-m-d');
		$type = array('deal','coupon','promotion');
		return view('cuckoo/show',['site'=>$site,'startDateFrom'=>$startDateFrom,'endDateFrom'=>$endDateFrom,'startDateTo'=>$startDateTo,'endDateTo'=>$endDateTo,'type'=>$type]);
	}
	/*
	 * ajax展示模板列表
	 */
	public function showList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$marketplace_id= isset($search['site']) ? urldecode($search['site']) : '';
		$account= isset($search['account']) ? urldecode($search['account']) : '';
		$startDate_from= isset($search['startDate_from']) ? $search['startDate_from'] : '';
		$startDate_to= isset($search['startDate_to']) ? $search['startDate_to'] : '';
		$endDate_from= isset($search['endDate_from']) ? $search['endDate_from'] : '';
		$endDate_to= isset($search['endDate_to']) ? $search['endDate_to'] : '';
		$type= isset($search['type']) ? $search['type'] : '';

		$where = "where marketplaceId = '".$marketplace_id."' and startDate >= '".$startDate_from." 00:00:00' and startDate<= '".$startDate_to." 23:59:59' and endDate>= '".$endDate_from." 00:00:00' and endDate<= '".$endDate_to." 23:59:59'";
		if($account){
			$account = implode("','",explode(",",$account));
			$account = "'".$account."'";
			$where .= ' and seller_id in('.$account.')';
		}
		if($type){
			$where .= " and type = '".$type."'";
		}
		$limit = '';
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql ="select SQL_CALC_FOUND_ROWS * FROM
  (
    SELECT
      cuckoo_coupons_item.asin,
      'coupon' AS `type`,
      cuckoo_coupons_item.minPrice,
      cuckoo_coupons_item.maxPrice,
	   'N/A' as price,
      cuckoo_coupons.customers,
      cuckoo_coupons.discountType,
      cuckoo_coupons.discount,
      cuckoo_coupons.save,
      cuckoo_coupons.budget,
      CASE
    WHEN DATE_FORMAT(now(), '%Y-%m-%d') < cuckoo_coupons.startDate THEN
      'Pending'
    WHEN DATE_FORMAT(now(), '%Y-%m-%d') > cuckoo_coupons.endDate THEN
      'Ended'
    ELSE
      'Active'
    END AS `status`,
    CONCAT(cuckoo_coupons.startDate,' 00:00:00') as startDate,
	CONCAT(cuckoo_coupons.endDate,' 23:59:59') as endDate,
    cuckoo_coupons.marketplaceId,
    cuckoo_coupons.seller_id
  FROM
    cuckoo_coupons_item
  LEFT JOIN cuckoo_coupons ON (cuckoo_coupons_item.coupons_id = cuckoo_coupons.coupons_id)
  UNION ALL
    SELECT
      cuckoo_deals_item.asin,
      'deal' AS `type`,
      cuckoo_deals_item.dealPrice AS minPrice,
      cuckoo_deals_item.dealPrice AS maxPrice,
   		cuckoo_deals_item.dealPrice AS price,
      'all' AS customers,
      'money' AS discountType,
      0.00 AS discount,
      '0%' AS save,
      'N/A' AS budget,
      cuckoo_deals.`status`,
      cuckoo_deals.startDate,
      cuckoo_deals.endDate,
      cuckoo_deals.marketplaceId,
      cuckoo_deals.seller_id
    FROM
      cuckoo_deals_item
    LEFT JOIN cuckoo_deals ON (cuckoo_deals_item.deals_id = cuckoo_deals.deals_id)
   UNION ALL 
          SELECT
      cuckoo_promotions_item.asin,
      'promotion' AS `type`,
      'N/A' AS minPrice,
      'N/A' AS maxPrice,
	   'N/A' as price,
      'N/A' AS customers,
      cuckoo_promotions.discountType,
      cuckoo_promotions.discount,
      cuckoo_promotions.save,
      'N/A' AS budget,
      CASE
    WHEN DATE_FORMAT(now(), '%Y-%m-%d') < cuckoo_promotions.startDate THEN
      'Pending'
    WHEN DATE_FORMAT(now(), '%Y-%m-%d') > cuckoo_promotions.endDate THEN
      'Ended'
    ELSE
      'Active'
    END AS `status`,
    cuckoo_promotions.startDate as startDate,
	cuckoo_promotions.endDate as endDate,
    cuckoo_promotions.marketplaceId,
    cuckoo_promotions.seller_id
  FROM
    cuckoo_promotions_item
  LEFT JOIN cuckoo_promotions ON (cuckoo_promotions_item.promotions_id = cuckoo_promotions.promotions_id)
  ) AS tmp
 {$where} ORDER BY
  tmp.asin DESC,
  tmp.startDate ASC {$limit}";
//		echo $sql;exit;

		$data = DB::select($sql);
		$recordsTotal = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = json_decode(json_encode($data),true);
		$siteDomain = getSiteDomain();//得到站点跟域名的键值对
		$sellerAccount = getSellerAccout();
		$asinListingPrice = $this->getAsinListingPrice($marketplace_id,$account);
		foreach($data as $key=>$val){
			$marketplaceid_sellerid_asin = $val['marketplaceId'].'_'.$val['seller_id'].'_'.$val['asin'];
			$sellerid_marketplaceid = $val['seller_id'].'_'.$val['marketplaceId'];
			$data[$key]['site'] = isset($siteDomain[$val['marketplaceId']]) ? $siteDomain[$val['marketplaceId']] : $val['marketplaceId'];
			$data[$key]['account_name'] = isset($sellerAccount[$sellerid_marketplaceid]) ? $sellerAccount[$sellerid_marketplaceid] : $sellerid_marketplaceid;
			$data[$key]['listing_price'] = isset($asinListingPrice[$marketplaceid_sellerid_asin]) ? $asinListingPrice[$marketplaceid_sellerid_asin] : 'N/A';
			if($val['type']=='deal'){
				$data[$key]['discount'] = $data[$key]['save'] = 'N/A';
				if(isset($asinListingPrice[$marketplaceid_sellerid_asin])){
					//deal的时候，discount折扣金额等于listing_price-price
					$data[$key]['discount'] = $asinListingPrice[$marketplaceid_sellerid_asin] - $val['price'];
					$data[$key]['save'] = '$'.$data[$key]['discount'];
				}
			}
//			$data[$key]['action'] = '<a href="/cuckoo/view?type=deals&id='.$val['deals_id'].'" class="btn btn-success btn-xs" >View</a>';
//			$data[$key]['action'] = '-';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}




    /*
     * 接口数据
     */
    public function index(){
		$input = file_get_contents("php://input");
		Log::info('cuckoo-data:'.(isset($input)?$input:""));
		$data = json_decode($input,true);
		if($data['type']=='deals'){
			//deals相关处理
			foreach($data['data'] as $key=>$val){
				$insert = array(
					'marketplaceId' => isset($val['marketplaceId']) ? $val['marketplaceId'] : '',
					'seller_id' => isset($val['merchantId']) ? $val['merchantId'] : '',
					'campaignKey' => isset($val['campaignKey']) ? $val['campaignKey'] : '',
					'startDateWithLocale' => isset($val['startDateWithLocale']) ? $val['startDateWithLocale'] : '',
					'endDateWithLocale' => isset($val['endDateWithLocale']) ? $val['endDateWithLocale'] : '',
					'startDate' => isset($val['startDate']) ? $val['startDate'] : '',
					'endDate' => isset($val['endDate']) ? $val['endDate'] : '',
					'status' => isset($val['status']) ? $val['status'] : '',
				);
				$res = DB::table('cuckoo_deals')
					->updateOrInsert(
						['deals_id' => $key],
						$insert
					);
				if($res){
					foreach($val['asin'] as $ak=>$av){
						$insert_item = array(
							'dealPrice' => isset($av['dealPrice']) ? $av['dealPrice'] : '',
							'dealQuantity' => isset($av['dealQuantity']) ? $av['dealQuantity'] : '',
							'sellerPrice' => isset($av['sellerPrice']) ? $av['sellerPrice'] : '',
							'sellerQuantity' => isset($av['sellerQuantity']) ? $av['sellerQuantity'] : '',
							'discountsPrice' => isset($av['discountsPrice']) ? $av['discountsPrice'] : '',
						);
						$res_item = DB::table('cuckoo_deals_item')
							->updateOrInsert(
								['deals_id' => $key,'asin'=>$av['asin']],
								$insert_item
							);
						if($res_item){
							DB::commit();
						}else{
							DB::rollBack();
						}
					}
				}else{
					DB::rollBack();
				}
			}
		}else if($data['type']=='coupons'){
			//coupons相关处理
			foreach($data['data'] as $key=>$val){
				$insert = array(
					'save' => isset($val['save']) ? $val['save'] : '',
					'discount' => isset($val['discount']) ? $val['discount'] : '',
					'discountType' => isset($val['discountType']) ? $val['discountType'] : '',
					'budget' => isset($val['Budget']) ? $val['Budget'] : '',
					'customers' => isset($val['customers']) ? $val['customers'] : '',
					'startDate' => isset($val['startDate']) ? $val['startDate'] : '',
					'endDate' => isset($val['endDate']) ? $val['endDate'] : '',
				);
				$res = DB::table('cuckoo_coupons')
					->updateOrInsert(
						['coupons_id' => $key],
						$insert
					);
				if($res){
					foreach($val['asin'] as $ak=>$av){
						$insert_item = array(
							'title' => isset($av['title']) ? $av['title'] : '',
							'minPrice' => isset($av['minPrice']) ? $av['minPrice'] : '',
							'maxPrice' => isset($av['maxPrice']) ? $av['maxPrice'] : '',
							'inStock' => isset($av['inStock']) ? $av['inStock'] : '',
						);
						$res_item = DB::table('cuckoo_coupons_item')
							->updateOrInsert(
								['coupons_id' => $key,'asin'=>$av['asin']],
								$insert_item
							);
						if($res_item){
							DB::commit();
						}else{
							DB::rollBack();
						}
					}
				}else{
					DB::rollBack();
				}
			}
		}else if($data['type']=='promotions'){
			//promotions相关处理,
			$promotionsData = current($data['data']);
			if(isset($promotionsData['Buyer_gets2']) && $promotionsData['Buyer_gets2'] && isset($promotionsData['promotionID'])){
				$promotionID = $promotionsData['promotionID'];
				$discountType = '';
				$remarkArray['Buyer_purchases1'] = isset($promotionsData['Buyer_purchases1']) ? $promotionsData['Buyer_purchases1'] : '';
				$remarkArray['Buyer_purchases2'] = isset($promotionsData['Buyer_purchases2']) ? $promotionsData['Buyer_purchases2'] : '';
				$remarkArray['Buyer_gets1'] = isset($promotionsData['Buyer_gets1']) ? $promotionsData['Buyer_gets1'] : '';
				$remarkArray['Buyer_gets2'] = isset($promotionsData['Buyer_gets2']) ? $promotionsData['Buyer_gets2'] : '';
				$remark = json_encode($remarkArray);
				if($promotionsData['Buyer_gets1']==='Fixed price for all items (in $)'){
					$discountType = 'money';
				}

				$insert = array(
					'marketplaceId' => isset($promotionsData['marketplaceId']) ? $promotionsData['marketplaceId'] : '',
					'seller_id' => isset($promotionsData['merchantId']) ? $promotionsData['merchantId'] : '',
					'save' => isset($promotionsData['Buyer_gets2']) ? '$'.$promotionsData['Buyer_gets2'] : '',
					'discount' => isset($promotionsData['Buyer_gets2']) ? $promotionsData['Buyer_gets2'] : '',
					'discountType' => $discountType,
					'remark' => $remark,
					'claim_code' => isset($promotionsData['claimCode']) ? $promotionsData['claimCode'] : '',
					'trackingId' => isset($promotionsData['trackingId']) ? $promotionsData['trackingId'] : '',
					'startDate' => isset($promotionsData['startDate']) ? $promotionsData['startDate'] : '',
					'endDate' => isset($promotionsData['endDate']) ? $promotionsData['endDate'] : '',
				);
				$res = DB::table('cuckoo_promotions')
					->updateOrInsert(
						['promotions_id' => $promotionID],
						$insert
					);
				if($res){
					$asin_list = end($data['data']);
					$asinArr = explode(',',$asin_list['asin_list']);
					foreach($asinArr as $asin){
						$insert_item = array(
							'buyer_quantity' => isset($promotionsData['Buyer_purchases2']) ? $promotionsData['Buyer_purchases2'] : '',
						);
						$res_item = DB::table('cuckoo_promotions_item')
							->updateOrInsert(
								['promotions_id' => $promotionID,'asin'=>$asin],
								$insert_item
							);
						if($res_item){
							DB::commit();
						}else{
							DB::rollBack();
						}
					}
				}else{
					DB::rollBack();
				}
			}
		}
		return 'ok';
    }

    /*
     *
     */
    public function feedback(){
		$input = file_get_contents("php://input");
		$sellerid = isset($_GET['sellerid']) ? $_GET['sellerid'] : '';
//		$sellerid = 'A2SKRJ26V7UUW3';//测试数据
		$today = date('Y-m-d');
//		$today = '2021-12-03';//测试数据
		$config_max_discount_common = '0.6';//共用的最大折扣设定百分比
//		$config_max_discount = array('asin1'=>'0.3','asin2'=>'0.4');//设置的每个asin能打的最大折扣百分比
		$config_max_discount = array();
		Log::info('cuckoo-feedback-data:'.$sellerid);

		$sql = "select * FROM
  (
    SELECT
      cuckoo_coupons_item.asin,
      'coupon' AS `type`,
      cuckoo_coupons_item.minPrice,
      cuckoo_coupons_item.maxPrice,
   'N/A' AS price,
      cuckoo_coupons.discountType,
      cuckoo_coupons.discount,
      CASE
    WHEN '".$today."' < cuckoo_coupons.startDate THEN
      'Pending'
    WHEN '".$today."' > cuckoo_coupons.endDate THEN
      'Ended'	
    ELSE
      'Active'
    END AS `status`,
    CONCAT(cuckoo_coupons.startDate,' 00:00:00') as startDate,
	CONCAT(cuckoo_coupons.endDate,' 23:59:59') as endDate,
    cuckoo_coupons.marketplaceId,
    cuckoo_coupons.seller_id
  FROM
    cuckoo_coupons_item
  LEFT JOIN cuckoo_coupons ON (cuckoo_coupons_item.coupons_id = cuckoo_coupons.coupons_id)
  UNION ALL
    SELECT
      cuckoo_deals_item.asin,
      'deal' AS `type`,
      cuckoo_deals_item.dealPrice AS minPrice,
      cuckoo_deals_item.dealPrice AS maxPrice,
      cuckoo_deals_item.dealPrice AS price,
      'money' AS discountType,
      0.00 AS discount,
      cuckoo_deals.`status`,
      cuckoo_deals.startDate,
      cuckoo_deals.endDate,
      cuckoo_deals.marketplaceId,
      cuckoo_deals.seller_id
    FROM
      cuckoo_deals_item
    LEFT JOIN cuckoo_deals ON (cuckoo_deals_item.deals_id = cuckoo_deals.deals_id)
    UNION ALL 
          SELECT
      cuckoo_promotions_item.asin,
      'promotion' AS `type`,
      'N/A' AS minPrice,
      'N/A' AS maxPrice,
	   'N/A' as price,
      cuckoo_promotions.discountType,
      cuckoo_promotions.discount,
      CASE
    WHEN '".$today."' < cuckoo_promotions.startDate THEN
      'Pending'
    WHEN '".$today."' > cuckoo_promotions.endDate THEN
      'Ended'
    ELSE
      'Active'
    END AS `status`,
    cuckoo_promotions.startDate as startDate,
	cuckoo_promotions.endDate as endDate,
    cuckoo_promotions.marketplaceId,
    cuckoo_promotions.seller_id
  FROM
    cuckoo_promotions_item
  LEFT JOIN cuckoo_promotions ON (cuckoo_promotions_item.promotions_id = cuckoo_promotions.promotions_id)
  ) AS tmp  
  WHERE seller_id ='".$sellerid."' 
	AND STATUS ='Active' 
  ORDER BY
  tmp.asin DESC,
  tmp.startDate ASC";
//		echo $sql;exit;
		$_data = DB::select($sql);
		$_data = json_decode(json_encode($_data),true);
		$data = array();
		foreach($_data as $key=>$val){
			$marketplaceid_sellerid_asin = $val['marketplaceId'].'_'.$sellerid.'_'.$val['asin'];
			$data[$marketplaceid_sellerid_asin][$val['type']][] = $val;
		}
		$data_account = getSellerAccout();
		$asinListingPrice = $this->getAsinListingPrice('',$sellerid);
		foreach($asinListingPrice as $key=>$val){
			$listing_price = $val;//折扣后的价格
			//deal折扣数据,deal的数据折扣类型只有money，deal的price为购物车价格
			if(isset($data[$key]['deal']) && $data[$key]['deal'] ){
				foreach($data[$key]['deal'] as $key_c=>$val_c){
					if($val_c['discountType']=='money'){
						if($val_c['price'] < $listing_price){//购物车价格小于原价的话，就把购物车价格赋值
							$listing_price = $val_c['price'];
						}
					}
				}
			}

			//coupon折扣数据
			$coupon_max_amount = 0.00;
			if(isset($data[$key]['coupon']) && $data[$key]['coupon'] ){
				foreach($data[$key]['coupon'] as $key_c=>$val_c){
					if($val_c['discountType']=='money'){
						if($val_c['discount'] > $coupon_max_amount){
							$coupon_max_amount = $val_c['discount'];
						}
					}
					if($val_c['discountType']=='percent'){
						if($val_c['discount']/100*$listing_price > $coupon_max_amount){
							$coupon_max_amount = $val_c['discount']/100*$listing_price;
						}
					}
				}
			}

			//promotion折扣数据
			$promotion_max_amount = 0.00;
			if(isset($data[$key]['promotion']) && $data[$key]['promotion'] ){
				foreach($data[$key]['promotion'] as $key_p=>$val_p){
					if($val_p['discountType']=='money'){
						if($val_p['discount'] > $promotion_max_amount){
							$promotion_max_amount = $val_p['discount'];
						}
					}
				}
			}

			//折扣后的价格再减去coupon折扣
			$discount_price = $listing_price - $coupon_max_amount - $promotion_max_amount;

			$marketplaceid_sellerid_asin_arr = explode('_',$key);
			$marketplace_id = $marketplaceid_sellerid_asin_arr[0];
			$asin = $marketplaceid_sellerid_asin_arr[2];

			//设置的每个asin能打的最大折扣，如果没有设置某个asin的最大折扣就用共用的最大折扣设定值
			$max_discount = isset($config_max_discount[$asin]) ? $config_max_discount[$asin] : $config_max_discount_common;
			//折扣后的价格<listing的价格打了最大折扣后的价格，就说明价格异常
			if($discount_price < $listing_price * $max_discount){
				$account_name = isset($data_account[$sellerid.'_'.$marketplace_id]) ? $data_account[$sellerid.'_'.$marketplace_id] : $marketplace_id;
				$msg = '账号:'.$account_name.';Asin:'.$asin.';价格异常(折后价为:'.$discount_price.')';
				$return = array('result'=>'Warning','msg'=>$msg);
				return json_encode($return);
//				$exception[] = $marketplaceId_asin;
			}
		}

		$return = array('result'=>'Normal','msg'=>'');
		return json_encode($return);
    }

    /*
     * 得到asin的listing价格
     */
	public function getAsinListingPrice($marketplace_id,$sellerid_str)
	{
		$today = date('Y-m-d');
//		$today = '2021-12-03';//测试数据
		//查询出该账号的所有asin的销售价格
		$price_sql = "SELECT ASIN as asin,asin_offer_lowest.listing_price as listing_price,asin_offer_summary.marketplace_id AS marketplace_id,asin_offers.seller_id as seller_id 
FROM asin_offer_summary
LEFT JOIN asin_offer_lowest ON asin_offer_summary.id = asin_offer_lowest.asin_offer_summary_id 
LEFT JOIN asin_offers ON asin_offer_summary.id = asin_offers.asin_offer_summary_id ";
		$where = " where asin_offer_lowest.fulfillment_channel ='Amazon' AND asin_offer_summary.date = '".$today."' ";
		if($marketplace_id){
			$where .= " and asin_offer_summary.marketplace_id = '".$marketplace_id."'";
		}
		if($sellerid_str){
			$where .= " and asin_offers.seller_id in('".$sellerid_str."')";
		}
		$price_sql = $price_sql . $where ." ORDER BY asin_offer_summary.asin desc";
//		echo $price_sql;exit;
		$_price_data = DB::connection('amazon')->select($price_sql);
		$_price_data = json_decode(json_encode($_price_data),true);
		$price_data = array();
		foreach($_price_data as $key=>$val){
			$price_data[$val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['asin']] = $val['listing_price'];
		}
		return $price_data;
	}



}