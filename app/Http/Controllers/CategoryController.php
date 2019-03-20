<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use App\Qa;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
class CategoryController extends Controller
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
	
    public function index()
    {
        $data = new Category;
        $order_by = 'created_at';
        $sort = 'desc';

        $data_one = $data->where('category_type', 1);
        $data_two = $data->where('category_type', 2);
        $category_one = $data_one->orderBy($order_by,$sort)->get()->toArray();
        $category_two = $data_two->orderBy($order_by,$sort)->get()->toArray();

        return view('category/index',['category_one'=>$category_one,'category_two'=>$category_two]);
    }

    public function create(Request $request)
    {

        $category_type = $request->get('type',1);
        $data = new Category;
        $order_by = 'created_at';
        $sort = 'desc';

        $lists =  $data->orderBy($order_by,$sort)->get()->toArray();
        if($category_type == 2){
            $tree = $this->getTree($lists,29);
        }else{
            $tree = $this->getTree($lists,28);
        }


        return view('category/add',['tree'=>$tree,'category_type'=>$category_type]);
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'category_name' => 'required|string',
        ]);

        $category = new Category();
        $category->category_pid = $request->get('superior_category');
        $category->category_name = $request->get('category_name');
        $category->category_order = 0;
        $category->category_type = $request->get('category_type',1);

        if ($category->save()) {
            $request->session()->flash('success_message','Set Category Success');
            return redirect('category');
        } else {
            $request->session()->flash('error_message','Set Category Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request)
    {
        //if(!Auth::user()->admin) die();

        $id = $request->get('cate_id');
        $data = new Category;

        $data = $data->where('category_pid',$id);
        $lists =  $data->get()->toArray();
        if(empty($lists)){
            Category::where('id',$id)->delete();
            $request->session()->flash('success_message','Delete Category Success');
        }else{
            $request->session()->flash('error_message','Delete Category Failed');
        }
        return redirect('category');
    }

    public function edit(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();
        $data = new Category;
        $order_by = 'created_at';
        $sort = 'desc';

        $category_type = $request->get('type',1);

        $lists =  $data->orderBy($order_by,$sort)->get()->toArray();
        if($category_type == 2){
            $tree = $this->getTree($lists,29);
        }else{
            $tree = $this->getTree($lists,28);
        }

        $category = Category::where('id',$id)->first()->toArray();

        if(!$category){
            $request->session()->flash('error_message','Qa Category not Exists');
            return redirect('category');
        }
        return view('category/edit',['category'=>$category,'tree'=>$tree,'category_type'=>$category_type]);
    }

    public function update(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();

        $this->validate($request, [
            'category_name' => 'required|string',
        ]);

//        $category = new Category();
        $category = Category::findOrFail($id);
        $category->category_pid = $request->get('superior_category');
        $category->category_name = $request->get('category_name');
        $category->category_order = 0;
        $category->category_type = $request->get('category_type',1);

        if ($category->save()) {
            $request->session()->flash('success_message','Set Category Success');
            return redirect('category');
        } else {
            $request->session()->flash('error_message','Set Category Failed');
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