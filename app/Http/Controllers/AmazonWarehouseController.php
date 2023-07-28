<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\Models\AmazonWarehouse;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class AmazonWarehouseController extends Controller
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
        return view('transfer/amazonWarehouseList');
    }

    public function get(Request $request)
    {

        $datas = new AmazonWarehouse();
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('code','like','%'.array_get($_REQUEST,'keyword').'%')->orWhere('address','like','%'.array_get($_REQUEST,'keyword').'%');
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
                '<input type="hidden" class="checkboxes" value="'.$list['id'].'">'.$list['code'],
                $list['address'],
                $list['state'],
                $list['city'],
                $list['zip'],
                $list['fee']
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
            $form = AmazonWarehouse::find($id)->toArray();
        }
        return view('transfer/amazonWarehouseEdit',['form'=>$form]);
    }


    public function update(Request $request)
    {
		DB::beginTransaction();
        try{
            AmazonWarehouse::updateOrCreate(
                [
                    'code'=>$request->get('code'),
                ],
                [
                    'address'=>$request->get('address'),
                    'state'=>$request->get('state'),
                    'city'=>$request->get('city'),
                    'zip'=>$request->get('zip'),
                    'fee'=>round($request->get('fee'),2),
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
				$code = trim(array_get($data,'A'));
				$fee = trim(array_get($data,'F'));
				if($code && $fee){
                    AmazonWarehouse::updateOrCreate(
                        [
                            'code'=>$code
                        ],
                        [
                            'address'=>trim(array_get($data,'B')),
                            'state'=>trim(array_get($data,'C')),
                            'city'=>trim(array_get($data,'D')),
                            'zip'=>trim(array_get($data,'E')),
                            'fee'=>round($fee,2),
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
