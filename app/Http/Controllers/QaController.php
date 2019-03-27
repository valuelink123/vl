<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Qa;
use App\Category;
use App\Asin;
use App\Group;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class QaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */

    public function __construct()
    {

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

		$keywords = $request->get('keywords');
        $group = $request->get('group');
        $item_group = $request->get('item_group');
        $knowledge_type = $request->get('knowledge_type');

		//$etype = $request->get('etype');

		$qas = new Qa;
		//if($etype) $qas = $qas->where('etype',$etype);
        if($group != 'ALL'){
            $qas = $qas->where('for_product1', 'like', '%'.$group.'%');
        }
        if($item_group != 'ALL'){
            $qas = $qas->where('for_product2', 'like', '%'.$item_group.'%');
        }
        if($knowledge_type) $qas = $qas->where('knowledge_type', 'like', '%'.$knowledge_type.'%');

		if($keywords){
			$qas = $qas->where(function ($query) use ($keywords){
				$query->where('product', 'like','%'.$keywords.'%')
				->orWhere('product_line', 'like','%'.$keywords.'%')
				->orWhere('item_no', 'like','%'.$keywords.'%')
				->orWhere('model', 'like','%'.$keywords.'%')
				->orWhere('title', 'like','%'.$keywords.'%')
				->orWhere('description', 'like','%'.$keywords.'%');
			});
		}
		$qas = $qas->orderBy('created_at','desc')->paginate(8);

        $data = new Category;
        $order_by = 'created_at';
        $sort = 'desc';

        $data_two = $data->where('category_type', 2);
        $category_two = $data_two->orderBy($order_by,$sort)->get()->toArray();

        $asin_order_by = 'asin';
        $asin_sort = 'asc';
        $asin_data = new Asin;
        $asin_list =  $asin_data->orderBy($asin_order_by,$asin_sort)->get()->toArray();

        $for_product2 = [];
        foreach($asin_list as $key=>$val){
            $for_product2[] = $val['item_group'];
        }
        $for_product2 = array_unique(array_filter($for_product2));

        return view('qa/index',['qas'=>$qas,'keywords'=>$keywords,'group'=>$group,'item_group'=>$item_group,'knowledge_type'=>$knowledge_type,'users'=>$this->getUsers(),'category_two'=>$category_two,'for_product2'=>$for_product2,'groups'=>$this->getGroups()]);

    }
	
	public function show($id)
    {

        $qa = Qa::where('id',$id)->first();
        $this->clicks($id,$qa['clicks']);

        $order_by = 'created_at';
        $sort = 'desc';

        $lists =  Category::orderBy($order_by,$sort)->get()->toArray();
        $tree = $this->getTree($lists,29);

        $asin_order_by = 'asin';
        $asin_sort = 'asc';
        $asin_data = new Asin;
        $asin_list =  $asin_data->orderBy($asin_order_by,$asin_sort)->get()->toArray();

        $for_product2 = [];
        foreach($asin_list as $key=>$val){
            $for_product2[] = $val['item_group'];
        }
        $for_product2 = array_unique(array_filter($for_product2));

        return view('qa/view',['qa'=>$qa,'users'=>$this->getUsers(),'tree'=>$tree,'for_product2'=>$for_product2,'groups'=>$this->getGroups()]);
    }

    public function clicks($id,$clicks)
    {

        $seller_account = Qa::findOrFail($id);
        $seller_account->clicks = $clicks + 1;
        $seller_account->save();
    }

    public function update(Request $request,$id)
    {

        $seller_account = Qa::findOrFail($id);
        $seller_account->dqe_content = $request->get('dqe_content');
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Save Question Success');
            return redirect('question/'.$id);
        } else {
            $request->session()->flash('error_message','Save Question Failed');
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