<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;


class CcpAdProductController extends Controller
{
        /**
         * Create a new controller instance.
         *
         * @return void
         *
         */
        use \App\Traits\DataTables;
        use \App\Traits\Mysqli;

        public $ccpAdmin = array("xumeiling@valuelinkcorp.com","lidan@valuelinkcorp.com","liuling@dtas.com","wuweiye@valuelinkcorp.com","luodenglin@valuelinkcorp.com","zhouzhiwen@valuelinkltd.com","zhangjianqun@valuelinkcorp.com","sunhanshan@valuelinkcorp.com","wangxiaohua@valuelinkltd.com","zhoulinlin@valuelinkcorp.com","wangshuang@valuelinkltd.com","lixiaojian@valuelinkltd.com");
        public $start_date = '';//搜索时间范围的开始时间
        public $end_date = '';//搜索时间范围的结束时间

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
                if(!Auth::user()->can(['ccp-ad-product-show'])) die('Permission denied -- ccp ad product show');
                $bgs = $this->queryFields('SELECT DISTINCT bg FROM asin order By bg asc');
                $bus = $this->queryFields('SELECT DISTINCT bu FROM asin order By bu asc');
                $site = getMarketDomain();//获取站点选项
                $this->date = date('Y-m-d');

                $siteDate = array();
                foreach($site as $kk=>$vv){
                        $siteDate[$vv->marketplaceid] = date('Y-m-d',$this->getCurrentTime($vv->marketplaceid,1));
                }
                $date = $siteDate[current($site)->marketplaceid];

