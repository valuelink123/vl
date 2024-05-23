<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\FbaAmazonFulfilledInventoryReport;
use App\Models\FbaDailyInventoryHistoryReport;
use App\Models\FbaInventoryAdjustmentsReport;
use App\Models\FbaMonthlyInventoryHistoryReport;
use App\Models\FbaManageInventory;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ReportsController extends Controller
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

    public function index(Request $request)
    {
        if(!Auth::user()->can(['reports-show'])) die('Permission denied -- reports-show');
        $accounts_data = DB::connection('amazon')->table('seller_accounts')->where('primary',1)->whereNull('deleted_at')->pluck('label','id');
        $type = $request->input('type');
        if(!$type) $type = 'fba_amazon_fulfilled_inventory_report';
        return view('reports/'.$type,['accounts_data'=>$accounts_data]);
    }

    public function get(Request $request)
    {
        if(!Auth::user()->can(['reports-show'])) die('Permission denied -- reports-show');
        set_time_limit(0);
        $accounts_data = DB::connection('amazon')->table('seller_accounts')->where('primary',1)->whereNull('deleted_at')->pluck('label','id');
        $type = $request->input('type');
        if(!$type) $type = 'fba_amazon_fulfilled_inventory_report';
        $exportFileName = '';
        $table = $type;
        if($table =='fba_manage_inventory'){
            //$table='view_fba_manage_inventory';
        }

        $datas = DB::connection('amazon')->table($table);
        if(array_get($_REQUEST,'seller_account_id')){
            $datas = $datas->whereIn('seller_account_id',array_get($_REQUEST,'seller_account_id'));
            $exportFileName.='Account'.implode(',',array_get($_REQUEST,'seller_account_id')).'_';
        }
        if(array_get($_REQUEST,'asin')){
            $datas = $datas->whereIn('asin',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'asin'))));
            $exportFileName.=str_replace([' ','	'],'',array_get($_REQUEST,'asin')).'_';
        } 
        if(array_get($_REQUEST,'seller_sku')){
            $datas = $datas->whereIn('seller_sku',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'seller_sku'))));
            $exportFileName.=str_replace([' ','	'],'',array_get($_REQUEST,'seller_sku')).'_';
        } 
        if(array_get($_REQUEST,'fnsku')){
            $datas = $datas->whereIn('fnsku',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'fnsku'))));
            $exportFileName.=str_replace([' ','	'],'',array_get($_REQUEST,'fnsku')).'_';
        }  
        if(array_get($_REQUEST,'fulfillment_center_id')){
            $datas = $datas->whereIn('fulfillment_center_id',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'fulfillment_center_id'))));
            $exportFileName.=str_replace([' ','	'],'',array_get($_REQUEST,'fulfillment_center_id')).'_';
        } 
        if(array_get($_REQUEST,'adjusted_date_from')){
            $datas = $datas->where('adjusted_date','>=',array_get($_REQUEST,'adjusted_date_from'));
            $exportFileName.=array_get($_REQUEST,'adjusted_date_from').'_';
        }
        if(array_get($_REQUEST,'adjusted_date_to')){
            $datas = $datas->where('adjusted_date','<=',array_get($_REQUEST,'adjusted_date_to'));
            $exportFileName.=array_get($_REQUEST,'adjusted_date_to').'_';
        }
        if(array_get($_REQUEST,'snapshot_date_from')){
            $datas = $datas->where('snapshot_date','>=',array_get($_REQUEST,'snapshot_date_from'));
            $exportFileName.=array_get($_REQUEST,'snapshot_date_from').'_';
        }
        if(array_get($_REQUEST,'snapshot_date_to')){
            $datas = $datas->where('snapshot_date','<=',array_get($_REQUEST,'snapshot_date_to'));
            $exportFileName.=array_get($_REQUEST,'snapshot_date_to').'_';
        }
        //FBA Smazon Fulfilled Inventory Report功能的搜索时间
		if(array_get($_REQUEST,'date_from')){
			$datas = $datas->where('updated_at','>=',array_get($_REQUEST,'date_from').' 00:00:00');
			$exportFileName.=array_get($_REQUEST,'date_from').'_';
		}
		if(array_get($_REQUEST,'date_to')){
			$datas = $datas->where('updated_at','<=',array_get($_REQUEST,'date_to').' 23:59:59');
			$exportFileName.=array_get($_REQUEST,'date_to').'_';
		}
        if(array_get($_REQUEST,'posted_date_from')){
            $datas = $datas->where('posted_date','>=',array_get($_REQUEST,'posted_date_from'));
            $exportFileName.=array_get($_REQUEST,'posted_date_from').'_';
        }
        if(array_get($_REQUEST,'posted_date_to')){
            $datas = $datas->where('posted_date','<=',array_get($_REQUEST,'posted_date_to'));
            $exportFileName.=array_get($_REQUEST,'posted_date_to').'_';
        }
        if(array_get($_REQUEST,'deposit_date_from')){
            $datas = $datas->where('deposit_date','>=',array_get($_REQUEST,'deposit_date_from'));
            $exportFileName.=array_get($_REQUEST,'deposit_date_from').'_';
        }
        if(array_get($_REQUEST,'deposit_date_to')){
            $datas = $datas->where('deposit_date','<=',array_get($_REQUEST,'deposit_date_to'));
            $exportFileName.=array_get($_REQUEST,'deposit_date_to').'_';
        }
        if(array_get($_REQUEST,'month_from')){
            $datas = $datas->where('month','>=',array_get($_REQUEST,'month_from'));
            $exportFileName.=array_get($_REQUEST,'month_from').'_';
        }
        if(array_get($_REQUEST,'month_to')){
            $datas = $datas->where('month','<=',array_get($_REQUEST,'month_to'));
            $exportFileName.=array_get($_REQUEST,'month_to').'_';
        }
        if(array_get($_REQUEST,'transaction_item_id')){
            $datas = $datas->where('transaction_item_id',array_get($_REQUEST,'transaction_item_id'));
            $exportFileName.=array_get($_REQUEST,'transaction_item_id').'_';
        }
        if(array_get($_REQUEST,'warehouse_condition_code')){
            $datas = $datas->where('warehouse_condition_code',array_get($_REQUEST,'warehouse_condition_code'));
            $exportFileName.=array_get($_REQUEST,'warehouse_condition_code').'_';
        }
        if(array_get($_REQUEST,'mfn_listing_exists')!==NULL && array_get($_REQUEST,'mfn_listing_exists')!==''){
            $datas = $datas->where('mfn_listing_exists',array_get($_REQUEST,'mfn_listing_exists'));
            $exportFileName.='MFN'.array_get($_REQUEST,'mfn_listing_exists').'_';
        }
        if(array_get($_REQUEST,'afn_listing_exists')!==NULL && array_get($_REQUEST,'afn_listing_exists')!==''){
            $datas = $datas->where('afn_listing_exists',array_get($_REQUEST,'afn_listing_exists'));
            $exportFileName.='AFN'.array_get($_REQUEST,'afn_listing_exists').'_';
        }

        if(array_get($_REQUEST,'fba_inventory_adjustments_report_state')){
            if(array_get($_REQUEST,'fba_inventory_adjustments_report_state')=='SELLABLE'){
                $datas = $datas->where('disposition','SELLABLE');
            }else{
                $datas = $datas->where('disposition','<>','SELLABLE');
            }
            $exportFileName.=array_get($_REQUEST,'fba_inventory_adjustments_report_state').'_';
        }

        if(array_get($_REQUEST,'reason')){
            $reason = [];
            foreach(array_get($_REQUEST,'reason') as $val){
                $reason = array_merge($reason,array_get(FbaInventoryAdjustmentsReport::REASONMATCH,$val,[]));
            }
            $datas = $datas->whereIn('reason',$reason);
            $exportFileName.=implode(',',$reason).'_';
        }
        if(array_get($_REQUEST,'disposition')){
            $datas = $datas->whereIn('disposition',array_get($_REQUEST,'disposition'));
            $exportFileName.=implode(',',array_get($_REQUEST,'disposition')).'_';
        }
        if(array_get($_REQUEST,'id')){
            $datas = $datas->whereIn('id',explode(',',array_get($_REQUEST,'id')));
            $exportFileName.=array_get($_REQUEST,'id').'_';
        }
        $action = $request->input('action');
        $records = array();
        $records["data"] = array();

        if($action == 'export'){
            if(array_get($_REQUEST,'limit')){
                $datas = $datas->offset(intval(array_get($_REQUEST,'offset')))->limit(intval(array_get($_REQUEST,'limit')));
                $exportFileName.='Page'.intval(intval(array_get($_REQUEST,'offset'))/intval(array_get($_REQUEST,'limit'))+1).'_';
            }
	
            if(!$exportFileName) $exportFileName = 'All_';
            $exportFileName.=date('YmdHis').'.xlsx';
			
            $lists =  $datas->get();
            $lists = json_decode(json_encode($lists), true);
			
            $arrayData = array();
            if($type  == 'fba_daily_inventory_history_report'){
                $records["data"][] = [
                    'Account','Snapshot Date','SellerSku','Fnsku','Country','Fulfillment Center Id','Disposition','Quantity','Updated At'
                ];
            }elseif($type  == 'fba_monthly_inventory_history_report'){
                $records["data"][] = [
                    'Account','Month','SellerSku','Fnsku','Country','Fulfillment Center Id','Disposition','Average Quantity','End Quantity','Updated At'
                ];
            }elseif($type  == 'fba_inventory_adjustments_report'){
                $records["data"][] = [
                    'Account','Adjusted Date','Transaction Item Id','SellerSku','Fnsku','Fulfillment Center Id','Quantity','Reason','State','Disposition','Reconciled','Unreconciled','Updated At'
                ];
            }elseif($type  == 'fba_manage_inventory'){
                $records["data"][] = [
                    'Account','Asin','SellerSku','Fnsku','Condition','MFN','MFN Fulfillable','AFN','AFN Warehouse','AFN Fulfillable','AFN Reserved','AFN Unsellable',
                    'Per Unit Volume','AFN Total','AFN Inbound Working','AFN Inbound Shipped','AFN Inbound Receiving','AFN Researching','AFN Reserved Future','AFN Future Buyable','Updated At'
                ];
            }elseif($type  == 'amazon_settlements'){
                $records["data"][] = [
                    'Account','Settlement Id','Start Date','End Date','Deposit Date','Total Amount','Currency','Updated At'
                ];
            }elseif($type  == 'amazon_settlement_details'){
                $records["data"][] = [
                    'Account','Settlement Id','Transaction Type','Order Id','Merchant Order Id','Adjustment Id','Shipment Id','Marketplace Name','Shipment Fee Type',
                    'Shipment Fee Amount','Order Fee Type','Order Fee Amount','Fulfillment Id','Posted Date','Order Item Code','Merchant Order Item Id','Merchant Adjustment Item Id','Sku','Quantity Purchased','Price Type','Price Amount','Item Related Fee Type','Item Related Fee Amount','Misc Fee Amount','Other Fee Amount','Other Fee Reason Description','Promotion Id','Promotion Type','Promotion Amount','Direct Payment Type','Direct Payment Amount','Other Amount','Currency','Updated At'
                ];
            }else{
                $records["data"][] = [
                    'Account','Asin','SellerSku','Fnsku','Condition Type','Warehouse Condition','Quantity','Updated At'
                ];
            } 
        }else{
            $iTotalRecords = $datas->count();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            $iDisplayStart = intval($_REQUEST['start']);
            $sEcho = intval($_REQUEST['draw']);
            
            $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('id','desc')->get()->toArray();
            $lists = json_decode(json_encode($lists), true);
        }

		foreach ( $lists as $list){
            $checkbox = '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  /><span></span></label>';
            if($type  == 'fba_daily_inventory_history_report'){
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['snapshot_date'],
                    $list['seller_sku'],
                    $list['fnsku'],
                    $list['country'],
                    $list['fulfillment_center_id'],
                    array_get(FbaDailyInventoryHistoryReport::DISPOSITION,$list['disposition']),
                    (string)$list['quantity'],
                    $list['updated_at'],
                );
            }elseif($type  == 'fba_monthly_inventory_history_report'){
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['month'],
                    $list['seller_sku'],
                    $list['fnsku'],
                    $list['country'],
                    $list['fulfillment_center_id'],
                    array_get(FbaMonthlyInventoryHistoryReport::DISPOSITION,$list['disposition']),
                    (string)$list['average_quantity'],
                    (string)$list['end_quantity'],
                    $list['updated_at'],
                );
            }elseif($type  == 'fba_inventory_adjustments_report'){
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['adjusted_date'],
                    $list['transaction_item_id'],
                    $list['seller_sku'],
                    $list['fnsku'],
                    $list['fulfillment_center_id'],
                    (string)$list['quantity'],
                    array_get(FbaInventoryAdjustmentsReport::REASON,$list['reason']),
                    array_get(FbaInventoryAdjustmentsReport::STATE,$list['disposition'],'不可售'),
                    array_get(FbaInventoryAdjustmentsReport::DISPOSITION,$list['disposition']),
                    (string)$list['reconciled'],
                    (string)$list['unreconciled'],
                    $list['updated_at'],
                );
            }elseif($type  == 'fba_manage_inventory'){
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['asin'],
                    $list['seller_sku'],
                    $list['fnsku'],
                    $list['condition'],
                    array_get(FbaManageInventory::LISTINGEXISTS,$list['mfn_listing_exists']),
                    (string)$list['mfn_fulfillable_quantity'],
                    array_get(FbaManageInventory::LISTINGEXISTS,$list['afn_listing_exists']),
                    (string)$list['afn_warehouse_quantity'],
                    (string)$list['afn_fulfillable_quantity'],
                    (string)$list['afn_reserved_quantity'],
                    (string)$list['afn_unsellable_quantity'],
                    (string)$list['per_unit_volume'],
                    (string)$list['afn_total_quantity'],
                    (string)$list['afn_inbound_working_quantity'],
                    (string)$list['afn_inbound_shipped_quantity'],
                    (string)$list['afn_inbound_receiving_quantity'],
                    (string)$list['afn_researching_quantity'],
                    (string)$list['afn_reserved_future_supply'],
                    (string)$list['afn_future_supply_buyable'],
                    $list['updated_at'],
                );
            }elseif($type  == 'amazon_settlements'){
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['settlement_id'],
                    $list['settlement_start_date'],
                    $list['settlement_end_date'],
                    $list['deposit_date'],
                    (string)$list['total_amount'],
                    $list['currency'],
                    $list['updated_at']
                );
            }elseif($type  == 'amazon_settlement_details'){
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['settlement_id'],
                    $list['transaction_type'],
                    $list['order_id'],
                    $list['merchant_order_id'],
                    $list['adjustment_id'],
                    $list['shipment_id'],
                    $list['marketplace_name'],
                    $list['shipment_fee_type'],
                    (string)$list['shipment_fee_amount'],
                    $list['order_fee_type'],
                    (string)$list['order_fee_amount'],
                    $list['fulfillment_id'],
                    $list['posted_date'],
                    $list['order_item_code'],
                    $list['merchant_order_item_id'],
                    $list['merchant_adjustment_item_id'],
                    $list['sku'],
                    (string)$list['quantity_purchased'],
                    $list['price_type'],
                    (string)$list['price_amount'],
                    $list['item_related_fee_type'],
                    (string)$list['item_related_fee_amount'],
                    (string)$list['misc_fee_amount'],
                    (string)$list['other_fee_amount'],
                    $list['other_fee_reason_description'],
                    $list['promotion_id'],
                    $list['promotion_type'],
                    (string)$list['promotion_amount'],
                    $list['direct_payment_type'],
                    (string)$list['direct_payment_amount'],
                    (string)$list['other_amount'],
                    $list['currency'],
                    $list['updated_at'],
                );
            }else{
                $line = array(
                    array_get($accounts_data,$list['seller_account_id']),
                    $list['asin'],
                    $list['seller_sku'],
                    $list['fnsku'],
                    $list['condition_type'],
                    array_get(FbaAmazonFulfilledInventoryReport::STATUS,$list['warehouse_condition_code']),
                    (string)$list['quantity_available'],
                    $list['updated_at'],
                );
            } 
            if($action != 'export') array_unshift($line,$checkbox);
            $records["data"][] = $line;
        }
        

        if($action == 'export'){
            if($records["data"]){
                $spreadsheet = new Spreadsheet();
                $spreadsheet->getActiveSheet()->fromArray($records["data"],NULL,'A1');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$exportFileName.'"');
                header('Cache-Control: max-age=0');
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }
            die();
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }
}
