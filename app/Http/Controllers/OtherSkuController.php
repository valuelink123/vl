<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\Models\OtherSku;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class OtherSkuController extends Controller
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
        return view('othersku/list');
    }

    public function get(Request $request)
    {

        $datas = OtherSku::leftJoin('sap_skus',function($q){
				$q->on('other_skus.sku', '=', 'sap_skus.sku');
			})->selectRaw('other_skus.*,sap_skus.description,(US09TJIT+US05+US04+US07+US08+US10+US13) as transfer,(purchase-unpicked) as purchase,(purchase+HK01+US09TJIT+US05+US04+US07+US08+US10+US02US7+US05HC1+US10DH1+US09TMU+US13FH1) as total');
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('other_skus.sku','like','%'.array_get($_REQUEST,'keyword').'%')->orWhere('description','like','%'.array_get($_REQUEST,'keyword').'%');
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$orderByConfig = [
			'0'=>'sku',
			'2'=>'purchase',
			'4'=>'unpicked',
			'5'=>'HK01',
			'6'=>'in_transit',
			'7'=>'transfer',
			'8'=>'US02US7',
			'9'=>'US05HC1',
			'10'=>'US10DH1',
			'11'=>'US09TMU',
			'12'=>'US13FH1',
			'13'=>'total',
		];
        $Lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy(array_get($orderByConfig,array_get($_REQUEST,'order.0.column',13)),array_get($_REQUEST,'order.0.dir','desc'))->get()->toArray();
        $records["data"] = [];
		
        foreach ( $Lists as $list){
			$transfer = $list['transfer'];
			if($list['US09TJIT']>0) $transfer.= '<BR>TEMU在途 '.$list['US09TJIT'];
			if($list['US05']>0) $transfer.= '<BR>鸿宸在途 '.$list['US05'];
			if($list['US04']>0) $transfer.= '<BR>DA在途 '.$list['US04'];
			if($list['US07']>0) $transfer.= '<BR>Tradeful在途 '.$list['US07'];
			if($list['US08']>0) $transfer.= '<BR>沃尔玛在途 '.$list['US08'];
			if($list['US10']>0) $transfer.= '<BR>敦煌在途 '.$list['US10'];
			if($list['US13']>0) $transfer.= '<BR>富皇在途 '.$list['US13'];
            $records["data"][] = array(
                '<input type="hidden" class="checkboxes" value="'.$list['id'].'"><a class="editData">'.$list['sku'].'</a>',
				$list['description'],
				$list['purchase'],
				str_replace(';','<BR>',$list['purchase_deails']),
                '<a class="editData">'.$list['unpicked'].'</a>',
				$list['HK01'],
				'<a class="editData">'.$list['in_transit'].'</a>',
				$transfer,
				$list['US02US7'],
				$list['US05HC1'],
				$list['US10DH1'],
				$list['US09TMU'],
				$list['US13FH1'],
				$list['total'],
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $form = [];
        if($id){
            $form = OtherSku::find($id)->toArray();
        }
        return view('othersku/edit',['form'=>$form]);
    }


    public function update(Request $request)
    {
		DB::beginTransaction();
        try{
			if(Auth::user()->sap_seller_id){
				OtherSku::updateOrCreate(
					[
						'sku'=>$request->get('sku'),
					],
					[
						'in_transit'=>intval($request->get('in_transit'))
					]
            	);
			}else{
				OtherSku::updateOrCreate(
					[
						'sku'=>$request->get('sku'),
					],
					[
						'unpicked'=>intval($request->get('unpicked'))
					]
            	);
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

    public function upload( Request $request )
    {
		try{
            $updateData=[];
            DB::beginTransaction();
			$file = $request->file('file');
			$ext = $file->getClientOriginalExtension();
			$newname = date('His').uniqid().'.'.$ext;
			$newpath = '/uploads/othersku/'.date('Ymd').'/';
			$inputFileName = public_path().$newpath.$newname;
			$file->move(public_path().$newpath,$newname);
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
			$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			foreach($importData as $key => $data){
				if($key==1) continue;
				$sku = trim(array_get($data,'A'));
				$unpicked = trim(array_get($data,'B'));
				$in_transit = trim(array_get($data,'C'));
				if($sku){
					if(Auth::user()->sap_seller_id){
						OtherSku::updateOrCreate(
							[
								'sku'=>$sku,
							],
							[
								'in_transit'=>intval($in_transit)
							]
						);
					}else{
						OtherSku::updateOrCreate(
							[
								'sku'=>$request->get('sku'),
							],
							[
								'unpicked'=>intval($unpicked)
							]
						);
					}
				}
                
			}
            DB::commit();
			$records["customActionStatus"] = 'OK';
			$records["customActionMessage"] = 'Upload Successed!';  
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
		}    
        echo json_encode($records);  
	}
	
	public function destroy(Request $request,$id)
    {
        $db = OtherSku::findOrFail($id);
        $db->delete();
        $request->session()->flash('success_message','Delete Success');
        return redirect('otherSku');
    }
}
