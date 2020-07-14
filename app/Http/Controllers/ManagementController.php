<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

header('Access-Control-Allow-Origin:*');

class ManagementController extends Controller
{

    public function __construct()
    {
        //联调时，注释下面的代码；上线时，需取消注释。
//		$this->middleware('auth');
//	    parent::__construct();
    }

    public function index()
    {
        return view('management.index');
    }

    public function getSubDepartments(Request $request){
        header('Access-Control-Allow-Origin:*');
        $parent_id = $request->input('parent_id');
        if($parent_id == null){
            return array('status'=> 0);
        }
        $order_by = 'created_at';
        $sort = 'asc';
        $list =  DB::connection('amazon')->table('departments')->where('parent_id','=',$parent_id)->orderBy($order_by,$sort)->get()->toArray();
        if($list){
            $list = json_encode($list, true);
            $returnData = array();
            $returnData['status'] = 1;
            $returnData['data'] = json_decode($list, true);
            return $returnData;
        }
        else{
            return array('status'=> 0);
        }
        exit;

    }

    //获取用户所属的各级部门ID
    public function getUserDepartmentIds(Request $request){
        header('Access-Control-Allow-Origin:*');
        //联调时，前端传入参数user_id。上线时应该去掉,采取Auth::user()->id
        //$userId = Auth::user()->id;
        $userId = $request->input('user_id');

        $departmentUser = DB::connection('amazon')->table('department_user')->where('user_id','=',$userId)->first();
        if(!$departmentUser){
            return array('status'=> 0);
        }

        $departmentId = $departmentUser->department_id;
        $rankDepartmentIds = $this->getRankDepartmentIds($departmentId);
        $rankDepartmentIds = array_reverse($rankDepartmentIds);

        //若用户所属的二级部门或三级部门不存在时，赋值-1
        //$list相应显示为：1,-1,-1; 9,10,-1; 1,2,7
        $list = array();
        for($i=0; $i<3; $i++){
            $list['dept_'.($i+1)] = array_get($rankDepartmentIds, $i, -1);
        }

        $list = json_encode($list, true);
        $returnData = array();
        $returnData['status'] = 1;
        $returnData['data'] = json_decode($list, true);
        return $returnData;

        exit;
    }


