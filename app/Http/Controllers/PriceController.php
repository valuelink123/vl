<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use App\Asin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PDO;
use DB;
use Log;
class PriceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */

    public function __construct()
    {
        $this->middleware('auth');
		parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		if(!Auth::user()->can(['price-model'])) die('Permission denied -- price-model');
		return view('price/index');
    }
	
	public function get(Request $request){
		if(!Auth::user()->can(['price-model'])) die('Permission denied -- price-model');
		
		$data = $request->get('data');
		$array = $priceGroup = $monthGroup = $normalGroup= [];
		$sku=$bg=$bu=$sap_seller_id='';//array_get($request->get('data'),'0.value');
		foreach($data as $val){
			if(substr($val['name'],0,11)=='price-group'){
				$vm = explode('][',$val['name']);
				$index = str_replace('price-group[','',array_get($vm,0)); 
				$key = str_replace(']','',array_get($vm,1)); 
				$priceGroup[str_replace('price-group[','',array_get($vm,0))][str_replace(']','',array_get($vm,1))] = (str_replace(']','',array_get($vm,1))=='ZLX')?trim($val['value']):round($val['value'],2);
			}elseif(substr($val['name'],0,6)=='month-'){
				$vm = explode('-',$val['name']);
				$monthGroup[array_get($vm,2)][array_get($vm,1)] = round($val['value'],2);
				$monthGroup[array_get($vm,2)]['MONTH2'] = sprintf("%02d",round(array_get($vm,2),2));
			}else{
				$normalGroup[$val['name']]=($val['name']=='I_MATNR' || $val['name']=='I_ZCPLX')?trim($val['value']):round($val['value'],2);
			}	
			if($val['name']=='I_MATNR') $sku = $val['value'];
		}
		
		if (Auth::user()->seller_rules) {
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $bg=array_get($rules,0);
			if(array_get($rules,1)!='*') $bu=array_get($rules,1);
		} elseif (Auth::user()->sap_seller_id) {
			$sap_seller_id = Auth::user()->sap_seller_id;
		} else {
		
		}
		$exAsin=Asin::where('item_no',$sku);
		if($bg) $exAsin=$exAsin->where('bg',$bg);
		if($bu) $exAsin=$exAsin->where('bu',$bu);
		if($sap_seller_id) $exAsin=$exAsin->where('sap_seller_id',$sap_seller_id);
		$exAsin = $exAsin->first();
		if(!$exAsin) die(json_encode(array('result'=>0,'message'=>'Permission denied or Sku Error')));
		$appkey = 'site0001';
		$appsecret= 'testsite0001';
		$array['sku']=array_get($normalGroup,'I_MATNR');
		$array['appid']= $appkey;
		$array['method']='getPrice';
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$array['I_TAB2']=$monthGroup;
		$array['I_TAB1']=$priceGroup;
		$array['IMPORT']=$normalGroup;
		$authstr=$authstr.$appsecret;
		$sign = strtoupper(sha1($authstr));
		$array['sign']=$sign;
		$res = curl_request('http://'.env("SAP_RFC").'/rfc_site.php',$array);
		die($res);
	}
	
	
	
	public function getStockAge(Request $request){
		$saprfc = new \App\Classes\SapRfcRequest();
		$res = $saprfc->getStockAge(['sku' => strtoupper($request->get('sku')),'site' => strtoupper($request->get('site'))]);
		die(json_encode((array_get($res,'1',[]))));
	}
	public function reservedProducts()
	{
	    return view('price/reservedProducts');
	}
}