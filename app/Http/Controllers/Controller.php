<?php
/* Date: 2019.10.15
 * Author: wulanfnag
 * 一些公共数据处理方法
 */
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

	public function __construct()
	{
		//计算倒计时相关天数,Black Friday=>'11.29','Cyber Monday'=>'12.2','Christmas'=>'12.24'
		$this->middleware(function ($request, $next) {
			$configArr = array(
				array('date'=>'2019-12-24','name'=>'Christmas'),//圣诞节
				array('date'=>'2020-01-01','name'=>'2020'),//元旦
				array('date'=>'2020-01-24','name'=>'Chinese New Year'),//春节
				// array('date'=>'2020-04-04','name'=>'Qingming Festival'),//清明节
			);
			$countDown = array();
			foreach($configArr as $key=>$val){
				$countDown[$key]['name'] = $val['name'];
				$countDown[$key]['day'] = (strtotime(date($val['date']))-strtotime(date('Y-m-d')))/86400;
			}
			session()->put('countDown',$countDown);
			return $next($request);
		});
	}

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

	/*
	 * 得到搜索数据内容的键值对
	 */
	public function getSearchData($search)
	{
		$searchData = array();
		foreach($search as $val){
			$sv = explode('=',$val);
			if(isset($sv[1])){
				$searchData[$sv[0]] = trim($sv[1]);
				if($sv[0]=='date_to'){
					$searchData[$sv[0]] = trim($sv[1]).' 23:59:59';
				}
			}
		}
		return $searchData;
	}

	/*
	 * 得到搜索的where语句
	 * $searchData为搜索内容的键值对，例如array('id'=>1,'name'=>123)
	 * $searchField为搜索字段与查询字段对应关系，例如array('id'=>'a.id','name'=>'a.name')
	 * 如果有起始时间等特殊查询，可用array('date_from'=>array('>='=>'created_at'),'date_to'=>array('<='=>'created_at'));
	 */
	public function getSearchWhereSql($searchData,$searchField)
	{
		$where = '';
		foreach($searchField as $fk=>$fv){
			if(isset($searchData[$fk]) && $searchData[$fk] !=='' ){
				if(is_array($fv)){
					foreach($fv as $vk=>$vv){
						$where .= " and {$vv} {$vk} '".$searchData[$fk]."'";
					}
				}else{
					$where .= " and {$fv} = '".$searchData[$fk]."'";
				}
			}
		}
		return $where;
	}

	/*
	 * 得到item_group的键值对
	 * key为item_group，val为item_group——brand_line
	 */
	public function getItemGroup()
	{
		$data = array();
		$_data = DB::select('SELECT item_group,any_value(brand_line) as brand_line FROM asin group by item_group order by item_group asc');
		foreach($_data as $key=>$val){
			$data[$val->item_group] = $val->item_group.'——'.$val->brand_line;
		}
		return $data;
	}
}
