<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ConfigOption;
use Illuminate\Support\Facades\Auth;
use DB;

class ConfigOptionController extends Controller
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

    public function index(Request $request)
    {
        if (!Auth::user()->can(['config-option-show'])) die('Permission denied -- config-option-show');

        $config_options = ConfigOption::get()->toArray();
        $id_name_pairs = ConfigOptionController::get_id_name_pairs();
        $id_pid_pairs = ConfigOptionController::get_id_pid_pairs();

        return view('config_option/index', compact(['config_options', 'id_name_pairs', 'id_pid_pairs']));
    }

    public function get(Request $request){

        //当用户点击Visible/Hidden按钮时，执行以下代码。
        if(!is_null($request->post('id'))){
            if(!Auth::user()->can(['config-option-update'])) die('Permission denied -- config-option-update');
            $config_option =  ConfigOption::findOrFail($request->post('id'));

            $config_option->co_status = ($config_option->co_status == 0) ? 1 : 0;

            if ($config_option->save()) {
                //$request->session()->flash('success_message','Changed Config Option Status successfully.');
            }
            else {
                $request->session()->flash('error_message','Failed to change Config Option Status.');
            }
        }

        $co_pid = $request->post('co_pid');
        $co_status = $request->post('co_status');
        $data = [];

        //当没有设置筛选条件时，按一级类目分组，组内的二级类目按order倒序排列
        if (is_null($co_pid)){

            //所有的一级类目
            $level_one_array = ConfigOption::where('co_pid','0')->orderBy('co_pid', 'ASC')->get()->toArray();

            $config_options = ConfigOption::whereRaw('1 = 1');

            //每个一级类目下的二级类目个数
            $level_one_child_counts = DB::select('select co_pid, count(*) as count from config_option where co_pid <> 0 group by co_pid order by co_pid;');
            if(!is_null($co_status)){
                $config_options = $config_options->where('co_status',$co_status);
                //如果Status选择了Visible或者Hidden，重新计算$level_one_child_counts
                $level_one_child_counts = DB::select('select co_pid, count(*) as count from config_option where co_pid <> 0 and co_status='.$co_status.' group by co_pid order by co_pid;');
            }

            $level_one_child_counts_array = array_map('get_object_vars', $level_one_child_counts);

            $level_two_array = $config_options->where('co_pid','<>','0')->orderBy('co_pid','ASC')->orderBy('co_order','DESC')->get()->toArray();

            $sum = 0;
            for($i=0; $i<count($level_one_child_counts_array); $i++){
                //当Status没有选择值，或者选择了Visible时，列表才会显示一级类目（一级类目的状态总是Visible）。
                if(is_null($co_status) || $co_status == 0){
                    array_push($data, $level_one_array[$i]);
                }
                $child_count = $level_one_child_counts_array[$i]['count'];
                for($j=0; $j<$child_count; $j++){
                    array_push($data, $level_two_array[$sum+$j]);

                }
                $sum += $child_count;
            }

        }
        //当设置筛选条件时（通过Parent Name，Status选择）
        else{
            $config_options = ConfigOption::whereRaw('1 = 1');

            if(!is_null($co_pid)){
                $config_options = $config_options->where('co_pid',$co_pid);
            }

            //不要使用：if($request->post('co_pid'))
            //原因：如果$request->post('co_status')值为0，则if(0)为false，起不到过滤效果。
            if(!is_null($co_status)){
                $config_options = $config_options->where('co_status',$co_status);

            }
            $data = $config_options->orderBy('co_order', 'DESC')->get()->toArray();

        }

        foreach($data as $key=>$val){
            $action = '';
            if(!Auth::user()->can(['config_option-update'])){
                if($val['co_pid'] == '0'){
                    $action = '-';
                }
                else{
                    $action = '<a href="'.url('config_option/'.$val['id'].'/edit').'"><button type="submit" class="btn btn-success btn-xs">Edit</button></a>';
                    $action.='<button type="button" onclick="change_status('.$val['id'].')" class="status_btn btn '.($val['co_status']?'btn-danger':'btn-success').' btn-xs" name="btn_status" value="'.($val['co_status']?0:1).'">'.($val['co_status']? 'Visible':'Hidden').'</button>';
                }
            }

            $data[$key]['action'] = $action;
            //co_pid这一项显示父级名字，而不是ID
            $id_name_pairs = ConfigOptionController::get_id_name_pairs();
            $co_pid = $data[$key]['co_pid'];
            $data[$key]['co_pid'] = ($co_pid == '0') ? '-' : $id_name_pairs[$co_pid];
            //status这一列：状态为0时，显示Visible；状态为1时，显示Hidden。
            $data[$key]['co_status'] = getConfigOptionStatus()[$data[$key]['co_status']];

        }

        return compact('data');

    }

    public function create()
    {
        if(!Auth::user()->can(['config-option-create'])) die('Permission denied -- config-option-create');
        $id_name_pairs = ConfigOptionController::get_id_name_pairs();
        $id_pid_pairs = ConfigOptionController::get_id_pid_pairs();
        return view('config_option/add',compact(['id_name_pairs','id_pid_pairs']));
    }

    private function get_id_name_pairs()
    {
        return ConfigOption::pluck('co_name','id');
    }

    private function get_id_pid_pairs()
    {
        return ConfigOption::pluck('co_pid','id');
    }

    public function store(Request $request)
    {
        if(!Auth::user()->can(['config-option-create'])) die('Permission denied -- config-option-create');

        $this->validate($request, [
            'co_pid' => 'required|int',
            'co_name' => 'required|string',
            'co_order' => 'required|int',
            'co_status' => 'required|int',
        ]);

        $co_pid = $request->get('co_pid');
        $co_name = $request->get('co_name');
        $co_order = $request->post('co_order');
        $co_status = $request->post('co_status');

        //检查Name和Order是否为空。
        if(is_null($co_name)){
            $request->session()->flash('error_message','Name should not be empty.');
            return redirect()->back()->withInput();
        }
        if(is_null($co_order)){
            $request->session()->flash('error_message','Order should not be empty.');
            return redirect()->back()->withInput();
        }

        //名字不能与任何一级类目的名字相同
        $parent_names = ConfigOption::where('co_pid','0')->pluck('co_name')->toArray();
        if(in_array($co_name, $parent_names)){
            $request->session()->flash('error_message', 'Name should be different from the names of parent items.');
            return redirect()->back()->withInput();
        }
        //名字不能与父级类目下所有子类目的名字相同
        $siblings_names = ConfigOption::where('co_pid',$co_pid)->pluck('co_name')->toArray();
        if(in_array($request->get('co_name'), $siblings_names)){
            $request->session()->flash('error_message', 'Name should be different from the names of siblings.');
            return redirect()->back()->withInput();
        }

        $config_option = new ConfigOption;
        $config_option->co_pid = $co_pid;
        $config_option->co_name = $co_name;
        $config_option->co_order = $co_order;
        $config_option->co_status = $co_status;

        if ($config_option->save()) {
            $request->session()->flash('success_message','Created Config Option Successfully.');
            return redirect('config_option');
        } else {
            $request->session()->flash('error_message','Failed to create Config Option.');
            return redirect()->back()->withInput();
        }
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['config-option-update'])) die('Permission denied -- config-option-show');
        $config_option= ConfigOption::where('id',$id)->first()->toArray();

        if(!$config_option){
            $request->session()->flash('error_message','Config Option does not exist.');
            return redirect('config_option');
        }

        $id_name_pairs = ConfigOptionController::get_id_name_pairs();
        $id_pid_pairs = ConfigOptionController::get_id_pid_pairs();

        return view('config_option/edit',compact(['config_option','id_name_pairs','id_pid_pairs']));
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->can(['config-option-update'])) die('Permission denied -- config-option-update');

        $this->validate($request, [
            'co_name' => 'required|string',
            'co_order' => 'required|int',
            'co_status' => 'required|int'
        ]);

        $co_pid = $request->post('co_pid');
        $co_name = $request->post('co_name');
        $co_order = $request->post('co_order');
        $co_status = $request->post('co_status');
