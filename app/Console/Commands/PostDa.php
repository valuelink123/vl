<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransferPlan;
use Swift_Mailer;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use App\Classes\SapRfc;
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
		$url = env('DAURL');//'http://main1.freightoa.com/webapi_doa/api/';
		$key = env('DAKEY');//'up4oY31x';
		$token = env('DATOKEN');//'PttzVyFJRNp4XMx6FeqW7/aOkGS7+qtD';
		$customerCode =env('DACUSTOMERCODE');//'VL';
		$shipEmail = env('SHIPMAIL');//'284299346@qq.com';//'zhanqiziyin@valuelinkltd.com';
		$daEmail = env('DAMAIL');//'284299346@qq.com';

		$checks = TransferPlan::where('status',5)->where('tstatus',0)->get();
		foreach($checks as $data){
			$subject = 'ShipmentID '.$data->shipment_id.' 调拨请求需要审核!';
			$html = new \Html2Text\Html2Text($subject);
			Mail::send(['emails.common','emails.common-text'],['content'=>$subject,'contentText'=>$html->getText()], function($m) use($subject,$shipEmail)
			{
				$m->to($shipEmail);
				$m->subject($subject);
			});
			sleep(2);
		}

		$daSkus = DB::connection('amazon')->table('da_sku_match')->pluck('da_sku','sku')->toArray();
		$amazonWarehouses = DB::connection('amazon')->table('amazon_warehouses')->get()->keyBy('code')->toArray();
		$creates = TransferPlan::where('status',6)->where('tstatus',0)->whereNull('da_order_id')->get();	
		foreach($creates as $data){
			sleep(2);
			
			try{
				$items = $data->items;
				
				$header=$profile=$details=$files= $postData= [];
				$sku = $warehouse = $fnsku = null;
				if(!empty($items)){
					foreach($items as $item){
						
						if(!$sku) $sku=array_get($daSkus, $item->sku, $item->sku);
						if(!$warehouse) $warehouse = $item->warehouse_code;
						if(!$fnsku) $fnsku =$item->fnsku;
						$details[]=[
							'item_no'=>array_get($daSkus, $item->sku, $item->sku),
							'qty'=>intval($item->quantity)
						];
						$wh = array_get($amazonWarehouses, $warehouse);
					}
				}
			
				$header[]='content-type: application/json';
				$header[]='appKey: '.$key;
				$header[]='appToken: '.$token;
				
				$profile['customer_code']=$customerCode;
				$profile['customer_ref_no']=$fnsku.'-'.$data->shipment_id;
				$profile['po_no']=$warehouse.'-'.$sku;
				$profile['ship_via']=$data->ship_method;
				$profile['ship_to_name']='AMAZON '.$warehouse;
				$profile['ship_to_address']=$wh->address;
				$profile['ship_to_city']=$wh->city;
				$profile['ship_to_state']=$wh->state;
				$profile['ship_to_zipcode']=$wh->zip;
				$profile['ship_to_contact']='';
				$profile['ship_to_tel']='';
				$profile['etd']=date('Y-m-d\TH:i:s',strtotime($data->created_at));
				$profile['pallet_count']=$data->broads;
				$profile['remark']=$data->remark;
				$postData['profile'] = $profile;
				$postData['items'] = $details;	
				if(!empty($data->files)){
					$uploads = explode(',', $data->files);
					foreach($uploads as $upload){
						$files[]=['file_name'=>basename(public_path($upload)),'file_content'=>base64_encode(file_get_contents(public_path($upload)))];
					}
				}
				$postData['files'] = $files;
			
				$result = json_decode($this->postJson($url.'/outbound/postOutbound', $postData, $header),true);
				if(array_get($result,'code')=='200'){
					$data->da_order_id = array_get($result,'msg');
					$data->tstatus=1;
					$data->api_msg = null;
					$data->save();
			
					$subject = 'Order No. '.$data->da_order_id.' Ref No. '.$fnsku.'-'.$data->shipment_id.' Attachs!';
					$html = new \Html2Text\Html2Text($subject);
					Mail::send(['emails.common','emails.common-text'],['content'=>$subject,'contentText'=>$html->getText()],  function($m) use($subject,$daEmail,$uploads)
					{
						$m->to($daEmail);
						$m->subject($subject);
						if ($uploads && count($uploads)>0){
							foreach($uploads as $attachment) {
								$m->attach(public_path().$attachment);
							}
						}
					});
			
				}else{
					$data->api_msg = array_get($result,'msg').json_encode(array_get($result,'data'));
					$data->save();
				}
			
			}catch (\Exception $e) { 
				Log::Info($e->getMessage());
			} 
		}



		$updates = TransferPlan::where('status',6)->where('tstatus',4)->whereNotNull('da_order_id')->get();
		foreach($updates as $data){
			sleep(2);
			try{
				$items = $data->items;
				$header=$profile=$details=$files=$sku = $fnsku = $postData= [];
				if(!empty($items)){
					foreach($items as $item){
						$ships = $item->ships;
						if(!empty($ships)){
							foreach($ships as $ship){
								$details[]=[
									'item_no'=>$ship->sku,
									'location_name'=>$ship->location,
									'remark'=>$item->warehouse_code,
									'qty'=>intval($ship->quantity)
								];
							}
						}
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
				$result = json_decode($this->postJson($url.'/outbound/SetCompleteOutbound', $postData, $header),true);
				
				if(array_get($result,'code')=='200'){
					$data->tstatus=5;
					$data->api_msg = null;	
				}else{
					$data->api_msg = array_get($result,'msg').json_encode(array_get($result,'data'));
				}
				$data->save();
			}catch (\Exception $e) { 
				Log::Info($e->getMessage());
			} 
		}

		$updateSap = TransferPlan::where('status',6)->where('tstatus',7)->whereNotNull('da_order_id')->whereNull('sap_st0')->get();
                foreach($updateSap as $data){
			sleep(2);
                        try{
                                $items = $data->items;
                                if(!empty($items)){
                                        $sap = new SapRfc();
                                        $sto = $tm = $dn = $sapData = [];
                                        $api_msg = '';
					$completed = true;
                                        foreach($items as $item){
                                                $ZID = $data->shipment_id.$item->warehouse_code;
                                                $sapData['postdata']['EXPORT']=array('O_MSG'=>'','O_FLAG'=>'');
                                                $sapData['postdata']['TABLE']= array('O_TAB'=>array(0));
                                                $sapData['postdata']['IMPORT']=array('I_ZID'=>$ZID);
                                                //$sapData['istest'] = 1;
                                                $res = $sap->ZMM_STO_DA($sapData);
                                                if(array_get($res,'ack')==1 && array_get($res,'data.O_FLAG')=='X'){
                                                        $lists = array_get($res,'data.O_TAB');
                                                        foreach($lists as $list){
                                                                $sto[$list['VGBEL']]=$list['VGBEL'];
                                                                $tm[$list['VBELN']]=$list['VBELN'];
                                                                $dn[$list['TKNUM']]=$list['TKNUM'];
                                                        }
                                                }else{
							$completed = false;
                                                        $api_msg= array_get($res,'data.O_FLAG').array_get($res,'data.O_MSG');
                                                }
                                        }
                                        if($completed){
                                                $data->api_msg = null;
                                                $data->sap_st0=implode(';', array_filter($sto));
                                                $data->sap_tm=implode(';', array_filter($tm));
                                                $data->sap_dn=implode(';', array_filter($dn));
                                        }else{
                                                $data->api_msg=$api_msg;
                                        }
                                        $data->save();
                                }
                        }catch (\Exception $e) {
                                Log::Info($e->getMessage());
                        }
                }


		$createSap = TransferPlan::where('status',6)->where('tstatus',6)->whereNotNull('da_order_id')->get();
		foreach($createSap as $data){
			sleep(2);
			try{
				$sapData = $ITAB1=$ITAB2=$LINENUM=[];
				$items = $data->items;
				if(!empty($items)){
					foreach($items as $item){
						$ITAB1[$data->shipment_id.$item->warehouse_code] = 
						[
							'ZID'=>$data->shipment_id.$item->warehouse_code,
							'ZEBELN'=>$data->da_order_id,
							'BSART'=>'UB',
							'EKORG'=>'VL02',
							'EKGRP'=>'G11',
							'BUKRS'=>'2000',
							'LIFNR'=>'US04',
						];
						if(!isset($LINENUM[$data->shipment_id.$item->warehouse_code])) $LINENUM[$data->shipment_id.$item->warehouse_code]=0;
						//$LINENUM[$data->shipment_id.$item->warehouse_code]=0;
						$ships = $item->ships;
						if(!empty($ships)){
							foreach($ships as $ship){
								$LINENUM[$data->shipment_id.$item->warehouse_code]+=10;
								$ITAB2[] = 
								[
									'ZID'=>$data->shipment_id.$item->warehouse_code,
									'EBELP'=>$LINENUM[$data->shipment_id.$item->warehouse_code],
									'PSTYP'=>'U',
									'EINDT'=>date('Ymd',strtotime($data->ship_date)),
									'MATNR'=>$item->sku,
									'MENGE'=>$ship->quantity,
									'TRAGR'=>'10',
									'RWERKS'=>'US01',
									'RLGORT'=>'AAA1',
									'SLGORT'=>'US1',
									'WLIFNR'=>'SZ-DA',
								];
								
							}
						}
					}
				}
				$sap = new SapRfc();
				$sapData['postdata']['EXPORT']=array('O_MSG'=>'','O_FLAG'=>'');
				$sapData['postdata']['TABLE']=array('I_TAB1'=>$ITAB1, 'I_TAB2'=>$ITAB2);
				//print_r($sapData);die();
			   //$sapData['istest'] = 1;
				$res = $sap->ZFMPHPRFC034($sapData);
				if(array_get($res,'ack')==1 && array_get($res,'data.O_FLAG')=='X'){
					$data->tstatus=7;
					$data->api_msg = null;	
				}else{
					$data->api_msg = array_get($res,'data.O_FLAG').array_get($res,'data.O_MSG');
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
