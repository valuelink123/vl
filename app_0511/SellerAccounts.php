<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Commands\ProductAmazonSync;
use App\Services\MultipleQueue;
use App\SellerPpcs;
use App\SellerMwsRequest;
class SellerAccounts extends Model
{
    //
    protected $table = 'seller_accounts';

    public function lock(){

    }
    public function unlock(){

    }

    public function assignProducts(array $orders,SellerAccounts $account,$scanImmediately = true)
    {
        $orderAsins = [];
        foreach ($orders as $order)
        {
            if (!isset($order['Items'])) continue;
            foreach($order['Items'] as $ordersItem){
                if(!array_get($ordersItem,'ASIN','')) continue;
                $orderAsins[array_get($ordersItem,'ASIN','')]['title'] = array_get($ordersItem,'Title','');
                $orderAsins[array_get($ordersItem,'ASIN','')]['seller_sku'] = array_get($ordersItem,'SellerSKU','');
                $orderAsins[array_get($ordersItem,'ASIN','')]['price'] = round(array_get($ordersItem,'ItemPrice.Amount',0),2);
            }
        }
        foreach ($orderAsins as $asin=>$asinInfo)
        {
            $productModel = Product::where(['asin' => $asin, 'user_id' => $account->user_id, 'seller_account_id' =>$account->id, 'marketplaceid' => $account->mws_marketplaceid])->first();
            if (empty($productModel)) {
                $productModel = Product::forceCreate(['asin' => $asin,'seller_sku' => $asinInfo['seller_sku'], 'title' => $asinInfo['title'], 'price' => $asinInfo['price'], 'user_id' => $account->user_id, 'seller_account_id' =>$account->id, 'marketplaceid' => $account->mws_marketplaceid,'updatingNow'=>true]);
                if ($scanImmediately) {
                    //MultipleQueue::pushOn(MultipleQueue::SCHEDULE_GET,
                        new ProductAmazonSync( $productModel->id,$productModel->asin,$productModel->marketplaceid );
                    //);
                }
            }
        }
    }

    public function createClient(){
        $siteConfig = getSiteConfig();
        $client = new \MarketplaceWebService_Client(
            $siteConfig[$this->mws_marketplaceid]['key_id'],
            $siteConfig[$this->mws_marketplaceid]['secret_key'],
            ['ServiceURL' => $siteConfig[$this->mws_marketplaceid]['serviceUrl'].'/',
                'ProxyHost' => null,
                'ProxyPort' => -1,
                'MaxErrorRetry' => 3],
            'AmazonGetOrders',
            '1.0.0'
        );
        return $client;
    }

    public function getReportId(\MarketplaceWebService_Client $client,$requestId){
        $reportId = '';
        $retry=0;
        while(!$reportId && $retry<3){
            sleep(60);
            $request = new \MarketplaceWebService_Model_GetReportRequestListRequest();
            $reportRequestIdListArray = new \MarketplaceWebService_Model_IdList();
            $reportRequestIdListArray->setId($requestId);
            $request->setMerchant($this->mws_seller_id);
            $request->setMWSAuthToken($this->mws_auth_token);
            $request->setReportRequestIdList($reportRequestIdListArray);
            $response = $client->getReportRequestList($request);

            $getReportRequestListResult = $response->getGetReportRequestListResult();
            $reportRequestInfoList = $getReportRequestListResult->getReportRequestInfoList();

            foreach ($reportRequestInfoList as $reportRequestInfo) {
                $reportId = $reportRequestInfo->getGeneratedReportId(); // 3746710502017158
                break;
            }
            $retry++;
        }
        return $reportId;
    }

