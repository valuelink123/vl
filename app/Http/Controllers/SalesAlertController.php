<?php

namespace App\Http\Controllers;

use App\SalesAlert;
use Illuminate\Http\Request;
use App\Qa;
use Illuminate\Support\Facades\Session;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;

class SalesAlertController extends Controller
{
    use \App\Traits\DataTables;
    use \App\Traits\Mysqli;
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
		//if(!Auth::user()->can(['sales-alert-show'])) die('Permission denied -- sales-alert-show');
        $data = new SalesAlert;
        $order_by = 'created_at';
        $sort = 'desc';

        $data = $data->orderBy($order_by,$sort)->get()->toArray();
        return view('salesAlert/index',['data'=>$data]);
    }

    public function create()
    {
        $bg = '';
        $bgs = $this->getBg($bg);
        return view('salesAlert/add',['bgs'=>$bgs]);
    }

    public function store(Request $request)
    {
		//if(!Auth::user()->can(['qa-category-create'])) die('Permission denied -- qa-category-create');
        $salesAlert = new SalesAlert();
        $salesAlert->department = $request->get('department');
        $salesAlert->start_time = $request->get('start_time');
        $salesAlert->end_time = $request->get('end_time');
        $salesAlert->year = $request->get('year');
        $salesAlert->month = $request->get('month');
        $salesAlert->sales = $request->get('sales');
        $salesAlert->marketing_expenses = $request->get('marketing_expenses');
        $salesAlert->creatrd_user = Auth::user()->name;

        if ($salesAlert->save()) {
            $request->session()->flash('success_message','Set SalesAlert Success');
            return redirect('salesAlert');
        } else {
            $request->session()->flash('error_message','Set SalesAlert Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        //if(!Auth::user()->can(['qa-category-delete'])) die('Permission denied -- qa-category-delete');
        $data = new SalesAlert();

        $lists = $data->where('id',$id)->get()->toArray();
        if(!empty($lists)){
            SalesAlert::where('id',$id)->delete();
            $request->session()->flash('success_message','Delete SalesAlert Success');
        }else{
            $request->session()->flash('error_message','Delete SalesAlert Failed');
        }
        return redirect('salesAlert');
    }

    public function edit(Request $request,$id)
    {
        //if(!Auth::user()->can(['qa-category-show'])) die('Permission denied -- qa-category-show');
        $bg = '';
        $bgs = $this->getBg($bg);
        $data = SalesAlert::where('id',$id)->first()->toArray();

        if(!$data){
            $request->session()->flash('error_message','SalesAlert not Exists');
            return redirect('salesAlert');
        }
        return view('salesAlert/edit',['data'=>$data,'bgs'=>$bgs]);
    }

    public function update(Request $request,$id)
    {
        //if(!Auth::user()->can(['qa-category-update'])) die('Permission denied -- qa-category-update');

        $salesAlert = SalesAlert::findOrFail($id);
        $salesAlert->department = $request->get('department');
        $salesAlert->start_time = $request->get('start_time');
        $salesAlert->end_time = $request->get('end_time');
        $salesAlert->year = $request->get('year');
        $salesAlert->month = $request->get('month');
        $salesAlert->sales = $request->get('sales');
        $salesAlert->marketing_expenses = $request->get('marketing_expenses');
        $salesAlert->creatrd_user = Auth::user()->name;

        if ($salesAlert->save()) {
            $request->session()->flash('success_message','Set salesAlert Success');
            return redirect('salesAlert');
        } else {
            $request->session()->flash('error_message','Set salesAlert Failed');
            return redirect()->back()->withInput();
        }
    }

    /**
     * 销售额报警（sku）维度
    */
    public function salesAlertSku(){

        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        $site = getMarketDomain();//获取站点选项
        $bg = '';
        $bgs = $this->getBg($bg);

        return view('salesAlert/salesAlertSku',['start_date'=>$start_date,'end_date'=>$end_date,'site'=>$site,'bgs'=>$bgs]);
    }

    //展示列表数据
    public function salesAlertTotalBgList(Request $req)
    {
        set_time_limit(0);
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
        $search = $this->getSearchData(explode('&',$search));
        //$data = $this->getTotalSkuData($search);

        $start_date = isset($search['start_date']) ? $search['start_date'] : '';
        $end_date = isset($search['end_date']) ? $search['end_date'] : '';
        //$start_date = '2020-01-01';//测试时间
        //$end_date = '2020-03-01';//测试时间
        $site = isset($search['site']) ? $search['site'] : '';
        $bg = isset($search['bg']) ? $search['bg'] : '';
        //$bg = 'BG1';

        $_proportion = $this->getProportion($bg);

        $data = array();
        if($_proportion) {
            //$_sales = $this->getSales($site,$start_date,$end_date,$bg);//求sku销售额

            $bu = '';
            $timeType = '';//时间类型，默认是0为北京时间，1为亚马逊后台当地时间
            $where = $orderwhere = $this->getDateWhere($site,$start_date,$end_date,$timeType);
            $domain = substr(getDomainBySite($site), 4);//orders.sales_channel
            $orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
            //用户权限sap_asin_match_sku
            $userwhere = $this->getUserWhere($site,$bg,$bu);
            $sql ="SELECT SQL_CALC_FOUND_ROWS kk.sku,SUM( item_price_amount ) AS sales,SUM( quantity_ordered ) AS units,any_value(kk.marketplace_id) as marketplace_id,any_value(kk.mws_seller_id) as seller_id
			FROM
			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,sap_asin_match_sku.sku,asin_price.seller_account_id,seller_accounts.mws_seller_id,asin_price.marketplace_id,
			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount,
			   		LENGTH( order_items.promotion_ids )> 10 AS PROMO,promotion_discount_amount as c_promotionAmount,
			  		CASE WHEN LENGTH( order_items.promotion_ids )> 10 THEN amazon_order_id ELSE '' END AS PROMO_ORDER_ID
			  	FROM order_items
				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."'
				left join sap_asin_match_sku on (order_items.asin=sap_asin_match_sku.asin and order_items.seller_sku=sap_asin_match_sku.seller_sku)
				left join seller_accounts on seller_accounts.id=order_items.seller_account_id
			  	WHERE order_items.amazon_order_id IN
				  (
					SELECT amazon_order_id
					FROM orders
					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwhere}
				  )
			  	{$where}
			  	and order_items.asin in({$userwhere})
			) AS kk GROUP BY kk.sku order by sales desc";
//            $_sales = DB::connection('vlz')->select($sql);
//            $_sales = json_decode(json_encode($_sales),true);

            if($req['length'] != '-1'){//等于-1时为查看全部的数据
                $limit = $this->dtLimit($req);
                $sql .= " LIMIT {$limit} ";
            }

            $_dataA = DB::connection('amazon')->select($sql);
            //print_r($_dataA);exit();
            $_sales = json_decode(json_encode($_dataA),true);

            $recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
            //print_r($_sales);exit();
            if(empty($_sales)){
                //echo 1111;exit();
                $data['sku'] = '';
                $data['ad_sales'] = '';
                $data['ad_cost'] = '';
                $data['proportion'] = '';
                return compact('data', 'recordsTotal', 'recordsFiltered');
            }

            $_data = $this->getAdData($site,$start_date,$end_date,$bg);//得到站点广告数据
            $skuData = $this->getSapAsinMatchSkuInfoTo($site,$bg);
            $proportion = round(($_proportion[0]['marketing_expenses']/$_proportion[0]['sales'])*100,2);//设置的占比

            foreach($_sales as $sk=>$sv){
                $data[$sv['sku']]['sku'] = $sv['sku'];
                $data[$sv['sku']]['ad_sales'] = $data[$sv['sku']]['ad_cost'] = 0.00;
                $data[$sv['sku']]['proportion'] = '-';
            }

            foreach($_sales as $key=>$val){
                $_key1 = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['sku'];
                $_key3 = $val['marketplace_id'].'_'.$val['sku'];

                foreach($_data as $k=>$v){
                    $_key2 = $v['marketplace_id'].'_'.$v['sku2'];
                    if($_key2 == $_key3){
                        $sku = $skuData[$_key1]['sku'];
                        $data[$sku]['ad_cost'] += $v['ad_cost'];
                        $data[$sku]['ad_sales'] += $val['sales'];
                    }
                }
            }

            if($data) {
                foreach ($data as $key => $val) {
                    $data[$key]['sku'] = $val['sku'];
                    $data[$key]['ad_sales'] = sprintf("%.2f", $val['ad_sales']);
                    $data[$key]['ad_cost'] = sprintf("%.2f", $val['ad_cost']);
                    $data[$key]['proportion'] = $val['ad_sales'] > 0 ? (round(($val['ad_cost']/$val['ad_sales'])*100,2) > $proportion ? "<span style='color: red;'>". round(($val['ad_cost']/$val['ad_sales'])*100,2)."%</span>" : "<span>". round(($val['ad_cost']/$val['ad_sales'])*100,2)."%</span>") : '-';
                }
            }
            $data = array_values($data);

        }else{
            $data['sku'] = '';
            $data['ad_sales'] = '';
            $data['ad_cost'] = '';
            $data['proportion'] = '';
            $recordsTotal = $recordsFiltered = 0;
        }
        //print_r($data);exit();
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    /**
     * 销售额报警（周）维度
     */
    public function salesAlertWeek(){
        echo 33;exit();
    }

    //得到sku维度统计数据
//    public function getTotalSkuData($search)
//    {
////        $start_date = isset($search['start_date']) ? $search['start_date'] : '';
////        $end_date = isset($search['end_date']) ? $search['end_date'] : '';
//        $start_date = '2020-01-01';//测试时间
//        $end_date = '2021-12-01';//测试时间
//        $site = isset($search['site']) ? $search['site'] : '';
//        //$bg = isset($search['bg']) ? $search['bg'] : '';
//        $bg = 'BG1';
//
//        $_proportion = $this->getProportion($bg);
//
//        $data = array();
//        if($_proportion) {
//            $_sales = $this->getSales($site,$start_date,$end_date,$bg);//求sku销售额
//
//            $_data = $this->getAdData($site,$start_date,$end_date,$bg);//得到站点广告数据
//
//            $skuData = $this->getSapAsinMatchSkuInfoTo($site,$bg);
//
//            $proportion = round(($_proportion[0]['marketing_expenses']/$_proportion[0]['sales'])*100,2);//设置的占比
//
//            foreach($_sales as $sk=>$sv){
//                $data[$sv['sku']]['sku'] = $sv['sku'];
//                $data[$sv['sku']]['ad_cost'] = $data[$sv['sku']]['ad_sales'] = 0.00;
//                $data[$sv['sku']]['proportion'] = '-';
//            }
//
//            foreach($_sales as $key=>$val){
//                $_key1 = $val['marketplace_id'].'_'.$val['seller_id'].'_'.$val['sku'];
//                $_key3 = $val['marketplace_id'].'_'.$val['sku'];
//                foreach($_data as $k=>$v){
//                    $_key2 = $v['marketplace_id'].'_'.$v['sku2'];
//                    if($_key2 == $_key3){
//                        $sku = $skuData[$_key1]['sku'];
//                        $data[$sku]['ad_cost'] += $v['ad_cost'];
//                        $data[$sku]['ad_sales'] += $val['sales'];
//                    }
//                }
//
//            }
//
//            if($data) {
//                foreach ($data as $key => $val) {
//                    $data[$key]['sku'] = $val['sku'];
//                    $data[$key]['ad_cost'] = sprintf("%.2f", $val['ad_cost']);
//                    $data[$key]['ad_sales'] = sprintf("%.2f", $val['ad_sales']);
//                    $data[$key]['proportion'] = $val['ad_sales'] > 0 ? (round(($val['ad_cost']/$val['ad_sales'])*100,2) > $proportion ? "<span style='color: red;'>". round(($val['ad_cost']/$val['ad_sales'])*100,2)."%</span>" : "<span>". round(($val['ad_cost']/$val['ad_sales'])*100,2)."%</span>") : '-';
//                }
//            }
//        }
//
//        $data = array_values($data);
//        return $data;
//    }

    //销售量
//    public function getSales($site,$start_date,$end_date,$bg)
//    {
//        $bu = '';
//        $timeType = '';//时间类型，默认是0为北京时间，1为亚马逊后台当地时间
//        $where = $orderwhere = $this->getDateWhere($site,$start_date,$end_date,$timeType);
//        $domain = substr(getDomainBySite($site), 4);//orders.sales_channel
//        $orderwhere .= " and sales_channel = '".ucfirst($domain)."'";
////        //用户权限sap_asin_match_sku
//        $userwhere = $this->getUserWhere($site,$bg,$bu);
//        $sql ="SELECT SQL_CALC_FOUND_ROWS kk.sku,SUM( item_price_amount ) AS sales,SUM( quantity_ordered ) AS units,any_value(kk.marketplace_id) as marketplace_id,any_value(kk.mws_seller_id) as seller_id
//			FROM
//			  (SELECT order_items.amazon_order_id,order_items.asin,asin_price.price AS default_unit_price,order_items.quantity_ordered,sap_asin_match_sku.sku,asin_price.seller_account_id,seller_accounts.mws_seller_id,asin_price.marketplace_id,
//			  		CASE order_items.item_price_amount WHEN 0.00 THEN asin_price.price * order_items.quantity_ordered ELSE order_items.item_price_amount END AS item_price_amount,
//			   		LENGTH( order_items.promotion_ids )> 10 AS PROMO,promotion_discount_amount as c_promotionAmount,
//			  		CASE WHEN LENGTH( order_items.promotion_ids )> 10 THEN amazon_order_id ELSE '' END AS PROMO_ORDER_ID
//			  	FROM order_items
//				LEFT JOIN asin_price ON order_items.seller_account_id = asin_price.seller_account_id   AND order_items.asin = asin_price.asin  AND asin_price.marketplace_id = '".$site."'
//				left join sap_asin_match_sku on (order_items.asin=sap_asin_match_sku.asin and order_items.seller_sku=sap_asin_match_sku.seller_sku)
//				left join seller_accounts on seller_accounts.id=order_items.seller_account_id
//			  	WHERE order_items.amazon_order_id IN
//				  (
//					SELECT amazon_order_id
//					FROM orders
//					WHERE order_status IN ( 'PendingAvailability', 'Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'InvoiceUnconfirmed', 'Unfulfillab' ) {$orderwhere}
//				  )
//			  	{$where}
//			  	and order_items.asin in({$userwhere})
//			) AS kk GROUP BY kk.sku order by sales desc";
//        $_sales = DB::connection('vlz')->select($sql);
//        $_sales = json_decode(json_encode($_sales),true);
//        return $_sales;
//    }

    //得到搜索时间的sql
    public function getDateWhere($site,$start_date,$end_date,$timeType='')
    {
        $startDate = $start_date;
        $endDate = $end_date;
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
    public function getUserWhere($site,$bg,$bu)
    {
        $userdata = Auth::user();
        $userWhere = " where marketplace_id  = '".$site."'";
        //if (!in_array(Auth::user()->email, $this->ccpAdmin)) {
        if (Auth::user()->email) {
            if ($userdata->seller_rules) {
                $rules = explode("-", $userdata->seller_rules);
                if (array_get($rules, 0) != '*') $userWhere .= " and sap_seller_bg = '".array_get($rules, 0)."'";
                if (array_get($rules, 1) != '*') $userWhere .= " and sap_seller_bu = '".array_get($rules, 1)."'";
            }elseif($userdata->sap_seller_id){
                $userWhere .= " and sap_seller_id = ".$userdata->sap_seller_id;
            }
        }

        if($bg){
            $userWhere .= " and sap_seller_bg = '".$bg."'";
        }
        if($bu){
            $userWhere .= " and sap_seller_bu = '".$bu."'";
        }
        $userWhere = "select DISTINCT sap_asin_match_sku.asin from sap_asin_match_sku {$userWhere}
					UNION ALL
					select DISTINCT asin_match_relation.asin from asin_match_relation {$userWhere}";
        return $userWhere;
    }

    /**
     * 得到设置占比数据
     */
    public function getProportion($bg){

        $sql = "select * from sales_alert WHERE department = '".$bg."' limit 1";
        $_data = DB::select($sql);
        $_data = json_decode(json_encode($_data),true);

        return $_data;
    }

    /*
	 * 得到广告数据
	 */
    public function getAdData($site,$start_date,$end_date,$bg='',$bu='')
    {
        //		$start_date = '2021-08-01';//测试时间
//		$end_date = '2021-12-01';//测试时间
        $userAsins = $this->getUserAsin($site,$bg,$bu);
        $userAsins_str = implode("','",$userAsins);
        $sql="select SQL_CALC_FOUND_ROWS any_value(union_table.marketplace_id) as marketplace_id,any_value(union_table.seller_id) as seller_id,any_value(union_table.sku) as sku,any_value(union_table.sku2) as sku2,sum(union_table.cost) as ad_cost,sum(union_table.attributed_sales1d) as ad_sales from (SELECT data.`date`,data.impressions,data.clicks,data.cost,data.attributed_sales1d,data.attributed_units_ordered1d,ads.asin,ads.sku,profile.seller_id,profile.marketplace_id,profile.account_name,asin.item_no as sku2
FROM ppc_report_datas as data
LEFT JOIN ppc_profiles as profile ON (data.profile_id=profile.profile_id)
LEFT JOIN ppc_sproducts_ads as ads ON (ads.ad_id=data.record_type_id )
left join asin on (ads.sku=asin.sellersku)
WHERE data.date BETWEEN '".$start_date."' AND '".$end_date."'
AND data.ad_type='SProducts' AND data.record_type='ad'
AND profile.marketplace_id='".$site."'
UNION ALL
SELECT data.`date`,data.impressions,data.clicks,data.cost,data.attributed_sales1d,data.attributed_units_ordered1d,ads.asin,ads.sku,profile.seller_id,profile.marketplace_id,profile.account_name,asin.item_no as sku2
FROM ppc_report_datas as data
LEFT JOIN ppc_profiles as profile ON (data.profile_id=profile.profile_id)
LEFT JOIN ppc_sdisplay_ads as ads ON (ads.ad_id=data.record_type_id )
left join asin on (ads.sku=asin.sellersku)
WHERE data.date BETWEEN '".$start_date."' AND '".$end_date."'
AND data.ad_type='SDisplay' AND data.record_type='ad'
AND profile.marketplace_id='".$site."'
    ) as union_table
    where CONCAT(union_table.asin,'_',union_table.sku)  in('".$userAsins_str."')
    group by union_table.sku2 order by ad_sales desc";
//		echo $sql;exit;
        $_data = DB::select($sql);
        $_data = json_decode(json_encode($_data),true);
        return $_data;
    }

}