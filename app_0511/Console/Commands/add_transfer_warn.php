<?php
/* Date: 2019.9.21
 * Author: wulanfnag
 *
 * 添加库存预警数据
 * 此脚本每天跑一次，属于统计数据
 * FBM-FMA的物流周期和上架时效的物流周期是根据站点来给予不同的天数
 * FBM-FMA的物流周期【JP=10天，EU=12天，US=20天】
 * 上架时效的物流周期【JP=2天，EU=3天，US=3天】
备注：JP的站点：www.amazon.co.jp-
US的站点：www.amazon.com-
         www.amazon.ca
EU的站点：www.amazon.co.uk-
         www.amazon.de-
         www.amazon.it-
         www.amazon.es-
         www.amazon.fr-
FBM库存要显示良品仓的库存数量：
备注：良品仓如下：US02	US2	CNC美国西仓
				US02	US6	DE WELL美国西仓
				JP02	CJP2	日本大熊猫东京仓
				GR02	CGR2	德国E-GATE仓
				GR04	GR4	德国中欧快铁仓
				UK02	UK3	英国Effecual仓
				CZ02	CZ2	捷克4-PX仓

FBA库存在Inventory Inquiry 中有，FBA库存为fba stock
FBA周转中(fba_transfer):
fbm->fba在途中数据为Inventory Inquiry 中fba transfer,
fbm->fba出库中(fbmfba_sorting):
fba库存数据和fbm->fba在途中数据，有多条数据的时候，随机读取一条(因fba库存表是以`seller_id`,`asin`,`seller_sku`为维度，所以可能同时存在多条数据)

 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class AddTransferWarn extends Command
{
	use \App\Traits\Mysqli;
	protected $signature = 'add:transfer_warn';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	protected $date = '';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	public function __destruct()
	{

	}

	//添加库存预警数据
	function handle()
	{
		set_time_limit(0);
		$this->date = $date = date('Y-m-d');
		// $this->date = '2019-06-01';//测试日期
		$yesterday = date('Y-m-d',strtotime($date)-86400);
		$ago_7day = date('Y-m-d',strtotime($date)-7*86400);
		Log::Info('Add Transfer Warn Start...');
		DB::connection()->enableQueryLog(); // 开启查询日志
		//得到FBM良品仓的库存数量
		$goodStoreHouse = getGoodStoreHouse();
		$sql = "SELECT MATNR,sum(LABST) as stock 
				from fbm_accs_stock 
				where LABST>0 and concat(WERKS,'_',LGORT) in('".join("','",$goodStoreHouse)."') 
				group by MATNR";
		Log::Info($sql);
		$_data = $this->queryRows($sql);
		$fbmData = array();
		foreach($_data as $key=>$val){
			$fbmData[$val['MATNR']]= $val['stock'];
		}

		$sql = "select any_value(asin.asin) as asin,any_value(asin.site) as site,any_value(asin.sellersku) as sellersku,any_value(asin.item_no) as item_no, any_value(sale.avg) as avg_sales,any_value(fba_stock.fba_stock) as fba_stock,any_value(fba_stock.fba_transfer) as fba_transfer,any_value(fbm_stock.fbm_stock) as fbm_stock 
				from asin 
				join ( 
					select min(status) as status,sellersku,site from asin group by sellersku,site
				) as b on asin.sellersku=b.sellersku and asin.site=b.site and asin.status=b.status 
				left join (
				  select sum(sales)/7 as avg,any_value(asin) as asin,any_value(domain) as domain from star_history where create_at >= '$ago_7day' and create_at <= '$yesterday' group by asin,domain 
				) as sale on sale.domain = asin.site and sale.asin=asin.asin 
				left join fba_stock on fba_stock.asin = asin.asin and fba_stock.seller_sku = asin.sellersku 
				left join fbm_stock on fbm_stock.item_code = asin.item_no 
				group by sellersku,site";
		Log::Info($sql);
		$data = $this->queryRows($sql);
		$insertData = array();
		$config = getSiteArr();
		$siteArr = $config['site'];
		$siteKey = array_keys($siteArr);
		foreach($data as $key=>$val){
			$fbmfba_days = $fbmfba_shelfing = 0;
			foreach($siteKey as $v){
				if(in_array($val['site'],$siteArr[$v])){
					$fbmfba_days = $config['fbmfba_days'][$v];
					$fbmfba_shelfing = $config['fbmfba_shelfing'][$v];
				}
			}

			$insertData[] = array(
				'site' => $val['site'],
				'sellersku' => $val['sellersku'],
				'asin' => $val['asin'],
				'date' => $date,
				'avg_sales' => $val['avg_sales'],
				'fbm_stock' => isset($fbmData[$val['item_no']]) ? $fbmData[$val['item_no']] : 0,
				'fba_available' => $val['fba_stock'],
				'fba_transfer' => 0,//FBA周转中(暂没有数据源)
				'fbmfba_transfer' => $val['fba_transfer'],
				'fbmfba_sorting' => 0,//fbm->fba出库中(暂没有数据源)
				'safety_days' => 7,
				'fbmfba_days' => $fbmfba_days,
				'fbmfba_shelfing' => $fbmfba_shelfing,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			);
		}
		if($insertData){
			batchInsert('transfer_warn',$insertData);//调用app/helper/functions.php的batchInsert方法插入数据,可以避免唯一键冲突
		}
		$queries = DB::getQueryLog(); // 获取查询日志
		Log::Info($queries);
		Log::Info('Execution script end...');

	}


}



