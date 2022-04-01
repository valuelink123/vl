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
		//if(!Auth::user()->can(['sales-alert-show'])) die('Permission denied -- sales-alert-show');
        $data = new SalesAlert;
        $order_by = 'created_at';
        $sort = 'desc';

        $data = $data->orderBy($order_by,$sort)->get()->toArray();
        return view('salesAlert/index',['data'=>$data]);
    }

    public function create()
    {
        return view('salesAlert/add');
    }

    public function store(Request $request)
    {
		if(!Auth::user()->can(['qa-category-create'])) die('Permission denied -- qa-category-create');
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
        if(!Auth::user()->can(['qa-category-delete'])) die('Permission denied -- qa-category-delete');

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
        if(!Auth::user()->can(['qa-category-show'])) die('Permission denied -- qa-category-show');
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
        if(!Auth::user()->can(['qa-category-update'])) die('Permission denied -- qa-category-update');

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

    //品线问题导入
	public function import(Request $request)
	{
		if($request->isMethod('POST')){
			$file = $request->file('importFile');
			if($file){
				if($file->isValid()){
					$ext = $file->getClientOriginalExtension();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/category/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);

					if($bool){
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						//得到的数据中,A=>name,B=>E-email,C=>phone,D=>Amazon_Order_Id,E=>country,F=>brand,G=>from

						foreach($importData as $key => $val){
							if($key==1 || empty($val['A']) || empty($val['B'])){
								unset($importData[$key]);
								continue;
							}
							//父级品线名称集合
							$parent_name[$val['A']] = $val['A'];
						}

						$parent_name = array_unique($parent_name);
						//查询是否有父级品线,没有父级品线的数据的话，就不执行操作，返回提示先添加父级数据
						$_category_data = Category::whereIn('category_name',$parent_name)->where('category_type',1)->get()->toArray();
						$category_data = array();
						foreach($_category_data as $ck=>$cv){
							//整理查出来的数据
							$category_data[$cv['category_name']] = $cv['id'];
						}

						$addnum = 0;
						//当查询出来的总数等于导入的总数就可以批量导入数据
						if(count($category_data) != count($parent_name)){
							//父级品线数据不相等，得到没有的父级品线，然后提示其先添加父级品线，然后再导入品线数据
							$diff_arr = array_diff($parent_name,array_keys($category_data));
							$diff_str = implode(',',$diff_arr);
							$request->session()->flash('error_message','请先添加这些父级：'.$diff_str.'；然后再导入数据');
						}else{
							//可以批量导入数据
							foreach($importData as $key => $val){
								$category_pid = $category_data[$val['A']];
								$insertInfo = array(
									'category_pid' => $category_pid,//父级id
									'category_name' => $val['B'],
									'category_order' => 0,
									'category_type' => 1,
								);
								$res = Category::updateOrCreate(['category_pid' => $category_pid, 'category_name' => $val['B']], $insertInfo);
								if($res){
									$addnum++;
								}
							}
						}
						$request->session()->flash('success_message','Import '.$addnum.' pieces of Data Success!');
					}else{
						$request->session()->flash('error_message','Import Data Failed');
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
			}
		}
		return redirect('category');
	}

}