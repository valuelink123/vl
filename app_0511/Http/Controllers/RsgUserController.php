<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\User;


class RsgUserController extends Controller
{
	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
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
	 * @return \Illuminate\Http\Responsefba_transfer
	 */
	public function list(Request $req)
	{
		if(!Auth::user()->can(['rsgUser-show'])) die('Permission denied -- rsgUser-show');
		$date_to = date('Y-m-d');
		$date_from = date('Y-m-d',strtotime($date_to)-7*86400);
		if ($req->isMethod('GET')) {
			return view('rsgUser/index', ['date_from' => $date_from,'date_to' => $date_to]);
		}
		//搜索相关
		$searchField = array('date_from'=>array('>='=>'created_at'),'date_to'=>array('<='=>'created_at'));
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = explode('&',$search);
		$searchData = $this->getSearchData($search);

		$where = $this->getSearchWhereSql($searchData,$searchField);
		if(isset($searchData['status'])){
			if($searchData['status']!=='-1'){
				$where .= ' and status = '.$searchData['status'];
			}
		}

		$limit = $this->dtLimit($req);
		$orderby = $this->dtOrderBy($req);

		$sql = "SELECT SQL_CALC_FOUND_ROWS 
        	id,email,status,created_at 
            from rsg_users 
			where 1 = 1  {$where} 
			order by {$orderby} 
        LIMIT {$limit}";
		$data = $this->queryRows($sql);

		foreach($data as $key=>$val){
			$data[$key]['status'] = 'Inactive';
			if($val['status']==1){
				$data[$key]['status'] = 'Activated';
			}
		}
		$recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}



}