<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\AmazonShipmentItem;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class ReimController extends Controller
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
		$accounts = DB::connection('amazon')->table('seller_accounts')->where('primary',1)->whereNull('deleted_at')->pluck('label','mws_seller_id');
        return view('reim/list',['accounts'=>$accounts, 'users'=>getUsers('sap_seller')]);

    }

    public function get(Request $request)
    {
        $datas = AmazonShipmentItem::selectRaw('amazon_shipment_items.*, seller_accounts.label,asin.bg,asin.bu,asin.sap_seller_id,asin.sku,amazon_reimbursements.case_id as r_case_id,amazon_reimbursements.currency_unit,amazon_reimbursements.amount_total,amazon_reimbursements.quantity_reimbursed_total,amazon_shipments.shipment_status')
		->leftJoin('amazon_shipments',function($q){
            $q->on('amazon_shipment_items.seller_id', '=', 'amazon_shipments.seller_id')->on(			'amazon_shipment_items.shipment_id', '=', 'amazon_shipments.shipment_id');
        })
		->leftJoin('seller_accounts',function($q){
            $q->on('amazon_shipment_items.seller_id', '=', 'seller_accounts.mws_seller_id');
        })
        ->leftJoin('amazon_reimbursements',function($q){
            $q->on('amazon_shipment_items.case_id', '=', 'amazon_reimbursements.case_id');
        })
        ->leftJoin(DB::raw("(select seller_sku,any_value(sku) as sku,any_value(sap_seller_id) as sap_seller_id,any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu from sap_asin_match_sku group by seller_sku) as asin"),function($q){
            $q->on('amazon_shipment_items.seller_sku', '=', 'asin.seller_sku');
        })->where('seller_accounts.primary',1)->where('amazon_shipments.shipment_status','CLOSED')->whereRaw('amazon_shipment_items.quantity_shipped>amazon_shipment_items.quantity_received');
        

		if(array_get($_REQUEST,'seller_account_id')){
            $datas = $datas->whereIn('amazon_shipment_items.seller_id',array_get($_REQUEST,'seller_account_id'));
        }
        if(array_get($_REQUEST,'sap_seller_id')){
            $datas = $datas->whereIn('asin.sap_seller_id',array_get($_REQUEST,'sap_seller_id'));
        }
        if(array_get($_REQUEST,'bg')){
            $datas = $datas->whereIn('asin.bg',array_get($_REQUEST,'bg'));
        }
        if(array_get($_REQUEST,'bu')){
            $datas = $datas->whereIn('asin.bu',array_get($_REQUEST,'bu'));
        }
        if(array_get($_REQUEST,'date_from')){
            $datas = $datas->where('amazon_shipment_items.updated_at','>=',array_get($_REQUEST,'date_from'));
        }
        if(array_get($_REQUEST,'date_to')){
            $datas = $datas->where('amazon_shipment_items.updated_at','<=',array_get($_REQUEST,'date_to'));
        }
		if(array_get($_REQUEST,'shipment_id')){
            $datas = $datas->where('amazon_shipment_items.shipment_id',array_get($_REQUEST,'shipment_id'));
        }
		
		if(array_get($_REQUEST,'case_id')){
            $datas = $datas->where('amazon_shipment_items.case_id',array_get($_REQUEST,'case_id'));
        }
        if(array_get($_REQUEST,'sku')){
            $datas = $datas->whereIn('asin.sku',explode(',',str_replace([' ','	'],'',array_get($_REQUEST,'sku'))));
        } 
		
		if(array_get($_REQUEST,'step')){
            $datas = $datas->whereIn('amazon_shipment_items.step',array_get($_REQUEST,'step'));
        }
		
        if(array_get($_REQUEST,'status')=='Pending'){
            $datas = $datas->whereNotNull('amazon_shipment_items.case_id')->whereNull('amazon_reimbursements.case_id');
        }
		
		if(array_get($_REQUEST,'status')=='Success'){
            $datas = $datas->whereNotNull('amazon_shipment_items.case_id')->whereNotNull('amazon_reimbursements.case_id');
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('amazon_shipment_items.id','desc')->get()->toArray();
        $users = getUsers('sap_seller');
        $records["data"] = array();
		foreach ( $lists as $list){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['updated_at'],
                $list['bg'].'-'.$list['bu'],
                array_get($users,$list['sap_seller_id'],$list['sap_seller_id']),
                $list['label'],
                $list['shipment_id'],
				$list['seller_sku'],
                $list['sku'],
				$list['quantity_shipped'],
				$list['quantity_received'],
				$list['quantity_shipped']-$list['quantity_received'],
				$list['shipment_status'],
				(!empty($list['pod']) && file_exists(public_path().$list['pod']))?'<a href="'.$list['pod'].'" target="_blank">'.basename($list['pod']).'</a>':$list['pod'],
				$list['case_id'],
                $list['amount_total'],
                $list['currency_unit'],
                $list['quantity_reimbursed_total'],
                empty($list['r_case_id'])?(empty($list['case_id'])?'':'Pending'):'Success',
				array_get(AmazonShipmentItem::STATUS,$list['step'],$list['step']),
				$list['remark'],
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }
	
	public function edit(Request $request,$id)
    {
        return view('reim/edit',['id'=>$id]);
    }

    public function batchUpdate(Request $request){
        DB::beginTransaction();
        try{ 
            $customActionMessage='';
			$file = $request->file('file');
			$updateData=[];
			if ($file) {
				if ($file->isValid()) {
					$originalName = $file->getClientOriginalName();
					$ext = $file->getClientOriginalExtension();
					$type = $file->getClientMimeType();
					$realPath = $file->getRealPath();
					$newname = date('Y-m-d-H-i-S') . '-' . uniqid() . '.' . $ext;
					$newpath = '/uploads/exceptionUpload/' . date('Ymd') . '/';
					$inputFileName = public_path() . $newpath . $newname;
					$bool = $file->move(public_path() . $newpath, $newname);
					$updateData['pod']=$newpath . $newname;
				}
			}
			if($request->get('isa')) $updateData['pod']=$request->get('isa');
			$updateData['step']=intval($request->get('step'));
			if($request->get('case_id')) $updateData['case_id']=$request->get('case_id');
			if($request->get('remark')) $updateData['remark']=$request->get('remark');
			$ids = explode(',',$request->get('ids'));
            AmazonShipmentItem::whereIn('id',$ids)->update($updateData);
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);   

    }

}