<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\CreatedReport;
use App\Models\ReportGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;
class RrController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can(['requestreport-show'])) die('Permission denied -- requestreport-show');
		$batch_del = $request->get('batch_del');
		if($batch_del){
			ReportGroup::whereIn('id',explode(',',$batch_del))->delete();
			CreatedReport::whereIn('group_id',explode(',',$batch_del))->delete();
		}
        $datas= ReportGroup::with('reports')->get();
        return view('rr/index',['datas'=>$datas,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);

    }

    public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }




     public function getAccounts(){

		$seller=[];
		$sellerids = DB::connection('amazon')->select("select id,(case mws_marketplaceid
		when 'ATVPDKIKX0DER' then 'US'
		when 'A2EUQ1WTGCTBG2' then 'US'
		when 'A1AM78C64UM0Y8' then 'US'
		when 'A1F83G8C2ARO7P' then 'EU'
		when 'A1PA6795UKMFR9' then 'EU'
		when 'APJ6JRA9NG5V4' then 'EU'
		when 'A1RKKUPIHCS9HS' then 'EU'
		when 'A13V1IB3VIYZZH' then 'EU'
		when 'A1VC38T7YXB528' then 'JP'
		else 'US' End) as area,label from seller_accounts where `primary` = 1 and deleted_at is NULL and refresh_token is not null");
		foreach($sellerids as $sellerid){
			$seller[$sellerid->id]['name']=$sellerid->label;
			$seller[$sellerid->id]['area']=$sellerid->area;
		}
		return $seller;

    }

    public function create()
    {
        if(!Auth::user()->can(['requestreport-create'])) die('Permission denied -- requestreport-create');
        return view('rr/add',['users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }


    public function store(Request $request)
    {
        if(!Auth::user()->can(['requestreport-create'])) die('Permission denied -- requestreport-create');
        $this->validate($request, [
            'seller_account_ids' => 'required|array',
            'report_type' => 'required|string',
			'after_date' => 'required|string',
			'before_date' => 'required|string',
        ]);
		$insertData = $report_option = [];
		$date = date('Y-m-d H:i:s');
		$group = ReportGroup::create([]);
		
		$options = $request->get('report_option');
		foreach($options as $option){
			if(!empty(array_get($option,'option')) && !empty(array_get($option,'value'))) $report_option[$option['option']] = $option['value'];
		}
		foreach($request->get('seller_account_ids') as $seller_account_id){
			$insertData[] = array(
				'seller_account_id'=>$seller_account_id,
				'report_type'=>$request->get('report_type'),
				'report_option'=>!empty($report_option)?json_encode($report_option):NULL,
				'after_date'=>$request->get('after_date'),
				'before_date'=>$request->get('before_date'),
				'created_at'=>$date,
				'updated_at'=>$date,
				'group_id'=>$group->id,	
				'user_id'=>Auth::user()->id,
			);
		}
        $result = CreatedReport::insert($insertData);
        if ($result) {
            $request->session()->flash('success_message','Set Report Success');
            return redirect('rr');
        } else {
            $request->session()->flash('error_message','Set Report Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->can(['requestreport-delete'])) die('Permission denied -- requestreport-delete');
        ReportGroup::where('id',$id)->delete();
		CreatedReport::where('group_id',$id)->delete();
        $request->session()->flash('success_message','Delete Report Success');
        return redirect('rr');
    }
}