    public function getDepartmentData(Request $request){
        header('Access-Control-Allow-Origin:*');
        $departmentId = $request->input('department_id');
        $department = DB::connection('amazon')->table('departments')->where('id','=',$departmentId)->first();
        if(!$department){
            return array('status'=> 0);
        }

        $returnData = array();
        $returnData['status'] = 1;

        //------部门信息tab------
        $deptIdDisplayNamePairs = $this->getDeptIdDisplayNamePairs();
        $userIdNamePairs = $this->getUserIdNamePairs();
        $permissionIdDisplayNamePairs = $this->getPermissionIdDisplayNamePairs();
        $titleIdNamePairs = $this->getTitleIdNamePairs();

        $deptArray = array();
        $rankDepartmentIds = $this->getRankDepartmentIds($departmentId);
        $countRank =  count($rankDepartmentIds);
        $deptArray['dept'] = array_get($deptIdDisplayNamePairs, $departmentId);
        if($countRank == 1){
            $deptArray['sup_dept'] = $deptArray['dept'];
        }
        else{
            $deptArray['sup_dept'] = array_get($deptIdDisplayNamePairs, $rankDepartmentIds[1]);
        }
        $deptArray['rank_one_dept'] = array_get($deptIdDisplayNamePairs, $rankDepartmentIds[$countRank-1]);

        $deptUser = DB::connection('amazon')->table('department_user')->where('department_id', '=', $departmentId)->where('is_leader', '=', 1)->first();
        if(!$deptUser){
            $deptArray['leader'] = '';
        }
        else{
            $deptArray['leader'] = array_get($userIdNamePairs,$deptUser->user_id);
        }

        $permissionIdArray = DB::connection('amazon')->table('permission_department')->where('department_id', '=', $departmentId)->pluck('permission_id');
        if(!$permissionIdArray){
            $deptArray['permission'] = '';
        }
        else{
            $permissionDisplayNameArray = array();
            foreach($permissionIdArray as $v){
                $permissionDisplayNameArray[] = array_get($permissionIdDisplayNamePairs, $v);
            }
            $deptArray['permission'] = implode(',', $permissionDisplayNameArray);
        }

        $deptArray = json_encode($deptArray, true);
        $returnData['dept_info'] = json_decode($deptArray, true);
//        return $returnData;


        //------岗位tab------
        //获取所选部门的ID以及在此部门下的所有级别部门的ID
        $a = DB::connection('amazon')->table('departments')->select(['id','parent_id'])->get();
        $a = json_decode(json_encode($a),true);
        $allDeptArray = $this->getMenuTree($a, $departmentId, 0);
        $allDeptIds = array();
        $allDeptIds[] = $departmentId;
        foreach($allDeptArray as $v){
            $allDeptIds[] = $v['id'];
        }

        $roleArray = array();
        foreach($allDeptIds as $deptId){
            $roles = DB::connection('amazon')->table('roles')->where('department_id', '=', $deptId)->get()->toArray();
            if(!$roles) continue;
            $roles = json_decode(json_encode($roles),true);

            foreach($roles as $k=>$v){
                $temArray = array();
                $temArray['role_id'] = $v['id'];
                $temArray['display_name'] = $v['display_name'];
                $temArray['title'] = array_get($titleIdNamePairs, $v['title_id']);
                $temArray['dept'] = array_get($deptIdDisplayNamePairs, $v['department_id']);

                $permissionIdArray = DB::connection('amazon')->table('permission_role')->where('role_id', '=', $v['id'])->pluck('permission_id');
                if(!$permissionIdArray){
                    $temArray['permission'] = '';
                }
                else{
                    $permissionDisplayNameArray = array();
                    foreach($permissionIdArray as $v){
                        $permissionDisplayNameArray[] = array_get($permissionIdDisplayNamePairs, $v);
                    }
                    $temArray['permission'] = implode(',', $permissionDisplayNameArray);
                }
                $roleArray[] = $temArray;
            }

        }

        $roleArray = json_encode($roleArray, true);
        $returnData['role_info'] = json_decode($roleArray, true);


        //------人力资源tab------
        $userArray = array();
        $userIdArray = array();
        foreach($allDeptIds as $deptId) {
            $userIds = DB::connection('amazon')->table('department_user')->where('department_id', '=', $deptId)->pluck('user_id')->toArray();
            if (!$userIds) continue;
            $userIdArray = array_merge($userIdArray, $userIds);
        }
        $usersInDepts = DB::connection('amazon')->table('users')->whereIn('id',$userIdArray)->get()->toArray();
        if($usersInDepts) {
            $usersInDepts = json_decode(json_encode($usersInDepts),true);
            foreach($usersInDepts as $v) {
                $temArray = array();
                $temArray['user_id'] = $v['id'];
                $temArray['name'] = $v['name'];
                $temArray['english_name'] = $v['english_name'];
                $temArray['role'] = '';
                $temArray['title'] = '';
                $roleUser = DB::connection('amazon')->table('role_user')->where('user_id','=',$v['id'])->first();
                if($roleUser){
                    $roleId = $roleUser->role_id;
                    $role = DB::connection('amazon')->table('roles')->where('id','=',$roleId)->first();
                    if($role){
                        $temArray['role'] = $role->display_name;
                        $temArray['title'] = array_get($titleIdNamePairs, $role->title_id);
                    }
                }

                $temArray['dingtalk_id'] = $v['dingtalk_id'];
                $temArray['status'] = $v['status'];
                $userArray[] = $temArray;

            }

        }

        $userArray = json_encode($userArray, true);
        $returnData['user_info'] = json_decode($userArray, true);
        return $returnData;

        exit;

    }

    public function createDepartmentInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $data = $this->getRankOneAndTwoIdDisplayNamePairs();
        $returnData= array();
        $returnData['status'] = 1;
        $returnData['sup_dept'] = $data;

