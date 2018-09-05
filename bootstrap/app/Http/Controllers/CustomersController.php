<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\SellerAccounts;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class CustomersController extends Controller
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

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seller_accounts = SellerAccounts::where('user_id',$this->getUserId())->where('active',1)->get()->toArray();
        return view('customers/index',['seller_accounts'=>$seller_accounts]);

    }

    public function get()
    {
        /*
   * Paging
   */

        $orderby = 'name';
        $sort = 'asc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'name';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'email';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'orders_count';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'seller_account_id';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'blacklisted';
            $sort = $_REQUEST['order'][0]['dir'];
        }


        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {
            Customer::where('user_id',$this->getUserId())->whereIN('id',$_REQUEST["id"])->update(['blacklisted'=>$_REQUEST["customActionName"]]);

            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
        }
        $customers = Customer::where('user_id',$this->getUserId());
        $seller_accounts = SellerAccounts::where('user_id',$this->getUserId())->where('active',1)->get()->toArray();
        foreach($seller_accounts as $seller_account){
            $seller_account_arr[$seller_account['id']] = $seller_account['mws_name'];
        }
        if($_REQUEST['customer_name']){
            $customers = $customers->where('name', 'like', '%'.$_REQUEST['customer_name'].'%');
        }
        if($_REQUEST['customer_email']){
            $customers = $customers->where('email', 'like', '%'.$_REQUEST['customer_email'].'%');
        }
        if($_REQUEST['customer_orders_count']){
            $customers = $customers->where('orders_count',$_REQUEST['customer_orders_count']);
        }
        if($_REQUEST['customer_account']){
            $customers = $customers->where('seller_account_id', $_REQUEST['customer_account']);
        }
        if($_REQUEST['customer_status']){
            $customers = $customers->where('blacklisted', $_REQUEST['customer_status']);
        }
        $customersList =  $customers->orderBy($orderby,$sort)->get()->toArray();

        $iTotalRecords = count($customersList);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $status_list[0] = "<span class=\"label label-sm label-success\">No</span>";
        $status_list[1] = "<span class=\"label label-sm label-danger\">Yes</span>";

        for($i = $iDisplayStart; $i < $end; $i++) {

            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList[$i]['id'].'"/><span></span></label>',
                $customersList[$i]['name'],
                $customersList[$i]['email'],
                $customersList[$i]['orders_count'],
                $seller_account_arr[$customersList[$i]['seller_account_id']],
                $status_list[$customersList[$i]['blacklisted']],
                '<a href="javascript:;" class="btn btn-sm btn-outline grey-salsa"><i class="fa fa-search"></i> Send Message </a>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

}