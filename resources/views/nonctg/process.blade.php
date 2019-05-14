@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>[['NON-CTG', '/nonctg'], 'Process']])
@endsection
@section('content')
    <style>
        .font-dark {
            color: #5888b9 !important;
        }
    </style>

    <link rel="stylesheet" href="/js/SmartWizard/css/smart_wizard.min.css"/>
    <link rel="stylesheet" href="/js/SmartWizard/css/smart_wizard_theme_arrows.min.css"/>
    <script src="/js/SmartWizard/js/jquery.smartWizard.min.js"></script>

    @include('frank.common')
    @include('UEditor::head')

    <h1 class="page-title font-red-intense"> NON-CTG Process
        <small></small>
    </h1>

    <div class="portlet light bordered">

        <div class="portlet-title">
            <div class="caption">
                <i class="icon-ghost font-dark"></i>
                <span class="caption-subject uppercase">Order ID <span class="font-dark">{!! $ctgRow['order_id'] !!}</span></span>
            </div>
        </div>

        <div class="portlet-body">
            <div>
                <ul class="nav nav-tabs" role="tablist" id="tabs">
                    <li role="presentation"><a href="#ctg-info" aria-controls="ctg-info" role="tab" data-toggle="tab">NON-CTG Info</a></li>

                    <li role="presentation"><a href="#process-steps" aria-controls="process-steps" role="tab" data-toggle="tab">Process Steps</a></li>

                    <li role="presentation"><a href="#order-info" aria-controls="order-info" role="tab" data-toggle="tab">Amazon Order Info</a></li>
                    <li role="presentation"><a href="#email-history" aria-controls="email-history" role="tab" data-toggle="tab">Email History</a></li>
                </ul>

                <div class="tab-content">

                    <div role="tabpanel" class="tab-pane" id="ctg-info">
                        <form class="row">
                            <div class="col-md-8">
								<div class="font-dark">Date</div>
                                <pre>{!! $ctgRow['date'] !!}</pre>
                                <div class="font-dark">Order ID</div>
                                <pre>{!! $ctgRow['amazon_order_id'] !!}</pre>
                                <div class="font-dark">Customer Information</div>
                                <pre>Name: {!! $ctgRow['name'] !!}<br/>Email: {!! $ctgRow['email'] !!}</pre>
                                <br/>
                                <div class="form-group">

                                    <label>
                                        Gift SKU
                                        <input class="xform-autotrim form-control" style="width:27em" type="text" value="{!! $ctgRow['gift_sku'] !!}" name="gift_sku">
                                    </label>
                                    <br/>

                                    <label>
                                        Task Assign to
                                        <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Processor" name="processor"
                                               value="{!! $ctgRow['processor']>0?"{$ctgRow['processor']} | {$users[$ctgRow['processor']]}":'' !!}" style="width:27em" list="list-users"/>
                                        <datalist id="list-users">
                                            @foreach($users as $id=>$name)
                                                <option value="{!! $id !!} | {!! $name !!}">
                                            @endforeach
                                        </datalist>
                                    </label>
                                </div>
                                <button class="btn blue" style="width:9em;" type="submit">Save</button>
                            </div>
                        </form>
                    </div>

                    {{--//这个页面是从CTG那边搬过来的--}}
                    <div role="tabpanel" class="tab-pane" id="process-steps">
                        <form id="thewizard" novalidate method="post">
                            {{--这里为选择下拉客户状态，填写跟进记录，展示跟进列表--}}
                            <div class="form-group">
                            <label>
                                STATUS
                            <select class="form-control" name="status" style="width:27em">
                                @foreach($status as $sk=>$sv)
                                    <option value="{!! $sk !!}"  @if($ctgRow['status']==$sk)selected @endif>{!! $sv !!}</option>
                                @endforeach
                            </select>
                            </label><br>
                            <label>
                                Tracking Note
                                <textarea class="form-control"  style="width:27em;height:80px;" name="track_note"></textarea>
                            </label><br>


                            <button class="btn blue" style="width:9em;" type="submit">Save</button>
                            <input type="hidden" name="id" value="{!! $ctgRow['id'] !!}" >
                            <input type="hidden" name="amazon_order_id" value="{!!$ctgRow['amazon_order_id']!!}">
                            <input type="hidden" name="way" value="1" >{{--用于区分是ajax还是页面普通提交--}}
                            <a href="/send/create?from_address=support@claimthegift.com&to_address={!!$ctgRow['email']!!}&subject=Claim the gift" target="_blank"><button class="btn green" style="width:9em" type="button">Compose</button></a>
                            <a href="/exception/create?request_orderid={!!$ctgRow['amazon_order_id']!!}" target="_blank"><button class="btn red" style="width:15em" type="button">Create Replacement</button></a>
                            </div>
                        </form>
                        @include('nonctg.trackLog')
                    </div>

                    <div role="tabpanel" class="tab-pane" id="order-info">
                        @if(!empty($order['orderItems']))
                            <div class="invoice-content-2 bordered">
                                <div class="row invoice-head">
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
                                <BR><BR>
                                <div class="row invoice-cust-add">
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
                                <div class="row invoice-body">
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
                                                    <td class="text-center sbold">{{round($item['ItemPriceAmount']/$item['QuantityOrdered'],2)}}</td>
                                                    <td class="text-center sbold">{{round($item['ShippingPriceAmount'],2)}} {{($item['ShippingDiscountAmount'])?'( -'.round($item['ShippingDiscountAmount'],2).' )':''}}</td>
                                                    <td class="text-center sbold">{{($item['PromotionDiscountAmount'])?'( -'.round($item['PromotionDiscountAmount'],2).' )':''}}</td>
                                                    <td class="text-center sbold">{{round($item['ItemTaxAmount'],2)}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row invoice-subtotal">
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

                    <div role="tabpanel" class="tab-pane" id="email-history">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover order-column" id="email_table">
                                <thead>
                                <tr>
                                    <th>From Address</th>
                                    <th>To Address</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($emails as $data)
                                    <tr class="odd gradeX">
                                        <td>
                                            {{array_get($data,'from_address')}}
                                        </td>
                                        <td>
                                            {{array_get($data,'to_address')}}
                                        </td>
                                        <td>
                                            <a href="/send/{{array_get($data,'id')}}" target="_blank"> {{array_get($data,'subject')}}</a>
                                        </td>
                                        <td>
                                            {{array_get($data,'date')}}
                                        </td>
                                        <td>
                                            {{array_get($users,array_get($data,'user_id'))}}
                                        </td>
                                        <td>
                                            {!!array_get($data,'send_date')?'<span class="label label-sm label-success">'.array_get($data,'send_date').'</span> ':'<span class="label label-sm label-danger">'.array_get($data,'status').'</span>'!!}
                                        </td>

                                    </tr>
                                @endforeach


                                </tbody>
                            </table>
                            <script>
                                $(function () {
                                    $('#email_table').dataTable({
                                        "language": {
                                            "aria": {
                                                "sortAscending": ": activate to sort column ascending",
                                                "sortDescending": ": activate to sort column descending"
                                            },
                                            "emptyTable": "No data available in table",
                                            "info": "Showing _START_ to _END_ of _TOTAL_ records",
                                            "infoEmpty": "No records found",
                                            "infoFiltered": "(filtered1 from _MAX_ total records)",
                                            "lengthMenu": "Show _MENU_",
                                            "search": "Search:",
                                            "zeroRecords": "No matching records found",
                                            "paginate": {
                                                "previous": "Prev",
                                                "next": "Next",
                                                "last": "Last",
                                                "first": "First"
                                            }
                                        },

                                        "bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
                                        "autoWidth": false,
                                        "lengthMenu": [
                                            [10, 50, 100, -1],
                                            [10, 50, 100, "All"] // change per page values here
                                        ],
                                        // set the initial value
                                        "pageLength": 10,
                                        "order": [
                                            [3, "desc"]
                                        ] // set first column as a default sort by asc
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript">
        //ctg-info模块的保存操作
        $('#ctg-info > form').submit(function () {

            postByJson(location.href, this).then(arr => {
                toastr.success('Saved !')
            }).catch(err => {
                toastr.error(err.message)
            })

            return false
        })

        $(function ($) {

            let $thewizard = $('#thewizard')

            {{--let current_index = rows2object(Object.keys(statusDict).map(i => [i, statusDict[i]]), 1, 0)["{!! $ctgRow['status'] !!}"]--}}


            let wizardInstance = $thewizard.data('smartWizard')

            // $thewizard.on('showStep', (e, anchorObject, stepNumber, stepDirection) => {
            //     ue.loadTrackNote()
            // })


            $thewizard.on('leaveStep', (e, anchorObject, stepNumber, stepDirection) => {
                switch (stepNumber) {
                    case 0:
                        wizardInstance.stepState(3, parseInt(thewizard.commented.value) < 1 ? 'show' : 'hide')
                        break
                }
                if ('backward' === stepDirection) return true
                $pages = $thewizard.children('.pages').children('div')
                // $inputs = $($pages[wizardInstance.current_index]).find('[name],[data-assoc-name]')
                for (let input of $inputs) {
                    if (!input.reportValidity()) {
                        return false
                    }
                }
            })


            $thewizard.submit(function () {

                // todo 退出自动保存、提示

                let steps = rows2object($thewizard.serializeArray(), 'name', 'value')
                // steps = Object.assign(_steps, steps)
                // steps.current_index = wizardInstance.current_index
                steps.track_notes = track_notes
                // let status = statusDict[wizardInstance.current_index]

                return false
            });


            XFormHelper.inputEnableByRadio(thewizard);
            XFormHelper.assocFormControls(thewizard);

            //let activeTab = @json($ctgRow['processor']>0)? 'process-steps' : 'ctg-info';
            let activeTab = 'ctg-info';
            $(`#tabs a[href="#${activeTab}"]`).tab('show')

        })
    </script>

@endsection