        return $returnData;
    }

    public function editDepartmentInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $departmentId = $request->input('department_id');
        if(!$departmentId){
            return array('status'=>0);
        }
        $returnData = array();
        $returnData['status'] = 1;
        $returnData['display_name'] = array_get($this->getDeptIdDisplayNamePairs(), $departmentId);
        $rankDepartmentIds = $this->getRankDepartmentIds($departmentId);
        $countRank =  count($rankDepartmentIds);
        if($countRank == 1){
            $returnData['sup_dept_id'] = $rankDepartmentIds;
        }
        else{
            $returnData['sup_dept_id'] = $rankDepartmentIds[1];
        }

        $ids = $this->getDeptPermIds($departmentId);
        $returnData['dept_permissions'] = $ids;

        return $returnData;
    }

    public function saveDepartmentInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $deptName = trim($request->input('department_name'));
        $deptId = $request->input('department_id');
        $supDeptId = $request->input('supDept_id', 0);

        if(!$deptName){
            return array('status'=>2, 'msg'=>'Name cannot be blank.');
        }

        $depts = DB::connection('amazon')->table('departments')->where('parent_id', '=',$supDeptId)->get()->toArray();
        $depts = json_decode(json_encode($depts),true);
        $departmentNameExists = false;

        //新建部门
        if(!$deptId){
            foreach($depts as $k=>$v){
                if($v['display_name'] == $deptName){
                    $departmentNameExists = true;
                    break;
                }
            }
        }
        //编辑部门
        else{
            $dept = DB::connection('amazon')->table('departments')->where('id', '=', $deptId)->first();
            $dept = json_decode(json_encode($dept),true);
            if($dept['parent_id'] = $supDeptId){
                foreach($depts as $k=>$v){
                    if($v['display_name'] == $deptName && $v['id'] != $deptId){
                        $departmentNameExists = true;
                        break;
                    }
                }
            }
            else{
                foreach($depts as $k=>$v){
                    if($v['display_name'] == $deptName){
                        $departmentNameExists = true;
                        break;
                    }
                }
            }
        }

        if($departmentNameExists){
            return array('status'=>2, 'msg'=>'Name exists.');
        }


        //1：复制权限 2：手动设置
        $permissions = array();
        $permSettings = $request->input('perm_settings');
        if($permSettings == 1){
            //1：复制部门 2：复制人员
            $copyType = $request->input('copy_type');
            if($copyType==1){
                $selectedDeptId = $request->input('selected_department_id');
                $permissions = $this->getDeptPermIds($selectedDeptId);
            }
            else if($copyType==2){
                $selectedUserId = $request->input('selected_user_id');
                $permissions = $this->getUserPermIds($selectedUserId);
            }

        }
        else{
            $permissions = $request->input('selected_permissions');
        }

        $updateArray = array();
        $updateArray['display_name']= $deptName;
        $deptIdDisplayNamePairs = $this->getDeptIdDisplayNamePairs();
        $updateArray['name'] = array_get($deptIdDisplayNamePairs, $supDeptId, '').'-'.$deptName;
        $updateArray['parent_id'] = $supDeptId;

        $returnData = array();
        $returnData['status'] = 1;
//        $returnData['supDept_id'] = $supDeptId;
//        $returnData['perm_settings'] = $permSettings == null ? '' : $permSettings;
//        $returnData['copy_type'] = $copyType == null ? '' : $copyType;
//        $returnData['selected_department_id'] = $selectedDeptId == null ? '' : $selectedDeptId;
//        $returnData['selected_user_id'] = $selectedUserId == null ? '' : $selectedUserId;
//        $returnData['selected_permissions'] = $permissions;


        //新建部门
        if(!$deptId){
            $updateArray['created_at'] = date('Y-m-d H:i:s');
            $updateArray['updated_at'] = date('Y-m-d H:i:s');

            DB::beginTransaction();
            if(DB::connection('amazon')->table('departments')->insert($updateArray)){
                $newDept = DB::connection('amazon')->table('departments')->orderBy('id', 'desc')->first();
                $newDeptId = $newDept->id;
                foreach($permissions as $v){
                    $updatePd = array('permission_id'=>$v, 'department_id'=>$newDeptId);
                    DB::connection('amazon')->table('permission_department')->insert($updatePd);
                }
                DB::commit();

                $returnData['msg'] = 'Created successfully.';
                //$returnData['department_id'] = $newDeptId;
                return $returnData;
            }
            else{
                DB::rollback();
                return array('status'=>2, 'msg'=>'Failed to create.');
            }
        }
        //编辑部门
        else{
            $updateArray['updated_at'] = date('Y-m-d H:i:s');
            DB::beginTransaction();
            if(DB::connection('amazon')->table('departments')->where('id','=', $deptId)->update($updateArray)){
                //编辑部门时，如果选了复制权限，但没有选择部门或人员，仍保留之前的权限
                $copyPermsEmpty = false;
                if($permSettings == 1){
                    if(!$copyType){
                        $copyPermsEmpty = true;
                    }
                    else if($copyType==1 && !$selectedDeptId){
                        $copyPermsEmpty = true;
                    }
                    else if($copyType==2 && !$selectedUserId){
                        $copyPermsEmpty = true;
                    }
                }
                if(!$copyPermsEmpty){
                    DB::connection('amazon')->table('permission_department')->where('department_id', '=', $deptId)->delete();
                    foreach($permissions as $v){
                        $updatePd = array('permission_id'=>$v, 'department_id'=>$deptId);
                        DB::connection('amazon')->table('permission_department')->insert($updatePd);
                    }
                }

                DB::commit();

                $returnData['msg'] = 'Updated successfully.';
                return $returnData;
            }
            else{
                DB::rollback();
                return array('status'=>2, 'msg'=>'Failed to update.');
            }
        }

    }

