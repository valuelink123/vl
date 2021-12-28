<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Budgetskus;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\TaxRate;
use DB;

class BudgetskuController extends Controller
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
        return view('budget/sku_list');
    }

    public function get(Request $request)
    {
        $datas = new Budgetskus;
 
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('sku','like','%'.array_get($_REQUEST,'keyword').'%')->orWhere('description','like','%'.array_get($_REQUEST,'keyword').'%');
        }
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->orderBy('id','desc')->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $users = User::where('sap_seller_id','>',0)->pluck('name','sap_seller_id');
        $planers = User::pluck('name','id');
        $records["data"] = array();
		foreach ( $lists as $list){
            $records["data"][] = array(
                '<input type="hidden" class="checkboxes" value="'.$list['id'].'"  />'.$list['sku'],
                $list['site'],
                $list['description'],
                array_get(getSkuStatuses(),$list['status']),
				$list['level'],
                intval($list['stock']),
                round($list['volume'],4),
                array_get(getSkuSize(),$list['size']),
                round($list['cost'],4),
                ($list['common_fee']*100).'%',
                round($list['pick_fee'],4),
                ($list['exception']*100).'%',
                array_get($users,$list['sap_seller_id'],$list['sap_seller_id']),
                array_get($planers,$list['planer_id'],$list['planer_id']),
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $form =  Budgetskus::where('id',$id)->first()->toArray();
        if(empty($form)) die('不存在!');
        $form['common_fee'] = ($form['common_fee']*100).'%';
        $form['exception'] = ($form['exception']*100).'%';
        $form['tax'] = (round(TaxRate::where('sku',$form['sku'])->where('site',array_get(getSiteShort(),$form['site']))->value('tax'),4)*100).'%';
        return view('budget/sku_edit',['form'=>$form]);
    }

    public function create()
    {  
        return view('budget/sku_edit',['form'=>[],'itemGroup' => $this->getItemGroup()]);
    }
	
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
            $data = $id?(Budgetskus::findOrFail($id)):(new Budgetskus);

            if(!$id){
                $item_group = $request->get('item_group');
                $year = date('Y');
                $year = $year > 2020 ? $year : 2020;
                $groupYear = $item_group.$year;
                $itemSku = Budgetskus::where('sku','like',$groupYear.'%')->orderBy('sku','desc')->first();
                $data->sku = empty($itemSku) ? $groupYear.'0001' : $groupYear.sprintf("%04d",explode($groupYear,$itemSku->sku)[1] + 1);
                $data->site = $request->get('site');
            }
            
            $data->description = $request->get('description');
            $data->status = $request->get('status');
            $data->level = $request->get('level');
            $data->stock = $request->get('stock');
            $data->volume = $request->get('volume');
            $data->size = $request->get('size');
            $data->cost = $request->get('cost');
            $data->pick_fee = $request->get('pick_fee');
            $data->description = $request->get('description');
			$data->exception = explode('%',$request->get('exception'))[0]/100;
			$data->common_fee = explode('%',$request->get('common_fee'))[0]/100;
            $data->sap_seller_id = $request->get('sap_seller_id');
            $data->planer_id = $request->get('planer_id');
            $tax = explode('%',$request->get('tax'))[0]/100;
            $siteShort = getSiteShort();
            TaxRate::updateOrCreate(
                ['sku' => $data->sku,'site' => isset($siteShort[$data->site]) ? strtoupper($siteShort[$data->site]) : $data->site],
                [
                    'tax' => $tax,
                ]
            );
            $user = User::where('sap_seller_id',$data->sap_seller_id)->first();
            if(!empty($user)){
                $data->bg = $user->ubg;
                $data->bu = $user->ubu;
            }
            
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


    public function upload( Request $request )
    {
        DB::beginTransaction();
        try{ 
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $type = $file->getClientMimeType();
            $realPath = $file->getRealPath();
            $newname = date('His').uniqid().'.'.$ext;
            $newpath = '/uploads/budgetSku/'.date('Ymd').'/';
            $inputFileName = public_path().$newpath.$newname;
            $bool = $file->move(public_path().$newpath,$newname);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $importData = $spreadsheet->getActiveSheet()->toArray(null, true, true);
            $users = User::where('sap_seller_id','>',0)->pluck('sap_seller_id','name');
            $planers = User::pluck('id','name')->toArray();
            foreach($importData as $key => $value){
                if($key>0) {
                    $sku = array_get($value,0);
                    $site = array_get($value,1);

                    
		    $data['description'] = array_get($value,2);
                    $data['status'] = intval(array_get(array_flip(getSkuStatuses()),array_get($value,3),0));
                    $data['level'] = array_get($value,4,'S');
                    $data['stock'] = intval(array_get($value,5,0));
                    $data['volume'] = round(array_get($value,6,0),4);
					$data['size'] = intval(array_get(array_flip(getSkuSize()),array_get($value,7,0)));
                    $data['cost'] = round(array_get($value,8,0),2);
                    $tax = round(array_get($value,9,0),4);
                    $data['pick_fee'] = round(array_get($value,10,0),2);
                    $data['exception'] = round(array_get($value,11,0),4);
                    $data['common_fee'] = round(array_get($value,12,0),4);
                    
			$sap_seller_id = array_get($value,13,'');
                    $planer_id = array_get($value,14,'');
                    $data['sap_seller_id'] = intval(isset($users[$sap_seller_id])?$users[$sap_seller_id]:0);
			$data['planer_id'] = intval(isset($planers[$planer_id])?$planers[$planer_id]:0);
                    
			$data['bg'] = array_get($value,15);
                    $data['bu'] = array_get($value,16);
                    $siteShort = getSiteShort();
                    TaxRate::updateOrCreate(
                        ['sku' => $sku,'site' => isset($siteShort[$site]) ? strtoupper($siteShort[$site]) : $site],
                        [
                            'tax' => $tax,
                        ]
                    );
                    if($sku && $site){
                        Budgetskus::updateOrCreate(
                            ['sku'=>$sku,'site'=>$site]
                            ,
                            $data
                        );
                    }
	
                }	
            }
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = '更新成功!';     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);  
	}


    public function export(Request $request){
		set_time_limit(0);        
	    $datas = new Budgetskus;
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('sku','like','%'.array_get($_REQUEST,'keyword').'%')->orWhere('description','like','%'.array_get($_REQUEST,'keyword').'%');
        }
        $datas =  $datas->get()->toArray();
		$arrayData = array();
		$headArray[] = '物料号';
		$headArray[] = '站点';
		$headArray[] = '产品名称';
		$headArray[] = '状态';
		$headArray[] = '等级';
		$headArray[] = '期初库存';
		$headArray[] = '体积';
		$headArray[] = '体积标准';
		$headArray[] = '成本';
		$headArray[] = '关税';
        $headArray[] = '拣配费';
        $headArray[] = '异常率';
        $headArray[] = '佣金比率';
        $headArray[] = '销售员';
        $headArray[] = '计划员';
        $headArray[] = 'BG';
        $headArray[] = 'BU';
		$arrayData[] = $headArray;
        $users = User::where('sap_seller_id','>',0)->pluck('name','sap_seller_id');
        $planers = User::pluck('name','id');
		foreach($datas as $data){
            $siteShort = getSiteShort();
            $tax = TaxRate::where('sku',$data['sku'])->where('site',isset($siteShort[$data['site']]) ? strtoupper($siteShort[$data['site']]) : $data['site'])->value('tax');
            $arrayData[] = array(
                $data['sku'],
                $data['site'],
                $data['description'],
                array_get(getSkuStatuses(),$data['status']??0),
                $data['level'],
                $data['stock'],
                $data['volume'],
                array_get(getSkuSize(),$data['size']??0),
                $data['cost'],
                $tax,
                $data['pick_fee'],
                $data['exception'],
                $data['common_fee'],
                array_get($users,$data['sap_seller_id'],$data['sap_seller_id']),
                array_get($planers,$data['planer_id'],$data['planer_id']),
                $data['bg'],
                $data['bu']
            );
		}

		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->fromArray($arrayData,NULL,'A1');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="gift_cards.xlsx"');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
}
