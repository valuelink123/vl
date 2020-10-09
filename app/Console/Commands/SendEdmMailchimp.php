<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Models\EdmCampaign;
use DrewM\MailChimp\MailChimp;

class SendEdmMailchimp extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'send:mailchimp';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'send edm mailchimp';

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
		//发送需发送但未发送的邮件
		DB::connection()->enableQueryLog(); // 开启查询日志
		$date_from = date('Y-m-d H:i:s');
		$date_to = date('Y-m-d H:i:s',strtotime("+1 hour",time()));
		Log::Info('send edm mailchimp start...');
		$data = EdmCampaign::where('send_status',0)->where('set_sendtime','>=',$date_from)->where('set_sendtime','<=',$date_to)->get()->toArray();
		$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
		$updateData = array();
		foreach($data as $key=>$val){
			$campaign_id = $val['mailchimp_campid'];
			$response = $MailChimp->post("/campaigns/$campaign_id/actions/send");//488782，tag6
			if(empty($response)){
				$updateData[] = array(
					'id'=>$val['id'],
					'send_status'=>1,
					'real_sendtime'=>date('y-m-d H:i:s'),
				);
			}
		}
		if($updateData){
			EdmCampaign::insertOnDuplicateKey($updateData);
		}
		$queries = DB::getQueryLog(); // 获取查询日志
		Log::Info($queries);
		Log::Info('send edm mailchimp end.');
	}

}
