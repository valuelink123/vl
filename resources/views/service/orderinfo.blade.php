<link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

<div>
    <div class="col-md-12">
        <div class="portlet light bordered">
        @if(!empty($orders))
            @foreach($orders as $order)
                <div class="invoice-content-2 bordered">
                    <div class="invoice-head">
                        <div class="col-md-7 col-xs-6">
                            <div class="invoice-logo">
                                <h1 class="uppercase">{!! $order['amazon_order_id'] !!} ( {!! $order['sellerName'] !!} )</h1>
                                Buyer Email : {!! $order['buyer_email'] !!}<BR>
                                Buyer Name : {!! $order['buyer_name'] !!}<BR>
                                PurchaseDate : {!! $order['purchase_date'] !!}
                            </div>
                        </div>
                        <div class="col-md-5 col-xs-6">
                            <div class="company-address">
                                <span class="bold ">{!! $order['name'] !!}</span>
                                <br> {!! $order['address_line1'] !!}
                                <br> {!! $order['address_line2'] !!}
                                <br> {!! $order['address_line3'] !!}
                                <br> {!! $order['city'] !!} {!! $order['state_or_region'] !!} {!! $order['country_code'] !!}
                                <br> {!! $order['postal_code'] !!}
                            </div>
                        </div>
                    </div>
                    <div style="clear: both"></div>
                    <BR><BR>
                    <div class="invoice-cust-add">
                        <div class="col-xs-3">
                            <h4 class="invoice-title ">Seller ID</h4>
                            <p class="invoice-desc">{!! $order['sellerId'] !!}   </p>
                        </div>
                        <div class="col-xs-3">
                            <h4 class="invoice-title ">Site</h4>
                            <p class="invoice-desc">{!! $order['sales_channel'] !!}</p>
                        </div>
                        <div class="col-xs-2">
                            <h4 class="invoice-title ">Fulfillment Channel</h4>
                            <p class="invoice-desc">{!! $order['fulfillment_channel'] !!}</p>
                        </div>
                        <div class="col-xs-2">
                            <h4 class="invoice-title ">Ship Service Level</h4>
                            <p class="invoice-desc">{!! $order['ship_service_level'] !!}</p>
                        </div>

                        <div class="col-xs-2">
                            <h4 class="invoice-title ">Status</h4>
                            <p class="invoice-desc">{!! $order['order_status'] !!}</p>
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
                                            <h4>{!! $item['asin'] !!} ( {!! $item['seller_sku'] !!} )</h4>
                                            <p> {!! $item['title'] !!} </p>
                                        </td>
                                        <td class="text-center sbold">{!! $item['quantity_ordered'] !!}</td>
                                        <td class="text-center sbold">{{($item['quantity_ordered'])?round($item['item_price_amount']/$item['quantity_ordered'],2):round($item['item_price_amount'],2)}}</td>
                                        <td class="text-center sbold">{{round($item['shipping_price_amount'],2)}} {{($item['shipping_discount_amount'])?'( -'.round($item['shipping_discount_amount'],2).' )':''}}</td>
                                        <td class="text-center sbold">{{($item['promotion_discount_amount'])?'( -'.round($item['promotion_discount_amount'],2).' )':''}}</td>
                                        <td class="text-center sbold">{{round($item['item_tax_amount'],2)}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="invoice-subtotal">
                        <div class="col-xs-6">
                            <h4 class="invoice-title uppercase">Total</h4>
                            <p class="invoice-desc grand-total">{{round($order['amount'],2)}} {{$order['currency_code']}}</p>
                        </div>
                    </div>

                </div>
            @endforeach
        @else
            <b>Can not match or find order</b>
        @endif
        </div>
    </div>
</div>