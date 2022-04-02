<?php

namespace App\Http\Controllers;

use App\SalesAlert;
use Illuminate\Http\Request;
use App\Qa;
use Illuminate\Support\Facades\Session;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;

class SalesAlertController extends Controller
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
		//if(!Auth::user()->can(['sales-alert-show'])) die('Permission denied -- sales-alert-show');
        $data = new SalesAlert;
        $order_by = 'created_at';
        $sort = 'desc';

        $data = $data->orderBy($order_by,$sort)->get()->toArray();
        return view('salesAlert/index',['data'=>$data]);
    }

    public function create()
    {
        $bg = '';
        $bgs = $this->getBg($bg);
        return view('salesAlert/add',['bgs'=>$bgs]);
    }

    public function store(Request $request)
    {
		//if(!Auth::user()->can(['qa-category-create'])) die('Permission denied -- qa-category-create');
        $salesAlert = new SalesAlert();
        $salesAlert->department = $request->get('department');
        $salesAlert->start_time = $request->get('start_time');
        $salesAlert->end_time = $request->get('end_time');
        $salesAlert->year = $request->get('year');
        $salesAlert->month = $request->get('month');
        $salesAlert->sales = $request->get('sales');
        $salesAlert->marketing_expenses = $request->get('marketing_expenses');
        $salesAlert->creatrd_user = Auth::user()->name;

        if ($salesAlert->save()) {
            $request->session()->flash('success_message','Set SalesAlert Success');
            return redirect('salesAlert');
        } else {
            $request->session()->flash('error_message','Set SalesAlert Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        //if(!Auth::user()->can(['qa-category-delete'])) die('Permission denied -- qa-category-delete');
        $data = new SalesAlert();

        $lists = $data->where('id',$id)->get()->toArray();
        if(!empty($lists)){
            SalesAlert::where('id',$id)->delete();
            $request->session()->flash('success_message','Delete SalesAlert Success');
        }else{
            $request->session()->flash('error_message','Delete SalesAlert Failed');
        }
        return redirect('salesAlert');
    }

    public function edit(Request $request,$id)
    {
        //if(!Auth::user()->can(['qa-category-show'])) die('Permission denied -- qa-category-show');

        $data = SalesAlert::where('id',$id)->first()->toArray();

        if(!$data){
            $request->session()->flash('error_message','SalesAlert not Exists');
            return redirect('salesAlert');
        }
        return view('salesAlert/edit',['data'=>$data]);
    }

    public function update(Request $request,$id)
    {
        //if(!Auth::user()->can(['qa-category-update'])) die('Permission denied -- qa-category-update');

        $salesAlert = SalesAlert::findOrFail($id);
        $salesAlert->department = $request->get('department');
        $salesAlert->start_time = $request->get('start_time');
        $salesAlert->end_time = $request->get('end_time');
        $salesAlert->year = $request->get('year');
        $salesAlert->month = $request->get('month');
        $salesAlert->sales = $request->get('sales');
        $salesAlert->marketing_expenses = $request->get('marketing_expenses');
        $salesAlert->creatrd_user = Auth::user()->name;

        if ($salesAlert->save()) {
            $request->session()->flash('success_message','Set salesAlert Success');
            return redirect('salesAlert');
        } else {
            $request->session()->flash('error_message','Set salesAlert Failed');
            return redirect()->back()->withInput();
        }
    }

    /**
     * 销售额报警（sku）维度
    */
    public function salesAlertSku(){

        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        $site = getMarketDomain();//获取站点选项
        $bg = '';
        $bgs = $this->getBg($bg);

        return view('salesAlert/salesAlertSku',['start_date'=>$start_date,'end_date'=>$end_date,'site'=>$site,'bgs'=>$bgs]);
    }

    /**
     * 销售额报警（周）维度
     */
    public function salesAlertWeek(){
        echo 33;exit();
    }

}