//    public function createRoleInfo(Request $request){
//        header('Access-Control-Allow-Origin:*');
//
//        $returnData= array();
//        $returnData['status'] = 1;
//        return $returnData;
//    }

    public function editRoleInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $roleId = $request->input('role_id');
        $role = DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->first();
        $role = json_decode(json_encode($role),true);
        if(!$role){
            return array('status'=>2, 'msg'=>'Role does not exist.');
        }

        $returnData= array();
        $returnData['status'] = 1;
        $returnData['display_name'] = $role['display_name'];
        $returnData['title_id'] = $role['title_id'];
        $returnData['department_id'] = $role['department_id'];
        $ids = $this->getRolePermIds($roleId);
        $returnData['role_permissions'] = $ids;

        return $returnData;
    }


    public function saveRoleInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $roleDisplayName = trim($request->input('role_display_name'));
        $titleId = $request->input('title_id');
        $deptId = $request->input('department_id');
        $roleId = $request->input('role_id');

        if(!$roleDisplayName){
            return array('status'=>2, 'msg'=>'Name cannot be blank.');
        }

        $roles = DB::connection('amazon')->table('roles')->where('department_id', '=', $deptId)->get()->toArray();
        $roles = json_decode(json_encode($roles),true);
        $roleNameExists = false;
        //新建岗位
        if(!$roleId){
            foreach($roles as $k=>$v){
                if($v['display_name'] == $roleDisplayName){
                    $roleNameExists = true;
                    break;
                }
            }
        }
        //编辑岗位
        else{
            $role = DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->first();
            $role = json_decode(json_encode($role),true);
            if($role['department_id'] = $deptId){
                foreach($roles as $k=>$v){
                    if($v['display_name'] == $roleDisplayName && $v['id'] != $roleId){
                        $roleNameExists = true;
                        break;
                    }
                }
            }
            else{
                foreach($roles as $k=>$v){
                    if($v['display_name'] == $roleDisplayName){
                        $roleNameExists = true;
                        break;
                    }
                }
            }
        }

        if($roleNameExists){
            return array('status'=>2, 'msg'=>'Name exists.');
        }

        //1：复制权限 2：手动设置
        $permissions = array();
        $permSettings = $request->input('perm_settings');
        if($permSettings == 1){
            //1：复制部门 2：复制人员
            $copyType = $request->input('copy_type');
            if($copyType==1){
                $selectedDeptId = $request->input('selected_department_id');
                $permissions = $this->getDeptPermIds($selectedDeptId);
            }
            else if($copyType==2){
                $selectedUserId = $request->input('selected_user_id');
                $permissions = $this->getUserPermIds($selectedUserId);
            }

        }
        else{
            $permissions = $request->input('selected_permissions');
        }

        $updateArray = array();
        $updateArray['display_name']= $roleDisplayName;
        $deptIdDisplayNamePairs = $this->getDeptIdDisplayNamePairs();
        $updateArray['name'] = array_get($deptIdDisplayNamePairs, $deptId, '').'-'.$roleDisplayName;
        $updateArray['department_id'] = $deptId;
        $updateArray['title_id'] = $titleId;

        $returnData = array();
        $returnData['status'] = 1;

        //新建岗位
        if(!$roleId){
            $updateArray['created_at'] = date('Y-m-d H:i:s');
            $updateArray['updated_at'] = date('Y-m-d H:i:s');

            DB::beginTransaction();
            if(DB::connection('amazon')->table('roles')->insert($updateArray)){
                $newRole = DB::connection('amazon')->table('roles')->orderBy('id', 'desc')->first();
                $newRoleId = $newRole->id;
                foreach($permissions as $v){
                    $updatePr = array('permission_id'=>$v, 'role_id'=>$newRoleId);
                    DB::connection('amazon')->table('permission_role')->insert($updatePr);
                }
                DB::commit();

                $returnData['msg'] = 'Created successfully.';
                return $returnData;
            }
            else{
                DB::rollback();
                return array('status'=>2, 'msg'=>'Failed to create.');
            }
        }

        //编辑岗位
        else{
            $updateArray['updated_at'] = date('Y-m-d H:i:s');
            $role = DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->first();
            $role = json_decode(json_encode($role),true);
            $oldDeptId = $role['department_id'];

            DB::beginTransaction();
            if(DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->update($updateArray)){
                //编辑岗位时，如果选了复制权限，但没有选择部门或人员，仍保留之前的权限
                $copyPermsEmpty = false;
                if($permSettings == 1){
                    if(!$copyType){
                        $copyPermsEmpty = true;
                    }
                    else if($copyType==1 && !$selectedDeptId){
                        $copyPermsEmpty = true;
                    }
                    else if($copyType==2 && !$selectedUserId){
                        $copyPermsEmpty = true;
                    }
                }
                if(!$copyPermsEmpty){
                    DB::connection('amazon')->table('permission_role')->where('role_id', '=', $roleId)->delete();
                    foreach($permissions as $v){
                        $updatePr = array('permission_id'=>$v, 'role_id'=>$roleId);
                        DB::connection('amazon')->table('permission_role')->insert($updatePr);
                    }
                }
                //该岗位换到另外一个部门
                if($oldDeptId != $deptId){
                    $userIds = DB::connection('amazon')->table('role_user')->where('role_id', '=', $roleId)->pluck('user_id');
                    $userIds = json_decode(json_encode($userIds),true);

                    DB::connection('amazon')->table('department_user')->whereIn('user_id', $userIds)->delete();
                    foreach($userIds as $v){
                        $updateDu = array('user_id'=> $v, 'department_id'=>$deptId, 'is_leader'=>0);
                        DB::connection('amazon')->table('department_user')->insert($updateDu);
                    }
                }


                DB::commit();

                $returnData['msg'] = 'Updated successfully.';
                return $returnData;
            }
            else{
                DB::rollback();
                return array('status'=>2, 'msg'=>'Failed to update.');
            }
        }

    }

    public function getRolesInDept(Request $request){
        header('Access-Control-Allow-Origin:*');

        $deptId = $request->input('department_id');
        $roles = DB::connection('amazon')->table('roles')->where('department_id', '=', $deptId)->get()->toArray();
        $roles = json_decode(json_encode($roles),true);

        $returnData= array();
        foreach($roles as $k=>$v){
            $returnData[] = array('id'=>$v['id'], 'display_name'=>$v['display_name']);
        }
        return $returnData;
    }

    public function getTitleForRole(Request $request){
        header('Access-Control-Allow-Origin:*');

        $roleId = $request->input('role_id');
        $role = DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->first();
        if(!$role){
            return array('status'=>2);
        }

        return array('status'=>1, 'title_name'=>array_get($this->getTitleIdNamePairs(),$role->title_id));
    }

    public function getPermissionsForRole(Request $request){
        header('Access-Control-Allow-Origin:*');

        $roleId = $request->input('role_id');
        $role = DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->first();
        if(!$role){
            return array('status'=>2);
        }

        $ids = DB::connection('amazon')->table('permission_role')->where('role_id','=',$roleId)->pluck('permission_id');
        $ids = json_decode(json_encode($ids),true);
        return array('status'=>1, 'permissions'=>$ids);
    }

