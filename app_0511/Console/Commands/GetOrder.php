<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;
use App\Review;

class GetOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:order';

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
		$this->runAccount =array();
		$accountList = DB::connection('order')->table('accounts')->where('status',1)->get();
        foreach($accountList as $account){
             $this->runAccount[$account->SellerId][$account->MarketPlaceId] = array(
                'sellerId' => $account->SellerId,
				'marketplaceId' => $account->MarketPlaceId,
                'awsAccessKeyId' => $account->APISignature,
                'secretKey' => $account->APIPassword,
                'amazonServiceUrl' => $account->APIServerUrl,
            );
        }
		$reviews = Review::whereRaw("amazon_order_id<>'NO MATCHED' and seller_id is not null and seller_id<>'' and char_length(amazon_order_id)<>19 and char_length(buyer_email)>4 and locate('@',buyer_email)>0 and locate('.',buyer_email)>0")->orderBy('id','asc')->take(100)->get();
		foreach($reviews as $review){

			$marketplaceId = $this->matchMarketplace($review->site);

			
			$runAccount = array_get($this->runAccount,$review->seller_id.'.'.$marketplaceId);

			if($runAccount){
				$this->client = new \MarketplaceWebServiceOrders_Client(
					array_get($runAccount,'awsAccessKeyId'),
					array_get($runAccount,'secretKey'),
					'AmazonGetOrders',
					'1.0.0',
					['ServiceURL' => array_get($runAccount,'amazonServiceUrl')."/Orders/2013-09-01",
						'ProxyHost' => null,
						'ProxyPort' => -1,
						'MaxErrorRetry' => 3]
	
				);
				//print_r($this->client);
				$notEnd = false;
				$nextToken = null;
				$cdate = date('Y-m-d\TH:i:s',strtotime('-2 years'));
				//print_r($cdate);
				$matchAsin = false;
				do {
					//print_r($notEnd);
					if ($nextToken) {
						$request = new \MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest();
						$request->setNextToken($nextToken);
						$resultName = 'ListOrdersByNextTokenResult';
					} else {
						$request = new \MarketplaceWebServiceOrders_Model_ListOrdersRequest();
						$request->setMarketplaceId($marketplaceId);
						$request->setBuyerEmail($review->buyer_email);
						$request->setCreatedAfter($cdate);
						$request->setOrderStatus(array('Shipped','Unshipped','PartiallyShipped'));
						$request->setMaxResultsPerPage(30);
						$resultName = 'ListOrdersResult';
					}
					$request->setSellerId($review->seller_id);
	
					try {
	
						$response = $nextToken?$this->client->listOrdersByNextToken($request):$this->client->listOrders($request);
						$objResponse = processResponse($response);
						$resultResponse = $objResponse->{$resultName};
						$nextToken = isset($resultResponse->NextToken)?$resultResponse->NextToken:null;
						$lastOrders = isset($resultResponse->Orders->Order)?$resultResponse->Orders->Order:[];
						$notEnd = !empty($nextToken);
						$do_count=0;
						
						foreach($lastOrders as $order)
						{
							$do_count++;
							$orderDetails = json_decode(json_encode($order), true);
							
							$arrayOrder = array(
								'SellerId' => $runAccount['sellerId'],
								'MarketPlaceId' => $runAccount['marketplaceId'],
								'AmazonOrderId' => array_get($orderDetails,'AmazonOrderId',''),
								'SellerOrderId' => array_get($orderDetails,'SellerOrderId',''),
								'ApiDownloadDate' => date('Y-m-d H:i:s'),
								'PurchaseDate' => date('Y-m-d H:i:s',strtotime(array_get($orderDetails,'PurchaseDate',''))),
								'LastUpdateDate' => date('Y-m-d H:i:s',strtotime(array_get($orderDetails,'LastUpdateDate',''))),
								'OrderStatus' => array_get($orderDetails,'OrderStatus',''),
								'FulfillmentChannel' => array_get($orderDetails,'FulfillmentChannel',''),
								'SalesChannel' => array_get($orderDetails,'SalesChannel',''),
								'OrderChannel' => array_get($orderDetails,'OrderChannel',''),
								'ShipServiceLevel' => array_get($orderDetails,'ShipServiceLevel',''),
								'Name' => array_get($orderDetails,'ShippingAddress.Name',''),
								'AddressLine1' => array_get($orderDetails,'ShippingAddress.AddressLine1',''),
								'AddressLine2' => array_get($orderDetails,'ShippingAddress.AddressLine2',''),
								'AddressLine3' => array_get($orderDetails,'ShippingAddress.AddressLine3',''),
								'City' => array_get($orderDetails,'ShippingAddress.City',''),
								'County' => array_get($orderDetails,'ShippingAddress.County',''),
								'District' => array_get($orderDetails,'ShippingAddress.District',''),
								'StateOrRegion' => array_get($orderDetails,'ShippingAddress.StateOrRegion',''),
								'PostalCode' => array_get($orderDetails,'ShippingAddress.PostalCode',''),
								'CountryCode' => array_get($orderDetails,'ShippingAddress.CountryCode',''),
								'Phone' => array_get($orderDetails,'ShippingAddress.Phone',''),
								'Amount' => round(array_get($orderDetails,'OrderTotal.Amount',''),6),
								'CurrencyCode' => array_get($orderDetails,'OrderTotal.CurrencyCode',''),
								'NumberOfItemsShipped' => array_get($orderDetails,'NumberOfItemsShipped',''),
								'NumberOfItemsUnshipped' => array_get($orderDetails,'NumberOfItemsUnshipped',''),
								'PaymentMethod' => array_get($orderDetails,'PaymentMethod',''),
								'BuyerName' => array_get($orderDetails,'BuyerName',''),
								'BuyerEmail' => array_get($orderDetails,'BuyerEmail',''),
								'ShipServiceLevelCategory' => array_get($orderDetails,'ShipServiceLevelCategory',''),
								'EarliestShipDate' => array_get($orderDetails,'EarliestShipDate',''),
								'LatestShipDate' => array_get($orderDetails,'LatestShipDate',''),
								'EarliestDeliveryDate' => array_get($orderDetails,'EarliestDeliveryDate',''),
								'LatestDeliveryDate' => array_get($orderDetails,'LatestDeliveryDate',''),
							);
							$matchAsin = false;
							sleep(2);
							$Items = $this->getOrderItems($runAccount,$arrayOrder['AmazonOrderId']);
							$matchAsin = false;
							$arrayOrderItems = array();
							foreach($Items as $itemDetails){
								//print_r($ordersItem);
								print_r(array_get($itemDetails,'ASIN'));
								if(array_get($itemDetails,'ASIN') == $review->asin) $matchAsin = true;
								$arrayOrderItems[] = array(
									'SellerId' => $runAccount['sellerId'],
									'MarketPlaceId' => $runAccount['marketplaceId'],
									'AmazonOrderId' => array_get($orderDetails,'AmazonOrderId',''),
									'ASIN' => array_get($itemDetails,'ASIN',''),
									'SellerSKU' => array_get($itemDetails,'SellerSKU',''),
									'OrderItemId' => array_get($itemDetails,'OrderItemId',''),
									'Title' => array_get($itemDetails,'Title',''),
									'QuantityOrdered' => array_get($itemDetails,'QuantityOrdered',''),
									'QuantityShipped' => array_get($itemDetails,'QuantityShipped',''),
									'GiftWrapLevel' => array_get($itemDetails,'GiftWrapLevel',''),
									'GiftMessageText' => array_get($itemDetails,'GiftMessageText',''),
									'ItemPriceAmount' => round(array_get($itemDetails,'ItemPrice.Amount',''),6),
									'ItemPriceCurrencyCode' => array_get($itemDetails,'ItemPrice.CurrencyCode',''),
									'ShippingPriceAmount' => round(array_get($itemDetails,'ShippingPrice.Amount',''),6),
									'ShippingPriceCurrencyCode' => array_get($itemDetails,'ShippingPrice.CurrencyCode',''),
									'GiftWrapPriceAmount' => round(array_get($itemDetails,'GiftWrapPrice.Amount',''),6),
									'GiftWrapPriceCurrencyCode' => array_get($itemDetails,'GiftWrapPrice.CurrencyCode',''),
									'ItemTaxAmount' => round(array_get($itemDetails,'ItemTax.Amount',''),6),
									'ItemTaxCurrencyCode' => array_get($itemDetails,'ItemTax.CurrencyCode',''),
									'ShippingTaxAmount' => round(array_get($itemDetails,'ShippingTax.Amount',''),6),
									'ShippingTaxCurrencyCode' => array_get($itemDetails,'ShippingTax.CurrencyCode',''),
									'GiftWrapTaxAmount' => round(array_get($itemDetails,'GiftWrapTax.Amount',''),6),
									'GiftWrapTaxCurrencyCode' => array_get($itemDetails,'GiftWrapTax.CurrencyCode',''),
									'ShippingDiscountAmount' => round(array_get($itemDetails,'ShippingDiscount.Amount',''),6),
									'ShippingDiscountCurrencyCode' => array_get($itemDetails,'ShippingDiscount.CurrencyCode',''),
									'PromotionDiscountAmount' => round(array_get($itemDetails,'PromotionDiscount.Amount',''),6),
									'PromotionDiscountCurrencyCode' => array_get($itemDetails,'PromotionDiscount.CurrencyCode',''),
									'PromotionIds' => serialize(array_get($itemDetails,'PromotionIds','')),
									'CODFeeAmount' => round(array_get($itemDetails,'CODFee.Amount',''),6),
									'CODFeeCurrencyCode' => array_get($itemDetails,'CODFee.CurrencyCode',''),
									'CODFeeDiscountAmount' => round(array_get($itemDetails,'CODFeeDiscount.Amount',''),6),
									'CODFeeDiscountCurrencyCode' => array_get($itemDetails,'CODFeeDiscount.CurrencyCode',''),
								);
							}
							if($matchAsin){
								//插入订单数据
								$exists = DB::table('amazon_orders')->where('SellerId', $runAccount['sellerId'])->where('MarketPlaceId',$runAccount['marketplaceId'] )->where('AmazonOrderId', array_get($orderDetails,'AmazonOrderId'))->first();
               					if(!$exists){
									DB::beginTransaction();
									try{
										DB::table('amazon_orders_item')->insert($arrayOrderItems);
										unset($arrayOrder['Items']);
										DB::table('amazon_orders')->insert($arrayOrder);
										DB::commit();
									} catch (\Exception $e) {
										DB::rollBack();
									}
								}
								$review->amazon_order_id = $arrayOrder['AmazonOrderId'];
								$review->save();
								break;
							}
							
						}
	
					} catch (\MarketplaceWebServiceOrders_Exception $ex) {
						if ($ex->getStatusCode()==503) {
							$notEnd = true;
							sleep(60);
						}else{
							continue;
						}
					}
					$do_left = 60 - ($do_count*2);
					if($do_left>0) sleep(intval($do_left));
				} while ($notEnd);
				if(!$matchAsin){
					$review->amazon_order_id = 'NO MATCHED';
					$review->save();
				}
				
			}else{
				$review->amazon_order_id = 'NO MATCHED';
				$review->save();
			}
			
		}
		
    }
	
	
	public function getOrderItems($runAccount,$amazonOrderId)
    {
        $nextToken = null;
        $timestamp = null;
        $orderItems = [];
        $notEnd = false;
        do {
            if ($nextToken) {
                $request = new \MarketplaceWebServiceOrders_Model_ListOrderItemsByNextTokenRequest();
                $request->setNextToken($nextToken);
                $resultName = 'ListOrderItemsByNextTokenResult';
            }
            else{
                $request = new \MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
                $resultName = 'ListOrderItemsResult';
            }
            $request->setSellerId($runAccount['sellerId']);
            $request->setAmazonOrderId($amazonOrderId);
            try {
                $response = $nextToken?$this->client->listOrderItemsByNextToken($request):$this->client->listOrderItems($request);
                $objResponse = processResponse($response);
                $resultResponse = $objResponse->{$resultName};
                $nextToken = isset($resultResponse->NextToken)?$resultResponse->NextToken:null;
                $lastOrderItems = isset($resultResponse->OrderItems->OrderItem)?$resultResponse->OrderItems->OrderItem:[];
                $notEnd = !empty($nextToken);
                foreach($lastOrderItems as $item)
                {
                    $orderItems[] = json_decode(json_encode($item), true);
                }

            } catch (\MarketplaceWebServiceOrders_Exception $ex) {
                if ($ex->getStatusCode()==503) {
                    $notEnd = true;
                    sleep(3);
                }
                else
                    throw $ex;
            }
            catch(\Exception $ex){
                throw new Exception($ex->getMessage().' Amazon Order Id: '.$amazonOrderId);
            }
        }
        while($notEnd);
        return $orderItems;
    }
	function matchMarketplace($site){

		$domain = strtolower(trim(substr($site, strripos($site, '.')+1)));

		$markets = array(
         'com' =>'ATVPDKIKX0DER',
         'ca' =>'A2EUQ1WTGCTBG2',
         'mx' =>'A1AM78C64UM0Y8',
         'uk' =>'A1F83G8C2ARO7P',
         'de' =>'A1PA6795UKMFR9',
         'fr' =>'A13V1IB3VIYZZH',
         'it' =>'APJ6JRA9NG5V4',
         'es' =>'A1RKKUPIHCS9HS',
         'jp' =>'A1VC38T7YXB528'
     	);
		return array_get($markets,$domain);
	}
}
