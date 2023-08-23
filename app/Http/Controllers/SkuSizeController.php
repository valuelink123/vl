<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\Models\SkuSize;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class SkuSizeController extends Controller
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
        return view('transfer/skuSizeList');
    }

    public function get(Request $request)
    {

        $datas = new SkuSize();
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
            $records["data"][] = array(
                '<input type="hidden" class="checkboxes" value="'.$list['id'].'">'.$list['sku'],
                $list['quantity'],
                $list['length'],
                $list['width'],
                $list['height'],
                $list['weight'],
                $list['volume'],
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
            $form = SkuSize::find($id)->toArray();
        }
        return view('transfer/skuSizeEdit',['form'=>$form]);
    }


    public function update(Request $request)
    {
		DB::beginTransaction();
        try{
            SkuSize::updateOrCreate(
                [
                    'sku'=>$request->get('sku'),
                ],
                [
                    'quantity'=>intval($request->get('quantity')),
                    'length'=>round($request->get('length'),2),
                    'width'=>round($request->get('width'),2),
                    'height'=>round($request->get('height'),2),
                    'weight'=>round($request->get('weight'),2),
                    'volume'=>round($request->get('volume'),2),
                ]
            );
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
			$newpath = '/uploads/da/'.date('Ymd').'/';
			$inputFileName = public_path().$newpath.$newname;
			$file->move(public_path().$newpath,$newname);
			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
			$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			foreach($importData as $key => $data){
				if($key==1) continue;
				$sku = trim(array_get($data,'A'));
				if($sku){
                    SkuSize::updateOrCreate(
                        [
                            'sku'=>$sku,
                        ],
                        [
                            'quantity'=>intval(trim(array_get($data,'B'))),
                            'length'=>round(trim(array_get($data,'C')),2),
                            'width'=>round(trim(array_get($data,'D')),2),
                            'height'=>round(trim(array_get($data,'E')),2),
                            'weight'=>round(trim(array_get($data,'F')),2),
                            'volume'=>round(trim(array_get($data,'G')),2),
                        ]
                    );
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
}
