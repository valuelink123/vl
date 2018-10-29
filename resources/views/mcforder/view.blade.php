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
                    if(isset($order->SellerFulfillmentOrderId)){
					?>
                    <div class="invoice-content-2 bordered">
                        <div class="row invoice-head">
                            <div class="col-md-7 col-xs-6">
                                <div class="invoice-logo">
                                    <h1 class="uppercase">{{$order->SellerFulfillmentOrderId}}</h1>
                                    Buyer Email : {{implode(', ',unserialize($order->NotificationEmailList))}}<BR>
                                    Buyer Name : {{$order->Name}}<BR>
                                    DisplayableOrderDateTime : {{$order->DisplayableOrderDateTime}}
                                </div>
                            </div>
                            <div class="col-md-5 col-xs-6">
                                <div class="company-address">
                                    <span class="bold ">{{$order->Name}}</span>
                                    <br> {{$order->AddressLine1}}
                                    <br> {{$order->AddressLine2}}
                                    <br> {{$order->AddressLine3}}
                                    <br> {{$order->City}} {{$order->StateOrRegion}} {{$order->CountryCode}}
                                    <br> {{$order->PostalCode}}
                                </div>
                            </div>
                        </div>
                            <BR><BR>
                        <div class="row invoice-cust-add">
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Seller ID</h4>
                                <p class="invoice-desc">{{$order->SellerId}}</p>
                            </div>
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Fulfillment Policy</h4>
                                <p class="invoice-desc">{{$order->FulfillmentPolicy}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Fulfillment Method</h4>
                                <p class="invoice-desc">{{$order->FulfillmentMethod}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Ship Service Level</h4>
                                <p class="invoice-desc">{{$order->ShippingSpeedCategory}}</p>
                            </div>

                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Status</h4>
                                <p class="invoice-desc">{{$order->FulfillmentOrderStatus}}</p>
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
                                        <td class="text-center sbold">{{$item->SellerFulfillmentOrderItemId}}</td>
                                        <td class="text-center sbold">{{$item->SellerSKU}}</td>
                                        <td class="text-center sbold">{{$item->Quantity}}</td>
                                        <td class="text-center sbold">{{$item->OrderItemDisposition}}</td>
                                        <td class="text-center sbold">{{round($item->PerUnitDeclared,2)}}</td>
										<td class="text-center sbold">{{$item->PerUnitDeclaredCurrencyCode}}</td>
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
                                        <td class="text-center sbold">{{$item->AmazonShipmentId}}</td>
                                        <td class="text-center sbold">{{$item->SellerSKU}}</td>
                                        <td class="text-center sbold">{{$item->Quantity}}</td>
                                        <td class="text-center sbold">{{$item->PackageNumber}}</td>
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
                                        <td class="text-center sbold">{{$item->PackageNumber}}</td>
                                        <td class="text-center sbold">{{$item->CarrierCode}}</td>
                                        <td class="text-center sbold">{{$item->TrackingNumber}}</td>
                                        <td class="text-center sbold">{{$item->EstimatedArrivalDateTime}}</td>
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