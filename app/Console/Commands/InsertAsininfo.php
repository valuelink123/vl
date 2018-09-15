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

		$sellerid_area=[];
		$sellerids = DB::connection('order')->select("select sellerid,(case MarketPlaceId
		when 'ATVPDKIKX0DER' then 'US'
		when 'A2EUQ1WTGCTBG2' then 'US'
		when 'A1AM78C64UM0Y8' then 'US'
		when 'A1F83G8C2ARO7P' then 'EU'
		when 'A1PA6795UKMFR9' then 'EU'
		when 'APJ6JRA9NG5V4' then 'EU'
		when 'A1RKKUPIHCS9HS' then 'EU'
		when 'A13V1IB3VIYZZH' then 'EU'
		when 'A1VC38T7YXB528' then 'JP'
		else 'US' End) as area from accounts GROUP BY sellerid,area");
		foreach($sellerids as $sellerid){
			$sellerid_area[$sellerid->sellerid] = $sellerid->area;
		}
		//print_r($sellerid_area);
		$stocks = DB::connection('order')->select('select asin,sellerid from amazon_inventory_supply group by asin,sellerid');
		
		foreach($stocks as $stock){
			$sites = [];
			if(array_get($sellerid_area,$stock->sellerid)=='JP'){
				$sites[]='www.amazon.co.jp';
			}
			if(array_get($sellerid_area,$stock->sellerid)=='US'){
				$sites[]='www.amazon.com';
				$sites[]='www.amazon.ca';
				//$sites[]='www.amazon.com.mx';
			}
			if(array_get($sellerid_area,$stock->sellerid)=='EU'){
				$sites[]='www.amazon.co.uk';
				$sites[]='www.amazon.de';
				$sites[]='www.amazon.it';
				$sites[]='www.amazon.es';
				$sites[]='www.amazon.fr';
			}
			foreach($sites as $site){
				//print_r($site);
				$exists = DB::connection('review_new')->table('tbl_star_system_product')->where('asin', $stock->asin)->where('domain', $site)->get()->toArray();
				if(!$exists) {
					DB::connection('review_new')->table('tbl_star_system_product')->insert(
						array(
							'asin'=>$stock->asin,
							'domain'=>$site
						)
					);	
				}
			}
		}
    }

}
