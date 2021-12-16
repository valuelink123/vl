<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PpcProfile;
use Log;
use DB;


class AmazonAuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {

        $profiles = $_REQUEST['profiles'];
        if($profiles) {
            $adRegions = getAdRegions();
            $profile_arr = json_decode($profiles, $assoc = FALSE, $depth = 512, $options = 0);
            $expires_in = time() + 3600;
            if ($profile_arr) {
                foreach ($profile_arr as $profile) {
                    $account = DB::connection('vlz')->table('seller_accounts')->select('label')->where('mws_seller_id', $profile->seller_id)->where('mws_marketplaceid', $profile->marketplace_id)->get();
                    if ($account) {
                        PpcProfile::updateOrCreate([
                            'profile_id' => trim($profile->profile_id),
                            'account_id' => 0,
                            'marketplace_id' => trim($profile->marketplace_id),
                            'seller_id' => trim($profile->seller_id),
                            'account_name' => trim($account->label),
                            'access_token' => trim($profile->access_token),
                            'refresh_token' => trim($profile->refresh_token),
                            'region' => isset($adRegions[trim($profile->marketplace_id)]) ? $adRegions[trim($profile->marketplace_id)] : '',
                            'expires_in' => $expires_in
                        ]);
                    }
                }
            }
            Log::info($profiles);
        }
        return 'ok';

    }
}