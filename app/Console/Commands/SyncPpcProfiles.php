<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PpcProfile;
use App\Classes\PpcRequest;
use DB;
use Log;

class SyncPpcProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:PpcProfiles';

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
		
		$oldProfiles = PpcProfile::get()->keyBy('profile_id');
		
		$maxAccountId = PpcProfile::max('account_id');
		
        $profiles = DB::connection('ad')->select("select a.profile_id,a.account_id,b.seller_id,c.marketplace as marketplace_id,b.name as account_name,a.access_token,a.refresh_token,'0' as expires_in,
(
case when b.marketplace_id<=3 then 'NA'
when b.marketplace_id=10 then 'FE'
else 'EU' end)
as region from ppc_profiles a left join accounts b on a.account_id=b.id
left join marketplaces c on b.marketplace_id=c.id where b.user_id=8566 and b.seller_id is not null");
	//print_r($profiles);	
        foreach($profiles as $profile){
			$oldProfile = array_get($oldProfiles,$profile->profile_id);
			if(!empty($oldProfile)){
				if($oldProfile->refresh_token == $profile->refresh_token) continue;
				$oldProfile->refresh_token = $profile->refresh_token;
				$oldProfile->account_id = $profile->account_id;
				$oldProfile->access_token = '';
				$oldProfile->expires_in = 0;
				$oldProfile->save();
			}elseif( $profile->account_id > $maxAccountId){
				PpcProfile::updateOrCreate(
					[
						'profile_id'=>$profile->profile_id
					]
					,
					[
						'account_id'=>$profile->account_id,
						'seller_id'=>$profile->seller_id,
						'marketplace_id'=>$profile->marketplace_id,
						'account_name'=>$profile->account_name??($profile->seller_id.' '.$profile->region),
						'refresh_token'=>$profile->refresh_token,
						'access_token'=>'',
						'expires_in'=>0,
						'region'=>$profile->region
					]
				);
			}
            
        }
        
	}

}