//    public function createUserInfo(Request $request){
//        header('Access-Control-Allow-Origin:*');
//
//        $returnData= array();
//        $returnData['status'] = 1;
//        return $returnData;
//    }

    public function editUserInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $userId = $request->input('user_id');
        $user = DB::connection('amazon')->table('users')->where('id', '=', $userId)->first();
        $user = json_decode(json_encode($user),true);
        if(!$user){
            return array('status'=>2, 'msg'=>'User does not exist.');
        }
        $role_user = DB::connection('amazon')->table('role_user')->where('user_id', '=', $userId)->first();
        $roleId = $role_user->role_id;
        $role = DB::connection('amazon')->table('roles')->where('id', '=', $roleId)->first();
        $role = json_decode(json_encode($role),true);
        $departmentUser = DB::connection('amazon')->table('department_user')->where('user_id', '=', $userId)->where('department_id', '=', $role['department_id'])->first();

        $returnData= array();
        $returnData['status'] = 1;
        $returnData['email'] = $user['email'];
        $returnData['name'] = $user['name'];
        $returnData['english_name'] = $user['english_name'];
        $returnData['dingtalk_id'] = $user['dingtalk_id'];
        $returnData['department_id'] = $role['department_id'];
        $returnData['is_leader'] = $departmentUser->is_leader;
        $returnData['role_id'] = $roleId;
        $returnData['title_name'] = array_get($this->getTitleIdNamePairs(), $role['title_id']);
        $returnData['user_status'] = $user['status'];
        $ids = $this->getUserPermIds($userId);
        $returnData['user_permissions'] = $ids;

        return $returnData;

    }

    public function saveUserInfo(Request $request){
        header('Access-Control-Allow-Origin:*');

        $userId = $request->input('user_id');
        $email = $request->input('email');
        $name = $request->input('name');
        $englishName = $request->input('english_name');
        $dingtalkId = $request->input('dingtalk_id');
        $departmentId = $request->input('department_id');
        $isLeader = $request->input('is_leader');
        $roleId = $request->input('role_id');
        $password = $request->input('password');
        $passwordConfirm = $request->input('password_confirm');
        $status = $request->input('user_status');

        //新建人员
        if(!$userId){
            if(!$email){
                return array('status'=>2, 'msg'=>'Email cannot be blank.');
            }
            $user = DB::connection('amazon')->table('users')->where('email', '=', $email)->first();
            if($user){
                return array('status'=>2, 'msg'=>'Email already exists.');
            }
        }
        if(!$name){
            return array('status'=>2, 'msg'=>'Name cannot be blank.');
        }
        if(!$departmentId){
            return array('status'=>2, 'msg'=>'Department cannot be blank.');
        }
        if(!$roleId){
            return array('status'=>2, 'msg'=>'Role cannot be blank.');
        }
        if(!$userId){
            if(!$password && !$passwordConfirm){
                return array('status'=>2, 'msg'=>'Password cannot be blank.');
            }
            if($password != $passwordConfirm){
                return array('status'=>2, 'msg'=>'The two passwords do not match.');
            }
        }
        else{
            if($password || $passwordConfirm){
                if($password != $passwordConfirm){
                    return array('status'=>2, 'msg'=>'The two passwords do not match.');
                }
            }
        }
        //如果表单里设置该用户为部门长
        if($isLeader == 1){
            $deptUserLeader = DB::connection('amazon')->table('department_user')->where('department_id', '=', $departmentId)->where('is_leader', '=', 1)->first();
            if(!$userId){
                if($deptUserLeader){
                    return array('status'=>2, 'msg'=>'Leader exists in the department.');
                }
            }
            else{
                $departmentUser = DB::connection('amazon')->table('department_user')->where('user_id', '=', $userId)->first();
                $oldDeptId = $departmentUser->department_id;
                if($oldDeptId == $departmentId){
                    if($deptUserLeader && ($userId != $deptUserLeader->user_id)){
                        return array('status'=>2, 'msg'=>'Leader exists in the department.');
                    }
                }
                else{
                    if($deptUserLeader){
                        return array('status'=>2, 'msg'=>'Leader exists in the department.');
                    }
                }
            }
        }

        //1：复制权限 2：手动设置
        $permissions = array();
        $permSettings = $request->input('perm_settings');
        if($permSettings == 1){
            //1：复制部门 2：复制人员
            $copyType = $request->input('copy_type');
            if($copyType==1){
                $selectedDeptId = $request->input('selected_department_id');
                $permissions = $this->getDeptPermIds($selectedDeptId);
            }
            else if($copyType==2){
                $selectedUserId = $request->input('selected_user_id');
                $permissions = $this->getUserPermIds($selectedUserId);
            }

        }
        else{
            $permissions = $request->input('selected_permissions');
        }

        $updateArray = array();
        $updateArray['email']= $email;
        $updateArray['name']= $name;
        $updateArray['english_name']= $englishName;
        $updateArray['dingtalk_id']= $dingtalkId;
        $updateArray['status']= $status;

        $returnData = array();
        $returnData['status'] = 1;

        //新建人员
        if(!$userId){
            $updateArray['created_at'] = date('Y-m-d H:i:s');
            $updateArray['updated_at'] = date('Y-m-d H:i:s');
            $updateArray['password'] = bcrypt($password);

            DB::beginTransaction();
            if(DB::connection('amazon')->table('users')->insert($updateArray)){
                $newUser = DB::connection('amazon')->table('users')->orderBy('id', 'desc')->first();
                $newUserId = $newUser->id;

                $updateDu = array('user_id'=>$newUserId, 'department_id'=>$departmentId, 'is_leader'=>$isLeader);
                DB::connection('amazon')->table('department_user')->insert($updateDu);

                $updateRu = array('user_id'=>$newUserId, 'role_id'=>$roleId);
                DB::connection('amazon')->table('role_user')->insert($updateRu);

                foreach($permissions as $v){
                    $updatePu = array('permission_id'=>$v, 'user_id'=>$newUserId);
                    DB::connection('amazon')->table('permission_user')->insert($updatePu);
                }
                DB::commit();

                $returnData['msg'] = 'Created successfully.';
                return $returnData;
            }
            else{
                DB::rollback();
                return array('status'=>2, 'msg'=>'Failed to create.');
            }
        }

        //编辑人员
        else{
            $updateArray['updated_at'] = date('Y-m-d H:i:s');
            if($password){
                $updateArray['password'] = bcrypt($password);
            }

            DB::beginTransaction();
            if(DB::connection('amazon')->table('users')->where('id', '=', $userId)->update($updateArray)){
                DB::connection('amazon')->table('department_user')->where('user_id', '=', $userId)->delete();
                $updateDu = array('user_id'=>$userId, 'department_id'=>$departmentId, 'is_leader'=>$isLeader);
                DB::connection('amazon')->table('department_user')->insert($updateDu);

                DB::connection('amazon')->table('role_user')->where('user_id', '=', $userId)->delete();
                $updateRu = array('user_id'=>$userId, 'role_id'=>$roleId);
                DB::connection('amazon')->table('role_user')->insert($updateRu);

                //编辑人员时，如果选了复制权限，但没有选择部门或人员，仍保留之前的权限
                $copyPermsEmpty = false;
                if($permSettings == 1){
                    if(!$copyType){
                        $copyPermsEmpty = true;
                    }
                    else if($copyType==1 && !$selectedDeptId){
                        $copyPermsEmpty = true;
                    }
                    else if($copyType==2 && !$selectedUserId){
                        $copyPermsEmpty = true;
                    }
                }
                if(!$copyPermsEmpty){
                    DB::connection('amazon')->table('permission_user')->where('user_id', '=', $userId)->delete();
                    foreach($permissions as $v){
                        $updatePu = array('permission_id'=>$v, 'user_id'=>$userId);
                        DB::connection('amazon')->table('permission_user')->insert($updatePu);
                    }
                }

                DB::commit();

                $returnData['msg'] = 'Updated successfully.';
                return $returnData;
            }
            else{
                DB::rollback();
                return array('status'=>2, 'msg'=>'Failed to update.');
            }
        }
    }

    public function changeUserStatus(Request $request){
        header('Access-Control-Allow-Origin:*');
        $userId = $request->input('user_id');
        $status = $request->input('user_status');
        $user = DB::connection('amazon')->table('users')->where('id', '=', $userId)->first();
        if(!$user){
            return array('status'=>2, 'msg'=>'User does not exist.');
        }
        if(DB::connection('amazon')->table('users')->where('id', '=', $userId)->update(array('status'=>$status))){
            return array('status'=>1, 'msg'=>'updated successfully.');
        }
        else{
            return array('status'=>2, 'msg'=>'Failed to update.');
        }
    }

    //获取给定部门以及所有层级的子部门的信息
    public function getInnerDepartments(Request $request){
        header('Access-Control-Allow-Origin:*');

        $departmentId = $request->input('department_id');
        //获取所选部门的ID以及在此部门下的所有级别部门的ID
        $a = DB::connection('amazon')->table('departments')->select(['id','parent_id'])->get();
        $a = json_decode(json_encode($a),true);
        $allDeptArray = $this->getMenuTree($a, $departmentId, 0);
        $allDeptIds = array();
        $deptIdDisplayNamePairs = $this->getDeptIdDisplayNamePairs();
        $allDeptIds[] = array('id'=>$departmentId, 'display_name'=>array_get($deptIdDisplayNamePairs, $departmentId));
        foreach($allDeptArray as $v){
            $allDeptIds[] = array('id'=>$v['id'], 'display_name'=>array_get($deptIdDisplayNamePairs, $v['id']));
        }

        return $allDeptIds;
    }


    //获取一二级部门的（ID，显示名）对
    public function getRankOneAndTwoIdDisplayNamePairs(){
        $dept = DB::connection('amazon')->table('departments')->select(['id','parent_id', 'display_name'])->get();
        $dept = json_decode(json_encode($dept),true);
        $rankOnePairs = array();
        foreach($dept as $k=>$v){
            if($v['parent_id'] == 0){
                $rankOnePairs[] = array('id'=>$v['id'], 'display_name'=>$v['display_name']);
                unset($dept[$k]);
            }
        }
        $rankPairs = array();
        foreach($rankOnePairs as $k=>$v){
            $rankPairs[] = $v;
            foreach($dept as $k2=>$v2){
                if($v2['parent_id'] == $v['id']){
                    $rankPairs[] = array('id'=>$v2['id'],'display_name'=>($v['display_name'].'-'.$v2['display_name']));
                }
            }
        }
        return $rankPairs;
    }

