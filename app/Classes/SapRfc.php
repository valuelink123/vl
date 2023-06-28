<?php
namespace App\Classes;

class SapRfc {

    private $host;
    private $appid;
    private $appsecret;

    public function __construct() {
        $this->host = env("SAP_RFC_HOST");
        $this->appid = env("SAP_RFC_ID");
        $this->appsecret = env("SAP_RFC_PWD");
    }

    public function __call($method, $arguments) {
        $arguments=$arguments[0];
        $arguments['appid'] = $this->appid;
        $arguments['method'] = $method;
        
        ksort($arguments);

        $authstr = "";
        foreach ($arguments as $k => $v) {
            $authstr.= $k;
        }

        
        $authstr.= $this->appsecret;
        $arguments['sign'] =  strtoupper(sha1($authstr));

        try {

            $json = curl_request($this->host,$arguments);
        } catch (\Exception $ex) {
            throw $ex;
        }

        $json = json_decode($json, true);
        return $json;
    }
}
