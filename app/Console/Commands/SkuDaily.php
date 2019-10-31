<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use App\Asin;
use App\SkusDailyInfo;
use PDO;
use DB;
use Log;

class SkuDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:skudaily';

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
		
		$date=date('Y-m-d',strtotime('-2 day'));
		$skus_info=[];
		$sku=$departments=[];
		//取汇率
		$rates = DB::table('cur_rate')->pluck('rate','cur');
		//去销售员部门
		$seller_departments=Asin::select(['sap_seller_id','bg','bu'])->where('sap_seller_id','>',0)->groupBy(['sap_seller_id','bg','bu'])->get()->toArray();
		foreach($seller_departments as $seller_depart){
			$departments[$seller_depart['sap_seller_id']]=['bg'=>$seller_depart['bg'],'bu'=>$seller_depart['bu']];
		}
		//仓储费
		$m_f='11_1_fee';
		if(date('m',strtotime($date))>1 && date('m',strtotime($date))<11) $m_f='2_10_fee';
		$storage_fee = DB::table('storage_fee')->select(DB::raw("CONCAT(type,'-',site,'-',size) as skey, $m_f as svalue"))->pluck('svalue','skey');
		//收入明细
		print_r('订单相关计算开始...');
		$sales =  DB::connection('order')->select("select sellersku,MarketplaceName,type,currency,sum(quantityshipped) as sales,
		sum(Amount) as amount from finances_shipment_event where left(PostedDate,10)='$date' 
		group by sellersku,MarketplaceName,type,currency");
		foreach($sales as $sale){
			
			if($sale->MarketplaceName=='Non-Amazon'){
				$sale->MarketplaceName = DB::connection('order')->table('finances_shipment_event')->where('sellersku',$sale->sellersku)->where('currency',$sale->currency)->orderBy('PostedDate','desc')->value('MarketplaceName');
			}
			if(strtolower($sale->MarketplaceName)=='si us prod marketplace') $sale->MarketplaceName='amazon.com';
			if(strtolower($sale->MarketplaceName)=='si ca prod marketplace') $sale->MarketplaceName='amazon.ca';
			if(strtolower($sale->MarketplaceName)=='si uk prod marketplace') $sale->MarketplaceName='amazon.co.uk';
			if(strtolower($sale->MarketplaceName)=='si prod es marketplace') $sale->MarketplaceName='amazon.es';
			if(strtolower($sale->MarketplaceName)=='si prod it marketplace') $sale->MarketplaceName='amazon.it';
			if(strtolower($sale->MarketplaceName)=='si prod de marketplace') $sale->MarketplaceName='amazon.de';
			if(strtolower($sale->MarketplaceName)=='si prod fr marketplace') $sale->MarketplaceName='amazon.fr';

			$sku = Asin::where('site',strtolower('www.'.$sale->MarketplaceName))->where('sellersku',$sale->sellersku)->first();
			if(!$sku) continue;
			if(!$sku['item_no']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.str_replace('.','',$sale->MarketplaceName).'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['amount'])) $skus_info[$key]['amount']=0;
			if(!isset($skus_info[$key]['sales'])) $skus_info[$key]['sales']=0;
			if(!isset($skus_info[$key]['fulfillmentfee'])) $skus_info[$key]['fulfillmentfee']=0;
			if(!isset($skus_info[$key]['commission'])) $skus_info[$key]['commission']=0;
			if(!isset($skus_info[$key]['otherfee'])) $skus_info[$key]['otherfee']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			if($sale->type=='Principal'){
				$skus_info[$key]['amount']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}elseif($sale->type=='FBAPerUnitFulfillmentFee'){
				$skus_info[$key]['sales']+=$sale->sales;
				$skus_info[$key]['fulfillmentfee']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}elseif($sale->type=='Commission'){
				$skus_info[$key]['commission']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}else{
				$skus_info[$key]['otherfee']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}

		}
		print_r('退款相关计算开始...');
		//退款明细
		$refunds =  DB::connection('order')->select("select MarketplaceName,sellersku,currency,sum(Amount) as amount from finances_refund_event
		where left(PostedDate,10)='$date' group by MarketplaceName,SellerSKU,Currency");
		foreach($refunds as $refund){
			$sku = Asin::where('site',strtolower('www.'.$refund->MarketplaceName))->where('sellersku',$refund->sellersku)->first();
			if(!$sku) continue;
			if(!$sku['item_no']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.str_replace('.','',$refund->MarketplaceName).'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['refund'])) $skus_info[$key]['refund']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			$skus_info[$key]['refund']+=round($refund->amount*array_get($rates,$refund->currency),2);
		}
		print_r('Deal相关计算开始...');
		//deal
		$coupons =  DB::connection('order')->select("select totalamount,currency,sku,site_code from finances_deal_event
		where left(PostedDate,10)='$date'");
		
		foreach($coupons as $coupon){
			if(!$coupon->sku) continue;
			if(!$coupon->site_code) continue;
			$match_skus =explode('/',$coupon->sku);
			$count_skus = count($match_skus);
			if(!$count_skus) continue;
			$amount = abs($coupon->totalamount);
			$avg_amount = round($amount/$count_skus,2);
			$i=0;
			foreach($match_skus as $match_sku){
				$i++;
				$sku = Asin::where('site',strtolower('www.'.array_get(getSapSiteCode(),$coupon->site_code)))->where('item_no',$match_sku)->first();
				if(!$sku['sap_seller_id']) continue;
				if($i==$count_skus){
					$sku_amount = round($amount - ($avg_amount*($count_skus-1)),2);
				}else{
					$sku_amount = $avg_amount;
				}
				$key=strtoupper(trim($sku['item_no']).'|'.array_get(getSapSiteCode(),$coupon->site_code).'|'.$sku['sap_seller_id']);
				if(!isset($skus_info[$key]['deal'])) $skus_info[$key]['deal']=0;
				if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
				$skus_info[$key]['deal']+=round($sku_amount*array_get($rates,$coupon->currency),2);
			}
		}
		
		print_r('Coupon相关计算开始...');
		//coupon
		$coupons =  DB::connection('order')->select("select totalamount,currency,sku,site_code from finances_coupon_event
		where left(PostedDate,10)='$date'");
		foreach($coupons as $coupon){
			if(!$coupon->sku) continue;
			if(!$coupon->site_code) continue;
			$match_skus =explode('/',$coupon->sku);
			$count_skus = count($match_skus);
			if(!$count_skus) continue;
			$amount = abs($coupon->totalamount);
			$avg_amount = round($amount/$count_skus,2);
			$i=0;
			foreach($match_skus as $match_sku){
				$i++;
				$sku = Asin::where('site',strtolower('www.'.array_get(getSapSiteCode(),$coupon->site_code)))->where('item_no',$match_sku)->first();
				if(!$sku['sap_seller_id']) continue;
				if($i==$count_skus){
					$sku_amount = round($amount - ($avg_amount*($count_skus-1)),2);
				}else{
					$sku_amount = $avg_amount;
				}
				$key=strtoupper(trim($sku['item_no']).'|'.array_get(getSapSiteCode(),$coupon->site_code).'|'.$sku['sap_seller_id']);
				if(!isset($skus_info[$key]['coupon'])) $skus_info[$key]['coupon']=0;
				if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
				$skus_info[$key]['coupon']+=round($sku_amount*array_get($rates,$coupon->currency),2);
			}
		}
		
		print_r('CPC相关计算开始...');
		//cpc
		$coupons =  DB::select("select marketplace_id,cost,sku from aws_report
		where date='$date' and state='enabled'");
		foreach($coupons as $coupon){
			if(!$coupon->sku) continue;
			if(!$coupon->cost) continue;
			$match_skus =explode('/',$coupon->sku);
			$count_skus = count($match_skus);
			if(!$count_skus) continue;
			$amount = abs($coupon->cost);
			$avg_amount = round($amount/$count_skus,2);
			$i=0;
			foreach($match_skus as $match_sku){
				$i++;
				$sku = Asin::where('site',strtolower('www.'.array_get(getSiteUrl(),$coupon->marketplace_id)))->where('item_no',$match_sku)->first();
				if(!$sku['sap_seller_id']) continue;
				if($i==$count_skus){
					$sku_amount = round($amount - ($avg_amount*($count_skus-1)),2);
				}else{
					$sku_amount = $avg_amount;
				}
				$key=strtoupper(trim($sku['item_no']).'|'.array_get(getSiteUrl(),$coupon->marketplace_id).'|'.$sku['sap_seller_id']);
				if(!isset($skus_info[$key]['cpc'])) $skus_info[$key]['cpc']=0;
				if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
				$skus_info[$key]['cpc']+=round($sku_amount*array_get($rates,array_get(getSiteCur(),array_get(getSiteUrl(),$coupon->marketplace_id))),2);
			}
		}
		print_r('FBA库存匹配开始...');
		//FBA库存对应
		$sellerskus =  DB::connection('order')->select("select sellersku,asin,instock from amazon_inventory_supply where instock>0");
		foreach($sellerskus as $sellersku_s){
			$sku = Asin::where('sellersku',$sellersku_s->sellersku)->where('asin',$sellersku_s->asin)->first();
			if(!$sku['item_no']) continue;
			if(!$sku['site']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.str_replace('www.','',$sku['site']).'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['fba_stock'])) $skus_info[$key]['fba_stock']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			$skus_info[$key]['fba_stock']+=intval($sellersku_s->instock);		
		}
		
		print_r('基础信息匹配开始...');
		foreach($skus_info as $key=>$v){
			$key_s = explode('|',$key);
			if(count($key_s)!=3) continue;
			$sku = trim($key_s[0]);
			$site = strtoupper(substr($key_s[1],-2));
			$skus_info[$key]['sku']=$sku;
			$skus_info[$key]['site']=strtolower('www.'.$key_s[1]);
			$skus_info[$key]['date']=$date;
			$sap_seller_id = $skus_info[$key]['sap_seller_id']=intval($key_s[2]);
			$skus_info[$key]['bg'] = array_get($departments,$sap_seller_id.'.bg');
			$skus_info[$key]['bu'] = array_get($departments,$sap_seller_id.'.bu');
			//采购成本
			$sku_base=DB::table('sku_base')->where('sku',$sku)->first();
			$skus_info[$key]['cost']=($sku_base)?round($sku_base->cost,2):0;
			$skus_info[$key]['volume']=($sku_base)?round($sku_base->volume/1000000,8):0;
			$skus_info[$key]['size']=($sku_base)?intval($sku_base->size):0;
			//关税
			$tax_rate=DB::table('tax_rate')->where('site',$site)->whereIn('sku',array('*',$sku))->pluck('tax','sku');
			$tax = ((array_get($tax_rate,$sku)??array_get($tax_rate,'*'))??0);
			$skus_info[$key]['tax']=round($skus_info[$key]['cost']*$tax,2);
			//头程运费
			$shipfee = (array_get(getShipRate(),$site.'.'.$sku)??array_get(getShipRate(),$site.'.default'))??0;
			$skus_info[$key]['headshipfee']=round($skus_info[$key]['volume']*$shipfee,2);
			
			//FBM仓储费
			$fbm_stock = DB::table('fbm_accs_stock')->select(DB::raw('sum(LABST) as stock'))->whereRaw("left(WERKS,2)='$site' and matnr='$sku'")->value('stock');
			$skus_info[$key]['fbm_stock']=intval($fbm_stock);
			$skus_info[$key]['fbm_storage']=round(array_get($storage_fee,'FBM-'.$site.'-'.$skus_info[$key]['size'],0)*$fbm_stock*$skus_info[$key]['volume']/date("t",strtotime($date)),2);
			
			//FBA仓储费
			if(!isset($skus_info[$key]['fba_stock'])) $skus_info[$key]['fba_stock']=0;			
			$skus_info[$key]['fba_storage']=round(array_get($storage_fee,'FBA-'.$site.'-'.$skus_info[$key]['size'],0)*$skus_info[$key]['fba_stock']*$skus_info[$key]['volume']/date("t",strtotime($date)),2);
			
			//单位仓储费
			if(isset($skus_info[$key]['sales'])){
				$unit_fee = DB::table('unit_storage')->where('sku',$sku)->where('site',$site)->value('cost');
				$skus_info[$key]['unit_storage']=$unit_fee*$skus_info[$key]['sales'];
			}
			
			//人工设定成本
			$cost_set = DB::table('cost_set')->where('sku',$sku)->where('site',$site)->value('cost');
			$skus_info[$key]['cost_set']=$cost_set??0;
			//平均库存金额
			$skus_info[$key]['stock_amount']=round(($skus_info[$key]['fba_stock']+$skus_info[$key]['fbm_stock'])*($skus_info[$key]['cost']+$skus_info[$key]['tax']+$skus_info[$key]['headshipfee']),2);
			//资金占用费
			$skus_info[$key]['amount_used']=round($skus_info[$key]['stock_amount']*0.015/date("t",strtotime($date)),2);
			
			//经济效益
			$skus_info[$key]['economic'] = round(array_get($skus_info[$key],'amount',0)+array_get($skus_info[$key],'fulfillmentfee',0)+array_get($skus_info[$key],'commission',0)+array_get($skus_info[$key],'otherfee',0)+array_get($skus_info[$key],'refund',0)-array_get($skus_info[$key],'deal',0)-array_get($skus_info[$key],'coupon',0)-array_get($skus_info[$key],'cpc',0)-(array_get($skus_info[$key],'cost',0)+array_get($skus_info[$key],'tax',0)+array_get($skus_info[$key],'headshipfee',0))*array_get($skus_info[$key],'sales',0)-array_get($skus_info[$key],'fbm_storage',0)-array_get($skus_info[$key],'fba_storage',0)-array_get($skus_info[$key],'amount_used',0),2);
			
			if($skus_info[$key]['status']==1){
				$skus_info[$key]['reserved'] = ($skus_info[$key]['economic']>0)?$skus_info[$key]['economic']:0;
			}else{
				 $eliminate1 = round(array_get($skus_info[$key],'amount',0)+array_get($skus_info[$key],'fulfillmentfee',0)+array_get($skus_info[$key],'commission',0)+array_get($skus_info[$key],'otherfee',0)+array_get($skus_info[$key],'refund',0)-array_get($skus_info[$key],'deal',0)-array_get($skus_info[$key],'coupon',0)-array_get($skus_info[$key],'cpc',0)-array_get($skus_info[$key],'cost_set',0)*array_get($skus_info[$key],'sales',0),2);
				 
				$skus_info[$key]['eliminate1']=( $eliminate1>0)? $eliminate1:0;
				
				$eliminate2 = round(array_get($skus_info[$key],'unit_storage',0)/2+(array_get($skus_info[$key],'cost',0)+array_get($skus_info[$key],'tax',0)+array_get($skus_info[$key],'headshipfee',0))/2*0.015,2);
				
				$skus_info[$key]['eliminate2']=( $eliminate2>0)? $eliminate2:0;
			}
			print_r($skus_info[$key]);
			SkusDailyInfo::updateOrCreate(
			['sku'=>$skus_info[$key]['sku'], 'site'=>$skus_info[$key]['site'], 'date'=>$skus_info[$key]['date']]
			,$skus_info[$key]);
		}
		
    }

    

}
