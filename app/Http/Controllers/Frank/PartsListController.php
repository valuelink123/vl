<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.12
 * Time: 17:53
 */

namespace App\Http\Controllers\Frank;
use Illuminate\Support\Facades\Auth;
use App\Asin;
use App\Classes\SapRfcRequest;
use App\Exceptions\DataInputException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SellerAccountsStatusRecord;
class PartsListController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    public function index() {
		if(!Auth::user()->can(['partslist-show'])) die('Permission denied -- partslist-show');
		$sql = "
        SELECT seller_name,any_value(account_status) as account_status FROM kms_stock group by seller_name";
		$_sellerName = $this->queryRows($sql);
		$sellerName = array();
		foreach($_sellerName as $key=>$val){
			if($val['seller_name']){
				$sellerName[$val['seller_name']]['seller_name'] = $val['seller_name'];
				$sellerName[$val['seller_name']]['class'] = '';
				if($val['account_status']==1){
					$sellerName[$val['seller_name']]['class'] = 'invalid-account';
				}
			}
		}
        return view('frank/kmsPartsList',['sellerName'=>$sellerName]);
    }

    /**
     * 查询产品配件
     * @param $item_code
     * @return array
     */
    private function subItemCodes($item_code) {

        $subCodes = [];

        $sap = new SapRfcRequest();
        $rows = $sap->getAccessories(['sku' => $item_code]);
        foreach ($rows as $row) {
            $subCodes[] = $row['IDNRK'];
        }
		
        return $subCodes;
    }

    public function getSubItemList(Request $req) {

        try {
            $subCodes = $this->subItemCodes($req->input('item_code'));
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }

        if (empty($subCodes)) return [];

        // kms_stock 为 fba_stockk + fbm_stock 的视图

		$rows = DB::table('kms_stock')->leftJoin('asin', function ($join) {
			$join->on('asin.asin', '=', 'kms_stock.asin')->on('asin.site','=','kms_stock.site')
				->ON('asin.sellersku', '=', 'kms_stock.seller_sku');
				})
            ->select('item_code', 'kms_stock.asin', 'kms_stock.fba_stock', 'kms_stock.fba_transfer', 'kms_stock.fbm_stock', 'item_name', 'seller_name', 'seller_sku','account_status','asin.site','fba_update','fbm_update')
            ->whereIn('item_code', $subCodes)
            ->get();
		$validStock = $this->getFbmAccsStock();
		//账号机的状态为1的时候，表示为无效账号机，此时标红显示
		$unsellableData = $this->getUnsellableData();//获取unsellable数据
		foreach($rows as $key=>$val){
			if($val->account_status==1){
				$rows[$key]->seller_name = '<div class="invalid-account">'.$val->seller_name.'</div>';
			}
			if(isset($validStock[$val->item_code]) && is_array($validStock[$val->item_code])){
				$valid = '';
				foreach($validStock[$val->item_code] as $k=>$v){
					$valid = $valid.$k.':'.$v.'<br>';
				}
				$rows[$key]->fbm_valid_stock = $valid;
			}else{
				$rows[$key]->fbm_valid_stock = 0;
			}
			//设置unsellable数量
			$rows[$key]->unsellable = 0;
			if(isset($unsellableData[$val->seller_sku.'-'.$val->asin])){
				$rows[$key]->unsellable = $unsellableData[$val->seller_sku.'-'.$val->asin];
			}
			$rows[$key]->asin = '<a href="https://'.$val->site.'/dp/'.$val->asin.'" target="_blank" rel="noreferrer">'.$val->asin.'</a>';
		}
		return $rows;

        // 改用视图
        // return DB::table('fba_stock AS t1')
        //     ->select(DB::raw('t1.item_code,t1.asin,t1.fba_stock,t1.fba_transfer,t2.fbm_stock,t2.item_name'))
        //     ->join('fbm_stock AS t2', 't1.item_code', '=', 't2.item_code')
        //     ->whereIn('t1.item_code', $subCodes)
        //     ->get();
        // ->toSql();

        // DB::enableQueryLog();
        // $user = User::get();
        // $query = DB::getQueryLog();
        // print_r($query);
    }

    /**
     * @throws \App\Traits\DataTablesException
     * @throws \App\Traits\MysqliException
     */
    public function get(Request $req) {
		if(!Auth::user()->can(['partslist-show'])) die('Permission denied -- partslist-show');
        $where = $this->dtWhere($req, ['item_code', 'item_name', 't1.asin', 'seller_id', 'seller_name', 'seller_sku'], ['item_group' => 't3.item_group', 'brand' => 't3.brand', 'item_model' => 't3.item_model']);

        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        // FROM fba_stock t1
        // INNER JOIN fbm_stock t2
        // USING(item_code)
        // 由于 INNER JOIN 导致数据不全，弃用

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
        t1.item_code,
        t1.seller_name,
        t1.asin,
        t1.seller_sku,
        t1.fba_stock,
        t1.fba_transfer,
        t1.fbm_stock,
        t1.item_name,
        asin.site as site,
	    t1.account_status,
	    t1.fba_update,
	    t1.fbm_update

        FROM kms_stock t1
        LEFT JOIN (
            SELECT
            ANY_VALUE(item_group) AS item_group,
            ANY_VALUE(brand) AS brand,
            ANY_VALUE(item_model) AS item_model,
            ANY_VALUE(site) as site,
            item_no AS item_code
            FROM asin
            GROUP BY item_no
        ) t3
        USING(item_code) 
        LEFT JOIN asin on t1.asin = asin.asin and t1.seller_sku = asin.sellersku and t1.site=asin.site 
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        $rows = $this->queryRows($sql);
        $validStock = $this->getFbmAccsStock();

		$unsellableData = $this->getUnsellableData();//获取unsellable数据

        foreach($rows as $key=>$val){
        	//账号机的状态为1的时候，表示为无效账号机，此时标红显示
        	if($val['account_status']==1){
				$rows[$key]['seller_name'] = '<div class="invalid-account">'.$val['seller_name'].'</div>';
			}
        	//AG0090
			$rows[$key]['fbm_valid_stock'] = 0;
        	if(isset($validStock[$val['item_code']]) && is_array($validStock[$val['item_code']])){
        		$valid = '';
				foreach($validStock[$val['item_code']] as $k=>$v){
					$valid = $valid.$k.':'.$v.'<br>';
				}

				$rows[$key]['fbm_valid_stock'] = $valid;
			}
			//设置unsellable数量
			$rows[$key]['unsellable'] = 0;
			if(isset($unsellableData[$val['seller_sku'].'-'.$val['asin']])){
				$rows[$key]['unsellable'] = $unsellableData[$val['seller_sku'].'-'.$val['asin']];
			}
			//在列表显示的asin加超链接，点击即可跳转到Amazon商品页面
			$rows[$key]['asin'] = '<a href="https://'.$val['site'].'/dp/'.$val['asin'].'" target="_blank" rel="noreferrer">'.$val['asin'].'</a>';
		}

        $total = $this->queryOne('SELECT FOUND_ROWS()');

        return ['data' => $rows, 'recordsTotal' => $total, 'recordsFiltered' => $total];
    }
	
	
	/*
     * 下载数据
     */
    public function export()
	{
		$sql = "
        SELECT SQL_CALC_FOUND_ROWS
        t1.item_code,
        t1.seller_name,
        t1.asin,
        t1.seller_sku,
        t1.fba_stock,
        t1.fba_transfer,
        t1.fbm_stock,
        t1.item_name,
        asin.site as site,
	    t1.account_status,
	    t1.fba_update,
	    t1.fbm_update

        FROM kms_stock t1
        LEFT JOIN (
            SELECT
            ANY_VALUE(item_group) AS item_group,
            ANY_VALUE(brand) AS brand,
            ANY_VALUE(item_model) AS item_model,
            ANY_VALUE(site) as site,
            item_no AS item_code
            FROM asin
            GROUP BY item_no
        ) t3
        USING(item_code) 
        LEFT JOIN asin on t1.asin = asin.asin and t1.seller_sku = asin.sellersku
        ";

        $rows = $this->queryRows($sql);
        $validStock = $this->getFbmAccsStock();

		$unsellableData = $this->getUnsellableData();//获取unsellable数据

        foreach($rows as $key=>$val){
			$rows[$key]['fbm_valid_stock'] = 0;
        	if(isset($validStock[$val['item_code']]) && is_array($validStock[$val['item_code']])){
        		$valid = '';
				foreach($validStock[$val['item_code']] as $k=>$v){
					$valid = $valid.$k.':'.$v.'<br>';
				}

				$rows[$key]['fbm_valid_stock'] = $valid;
			}
			//设置unsellable数量
			$rows[$key]['unsellable'] = 0;
			if(isset($unsellableData[$val['seller_sku'].'-'.$val['asin']])){
				$rows[$key]['unsellable'] = $unsellableData[$val['seller_sku'].'-'.$val['asin']];
			}
			//在列表显示的asin加超链接，点击即可跳转到Amazon商品页面
			$rows[$key]['asin'] = $val['asin'];
		}
		$arrayData = array();
		$arrayData[] = array('Sku','Seller Name','Asin','Site','Seller SKU','Item Name','Fbm Stock','Fbm Valid Stock','Fba Stock','Fba Transfer','Unsellable');
		foreach ($rows as $key=>$val){
			$arrayData[] = array(
				$val['item_code'],
				$val['seller_name'],
				$val['asin'],
                $val['site'],
				$val['seller_sku'],
				$val['item_name'],
				$val['fbm_stock'],
				$val['fbm_valid_stock'],
				$val['fba_stock'],
				$val['fba_transfer'],
				$val['unsellable']
			);
		}
		if($arrayData){
			$spreadsheet = new Spreadsheet();

			$spreadsheet->getActiveSheet()
				->fromArray(
					$arrayData,  // The data to set
					NULL,        // Array values with this value will not be set
					'A1'         // Top left coordinate of the worksheet range where
				//    we want to set these values (default is A1)
				);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
			header('Content-Disposition: attachment;filename="Export_Inventory.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

    /**
     * @throws DataInputException
     * @throws \App\Traits\MysqliException
     */
    public function getStockList(Request $req) {

        $item_code = $req->input('item_code');
		$countryCode = $req->input('countryCode');
//		$item_code = 'MP0601';//测试数据

        if (empty($item_code)) {
            // 查 fba
            $result = DB::table('fba_stock')
                ->select('item_code')
                ->where('seller_id', $req->input('seller_id'))
                ->where('seller_sku', $req->input('seller_sku'))
				->where('account_status',0)
                ->whereNotNull('item_code')
                ->get();
            // fba 查不到，再查 fbm
            if ($result->isEmpty()) {

                $asinRow = Asin::select('item_no')
                    ->where('site', $req->input('site'))
                    ->where('asin', $req->input('asin'))
                    ->where('sellersku', $req->input('seller_sku'))->first();

                if (empty($asinRow)) {
                    return [];
                } else {
                    $item_code = $asinRow->item_no;
                }

            } else {
                $item_code = $result[0]->item_code;
            }
        }

        if (empty($item_code) || !preg_match('#^[A-z0-9_-]+$#', $item_code)) {
            throw new DataInputException("Wrong Item No: {$item_code}");
        }
        $accountStatusModel = new SellerAccountsStatusRecord();
		$accountInfo = $accountStatusModel->getEnableAccountInfo();
		$accountInfo = array_keys($accountInfo);
		$accountInfo = implode("','",$accountInfo);
		$accountInfo = "'".$accountInfo."'";

        $rows = $this->queryRows(
            "SELECT
                item_code,
                seller_id,
                seller_name,
                seller_sku,
                item_name,
                fba_stock AS stock
            FROM
                fba_stock
            LEFT JOIN fbm_stock USING (item_code)
            WHERE
                item_code = '{$item_code}' 
			AND account_status = 0
			and CONCAT(site,'_',seller_id) in({$accountInfo})
            UNION
            SELECT
                item_code,
                'FBM' AS seller_id,
                'FBM' AS seller_name,
                CONCAT('ITEM CODE - ', item_code) AS seller_sku,
                item_name,
                fbm_stock AS stock
            FROM
                fbm_stock
            WHERE
                item_code = '{$item_code}'"
        );

		$validStock = $this->getFbmAccsStock();
		$storeByCountryCode= getCountryCode();
        foreach ($rows as $key=>$row) {
            // 全部转成 INT，避免 JS 数字字符串的陷阱(比较、加法会出麻烦)
            // 通过配置 PDO、MYSQLI 可以默认返回数字
            // PDO::ATTR_STRINGIFY_FETCHES
            // MYSQLI_OPT_INT_AND_FLOAT_NATIVE
			$rows[$key]['stock'] = (int)$row['stock'];
            if($row['seller_id']=='FBM'){
				if(isset($validStock[$row['item_code']]) && is_array($validStock[$row['item_code']])){
					$valid = '';
					foreach($validStock[$row['item_code']] as $k=>$v){
						if($countryCode && in_array($k,$storeByCountryCode[$countryCode]['store'])){
							$valid = $valid.$k.':'.$v.',';
						}
					}
					$rows[$key]['stock'] = $valid;
					if(empty($valid)){
						unset($rows[$key]);
					}
				}else{
					unset($rows[$key]);
				}
			}
        }

        return $rows;
    }

    /*
     * 设置账号机有效或者是无效
     */
    public function updateStatus(Request $req)
	{
		$seller_name = isset($_POST['seller_name']) ? $_POST['seller_name'] : '';
		$status = isset($_POST['status']) ? $_POST['status'] : '';
		DB::table('fba_stock')->whereIn('seller_name',$seller_name)->update(array('account_status'=>$status));
		return [true];
	}
	/*
	 * 得到有效的fbm库存数量
	 */
	public function getFbmAccsStock()
	{
		$sql = "SELECT MATNR,concat(WERKS,'-',LGORT) as WerksLgort,sum(LABST) as stock from fbm_accs_stock where LABST>0 
        AND (WERKS<>'US04' OR LGORT<>'US2') group by MATNR,WerksLgort";
		$_data = $this->queryRows($sql);
		$data = array();
		foreach($_data as $key=>$val){
			$data[$val['MATNR']][$val['WerksLgort']] = $val['stock'];
		}
		return $data;

	}
	/*
	 * 获取unsellable数据,以sellersku和asin组合为唯一键，查询最新的一条数据的UNSELLABLE的数量
	 */
	public function getUnsellableData()
	{
		$sql = "SELECT CONCAT(`SellerSku`,'-',`Asin`) as skuasin,Quantity 
				FROM amazon_fba_stock as a 
				JOIN(
					SELECT ANY_VALUE(SellerSku) as t_sku,ANY_VALUE(Asin) as t_asin,CONCAT(`SellerSku`,'-',`Asin`) as skuasin,max(SnapshotDate) as maxdate
					FROM amazon_fba_stock 
					where WarehouseConditionCode='UNSELLABLE' 
					group by skuasin
				) as t on SellerSku = t_sku and Asin=t_asin and a.SnapshotDate=t.maxdate
				where WarehouseConditionCode='UNSELLABLE' ";
		$_unsellableData= DB::connection('order')->select($sql);
		$unsellableData = array();
		foreach($_unsellableData as $key=>$val){
			$unsellableData[$val->skuasin] = $val->Quantity;
		}
		return $unsellableData;
	}

}
