<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\SapAsinMatchSku;
use App\User;
use App\Models\TransferPlan;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class DaPlanController extends Controller
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
        $statusList = TransferPlan::selectRaw('tstatus,count(id) as count')->groupBy('tstatus')->pluck('count','tstatus');
        return view('transfer/daList',['statusList'=>$statusList]);
    }

    public function get(Request $request)
    {

        $datas = TransferPlan::leftJoin('amazon_warehouses',function($q){
			$q->on('transfer_plans.warehouse_code', '=', 'amazon_warehouses.code');
		})->where('status',5);

        if(array_get($_REQUEST,'tstatus')!==NULL && array_get($_REQUEST,'tstatus')!==''){
            $datas = $datas->whereIn('tstatus',array_get($_REQUEST,'tstatus'));
        }
        
        if(array_get($_REQUEST,'ship_start')){
            $datas = $datas->where('ship_date','>=',array_get($_REQUEST,'ship_start'));
        }
        if(array_get($_REQUEST,'ship_end')){
            $datas = $datas->where('ship_date','<=',array_get($_REQUEST,'ship_end'));
        }
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('items','like','%'.array_get($_REQUEST,'keyword').'%');
        }
        if(array_get($_REQUEST,'da_order_id')){
            $datas = $datas->where('da_order_id','like','%'.array_get($_REQUEST,'da_order_id').'%');
        }
        
        $daSkus = DB::connection('amazon')->table('da_sku_match')->pluck('da_sku','sku')->toArray();
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $shipmentList =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('id','desc')->get()->toArray();
        $records["data"] = [];

        foreach ( $shipmentList as $list){
            $items = $list['items'];
            $str = '';
            $daSkuShips = [];
            if(is_array($items)){
                foreach($items as $item){
                    $str .= '<div class="row" style="margin-bottom:5px;"><div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=100% height=100%></div>
                    <div class="col-md-10" style="text-align:left;">
                    <div class="col-md-6">DASKU : '.array_get($daSkus, $item['sku'], $item['sku']).'</div>
                    <div class="col-md-6">FNSKU : '.$item['fnsku'].'</div>
                    <div class="col-md-12">Quantity : '.intval(array_get($item,'quantity')).'</div>
                    <div class="col-md-6">Pallet Count : '.$item['broads'].'</div>
                    <div class="col-md-6">Boxes Count : '.$item['packages'].'</div>
                    <div class="col-md-6">RMS : '.array_get(\App\Models\TransferPlan::TF,$item['rms']).'</div>
                    <div class="col-md-6">RCard : '.array_get(\App\Models\TransferPlan::TF,$item['rcard']).'</div>
                    </div></div>';
                    $daSkuShips[array_get($daSkus, $item['sku'], $item['sku'])] = 
                    [
                        'image'=>$item['image'],
                        'quantity'=>0,
                        'broads'=>0,
                        'packages'=>0,
                        'locations'=>[],
                    ];
                }
            }
            $str .= '<div class="col-md-12" style="textc -align:left;"><span class="label label-primary">'.$list['reson'].'</span> <span class="label label-danger">'.$list['remark'].'</span></div>';
            $items = $list['ships'];
            $shipStr = '';
            if(is_array($items)){
                foreach($items as $item){
                    $daSkuShips[$item['sku']]['quantity'] += intval($item['quantity']);
                    $daSkuShips[$item['sku']]['broads'] += intval($item['broads']);
                    $daSkuShips[$item['sku']]['packages'] += intval($item['packages']);
                    $daSkuShips[$item['sku']]['locations'][]= $item['location'];
                }
                foreach($daSkuShips as $key=>$item){
                    $shipStr .= '<div class="row" style="margin-bottom:5px;"><div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=100% height=100%></div>
                    <div class="col-md-10" style="text-align:left;">
                    <div class="col-md-6">DASKU : '.$key.'</div>
                    <div class="col-md-6">Quantity : '.intval($item['quantity']).'</div>
                    <div class="col-md-6">Pallet Count : '.intval($item['broads']).'</div>
                    <div class="col-md-6">Boxes Count : '.intval($item['packages']).'</div>
                    <div class="col-md-12">Locations : '.implode(', ',$item['locations']).'</div>
                    </div></div>';
                }
                
            }
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['created_at'],
                $list['da_order_id'],
                array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']),
                $list['ship_date'],
                $list['reservation_date'],
                $str,
                $shipStr.'<div class="col-md-12" style="text-align:left;"><span class="label label-danger">'.$list['api_msg'].'</span></div>',
                $list['shipment_id'],
                $list['reservation_id'],
                $list['address'],
                $list['state'],
                $list['city'],
                $list['zip'],
                $list['warehouse_code'],
                array_get(TransferPlan::SHIPMETHOD,$list['ship_method']),
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $warehouses = DB::connection('amazon')->table('amazon_warehouses')->get()->keyBy('code')->toArray();
		$daSkus = DB::connection('amazon')->table('da_sku_match')->pluck('da_sku','sku')->toArray();
		$form=$items=[];
        if($id){
            $form = TransferPlan::find($id)->toArray();
            $items = $form['items'];
            $ships = $form['ships'];
        }
        return view('transfer/daEdit',['form'=>$form ,'items'=>$items ,'ships'=>$ships ,'daSkus'=>$daSkus ,'warehouses'=>$warehouses]);
    }


    public function update(Request $request)
    {
		DB::beginTransaction();
        try{
            $data = $request->all();
            $id = $data['id'];
            unset($data['id']);unset($data['_token']);
            if($id){
                $transferPlan = TransferPlan::find($id);
                if($transferPlan->tstatus==4) throw new \Exception('已发货状态无法修改!');
                TransferPlan::updateOrCreate(['id'=>$id],$data);
            }
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = "Success!";     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }
        echo json_encode($records);
    }

    public function batchUpdate(Request $request){
        $tstatus = intval(array_get($_REQUEST,"confirmStatus"));
        DB::beginTransaction();
        try{ 
            $customActionMessage='';
            foreach($_REQUEST["id"] as $plan_id){
                $transferPlan = TransferPlan::find($plan_id);
                if(empty($transferPlan)){
                    $customActionMessage.='ID:'.$plan_id.' Not Exists!</BR>';
                    continue;
                }

                if(!in_array($transferPlan->tstatus,[0,1,2,3,8])){
                    $customActionMessage.='ID:'.$plan_id.' 状态无法修改!</BR>';
                    continue;
                }

                $transferPlan->tstatus = $tstatus;
                $transferPlan->save();
                $customActionMessage.='ID:'.$plan_id.' Update Success!</BR>';
            }
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