//        $id = $request->post('co_id');

        //检查Name和Order是否为空。
        if(is_null($co_name)){
            $request->session()->flash('error_message','Name should not be empty.');
            return redirect()->back()->withInput();
        }
        if(is_null($co_order)){
            $request->session()->flash('error_message','Order should not be empty.');
            return redirect()->back()->withInput();
        }

        //名字不能与任何一级类目的名字相同
        $parent_names = ConfigOption::where('co_pid','0')->pluck('co_name')->toArray();
        if(in_array($co_name, $parent_names)){
            $request->session()->flash('error_message', 'Name should be different from the names of parent items.');
            return redirect()->back()->withInput();
        }
        //名字不能与父级类目下所有'其它'子类目的名字相同
        $siblings_names = ConfigOption::where('co_pid',$co_pid)->where('id', '<>', $id)->pluck('co_name')->toArray();
        if(in_array($request->get('co_name'), $siblings_names)){
            $request->session()->flash('error_message', 'Name should be different from the names of siblings.');
            return redirect()->back()->withInput();
        }

        $config_option =  ConfigOption::findOrFail($id);
        $config_option->co_name = $co_name;
        $config_option->co_order = $co_order;
        $config_option->co_status = $co_status;

        if ($config_option->save()) {
            $request->session()->flash('success_message','Updated Config Option successfully.');
            return redirect('config_option');
        } else {
            $request->session()->flash('error_message','Failed to update Config Option.');
            return redirect()->back()->withInput();
        }
    }

}