//    //获取部门的权限ID(编辑部门弹窗时，手动设置权限，该部门的权限处于选中状态)
//    public function getDeptPermissionIDs(Request $request){
//        header('Access-Control-Allow-Origin:*');
//
//        $departmentId = $request->input('department_id');
//        $ids = $this->getDeptPermIDs($departmentId);
//        $returnData= array();
//        $returnData['permissions'] = $ids;
//
//        return $returnData;
//    }

    public function getDeptPermIds($departmentId){
        $ids = DB::connection('amazon')->table('permission_department')->where('department_id','=',$departmentId)->pluck('permission_id');
        $ids = json_decode(json_encode($ids),true);
        return $ids;
    }

//    //获取岗位的权限ID(编辑岗位弹窗时，手动设置权限，该岗位的权限处于选中状态)
//    public function getRolePermissionIds(Request $request){
//        header('Access-Control-Allow-Origin:*');
//
//        $roleId = $request->input('role_id');
//        $ids = $this->getRolePs($roleId);
//        $returnData= array();
//        $returnData['permissions'] = $ids;
//
//        return $returnData;
//    }

    public function getRolePermIds($roleId){
        $ids = DB::connection('amazon')->table('permission_role')->where('role_id','=',$roleId)->pluck('permission_id');
        $ids = json_decode(json_encode($ids),true);
        return $ids;
    }


