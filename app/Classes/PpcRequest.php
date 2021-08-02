<?php
namespace App\Classes;
use easyAmazonAdv\Factory;
use App\Models\PpcProfile;
class PpcRequest {

    private $app;
    private $config;
    private $profile;

    public function __construct($profile_id) {

        $this->profile = PpcProfile::where('profile_id',$profile_id)->first();
    }

    public function request($method) {
        return $this->app;
    }

    public function refreshToken() {

        $result = $this->RefreshToken();
        
    }
}

