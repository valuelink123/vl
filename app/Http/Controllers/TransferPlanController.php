<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\SapAsinMatchSku;
use App\User;
use App\Models\TransferPlan;
use App\Models\TransferPlanItem;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Exception;
use Illuminate\Http\Response;
class TransferPlanController extends Controller
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
        $statusList = TransferPlan::selectRaw('status,count(id) as count')->groupBy('status')->pluck('count','status');
        return view('transfer/planList',['statusList'=>$statusList]);
    }

    public function get(Request $request)
    {

        $datas = TransferPlan::orderBy('id','desc');
        if (Auth::user()->seller_rules) {
			$where = '1=1 '.getSellerRules(Auth::user()->seller_rules,'bg','bu');
			$datas = $datas->whereRaw($where);
		} elseif (Auth::user()->sap_seller_id) {
            $datas = $datas->where('sap_seller_id',Auth::user()->sap_seller_id);
		} 
        if(array_get($_REQUEST,'marketplace_id')){
            $datas = $datas->whereIn('marketplace_id',array_get($_REQUEST,'marketplace_id'));
        }
        if(array_get($_REQUEST,'bg')){
            $datas = $datas->whereIn('bg',array_get($_REQUEST,'bg'));
        }
        if(array_get($_REQUEST,'bu')){
            $datas = $datas->whereIn('bu',array_get($_REQUEST,'bu'));
        }
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->whereIn('status',array_get($_REQUEST,'status'));
        }
        if(array_get($_REQUEST,'tstatus')!==NULL && array_get($_REQUEST,'tstatus')!==''){
            $datas = $datas->whereIn('tstatus',array_get($_REQUEST,'tstatus'));
        }
        if(array_get($_REQUEST,'sap_seller_id')){
            $datas = $datas->whereIn('sap_seller_id',array_get($_REQUEST,'sap_seller_id'));
        }
        if(array_get($_REQUEST,'seller_id')){
            $datas = $datas->whereIn('seller_id',array_get($_REQUEST,'seller_id'));
        }
        if(array_get($_REQUEST,'created_start')){
            $datas = $datas->where('created_at','>=',array_get($_REQUEST,'created_start'));
        }
        if(array_get($_REQUEST,'created_end')){
            $datas = $datas->where('created_at','<=',array_get($_REQUEST,'created_end'));
        }
        if(array_get($_REQUEST,'received_start')){
            $datas = $datas->where('received_date','>=',array_get($_REQUEST,'received_start'));
        }
        if(array_get($_REQUEST,'received_end')){
            $datas = $datas->where('received_date','<=',array_get($_REQUEST,'received_end'));
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
                            ->orWhere('fnsku','like','%'.$keyword.'%');
                    });
            });
        }
        $sellers = getUsers('sap_seller');
        $accounts = getSellerAccount();
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
                        array_get(array_flip(getSiteCode()),$list['marketplace_id']),
                        $list['bg'].$list['bu'].'-'.array_get($sellers,intval($list['sap_seller_id'])),
                        date('Y-m-d',strtotime($list['created_at'])),
                        array_get($accounts,$list['seller_id'],$list['seller_id']),
                        $list['shipment_id'],
                        array_get(TransferPlan::SHIPMETHOD,$list['ship_method']),
                        $list['ship_fee'],
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
                        $list['received_date'],
                        $list['ship_date'],
                        $list['reson'],
                        $list['remark'],
                        array_get(TransferPlan::STATUS,$list['status']),
                        array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']),
                        $list['in_factory_code'],
                        $list['out_factory_code'],
                        empty($list['files']) ? '<button class="uploadDataBtn btn red-sunglo" value="'.$list['id'].'">上传大货资料</button>' : '<button class="uploadDataBtn btn  green-meadow" value="'.$list['id'].'">查看大货资料</button>',
                        $list['api_msg'],
                    );
                }else{
                    $records["data"][] = array(
                        '<input type="hidden" class="checkboxes" value="'.$list['id'].'">',
                        '<div style="display:none;">'.array_get(array_flip(getSiteCode()),$list['marketplace_id']).'</div>',
                        '<div style="display:none;">'.$list['bg'].$list['bu'].'-'.array_get($sellers,intval($list['sap_seller_id'])).'</div>',
                        '<div style="display:none;">'.date('Y-m-d',strtotime($list['created_at'])).'</div>',
                        '<div style="display:none;">'.array_get($accounts,$list['seller_id'],$list['seller_id']).'</div>',
                        '<div style="display:none;">'.$list['shipment_id'].'</div>',
                        '<div style="display:none;">'.array_get(TransferPlan::SHIPMETHOD,$list['ship_method']).'</div>',
                        '<div style="display:none;">'.$list['ship_fee'].'</div>',
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
                        '<div style="display:none;">'.$list['received_date'].'</div>',
                        '<div style="display:none;">'.$list['ship_date'].'</div>',
                        '<div style="display:none;">'.$list['reson'].'</div>',
                        '<div style="display:none;">'.$list['remark'].'</div>',
                        '<div style="display:none;">'.array_get(TransferPlan::STATUS,$list['status']).'</div>',
                        '<div style="display:none;">'.array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']).'</div>',
                        '<div style="display:none;">'.$list['in_factory_code'].'</div>',
                        '<div style="display:none;">'.$list['out_factory_code'].'</div>',
                        '',
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
        $warehouses = DB::connection('amazon')->table('amazon_warehouses')->selectRaw("code,concat(city,' ',address,' ',zip) as address")->pluck('address','code');
	$factorys = SapAsinMatchSku::where('sap_factory_code','<>','')->whereNotNull('sap_factory_code')->groupBy('sap_factory_code')->select(['sap_factory_code'])->pluck('sap_factory_code')->toArray();
		$form=$items=[];
        if($id){
            $form = TransferPlan::find($id)->toArray();
            $items = $form['items'];
        }
        return view('transfer/planEdit',['form'=>$form ,'items'=>$items ,'factorys'=>$factorys ,'warehouses'=>$warehouses]);
    }

    public function getSellerSku(Request $request)
    {
        $data = [];
        $sku = $seller_id = $image = null;

        if (!empty($request['asin']) && !empty($request['marketplace_id'])) {
            $sql = "SELECT any_value(sku) as sku, seller_id, seller_sku from sap_asin_match_sku WHERE asin='" . $request['asin'] . "' AND marketplace_id='" . $request['marketplace_id'] . "' GROUP BY seller_id,seller_sku;";
            $sellersku = DB::connection('amazon')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            $accounts = getSellerAccount();
            foreach($data as $k=>$v){
                $data[$k]['label'] = array_get($accounts,$v['seller_id'],$v['seller_id']);
                $sku = $v['sku'];
            }
            $images = DB::connection('amazon')->table('asins')->where('asin',$request['asin'])->where('marketplaceid',$request['marketplace_id'])->value('images');
            $images = explode(',',$images);
            $image = array_get($images,0);
        }
        return ['seller_sku_list' => $data , 'sku' => $sku, 'image' => $image];
    }
	
    public function getUploadData(Request $request)
    {
        $id = @$request['id'];
        $cargo_data_arr = $cargo_data_arr_new = [];
        if ($id > 0) {
            $files = TransferPlan::where('id',$id)->value('files');
            if (!empty($files)) {
                $cargo_data_arr = explode(',', $files);
                if (!empty($cargo_data_arr)) {
                    foreach ($cargo_data_arr as $ck => $cv) {
                        $last = strripos($cv, '/');
                        $fileName = substr($cv, $last + 1);
                        $cargo_data_arr_new[$ck]['title'] = $fileName;
                        $cargo_data_arr_new[$ck]['url'] = $cv;
                    }
                }
            }
            return $cargo_data_arr_new;
        } else {
            return [];
        }

    }

    public function updateFiles(Request $request)
    {
        $id = $request['id'];
        if (!empty($request['files']) && !empty($id)) {
            $result = TransferPlan::where('id',$id)->update([
                'files' => $request['files']
            ]);
            if ($result > 0) {
                $r_message = ['status' => 1, 'msg' => '保存成功'];
            } else {
                $r_message = ['status' => 0, 'msg' => '保存失败'];
            }
        } else {
            $r_message = ['status' => 0, 'msg' => '缺少数据'];
        }
        return $r_message;
    }

    public function update(Request $request)
    {
	DB::beginTransaction();
        try{
            $id = $request->get('id');
	    $exShipmentId = TransferPlan::where('id','<>',intval($id))->where('shipment_id',$request->get('shipment_id'))->value('shipment_id');
	    if($exShipmentId) throw new \Exception('ShipmentId 已存在!');	
            $transferPlan = $id?(TransferPlan::findOrFail($id)):(new TransferPlan);
            if(!in_array($transferPlan->status,array_keys(getTransferForRole()))) throw new \Exception('无权限修改!');
            $transferPlan->remark = $request->get('remark');
            $transferPlan->status = $request->get('status');
            $transferPlan->api_msg = null;
	    
            $items = [];
            if(($id && $transferPlan->sap_seller_id == \Auth::user()->sap_seller_id && $transferPlan->status<=1) || !$id){
                $items = $request->get('items');
                $currencyRate = DB::connection('amazon')->table('currency_rates')->where('currency','USD')->value('rate');
                $warehousesFee = DB::connection('amazon')->table('amazon_warehouses')->pluck('fee','code')->toArray();
                if(!$currencyRate) throw new \Exception('缺失汇率数据!');
                $broads = $ship_fee = $packages = $totalWeight = $totalVolume = 0;
                foreach($items as $key=>$item){
                    
                    $sizeInfo = DB::connection('amazon')->table('sku_size')->where('sku',$item['sku'])->first();
                    if(empty($sizeInfo)){
                        throw new \Exception($item['sku'].'缺失基础数据!');
                    }
                    $items[$key]['broads'] = ceil(($sizeInfo->volume)/167/1.5);
		    $items[$key]['weight'] = round(($sizeInfo->weight)*intval($item['packages']),2);
		    $items[$key]['volume'] = round(($sizeInfo->length)*($sizeInfo->width)*($sizeInfo->height)/6000*intval($item['packages']),2);
                    $items[$key]['ship_fee'] =  0;
                    if($request->get('ship_method')=='other'){
                        $warehouseFee = round(array_get($warehousesFee,$item['warehouse_code']),2);
                        if(!$warehouseFee) throw new \Exception($item['warehouse_code'].'缺失运费数据!');
                        $items[$key]['ship_fee'] += round($items[$key]['broads']*$warehouseFee,2);
                    }
                    if($item['rcard']=='1'){
                        $items[$key]['ship_fee'] += 0.5*intval($item['quantity']);
                    }
                    $items[$key]['ship_fee'] += 0.35*intval($item['quantity']);
                    $items[$key]['ship_fee'] += $items[$key]['broads']*15;
                    $items[$key]['ship_fee'] += (($request->get('ship_method')=='other')?1.2:1.8)*intval($item['packages']);
                    $items[$key]['ship_fee'] += (0.5*intval($item['quantity'])<8*intval($item['packages']))?0.5*intval($item['quantity']):8*intval($item['packages']);
                    $items[$key]['ship_fee'] = round($items[$key]['ship_fee']*$currencyRate,2);

                    $broads+=$items[$key]['broads'];
                    $packages+=intval($item['packages']);
                    $ship_fee+=$items[$key]['ship_fee'];
		    $totalWeight+=$items[$key]['weight'];
		    $totalVolume+=$items[$key]['volume'];
                }
                $transferPlan->sap_seller_id = Auth::user()->sap_seller_id;
                $transferPlan->bg = Auth::user()->ubg;
                $transferPlan->bu = Auth::user()->ubu;
                $transferPlan->seller_id = $request->get('seller_id');
                $transferPlan->marketplace_id = $request->get('marketplace_id');
                $transferPlan->in_factory_code = $request->get('in_factory_code');
                $transferPlan->out_factory_code = $request->get('out_factory_code');
                $transferPlan->received_date = $request->get('received_date');
                $transferPlan->shipment_id = $request->get('shipment_id');
                $transferPlan->reservation_id = $request->get('reservation_id');
                $transferPlan->ship_method = $request->get('ship_method');
                $transferPlan->reson = $request->get('reson');
                $transferPlan->remark = $request->get('remark');
                $transferPlan->broads = $broads;
                $transferPlan->packages = $packages;
                $transferPlan->ship_fee = $ship_fee;
		$transferPlan->weight = $totalWeight;
		$transferPlan->volume = $totalVolume;
            }else{
                $items=[];
            }
            
            $transferPlan->save();
            if($items){
                $transferPlan->items()->delete();
                $itemData = [];
                foreach ($items as $item) {
                    $itemData[] = new TransferPlanItem($item);
                }
                $transferPlan->items()->saveMany($itemData);
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
        $status = intval(array_get($_REQUEST,"confirmStatus"));
        DB::beginTransaction();
        try{ 
            $customActionMessage='';
            foreach($_REQUEST["id"] as $plan_id){
                $transferPlan = TransferPlan::find($plan_id);
                if(empty($transferPlan)){
                    throw new \Exception('ID:'.$plan_id.' 不存在!');
                }
                if(!in_array($transferPlan->status,array_keys(getTransferForRole()))){
                    throw new \Exception('ID:'.$plan_id.' 无权限修改!');
                }
                $transferPlan->status = $status;
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
