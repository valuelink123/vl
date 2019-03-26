<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Qa;
use App\Asin;
use App\Group;
use Illuminate\Support\Facades\Session;
use App\Category;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class QaiController extends Controller
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
	
	
	public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }
    public function getGroups(){
        $users = Group::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['group_name'];
        }
        return $users_array;
    }
	
    public function index()
    {
        //if(!Auth::user()->admin) die();
        return view('qai/index',['users'=>$this->getUsers()]);

    }

    public function create()
    {

        $order_by = 'created_at';
        $sort = 'desc';

        $lists =  Category::orderBy($order_by,$sort)->get()->toArray();
        $tree = $this->getTree($lists,29);
        $trees = $this->getTree($lists,118);

        $asin_order_by = 'asin';
        $asin_sort = 'asc';
        $asin_data = new Asin;
        $asin_list =  $asin_data->orderBy($asin_order_by,$asin_sort)->get()->toArray();

        //$epoint1 = [];
        $for_product2 = [];
        $for_product3 = [];
        $for_product4 = [];
        foreach($asin_list as $key=>$val){
            //$epoint1[] = $val['group_id'];
            $for_product2[] = $val['item_group'];
            $for_product3[] = $val['item_model'];
            $for_product4[] = $val['item_no'];
        }
        //$epoint1 = array_unique($epoint1);
        $for_product2 = array_unique(array_filter($for_product2));
        $for_product3 = array_unique(array_filter($for_product3));
        $for_product4 = array_unique(array_filter($for_product4));

        $qas = new Qa;
        $qas = $qas->orderBy('created_at','desc')->get()->toArray();

        return view('qai/add',['tree'=>$tree,'for_product2'=>$for_product2,'for_product3'=>$for_product3,'for_product4'=>$for_product4,'groups'=>$this->getGroups(),'users'=>$this->getUsers(),'qas'=>$qas,'trees'=>$trees]);
    }
	
	
	public function get(Request $request)
    {
        /*
   * Paging
   */

        $orderby = 'created_at';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
			if($_REQUEST['order'][0]['column']==1) $orderby = 'created_at';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'user_id';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'comfirm';
			if($_REQUEST['order'][0]['column']==4) $orderby = 'title';
			if($_REQUEST['order'][0]['column']==5) $orderby = 'clicks';
			if($_REQUEST['order'][0]['column']==6) $orderby = 'updated_at';
            $sort = $_REQUEST['order'][0]['dir'];
        }

        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {
     
			$updatebox = new Qa;
            $action  = array_get($_REQUEST,"QaAction");
			if($action =='delete'){
				$updatebox->whereIN('id',$_REQUEST["id"])->delete();
			}
			if($action =='confirm'){
				$updatebox->whereIN('id',$_REQUEST["id"])->update(['confirm'=>1]);
			}
			if($action =='unconfirm'){
				$updatebox->whereIN('id',$_REQUEST["id"])->update(['confirm'=>0]);
			}

			unset($updatebox);
        }
		
		if(Auth::user()->admin){
            $customers = new Qa;
        }else{
            $customers = new Qa; //$customers = Qa::where('user_id',$this->getUserId());
        }
		
//		if (array_get($_REQUEST, 'user_id')) {
//			$customers = $customers->where('user_id', $_REQUEST['user_id']);
//		}
//
//		if(array_get($_REQUEST,'product_line')){
//            $customers = $customers->where('product_line', 'like', '%'.$_REQUEST['product_line'].'%');
//        }
//        if(array_get($_REQUEST,'product')){
//            $customers = $customers->where('product', 'like', '%'.$_REQUEST['product'].'%');
//        }
//        if(array_get($_REQUEST,'model')){
//            $customers = $customers->where('model', 'like', '%'.$_REQUEST['model'].'%');
//        }
//
//		if(array_get($_REQUEST,'item_no')){
//            $customers = $customers->where('item_no', 'like', '%'.$_REQUEST['item_no'].'%');
//        }
//		if(array_get($_REQUEST,'etype')){
//            $customers = $customers->where('etype', $_REQUEST['etype']);
//        }
//		if(array_get($_REQUEST,'epoint')){
//            $customers = $customers->where('epoint', 'like', '%'.$_REQUEST['epoint'].'%');
//        }
//
//		if(array_get($_REQUEST,'title')){
//            $customers = $customers->where('title', 'like', '%'.$_REQUEST['title'].'%');
//        }
//		if(array_get($_REQUEST,'confirm')){
//			if(array_get($_REQUEST,'confirm') =='confirm'){
//				$customers = $customers->where('confirm', 1);
//			}
//
//			if(array_get($_REQUEST,'confirm') =='unconfirm'){
//				$customers = $customers->where('confirm', 0);
//			}
//
//        }
		
