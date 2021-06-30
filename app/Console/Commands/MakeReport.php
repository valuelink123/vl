<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MakeReport extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'make:report {--id=}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Log::Info('make:report start ');
		$id =$this->option('id');
		$data = DB::table('report')->where('id',$id)->first();

		if($data) {
			$where = " finances_shipment_events.posted_date >= '" . $data->from_date . " 00:00:00' and finances_shipment_events.posted_date <= '" . $data->to_date . " 23:59:59'";
			if (isset($data->account) && $data->account) {
				$where .= " and seller_account_id in (" . $data->account . ")";
			}

			$sql = "SELECT
						seller_accounts.label AS account,
						finances_shipment_events.amazon_order_id,
						finances_shipment_events.seller_order_id,
						finances_shipment_events.marketplace_name,
						finances_shipment_events.posted_date,
						finances_shipment_events.order_item_id,
						finances_shipment_events.seller_sku,
						finances_shipment_events.line_num,
						finances_shipment_events.quantity_shipped,
						finances_shipment_events.item_type,
						finances_shipment_events.type,
						finances_shipment_events.type_id,
						finances_shipment_events.amount,
						finances_shipment_events.currency,
						finances_shipment_events.dom_type,
						finances_shipment_events.order_adjustment_item_id
					FROM
						finances_shipment_events
					LEFT JOIN seller_accounts ON (finances_shipment_events.seller_account_id=seller_accounts.id)
					WHERE 1 = 1 and {$where}";
			$query_data = DB::connection('amazon')->select($sql);
			$query_data = json_decode(json_encode($query_data), true);

			$arrayData = array();
			$headArray[] = 'account';
			$headArray[] = 'amazon_order_id';
			$headArray[] = 'seller_order_id';
			$headArray[] = 'marketplace_name';
			$headArray[] = 'posted_date';

			$headArray[] = 'order_item_id';
			$headArray[] = 'seller_sku';
			$headArray[] = 'line_num';
			$headArray[] = 'quantity_shipped';
			$headArray[] = 'item_type';

			$headArray[] = 'type';
			$headArray[] = 'type_id';
			$headArray[] = 'amount';
			$headArray[] = 'currency';
			$headArray[] = 'dom_type';
			$headArray[] = 'order_adjustment_item_id';
			$arrayData[] = $headArray;

			foreach ($query_data as $key => $val) {
				$arrayData[] = array(
					$val['account'],
					$val['amazon_order_id'],
					$val['seller_order_id'],
					$val['marketplace_name'],
					$val['posted_date'],

					$val['order_item_id'],
					$val['seller_sku'],
					$val['line_num'],
					$val['quantity_shipped'],
					$val['item_type'],


					$val['type'],
					$val['type_id'],
					$val['amount'],
					$val['currency'],
					$val['dom_type'],
					$val['order_adjustment_item_id'],

				);
			}

			if($arrayData) {
				$spreadsheet = new Spreadsheet();
				$spreadsheet->getActiveSheet()
					->fromArray(
						$arrayData,  // The data to set
						NULL,        // Array values with this value will not be set
						'A1'         // Top left coordinate of the worksheet range where
					//    we want to set these values (default is A1)
					);

				$file_path = $data->file_path;

				$path = dirname($file_path);

				$path_url = './public' . $path;

				try {
					if (!is_dir($path_url)) {
						mkdir($path_url, 777, true);
					}
					$writer = new Xlsx($spreadsheet);
					$new_file_path = './public' . $file_path;
					$writer->save($new_file_path);
					//成功
					DB::table('report')->where('id',$id)->update(['status'=>1,'updated_at'=>date('Y-m-d H:i:s')]);
				} catch (\Exception $e) {
					//失败
					DB::table('report')->where('id',$id)->update(['status'=>2,'updated_at'=>date('Y-m-d H:i:s')]);
				}
			}
			die();
		}
		Log::Info('make:report end ');
	}


}
