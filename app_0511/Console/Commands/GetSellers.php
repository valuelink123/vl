<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

class GetSellers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:sellers';

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
		
		/*
		//die();
		$url = 'https://www.amazon.com/gp/offer-listing/B01N64GLDQ/ref=dp_olp_new?ie=UTF8&condition=new';
		print_r($url);
		$html = $this->customCurl($url,array('host'=>'47.88.9.187:8123','username'=>'amazonmessage','password'=>'amazonmessagepwd'));
		print_r($html);
		preg_match('/<h1[\S\s]*?olpSubHeadingSection[\S\s]*?>([[\S\s]*?)<\/h1>/i', $html, $titles);
		
		print_r($titles);
		die('123');	
		*/
		$asinList = DB::table('asin_seller_count')->where('status','<>','X')->where('seller_count_updated_at','<>',date('Y-m-d'))->orderBy('seller_count_updated_at','asc')->orderBy('error_count','asc')->take(10)->get();
		
		foreach($asinList as $asinInfo){
			try{
				$startIndex  = $endIndex = $sellerCount = 0;
				$title='';
				while($startIndex <= $endIndex){
					$asin = $asinInfo->asin;
					$url = 'https://'.$asinInfo->site.'/gp/offer-listing/'.$asin.'/?ie=UTF8&f_new=true&startIndex='.$startIndex;					
					$this->info($url);
					$html = $this->customCurl($url,array('host'=>'47.88.9.187:8123','username'=>'amazonmessage','password'=>'amazonmessagepwd'));
					if($startIndex==0){
						
						preg_match('/<h1[\S\s]*?olpSubHeadingSection[\S\s]*?>([[\S\s]*?)<\/h1>/i', $html, $titles);
						
						$title = trim(strip_tags(array_get($titles,1,'')));
						$this->info($title);
						preg_match_all('/\/gp\/offer-listing\/'.$asin.'\/[\S]*?ref=olp_page_([\d]+)[\S\s]*?>/i', $html, $pages);
						foreach($pages[1] as $page){
							$thisIndex = (intval($page)-1)*10;
							if($thisIndex>$endIndex) $endIndex=$thisIndex;
						}
					}
					
					
					preg_match_all('/\/gp\/aag\/main\/ref=olp_merch_name_[\S]*?asin='.$asin.'[\S]*?>([\S\s]*?)<\//i', $html, $sellers);
					$listsStr=$sellers[0];
					$listsArr=$sellers[1];
					if(!$listsStr){
						if($asinInfo->error_count>=3){
							DB::table('asin_seller_count')->where('asin',$asin)->where('site',$asinInfo->site)->update(
								array(
									'seller_count'=>0,
									'seller_count_updated_at'=>date('Y-m-d',strtotime('+1 day')),
									'error_count'=>0
								)
							);
						}else{
							DB::table('asin_seller_count')->where('asin',$asin)->where('site',$asinInfo->site)->update(
								array(
									'error_count'=>$asinInfo->error_count+1
								)
							);
						}
						break;
					}
					for ($i = 0; $i < count($listsStr); $i++){
						preg_match('/\/gp\/aag\/main\/ref=olp_merch_name_[\S]*?asin='.$asin.'[\S]*?seller=([A-Z0-9]*?)["&][\S\s]*?>/i', $listsStr[$i], $sellerids);
						$sellerid = $sellerids[1];
						if($sellerid && array_get($listsArr,$i)){
							$sellerCount++;
							$this->info(array_get($listsArr,$i));
							$this->info($sellerid);
							DB::table('asin_seller_details')->insert(
								array(
									'seller_id'=>$sellerid,
									'seller_name'=>array_get($listsArr,$i),
									'asin_seller_count_id'=>$asinInfo->id,
									'date'=>date('Y-m-d')
								)
							);
						}
					}
					$startIndex=$startIndex+10;				
				}
				
				if($title){
					DB::table('asin_seller_count')->where('asin',$asin)->where('site',$asinInfo->site)->update(
						array(
							'seller_count'=>$sellerCount,
							'seller_count_updated_at'=>date('Y-m-d'),
							'error_count'=>0,
							'title'=>$title
						)
					);
				}
				
			} catch(Exception $e) {
				$print_r($e);
				if($asinInfo->error_count>=3){
					DB::table('asin_seller_count')->where('asin',$asin)->where('site',$asinInfo->site)->update(
						array(
							'seller_count'=>0,
							'seller_count_updated_at'=>date('Y-m-d',strtotime('+1 day')),
							'error_count'=>0
						)
					);
				}else{
					DB::table('asin_seller_count')->where('asin',$asin)->where('site',$asinInfo->site)->update(
						array(
							'error_count'=>$asinInfo->error_count+1
						)
					);
				}
   			}
			sleep(5);
    	}
	}
	
	
	public function customCurl($url,$proxy='')
    {
        $s=rand(1,5);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->setUserAgent());
		//print_r($proxy);
		if($proxy && $s>1){
			$this->info('proxy');
			curl_setopt ($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, array_get($proxy,'host'));
			//curl_setopt ($ch, CURLOPT_PROXYPORT, array_get($proxy,'port'));
			curl_setopt ($ch, CURLOPT_PROXYUSERPWD, array_get($proxy,'username').":".array_get($proxy,'password'));
		}
		if(substr($url,0,5)=='https'){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT,60);

		$output = curl_exec($ch);
		print_r( curl_error($ch));
		$this->info($output);
		curl_close($ch);
		return $output;
    }

    public function setUserAgent()
    {
        $agentBrowser = array(
            'Firefox',
            'Safari',
            'Opera',
            'Flock',
            'Internet Explorer',
            'Seamonkey',
            'Konqueror'
        );
        $agentOS = array(
            'Windows 3.1',
            'Windows 95',
            'Windows 98',
            'Windows 2000',
            'Windows NT',
            'Windows XP',
            'Windows Vista',
            'Redhat Linux',
            'Ubuntu',
            'Fedora',
            'AmigaOS',
            'OS 10.5'
        );
        return $agentBrowser[rand(0,6)].'/'.rand(1,8).'.'.rand(0,9).' (' .$agentOS[rand(0,11)].' '.rand(1,7).'.'.rand(0,9).'; en-US;)';
    }

}
