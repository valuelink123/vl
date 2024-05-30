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

        $datas = new OtherSku();
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('sku','like','%'.array_get($_REQUEST,'keyword').'%');
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $Lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('id','desc')->get()->toArray();
        $records["data"] = [];
		
        foreach ( $Lists as $list){
			$transfer = '';
			if($list['US09TJIT']>0) $transfer.= 'TEMU在途 '.$list['US09TJIT'].'<BR>';
			if($list['US05']>0) $transfer.= '鸿宸在途 '.$list['US05'].'<BR>';
			if($list['US04']>0) $transfer.= 'DA在途 '.$list['US04'].'<BR>';
			if($list['US07']>0) $transfer.= 'Tradeful在途 '.$list['US07'].'<BR>';
			if($list['US08']>0) $transfer.= '沃尔玛在途 '.$list['US08'].'<BR>';
			if($list['US10']>0) $transfer.= '敦煌在途 '.$list['US10'].'<BR>';
            $records["data"][] = array(
                '<input type="hidden" class="checkboxes" value="'.$list['id'].'">'.$list['sku'],
				$list['purchase'],
				str_replace(';','<BR>',$list['purchase_deails']),
                $list['unpicked'],
				$list['HK01'],
				$list['in_transit'],
				$transfer,
				$list['US02US7'],
				$list['US05HC1'],
				$list['US10DH1'],
				$list['US09TMU'],
				$list['purchase']+$list['unpicked']+$list['HK01']+$list['US09TJIT']+$list['US05']+$list['US04']+$list['US07']+$list['US08']+$list['US10']+$list['US02US7']+$list['US05HC1']+$list['US10DH1']+$list['US09TMU'],
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
				OtherSku::where('sku',$request->get('sku'))->update(
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
						OtherSku::where('sku',$sku)->update(
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
