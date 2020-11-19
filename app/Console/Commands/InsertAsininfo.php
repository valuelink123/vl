<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

class InsertAsininfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:asininfo';

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

		$ratingAsins=DB::table('rating_asin')->where('flag',0)->get()->toArray();
		if($ratingAsins){
			foreach ($ratingAsins as $ratingAsin){
				$exists = DB::connection('review_new')->table('tbl_star_system_product')->where('asin', $ratingAsin['asin'])->where('domain', $ratingAsin['domain'])->get()->toArray();
				if(!$exists){
					DB::connection('review_new')->table('tbl_star_system_product')->insert(
						array(
							'asin'=>$ratingAsin['asin'],
							'domain'=>$ratingAsin['domain']
						)
					);

				}
				DB::table('rating_asin')->where('id', $ratingAsin['id'])->update(['flag' => 1]);
			}
		}
    }

}
