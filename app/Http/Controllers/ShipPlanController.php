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
class ShipPlanController extends Controller
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
        return view('transfer/shipList',['statusList'=>$statusList]);
    }

    public function get(Request $request)
    {

        $datas = TransferPlan::where('status',6)->where('tstatus','>',4);

        if(array_get($_REQUEST,'tstatus')!==NULL && array_get($_REQUEST,'tstatus')!==''){
            $datas = $datas->whereIn('tstatus',array_get($_REQUEST,'tstatus'));
        }
        
        if(array_get($_REQUEST,'created_start')){
            $datas = $datas->where('created_at','>=',array_get($_REQUEST,'created_start'));
        }
        if(array_get($_REQUEST,'created_end')){
            $datas = $datas->where('created_at','<=',array_get($_REQUEST,'created_end'));
        }
        if(array_get($_REQUEST,'keyword')){
            $keyword = array_get($_REQUEST,'keyword');
            $datas = $datas->where(function ($query) use ($keyword) {
                $query->where('shipment_id', 'like', '%'.$keyword.'%')
                    ->orwhere('da_order_id', 'like', '%'.$keyword.'%')
                    ->orwhereHas('items',function($itemQuery)use($keyword){
                        $itemQuery->where('asin','like','%'.$keyword.'%')
                            ->orWhere('sku','like','%'.$keyword.'%')
                            ->orWhere('sellersku','like','%'.$keyword.'%')
                            ->orWhere('fnsku','like','%'.$keyword.'%')
                            ->orwhereHas('ships',function($shipQuery)use($keyword){
                                $shipQuery->where('sku','like','%'.$keyword.'%');
                            });
                    });
            });
        }
        if(array_get($_REQUEST,'shipment_id')){
            $datas = $datas->where('shipment_id','like','%'.array_get($_REQUEST,'shipment_id').'%');
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
            $str = $strShip = '';
            $total_broads = $total_packages = $actual_broads = $actual_packages = 0;
            if(is_array($items)){
                foreach($items as $item){
                    $str .= '<div class="row" style="margin-bottom:10px;"><div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=50px height=50px></div>
                    <div class="col-md-10" style="text-align:left;">
                    <div class="col-md-6">SKU : '.$item['sku'].'</div>
                    <div class="col-md-6">FNSKU : '.$item['fnsku'].'</div>
                    <div class="col-md-6">Asin : '.$item['asin'].'</div>
                    <div class="col-md-6">SellerSku : '.$item['sellersku'].'</div>
                    <div class="col-md-6">仓库 : '.array_get($item,'warehouse_code').'</div>
                    <div class="col-md-6">数量 : '.intval(array_get($item,'quantity')).'</div>

                    <div class="col-md-6">预计卡板数 : '.$item['broads'].'</div>
                    <div class="col-md-6">预计箱数 : '.$item['packages'].'</div>
                    <div class="col-md-6">RMS : '.array_get(\App\Models\TransferPlan::TF,$item['rms']).'</div>
                    <div class="col-md-6">抽卡 : '.array_get(\App\Models\TransferPlan::TF,$item['rcard']).'</div>
                    <div class="col-md-12">预计运费:'.$item['ship_fee'].'</div>
                    </div></div>';
                    $ships = $item['ships'];
                    if(!empty($ships)){
                        $shipSku = '';
                        $locations= [];
                        $quantity = $broads = $packages = 0;
                        foreach($ships as $ship){
                            $quantity += intval($ship['quantity']);
                            $broads += intval($ship['broads']);
                            $packages += intval($ship['packages']);
                            $locations[] = $ship['location'];
                            $shipSku = $ship['sku'];
                            $actual_broads += intval($ship['broads']);
                            $actual_packages += intval($ship['packages']);
                        }
                        
                        $strShip.='<div class="row" style="margin-bottom:10px;text-align:left;">
                        <div class="col-md-12">DASKU : '.$shipSku.'</div>
                        <div class="col-md-6">Quantity : '.intval($quantity).'</div>
                        <div class="col-md-6">Warehouse : '.array_get($item,'warehouse_code').'</div>
                        <div class="col-md-6">Pallet Count : '.intval($broads).'</div>
                        <div class="col-md-6">Boxes Count : '.intval($packages).'</div>
                        <div class="col-md-12">Locations : '.implode(' , ',$locations).'</div>
                        </div>';
                    }
                    $total_broads += intval($item['broads']);
                    $total_packages += intval($item['packages']);
                }
            }
            $str .= '<div class="col-md-12" style="text-align:left;"><span class="label label-primary">'.$list['reson'].'</span> <span class="label label-danger">'.$list['remark'].'</span></div>';
            
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['created_at'],
                $list['shipment_id'],
                array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']),
                array_get(TransferPlan::SHIPMETHOD,$list['ship_method']),
                $list['ship_date'],
                $str,
                $strShip.'<div class="col-md-12" style="text-align:left;"><span class="label label-danger">'.$list['api_msg'].'</span></div>',
                $total_broads,
                $total_packages,
                $actual_broads,
                $actual_packages,
                $list['ship_fee'],
                $list['sap_tm'],
                $list['sap_dn'],
                $list['sap_st0'],
                $list['da_order_id'],
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $warehouses = json_decode(json_encode(DB::connection('amazon')->table('amazon_warehouses')->get()->keyBy('code')->toArray()),true);
		$form=$items=[];
        if($id){
            $form = TransferPlan::find($id)->toArray();
            $items = $form['items'];
        }
        return view('transfer/shipEdit',['form'=>$form ,'items'=>$items,'warehouses'=>$warehouses ]);
    }


    public function update(Request $request)
    {
		DB::beginTransaction();
        try{
            $id = $request->get('id');
            if($id){
                $transferPlan = TransferPlan::findOrFail($id);
                if(!in_array($transferPlan->tstatus,[5,6,8])) throw new \Exception('This Status Can Not Update!');
                $transferPlan->tstatus = $request->get('tstatus');
                $transferPlan->ship_fee = $request->get('ship_fee');
                $transferPlan->save();    
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
                    throw new \Exception('ID:'.$plan_id.' 不存在!');
                }
                if(!in_array($transferPlan->tstatus,[5,6,8])){
                    throw new \Exception('ID:'.$plan_id.' 状态无法修改!');
                }

                $transferPlan->tstatus = $tstatus;
                $transferPlan->save();
                $customActionMessage.='ID:'.$plan_id.' 更新成功!</BR>';
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