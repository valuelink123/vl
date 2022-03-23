<?php
/*
 * 添加广告映射关系数据,每日更新
 * ppc_ad_campaign，一个campaign一条数据，以campaign_id为唯一标识
 * ppc_ad_campaign_match_asin，表示一个campaign可能对应多个asin,一个campaign可能有多条数据，跟ppc_ad_campaign表是一对多的关系
 */

namespace App\Console\Commands;
use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asinads;
use PDO;
use DB;
use Log;

class AddPpcAdCampaign extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'add:ppc_ad_campaign';

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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Log::Info('add:ppc_ad_campaign Start...');
		$sql = "SELECT ppc_ad_campaign.campaign_id AS vop_campaign_id,ppc_ad_campaign_match_asin.campaign_id as vop_asin_campaign_id, union_table.*,profiles.marketplace_id AS marketplace_id,profiles.seller_id AS seller_id
        FROM (
    SELECT products.asin as asin,products.sku as sku,'sproducts' AS ad_type,groups.name AS group_name,campaigns.name AS campaign_name,
  campaigns.profile_id AS profile_id,campaigns.campaign_id AS campaign_id,products.ad_group_id AS ad_group_id
  FROM ppc_sproducts_ads as products
  LEFT JOIN ppc_sproducts_ad_groups as groups ON groups.ad_group_id = products.ad_group_id
  left join ppc_sproducts_campaigns as campaigns on products.campaign_id = campaigns.campaign_id
  UNION all
  SELECT products.asin as asin,products.sku as sku,'sdisplay' AS ad_type,groups.name AS group_name,campaigns.name AS campaign_name,
  campaigns.profile_id AS profile_id,campaigns.campaign_id AS campaign_id,products.ad_group_id AS ad_group_id
  FROM ppc_sdisplay_ads as products
  LEFT JOIN ppc_sdisplay_ad_groups as groups ON groups.ad_group_id = products.ad_group_id
  left join ppc_sdisplay_campaigns as campaigns on products.campaign_id = campaigns.campaign_id
  UNION all
  SELECT '' AS asin,'' AS sku,'sbrands' AS ad_type,groups.name AS group_name,campaigns.name AS campaign_name,
  campaigns.profile_id AS profile_id,campaigns.campaign_id AS campaign_id,groups.ad_group_id AS ad_group_id
  from ppc_sbrands_campaigns AS campaigns
  LEFT JOIN ppc_sbrands_ad_groups AS groups ON campaigns.campaign_id = groups.campaign_id
   ) AS union_table

 left join ppc_profiles AS profiles on union_table.profile_id = profiles.profile_id
LEFT JOIN ppc_ad_campaign ON union_table.campaign_id = ppc_ad_campaign.campaign_id
 LEFT JOIN ppc_ad_campaign_match_asin ON ppc_ad_campaign_match_asin.campaign_id = ppc_ad_campaign.campaign_id
 WHERE union_table.campaign_id IS NOT NULL  ";
		$_data = DB::select($sql);
		$insertData = $insertAsinData = array();
		foreach($_data as $key=>$val){
			$val = (array)$val;
			if(empty($val['vop_campaign_id'])){
				$insertData[$val['campaign_id']] = array(
					'campaign_id'=>$val['campaign_id'],
					'profile_id'=>$val['profile_id'],
					'marketplace_id'=>$val['marketplace_id'],
					'seller_id'=>$val['seller_id'],
					'ad_group_id'=>$val['ad_group_id'],
					'ad_type'=>$val['ad_type'],
                    'group_name'=>$val['group_name'],
                    'campaign_name'=>$val['campaign_name'],
				);
			}
			if(empty($val['vop_asin_campaign_id']) && $val['asin']){
				$insertAsinData[$val['campaign_id'].'_'.$val['asin']] = array(
					'campaign_id'=>$val['campaign_id'],
					'asin'=>$val['asin'],
					'sku'=>$val['sku'],
				);
			}
			unset($val);
		}
		if($insertData){
			DB::table('ppc_ad_campaign')->insert($insertData);
		}
		if($insertAsinData){
			DB::table('ppc_ad_campaign_match_asin')->insert($insertAsinData);
		}
		Log::Info('add:ppc_ad_campaign End...');
	}
}
