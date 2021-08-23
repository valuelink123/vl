<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
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
		if($batch_del) DB::connection('order')->table('request_report')->whereIn('id',explode(',',$batch_del))->delete();
        $datas= DB::connection('order')->table('request_report')->orderBy('RequestDate','Desc')->get()->toArray();
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
		$sellerids = DB::connection('vlz')->select("select mws_seller_id,(case mws_marketplaceid
		when 'ATVPDKIKX0DER' then 'US'
		when 'A2EUQ1WTGCTBG2' then 'US'
		when 'A1AM78C64UM0Y8' then 'US'
		when 'A1F83G8C2ARO7P' then 'EU'
		when 'A1PA6795UKMFR9' then 'EU'
		when 'APJ6JRA9NG5V4' then 'EU'
		when 'A1RKKUPIHCS9HS' then 'EU'
		when 'A13V1IB3VIYZZH' then 'EU'
		when 'A1VC38T7YXB528' then 'JP'
		else 'US' End) as area,label from seller_accounts where deleted_at is NULL GROUP BY mws_seller_id,area,label");
		foreach($sellerids as $sellerid){
			$seller[$sellerid->mws_seller_id]['name']=$sellerid->label;
			$seller[$sellerid->mws_seller_id]['area']=$sellerid->area;
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
            'sellerid' => 'required|array',
            'type' => 'required|string',
			'startdate' => 'required|string',
			'enddate' => 'required|string',
        ]);
		$insertData=[];
		foreach($request->get('sellerid') as $sellerid){
		$insertData[] = array('SellerId'=>$sellerid,
			'Type'=>$request->get('type'),
			'StartDate'=>$request->get('startdate'),
			'EndDate'=>$request->get('enddate'),
			'UserId'=>Auth::user()->id,
			'Message'=>'_IN_PROGRESS_',
			'RequestDate'=>date('Y-m-d H:i:s')
			);
		}
        $result = DB::connection('order')->table('request_report')->insert($insertData);
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
        $result = DB::connection('order')->table('request_report')->where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Report Success');
        return redirect('rr');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['requestreport-download'])) die('Permission denied -- requestreport-download');
        $result = DB::connection('order')->table('request_report')->where('id',$id)->first()->toArray();
        if($result){
            print_r($result);
        }
    }
}