//    //获取人员的权限ID(编辑人员弹窗时，手动设置权限，该人员的权限处于选中状态)
//    public function getUserPermissionIds(Request $request){
//        header('Access-Control-Allow-Origin:*');
//
//        $userId = $request->input('user_id');
//        $ids = $this->getUserPs($userId);
//        $returnData= array();
//        $returnData['permissions'] = $ids;
//
//        return $returnData;
//    }

    public function getUserPermIds($userId){
        $ids = DB::connection('amazon')->table('permission_user')->where('user_id','=',$userId)->pluck('permission_id');
        $ids = json_decode(json_encode($ids),true);
        return $ids;
    }

    public function getRankDepartmentIds($deptId){
        $rankDepartmentIds = array();
        $departmentId = $deptId;
        $rankDepartmentIds[] = $departmentId;
        while($departmentId > 0){
            $record = DB::connection('amazon')->table('departments')->where('id','=',$departmentId)->first();
            if($record){
                $departmentId = $record->parent_id;
                $rankDepartmentIds[] = $departmentId;
            }
        }
        array_pop($rankDepartmentIds);

        return $rankDepartmentIds;
    }

    public function getDeptIdDisplayNamePairs(){
        $data = DB::connection('amazon')->table('departments')->pluck('display_name','id');
        return $data;
    }

    public function getUserIdNamePairs(){
        $data = DB::connection('amazon')->table('users')->pluck('name','id');
        return $data;
    }

    public function getPermissionIdDisplayNamePairs(){
        $data = DB::connection('amazon')->table('permissions')->pluck('display_name','id');
        return $data;
    }

    public function getRoleIdDisplayNamePairs(){
        $data = DB::connection('amazon')->table('roles')->pluck('display_name','id');
        return $data;
    }

    public function getAllPermissions(){
        header('Access-Control-Allow-Origin:*');

        $permissions = DB::connection('amazon')->table('permissions')->get()->toArray();
        $permissions = json_decode(json_encode($permissions),true);
        $permissions_group=[];
        foreach($permissions as $permission){
            if($permission['parent_id']){
                $permissions_group[$permission['parent_id']]['child'][]= ['id'=>$permission['id'],'display_name'=>$permission['display_name']];
            }else{
                $permissions_group[$permission['id']]['display_name']=$permission['display_name'];
            }
        }
        $perms = array();
        foreach($permissions_group as $k=>$v){
            $perms[] = array('group'=>$v['display_name'], 'id'=>$k);
            if(isset($v['child'])){
                $child = $v['child'];
                foreach($child as $v2){
                    $perms[] = array('opt'=>$v2['display_name'], 'id'=>$v2['id']);
                }
            }
        }

        return $perms;
    }

    public function getAllDepartments(){
        header('Access-Control-Allow-Origin:*');

        $a = DB::connection('amazon')->table('departments')->select(['id','parent_id','display_name'])->get();
        $a = json_decode(json_encode($a),true);
        $allDeptArray = $this->getMenuTree($a, 0, 0);
        foreach($allDeptArray as $k=>$v){
            unset($allDeptArray[$k]['parent_id']);
        }
        $returnData = array();
        $returnData['status'] = 1;
        $returnData['departments'] = $allDeptArray;

        return $returnData;
    }

    public function getAllUsers(){
        header('Access-Control-Allow-Origin:*');

        $a = DB::connection('amazon')->table('users')->select(['id','name'])->get();
        $a = json_decode(json_encode($a),true);
        $returnData = array();
        $returnData['status'] = 1;
        $returnData['departments'] = $a;

        return $returnData;
    }

//    public function getAllTitles(){
//        header('Access-Control-Allow-Origin:*');
//
//        $a = DB::connection('amazon')->table('titles')->select(['id','name'])->get();
//        $a = json_decode(json_encode($a),true);
//        $returnData = array();
//        $returnData['status'] = 1;
//        $returnData['titles'] = $a;
//
//        return $returnData;
//    }


    public function getTitleIdNamePairs(){
        $data = DB::connection('amazon')->table('titles')->pluck('name','id');
        return $data;
    }


    /**
     * 递归无限级分类【先序遍历算】，获取任意节点下所有子孩子
     * @param array $arrCate 待排序的数组
     * @param int $parent_id 父级节点
     * @param int $level 层级数
     * @return array $arrTree 排序后的数组
     */
    public function getMenuTree($arrCat, $parent_id = 0, $level = 0)
    {
        static $arrTree = array(); //使用static代替global
        if(empty($arrCat)) return array();
        $level++;
        foreach($arrCat as $key => $value)
        {
            if($value['parent_id' ] == $parent_id)
            {
                $value[ 'level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); //注销当前节点数据，减少已无用的遍历
                $this->getMenuTree($arrCat, $value['id'], $level);
            }
        }

        return $arrTree;
    }



}