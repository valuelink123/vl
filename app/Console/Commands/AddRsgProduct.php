<?php
/*
 * By wulanfang
 * Date: 2019.11.14
 * 说明：此批处理需每日凌晨跑数据
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Classes\SapRfcRequest;


class AddRsgProduct extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:rsgProduct';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	public function __destruct()
	{

	}

	//添加rsgProduct数据
	function handle()
	{
		set_time_limit(0);
		$today = date('Y-m-d');
		$yestoday = date('Y-m-d',strtotime($today)-86400);
		$yestodayYmd = date('Ymd',strtotime($today)-86400);
		Log::Info('Execution addRsgProduct.php script start time:'.$today."\n");
		//把asin表里的帖子状态和帖子类型统一化(新增加的asin数据帖子状态和帖子类型会置为0)，避免不统一化
		$asin_sql = "UPDATE asin as t1,(select asin,site,max(asin.post_status) as post_status,max(asin.post_type) as post_type  
				from asin 
				group by asin,site 
			) as t2 
		SET t1.post_status = t2.post_status,t1.post_type = t2.post_type WHERE t1.asin = t2.asin and t1.site = t2.site";
		DB::select($asin_sql);

		$sql = "SELECT asin.asin as asin,asin.site as site,any_value(asin.post_status) as post_status,any_value(asin.post_type) as post_type,any_value(asin.push_date) as push_date,any_value(item_no) as sku,any_value(star_history.total_star_number) as number_of_reviews,any_value(star_history.average_score) as review_rating,any_value(skus_week_details.ranking) as position,any_value(sku_keywords.keywords) as keyword,any_value(users.id) as user_id,any_value(asin.sellersku) as sellersku   
				from  (
					select asin,site,max(asin.sellersku) as sellersku 
					from asin 
					group by asin,site 
				) as asin2 
				left join asin on asin.asin = asin2.asin and asin.site = asin2.site and asin.sellersku = asin2.sellersku  
				LEFT JOIN users on users.sap_seller_id = asin.sap_seller_id 
				LEFT JOIN star_history on create_at = '".$yestoday."' and asin.asin = star_history.asin and asin.site = star_history.domain
				LEFT JOIN skus_week_details on weeks = '".$yestodayYmd."' and asin.asin = skus_week_details.asin and asin.site = skus_week_details.site
				LEFT JOIN (
					select t2.asin,t2.site,t2.weeks,keywords
					from skus_week_details as t2
					left join (
							select asin,site,max(weeks) as max_weeks
							from skus_week_details
							where keywords is not null group by asin,site
					) as t1 on t1.asin=t2.asin and t1.site=t2.site and t2.weeks = t1.max_weeks
					where max_weeks is not null
				) as sku_keywords on asin.asin = sku_keywords.asin and asin.site = sku_keywords.site
				WHERE 1 = 1
				GROUP BY asin,site";
		$data = $this->queryRows($sql);

		//取前一天产品表里面的order_status
		$order_sql = "select concat(asin,'_',site) as asin_site,any_value(rsg_products.order_status) as order_status 
					  from rsg_products 
					  where created_at = '".$yestoday."' 
					  group by asin,site";
		$_orderdata = $this->queryRows($order_sql);
		$orderdata = array();
		foreach($_orderdata as $key=>$val){
			$orderdata[$val['asin_site']]['order_status'] = $val['order_status'];
		}

		//sku状态信息
		$sapSiteCode = getSapSiteCode();
		$sku_sql = "select sku,sap_site_id,any_value(level) as sku_level from skus_status group by sku,sap_site_id";
		$_skuData = $this->queryRows($sku_sql);
		$skuData = array();
		foreach($_skuData as $key=>$val){
			$site = isset($sapSiteCode[$val['sap_site_id']]) ? 'www.'.$sapSiteCode[$val['sap_site_id']] : $val['sap_site_id'];
			$skuData[$val['sku'].'_'.$site]['sku_level'] = $val['sku_level'];
		}

		//求出当前库存可维持天数
		$sql = "SELECT
				sum(fba_stock + fba_transfer) AS fba_stock,
				sum(sales_07_01) AS sales_07_01,
				sum(sales_14_08) AS sales_14_08,
				sum(sales_21_15) AS sales_21_15,
				sum(sales_28_22) AS sales_28_22,
				asin,
				site
			FROM asin 
			GROUP BY asin,site";
		$_saleData = $this->queryRows($sql);
		$saleData = array();

		foreach($_saleData as $key=>$val){
			//平均日销量
			$sales = ((((array_get($val,'sales_07_01')??array_get($val,'sales_14_08'))??array_get($val,'sales_21_15'))??array_get($val,'sales_28_22'))??0)/7 ;
			//库存可维持的天数
			$saleData[$val['asin'].'_'.$val['site']] = $sales > 0 ? $val['fba_stock']/$sales : '10000';//每日销量为0的时候，默认可维持天数为10000
		}

		//取亚马逊的产品相关数据
		$amazon_sql = "select asin,marketplaceid,title,images,features,description,price,buybox_sellerid  
				from asins";
		$_amazonData = DB::connection('amazon')->select($amazon_sql);
		$amazonData = array();
		$siteUrl = getSiteUrl();
		foreach($_amazonData as $key=>$val){
			$site = isset($siteUrl[$val->marketplaceid]) ? 'www.'.$siteUrl[$val->marketplaceid] : $val->marketplaceid;
			$amazonData[$val->asin.'_'.$site]['asin'] = $val->asin;
			$amazonData[$val->asin.'_'.$site]['marketplaceid'] = $val->marketplaceid;
			$amazonData[$val->asin.'_'.$site]['title'] = str_replace('"',"'",$val->title);
			$amazonData[$val->asin.'_'.$site]['features'] = str_replace('"',"'",$val->features);
			$amazonData[$val->asin.'_'.$site]['description'] = str_replace('"',"'",$val->description);
			$amazonData[$val->asin.'_'.$site]['price'] = $val->price;
			$amazonData[$val->asin.'_'.$site]['seller_id'] = $val->buybox_sellerid;
			$imageArr = explode(',',$val->images);
			if($imageArr){
				$amazonData[$val->asin.'_'.$site]['image'] = 'https://images-na.ssl-images-amazon.com/images/I/'.$imageArr[0];
			}
		}

		$insertData = array();
		foreach($data as $key=>$val){
			$product_name = $product_img = $price = $product_summary = $product_content = $seller_id = '';
			if(isset($amazonData[$val['asin'].'_'.$val['site']])){
				$product_name = $amazonData[$val['asin'].'_'.$val['site']]['title'];
				$product_img = $amazonData[$val['asin'].'_'.$val['site']]['image'];
				$price = $amazonData[$val['asin'].'_'.$val['site']]['price'];
				$product_summary = $amazonData[$val['asin'].'_'.$val['site']]['features'];
				$product_content = $amazonData[$val['asin'].'_'.$val['site']]['description'];
				$seller_id = $amazonData[$val['asin'].'_'.$val['site']]['seller_id'];
			}
			$days = isset($saleData[$val['asin'].'_'.$val['site']]) ? $saleData[$val['asin'].'_'.$val['site']] : 10000;
			//美国站点 5个/天 其他站点3个/天， 新品上线第一周(帖子状态为待推贴，并且更新时间为一周内)美国站点 10个/天 其他站点5个/天
			if($val['post_status']==2 && (time()-strtotime($val['push_date'])) <= 86400*7){//新品上线第一周
				if($val['site']=='www.amazon.com'){
					$sales_target_reviews = 10;
				}else{
					$sales_target_reviews = 5;
				}
			}else{
				if($val['site']=='www.amazon.com'){
					$sales_target_reviews = 5;
				}else{
					$sales_target_reviews = 3;
				}
			}


			$insertData[] = array(
				'asin' => $val['asin'],
				'site' => $val['site'],
				'sellersku' => $val['sellersku'],
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d H:i:s'),
				'post_status' => $val['post_status'],
				'post_type' => $val['post_type'],
				'user_id' => $val['user_id'],//user_id=1时表示为系统添加
				'review_rating' => $val['review_rating'],//昨天星级
				'number_of_reviews' => $val['number_of_reviews'],//昨天的评论总数
				'sales_target_reviews' => $sales_target_reviews,
				'order_status' => isset($orderdata[$val['asin'].'_'.$val['site']]) ? $orderdata[$val['asin'].'_'.$val['site']]['order_status'] : 0,
				'keyword' => $val['keyword'],
				'position' => $val['position'],
				'product_name' => $product_name,
				'product_img' => $product_img,
				'price' => $price,
				'product_summary' => $product_summary,
				'product_content' => $product_content,
				'sku_level' => isset($skuData[$val['sku'].'_'.$val['site']]) ? $skuData[$val['sku'].'_'.$val['site']]['sku_level'] : '',
				'seller_id' => $seller_id,
				'stock_days' => $days,
			);
		}
		if($insertData){
			batchInsert('rsg_products',$insertData);
		}
		Log::Info($insertData);
		Log::Info('Execution script end');
	}
}



