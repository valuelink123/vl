<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleDailyInfo;
use App\SapAsinMatchSku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;
use DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
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
    public function index(Request $request)
    {
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m',strtotime('-2days')).'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d',strtotime('-2days'));
		if($date_to>date('Y-m-d',strtotime('-2days'))) $date_to=date('Y-m-d',strtotime('-2days'));
		if($date_from>$date_to) $date_from=$date_to;
		
		$datas  = new SaleDailyInfo;
		$user_teams = new SapAsinMatchSku;

		$exportFileName = '';
		if (Auth::user()->seller_rules) {
			$where = '1=1 '.getSellerRules(Auth::user()->seller_rules,'sap_seller_bg','sap_seller_bu');
			$datas = $datas->whereRaw($where);
			$user_teams =$user_teams->whereRaw($where);
		} else {
			$datas =$datas->where('sap_seller_id',Auth::user()->sap_seller_id); 
			$user_teams =$user_teams->where('sap_seller_id',Auth::user()->sap_seller_id);
		} 

		$user_teams = $user_teams->selectRaw('sap_seller_bg as bg , sap_seller_bu as bu')
		->groupBy(['bg','bu'])->orderBy('bg','asc')->orderBy('bu','asc')->get();
		
		if(array_get($_REQUEST,'sap_seller_id')){
			$datas =$datas->where('sap_seller_id',array_get($_REQUEST,'sap_seller_id'));
			$exportFileName .= array_get($_REQUEST,'sap_seller_id').'_';
		}

		if(array_get($_REQUEST,'bgbu')){
			$bgbu = array_get($_REQUEST,'bgbu');
			$bgbu_arr = explode('_',$bgbu);
			if(array_get($bgbu_arr,0)) {
				$datas =$datas->where('sap_seller_bg',array_get($bgbu_arr, 0));
				$exportFileName .= array_get($bgbu_arr,0).'_';
			}
			if(array_get($bgbu_arr,1)){
				$datas =$datas->where('sap_seller_bu',array_get($bgbu_arr, 1));
				$exportFileName .= array_get($bgbu_arr,1).'_';
			}
		}
		if(array_get($_REQUEST,'keywords')){
			$keywords = array_get($_REQUEST,'keywords');
			$exportFileName .= $keywords.'_';
			if($keywords){
				$search_keys = explode(',', $keywords);
				$datas = $datas->where(function ($query) use ($search_keys) {
					foreach($search_keys as $search_key){
						$query->where('sku', 'like', '%'.$search_key.'%')
						->orWhere('asin', 'like', '%'.$search_key.'%');
					}
				});	
			}
		}
		
		if(array_get($_REQUEST,'sku_status')!==NULL && array_get($_REQUEST,'sku_status')!==''){
			$datas =$datas->where('sku_status',array_get($_REQUEST,'sku_status'));
			$exportFileName .= array_get($_REQUEST,'sku_status').'_';
		}
		if(!$exportFileName) $exportFileName = 'All_';
		$total_info = clone $datas;
		$hb_total_info = clone $datas;

		$datas =$datas->where('date','>=',$date_from)->where('date','<=',$date_to)->selectRaw('
			asin,
			marketplace_id,
			any_value(sku) as sku,
			any_value(sku_status) as sku_status,
			sum(promotion) as promotion,
			sum(shippingfee) as shippingfee,
			sum(commission) as commission,
			sum(tax) as tax,
			sum(amount) as amount,
			sum(resvered) as resvered,
			sum(sale) as sale,
			sum(income) as income,
			sum(shipped) as shipped,
			sum(`replace`) as `replace`,
			sum(profit) as profit,
			sum(refund) as refund,
			sum(`return`) as `return`,
			sum(cost) as cost,
			sum(coupon) as coupon,
			sum(deal) as deal,
			sum(cpc) as cpc,
			sum(bonus) as bonus,
			sum(economic) as economic
			')
		->groupBy(['asin','marketplace_id'])->orderBy("economic","desc")->get()->toArray();
		
		

		$sku_statuses = getSkuStatuses();
		$sap_seller = getUsers('sap_seller');
		$action = $request->input('action');
		if($action == 'Export'){
			$spreadsheet = new Spreadsheet();
			$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Total');
			$spreadsheet->addSheet($myWorkSheet, 0);
			$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Details');
			$spreadsheet->addSheet($myWorkSheet, 1);
			$arrayData=[];
			
			$arrayData[] = ['ASIN','站点','SKU','SKU状态','订单金额','退款金额','订单销量(含Pending)','退货数量','重发数量','日均销量','均价','Coupon','Deal','CPC','经济效益','提成'];
			foreach($datas as $val){
				$arrayData[] = [$val['asin'],array_get(getSiteUrl(),$val['marketplace_id'],$val['marketplace_id']),$val['sku'],array_get(getSkuStatuses(),$val['sku_status']),
				(string)$val['amount'],(string)$val['refund'],(string)$val['resvered'],(string)$val['return'],(string)$val['replace'],(string)round(($val['replace']+$val['shipped'])/((strtotime($date_to)-strtotime($date_from))/86400+1),2),
				(string)(($val['shipped']!=0)?round($val['income']/$val['shipped'],2):0),(string)$val['coupon'],(string)$val['deal'],(string)$val['cpc'],(string)$val['economic'],(string)$val['bonus']];
			}
			$spreadsheet->getSheet(0)->fromArray($arrayData,NULL,'A1');
			$arrayData=[];
			$arrayData[] = ['账号','站点','SellerSku','Date','Asin','Sku','SKU状态','订单金额','订单销量(含Pending)','订单成交量','财务收入','发货量','重发数量','毛利','退款金额','退货数量','成本(含头程关税)','Coupon','Deal','CPC','经济效益','提成','销售员','BG','BU'];
			$total_info =$total_info->where('date','>=',$date_from)->where('date','<=',$date_to)
			->orderBy("date","asc")->get()->toArray();
			$sellers = DB::connection('amazon')->table('seller_accounts')->where('primary',1)->pluck('label','mws_seller_id');
			foreach($total_info as $val){
				$arrayData[] = [
					array_get($sellers,$val['seller_id'],$val['seller_id']),
					array_get(getSiteUrl(),$val['marketplace_id'],$val['marketplace_id']),
					$val['seller_sku'],
					$val['date'],
					$val['asin'],
					$val['sku'],
					array_get(getSkuStatuses(),$val['sku_status']),
				(string)$val['amount'],(string)$val['resvered'],(string)$val['sale'],(string)$val['income'],(string)$val['shipped'],(string)$val['replace'],(string)$val['profit'],(string)$val['refund'],(string)$val['return'],(string)$val['cost'],
				(string)$val['coupon'],(string)$val['deal'],(string)$val['cpc'],(string)$val['economic'],(string)$val['bonus'],array_get($sap_seller,$val['sap_seller_id']),$val['sap_seller_bg'],$val['sap_seller_bu']];
			}
			
			$spreadsheet->getSheet(1)->fromArray($arrayData,NULL,'A1');
			$spreadsheet->setActiveSheetIndex(0);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$exportFileName.'.xlsx"');
			header('Cache-Control: max-age=0');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
			exit;
		}
		
		$hb_date_to = date('Y-m-d',strtotime($date_from)-86400);
		$hb_date_from = date('Y-m-d',2*strtotime($date_from)-strtotime($date_to)-86400);
		
		$total_info = $total_info->where('date','>=',$date_from)->where('date','<=',$date_to)
		->selectRaw('
			sum(amount) as amount,
			sum(promotion) as promotion,
			sum(shippingfee) as shippingfee,
			sum(commission) as commission,
			sum(tax) as tax,
			sum(resvered) as resvered,
			sum(sale) as sale,
			sum(income) as income,
			sum(shipped) as shipped,
			sum(`replace`) as `replace`,
			sum(profit) as profit,
			sum(refund) as refund,
			sum(`return`) as `return`,
			sum(cost) as cost,
			sum(coupon) as coupon,
			sum(deal) as deal,
			sum(cpc) as cpc,
			sum(bonus) as bonus,
			sum(economic) as economic
			')
		->first()->toArray();
		
		$hb_total_info = $hb_total_info->where('date','>=',$hb_date_from)->where('date','<=',$hb_date_to)
		->selectRaw('
			sum(amount) as amount,
			sum(promotion) as promotion,
			sum(shippingfee) as shippingfee,
			sum(commission) as commission,
			sum(tax) as tax,
			sum(resvered) as resvered,
			sum(sale) as sale,
			sum(income) as income,
			sum(shipped) as shipped,
			sum(`replace`) as `replace`,
			sum(profit) as profit,
			sum(refund) as refund,
			sum(`return`) as `return`,
			sum(cost) as cost,
			sum(coupon) as coupon,
			sum(deal) as deal,
			sum(cpc) as cpc,
			sum(bonus) as bonus,
			sum(economic) as economic
			')
		->first()->toArray();
	
		$viewData['total_info']= $total_info;
		$viewData['hb_total_info']= $hb_total_info;
		$viewData['datas']= $datas;
		$viewData['teams']= $user_teams;
		$viewData['sku_statuses']= $sku_statuses;
		$viewData['selected_sku_status']= $request->get('sku_status');
		$viewData['selected_keywords']= $request->get('keywords');
		$viewData['users']= $sap_seller;
		$viewData['date_from']= $date_from;
		$viewData['date_to']= $date_to;
		$viewData['selected_sap_seller_id']= $request->get('sap_seller_id');
		$viewData['bgbu']= $request->get('bgbu');
        return view('home',$viewData);

    }
}