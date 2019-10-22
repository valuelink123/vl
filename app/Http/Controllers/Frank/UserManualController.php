<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;

use Illuminate\Http\Request;
use App\Models\KmsUserManual;
use Illuminate\Support\Facades\Auth;
class UserManualController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
        if(!Auth::user()->can(['partslist-show'])) die('Permission denied -- partslist-show');
		return view('frank/kmsUserManual');
    }

    /**
     * @throws \App\Traits\MysqliException
     */
    public function import() {
		if(!Auth::user()->can(['partslist-create'])) die('Permission denied -- partslist-create');
        $rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

        foreach ($rows as $row) {
            $vars['itemGroupBrandModels'][$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
        }

        return view('frank/kmsUserManualCreate', $vars);
    }

    /**
     * @throws \App\Exceptions\DataImportException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function create(Request $req) {
		if(!Auth::user()->can(['partslist-create'])) die('Permission denied -- partslist-create');
        try {
            $count = KmsUserManual::import($req);
            $errors = ['success' => "Written $count Records."];
        } catch (\Exception $e) {
            $errors = ['error' => $e->getMessage()];
        }

        // qa 加 type dot 连个字段，可选择、可编辑
        // 下载过渡页
        // 可编辑 table
        // search 加强 多关键字 and

        // Blade 中的使用方式:
        // $errors->dataImport->first('field')
        // $errors->dataImport->all('替换 :message 并返回数组')
        // $errors->dataImport->all('<div>:message</div>')
        // 不指定 key 就用默认公共的
        // $errors->all()
        return redirect()->back()->withErrors($errors, 'dataImport');
    }

    /**
     * @throws \App\Traits\MysqliException
     * @throws \App\Traits\DataTablesException
     */
    public function get(Request $req) {

        $where = $this->dtWhere($req, ['t2.sellersku', 't1.brand', 't2.brand_line', 't2.asin', 't2.item_no', 't1.item_group', 't1.item_model'], ['item_group' => 't1.item_group', 'brand' => 't1.brand', 'item_model' => 't1.item_model']);
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);
        // todo UPDATE asin SET xxx=TRIM(IFNULL(xxx, ''))
        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
	    id,
        item_group,
        item_model,
        link,
        updated_at,
        brand,
        t2.brand_line
        FROM kms_user_manual t1
        LEFT JOIN (
          SELECT item_group,brand,item_model,
          GROUP_CONCAT(DISTINCT sellersku) AS sellersku,
          GROUP_CONCAT(DISTINCT brand_line) AS brand_line,
          GROUP_CONCAT(DISTINCT asin) AS asin,
          GROUP_CONCAT(DISTINCT item_no) AS item_no
          FROM asin
          GROUP BY item_group,brand,item_model
        ) t2
        USING(item_group,brand,item_model)
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        $rows = $this->queryRows($sql);
		if(Auth::user()->can(['user-manual-update'])){//有修改编辑权限
			foreach($rows as $key=>$val){
				$rows[$key]['action'] = '<a class="btn btn-danger btn-xs" href="'.url('kms/usermanual/edit?id='.$val['id']).'" target="_blank">Edit</a>';;
			}
		}else{
			//无修改编辑权限
			foreach($rows as $key=>$val){
				$rows[$key]['action'] = '-';
			}
		}


        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }

    /*
     * 编辑某条操作
     */
    public function edit(Request $req)
	{

		if(!Auth::user()->can(['user-manual-update'])) die('Permission denied -- user-manual-update');
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : '';
		$data= KmsUserManual::where('id',$id)->first()->toArray();
		if(!$data){
			$req->session()->flash('error_message','user manual not Exists');
			return redirect('kms/usermanual');
		}

		$rows = $this->queryRows('SELECT item_group,brand,GROUP_CONCAT(DISTINCT item_model) AS item_models FROM asin GROUP BY item_group,brand');

		foreach ($rows as $row) {
			$vars['itemGroupBrandModels'][$row['item_group']][$row['brand']] = explode(',', $row['item_models']);
		}
		return view('frank/kmsUserManualEdit', ['itemGroupBrandModels'=>$vars['itemGroupBrandModels'],'data'=>$data]);
	}

	/*
	 * 更新数据操作
	 */
	public function update(Request $req)
	{
		if(!Auth::user()->can(['user-manual-update'])) die('Permission denied -- user-manual-update');
		try {
			$count = KmsUserManual::import($req);
			$errors = ['success' => "Update $count Records."];
		} catch (\Exception $e) {
			$errors = ['error' => $e->getMessage()];
		}
		return redirect()->back()->withErrors($errors, 'dataImport');
	}

}
