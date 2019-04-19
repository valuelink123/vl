<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NonCtg;
use DB;
use App\User;

class NonctgController extends Controller
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
        return view('nonctg/index');
    }
    //列表的ajax请求数据
    public function get(Request $request)
    {
        //取出所有用户的id=>name的映射数组
        $users = User::getUsers();

        $orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==0) $orderby = 'date';
            $sort = $_REQUEST['order'][0]['dir'];
        }
        $customers = new NonCtg;
        $searchField = array('email','name','from','amazon_order_id');
        foreach($searchField as $field){
            if(array_get($_REQUEST,$field)){
                $customers = $customers->where($field, 'like', '%'.$_REQUEST[$field].'%');
            }
        }

        if(array_get($_REQUEST,'date_from')){
            $customers = $customers->where('date','>=',$_REQUEST['date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'date_to')){
            $customers = $customers->where('date','<=',$_REQUEST['date_to'].' 23:59:59');
        }


        $iTotalRecords = $customers->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);

        $customersLists =  $customers->orderBy($orderby,$sort)->skip($iDisplayStart)->take($iDisplayLength)->get()->toArray();
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        if($customersLists) {
            //根据订单id得到物料组asin和销售人姓名
            $amazonOrderIds = $orderAsinSeller = array();
            foreach ($customersLists as $val) {
                $amazonOrderIds[] = $val['amazon_order_id'];
            }
            $orderAsinSeller = $this->getOrderBasicInfo($amazonOrderIds);

            //数据数据得到前端需要显示的内容
            foreach ($customersLists as $customersList) {
                $records["data"][] = array(
                    $customersList['date'],
                    $customersList['email'],
                    $customersList['name'],
                    $customersList['amazon_order_id'],
                    isset($orderAsinSeller[$customersList['amazon_order_id']]['asin']) ? $orderAsinSeller[$customersList['amazon_order_id']]['asin'] : '未知',
                    isset($orderAsinSeller[$customersList['amazon_order_id']]['item_group']) ? $orderAsinSeller[$customersList['amazon_order_id']]['item_group'] : '未知',
                    isset($orderAsinSeller[$customersList['amazon_order_id']]['item_no']) ? $orderAsinSeller[$customersList['amazon_order_id']]['item_no'] : '未知',
                    isset($orderAsinSeller[$customersList['amazon_order_id']]['seller']) ? $orderAsinSeller[$customersList['amazon_order_id']]['seller'] : '未知',
                    $customersList['from'],
                    '<td>-</td>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    //通过亚马逊订单ID得到物料组的信息和销售人员信息
    public function getOrderBasicInfo($orderIds)
    {
        $orderBasicInfo = array();
        $sql = 'select b.ASIN as asin,b.SellerSKU as sellersku,SalesChannel,a. AmazonOrderId as amazonOrderId,c.item_group as item_group, c.item_no as item_no,seller  
                from amazon_orders as a
                left join amazon_orders_item  as b on a.AmazonOrderId=b.AmazonOrderId 
                left join asin as c on c.asin = b.ASIN and c.SellerSKU = b.SellerSKU and c.site = concat("www.",a.SalesChannel) 
                where a. AmazonOrderId in("'.join('","',$orderIds).'")';
        $orderInfo = DB::select($sql);
        foreach($orderInfo as $key=>$val){
            $orderBasicInfo[$val->amazonOrderId] = array(
                'asin' => $val->asin,
                'sellersku' => $val->sellersku,
                'SalesChannel' => $val->SalesChannel,
                'amazonOrderId' => $val->amazonOrderId,
                'item_group' => $val->item_group,
                'item_no' => $val->item_no,
                'seller' => $val->seller,
            );
        }
        return $orderBasicInfo;
    }



}
