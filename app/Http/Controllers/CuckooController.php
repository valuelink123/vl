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
		return view('cuckoo/show',['site'=>$site,'startDateFrom'=>$startDateFrom,'endDateFrom'=>$endDateFrom,'startDateTo'=>$startDateTo,'endDateTo'=>$endDateTo]);
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

		$where = "where marketplaceId = '".$marketplace_id."' and startDate >= '".$startDate_from." 00:00:00' and startDate<= '".$startDate_to." 23:59:59' and endDate>= '".$endDate_from." 00:00:00' and endDate<= '".$endDate_to." 23:59:59'";
		if($account){
			$account = implode("','",explode(",",$account));
			$account = "'".$account."'";
			$where .= ' and seller_id in('.$account.')';
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
  LEFT JOIN cuckoo_coupons ON (
    cuckoo_coupons_item.coupons_id = cuckoo_coupons.coupons_id
  )
  UNION ALL
    SELECT
      cuckoo_deals_item.asin,
      'deal' AS `type`,
      cuckoo_deals_item.dealPrice AS minPrice,
      cuckoo_deals_item.dealPrice AS maxPrice,
      'All' AS customers,
      'money' AS discountType,
      0.00 AS discount,
      '0%' AS save,
      0.00 AS budget,
      cuckoo_deals.`status`,
      cuckoo_deals.startDate,
      cuckoo_deals.endDate,
      cuckoo_deals.marketplaceId,
      cuckoo_deals.seller_id
    FROM
      cuckoo_deals_item
    LEFT JOIN cuckoo_deals ON (
      cuckoo_deals_item.deals_id = cuckoo_deals.deals_id
    )
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
		foreach($data as $key=>$val){
			$sellerid_marketplaceid = $val['seller_id'].'_'.$val['marketplaceId'];
			$data[$key]['site'] = isset($siteDomain[$val['marketplaceId']]) ? $siteDomain[$val['marketplaceId']] : $val['marketplaceId'];
			$data[$key]['account_name'] = isset($sellerAccount[$sellerid_marketplaceid]) ? $sellerAccount[$sellerid_marketplaceid] : $sellerid_marketplaceid;
//			$data[$key]['action'] = '<a href="/cuckoo/view?type=deals&id='.$val['deals_id'].'" class="btn btn-success btn-xs" >View</a>';
			$data[$key]['action'] = '-';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}
    /*
     * 系统中显示列表数据
     */
	public function deals()
	{
		if(!Auth::user()->can(['cuckoo-show'])) die('Permission denied -- cuckoo-show');
		$site = getMarketDomain();//获取站点选项
		return view('cuckoo/deals',['site'=>$site]);
	}
	/*
	 * ajax展示模板列表
	 */
	public function dealsList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$marketplace_id= isset($search['site']) ? urldecode($search['site']) : '';
		$account= isset($search['account']) ? urldecode($search['account']) : '';

		$where = "where marketplaceId = '$marketplace_id'";
		if($account){
			$account = implode("','",explode(",",$account));
			$account = "'".$account."'";
			$where .= ' and seller_id in('.$account.')';
		}
		$limit = '';
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql ="select SQL_CALC_FOUND_ROWS * from cuckoo_deals {$where} {$limit}";

		$data = DB::select($sql);
		$recordsTotal = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = json_decode(json_encode($data),true);
		$siteDomain = getSiteDomain();//得到站点跟域名的键值对
		$sellerAccount = getSellerAccout();
		foreach($data as $key=>$val){
			$sellerid_marketplaceid = $val['seller_id'].'_'.$val['marketplaceId'];
			$data[$key]['site'] = isset($siteDomain[$val['marketplaceId']]) ? $siteDomain[$val['marketplaceId']] : $val['marketplaceId'];
			$data[$key]['account_name'] = isset($sellerAccount[$sellerid_marketplaceid]) ? $sellerAccount[$sellerid_marketplaceid] : $sellerid_marketplaceid;
			$data[$key]['action'] = '<a href="/cuckoo/view?type=deals&id='.$val['deals_id'].'" class="btn btn-success btn-xs" >View</a>';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
 * 系统中显示列表数据
 */
	public function coupons()
	{
		if(!Auth::user()->can(['cuckoo-show'])) die('Permission denied -- cuckoo-show');
		$site = getMarketDomain();//获取站点选项
		return view('cuckoo/coupons',['site'=>$site]);
	}
	/*
	 * ajax展示模板列表
	 */
	public function couponsList(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$marketplace_id= isset($search['site']) ? urldecode($search['site']) : '';
		$account= isset($search['account']) ? urldecode($search['account']) : '';

		$where = "where marketplaceId = '$marketplace_id'";
		if($account){
			$account = implode("','",explode(",",$account));
			$account = "'".$account."'";
			$where .= ' and seller_id in('.$account.')';
		}
		$limit = '';
		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql ="select SQL_CALC_FOUND_ROWS * from cuckoo_coupons {$where} {$limit}";

		$data = DB::select($sql);
		$recordsTotal = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = json_decode(json_encode($data),true);
		$siteDomain = getSiteDomain();//得到站点跟域名的键值对
		$sellerAccount = getSellerAccout();
		foreach($data as $key=>$val){
			$sellerid_marketplaceid = $val['seller_id'].'_'.$val['marketplaceId'];
			$data[$key]['site'] = isset($siteDomain[$val['marketplaceId']]) ? $siteDomain[$val['marketplaceId']] : $val['marketplaceId'];
			$data[$key]['account_name'] = isset($sellerAccount[$sellerid_marketplaceid]) ? $sellerAccount[$sellerid_marketplaceid] : $sellerid_marketplaceid;
			$data[$key]['action'] = '<a href="/cuckoo/view?type=coupons&id='.$val['coupons_id'].'" class="btn btn-success btn-xs" >View</a>';
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 查看明细
	 */
	public function view(Request $request)
	{
		$type = isset($_GET['type']) && $_GET['type'] ? $_GET['type'] : '';
		$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
		$config = array(
			'deals'=>array(
				'table'=>'cuckoo_deals_item',
				'tableField' => array('deals_id'=>'Deals ID','asin'=>'Asin','dealPrice'=>'Deal Price','dealQuantity'=>'Deal Quantity','sellerPrice'=>'Seller Price','sellerQuantity'=>'Seller Quantity','discountsPrice'=>'Discounts Price','created_at'=>'Created Date'),
				'idField' => 'deals_id',
			),
			'coupons'=>array(
				'table'=>'cuckoo_coupons_item',
				'tableField' => array('coupons_id'=>'Coupons ID','asin'=>'Asin','title'=>'Title','minPrice'=>'Min Price','maxPrice'=>'Max Price','inStock'=>'InStock','created_at'=>'Created Date'),
				'idField' => 'coupons_id',
			),
		);
		$table = isset($config[$type]) ? $config[$type]['table'] : '';
		$idField = isset($config[$type]) ? $config[$type]['idField'] : '';
		$tableField = isset($config[$type]) ? $config[$type]['tableField'] : '';
		if($table && $idField) {
			$data = DB::table($table)->where($idField, $id)->get();
			if ($data) {
				$data = json_decode(json_encode($data), true);
				return view('/cuckoo/view', ['data' => $data,'tableField'=>$tableField]);
			} else {
				$request->session()->flash('error_message', 'ID error');
				return redirect()->back()->withInput();
			}
		}
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
		}
		return 'ok';
    }

    public function feedback(){
		$input = file_get_contents("php://input");
		$sellerid = isset($_GET['sellerid']) ? $_GET['sellerid'] : '';
		Log::info('cuckoo-feedback-data:'.$sellerid);
		$return = array('result'=>'Normal','msg'=>'');
		$return = array('result'=>'Warning','msg'=>'该账号异常');
		return json_encode($return);
    }

}