<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use App\Models\SaleDailyInfo;
use PDO;
use DB;
use Log;

class CalDailySales extends Command
{cal:dailySales --afterDate=2020-10-01 --beforeDate=
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cal:dailySales {--afterDate=} {--beforeDate=}';

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
        $afterDate =  $this->option('afterDate');
        $beforeDate =  $this->option('beforeDate');
        if(!$beforeDate) $beforeDate = date('Y-m-d',strtotime('-2day'));
        if(!$afterDate) $afterDate = date('Y-m-d',strtotime('-2day'));
        if($beforeDate < $afterDate) $beforeDate = $afterDate;

        //取汇率
        $rates = DB::connection('amazon')->table('currency_rates')->pluck('rate','currency');
        
        $marketplaces = DB::connection('amazon')->table('additional_marketplaces')->get();
        foreach($marketplaces as $marketplace){
            $additionalMarketplaces[$marketplace->additional_marketplace_id] = $marketplace->marketplace_id;
            $marketplaceCountryCodes[$marketplace->marketplace_id] = $marketplace->country_code;
        }

        $sellers = DB::connection('amazon')->table('seller_accounts')->get();
        foreach($sellers as $seller){
            $sellerAccounts[$seller->id] = $seller->mws_seller_id;
            if($seller->primary) $primarySellerAccounts[$seller->mws_seller_id] = $seller->id;
            $sellerMarketplaces[$seller->id] = $seller->mws_marketplaceid;
        }
        
