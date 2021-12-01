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
}