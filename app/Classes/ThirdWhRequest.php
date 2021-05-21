<?php
namespace App\Classes;
use App\Models\GuCangRequest;
class ThirdWhRequest {

    private $webServiceUrl;
    private $appToken;
    private $appkey;

    public function __construct($webServiceUrl, $appToken, $appkey) {
        $this->webServiceUrl = $webServiceUrl;
        $this->appToken = $appToken;
        $this->appkey = $appkey;
    }

    public function __call(string $method, array $arguments) {
        try {
            $paramsJson = json_encode($arguments[0]);
            $postFields = 
            '<?xml version="1.0" encoding="UTF-8"?>
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.example.org/Ec/">
                <SOAP-ENV:Body>
                    <ns1:callService>
                        <paramsJson>'.$paramsJson.'</paramsJson>
                        <appToken>'.$this->appToken.'</appToken>
                        <appKey>'.$this->appkey.'</appKey>
                        <service>'.$method.'</service>
                    </ns1:callService>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>';
            $returnData = curl_request($this->webServiceUrl,$postFields);
            $returnData = simplexml_load_string(html_entity_decode($returnData));
            $returnData = $returnData->xpath('//response')[0];
            $returnData = json_decode($returnData, true);
        } catch (\Exception $ex) {
            $returnData = [
                'ask'=>'Failure',
                'message'=>$ex->getMessage(),
            ];
        }
        GuCangRequest::Create(
            [
                'method'=>$method,
                'request'=>$paramsJson,
                'response'=>json_encode($returnData),
            ]
        );
        return $returnData;
    }

    
}