        while($afterDate <= $beforeDate){
            try{
                Log::Info($afterDate);
                $skus_info = [];

                $orders = DB::connection('amazon')->select("select seller_account_id,
                seller_sku,
                asin,
                item_price_currency_code as currency,
                sum(quantity_ordered) as sales,
                sum(item_price_amount-promotion_discount_amount) as amount,
                sum((case when item_price_amount>0 then quantity_ordered else 0 end)) 
                as quantity
                from order_items where date(purchase_date)='$afterDate'
                group by seller_account_id,seller_sku,asin,currency");
                foreach($orders as $order){
                    $marketplaceId = array_get($sellerMarketplaces,$order->seller_account_id);
                    $sellerId = array_get($sellerAccounts,$order->seller_account_id);
                    $primarySellerAccountId = array_get($primarySellerAccounts,$sellerId);
                    $key = $primarySellerAccountId.'|'.$marketplaceId.'|'.$order->seller_sku;
                    if(!isset($skus_info[$key]['amount'])) $skus_info[$key]['amount']=0;
                    if(!isset($skus_info[$key]['sale'])) $skus_info[$key]['sale']=0;
                    if(!isset($skus_info[$key]['resvered'])) $skus_info[$key]['resvered']=0;
                    if(!isset($skus_info[$key]['asin'])) $skus_info[$key]['asin']=$order->asin;
                    $skus_info[$key]['resvered']+=$order->sales;
                    $skus_info[$key]['amount']+=round($order->amount*array_get($rates,$order->currency),2);
                    $skus_info[$key]['sale']+=$order->quantity;
                }
                
                $sales =  DB::connection('amazon')->select("select seller_sku,item_type,type,currency,sum(quantity_shipped) as shipped,
                sum(amount) as amount,seller_account_id,current_marketplace_id from finances_shipment_events where date(posted_date)='$afterDate' 
                group by seller_account_id,current_marketplace_id,seller_sku,item_type,type,currency");
                foreach($sales as $sale){
                    $marketplaceId = ($sale->current_marketplace_id)?array_get($additionalMarketplaces,$sale->current_marketplace_id):array_get($sellerMarketplaces,$sale->seller_account_id);
                    $key=strtoupper($sale->seller_account_id.'|'.$marketplaceId.'|'.$sale->seller_sku);
                    if(!isset($skus_info[$key]['income'])) $skus_info[$key]['income']=0;
                    if(!isset($skus_info[$key]['shipped'])) $skus_info[$key]['shipped']=0;
                    if(!isset($skus_info[$key]['replace'])) $skus_info[$key]['replace']=0;
                    if(!isset($skus_info[$key]['profit'])) $skus_info[$key]['profit']=0;
                    if($sale->item_type == 'ItemCharge') $skus_info[$key]['income']+=round($sale->amount*array_get($rates,$sale->currency),2);
                    if($sale->type == 'Principal') $skus_info[$key]['shipped']+=$sale->shipped;
                    if(!$sale->current_marketplace_id && $sale->type=='FBAPerUnitFulfillmentFee') $skus_info[$key]['replace']+=$sale->shipped;
                    $skus_info[$key]['profit']+=round($sale->amount*array_get($rates,$sale->currency),2);
                }

                $refunds =  DB::connection('amazon')->select("select seller_account_id,current_marketplace_id,seller_sku,item_type,currency,sum(amount) as amount from finances_refund_events
                where date(posted_date)='$afterDate' group by seller_account_id,current_marketplace_id,seller_sku,item_type,currency");
                foreach($refunds as $refund){
                    $marketplaceId = ($refund->current_marketplace_id)?array_get($additionalMarketplaces,$refund->current_marketplace_id):array_get($sellerMarketplaces,$refund->seller_account_id);
                    $key=strtoupper($refund->seller_account_id.'|'.$marketplaceId.'|'.$refund->seller_sku);
                    if(!isset($skus_info[$key]['refund'])) $skus_info[$key]['refund']=0;
                    if(!isset($skus_info[$key]['profit'])) $skus_info[$key]['profit']=0;
                    if($refund->item_type == 'ItemChargeAdjustment') $skus_info[$key]['refund']+=round($refund->amount*array_get($rates,$refund->currency),2);
                    $skus_info[$key]['profit']+=round($refund->amount*array_get($rates,$refund->currency),2);
                }

                $returns =  DB::connection('amazon')->select("select 
                a.seller_account_id,a.seller_sku,a.asin,sum(a.quantity) as quantity, b.seller_account_id as current_seller_account_id from amazon_returns a 
                left join order_items b 
                on a.amazon_order_id=b.amazon_order_id and a.seller_sku=b.seller_sku and a.asin=b.asin
                where date(a.return_date)='$afterDate' group by seller_account_id,seller_sku,asin,current_seller_account_id");
                foreach($returns as $return){
                    $marketplaceId = ($return->current_seller_account_id)?array_get($sellerMarketplaces,$return->current_seller_account_id):array_get($sellerMarketplaces,$return->seller_account_id);
                    $key=strtoupper($return->seller_account_id.'|'.$marketplaceId.'|'.$return->seller_sku);
                    if(!isset($skus_info[$key]['return'])) $skus_info[$key]['return']=0;
                    if(!isset($skus_info[$key]['asin'])) $skus_info[$key]['asin']=$return->asin;
                    $skus_info[$key]['return']+=intval($return->quantity);
                }
                foreach($skus_info as $key=>$v){
                    $keySplit = explode('|',$key);
                    if(count($keySplit)!=3) continue;
                    $sellerAccountId = $keySplit[0];
                    $marketplaceId = $keySplit[1];
                    $sellerSku = $keySplit[2];
                    if(!isset($v['asin'])){
                        $v['asin'] = DB::connection('amazon')->table('seller_skus')
                        ->where('seller_account_id',$sellerAccountId)
                        ->where('seller_sku',$sellerSku)
                        ->value('asin');
                    }

                    $matchSku = DB::connection('amazon')->table('sap_asin_match_sku')
                        ->where('seller_id',array_get($sellerAccounts,$sellerAccountId))
                        ->where('seller_sku',$sellerSku)
                        ->whereRaw("marketplace_id = '".$marketplaceId."' ".(($v['asin'])?" and asin='".$v['asin']."'":""))
                        ->first();  

                    if(!empty($matchSku)){
                        $v['asin'] = $v['asin']??$matchSku->asin;
                        $v['sku'] = $matchSku->sku;
                        $v['sku_status'] = $matchSku->sku_status;
                        $v['sap_seller_id'] = $matchSku->sap_seller_id;
                        $v['sap_seller_bg'] = $matchSku->sap_seller_bg;
                        $v['sap_seller_bu'] = $matchSku->sap_seller_bu;
                    }

                    if(!isset($v['asin'])) $v['asin']=NULL;
                    if(!isset($v['sku'])) $v['sku']=NULL;

                    $sku = $v['sku'];
                    $site = array_get($marketplaceCountryCodes,$marketplaceId);
                    //采购成本
                    $sku_base=DB::table('sku_base')->where('sku',$sku)->first();
                    $cost=!empty($sku_base)?round($sku_base->cost,2):0;
                    $volume=!empty($sku_base)?round($sku_base->volume/1000000,8):0;
                    $size=!empty($sku_base)?intval($sku_base->size):0;

                    //关税
                    $tax_rate=DB::table('tax_rate')->where('site',$site)->whereIn('sku',array('OTHERSKU',$sku))->pluck('tax','sku');
                    $tax = round(isset($tax_rate[$sku])?$tax_rate[$sku]:array_get($tax_rate,'OTHERSKU',0),4);

                    $tax = round($cost*$tax,2);

                    //头程运费
                    $shipfee = (array_get(getShipRate(),$site.'.'.$sku)??array_get(getShipRate(),$site.'.default'))??0;
                    $headshipfee=round($volume*$shipfee,2);
                    
                    //人工设定成本
                    $cost_set = DB::table('cost_set')->where('sku',$sku)->where('site',$site)->value('cost');
                    $cost_set = $cost_set??0;
                    
                    //经济效益
                    $v['cost'] = ($cost+$tax+$headshipfee)*array_get($v,'shipped',0);
                    $v['economic'] = round(array_get($v,'profit',0)-$v['cost'],2);
                    
                    SaleDailyInfo::updateOrCreate(
                    [
                        'seller_id'=>array_get($sellerAccounts,$sellerAccountId), 
                        'marketplace_id'=>$marketplaceId, 
                        'seller_sku'=>$sellerSku, 
                        'date'=>$afterDate]
                        ,
                        $v
                    );
                }
            } catch (\Exception $ex) {
                throw $ex;
            }
            $afterDate = date('Y-m-d',strtotime($afterDate)+86400);
        }
        
    }
}