//		if(array_get($_REQUEST,'create_date_from')){
//            $customers = $customers->where('created_at','>=',$_REQUEST['create_date_from'].' 00:00:00');
//        }
//        if(array_get($_REQUEST,'create_date_to')){
//            $customers = $customers->where('created_at','<=',$_REQUEST['create_date_to'].' 23:59:59');
//        }
//
//		if(array_get($_REQUEST,'update_date_from')){
//            $customers = $customers->where('updated_at','>=',$_REQUEST['update_date_from'].' 00:00:00');
//        }
//        if(array_get($_REQUEST,'update_date_to')){
//            $customers = $customers->where('updated_at','<=',$_REQUEST['update_date_to'].' 23:59:59');
//        }
        if(array_get($_REQUEST,'group')){
            $customers = $customers->where('for_product1', 'like', '%'.$_REQUEST['group'].'%');
        }
        if(array_get($_REQUEST,'item_group')){
            $customers = $customers->where('for_product2', 'like', '%'.$_REQUEST['item_group'].'%');
        }
        if(array_get($_REQUEST,'item_model')){
            $customers = $customers->where('for_product3', 'like', '%'.$_REQUEST['item_model'].'%');
        }
        if(array_get($_REQUEST,'keywords')){
            $customers = $customers->where('title', 'like', '%'.$_REQUEST['keywords'].'%');
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
        $users = $this->getUsers();
        for($i = $iDisplayStart; $i < $end; $i++) {
            if($customersList[$i]['confirm'] == 2){
                $confirm = '<span class="label label-sm label-success">Invaild</span>';
            }else if($customersList[$i]['confirm'] == 1){
                $confirm = '<span class="label label-sm label-primary">Active</span>';
            }else{
                $confirm = '<span class="label label-sm label-danger">Pending</span>';
            }
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList[$i]['id'].'"/><span></span></label>',
				$customersList[$i]['created_at'],
                array_get($users,$customersList[$i]['user_id']),
                //$customersList[$i]['confirm']?'<span class="label label-sm label-primary">Confirmed</span>':'<span class="label label-sm label-danger">Un Confirm</span>',
                $confirm,
                $customersList[$i]['title'],
                $customersList[$i]['clicks'],
                $customersList[$i]['updated_at'],
				'<a href="'.url('qa/'.$customersList[$i]['id'].'/edit').'" target="_blank">
					<button type="button" class="btn btn-success btn-xs">Edit</button>
				</a>
				<a href="'.url('question/'.$customersList[$i]['id']).'" target="_blank">
					<button type="button" class="btn btn- btn-xs">View</button>
				</a>
				<form action="'.url('qa/'.$customersList[$i]['id']).'" method="POST" style="display: inline;">
					'.method_field('DELETE').'
					'.csrf_field().'
					<button type="submit" class="btn btn-danger btn-xs">Delete</button>
				</form>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function get_old(Request $request)
    {
        /*
   * Paging
   */

        $orderby = 'created_at';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'product_line';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'product';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'model';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'item_no';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'title';
            if($_REQUEST['order'][0]['column']==6) $orderby = 'user_id';
            if($_REQUEST['order'][0]['column']==7) $orderby = 'comfirm';
            if($_REQUEST['order'][0]['column']==8) $orderby = 'updated_at';
            $sort = $_REQUEST['order'][0]['dir'];
        }

        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {

            $updatebox = new Qa;
            $action  = array_get($_REQUEST,"QaAction");
            if($action =='delete'){
                $updatebox->whereIN('id',$_REQUEST["id"])->delete();
            }
            if($action =='confirm'){
                $updatebox->whereIN('id',$_REQUEST["id"])->update(['confirm'=>1]);
            }
            if($action =='unconfirm'){
                $updatebox->whereIN('id',$_REQUEST["id"])->update(['confirm'=>0]);
            }

            unset($updatebox);
        }

        if(Auth::user()->admin){
            $customers = new Qa;
        }else{
            $customers = new Qa; //$customers = Qa::where('user_id',$this->getUserId());
        }

        if (array_get($_REQUEST, 'user_id')) {
            $customers = $customers->where('user_id', $_REQUEST['user_id']);
        }

        if(array_get($_REQUEST,'product_line')){
            $customers = $customers->where('product_line', 'like', '%'.$_REQUEST['product_line'].'%');
        }
        if(array_get($_REQUEST,'product')){
            $customers = $customers->where('product', 'like', '%'.$_REQUEST['product'].'%');
        }
        if(array_get($_REQUEST,'model')){
            $customers = $customers->where('model', 'like', '%'.$_REQUEST['model'].'%');
        }

        if(array_get($_REQUEST,'item_no')){
            $customers = $customers->where('item_no', 'like', '%'.$_REQUEST['item_no'].'%');
        }
        if(array_get($_REQUEST,'etype')){
            $customers = $customers->where('etype', $_REQUEST['etype']);
        }
        if(array_get($_REQUEST,'epoint')){
            $customers = $customers->where('epoint', 'like', '%'.$_REQUEST['epoint'].'%');
        }

        if(array_get($_REQUEST,'title')){
            $customers = $customers->where('title', 'like', '%'.$_REQUEST['title'].'%');
        }
        if(array_get($_REQUEST,'confirm')){
            if(array_get($_REQUEST,'confirm') =='confirm'){
                $customers = $customers->where('confirm', 1);
            }

            if(array_get($_REQUEST,'confirm') =='unconfirm'){
                $customers = $customers->where('confirm', 0);
            }

        }

        if(array_get($_REQUEST,'create_date_from')){
            $customers = $customers->where('created_at','>=',$_REQUEST['create_date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'create_date_to')){
            $customers = $customers->where('created_at','<=',$_REQUEST['create_date_to'].' 23:59:59');
        }

        if(array_get($_REQUEST,'update_date_from')){
            $customers = $customers->where('updated_at','>=',$_REQUEST['update_date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'update_date_to')){
            $customers = $customers->where('updated_at','<=',$_REQUEST['update_date_to'].' 23:59:59');
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
        $users = $this->getUsers();
        for($i = $iDisplayStart; $i < $end; $i++) {
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList[$i]['id'].'"/><span></span></label>',
                $customersList[$i]['product_line'],
                $customersList[$i]['product'],

                $customersList[$i]['model'],
                $customersList[$i]['item_no'],
                $customersList[$i]['etype'],
                $customersList[$i]['epoint'],
                $customersList[$i]['title'],

                array_get($users,$customersList[$i]['user_id']),
                $customersList[$i]['confirm']?'<span class="label label-sm label-primary">Confirmed</span>':'<span class="label label-sm label-danger">Un Confirm</span>',
                $customersList[$i]['updated_at'],
                '<a href="'.url('qa/'.$customersList[$i]['id'].'/edit').'" target="_blank">
					<button type="button" class="btn btn-success btn-xs">Edit</button>
				</a>
				<a href="'.url('question/'.$customersList[$i]['id']).'" target="_blank">
					<button type="button" class="btn btn- btn-xs">View</button>
				</a>
				<form action="'.url('qa/'.$customersList[$i]['id']).'" method="POST" style="display: inline;">
					'.method_field('DELETE').'
					'.csrf_field().'
					<button type="submit" class="btn btn-danger btn-xs">Delete</button>
				</form>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function store(Request $request)
    {
        //if(!Auth::user()->admin) die();
		
        $this->validate($request, [
//            'model' => 'required|string',
//			'product_line' => 'required|string',
//            'item_no' => 'required|string',
			'title' => 'required|string',
			'description' => 'required|string',
        ]);

        $similar_question = $request->get('similar_question');
        $similar_question_list = [];
        foreach($similar_question as $key=>$val){
            if($val != ''){
                $similar_question_list[] = $val;
            }
        }
		
        $seller_account = new Qa;
        $seller_account->product = $request->get('product');
		$seller_account->model = $request->get('model');
		$seller_account->product_line = $request->get('product_line');
		$seller_account->item_no = $request->get('item_no');
		$seller_account->etype = $request->get('etype');
		$seller_account->epoint = $request->get('epoint');
        $seller_account->title = $request->get('title');
		$seller_account->description = $request->get('description');
        $seller_account->service_content = $request->get('service_content');
		$seller_account->dqe_content = $request->get('dqe_content');
		$seller_account->confirm = $request->get('confirm');

        $seller_account->knowledge_type = $request->get('knowledge_type');
        $seller_account->for_product1 = $request->get('for_product1');
        $seller_account->for_product2 = $request->get('for_product2');
        $seller_account->for_product3 = $request->get('for_product3');
        $seller_account->for_product4 = $request->get('for_product4');
        $seller_account->for_question = $request->get('for_question');
        $seller_account->similar_question = json_encode($similar_question_list,true);
        $seller_account->related_knowledge = $request->get('related_knowledge')?implode(';',$request->get('related_knowledge')):null;

		$seller_account->user_id = intval(Auth::user()->id);
        if($request->get('id')>0){
            $seller_account->id = $request->get('id');
        }
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Qa Success');
            return redirect('qa');
        } else {
            $request->session()->flash('error_message','Set Qa Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();
        Qa::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Qa Success');
        return redirect('qa');
    }

    public function edit(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();

        $order_by = 'created_at';
        $sort = 'desc';

        $lists =  Category::orderBy($order_by,$sort)->get()->toArray();
        $tree = $this->getTree($lists,29);
        $trees = $this->getTree($lists,118);

        $asin_order_by = 'asin';
        $asin_sort = 'asc';
        $asin_data = new Asin;
        $asin_list =  $asin_data->orderBy($asin_order_by,$asin_sort)->get()->toArray();

        //$epoint1 = [];
        $for_product2 = [];
        $for_product3 = [];
        $for_product4 = [];
        foreach($asin_list as $key=>$val){
            //$epoint1[] = $val['group_id'];
            $for_product2[] = $val['item_group'];
            $for_product3[] = $val['item_model'];
            $for_product4[] = $val['item_no'];
        }
        //$epoint1 = array_unique($epoint1);
        $for_product2 = array_unique(array_filter($for_product2));
        $for_product3 = array_unique(array_filter($for_product3));
        $for_product4 = array_unique(array_filter($for_product4));

        $qas = new Qa;
        $qas = $qas->orderBy('created_at','desc')->get()->toArray();

        $qa = Qa::where('id',$id)->first()->toArray();
        if(!$qa){
            $request->session()->flash('error_message','Qa not Exists');
            return redirect('qa');
        }

        return view('qai/edit',['tree'=>$tree,'for_product2'=>$for_product2,'for_product3'=>$for_product3,'for_product4'=>$for_product4,'groups'=>$this->getGroups(),'users'=>$this->getUsers(),'qas'=>$qas,'trees'=>$trees,'qa'=>$qa]);

        //return view('qai/edit',['users'=>$this->getUsers(),'qa'=>$qa]);
    }

    public function update(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();

        $this->validate($request, [
			'title' => 'required|string',
			'description' => 'required|string',
        ]);

        $similar_question = $request->get('similar_question');
        $similar_question_list = [];
        foreach($similar_question as $key=>$val){
            if($val != ''){
                $similar_question_list[] = $val;
            }
        }

        $seller_account = Qa::findOrFail($id);
        $seller_account->product = $request->get('product');
		$seller_account->model = $request->get('model');
		$seller_account->product_line = $request->get('product_line');
		$seller_account->item_no = $request->get('item_no');
		$seller_account->etype = $request->get('etype');
		$seller_account->epoint = $request->get('epoint');
        $seller_account->title = $request->get('title');
		$seller_account->description = $request->get('description');
        $seller_account->service_content = $request->get('service_content');
		$seller_account->dqe_content = $request->get('dqe_content');
		$seller_account->confirm = $request->get('confirm');

        $seller_account->knowledge_type = $request->get('knowledge_type');
        $seller_account->for_product1 = $request->get('for_product1');
        $seller_account->for_product2 = $request->get('for_product2');
        $seller_account->for_product3 = $request->get('for_product3');
        $seller_account->for_product4 = $request->get('for_product4');
        $seller_account->for_question = $request->get('for_question');
        $seller_account->similar_question = json_encode($similar_question_list,true);
        $seller_account->related_knowledge = $request->get('related_knowledge')?implode(';',$request->get('related_knowledge')):null;

		$seller_account->user_id = intval(Auth::user()->id);
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Qa Success');
            return redirect('qa');
        } else {
            $request->session()->flash('error_message','Set Qa Failed');
            return redirect()->back()->withInput();
        }
    }

    public function getTree($data, $pId)
    {
        $tree = [];
        foreach($data as $k => $v)
        {
            if($v['category_pid'] == $pId)
            {
                //父亲找到儿子
                $v['category_pid'] = $this->getTree($data, $v['id']);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }



}