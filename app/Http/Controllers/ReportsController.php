<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\FbaAmazonFulfilledInventoryReport;
use App\Models\FbaDailyInventoryHistoryReport;
use App\Models\FbaInventoryAdjustmentsReport;
use App\Models\FbaMonthlyInventoryHistoryReport;
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
        $datas = DB::connection('amazon')->table($type);
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
        if(array_get($_REQUEST,'reason')){
            $datas = $datas->whereIn('reason',array_get(FbaInventoryAdjustmentsReport::REASONMATCH,array_get($_REQUEST,'reason'),array_get($_REQUEST,'reason')));
            $exportFileName.=implode(',',array_get($_REQUEST,'reason')).'_';
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
                $datas->offset(intval(array_get($_REQUEST,'offset')))->limit(intval(array_get($_REQUEST,'limit')));
                $exportFileName.='Page'.intval(intval(array_get($_REQUEST,'offset'))/intval(array_get($_REQUEST,'limit'))+1).'_';
            }
            if(!$exportFileName) $exportFileName = 'All_';
            $exportFileName.=date('YmdHis').'.xlsx';
            $lists =  $datas->orderBy('id','desc')->get()->toArray();
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
                    'Account','Adjusted Date','Transaction Item Id','SellerSku','Fnsku','Fulfillment Center Id','Quantity','Reason','Disposition','Reconciled','Unreconciled','Updated At'
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
                    array_get(FbaInventoryAdjustmentsReport::DISPOSITION,$list['disposition']),
                    (string)$list['reconciled'],
                    (string)$list['unreconciled'],
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