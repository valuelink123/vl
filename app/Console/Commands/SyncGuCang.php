<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\ThirdWhRequest;
use Illuminate\Support\Facades\Input;
use App\Models\GuCangSku;
use App\Models\GuCangWarehouse;
use App\Models\GuCangShippingMethod;
use App\Models\GuCangInventory;
use App\Models\GuCangOrder;
use App\Models\PlatformOrder;
use App\Models\PlatformOrderItem;
use App\Models\GuCangOrderItem;
use App\Models\GuCangOrderBox;
use App\Models\GuCangOrderFee;
use App\User;

class SyncGuCang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:gucang {--type=} {--afterDate=} {--beforeDate=}';

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
	protected $request;
	
    public function __construct()
    {
        parent::__construct();
		$this->request = new ThirdWhRequest(env('GUCANG_SERVICEURL'),env('GUCANG_APPTOKEN'),env('GUCANG_APPKEY'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type =  $this->option('type');
		switch ($type) {
			case "inventory":
				self::syncInventory();
				break;
			case "warehouse":
				self::syncWarehouse();
				break;
			case "shippingMethod":
				self::syncShippingMethod();
				break;
            case "sku":
                self::syncSku();
                break;
            case "createOrder":
                self::createOrder();
                break;
            case "modifyOrder":
                self::modifyOrder();
                break;
            case "cancelOrder":
                self::cancelOrder();
                break;
			default:
        		self::syncOrder();
				break;
		}	
	}
	
	public function syncInventory()
    {
        $notEnd = true;
        $params['page'] = 0;
        $params['pageSize'] = 50;
        while($notEnd){
            $params['page']++;
            $returnData = $this->request->getProductInventory($params);
            $datas = array_get($returnData,'data');
            if(is_array($datas)){
                foreach($datas as $data){
                    GuCangInventory::updateOrCreate(
                        [
                            'product_sku'=>$data['product_sku'],
                            'warehouse_code'=>$data['warehouse_code'],
                        ],
                        $data
                    );
                }
            }
            if(empty($returnData['nextPage']) || $returnData['nextPage']=='false') $notEnd = false;
        }
	}

    public function syncWarehouse()
    {
        $notEnd = true;
        $params['page'] = 0;
        $params['pageSize'] = 50;
        while($notEnd){
            $params['page']++;
            $returnData = $this->request->getWarehouse($params);
            $datas = array_get($returnData,'data');
            if(is_array($datas)){
                foreach($datas as $data){
                    GuCangWarehouse::updateOrCreate(
                        [
                            'country_code'=>$data['country_code'],
                            'warehouse_code'=>$data['warehouse_code'],
                        ],
                        $data
                    );
                }
            }
            if(empty($returnData['nextPage']) || $returnData['nextPage']=='false') $notEnd = false;
        }
	}

    public function syncShippingMethod()
    {
        $warehouses = GuCangWarehouse::all();
        foreach($warehouses as $warehouse){
            $returnData = $this->request->getShippingMethod(['warehouseCode'=>$warehouse->warehouse_code]);
            $datas = array_get($returnData,'data');
            if(is_array($datas)){
                foreach($datas as $data){
                    GuCangShippingMethod::updateOrCreate(
                        [
                            'code'=>$data['code'],
                            'warehouse_code'=>$data['warehouse_code'],
                        ],
                        $data
                    );
                }
            }
        }
	}


    public function syncSku()
    {
        $notEnd = true;
        $params['page'] = 0;
        $params['pageSize'] = 50;
        $lastSkuModfiyTime = GuCangSku::max('product_modify_time')??date('Y-m-d H:i:s',strtotime('-1 day'));
        $params['product_update_time_from'] = $this->option('afterDate')??date('Y-m-d H:i:s',strtotime($lastSkuModfiyTime)-3600);
        $params['product_update_time_to'] = $this->option('beforeDate')??date("Y-m-d H:i:s");
        while($notEnd){
            $params['page']++;
            $returnData = $this->request->getProductSkuList($params);
            $datas = array_get($returnData,'data');
            if(is_array($datas)){
                foreach($datas as $data){
                    GuCangSku::updateOrCreate(
                        [
                            'product_sku'=>$data['product_sku'],
                        ],
                        $data
                    );
                }
            }
            if(empty($returnData['nextPage']) || $returnData['nextPage']=='false') $notEnd = false;
        }
	}

    public function syncOrder()
    {
        $notEnd = true;
        $params['page'] = 0;
        $params['pageSize'] = 1;
        $lastOrderModfiyTime = GuCangOrder::max('date_modify')??date('Y-m-d H:i:s',strtotime('-1 day'));
        $params['modify_date_from'] = $this->option('afterDate')??date('Y-m-d H:i:s',strtotime($lastOrderModfiyTime)-3600);
        $params['modify_date_to'] = $this->option('beforeDate')??date("Y-m-d H:i:s");
        while($notEnd){
            $params['page']++;
            $returnData = $this->request->getOrderList($params);
            $datas = array_get($returnData,'data');
            if(is_array($datas)){
                foreach($datas as $data){
                    $items = array_get($data,'items');
                    $fee_details = array_get($data,'fee_details');
                    $orderBoxInfo = array_get($data,'orderBoxInfo');
                    unset($data['items']);unset($data['fee_details']);unset($data['orderBoxInfo']);
                    unset($data['order_fee']);
                    $result = GuCangOrder::updateOrCreate(
                        [
                            'order_code'=>$data['order_code'],
                        ],
                        $data
                    );
                    GuCangOrderItem::where('order_code',$result->order_code)->delete();
                    if($items){
                        $itemData = [];
                        foreach ($items as $item) {
                            $itemData[] = new GuCangOrderItem($item);
                        }
                        $result->items()->saveMany($itemData);
                    }

                    GuCangOrderFee::where('order_code',$result->order_code)->delete();
                    if($fee_details){
                        $itemData = [];
                        if(isset($fee_details[0])){
                            foreach ($fee_details as $item) {
                                $itemData[] = new GuCangOrderFee($item);
                            }
                        }else{
                            $itemData[] = new GuCangOrderFee($fee_details);
                        }
                        $result->fee_details()->saveMany($itemData);
                    }

                    GuCangOrderBox::where('order_code',$result->order_code)->delete();
                    if($orderBoxInfo){
                        $itemData = [];
                        if(isset($orderBoxInfo[0])){
                            foreach ($orderBoxInfo as $item) {
                                $itemData[] = new GuCangOrderBox($item);
                            }
                        }else{
                            $itemData[] = new GuCangOrderBox($orderBoxInfo);
                        }
                        $result->orderBoxInfo()->saveMany($itemData);
                    }
                    
                }
            }
            if(empty($returnData['nextPage']) || $returnData['nextPage']=='false') $notEnd = false;
        }
	}


    public function createOrder()
    {
        $orders = PlatformOrder::where('sync',0)->where('sync_status',0)
        ->leftJoin('platform_shipping_methods',function($key){
            $key->on('platform_orders.platform', '=', 'platform_shipping_methods.platform')
            ->on('platform_orders.country_code', '=', 'platform_shipping_methods.country_code')
            ->on('platform_orders.shipping_method', '=', 'platform_shipping_methods.platform_shipping_method');
        })->take(10)->lockForUpdate()->get([
            'platform_orders.id',
            'platform_orders.reference_no',
            'platform_orders.platform',
            'platform_orders.order_desc',
            'platform_shipping_methods.shipping_method',
            'platform_shipping_methods.warehouse_code',
            'platform_orders.fba_shipment_id',
            'platform_orders.fba_shipment_id_create_time',
            'platform_orders.country_code',
            'platform_orders.province',
            'platform_orders.city',
            'platform_orders.address1',
            'platform_orders.address2',
            'platform_orders.address3',
            'platform_orders.zipcode',
            'platform_orders.doorplate',
            'platform_orders.company',
            'platform_orders.name',
            'platform_orders.cell_phone',
            'platform_orders.phone',
            'platform_orders.email',
            'platform_orders.verify',
            'platform_orders.is_shipping_method_not_allow_update',
            'platform_orders.is_signature',
            'platform_orders.is_insurance',
            'platform_orders.insurance_value',
            'platform_orders.is_change_label',
            'platform_orders.age_detection',
            'platform_orders.LiftGate',
			'platform_orders.user_id','platform_orders.order_code',
            ])->toArray();
        foreach($orders as $order){
            $orderId = $order['id'];
			$user_id = $order['user_id'];
			$user = User::find($user_id);
			if(empty($user)) continue;
            $items = PlatformOrderItem::where('platform_order_id',$orderId)
            ->leftJoin('platform_skus',function($key){
                $key->on('platform_order_items.product_sku', '=', 'platform_skus.platform_sku');
            })
            ->where('platform_skus.platform', $order['platform'])
            ->where('platform_skus.country_code',$order['country_code'])
            ->get(['platform_skus.product_sku','platform_order_items.fba_product_code','platform_order_items.quantity','platform_order_items.transaction_id','platform_order_items.item_id'])
            ->toArray();
            $order['items'] = $items;
            unset($order['id']);unset($order['user_id']);
			$order['verify']=1;
            $returnData = $this->request->createOrder($order);
            $updateData = [];
            $updateData['sync_status'] = array_get($returnData,'ask')=='Success'?1:-1;
            $updateData['sync_message'] = array_get($returnData,'message');
            if(array_get($returnData,'ask')=='Success'){
				$updateData['order_code'] = array_get($returnData,'order_code');
				$order['order_code']=array_get($returnData,'order_code');
			}
			$order['user_id'] = $user_id;
			if(array_get($order,'order_code')){
				$user = User::find($user_id);
				$arguments['data'] = [$order];
				$arguments['user_id'] = $user_id;
				$arguments['timestamp'] = time();
				$arguments['sign'] = strtoupper(sha1($arguments['user_id'].$arguments['timestamp'].$user->password));
				$result = curl_request(env('REQUEST_WMS_URL',''),$arguments);
				$result = json_decode($result,true);
				if(array_get($result,'ask')=='Success'){
					$updateData['wms_order_id'] = array_get($result,'data.0.wms_order_id');
					$updateData['wms_deliver_order_id'] = array_get($result,'data.0.wms_deliver_order_id');
				}
			}
            PlatformOrder::where('id',$orderId)->update($updateData);
			
        }
	}

    public function modifyOrder()
    {
        $orders = PlatformOrder::where('sync',1)->where('sync_status',0)
        ->leftJoin('platform_shipping_methods',function($key){
            $key->on('platform_orders.platform', '=', 'platform_shipping_methods.platform')
            ->on('platform_orders.country_code', '=', 'platform_shipping_methods.country_code')
            ->on('platform_orders.shipping_method', '=', 'platform_shipping_methods.platform_shipping_method');
        })->take(10)->lockForUpdate()->get([
            'platform_orders.id',
            'platform_orders.order_code',
            'platform_orders.reference_no',
            'platform_orders.platform',
            'platform_orders.order_desc',
            'platform_shipping_methods.shipping_method',
            'platform_shipping_methods.warehouse_code',
            'platform_orders.fba_shipment_id',
            'platform_orders.fba_shipment_id_create_time',
            'platform_orders.country_code',
            'platform_orders.province',
            'platform_orders.city',
            'platform_orders.address1',
            'platform_orders.address2',
            'platform_orders.address3',
            'platform_orders.zipcode',
            'platform_orders.doorplate',
            'platform_orders.company',
            'platform_orders.name',
            'platform_orders.cell_phone',
            'platform_orders.phone',
            'platform_orders.email',
            'platform_orders.verify',
            'platform_orders.is_shipping_method_not_allow_update',
            'platform_orders.is_signature',
            'platform_orders.is_insurance',
            'platform_orders.insurance_value',
            'platform_orders.is_change_label',
            'platform_orders.age_detection',
            'platform_orders.LiftGate',
			'platform_orders.user_id',
            ])->toArray();
        foreach($orders as $order){
            $orderId = $order['id'];
            $items = PlatformOrderItem::where('platform_order_id',$orderId)
            ->leftJoin('platform_skus',function($key){
                $key->on('platform_order_items.product_sku', '=', 'platform_skus.platform_sku');
            })
            ->where('platform_skus.platform', $order['platform'])
            ->where('platform_skus.country_code',$order['country_code'])
            ->get(['platform_skus.product_sku','platform_order_items.fba_product_code','platform_order_items.quantity','platform_order_items.transaction_id','platform_order_items.item_id'])
            ->toArray();
            $order['items'] = $items;
			unset($order['id']);
            $returnData = $this->request->modifyOrder($order);
            $updateData = [];
            $updateData['sync_status'] = array_get($returnData,'ask')=='Success'?1:-1;
            $updateData['sync_message'] = array_get($returnData,'message');
            PlatformOrder::where('id',$orderId)->update($updateData);
        }
	}

    public function cancelOrder()
    {
        $orders = PlatformOrder::where('sync','-1')->where('sync_status',0)->take(10)->lockForUpdate()->get();
        foreach($orders as $order){
            $data  = ['order_code'=>$order->order_code];
            $returnData = $this->request->cancelOrder($data);
            $order->sync_status = array_get($returnData,'ask')=='Success'?1:-1;
            $order->sync_message = array_get($returnData,'message');
            $order->save();
        }

	}
}
