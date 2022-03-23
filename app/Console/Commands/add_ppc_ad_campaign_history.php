<?php
/*
 * 把销售之前维护的ppc_ad_match_asin表里面的映射关系，迁移到新的表里面，ppc_ad_campaign和ppc_ad_campaign_match_asin表
 */

namespace App\Console\Commands;
use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Asinads;
use PDO;
use DB;
use Log;

class AddPpcAdCampaignHistory extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'add:ppc_ad_campaign_history';

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
		Log::Info('add:ppc_ad_campaign_history Start...');
		$sql = "SELECT ppc_ad_match_asin.*,ppc_ad_campaign.campaign_id AS vop_campaign_id,ppc_ad_campaign_match_asin.campaign_id as vop_asin_campaign_id 
FROM ppc_ad_match_asin 
LEFT JOIN ppc_ad_campaign ON ppc_ad_match_asin.campaign_id = ppc_ad_campaign.campaign_id 
LEFT JOIN ppc_ad_campaign_match_asin ON ppc_ad_campaign_match_asin.campaign_id = ppc_ad_campaign.campaign_id 
WHERE ppc_ad_match_asin.ad_type !='sproducts' order by ppc_ad_match_asin.id asc";
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
					'group_name'=>$val['ad_group'],
					'campaign_name'=>$val['campaign'],
					'created_at'=>$val['created_at'],
					'updated_at'=>$val['updated_at'],
				);
			}
			if(empty($val['vop_asin_campaign_id']) && $val['asin']){
				$insertAsinData[$val['campaign_id'].'_'.$val['asin']] = array(
					'campaign_id'=>$val['campaign_id'],
					'asin'=>$val['asin'],
					'sku'=>$val['sku'],
					'sap_seller_id'=>$val['sap_seller_id'],
					'created_at'=>$val['created_at'],
					'updated_at'=>$val['updated_at'],
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
		Log::Info('add:ppc_ad_campaign_history End...');
	}
}
