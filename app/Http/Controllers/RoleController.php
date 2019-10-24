<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Permission;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;
class RoleController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		if(!Auth::user()->can(['role-show'])) die('Permission denied -- role-show');
        $roles = Role::get()->toArray();
        return view('role/index',['roles'=>$roles]);
    }


    public function create()
    {
		if(!Auth::user()->can(['role-create'])) die('Permission denied -- role-create');
        return view('role/add',['permissions'=>Permission::get()->toArray()]);
    }


    public function store(Request $request)
    {
		if(!Auth::user()->can(['role-create'])) die('Permission denied -- role-create');
        $this->validate($request, [
            'name' => 'required|string',
            'display_name' => 'required|string',
			'permission' => 'required|array',
        ]);
		
        $role = new Role();
		$role->name = $request->get('name');
		$role->display_name = $request->get('display_name');
		$role->description = $request->get('description');
		$role->save();
		$role->perms()->sync($request->get('permission'));
        if ($role->save()) {
            $request->session()->flash('success_message','Set Role Success');
            return redirect('role');
        } else {
            $request->session()->flash('error_message','Set Role Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
		if(!Auth::user()->can(['role-delete'])) die('Permission denied -- role-delete');
        Role::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Role Success');
        return redirect('role');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['role-show'])) die('Permission denied -- role-show');
		$role= Role::where('id',$id)->first()->toArray();
        $rolePermissions = DB::table("permission_role")->where("permission_role.role_id",$id)
            ->pluck('permission_role.permission_id')->toArray();
        return view('role/edit',['role'=>$role,'rolePermissions'=>$rolePermissions,'permissions'=>Permission::get()->toArray()]);
    }

    public function update(Request $request,$id)
    {
		if(!Auth::user()->can(['role-update'])) die('Permission denied -- role-update');
        $this->validate($request, [
            'name' => 'required|string',
            'display_name' => 'required|string',
			'permission' => 'required|array',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->get('name');
		$role->display_name = $request->get('display_name');
		$role->description = $request->get('description');
		$role->save();
		$role->perms()->sync([]);
		$role->perms()->sync($request->get('permission'));
        if ($role->save()) {
            $request->session()->flash('success_message','Set Role Success');
            return redirect('role');
        } else {
            $request->session()->flash('error_message','Set Role Failed');
            return redirect()->back()->withInput();
        }
    }

}