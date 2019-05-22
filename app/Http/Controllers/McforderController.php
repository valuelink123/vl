<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
class McforderController extends Controller
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
        if(!Auth::user()->can(['mcforders'])) die('Permission denied -- mcforders');
		$date_from=date('Y-m-d',strtotime('-90 days'));		
		$date_to=date('Y-m-d');	

        return view('mcforder/index',['date_from'=>$date_from ,'date_to'=>$date_to,'accounts'=>$this->getSellerId()]);
    }
	
	public function getSellerId(){
		$seller=[];
		$accounts= DB::connection('order')->table('accounts')->where('status',1)->groupBy(['sellername','sellerid'])->get(['sellername','sellerid']);
		$accounts=json_decode(json_encode($accounts), true);
		foreach($accounts as $account){
			$seller[$account['sellerid']]=$account['sellername'];
		}
		return $seller;
	}
	

    public function show($id)
    {
		if(!Auth::user()->can(['mcforders'])) die('Permission denied -- mcforders');
        $mcf_order = DB::connection('order')->table('amazon_mcf_orders')->find($id);
		if($mcf_order){
			$mcf_order->items = DB::connection('order')->table('amazon_mcf_orders_item')->where('SellerId',$mcf_order->SellerId)->where('SellerFulfillmentOrderId',$mcf_order->SellerFulfillmentOrderId)->get();
			$mcf_order->shipments = DB::connection('order')->table('amazon_mcf_shipment_item')->where('SellerId',$mcf_order->SellerId)->where('SellerFulfillmentOrderId',$mcf_order->SellerFulfillmentOrderId)->get();
			$mcf_order->packages = DB::connection('order')->table('amazon_mcf_shipment_package')->where('SellerId',$mcf_order->SellerId)->where('SellerFulfillmentOrderId',$mcf_order->SellerFulfillmentOrderId)->get();
		}else{
			die();
		}
		return view('mcforder/view',['order'=>$mcf_order,'accounts'=>$this->getSellerId()]);
    }
    public function get(Request $request)
    {

        if(!Auth::user()->can(['mcforders'])) die('Permission denied -- mcforders');
		$orderby = $request->input('order.0.column',1);
		if($orderby==6){
			$orderby = 'StatusUpdatedDateTime';
		}else{
			$orderby = 'DisplayableOrderDateTime';
		}
        $sort = $request->input('order.0.dir','desc');
		
        $date_from=$request->input('date_from')?$request->input('date_from'):date('Y-m-d',strtotime('- 90 days'));
        $date_to=$request->input('date_to')?$request->input('date_to'):date('Y-m-d');
		
		$datas= DB::connection('order')->table('amazon_mcf_orders')->where('DisplayableOrderDateTime','>=',$date_from.' 00:00:00')->where('DisplayableOrderDateTime','<=',$date_to.' 23:59:59');
               
        if($request->input('sellerid')){
            $datas = $datas->where('SellerId', $request->input('sellerid'));
        }
	
		if($request->input('order_id')){
            $datas = $datas->where('SellerFulfillmentOrderId', $request->input('order_id'));
        }
		if($request->input('status')){
            $datas = $datas->where('FulfillmentOrderStatus', $request->input('status'));
        }
		if($request->input('name')){
            $datas = $datas->where('Name', $request->input('name'));
        }

        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$lists =  $datas->orderBy($orderby,$sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
		$accounts = $this->getSellerId();
		$lists=json_decode(json_encode($lists), true);
		foreach ( $lists as $list){
            $records["data"][] = array(
                $list['DisplayableOrderDateTime'],
				array_get($accounts,$list['SellerId']),
				$list['SellerFulfillmentOrderId'],
				$list['Name'],
				$list['CountryCode'],
				$list['FulfillmentOrderStatus'],
				$list['StatusUpdatedDateTime'],
				'<a href="/mcforder/'.$list['id'].'" target="_blank">
					<button type="submit" class="btn btn-success btn-xs">View</button>
				</a>'
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

}