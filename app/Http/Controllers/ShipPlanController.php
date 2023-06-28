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
	$accounts = getSellerAccount();
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
            $rows = count($items);
            $i=1;
            foreach($items as $item){
                $ships = $item['ships'];
                $shipSku = [];
                $quantity = $broads = $packages = 0;
                if(!empty($ships)){
                    foreach($ships as $ship){
                        $quantity += intval($ship['quantity']);
                        $broads += intval($ship['broads']);
                        $packages += intval($ship['packages']);
                        $shipSku[trim($ship['sku'])] = $ship['sku'];
                    }
                }
                if($i==1){
                    $records["data"][] = array(
                        '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                        $list['created_at'],
                        $list['shipment_id'],
                        array_get($accounts,$list['seller_id'],$list['seller_id']),
                        array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']),
                        array_get(TransferPlan::SHIPMETHOD,$list['ship_method']),
                        $list['ship_date'],
                        '<image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=50px height=50px>',
                        $item['sku'],
                        $item['fnsku'],
                        $item['sellersku'],
                        $item['asin'],
                        intval(array_get($item,'quantity')),
                        array_get($item,'warehouse_code'),
                        array_get(\App\Models\TransferPlan::TF,$item['rcard']),
                        array_get(\App\Models\TransferPlan::TF,$item['rms']),
                        $item['packages'],
                        $item['ship_fee'],
                        $item['weight'],
                        $item['volume'],
                        implode(',',$shipSku),
                        $quantity,
                        $broads,
                        $packages,
                        array_get($item,'sap_st0'),
                        array_get($item,'sap_tm'),
                        array_get($item,'sap_dn'),
                        $list['ship_fee'],
                        $list['weight'],
                        $list['volume'],
                        $list['da_order_id'],
                        $list['api_msg'],
                    );
                }else{
                    $records["data"][] = array(
                        '<input type="hidden" class="checkboxes" value="'.$list['id'].'">',
                        '<div style="display:none;">'.$list['created_at'].'</div>',
                        '<div style="display:none;">'.$list['shipment_id'].'</div>',
                        '<div style="display:none;">'.array_get($accounts,$list['seller_id'],$list['seller_id']).'</div>',
                        '<div style="display:none;">'.array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']).'</div>',
                        '<div style="display:none;">'.array_get(TransferPlan::SHIPMETHOD,$list['ship_method']).'</div>',
                        '<div style="display:none;">'.$list['ship_date'].'</div>',
                        '<image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=50px height=50px>',
                        $item['sku'],
                        $item['fnsku'],
                        $item['sellersku'],
                        $item['asin'],
                        intval(array_get($item,'quantity')),
                        array_get($item,'warehouse_code'),
                        array_get(\App\Models\TransferPlan::TF,$item['rcard']),
                        array_get(\App\Models\TransferPlan::TF,$item['rms']),
                        $item['packages'],
                        $item['ship_fee'],
                        $item['weight'],
                        $item['volume'],
                        implode(',',$shipSku),
                        $quantity,
                        $broads,
                        $packages,
                        array_get($item,'sap_st0'),
                        array_get($item,'sap_tm'),
                        array_get($item,'sap_dn'),
                        '<div style="display:none;">'.$list['ship_fee'].'</div>',
                        '<div style="display:none;">'.$list['weight'].'</div>',
                        '<div style="display:none;">'.$list['volume'].'</div>',
                        '<div style="display:none;">'.$list['da_order_id'].'</div>',
                        '',
                    );
                    
                }
                $i++;
            }
            
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
		$transferPlan->weight = $request->get('weight');
		$transferPlan->volume = $request->get('volume');
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
