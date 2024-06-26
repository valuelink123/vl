<?php
/*
 * 接口文档地址：http://docs.developer.amazonservices.com/en_US/finances/Finances_Datatypes.html
 * 不同的费用类型分别计入不同的费用字段中
 * ChargeComponent  计收入
 * Fee Types--Selling on Amazon Fees 计营销费用
 * Fee Types--Fulfillment By Amazon Fees 计仓库操作费
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use App\Asin;
use App\SkusDailyInfo;
use App\AsinDailyInfo;
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
    protected $signature = 'scan:skudaily {--time=}';

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
		$time =  $this->option('time');
        if(!$time) $time='2day';
		$date=date('Y-m-d',strtotime('-'.$time));
//		$date = '2020-06-07';//测试时间2020-10-01
		print_r($date.'start...');
		$skus_info=[];
		$sku=$departments=[];
		//计入收入的类型 ChargeComponent  计收入
		$amount_type = ['Principal','Tax','MarketplaceFacilitatorTax-Principal','MarketplaceFacilitatorTax-Shipping','MarketplaceFacilitatorTax-Giftwrap','MarketplaceFacilitatorTax-Other','Discount','TaxDiscount','CODItemCharge','CODItemTaxCharge','CODOrderCharge','CODOrderTaxCharge','CODShippingCharge','CODShippingTaxCharge','ShippingCharge','ShippingTax','Goodwill','Giftwrap','GiftwrapTax','RestockingFee','ReturnShipping','PointsFee','GenericDeduction','FreeReplacementReturnShipping','PaymentMethodFee','ExportCharge','SAFE-TReimbursement','TCS-CGST','TCS-SGST','TCS-IGST','TCS-UTGST'];
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
		print_r('order...');
		$sales =  DB::connection('amazon')->select("select seller_sku as sellersku,marketplace_name as MarketplaceName,type,currency,sum(quantity_shipped) as sales,
        sum(amount) as amount from finances_shipment_events where date(posted_date)='$date' 
		group by seller_sku,marketplace_name,type,currency");
		foreach($sales as $sale){
			$match_site = $sale->MarketplaceName;
			if($match_site=='Non-Amazon'){
				$match_site = DB::connection('amazon')->table('finances_shipment_events')->where('seller_sku',$sale->sellersku)->where('currency',$sale->currency)->where('marketplace_name','<>','Non-Amazon')->orderBy('posted_date','desc')->value('marketplace_name');
				if(!$match_site) continue;
			}
			if(strtolower($match_site)=='si us prod marketplace') $match_site='amazon.com';
			if(strtolower($match_site)=='si ca prod marketplace') $match_site='amazon.ca';
			if(strtolower($match_site)=='si uk prod marketplace') $match_site='amazon.co.uk';
			if(strtolower($match_site)=='si prod es marketplace') $match_site='amazon.es';
			if(strtolower($match_site)=='si prod it marketplace') $match_site='amazon.it';
			if(strtolower($match_site)=='si prod de marketplace') $match_site='amazon.de';
			if(strtolower($match_site)=='si prod fr marketplace') $match_site='amazon.fr';

			$sku = Asin::where('site',strtolower('www.'.$match_site))->where('sellersku',$sale->sellersku)->whereRaw('length(asin)=10')->first();
			if(!$sku) continue;
			if(!$sku['item_no']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.$match_site.'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['amount'])) $skus_info[$key]['amount']=0;
			if(!isset($skus_info[$key]['sales'])) $skus_info[$key]['sales']=0;
			if(!isset($skus_info[$key]['fulfillmentfee'])) $skus_info[$key]['fulfillmentfee']=0;
			if(!isset($skus_info[$key]['commission'])) $skus_info[$key]['commission']=0;
			if(!isset($skus_info[$key]['otherfee'])) $skus_info[$key]['otherfee']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
			if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
			if(in_array($sale->type,$amount_type)){
				if($sale->type == 'Principal') $skus_info[$key]['sales']+=$sale->sales;
				$skus_info[$key]['amount']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}elseif(in_array($sale->type,['FBAPerUnitFulfillmentFee','CODChargeback','GiftwrapChargeback','ShippingChargeback'])){
				$skus_info[$key]['fulfillmentfee']+=round($sale->amount*array_get($rates,$sale->currency),2);
				if(strtolower(substr($sale->MarketplaceName,0,6))!='amazon' && $sale->type=='FBAPerUnitFulfillmentFee') $skus_info[$key]['sales']+=$sale->sales;
			}elseif(in_array($sale->type,['Commission','ShippingHB'])){
				
				$skus_info[$key]['commission']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}else{
				$skus_info[$key]['otherfee']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}

		}
		print_r('refund...');

		//退款明细
		$refunds =  DB::connection('amazon')->select("select marketplace_name as MarketplaceName,seller_sku as sellersku,type,currency,sum(amount) as amount from finances_refund_events
		where left(posted_date,10)='$date' group by marketplace_name,seller_sku,type,currency");

		foreach($refunds as $refund){
			$sku = Asin::where('site',strtolower('www.'.$refund->MarketplaceName))->where('sellersku',$refund->sellersku)->first();
			if(!$sku) continue;
			if(!$sku['item_no']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.$refund->MarketplaceName.'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['amount'])) $skus_info[$key]['amount']=0;
			if(!isset($skus_info[$key]['fulfillmentfee'])) $skus_info[$key]['fulfillmentfee']=0;
			if(!isset($skus_info[$key]['commission'])) $skus_info[$key]['commission']=0;
			if(!isset($skus_info[$key]['otherfee'])) $skus_info[$key]['otherfee']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
			if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
			if(in_array($refund->type,['CostOfPointsReturned','GiftWrap','GiftWrapTax','Goodwill','PointsAdjusted','Principal','RestockingFee','ShippingCharge','ShippingTax','Tax','LowValueGoodsTax-Principal','LowValueGoodsTax-Shipping','MarketplaceFacilitatorTax-Other','MarketplaceFacilitatorTax-Principal','MarketplaceFacilitatorTax-RestockingFee','MarketplaceFacilitatorTax-Shipping','PromotionMetaDataDefinitionValue'])){
				$skus_info[$key]['amount']+=round($refund->amount*array_get($rates,$refund->currency),2);
			}elseif(in_array($refund->type,['GiftwrapChargeback','ShippingChargeback'])){
				$skus_info[$key]['fulfillmentfee']+=round($refund->amount*array_get($rates,$refund->currency),2);
			}elseif(in_array($refund->type,['Commission','RefundCommission'])){
				$skus_info[$key]['commission']+=round($refund->amount*array_get($rates,$refund->currency),2);
			}else{
				$skus_info[$key]['otherfee']+=round($refund->amount*array_get($rates,$refund->currency),2);
			}

		}
		
		print_r('return...');
		//退货明细
		$returns =  DB::connection('amazon')->select("select asin,seller_sku as sellersku,sum(quantity) as quantity from amazon_returns
		where left(return_date,10)='$date' and status<>'Reimbursed' group by asin,seller_sku");
		foreach($returns as $return){
			$sku = Asin::where('asin',trim($return->asin))->where('sellersku',trim($return->sellersku))->first();
			if(!$sku) continue;
			if(!$sku['item_no']) continue;
			if(!$sku['sap_seller_id']) continue;
			if(!$sku['site']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.str_replace('www.','',$sku->site).'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['returnqty'])) $skus_info[$key]['returnqty']=0;
			if(!isset($skus_info[$key]['sales'])) $skus_info[$key]['sales']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
			if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
			$skus_info[$key]['returnqty']+=intval($return->quantity);
			$skus_info[$key]['sales']=$skus_info[$key]['sales']-intval($return->quantity);
		}
		print_r('Deal...');
		/*
		 * deal和coupon和CPC暂时先不取，这三个选项暂时没有数据
		//deal==处理
		$coupons =  DB::connection('amazon')->select("select total_amount as totalamount,currency,sku,site_code from finances_deal_events
		where left(posted_date,10)='$date'");
		
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
				if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
				if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
				$skus_info[$key]['deal']+=round($sku_amount*array_get($rates,$coupon->currency),2);
			}
		}
		
		print_r('Coupon...');
		//coupon==处理
		$coupons =  DB::connection('amazon')->select("select total_amount as totalamount,currency,sku,site_code from finances_coupon_events
		where left(posted_date,10)='$date'");
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
				if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
				if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
				$skus_info[$key]['coupon']+=round($sku_amount*array_get($rates,$coupon->currency),2);
			}
		}
		
		print_r('CPC...');
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
				if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
				if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
				$skus_info[$key]['cpc']+=round($sku_amount*array_get($rates,array_get(getSiteCur(),array_get(getSiteUrl(),$coupon->marketplace_id))),2);
			}
		}
		*/
		print_r('FBA...');
		//FBA库存对应 ==处理
		$sellerskus =  DB::connection('amazon')->select("select seller_sku as sellersku,asin,in_stock as instock from seller_inventory_supply where in_stock>0");
		foreach($sellerskus as $sellersku_s){
			$sku = Asin::where('sellersku',$sellersku_s->sellersku)->where('asin',$sellersku_s->asin)->first();
			if(!$sku['item_no']) continue;
			if(!$sku['site']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['item_no']).'|'.str_replace('www.','',$sku['site']).'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['fba_stock'])) $skus_info[$key]['fba_stock']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
			if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
			$skus_info[$key]['fba_stock']+=intval($sellersku_s->instock);		
		}
		
		print_r('baseinfo...');
		foreach($skus_info as $key=>$v){
			$key_s = explode('|',$key);
			if(count($key_s)!=3) continue;
			$sku = trim($key_s[0]);
			$site = substr($key_s[1],-2);
			if($site=='OM') $site='US';
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
			$tax_rate=DB::table('tax_rate')->where('site',$site)->whereIn('sku',array('OTHERSKU',$sku))->pluck('tax','sku');
			$tax = round(((array_get($tax_rate,$sku)??array_get($tax_rate,'OTHERSKU'))??0),4);
			$skus_info[$key]['tax']=round($skus_info[$key]['cost']*$tax,2);

			//头程运费
			$shipfee = (array_get(getShipRate(),$site.'.'.$sku)??array_get(getShipRate(),$site.'.default'))??0;
			$skus_info[$key]['headshipfee']=round($skus_info[$key]['volume']*round($shipfee,4),2);
			
			//FBM仓储费
			$fbm_stock = DB::table('fbm_accs_stock')->select(DB::raw('sum(LABST) as stock'))->whereRaw("left(WERKS,2)='$site' and matnr='$sku'")->value('stock');
			$skus_info[$key]['fbm_stock']=intval($fbm_stock);
			$skus_info[$key]['fbm_storage']=round(array_get($storage_fee,'FBM-'.$site.'-'.$skus_info[$key]['size'],0)*intval($fbm_stock)*$skus_info[$key]['volume']/date("t",strtotime($date)),2);
			
			//FBA仓储费
			if(!isset($skus_info[$key]['fba_stock'])) $skus_info[$key]['fba_stock']=0;			
			$skus_info[$key]['fba_storage']=round(array_get($storage_fee,'FBA-'.$site.'-'.$skus_info[$key]['size'],0)*intval($skus_info[$key]['fba_stock'])*$skus_info[$key]['volume']/date("t",strtotime($date)),2);
			
			//单位仓储费
			if(isset($skus_info[$key]['sales'])){
				$unit_fee = DB::table('unit_storage')->where('sku',$sku)->where('site',$site)->value('cost');
				if(in_array($site,array('UK,DE,FR,IT,ES')) && !$unit_fee){
					$unit_fee = DB::table('unit_storage')->where('sku',$sku)->where('site','EU')->value('cost');
				}
				$skus_info[$key]['unit_storage']=round($unit_fee,2);
			}
			
			//人工设定成本
			$cost_set = DB::table('cost_set')->where('sku',$sku)->where('site',$site)->value('cost');
			$skus_info[$key]['cost_set']=$cost_set??0;
			//平均库存金额
			$skus_info[$key]['stock_amount']=round(($skus_info[$key]['fba_stock']+$skus_info[$key]['fbm_stock'])*($skus_info[$key]['cost']+$skus_info[$key]['tax']+$skus_info[$key]['headshipfee']),2);
			//资金占用费
			$skus_info[$key]['amount_used']=round($skus_info[$key]['stock_amount']*0.015/date("t",strtotime($date)),2);
			
			//经济效益
			$skus_info[$key]['economic'] = round(array_get($skus_info[$key],'amount',0)+array_get($skus_info[$key],'fulfillmentfee',0)+array_get($skus_info[$key],'commission',0)+array_get($skus_info[$key],'otherfee',0)-array_get($skus_info[$key],'deal',0)-array_get($skus_info[$key],'coupon',0)-array_get($skus_info[$key],'cpc',0)-(array_get($skus_info[$key],'cost',0)+array_get($skus_info[$key],'tax',0)+array_get($skus_info[$key],'headshipfee',0))*array_get($skus_info[$key],'sales',0)-array_get($skus_info[$key],'fbm_storage',0)-array_get($skus_info[$key],'fba_storage',0)-array_get($skus_info[$key],'amount_used',0),2);
			//SKU状态
			$skus_info[$key]['sku_status'] = DB::table('skus_status')->where('sku',$sku)->where('site',$skus_info[$key]['site'])->value('status');
			//完成率
			$budget_year = date('Y',strtotime($date));
			$budget_id = intval(DB::table('budgets')->where('sku',$sku)->where('site',$skus_info[$key]['site'])->where('year',$budget_year)->value('id'));
			$day_budget_datas = DB::table('budget_details')->where('budget_id',$budget_id)->where('date',$date)->selectRaw('(qty+promote_qty) as qty,income as amount,(income-cost-common_fee-pick_fee-storage_fee-promotion_fee-amount_fee) as profit')->first();
			$day_budget_datas = json_decode(json_encode($day_budget_datas), true);
			$budget_month = date('Y-m',strtotime($date));
			$month_budget_datas = DB::table('budget_details')->where('budget_id',$budget_id)->whereRaw("left(date,7) = '".$budget_month."'")->selectRaw('sum(qty+promote_qty) as qty,sum(income) as amount,sum(income-cost-common_fee-pick_fee-storage_fee-promotion_fee-amount_fee) as profit')->first();
			$month_budget_datas = json_decode(json_encode($month_budget_datas), true);
			$oa_qty_target = round(array_get($day_budget_datas,'qty',0),2);
			$oa_amount_target = round(array_get($day_budget_datas,'amount',0),2);
			$oa_profit_target = round(array_get($day_budget_datas,'profit',0),2);
			$oa_amount_target_total = round(array_get($month_budget_datas,'amount',0),2);
			
			$skus_info[$key]['profit'] = round(array_get($skus_info[$key],'amount',0)+array_get($skus_info[$key],'fulfillmentfee',0)+array_get($skus_info[$key],'commission',0)+array_get($skus_info[$key],'otherfee',0)-array_get($skus_info[$key],'deal',0)-array_get($skus_info[$key],'coupon',0)-array_get($skus_info[$key],'cpc',0)-(array_get($skus_info[$key],'cost',0)*1.3+array_get($skus_info[$key],'tax',0)+array_get($skus_info[$key],'headshipfee',0))*array_get($skus_info[$key],'sales',0)-array_get($skus_info[$key],'fbm_storage',0)-array_get($skus_info[$key],'fba_storage',0),2);
			
			if($oa_amount_target<0){
				$amount_per = round(2-array_get($skus_info[$key],'amount',0)/$oa_amount_target,4);
			}elseif($oa_amount_target>0){
				$amount_per =round(array_get($skus_info[$key],'amount',0)/$oa_amount_target,4);
			}else{
				$amount_per =0;
			}
			
			if($oa_qty_target<0){
				$sales_per = round(2-array_get($skus_info[$key],'sales',0)/$oa_qty_target,4);
			}elseif($oa_qty_target>0){
				$sales_per =round(array_get($skus_info[$key],'sales',0)/$oa_qty_target,4);
			}else{
				$sales_per =0;
			}
			
			if($oa_profit_target<0){
				$profit_per = round(2-array_get($skus_info[$key],'economic',0)/$oa_profit_target,4);
			}elseif($oa_profit_target>0){
				$profit_per =round(array_get($skus_info[$key],'economic',0)/$oa_profit_target,4);
			}else{
				$profit_per =0;
			}
			$skus_info[$key]['amount_target'] = $oa_amount_target;
			$skus_info[$key]['profit_target'] = $oa_profit_target;
			$skus_info[$key]['sales_target'] = $oa_qty_target;
			$skus_info[$key]['amount_per'] = $amount_per;
			$skus_info[$key]['profit_per'] = $profit_per;
			$skus_info[$key]['sales_per'] = $sales_per;	
			if($skus_info[$key]['status']==1){
				$cut_fee = 0;
				if($sku == 'AP0373' && $site =='US') $cut_fee = round(250000/date("t",strtotime($date)),2);
				if($sku == 'CS0503' && $site =='US') $cut_fee = round(350000/date("t",strtotime($date)),2);
				if($sku == 'CS0523' && $site =='US') $cut_fee = round(100000/date("t",strtotime($date)),2);
				if($sku == 'HPC0133' && $site =='US') $cut_fee = round(150000/date("t",strtotime($date)),2);
				if($sku == 'MP0602' && $site =='US') $cut_fee = round(50000/date("t",strtotime($date)),2);
				$skus_info[$key]['reserved'] = ($skus_info[$key]['economic']-$cut_fee>0)?($skus_info[$key]['economic']-$cut_fee):0;
				
				$skus_info[$key]['bonus'] = $skus_info[$key]['reserved']*0.04;
			}elseif($skus_info[$key]['status']==2){
				
				if($oa_amount_target_total>=600000){
					$pro_base=1000;
				}elseif($oa_amount_target_total>=400000){
					$pro_base=800;
				}elseif($oa_amount_target_total>=200000){
					$pro_base=600;
				}elseif($oa_amount_target_total>=100000){
					$pro_base=400;
				}elseif($oa_amount_target_total>=10000){
					$pro_base=200;
				}else{
					$pro_base=0;
				}
				
				$pro_base=round($pro_base/date("t",strtotime($date)),2);
				
				if($profit_per>=1.6){
					if($profit_per>5) $profit_per=5;
					$skus_info[$key]['bonus'] = $pro_base*(1+0.2*1+0.2*1.1+0.2*1.2+($profit_per-1.6)*1.3);
				}elseif($profit_per>=1.4){
					$skus_info[$key]['bonus'] = $pro_base*(1+0.2*1+0.2*1.1+($profit_per-1.4)*1.2);
				}elseif($profit_per>=1.2){
					$skus_info[$key]['bonus'] = $pro_base*(1+0.2*1+($profit_per-1.2)*1.1);
				}elseif($profit_per>=1){
					$skus_info[$key]['bonus'] = $pro_base*$profit_per;
				}else{
					$skus_info[$key]['bonus']=0;
				}			
			}else{
				 $eliminate1 = round(array_get($skus_info[$key],'amount',0)+array_get($skus_info[$key],'fulfillmentfee',0)+array_get($skus_info[$key],'commission',0)+array_get($skus_info[$key],'otherfee',0)-array_get($skus_info[$key],'deal',0)-array_get($skus_info[$key],'coupon',0)-array_get($skus_info[$key],'cpc',0)-array_get($skus_info[$key],'cost_set',0)*array_get($skus_info[$key],'sales',0),2);
				 
				$skus_info[$key]['eliminate1']=( $eliminate1>0)? $eliminate1:0;
				
				$eliminate2 = round(array_get($skus_info[$key],'unit_storage',0)*array_get($skus_info[$key],'sales',0)/2+(array_get($skus_info[$key],'cost',0)+array_get($skus_info[$key],'tax',0)+array_get($skus_info[$key],'headshipfee',0))*array_get($skus_info[$key],'sales',0)/2*0.015,2);
				
				$skus_info[$key]['eliminate2']=( $eliminate2>0)? $eliminate2:0;
				$skus_info[$key]['bonus'] = $skus_info[$key]['eliminate1']*0.03+$skus_info[$key]['eliminate2']*0.4;
			}
			SkusDailyInfo::where('sku',$skus_info[$key]['sku'])->where('site',$skus_info[$key]['site'])->where('date',$skus_info[$key]['date'])->delete();
			SkusDailyInfo::updateOrCreate(
			['sku'=>$skus_info[$key]['sku'], 'site'=>$skus_info[$key]['site'], 'date'=>$skus_info[$key]['date']]
			,$skus_info[$key]);
		}
		
		
		
		//计算ASIN维度销量明细
		print_r('asinorder...');
		$skus_info=[];
		$sales =  DB::connection('amazon')->select("select seller_sku as sellersku,marketplace_name as MarketplaceName,type,currency,sum(quantity_shipped) as sales,
        sum(amount) as amount from finances_shipment_events where date(posted_date)='$date' 
		group by seller_sku,marketplace_name,type,currency");
		
		foreach($sales as $sale){
			$match_site = $sale->MarketplaceName;
			if($match_site=='Non-Amazon'){
				$match_site = DB::connection('amazon')->table('finances_shipment_events')->where('seller_sku',$sale->sellersku)->where('currency',$sale->currency)->where('marketplace_name','<>','Non-Amazon')->orderBy('posted_date','desc')->value('marketplace_name');
				if(!$match_site) continue;
			}

			if(strtolower($match_site)=='si us prod marketplace') $match_site='amazon.com';
			if(strtolower($match_site)=='si ca prod marketplace') $match_site='amazon.ca';
			if(strtolower($match_site)=='si uk prod marketplace') $match_site='amazon.co.uk';
			if(strtolower($match_site)=='si prod es marketplace') $match_site='amazon.es';
			if(strtolower($match_site)=='si prod it marketplace') $match_site='amazon.it';
			if(strtolower($match_site)=='si prod de marketplace') $match_site='amazon.de';
			if(strtolower($match_site)=='si prod fr marketplace') $match_site='amazon.fr';

			$sku = Asin::where('site',strtolower('www.'.$match_site))->where('sellersku',$sale->sellersku)->whereRaw('length(asin)=10')->first();
			if(!$sku) continue;
			if(!$sku['asin']) continue;
			if(!$sku['sap_seller_id']) continue;
			$key=strtoupper(trim($sku['asin']).'|'.$match_site.'|'.$sku['sap_seller_id']);
			if(!isset($skus_info[$key]['amount'])) $skus_info[$key]['amount']=0;
			if(!isset($skus_info[$key]['sales'])) $skus_info[$key]['sales']=0;
			if(!isset($skus_info[$key]['fulfillmentfee'])) $skus_info[$key]['fulfillmentfee']=0;
			if(!isset($skus_info[$key]['commission'])) $skus_info[$key]['commission']=0;
			if(!isset($skus_info[$key]['otherfee'])) $skus_info[$key]['otherfee']=0;
			if(!isset($skus_info[$key]['status'])) $skus_info[$key]['status']=$sku['item_status'];
			if(!isset($skus_info[$key]['level'])) $skus_info[$key]['level']=$sku['status'];
			if(!isset($skus_info[$key]['review_user_id'])) $skus_info[$key]['review_user_id']=$sku['review_user_id'];
			if(in_array($sale->type,$amount_type)){
				if($sale->type == 'Principal') $skus_info[$key]['sales']+=$sale->sales;
				$skus_info[$key]['amount']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}elseif(in_array($sale->type,['FBAPerUnitFulfillmentFee','CODChargeback','GiftwrapChargeback','ShippingChargeback'])){
				$skus_info[$key]['fulfillmentfee']+=round($sale->amount*array_get($rates,$sale->currency),2);
				if(strtolower(substr($sale->MarketplaceName,0,6))!='amazon' && $sale->type=='FBAPerUnitFulfillmentFee') $skus_info[$key]['sales']+=$sale->sales;
			}elseif(in_array($sale->type,['Commission','ShippingHB'])){
				
				$skus_info[$key]['commission']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}else{
				$skus_info[$key]['otherfee']+=round($sale->amount*array_get($rates,$sale->currency),2);
			}

		}
		
		foreach($skus_info as $key=>$v){
			$key_s = explode('|',$key);
			if(count($key_s)!=3) continue;
			$asin = trim($key_s[0]);
			$skus_info[$key]['asin']=$asin;
			$skus_info[$key]['site']=strtolower('www.'.$key_s[1]);
			$skus_info[$key]['date']=$date;
			$skus_info[$key]['sap_seller_id']=intval($key_s[2]);
			$skus_info[$key]['bg'] = array_get($departments,$skus_info[$key]['sap_seller_id'].'.bg');
			$skus_info[$key]['bu'] = array_get($departments,$skus_info[$key]['sap_seller_id'].'.bu');
			AsinDailyInfo::where('asin',$skus_info[$key]['asin'])->where('site',$skus_info[$key]['site'])->where('date',$skus_info[$key]['date'])->delete();
			AsinDailyInfo::updateOrCreate(
			['asin'=>$skus_info[$key]['asin'], 'site'=>$skus_info[$key]['site'], 'date'=>$skus_info[$key]['date']]
			,$skus_info[$key]);
		}
    }

    

}
