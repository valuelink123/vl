<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use DB;
use App\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function getUserId()
    {
        return Auth::user()->getAuthIdentifier();
    }

	/*
	* 限制数据权限
	* BG领导只能看该BG下的所有asin的相关数据信息
	* BU领导只能看该BU下的所有asin的相关数据信息
	* 只是销售员的话，只能看自己所属的asin相关数据信息
	*/
	public function getAsinWhere($bg = 'asin.bg',$bu = 'asin.bu',$userid = 'processor',$permission='')
	{
		$asinWhere = '';
		if(!Auth::user()->can($permission)){
			if (Auth::user()->seller_rules) {
				$rules = explode("-",Auth::user()->seller_rules);
				if(array_get($rules,0)!='*') $asinWhere .= ' AND '.$bg.' = "'.array_get($rules,0).'"';
				if(array_get($rules,1)!='*') $asinWhere .=  ' AND '.$bu.' = "'.array_get($rules,1).'"';
			} else{
				$asinWhere = ' AND '.$userid.' = '.Auth::user()->id;
			}
		}
		return $asinWhere;
	}

	//得到用户的idName键值对
	public function getUsersIdName(){
		$users = User::get()->toArray();
		$users_array = array();
		foreach($users as $user){
			$users_array[$user['id']] = $user['name'];
		}
		return $users_array;
	}
	//得到所有BG的值
	public function getBgs()
	{
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
		return $bgs;
	}
	//得到所有BU的值
	public function getBus()
	{
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
		return $bus;
	}

}