    public function updateProductsRequest( $scanAsin = true) {
        //set_time_limit(0);
        $client = $this->createClient();

        try {

            $request = new \MarketplaceWebService_Model_RequestReportRequest();
            $request->setReportType('_GET_FLAT_FILE_OPEN_LISTINGS_DATA_');
            $request->setMerchant($this->mws_seller_id);
            $request->setMWSAuthToken($this->mws_auth_token);
            $request->setMarketplace($this->mws_marketplaceid);

            $response = $client->requestReport($request);
            $requestReportResult = $response->getRequestReportResult();
            $reportRequestInfo = $requestReportResult->getReportRequestInfo();
            $requestId = $reportRequestInfo->getReportRequestId();

            $reportId = $this->getReportId($client,$requestId);

            if($reportId){
                $res = getReportById($client,$reportId, $this->mws_seller_id, $this->mws_auth_token);

                foreach ($res as $asinInfo)
                {
                    $sku = current($asinInfo);
                    $asin = next($asinInfo);
                    $price = next($asinInfo);
                    $productModel = Product::where(['asin' => $asin, 'user_id' => $this->user_id, 'seller_account_id' =>$this->id, 'marketplaceid' => $this->mws_marketplaceid])->first();
                    if (empty($productModel)) {
                        $productModel = Product::forceCreate(['asin' => $asin,'seller_sku' => $sku, 'price' => round($price,2),'user_id' => $this->user_id, 'seller_account_id' =>$this->id, 'marketplaceid' => $this->mws_marketplaceid,'updatingNow'=>true]);
                        if($scanAsin){
                            //MultipleQueue::pushOn(MultipleQueue::SCHEDULE_GET,
                                new ProductAmazonSync( $productModel->id,$productModel->asin,$productModel->marketplaceid );
                            //);
                        }

                    }
                }
                $this->last_action_result = 'Connect Success';
            }else{
                $this->last_action_result = 'Connect Error';
            }

        } catch(\MarketplaceWebService_Exception $e){
            $error = $e->getMessage();
            $this->last_action_result = $error;
        }
        $this->save();
    }



    public function updatePpcRequest( $date ) {
        //set_time_limit(0);
        $client = $this->createClient();

        try {

            $request = new \MarketplaceWebService_Model_RequestReportRequest();
            $request->setReportType('_GET_PADS_PRODUCT_PERFORMANCE_OVER_TIME_DAILY_DATA_TSV_');
            $request->setMerchant($this->mws_seller_id);
            $request->setMWSAuthToken($this->mws_auth_token);
            $request->setMarketplace($this->mws_marketplaceid);
            $request->setStartDate($date);
            $response = $client->requestReport($request);
            $requestReportResult = $response->getRequestReportResult();
            $reportRequestInfo = $requestReportResult->getReportRequestInfo();
            $requestId = $reportRequestInfo->getReportRequestId();
            if(!$requestId) $requestId='';
            $reportId = $this->getReportId($client,$requestId);

            if($reportId){
                $res = getReportById($client,$reportId, $this->mws_seller_id, $this->mws_auth_token);
                $content = array();
                foreach ($res as $prod)
                {
                    if(!array_get($prod,'SKU','')) break;
                    unset($content_item);
                    $content_item['report_id'] = $reportId;
                    $content_item['seller_account_id'] = $this->id;
                    $content_item['user_id'] = $this->user_id;
                    $content_item['Date'] = substr($prod['Start Date'],0,10);
                    $content_item['SKU'] = $prod['SKU'];
                    $content_item['Clicks'] = intval($prod['Clicks']);
                    $content_item['Impressions'] = intval($prod['Impressions']);
                    $content_item['CTR'] = round($prod['CTR'],4);
                    $content_item['Currency'] = $prod['Currency'];
                    $content_item['TotalSpend'] = round(format_num($prod['Total Spend']),2);
                    $content_item['AvgCPC'] = round(format_num($prod['Avg. CPC']),2);
                    $content[] = $content_item;


                }
                if($content){
                    //MultipleQueue::pushOn(MultipleQueue::DB_WRITE,function () use ($content) {
                        SellerPpcs::insertIgnore($content);
                    //});
                }
                $this->last_action_result = 'Connect Success';
            }else{
                $this->last_action_result = 'Connect Error';
            }

        } catch(\MarketplaceWebService_Exception $e){
            $error = $e->getMessage();
            $this->last_action_result = $error;
        }
        //MultipleQueue::pushOn(MultipleQueue::DB_WRITE,function () use ($content) {
            SellerMwsRequest::forceCreate(['content' => json_encode($content),'report_type' => '_GET_PADS_PRODUCT_PERFORMANCE_OVER_TIME_DAILY_DATA_TSV_','request_report_id'=>$requestId, 'report_id' => $reportId,'user_id' => $this->user_id, 'seller_account_id' =>$this->id, 'request_startDate' => $date,'error'=>$error]);
        //});
        $this->save();
    }


}
