<?php

namespace App\Http\Controllers;

use App\Order;
use App\SellerAccounts;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class OrderController extends Controller
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
        return view('order/index',['seller_accounts'=>$seller_accounts]);

    }

    public function get()
    {
        /*
   * Paging
   */

        $orderby = 'last_update_date';
        $sort = 'desc';

        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==0) $orderby = 'seller_account_id';
            if($_REQUEST['order'][0]['column']==1) $orderby = 'amazon_order_id';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'buyer_name';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'buyer_email';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'purchase_date';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'order_status';
            if($_REQUEST['order'][0]['column']==6) $orderby = 'amount';
            $sort = $_REQUEST['order'][0]['dir'];
        }


        $seller_accounts = SellerAccounts::where('user_id',$this->getUserId())->where('active',1)->get()->toArray();
        unset($ids);
        $ids[]=0;
        foreach($seller_accounts as $seller_account){
            $seller_account_arr[$seller_account['id']] = $seller_account['mws_name'];
            $ids[] = $seller_account['id'];
        }

        $orders = Order::whereIn('seller_account_id',$ids);



        if($_REQUEST['customer_account']){
            $orders = $orders->where('seller_account_id', $_REQUEST['customer_account']);
        }
        if($_REQUEST['amazon_order_id']){
            $orders = $orders->where('amazon_order_id', $_REQUEST['amazon_order_id']);
        }
        if($_REQUEST['customer_name']){
            $orders = $orders->where('buyer_name', 'like', '%'.$_REQUEST['customer_name'].'%');
        }
        if($_REQUEST['customer_email']){
            $orders = $orders->where('buyer_email', 'like', '%'.$_REQUEST['customer_email'].'%');
        }
        if($_REQUEST['order_date_from']){
            $orders = $orders->where('purchase_date','>=',$_REQUEST['order_date_from'].'T00:00:00Z');
        }
        if($_REQUEST['order_date_to']){
            $orders = $orders->where('purchase_date','<=',$_REQUEST['order_date_to'].'T23:59:59Z');
        }

        if($_REQUEST['amount_from']){
            $orders = $orders->where('amount','>=',round($_REQUEST['amount_from'],2));
        }
        if($_REQUEST['amount_to']){
            $orders = $orders->where('amount','<=',round($_REQUEST['amount_to'],2));
        }

        if($_REQUEST['order_status']){
            $orders = $orders->where('order_status', $_REQUEST['order_status']);
        }

        $ordersList =  $orders->orderBy($orderby,$sort)->get()->toArray();

        $iTotalRecords = count($ordersList);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;


        for($i = $iDisplayStart; $i < $end; $i++) {

            $records["data"][] = array(
                $seller_account_arr[$ordersList[$i]['seller_account_id']],
                $ordersList[$i]['amazon_order_id'],
                $ordersList[$i]['buyer_name'],
                $ordersList[$i]['buyer_email'],
                $ordersList[$i]['purchase_date'],

                $ordersList[$i]['order_status'],
                round($ordersList[$i]['amount'],2).' '.$ordersList[$i]['currency_code'],
                '<a href="javascript:;" class="btn btn-sm btn-outline grey-salsa"><i class="fa fa-search"></i> View </a>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);

    }

}