                return view('ccp/ad_product',['bgs'=>$bgs,'bus'=>$bus,'site'=>$site,'date'=>$date,'siteDate'=>$siteDate]);
        }
        /*
        * 获得统计总数据
         */
        public function showTotal()
        {
            //搜索条件，统计数据不受下面的asin搜索的影响
			$search = isset($_REQUEST['search_data']) ? $_REQUEST['search_data'] : '';
			$search = $this->getSearchData(explode('&',$search));
			$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
			$account = isset($search['account']) ? $search['account'] : '';//账号id,seller_id
			$bg = isset($search['bg']) ? $search['bg'] : '';
			$bu = isset($search['bu']) ? $search['bu'] : '';
			$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
			$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
			$domain = substr(getDomainBySite($site), 4);//orders.sales_channel
			$siteCur = getSiteCur();
			$currency_code = isset($siteCur[$domain]) ? $siteCur[$domain] : '';

			//时间搜索范围
			$where = $this->getDateWhere($site);
			$where_profile = " and marketplaces.marketplace = '".$site."'";

			if($account){
					$account_str = implode("','", explode(',',$account));
					$where_profile .= " and accounts.seller_id in('".$account_str."')";
			}

			//用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据，
			$asin_sellersku_arr = $this->getSellerSkuData($site,$bg,$bu);
			$asin_sellersku_str = implode("','", $asin_sellersku_arr);
			$where .= " and CONCAT(ppc_product_ads.asin,'_',ppc_product_ads.sku) in('".$asin_sellersku_str."')";


			//sales数据，orders数据
			$sql ="SELECT  
									round(sum(ppc_reports.cost),2) as cost,
									round(sum(ppc_reports.attributed_sales1d),2) as sales
							FROM
									ppc_product_ads
							LEFT JOIN ppc_reports ON (
									ppc_reports.record_type = 'Ppc::ProductAd'
									AND ppc_product_ads.ad_id = ppc_reports.record_type_id
							)
							WHERE
									ppc_reports.profile_id IN (
											SELECT
													ppc_profiles.profile_id
											FROM
													accounts,
													ppc_profiles,
													marketplaces
											WHERE
													accounts.user_id = 8566
											AND ppc_profiles.account_id = accounts.id
											AND accounts.marketplace_id = marketplaces.id 
										{$where_profile}
									)
							{$where}";

			$orderData = DB::connection('ad')->select($sql);
			$array = array(
					'sales' => round($orderData[0]->sales,2),
					'cost' => round($orderData[0]->cost,2),
					'danwei' => $currency_code,
			);
			return $array;
        }

        //展示列表数据
        public function list(Request $req)
        {
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
        	$site = isset($search['site']) ? $search['site'] : '';//站点，为marketplaceid
        	$account = isset($search['account']) ? $search['account'] : '';//账号id,例如115,137
			$bg = isset($search['bg']) ? $search['bg'] : '';
			$bu = isset($search['bu']) ? $search['bu'] : '';
        	$asin = isset($search['asin']) ? trim($search['asin'],'+') : '';//asin输入框的值
			$this->start_date = isset($search['start_date']) ? $search['start_date'] : '';
			$this->end_date = isset($search['end_date']) ? $search['end_date'] : '';
			//时间搜索范围
			$where = $this->getDateWhere($site);
			$where_profile = " and marketplaces.marketplace = '".$site."'";
			if($account){
					$account_str = implode("','", explode(',',$account));
					$where_profile .= " and accounts.seller_id in('".$account_str."')";
			}

			//用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据，
			$asin_sellersku_arr = $this->getSellerSkuData($site,$bg,$bu);
			$asin_sellersku_str = implode("','", $asin_sellersku_arr);
			$where .= " and CONCAT(ppc_product_ads.asin,'_',ppc_product_ads.sku) in('".$asin_sellersku_str."')";
			if($asin){
					$where .= " and asin = '".$asin."'";
			}

			if($_REQUEST['length']){
					$limit = $this->dtLimit($req);
					$limit = " LIMIT {$limit} ";
			}

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
                                        ppc_product_ads.asin,
                                        round(sum(ppc_reports.cost),2) as cost,
                                        sum(ppc_reports.clicks) as clicks,
                                        round(sum(ppc_reports.attributed_sales1d),2) as sales,
                                        sum(ppc_reports.attributed_conversions1d_same_sku) as orders,
                                        sum(ppc_reports.impressions) as impressions
                                FROM
                                        ppc_product_ads
                                LEFT JOIN ppc_reports ON (
                                        ppc_reports.record_type = 'Ppc::ProductAd'
                                        AND ppc_product_ads.ad_id = ppc_reports.record_type_id
                                )
                                WHERE
                                        ppc_reports.profile_id IN (
                                                SELECT
                                                        ppc_profiles.profile_id
                                                FROM
                                                        accounts,
                                                        ppc_profiles,
                                                        marketplaces
                                                WHERE
                                                        accounts.user_id = 8566
                                                AND ppc_profiles.account_id = accounts.id
                                                AND accounts.marketplace_id = marketplaces.id 
                                            {$where_profile}
                                
                                        )
                                {$where}
                                
                                GROUP BY ppc_product_ads.asin 
                                 order by sales desc {$limit}";


                $_data = DB::connection('ad')->select($sql);
                $recordsTotal = $recordsFiltered = DB::connection('ad')->select('SELECT FOUND_ROWS() as total');
                $recordsTotal = $recordsFiltered = $recordsTotal[0]->total;

                $domain = substr(getDomainBySite($site), 4);
                //AD CONVERSION RATE = orders/click  CTR = click/impressions  cpc = sum(cost*clicks)/sum(clicks)  acos=cost/sales
                $data = array();
                $asins = array();
                foreach($_data as $key=>$val){
                        $val = (array)$val;
                        $asins[] = $val['asin'];
                        $val['title'] = $val['item_no'] = $val['image'] = '/NA';
                        $val['acos'] = $val['sales'] > 0 ? sprintf("%.2f",$val['cost']*100/$val['sales']).'%' : '-';
                        $val['ctr'] = $val['impressions'] > 0 ? sprintf("%.2f",$val['clicks']*100/$val['impressions']).'%' : '-';
                        $val['cpc'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['cost']/$val['clicks']) : '-';
                        $val['cr'] = $val['clicks'] > 0 ? sprintf("%.2f",$val['orders']*100/$val['clicks']).'%' : '-';
                        $data[$val['asin']] = $val;
                        $data[$val['asin']]['asin'] = '<a href="https://www.' .$domain. '/dp/' . $val['asin'] .'" target="_blank" rel="noreferrer">'.$val['asin'].'</a>';
                }
		 if($asins){
                        $asins = "'".implode("','",$asins)."'";
                        $product_sql = "select max(title) as title,max(images) as images,asin,max(sku) as item_no
                                                from asins
                                                where asin in({$asins})
                                                and marketplaceid = '{$site}'
                                                group by asin ";

                        $productData = DB::connection('vlz')->select($product_sql);
                        foreach($productData as $pkey=>$pval){
                                if(isset($data[$pval->asin])){
                                        $title = mb_substr($pval->title,0,50);
                                        $data[$pval->asin]['title'] = '<span title="'.$pval->title.'">'.$title.'</span>';
                                        $data[$pval->asin]['item_no'] = $pval->item_no ? $pval->item_no : $data[$pval['asin']]['item_no'];
                                        if($pval->images){
                                                $imageArr = explode(',',$pval->images);
                                                if($imageArr){
                                                        $image = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
                                                        $data[$pval->asin]['image'] = '<a href="https://www.' .$domain. '/dp/' . $pval->asin .'" target="_blank" rel="noreferrer"><image style="width:50px;height:50px;" src="'.$image.'"></a>';
                                                }
                                        }
                                }
                        }
                }
                $data = array_values($data);

                return compact('data', 'recordsTotal', 'recordsFiltered');
        }
        /*
         * 用户权限数据，通过sap_asin_match_sku表得到可查范围的asin_selersku组合数据
         */
        public function getSellerSkuData($site,$bg,$bu)
        {
                $userdata = Auth::user();
                $userWhere = " where LENGTH(asin)=10 and marketplace_id  = '".$site."'";
                if (!in_array(Auth::user()->email, $this->ccpAdmin)) {
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
                $sql_user = " select DISTINCT CONCAT(sap_asin_match_sku.asin,'_',sap_asin_match_sku.seller_sku) as asin_sku  from sap_asin_match_sku {$userWhere}";
                $_data = DB::connection('vlz')->select($sql_user);
                $data = array();
                foreach($_data as $key=>$val){
                        $data[] = $val->asin_sku;
                }
                return $data;
        }

        //得到搜索时间的sql
        public function getDateWhere($site)
        {
                $startDate = date('Y-m-d',strtotime($this->start_date));//开始时间
                $endDate = date('Y-m-d',strtotime($this->end_date));//结束时间
                $where = " and ppc_reports.date >= '".$startDate."' and ppc_reports.date <= '".$endDate."'";
                return $where;
        }

}