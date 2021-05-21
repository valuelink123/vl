<?php

namespace App\Http\Controllers\Platform;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\PlatFormShip;
use App\Models\PlatformOrder;
use App\Models\GuCangShippingMethod;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use DB;
class PlatformShipController extends Controller
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
        return view('platform/ship_list');
    }

    public function get(Request $request)
    {
        $datas = PlatFormShip::with('user');
        

        if(array_get($_REQUEST,'platform')!==NULL && array_get($_REQUEST,'platform')!==''){
            $datas = $datas->whereIn('platform',array_get($_REQUEST,'platform'));
        }
        if(array_get($_REQUEST,'country_code')){
            $datas = $datas->where('country_code',array_get($_REQUEST,'country_code'));
        }
        if(array_get($_REQUEST,'platform_shipping_method')){
            $datas = $datas->where('platform_shipping_method',array_get($_REQUEST,'platform_shipping_method'));
        }
        if(array_get($_REQUEST,'warehouse_code')){
            $datas = $datas->where('warehouse_code',array_get($_REQUEST,'warehouse_code'));
        } 
        if(array_get($_REQUEST,'shipping_method')){
            $datas = $datas->where('shipping_method',array_get($_REQUEST,'shipping_method'));
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
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['platform'],
                $list['country_code'],
                $list['platform_shipping_method'],
                $list['warehouse_code'],
                $list['shipping_method'],
                $list['user']['name'],
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
        $form =  PlatFormShip::find($id)->toArray();
        if(empty($form)) die('不存在!');    
        return view('platform/ship_edit',['form'=>$form]);
    }

    public function create()
    {  
        return view('platform/ship_edit',['form'=>[]]);
    }
	
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
            $data = $id?(PlatFormShip::findOrFail($id)):(new PlatFormShip);
            $fileds = array(
                'platform','country_code','platform_shipping_method'
            );
            foreach($fileds as $filed){
                $data->{$filed} = $request->get($filed);
            }
            $ws = $request->get('shipping_method');
            $ws = explode('|',$ws,2);
            $data->warehouse_code = $ws[0];
            $data->shipping_method = $ws[1];
            $data->user_id = Auth::user()->id;
            $data->save();
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
            if($status == -1)  PlatFormShip::whereIn('id',$request->get('id'))->delete();
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