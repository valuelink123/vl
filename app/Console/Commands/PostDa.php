<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransferPlan;
use Illuminate\Support\Facades\Mail;
use DB;
use Log;

class PostDa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:da {--id=}';

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
		$url = 'http://main1.freightoa.com/webapi_doa/api/';
		$key = 'up4oY31x';
		$token = 'PttzVyFJRNp4XMx6FeqW7/aOkGS7+qtD';
		$customerCode ='VL';
		$shipEmail = 'wangjianeng@valuelinkltd.com';//'zhanqiziyin@valuelinkltd.com';
		$daEmail = 'wangjianeng@valuelinkltd.com';

		$checks = TransferPlan::where('status',5)->where('tstatus',0)->get();
		foreach($checks as $data){
			$subject = $content = $data->shipment_id.' 调拨请求需要审核!';
			$to = $shipEmail;
			Mail::send(['emails.common'],['content'=>$content], function($m) use($subject,$to)
			{
				$m->to($to);
				$m->subject($subject);
			});
		}

		$daSkus = DB::connection('amazon')->table('da_sku_match')->pluck('da_sku','sku')->toArray();
		$amazonWarehouses = DB::connection('amazon')->table('amazon_warehouses')->get()->keyBy('code')->toArray();
		$creates = TransferPlan::where('status',6)->where('tstatus',0)->get();
		foreach($creates as $data){
			try{
				$items = $data->items;
				$header=$profile=$details=$files=$sku = $fnsku = $postData= [];
				if(is_array($items)){
					foreach($items as $item){
						$sku[]=array_get($item,'sku');
						$fnsku[] =array_get($item,'fnsku');
						$details[]=[
							'item_no'=>array_get($daSkus, array_get($item,'sku'), array_get($item,'sku')),
							'qty'=>intval(array_get($item,'quantity'))
						];
					}
				}
				$header[]='content-type: application/json';
				$header[]='appKey: '.$key;
				$header[]='appToken: '.$token;
				$warehouse = array_get($amazonWarehouses, $data->warehouse_code);
				$profile['customer_code']=$customerCode;
				$profile['customer_ref_no']=implode('/',$fnsku).'-'.$data->shipment_id;
				$profile['po_no']=$data->warehouse_code.'-'.implode('/',$sku);
				$profile['ship_via']=$data->ship_method;
				$profile['ship_to_name']='AMAZON '.$data->warehouse_code;
				$profile['ship_to_address']=$warehouse->address;
				$profile['ship_to_city']=$warehouse->city;
				$profile['ship_to_state']=$warehouse->state;
				$profile['ship_to_zipcode']=$warehouse->zip;
				$profile['ship_to_contact']='';
				$profile['ship_to_tel']='';
				$profile['etd']=date('Y-m-d\TH:i:s',strtotime($data->created_at));
				$profile['pallet_count']=$data->broads;
				$profile['remark']=$data->remark;
				$postData['profile'] = $profile;
				$postData['items'] = $details;
				
				$uploads = explode(',', $data->files);
				foreach($uploads as $upload){
					$files[]=['file_name'=>basename(public_path($upload)),'file_content'=>base64_encode(file_get_contents(public_path($upload)))];
				}
				$postData['files'] = $files;
				$result = json_decode($this->postJson($url.'outbound/postOutbound', $postData, $header),true);
				
				if(array_get($result,'code')=='200'){
					$data->da_order_id = array_get($result,'msg');
					$data->tstatus=1;
					$data->api_msg = null;
					$data->save();
					$subject = $content = 'Ref No. '.implode('/',$fnsku).'-'.$data->shipment_id.' Attachs!';
					$to = $daEmail;
					Mail::send(['emails.common'],['content'=>$content], function($m) use($subject,$to,$uploads)
					{
						$m->to($to);
						$m->subject($subject);
						if ($uploads && count($uploads)>0){
							foreach($uploads as $attachment) {
								$m->attach(public_path().$attachment);
							}
						}
					});

				}else{
					$data->api_msg = json_encode(array_get($result,'data'));
					$data->save();
				}
				

				

			}catch (\Exception $e) { 
				Log::Info($e->getMessage());
			} 
		}



		$updates = TransferPlan::where('status',6)->where('tstatus',4)->whereNotNull('da_order_id')->get();
		foreach($update as $data){
			try{
				$items = $data->ships;
				$header=$profile=$details=$files=$sku = $fnsku = $postData= [];
				if(is_array($items)){
					foreach($items as $item){
						$details[]=[
							'item_no'=>array_get($item,'sku'),
							'location_name'=>array_get($item,'location'),
							'qty'=>intval(array_get($item,'quantity'))
						];
					}
				}
				$header[]='content-type: application/json';
				$header[]='appKey: '.$key;
				$header[]='appToken: '.$token;
				$profile['customer_code']=$customerCode;
				$profile['lot_no'] = $data->da_order_id;
				$profile['actual_date']=date('Y-m-d\TH:i:s',strtotime($data->ship_date));
				$postData['profile'] = $profile;
				$postData['items'] = $details;
				$result = json_decode($this->postJson($url.'outbound/SetCompleteOutbound', $postData, $header),true);
				if(array_get($result,'code')=='200'){
					$data->tstatus=5;
					$data->api_msg = null;
				}else{
					$data->api_msg = json_encode(array_get($result,'data'));
				}
				$data->save();
			}catch (\Exception $e) { 
				Log::Info($e->getMessage());
			} 
		}
		
	}



	function postJson($url,$data=[],$header=[]){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			if(substr($url,0,5)=='https'){
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
			}
			if(!empty($data)){
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			}
			if(!empty($header)){
				curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			}
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			$data = curl_exec($curl);
			if (curl_errno($curl)) {
				return curl_error($curl);
			}
			curl_close($curl);
			return $data;
	}
}
