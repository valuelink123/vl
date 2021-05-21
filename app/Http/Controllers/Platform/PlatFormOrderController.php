<?php

namespace App\Http\Controllers\Platform;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\PlatformOrder;
use App\Models\PlatformOrderItem;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use DB;
class PlatformOrderController extends Controller
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
        return view('platform/order_list');
    }

    public function get(Request $request)
    {
        $datas = PlatFormOrder::with('user')->with('items');
        
        if(array_get($_REQUEST,'platform')!==NULL && array_get($_REQUEST,'platform')!==''){
            $datas = $datas->whereIn('platform',array_get($_REQUEST,'platform'));
        }
        if(array_get($_REQUEST,'reference_no')){
            $datas = $datas->where('reference_no',array_get($_REQUEST,'reference_no'));
        }
        if(array_get($_REQUEST,'order_code')){
            $datas = $datas->where('order_code',array_get($_REQUEST,'order_code'));
        }
        if(array_get($_REQUEST,'country_code')){
            $datas = $datas->where('country_code',array_get($_REQUEST,'country_code'));
        } 
        if(array_get($_REQUEST,'name')){
            $datas = $datas->where('name',array_get($_REQUEST,'name'));
        } 
        if(array_get($_REQUEST,'email')){
            $datas = $datas->where('email',array_get($_REQUEST,'email'));
        }
        if(array_get($_REQUEST,'sync')!==NULL && array_get($_REQUEST,'sync')!==''){
            $datas = $datas->whereIn('sync',array_get($_REQUEST,'sync'));
        }
        if(array_get($_REQUEST,'sync_status')!==NULL && array_get($_REQUEST,'sync_status')!==''){
            $datas = $datas->whereIn('sync_status',array_get($_REQUEST,'sync_status'));
        } 
        if(array_get($_REQUEST,'user_id')!==NULL && array_get($_REQUEST,'user_id')!==''){
            $datas = $datas->whereIn('user_id',array_get($_REQUEST,'user_id'));
        }
        
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        
        $records["data"] = array();
		foreach ( $lists as $list){
            $items='';
            foreach($list['items'] as $item){
                $items .= $item['product_sku'].'*'.$item['quantity'].'; ';
            }
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['platform'],
                $list['reference_no'],
                $list['order_code'],
                $list['country_code'],
                $list['shipping_method'],
                $items,
                $list['name'],
                $list['email'],
                $list['user']['name'],
                array_get(PlatFormOrder::SYNC, $list['sync']),    
                array_get(PlatFormOrder::SYNC_STATUS, $list['sync_status']),    
                $list['sync_message'],
                $list['created_at'],
				$list['updated_at'],
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $form =  PlatFormOrder::where('id',$id)->with('items')->first()->toArray();
        if(empty($form)) die('不存在!');    
        return view('platform/order_edit',['form'=>$form]);
    }

    public function create()
    {  
        return view('platform/order_edit',['form'=>[]]);
    }
	
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
            $data = $id?(PlatFormOrder::findOrFail($id)):(new PlatFormOrder);
            $fileds = array(
                'reference_no','platform','order_desc','shipping_method','fba_shipment_id','fba_shipment_id_create_time','country_code','province','city','address1','address2','address3','zipcode','doorplate','company','name','cell_phone','phone','email','verify','is_shipping_method_not_allow_update','is_signature','is_insurance','insurance_value','is_change_label','age_detection','LiftGate'
            );
            foreach($fileds as $filed){
                $data->{$filed} = $request->get($filed);
            }
            
            
            $data->user_id = Auth::user()->id;
            if($data->order_code) $data->sync = 1;
            $data->sync_status=0;
            $data->sync_message=NULL;
            $data->save();
            $items = $request->get('items');
            if($items){
                PlatFormOrderItem::where('platform_order_id',$data->id)->delete();
                $itemData = [];
                foreach ($items as $item) {
                    $itemData[] = new PlatFormOrderItem($item);
                }
                $data->items()->saveMany($itemData);
            }
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = "更新成功!";
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }
        echo json_encode($records);
    }


    public function batchUpdate(Request $request){
        $status = intval($request->get('confirmStatus'));
        DB::beginTransaction();
        try{ 
            $customActionMessage='';
            if($status == -1){
                PlatFormOrder::whereIn('id',$request->get('id'))
                ->whereNotNull('order_code')->update(['sync'=>$status,'sync_status'=>0,'sync_message'=>NULL]);
            }else{
                $status = ($status ==0)?0:-2;
                PlatFormOrder::whereIn('id',$request->get('id'))
                ->whereIn('sync_status',[-2,-1])->update(['sync_status'=>$status,'sync_message'=>NULL]);
            }
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = 'Success';     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);   

    }
}