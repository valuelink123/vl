<?php
/**
 * Created by PhpStorm.
 * Author: liyali
 * Date: 9/20/2018
 * Time: 2:02 PM
 */

namespace App\Console\Commands;


use Illuminate\Console\Command;
use DB;
use Log;

class GetSettlementReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:settlementreport';

    /**
     *  访问公司账号，通过MWS获取settlement report信息，
     *  report类型为_GET_V2_SETTLEMENT_REPORT_DATA_XML_
     *
     * @var string
     */
    protected $description = 'Command description';

    public static function getRegionData()
    {
        return [
            'com'=>[
                'marketplaceId'=>'ATVPDKIKX0DER',
                'serviceUrl'=>'https://mws.amazonservices.com/',
                'country'=>'USA',
                'access_key_id'=>'AKIAJHGCIIWNBX73UCWQ',
                'secret_key'=>'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'
            ],
            'de'=>[
                'marketplaceId'=>'A1PA6795UKMFR9',
                'serviceUrl'=>'https://mws.amazonservices.de/',
                'country'=>'Germany',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ],
            'uk'=>[
                'marketplaceId'=>'A1F83G8C2ARO7P',
                'serviceUrl'=>'https://mws.amazonservices.co.uk/',
                'country'=>'United Kingdom',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ],
            'fr'=>[
                'marketplaceId'=>'A13V1IB3VIYZZH',
                'serviceUrl'=>'https://mws.amazonservices.fr/',
                'country'=>'France',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ],
            'ca'=>[
                'marketplaceId'=>'A2EUQ1WTGCTBG2',
                'serviceUrl'=>'https://mws.amazonservices.ca/',
                'country'=>'Canada',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ],
            'jp'=>[
                'marketplaceId'=>'A1VC38T7YXB528',
                'serviceUrl'=>'https://mws.amazonservices.jp/',
                'country'=>'Japan',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ],
            'it'=>[
                'marketplaceId'=>'APJ6JRA9NG5V4',
                'serviceUrl'=>'https://mws.amazonservices.it/',
                'country'=>'Italy',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ],
            'es'=>[
                'marketplaceId'=>'A1RKKUPIHCS9HS',
                'serviceUrl'=>'https://mws.amazonservices.es/',
                'country'=>'Spain',
                'access_key_id'=>'AKIAJNNSOGJXNZ3OYOIA',
                'secret_key'=>'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'
            ]
        ];
    }
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
        set_time_limit(0);
        //获取所有用户，然后循环读取用户信息，通过站点标识从站点拉取report信息
        $accountList = DB::table('seller_accounts')->where('actived',1)->get();
        if(isset($accountList) && !empty($accountList)){
            foreach ($accountList as $account)
            {
                sleep(20);
                $regionData = self::getRegionData();
                $domain = $account->domain;
                $serviceUrl = $regionData[$domain]['serviceUrl'];
                $service = new \MarketplaceWebService_Client(
                    $regionData[$domain]['access_key_id'],
                    $regionData[$domain]['secret_key'],
                    $this->getConfig($serviceUrl),
                    'AMZMSG',
                    'V1.0');
                $request = new \MarketplaceWebService_Model_GetReportListRequest();
                $type = new \MarketplaceWebService_Model_TypeList();
                try{
                        $request->setMerchant($account->seller_id);
                        $request->setMWSAuthToken($account->token);
                        $request->setAvailableFromDate('2018-07-01T00:00:00+00:00 ');
                        $request->setAvailableToDate('2018-09-25T23:59:59+00:00 ');
                        $type->setType('_GET_V2_SETTLEMENT_REPORT_DATA_FLAT_FILE_');
                        $request->setReportTypeList($type);
                        $response = $service->getReportList($request);
                        $reportListResult = $response->getGetReportListResult();
                        $reportInfoList = $reportListResult->getReportInfoList();
                        if(!isset($reportInfoList) || empty($reportInfoList)){
                            continue;
                        }
                        foreach ($reportInfoList as $reportInfo) {
                            $reportId = $reportInfo->getReportId();
                            $reportInfo->getAvailableDate()->format('Y-m-d\TH:i:s\Z');
                            sleep(60);
                            //拉取报告信息，并存储
                            $this->getReport($account->seller_id,$reportId,$service);
                        }

                 }catch (MarketplaceWebService_Exception $ex){

                }
           }
        }
    }
    /*
     * 根据reportid 获取report的具体信息
     */
    public function getReport($seller_id,$reportId,$service)
    {
        set_time_limit(0);
        try {
            ob_start();
            $fileHandle = @fopen('php://memory', 'rw+');
            $parameters = array(
                'Merchant' => $seller_id,
                'Report' => $fileHandle,
                'ReportId' => $reportId,
                /*    'MWSAuthToken' => $auth_token, // Optional*/
            );
            $request = new \MarketplaceWebService_Model_GetReportRequest($parameters);
            $response = $service->getReport($request);
            $getReportResult = $response->getGetReportResult();
            rewind($fileHandle);
            $header = NULL;
            $updateMarketplace = false;
            $currency ="";
            while(!feof($fileHandle)){
                $responseStr =  fgets($fileHandle);
                $reportInfo =explode("\t",$responseStr);
                if(empty($responseStr) || !isset($responseStr)){
                    break;
                }
                if(!$header){
                     array_unshift($reportInfo,"sellerid");
                    $header = $reportInfo;
                }else{
                    array_unshift($reportInfo,$seller_id);
                    $header[36] = "other-amount";
                    $reportItem = array_combine($header,$reportInfo);
                    if(isset($reportItem["settlement-start-date"]) && !empty($reportItem["settlement-start-date"]) && $reportItem["settlement-start-date"]!=''){
                        $currency = $reportItem["currency"];
                        $settlement = array(
                            "sellerid"=> $seller_id,
                            "settlement-id" =>$reportItem["settlement-id"],
                            "settlement-start-date" =>substr($reportItem["settlement-start-date"],0,19),
                            "settlement-end-date" =>substr($reportItem["settlement-end-date"],0,19),
                            "deposit-date" =>substr($reportItem["deposit-date"],0,19),
                            "total-amount" =>$reportItem["total-amount"],
                            "currency" =>$currency,
                        );
                        DB::table('settlement')->insert($settlement);
                    }else{
                        unset($reportItem["settlement-start-date"]);
                        unset($reportItem["settlement-end-date"]);
                        unset($reportItem["deposit-date"]);
                        unset($reportItem["total-amount"]);
                        $reportItem["currency"] = $currency;
                        $reportItem["posted-date"] = substr($reportItem["posted-date"],0,19);
                        $reportItem["shipment-fee-amount"] = (isset($reportItem["shipment-fee-amount"]) && !empty($reportItem["shipment-fee-amount"]))?:0;
                        $reportItem["order-fee-amount"] = (isset($reportItem["order-fee-amount"]) && !empty($reportItem["order-fee-amount"]))?:0;
                        $reportItem["item-related-fee-amount"] = (isset($reportItem["item-related-fee-amount"]) && !empty($reportItem["item-related-fee-amount"]))?:0;
                        $reportItem["price-amount"] = (isset($reportItem["price-amount"]) && !empty($reportItem["price-amount"]))?:0;
                        $reportItem["misc-fee-amount"] = (isset($reportItem["misc-fee-amount"]) && !empty($reportItem["misc-fee-amount"]))?:0;
                        $reportItem["other-fee-amount"] = (isset($reportItem["other-fee-amount"]) && !empty($reportItem["other-fee-amount"]))?:0;
                        $reportItem["promotion-amount"] = (isset($reportItem["promotion-amount"]) && !empty($reportItem["promotion-amount"]))?:0;
                        $reportItem["direct-payment-amount"] = (isset($reportItem["direct-payment-amount"]) && !empty($reportItem["direct-payment-amount"]))?:0;
                        $reportItem["other-amount"] = (isset($reportItem["other-amount"]) && !empty($reportItem["other-amount"]))?:0;
                        $reportItem["quantity-purchased"] = (isset($reportItem["quantity-purchased"]) && !empty($reportItem["quantity-purchased"]))?:0;
                        DB::table('settlement_report')->insert($reportItem);
                        if(!$updateMarketplace){
                            $updateMarketplace = DB::table('settlement_report')->where("settlement-id",$reportItem["settlement-id"])->update(["marketplace-name"=>$reportItem["marketplace-name"]]);
                        }
                    }
                }
            }
            @fclose($fileHandle);
            ob_end_clean();
        }catch (Exception $ex){
            echo("Caught Exception: " . $ex->getMessage() . "\n");
            echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
        }
    }

    /**
     * 配置文件
     * @author lyl
     * @copyright 2018-09-20
     * @param unknown $serviceUrl
     * @return multitype:NULL number unknown**/

    public function getConfig($serviceUrl)
    {
        $config = array(
            'ServiceURL' => $serviceUrl,
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'MaxErrorRetry' => 3,
        );
        return $config;
    }


}