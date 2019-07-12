<?php

namespace App\Http\Controllers;
use App\User;
use App\Asin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
class ProlineController extends Controller
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

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {	
		if(!Auth::user()->can(['proline-show'])) die('Permission denied -- proline-show');
		$teams= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
        return view('proline/index',['teams'=>$teams,'users'=>$this->getUsers()]);
		

    }
	
	
	public function getUsers(){
        $users = User::Where('sap_seller_id','>',0)->orderBy('sap_seller_id','asc')->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['sap_seller_id']] = $user['name'];
        }
        return $users_array;
    }

    
	
    public function get(Request $request)
    {
		if(!Auth::user()->can(['proline-show'])) die('Permission denied -- proline-show');
		$users= $this->getUsers();
		
		$datas=  DB::table('asin')->leftJoin('skus_base',function($q){
				$q->on('asin.item_no', '=', 'skus_base.MATNR');
			})->leftJoin('skus_site_base',function($q){
				$q->on('asin.item_no', '=', 'skus_site_base.MATNR')
					->on('asin.sap_site_id', '=', 'skus_site_base.VKBUR');
			})->leftJoin('fba_stock',function($q){
				$q->on('asin.item_no', '=', 'fba_stock.item_code')
					->on('asin.asin', '=', 'fba_stock.asin')->on('asin.sellersku', '=', 'fba_stock.seller_sku');
			});
               
        if($request->input('sap_seller_id')){
            $datas = $datas->where('asin.sap_seller_id', $request->input('sap_seller_id'));
        }
		
		if($request->input('bgbu') ){
			   $bgbu = $request->input('bgbu');
			   $bgbu_arr = explode('_',$bgbu);
			   if(count($bgbu_arr)>1){
			   	if(array_get($bgbu_arr,0)) $datas = $datas->where('bg',array_get($bgbu_arr,0));
			   	if(array_get($bgbu_arr,1)) $datas = $datas->where('bu',array_get($bgbu_arr,1));
			   }else{
			   		$datas = $datas->whereNull('bg');
			   }
		}
		if($request->input('sku')){
            $datas = $datas->where('item_no', $request->input('sku'));
        }
		if($request->input('sap_site_id')){
            $datas = $datas->where('sap_site_id', $request->input('sap_site_id'));
        }
		
		
		$iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->get(['asin.*','skus_base.*','skus_site_base.*','fba_stock.fba_stock'])->toArray();
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

		
		$lists=json_decode(json_encode($lists), true);
		foreach ( $lists as $list){
            $records["data"][] =
			array(
				$list['item_no'],
				$list['ZPM'],
				$list['LABOR'],
				$list['ZDMET'],
				$list['ZGCRY'],
				$list['ZCORY'],
				$list['WGBEZ'],
				$list['MATKL'],
				$list['ZPXDJ'],
				$list['BRAND'],
				$list['ZBRAND'],
				$list['MODEL'],
				$list['GUIG'],
				$list['SFPJ'],
				round($list['LAENG'],2),
				round($list['BREIT'],2),
				round($list['HOEHE'],2),
				$list['ZCC'],
				$list['NAME1'],
				$list['LIFNR'],
				$list['SFHZ'],
				$list['MATNRZT'],
				$list['MATNRDJ'],
				'',
				'<a href="https://'.array_get(getSapSiteCode(),$list['sap_site_id']).'/dp/'.$list['asin'].'">'.$list['asin'],
				array_get(getSapSiteCode(),$list['sap_site_id']),
				'',
				$list['last_keywords'],
				$list['sku_ranking'],
				$list['sku_ranking'],
				$list['sku_review'],
				$list['sku_rating'],
				$list['sku_sales'],
				$list['RATE'],
				$list['sku_price'],
				$list['ZCRATIO'].'%',
				round($list['sku_price']*$list['ZCRATIO']/100,2),
				round($list['NETPR2'],2),
				($list['Z003']*100).'%',
				round($list['NETPR']),
				$list['CCF1'],
				$list['CCF2'],
				$list['FBAPRICE'],
				round($list['VERPR_FBA'],2),
				round($list['FBAPRICE']+($list['sku_price']*$list['ZCRATIO']/100)+(($list['RATE'])?round($list['VERPR_FBA']/$list['RATE']):$list['VERPR_FBA']),2),
				round($list['sku_price']-($list['FBAPRICE']+round($list['sku_price']*$list['ZCRATIO']/100,2)+(($list['RATE'])?round($list['VERPR_FBA']/$list['RATE']):$list['VERPR_FBA'])),2),
				round(($list['sku_price'])?(($list['sku_price']-($list['FBAPRICE']+round($list['sku_price']*$list['ZCRATIO']/100,2)+(($list['RATE'])?round($list['VERPR_FBA']/$list['RATE']):$list['VERPR_FBA'])))/$list['sku_price']):0,2),
				$list['YCL'].'%',
				round((($list['sku_price'])?(($list['sku_price']-($list['FBAPRICE']+round($list['sku_price']*$list['ZCRATIO']/100,2)+(($list['RATE'])?round($list['VERPR_FBA']/$list['RATE']):$list['VERPR_FBA'])))/$list['sku_price']):0)+round($list['YCL']/100,4),2),
				
				intval($list['fba_stock']),
				intval($list['LBKUM_FBM']),
				intval($list['FBM_ZT']),
				intval($list['FBM_SC']),
				intval($list['FBM_WJ']),
				intval($list['fba_stock']+$list['LBKUM_FBM']+$list['FBM_ZT']+$list['FBM_SC']+$list['FBM_WJ']),
				(($list['sku_sales'])?round(($list['fba_stock']+$list['LBKUM_FBM']+$list['FBM_ZT']+$list['FBM_SC']+$list['FBM_WJ'])/$list['sku_sales'],2):'MAX'),
				round(($list['fba_stock']+$list['LBKUM_FBM']+$list['FBM_ZT']+$list['FBM_SC']+$list['FBM_WJ'])*$list['VERPR_FBA'],2),
				
				
				$list['bg'],
				$list['bu'],
				$list['seller'],
				
				
				$list['PRICE1'],
				$list['YWLRL1'],
				$list['YXFYL1'],
				$list['YWJLL1'],
				$list['XL1'],
				$list['XSE1'],
				
				
				$list['PRICE2'],
				$list['YWLRL2'],
				$list['YXFYL2'],
				$list['YWJLL2'],
				$list['XL2'],
				$list['XSE2'],
				
				
				$list['PRICE3'],
				$list['YWLRL3'],
				$list['YXFYL3'],
				$list['YWJLL3'],
				$list['XL3'],
				$list['XSE3'],

				$list['sku_strategy'],
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }
	
	
}