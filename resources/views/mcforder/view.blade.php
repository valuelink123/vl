@extends('layouts.layout')
@section('label', 'Mcf Order Details')
@section('content')

    <div class="row">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered">
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-microphone font-green"></i>
            <span class="caption-subject bold font-green"> Mcf Order Details</span>
        </div>

    </div>
    <div class="portlet-body">
        <div class="tab-pane" id="tab_2">
                    <?php
                    if(isset($order->seller_fulfillment_order_id)){
					?>
                    <div class="invoice-content-2 bordered">
                        <div class="row invoice-head">
                            <div class="col-md-7 col-xs-6">
                                <div class="invoice-logo">
                                    <h1 class="uppercase">{{$order->seller_fulfillment_order_id}}</h1>
                                    Buyer Email : {{$order->notification_email_list}}<BR>
                                    Buyer Name : {{$order->name}}<BR>
                                    DisplayableOrderDateTime : {{$order->displayable_order_date_time}}
                                </div>
                            </div>
                            <div class="col-md-5 col-xs-6">
                                <div class="company-address">
                                    <span class="bold ">{{$order->name}}</span>
                                    <br> {{$order->address_line_1}}
                                    <br> {{$order->address_line_2}}
                                    <br> {{$order->address_line_3}}
                                    <br> {{$order->city}} {{$order->state_or_region}} {{$order->country_code}}
                                    <br> {{$order->postal_code}}
                                </div>
                            </div>
                        </div>
                            <BR><BR>
                        <div class="row invoice-cust-add">
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Seller ID</h4>
                                <p class="invoice-desc">{{array_get($accounts,$order->seller_account_id)}}</p>
                            </div>
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Fulfillment Policy</h4>
                                <p class="invoice-desc">{{$order->fulfillment_policy}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Fulfillment Method</h4>
                                <p class="invoice-desc">{{$order->fulfillment_method}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Ship Service Level</h4>
                                <p class="invoice-desc">{{$order->shipping_speed_category}}</p>
                            </div>

                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Status</h4>
                                <p class="invoice-desc">{{$order->fulfillment_order_status}}</p>
                            </div>


                        </div>
                        <BR><BR>
                        <div class="row invoice-body">
                            <div class="col-xs-12 table-responsive">
								<h1 class="uppercase">Order Items Info</h1>
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="invoice-title uppercase">Order Item Id</th>
                                        <th class="invoice-title uppercase text-center">Seller Sku</th>
                                        <th class="invoice-title uppercase text-center">Qty</th>
										<th class="invoice-title uppercase text-center">Disposition</th>
										<th class="invoice-title uppercase text-center">Per Unit Declared</th>
                                        <th class="invoice-title uppercase text-center">Currency Code</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach($order->items as $item){
									?>
                                    <tr>
                                        <td class="text-center sbold">{{$item->seller_fulfillment_order_item_id}}</td>
                                        <td class="text-center sbold">{{$item->seller_sku}}</td>
                                        <td class="text-center sbold">{{$item->quantity}}</td>
                                        <td class="text-center sbold">{{$item->order_item_disposition}}</td>
                                        <td class="text-center sbold">{{round($item->per_unit_declared,2)}}</td>
										<td class="text-center sbold">{{$item->per_unit_declared_currency_code}}</td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
						 <BR><BR>
						<div class="row invoice-body">
                            <div class="col-xs-12 table-responsive">
							<h1 class="uppercase">Shipment Info</h1>
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="invoice-title uppercase">Amazon Shipment Id</th>
                                        <th class="invoice-title uppercase text-center">Seller Sku</th>
                                        <th class="invoice-title uppercase text-center">Qty</th>
										<th class="invoice-title uppercase text-center">Package Number</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach($order->shipments as $item){ ?>
                                    <tr>
                                        <td class="text-center sbold">{{$item->amazon_shipment_id}}</td>
                                        <td class="text-center sbold">{{$item->seller_sku}}</td>
                                        <td class="text-center sbold">{{$item->quantity}}</td>
                                        <td class="text-center sbold">{{$item->package_number}}</td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
						
						 <BR><BR>
						<div class="row invoice-body">
                            <div class="col-xs-12 table-responsive">
							<h1 class="uppercase">Package Info</h1>
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="invoice-title uppercase">Package Number</th>
                                        <th class="invoice-title uppercase text-center">Carrier Code</th>
                                        <th class="invoice-title uppercase text-center">Tracking Number</th>
										<th class="invoice-title uppercase text-center">Estimated Arrival Date Time</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach($order->packages as $item){ ?>
                                    <tr>
                                        <td class="text-center sbold">{{$item->package_number}}</td>
                                        <td class="text-center sbold">{{$item->carrier_code}}</td>
                                        <td class="text-center sbold">{{$item->tracking_number}}</td>
                                        <td class="text-center sbold">{{$item->estimated_arrival_date_time}}</td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>




                        <div class="row">
                            <div class="col-xs-12">
                                <a class="btn btn-lg green-haze hidden-print uppercase print-btn" onclick="javascript:window.print();">Print</a>
                            </div>
                        </div>
                    </div>
                       <?php }else{
                            echo "Can not match or find order";

                        } ?>
                </div>


    </div>
</div>
        </div>
		 <div style="clear:both;"></div></div>

@endsection