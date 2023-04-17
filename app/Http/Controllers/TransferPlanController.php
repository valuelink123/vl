<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\SapAsinMatchSku;
use App\User;
use App\Models\TransferPlan;
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
			$rules = explode("-",Auth::user()->seller_rules);
			if(array_get($rules,0)!='*') $datas = $datas->where('bg',array_get($rules,0));
			if(array_get($rules,1)!='*') $datas = $datas->where('bu',array_get($rules,1));
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
            $datas = $datas->where('items','like','%'.array_get($_REQUEST,'keyword').'%');
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
            $str = '';
            if(is_array($items)){
                foreach($items as $item){
                    $str .= '<div class="row" style="margin-bottom:5px;"><div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=50px height=50px></div>
                    <div class="col-md-10" style="text-align:left;">
                        <div class="col-md-6">SKU : '.$item['sku'].'</div>
                        <div class="col-md-6">FNSKU : '.$item['fnsku'].'</div>
                        <div class="col-md-6">Asin : '.$item['asin'].'</div>
                        <div class="col-md-6">SellerSku : '.$item['sellersku'].'</div>
                        <div class="col-md-12">数量 : '.intval(array_get($item,'quantity')).'</div>
                        <div class="col-md-6">预计卡板数 : '.$item['broads'].'</div>
                        <div class="col-md-6">预计箱数 : '.$item['packages'].'</div>
                        <div class="col-md-6">RMS : '.array_get(\App\Models\TransferPlan::TF,$item['rms']).'</div>
                        <div class="col-md-6">抽卡 : '.array_get(\App\Models\TransferPlan::TF,$item['rcard']).'</div>
                        <div class="col-md-12">预计运费:'.$item['ship_fee'].'</div>
                    </div></div>';
                }
            }
            $str .= '<div class="col-md-12" style="textc -align:left;"><span class="label label-sm label-primary">'.$list['reson'].'</span> <span class="label label-sm label-danger">'.$list['remark'].'</span></div>';
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                array_get(array_flip(getSiteCode()),$list['marketplace_id']),
                $list['bg'].$list['bu'].'-'.array_get($sellers,intval($list['sap_seller_id'])),
                array_get($accounts,$list['seller_id'],$list['seller_id']),
                $list['shipment_id'],
                $list['warehouse_code'],
                array_get(TransferPlan::SHIPMETHOD,$list['ship_method']).(!empty($list['ship_fee'])?'<BR><span class="label label-sm label-primary">'.$list['ship_fee'].'</span>':''),
                $str,
                $list['received_date'].(!empty($list['ship_date'])?'<BR><span class="label label-sm label-primary">'.$list['ship_date'].'</span>':''),
                array_get(TransferPlan::STATUS,$list['status']),
                array_get(TransferPlan::SHIPMENTSTATUS,$list['tstatus']),
                $list['in_factory_code'],
                $list['out_factory_code'],
                empty($list['files']) ? '<button class="uploadDataBtn btn red-sunglo" value="'.$list['id'].'">上传大货资料</button>' : '<button class="uploadDataBtn btn  green-meadow" value="'.$list['id'].'">查看大货资料</button>',
            );
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
            $sellersku = DB::connection('vlz')->select($sql);
            $data = (json_decode(json_encode($sellersku), true));
            $accounts = getSellerAccount();
            foreach($data as $k=>$v){
                $data[$k]['seller_id'] = array_get($accounts,$v['seller_id'],$v['seller_id']);
                $sku = $v['sku'];
                $seller_id = $v['seller_id'];
            }
            $images = DB::connection('vlz')->table('asins')->where('asin',$request['asin'])->where('marketplaceid',$request['marketplace_id'])->value('images');
            $images = explode(',',$images);
            $image = array_get($images,0);
        }
        return ['seller_sku_list' => $data , 'sku' => $sku, 'seller_id' => $seller_id, 'image' => $image];
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
            $data = $request->all();
            $id = $data['id'];
            unset($data['id']);unset($data['_token']);
            $transferPlan = TransferPlan::find($id);
            if((!empty($transferPlan) && $transferPlan->sap_seller_id == \Auth::user()->sap_seller_id && $transferPlan->status<=1) || empty($transferPlan)){
                $items = $data['items'];
                $currencyRate = DB::connection('vlz')->table('currency_rates')->where('currency','USD')->value('rate');
                if(!$currencyRate) throw new \Exception('缺失汇率数据!');
                $warehouseFee = DB::connection('vlz')->table('amazon_warehouse_fee')->where('code',$data['warehouse_code'])->value('fee');
                if(!$warehouseFee) throw new \Exception($data['warehouse_code'].'缺失运费数据!');
                $data['broads'] = $data['ship_fee'] = $data['packages'] = 0;
                foreach($items as $key=>$item){
                    $sizeInfo = DB::connection('vlz')->table('sku_size')->where('sku',$item['sku'])->first();
                    if(empty($sizeInfo)){
                        throw new \Exception($item['sku'].'缺失基础数据!');
                    }
                    $data['items'][$key]['per_package_qty'] = intval($sizeInfo->quantity);
                    $data['items'][$key]['packages'] = ceil($item['quantity']/$sizeInfo->quantity);
                    $data['items'][$key]['volume'] = round($sizeInfo->volume,4);
                    $data['items'][$key]['broads'] = ceil(($sizeInfo->volume)*$item['quantity']/1.5);
                    $data['items'][$key]['ship_fee'] = round($data['items'][$key]['broads']*$warehouseFee*$currencyRate,2);

                    $data['broads']+= $data['items'][$key]['broads'];
                    $data['ship_fee']+= $data['items'][$key]['ship_fee'];
                    $data['packages']+= $data['items'][$key]['packages'];
                }
            }
            if(!empty($transferPlan)){
                if($transferPlan->sap_seller_id != \Auth::user()->sap_seller_id || $transferPlan->status>1){
                    $data = [];
                    $data['status'] = $request->get('status');
                }
                if($transferPlan->status==5) throw new \Exception('已审批状态无法修改!');
                TransferPlan::updateOrCreate(['id'=>$id],$data);
            }else{
                $data['sap_seller_id'] = Auth::user()->sap_seller_id;
                $data['bg'] = Auth::user()->ubg;
                $data['bu'] = Auth::user()->ubu;
                TransferPlan::create($data);
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
                    $customActionMessage.='ID:'.$plan_id.' 不存在!</BR>';
                    continue;
                }
                if($transferPlan->status == 5){
                    $customActionMessage.='ID:'.$plan_id.' 已审批状态无法修改!</BR>';
                    continue;
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