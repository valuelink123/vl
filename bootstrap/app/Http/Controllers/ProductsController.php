<?php

namespace App\Http\Controllers;

use App\Product;
use App\SellerAccounts;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class ProductsController extends Controller
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
        return view('products/index',['seller_accounts'=>$seller_accounts]);

    }

    public function get()
    {
        /*
   * Paging
   */

        $orderby = 'review_date';
        $sort = 'desc';

        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==0) $orderby = 'seller_account_id';
            if($_REQUEST['order'][0]['column']==1) $orderby = 'asin';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'rating';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'review_date';
            $sort = $_REQUEST['order'][0]['dir'];
        }

        $siteUrl=getSiteUrl();
        $seller_accounts = SellerAccounts::where('user_id',$this->getUserId())->where('active',1)->get()->toArray();
        foreach($seller_accounts as $seller_account){
            $seller_account_arr[$seller_account['id']] = $seller_account['mws_name'];
            $seller_account_site_url_arr[$seller_account['id']] = $siteUrl[$seller_account['mws_marketplaceid']];
        }

        $orders = ProductReview::where('user_id',$this->getUserId());



        if($_REQUEST['customer_account']){
            $orders = $orders->where('seller_account_id', $_REQUEST['customer_account']);
        }
        if($_REQUEST['rating']){
            $orders = $orders->where('rating', $_REQUEST['rating']);
        }
        if($_REQUEST['asin']){
            $orders = $orders->where('asin', 'like', '%'.$_REQUEST['asin'].'%');
        }
        if($_REQUEST['name']){
            $orders = $orders->where('reviewer_name', 'like', '%'.$_REQUEST['name'].'%');
        }
        if($_REQUEST['date_from']){
            $orders = $orders->where('review_date','>=',$_REQUEST['review_date']);
        }
        if($_REQUEST['date_to']){
            $orders = $orders->where('review_date','<=',$_REQUEST['review_date']);
        }

        if($_REQUEST['title']){
            $orders = $orders->where('title', 'like', '%'.$_REQUEST['title'].'%');
        }
        if($_REQUEST['content']){
            $orders = $orders->where('content', 'like', '%'.$_REQUEST['content'].'%');
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
                $ordersList[$i]['asin'],
                $ordersList[$i]['rating'],
                substr($ordersList[$i]['review_date'],0,10),
                $ordersList[$i]['reviewer_name'],
                $ordersList[$i]['title'],
                'Not yet matched',
                '<a href="https://'.$seller_account_site_url_arr[$ordersList[$i]['seller_account_id']].'/gp/customer-reviews/'.$ordersList[$i]['review'].'" target="_blank" class="btn btn-sm btn-outline grey-salsa"><i class="fa fa-search"></i> View </a>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);

    }

}