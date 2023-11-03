<?php
namespace App\Classes;
use easyAmazonAdv\Factory;
use App\Models\PpcProfile;
class PpcRequest {

    private $app;
    private $config;
    private $profile;

    public function __construct($profile_id) {
        $this->config = [
            'clientId'      => 'amzn1.application-oa2-client.381f5cf4174e4f0882733038a88e5460',
            'clientSecret'  => '6ef00890105847a66d135e0847a78c5bd526a789ef08a65f4220af0ae4563387',
        ];
        $this->profile = PpcProfile::where('profile_id',$profile_id)->first();
        if(empty($this->profile)) throw new \Exception('Profile Id not Exists');
        $this->config['refreshToken'] = $this->profile->refresh_token;
        $this->config['accessToken'] = $this->profile->access_token;
        $this->config['region'] = ($this->profile->region)?$this->profile->region:'NA';
        if($this->profile->expires_in < time()+600) $this->refreshToken();
    }

    public function request($method) {
        $this->app = Factory::$method($this->config);
        $this->app->client->profileId =$this->profile->profile_id;
        return $this->app;
    }

    public function refreshToken() {
        $app = Factory::BaseService($this->config);
        $result = $app->access_token->RefreshToken();
        if(array_get($result,'success') == 1 && !empty(array_get($result,'response.access_token'))) {
            $this->config['accessToken'] = $this->profile->access_token = array_get($result,'response.access_token');
            $this->profile->expires_in = time()+3600;
            $this->profile->save();
        }
    }
}

