<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GiftCard;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class GiftCardController extends Controller
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
        return view('giftcard/list');
    }

    public function get(Request $request)
    {
        $datas = GiftCard::with('user')->with('exception');
 
        if(array_get($_REQUEST,'bg')){
            $datas = $datas->where('bg',array_get($_REQUEST,'bg'));
        }
        if(array_get($_REQUEST,'bu')){
            $datas = $datas->where('bu',array_get($_REQUEST,'bu'));
        }
        if(array_get($_REQUEST,'code')){
            $datas = $datas->where('code',array_get($_REQUEST,'code'));
        }
        if(array_get($_REQUEST,'amazon_order_id')){
            $datas = $datas->where('exception.amazon_order_id',array_get($_REQUEST,'amazon_order_id'));
        } 
        if(array_get($_REQUEST,'user_id')!==NULL && array_get($_REQUEST,'user_id')!==''){
            $datas = $datas->whereIn('user_id',array_get($_REQUEST,'user_id'));
        }
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->whereIn('status',array_get($_REQUEST,'status'));
        }
        
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        
        $records["data"] = array();
		foreach ( $lists as $list){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['bg'],
                $list['bu'],
                $list['code'],
                $list['amount'],
                $list['currency'],
                array_get($list,'user.name'),
                array_get(GiftCard::STATUS,$list['status']),
                array_get($list,'exception.amazon_order_id'),
                $list['created_at'],
				$list['updated_at'],
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $form =  GiftCard::with('exception')->where('id',$id)->first()->toArray();
        if(empty($form)) die('不存在!');    
        return view('giftcard/edit',['form'=>$form]);
    }

    public function create()
    {  
        return view('giftcard/edit',['form'=>[]]);
    }
	
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
            $data = $id?(GiftCard::where('status',0)->findOrFail($id)):(new GiftCard);
            $fileds = array(
                'bg','bu','code','amount','currency'
            );
            foreach($fileds as $filed){
                $data->{$filed} = $request->get($filed);
            }
            $data->user_id = Auth::user()->id;
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
            $newpath = '/uploads/giftcard/'.date('Ymd').'/';
            $inputFileName = public_path().$newpath.$newname;
            $bool = $file->move(public_path().$newpath,$newname);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $importData = $spreadsheet->getActiveSheet()->toArray(null, true, true);

            foreach($importData as $key => $data){
                if($key>0) {
                    $data['bg'] = array_get($data,0);
                    $data['bu'] = array_get($data,1);
                    $data['code'] = array_get($data,2);
                    $data['amount'] = round(array_get($data,3),2);
                    $data['currency'] = array_get($data,4);
                    
                    if($data['bg'] && $data['bu'] && $data['code'] && $data['amount'] && $data['currency']){
                        GiftCard::firstOrCreate(
                            ['code'=>$data['code']]
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
		if(!Auth::user()->can(['gift-card-export'])) die('Permission denied -- gift-card-export');
		set_time_limit(0);        
	    $datas = GiftCard::with('user')->with('exception');
        if(array_get($_REQUEST,'bg')){
            $datas = $datas->where('bg',array_get($_REQUEST,'bg'));
        }
        if(array_get($_REQUEST,'bu')){
            $datas = $datas->where('bu',array_get($_REQUEST,'bu'));
        }
        if(array_get($_REQUEST,'code')){
            $datas = $datas->where('code',array_get($_REQUEST,'code'));
        }
        if(array_get($_REQUEST,'amazon_order_id')){
            $datas = $datas->where('exception.amazon_order_id',array_get($_REQUEST,'amazon_order_id'));
        } 
        if(array_get($_REQUEST,'user_id')!==NULL && array_get($_REQUEST,'user_id')!==''){
            $datas = $datas->whereIn('user_id',explode(',',array_get($_REQUEST,'user_id')));
        }
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->whereIn('status',explode(',',array_get($_REQUEST,'status')));
        }
        $datas =  $datas->get()->toArray();

		$arrayData = array();
		$headArray[] = 'BG';
		$headArray[] = 'BU';
		$headArray[] = '卡号';
		$headArray[] = '金额';
		$headArray[] = '货币';
		$headArray[] = '用户';
		$headArray[] = '状态';
		$headArray[] = '异常订单号';
		$headArray[] = '创建时间';
		$headArray[] = '更新时间';
		$arrayData[] = $headArray;
		foreach($datas as $data){
            $arrayData[] = array(
                $data['bg'],
                $data['bu'],
                $data['code'],
                $data['amount'],
                $data['currency'],
                array_get($data,'user.name'),
                array_get(GiftCard::STATUS,$data['status']),
                array_get($data,'exception.amazon_order_id'),
                $data['created_at'],
                $data['updated_at'],

            );
		}

		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->fromArray($arrayData,NULL, 'A1' );
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="gift_cards.xlsx"');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
}