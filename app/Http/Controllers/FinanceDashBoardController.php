<?php


namespace App\Http\Controllers;


use App\Models\FinancesShipmentEvents;
use Illuminate\Http\Request;
use App\Models\AmazonSettlementDetail;
use DB;

class FinanceDashBoardController extends Controller
{


	private $shippedOrderModel;


	private $settlementDetailModel;

	//定义查询默认起始时间（yyyy-mm-dd）
	private $default_date_from;

	//定义查询的默认结束时间（yyyy-mm-dd）
	private $default_date_to;


	private $incomeTypes = array('ItemCharge');

	private $promotionTypes = array('Promotion');

	private $shippingFeeTypeIds = array('FBAPerUnitFulfillmentFee', 'ShippingChargeback');

	private $commissionTypeIds = array('Commission');

	/**
	 * FinanceDashBoardController constructor.
	 */
	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();

		$this->shippedOrderModel = new FinancesShipmentEvents();
		$this->settlementDetailModel = new AmazonSettlementDetail();

		// 一般Amazon发货数据获取的时效大约2天
		//设置查询默认起始时间为 2天之前所在月的1日
		$this->default_date_from = date('Y-m', strtotime('-2 days')) . '-01';
		//设置查询默认结束时间为 2天之前的日期
		$this->default_date_to = date('Y-m-d', strtotime('-2 days'));
//		$this->default_date_from = '2020-06-01';
//		$this->default_date_to = '2020-06-31';
//        echo $this->default_date_from,'-----',$this->default_date_to;


	}


	public function index(Request $request)
	{
		$account = DB::connection('vlz')->table('seller_accounts')->where('primary',1)->pluck('label', 'id')->toArray();
		$site = getMarketDomain();//获取站点选项

		$date_from = $request->get('date_from');
		$date_to = $request->get('date_to');
		$seller_account_id = $request->get('account',current(array_keys($account)));

		if (is_null($date_from) || empty(trim($date_from))) {
			$date_from = $this->default_date_from;
		}

		if (is_null($date_to) || empty(trim($date_to))) {
			$date_to = $this->default_date_to;
		}

		if ($date_from > $date_to) {
			$date_from = $date_to;
		}
//		echo $seller_account_id;exit;
		$rates = DB::connection('amazon')->table('currency_rates')->pluck('rate', 'currency');

		$shipped_fees = $this->shippedOrderFinanceCalc($rates, $date_from, $date_to,$seller_account_id);

		$settlement_fees = $this->settlementFinanceCalc($rates, $date_from, $date_to,$seller_account_id);

		$table_th = array('shipment_fee_amount'=>'Shipment Fee Amount', 'order_fee_amount'=>'Order Fee Amount', 'price_amount'=>'Price Amount','item_related_fee_amount'=>'Item Related Fee Amount','misc_fee_amount'=>'misc Fee Amount','other_fee_amount'=>'other Fee Amount','promotion_amount'=>'promotion Amount','direct_payment_amount'=>'direct Payment Amount','other_amount'=>'Other Amount');

		$total_color = array('shipment_fee_amount'=>'#99FFFF', 'order_fee_amount'=>'#99FF00', 'price_amount'=>'#9999FF','item_related_fee_amount'=>'#996600','misc_fee_amount'=>'#2ab4c0','other_fee_amount'=>'#f36a5a','promotion_amount'=>'#8877a9','direct_payment_amount'=>'#5C9BD1','other_amount'=>'#2ab4c0');

		return view('finance/dashboard', array('shipped_fees' => $shipped_fees, 'settlement_fees' => $settlement_fees, 'site' => $site, 'date_from' => $date_from, 'date_to' => $date_to, 'table_th' => $table_th,'total_color'=>$total_color,'account'=>$account,'seller_account_id'=>$seller_account_id));

	}

	/**
	 * 以发货单为准的收入、支出统计
	 * @param object $rates 汇率列表
	 * @param string $date_from 查询起始时间
	 * @param string $date_to 查询结束时间
	 * @return array
	 */
	protected function shippedOrderFinanceCalc($rates, string $date_from, string $date_to,$seller_account_id): array
	{
		$shipped_orders_data = $this->shippedOrderModel
			->where('posted_date', '>=', $date_from)
			->where('posted_date', '<=', $date_to)
			->where('seller_account_id',$seller_account_id)
			->selectRaw('seller_account_id,item_type,type_id,sum( amount ) AS amount,currency')
			->groupBy(['seller_account_id', 'item_type', 'type_id', 'currency'])
			->get();


		$INCOME = 'income';
		$PROMOTION = 'promotion';
		$SHIPPING = 'shipping';
		$COMMISSION = 'commission';
		$OTHER = 'other';


		$result = array($INCOME => 0.00, $PROMOTION => 0.00, $SHIPPING => 0.00, $COMMISSION => 0.00, $OTHER => 0.00);

		foreach ($shipped_orders_data as $data_item) {
			$rate = array_get($rates, $data_item->currency);
			if (in_array($data_item['item_type'], $this->incomeTypes)) {
				//收入计算
				$fee_type = $INCOME;//设置当前数据为收入金额
			} else {
				//费用计算
				if (in_array($data_item['item_type'], $this->promotionTypes)) {
					//promotion 支出
					$fee_type = $PROMOTION;//设置当前数据为promotion金额
				} else {
					if (in_array($data_item['type_id'], $this->shippingFeeTypeIds)) {
						//运费计算
						$fee_type = $SHIPPING;//设置当前数据为运费金额
					} else if (in_array($data_item['type_id'], $this->commissionTypeIds)) {
						//佣金计算
						$fee_type = $COMMISSION;//设置当前数据为佣金金额
					} else {
						//其他费用计算（如:包装费用）
						$fee_type = $OTHER;//设置当前数据为其他费用金额
					}
				}
			}

			//分别累计不同金额类型的金额值
			$result[$fee_type] += round($data_item->amount * $rate, 2);
		}
		$result[$INCOME] = sprintf('%.2f',$result[$INCOME]/10000).'万';
		$result[$PROMOTION] = sprintf('%.2f',$result[$PROMOTION]/10000).'万';
		$result[$SHIPPING] = sprintf('%.2f',$result[$SHIPPING]/10000).'万';
		$result[$COMMISSION] = sprintf('%.2f',$result[$COMMISSION]/10000).'万';
		$result[$OTHER] = sprintf('%.2f',$result[$OTHER]/10000).'万';

		return $result;
	}


	/**
	 * 以结算单为准的收入、支出统计
	 * @param object $rates 汇率列表
	 * @param string $date_from 查询起始时间
	 * @param string $date_to 查询结束时间
	 * @return array
	 */
	protected function settlementFinanceCalc($rates, string $date_from, string $date_to,$seller_account_id): array
	{
		$settlement_data = $this->settlementDetailModel
			->where('posted_date', '>=', $date_from)
			->where('posted_date', '<=', $date_to)
			->where('seller_account_id',$seller_account_id)
			->selectRaw('
                seller_account_id,
                transaction_type,
                sum( shipment_fee_amount ) AS shipment_fee_amount,
                sum( order_fee_amount ) AS order_fee_amount,
                sum( price_amount ) AS price_amount,
                sum( item_related_fee_amount ) AS item_related_fee_amount,
                sum( misc_fee_amount ) AS misc_fee_amount,
                sum( other_fee_amount ) AS other_fee_amount,
                sum( promotion_amount ) AS promotion_amount,
                sum( direct_payment_amount ) AS direct_payment_amount,
                sum( other_amount ) AS other_amount,
                currency 
            ')
			->groupBy(['seller_account_id', 'transaction_type', 'currency'])
			->get();

		$result = array();


		//分别累计各交易类型产生的金额

		$result['total']['shipment_fee_amount']= 0.0;
		$result['total']['order_fee_amount']= 0.0;
		$result['total']['price_amount']= 0.0;
		$result['total']['item_related_fee_amount']= 0.0;
		$result['total']['misc_fee_amount'] = 0.0;
		$result['total']['other_fee_amount']= 0.0;
		$result['total']['promotion_amount'] = 0.0;
		$result['total']['direct_payment_amount']= 0.0;
		$result['total']['other_amount'] = 0.0;

		foreach ($settlement_data as $data_item) {
			$rate = array_get($rates, $data_item->currency);
			if (!isset($result[$data_item['transaction_type']])) {
				//运费
				$result[$data_item['transaction_type']]['shipment_fee_amount'] = round($data_item->shipment_fee_amount * $rate, 2);
				//订单
				$result[$data_item['transaction_type']]['order_fee_amount'] = round($data_item->order_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['price_amount'] = round($data_item->price_amount * $rate, 2);
				$result[$data_item['transaction_type']]['item_related_fee_amount'] = round($data_item->item_related_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['misc_fee_amount'] = round($data_item->misc_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['other_fee_amount'] = round($data_item->other_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['promotion_amount'] = round($data_item->promotion_amount * $rate, 2);
				$result[$data_item['transaction_type']]['direct_payment_amount'] = round($data_item->direct_payment_amount * $rate, 2);
				$result[$data_item['transaction_type']]['other_amount'] = round($data_item->other_amount * $rate, 2);
			} else {
				$result[$data_item['transaction_type']]['shipment_fee_amount'] += round($data_item->shipment_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['order_fee_amount'] += round($data_item->order_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['price_amount'] += round($data_item->price_amount * $rate, 2);
				$result[$data_item['transaction_type']]['item_related_fee_amount'] += round($data_item->item_related_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['misc_fee_amount'] += round($data_item->misc_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['other_fee_amount'] += round($data_item->other_fee_amount * $rate, 2);
				$result[$data_item['transaction_type']]['promotion_amount'] += round($data_item->promotion_amount * $rate, 2);
				$result[$data_item['transaction_type']]['direct_payment_amount'] += round($data_item->direct_payment_amount * $rate, 2);
				$result[$data_item['transaction_type']]['other_amount'] += round($data_item->other_amount * $rate, 2);
			}

			$result['total']['shipment_fee_amount'] += round($data_item->shipment_fee_amount * $rate, 2);
			$result['total']['order_fee_amount'] += round($data_item->order_fee_amount * $rate, 2);
			$result['total']['price_amount'] += round($data_item->price_amount * $rate, 2);
			$result['total']['item_related_fee_amount'] += round($data_item->item_related_fee_amount * $rate, 2);
			$result['total']['misc_fee_amount'] += round($data_item->misc_fee_amount * $rate, 2);
			$result['total']['other_fee_amount'] += round($data_item->other_fee_amount * $rate, 2);
			$result['total']['promotion_amount'] += round($data_item->promotion_amount * $rate, 2);
			$result['total']['direct_payment_amount'] += round($data_item->direct_payment_amount * $rate, 2);
			$result['total']['other_amount'] += round($data_item->other_amount * $rate, 2);
		}
		foreach($result['total'] as $key=>$value) {
			$result['total'][$key] = round($value / 10000, 2) . '万';
		}

		return $result;

	}

}