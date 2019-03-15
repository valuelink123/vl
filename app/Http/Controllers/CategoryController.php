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

        $tree =  $data->orderBy($order_by,$sort)->get()->toArray();
        //$tree = $this->getTree($lists,0);

        return view('category/index',['tree'=>$tree]);
    }

    public function create()
    {

        $data = new Category;
        $order_by = 'created_at';
        $sort = 'desc';

        $lists =  $data->orderBy($order_by,$sort)->get()->toArray();
        $tree = $this->getTree($lists,0);

        return view('category/add',['tree'=>$tree]);
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
        $lists =  $data->orderBy($order_by,$sort)->get()->toArray();
        $tree = $this->getTree($lists,0);

        $category = Category::where('id',$id)->first()->toArray();

        if(!$category){
            $request->session()->flash('error_message','Qa Category not Exists');
            return redirect('category');
        }
        return view('category/edit',['category'=>$category,'tree'=>$tree]);
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