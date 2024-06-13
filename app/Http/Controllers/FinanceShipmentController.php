<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\FinancesShipmentEvent;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class FinanceShipmentController extends Controller
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

    public function index()
    {
		$accounts = DB::connection('amazon')->table("seller_accounts")->where('primary',1)->where('mws_marketplaceid','A1VC38T7YXB528')->whereNull('deleted_at')->pluck('label','id');
        return view('finance/list',['accounts'=>$accounts]);
    }

    public function get(Request $request)
    {

        $datas = FinancesShipmentEvent::where('marketplace_name','Amazon.co.jp');
        $accounts = DB::connection('amazon')->table("seller_accounts")->where('primary',1)->where('mws_marketplaceid','A1VC38T7YXB528')->whereNull('deleted_at')->pluck('label','id');
        if(array_get($_REQUEST,'date_from')) {
            $datas = $datas->where('posted_date', '>=', array_get($_REQUEST,'date_from').' 00:00:00');
        }

        if(array_get($_REQUEST,'date_to')){
            $datas = $datas->where('posted_date', '<=', array_get($_REQUEST,'date_to').' 23:59:59');
        }
		
		if(array_get($_REQUEST,'seller_account_id')) {
            $datas = $datas->whereIn('seller_account_id',array_get($_REQUEST,'seller_account_id'));
        }
		
		if(array_get($_REQUEST,'keyword')){
			$keyword = array_get($_REQUEST,'keyword');
			$datas = $datas->where(function ($query) use ($keyword) {
                $query->where('amazon_order_id', 'like', '%'.$keyword.'%')
                    ->orwhere('seller_sku', 'like', '%'.$keyword.'%');
            });
        }
		
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $Lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $records["data"] = [];
		
        foreach ( $Lists as $list){
            $records["data"][] = array(
                array_get($accounts,$list['seller_account_id'],$list['seller_account_id']),
				$list['amazon_order_id'],
				$list['seller_order_id'],
				$list['marketplace_name'],
				$list['posted_date'],
				$list['order_item_id'],
				$list['seller_sku'],
				$list['quantity_shipped'],
				$list['type_id'],
				$list['amount'],
				$list['currency']
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }
	
	public function export(Request $request)
	{
		$datas = FinancesShipmentEvent::where('marketplace_name','Amazon.co.jp');
        $accounts = DB::connection('amazon')->table("seller_accounts")->where('primary',1)->where('mws_marketplaceid','A1VC38T7YXB528')->whereNull('deleted_at')->pluck('label','id');
        if(array_get($_REQUEST,'date_from')) {
            $datas = $datas->where('posted_date', '>=', array_get($_REQUEST,'date_from').' 00:00:00');
        }

        if(array_get($_REQUEST,'date_to')){
            $datas = $datas->where('posted_date', '<=', array_get($_REQUEST,'date_to').' 23:59:59');
        }
		
		if(array_get($_REQUEST,'seller_account_id')) {
            $datas = $datas->whereIn('seller_account_id',array_get($_REQUEST,'seller_account_id'));
        }
		
		if(array_get($_REQUEST,'keyword')){
			$keyword = array_get($_REQUEST,'keyword');
			$datas = $datas->where(function ($query) use ($keyword) {
                $query->where('amazon_order_id', 'like', '%'.$keyword.'%')
                    ->orwhere('seller_sku', 'like', '%'.$keyword.'%');
            });
        }
		$data = [];
		$data[] = ['Account','AmazonOrderID','SellerOrderID','MarketPlace','Date','OrderItemID','SellerSku','Quantity','Type','Amount','Currency'];
        $Lists =  $datas->get()->toArray();
        foreach ( $Lists as $list){
            $data[] = array(
                array_get($accounts,$list['seller_account_id'],$list['seller_account_id']),
				$list['amazon_order_id'],
				$list['seller_order_id'],
				$list['marketplace_name'],
				$list['posted_date'],
				$list['order_item_id'],
				$list['seller_sku'],
				$list['quantity_shipped'],
				$list['type_id'],
				$list['amount'],
				$list['currency']
            );
		}
		if($data){
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $data,
                    NULL, 
                    'A1' 
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="ExportFinanceShipment.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

}
