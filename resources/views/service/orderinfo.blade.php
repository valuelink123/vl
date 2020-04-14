<link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

<div>
    <div class="col-md-12">
        <div class="portlet light bordered">
        @if(!empty($order['orderItems']))
            <div class="invoice-content-2 bordered">
                <div class="invoice-head">
                    <div class="col-md-7 col-xs-6">
                        <div class="invoice-logo">
                            <h1 class="uppercase">{!! $order['AmazonOrderId'] !!} ( {!! $order['SellerName'] !!} )</h1>
                            Buyer Email : {!! $order['BuyerEmail'] !!}<BR>
                            Buyer Name : {!! $order['BuyerName'] !!}<BR>
                            PurchaseDate : {!! $order['PurchaseDate'] !!}
                        </div>
                    </div>
                    <div class="col-md-5 col-xs-6">
                        <div class="company-address">
                            <span class="bold ">{!! $order['Name'] !!}</span>
                            <br> {!! $order['AddressLine1'] !!}
                            <br> {!! $order['AddressLine2'] !!}
                            <br> {!! $order['AddressLine3'] !!}
                            <br> {!! $order['City'] !!} {!! $order['StateOrRegion'] !!} {!! $order['CountryCode'] !!}
                            <br> {!! $order['PostalCode'] !!}
                        </div>
                    </div>
                </div>
                <div style="clear: both"></div>
                <BR><BR>
                <div class="invoice-cust-add">
                    <div class="col-xs-3">
                        <h4 class="invoice-title ">Seller ID</h4>
                        <p class="invoice-desc">{!! $order['SellerId'] !!}   </p>
                    </div>
                    <div class="col-xs-3">
                        <h4 class="invoice-title ">Site</h4>
                        <p class="invoice-desc">{!! $order['SalesChannel'] !!}</p>
                    </div>
                    <div class="col-xs-2">
                        <h4 class="invoice-title ">Fulfillment Channel</h4>
                        <p class="invoice-desc">{!! $order['FulfillmentChannel'] !!}</p>
                    </div>
                    <div class="col-xs-2">
                        <h4 class="invoice-title ">Ship Service Level</h4>
                        <p class="invoice-desc">{!! $order['ShipServiceLevel'] !!}</p>
                    </div>

                    <div class="col-xs-2">
                        <h4 class="invoice-title ">Status</h4>
                        <p class="invoice-desc">{!! $order['OrderStatus'] !!}</p>
                    </div>
                </div>
                <BR><BR>
                <div class="invoice-body">
                    <div class="col-xs-12 table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th class="invoice-title uppercase">Description</th>
                                <th class="invoice-title uppercase text-center">Qty</th>
                                <th class="invoice-title uppercase text-center">Price</th>
                                <th class="invoice-title uppercase text-center">Shipping</th>
                                <th class="invoice-title uppercase text-center">Promotion</th>
                                <th class="invoice-title uppercase text-center">Tax</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order['orderItems'] as $item)
                                <tr>
                                    <td>
                                        <h4>{!! $item['ASIN'] !!} ( {!! $item['SellerSKU'] !!} )</h4>
                                        <p> {!! $item['Title'] !!} </p>
                                    </td>
                                    <td class="text-center sbold">{!! $item['QuantityOrdered'] !!}</td>
                                    <td class="text-center sbold">{{($item['QuantityOrdered'])?round($item['ItemPriceAmount']/$item['QuantityOrdered'],2):round($item['ItemPriceAmount'],2)}}</td>
                                    <td class="text-center sbold">{{round($item['ShippingPriceAmount'],2)}} {{($item['ShippingDiscountAmount'])?'( -'.round($item['ShippingDiscountAmount'],2).' )':''}}</td>
                                    <td class="text-center sbold">{{($item['PromotionDiscountAmount'])?'( -'.round($item['PromotionDiscountAmount'],2).' )':''}}</td>
                                    <td class="text-center sbold">{{round($item['ItemTaxAmount'],2)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="invoice-subtotal">
                    <div class="col-xs-6">
                        <h4 class="invoice-title uppercase">Total</h4>
                        <p class="invoice-desc grand-total">{{round($order['Amount'],2)}} {{$order['CurrencyCode']}}</p>
                    </div>
                </div>

            </div>
        @else
            <b>Can not match or find order</b>
        @endif
        </div>
    </div>
</div>