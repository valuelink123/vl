@extends('layouts.layout')
@section('label', 'Account Stocklist')
@section('content')
    <form  action="" id="exception_form" novalidate method="POST" enctype="multipart/form-data">
        <div class="col-sm-12">
            <div class="form-group mt-repeater frank">
                <div data-repeater-list="group-products" id="replacement-product-list">
                    <div data-repeater-item class="mt-repeater-item">
                        <div class="row mt-repeater-row">
                            <div class="col-lg-2 col-md-2">
                                <label class="control-label">Item No.</label>
                                <input type="text" class="form-control item_code" name="item_code" placeholder="Item No" autocomplete="off" required />
                                <input type="hidden" class="seller_id" name="seller_id" />
                                <input type="hidden" class="seller_sku" name="seller_sku" />
                                <input type="hidden" class="find_item_by" name="find_item_by" />
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <label class="control-label">Search by Item No and select</label>
                                <input type="hidden" class="item_name" name="title" />
                                <input type="text" class="form-control seller-sku-selector" name="note" placeholder="Seller Account and SKU" autocomplete="off" required />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script id="tplStockDatalist" type="text/template">
                <datalist id="list-${item_code}-stocks">
                    <% for(let {seller_name,seller_id,seller_sku,stock} of stocks){ %>
                    <option value="${seller_name} | ${seller_sku}" label="Stock: ${stock}">
                        <% } %>
                </datalist>
            </script>
        </div>
    </form>
    @include('frank.common')
    <script>

        jQuery($ => {
            bindDelayEvents('#replacement-product-list', 'change keyup paste', '.item_code', handleItemCodeSearch);
        });
        $(function() {
            handleItemCodeSearch();
        });
        /**
         * 通过 item_code (手动输入)
         * 或者 seller_id + seller_sku (FBA发货)
         * 或者 site + seller_sku + asin (FBM发货)
         * 把物料的库存列表带出来(包括fba、fbm)以供选择重发
         */
        function handleItemCodeSearch() {
            $('.item_code').each(function () {
                let $item_code = $(this);
                let item_code = $item_code.val().trim()
                $item_code.val(item_code.toUpperCase())
                let $sellerSkuSelector = $item_code.closest('.mt-repeater-row').find('.seller-sku-selector')
                if ($sellerSkuSelector.attr('list') === `list-${item_code}-stocks`) return
                $sellerSkuSelector.val('').change().removeAttr('list').data('skusInfo', null).next('datalist').remove()
                if (!item_code) {
                    return $sellerSkuSelector.attr('placeholder', 'Seller Account and SKU')
                } else {
                    var postData = {item_code, '_token': '{{csrf_token()}}'}
                }
                $.ajax({
                    method: 'POST',
                    url: '/kms/stocklist',
                    data: postData,
                    dataType: 'json',
                    success(stocks) {
                        if (!stocks.length) {
                            $sellerSkuSelector.attr('placeholder', 'no match')
                            return
                        }

                        if (false === stocks[0]) {
                            let errmsg = stocks[1]
                            $sellerSkuSelector.attr('placeholder', errmsg)
                            return
                        }

                        // console.log(stocks)
                        if (!item_code) {
                            item_code = stocks[0].item_code
                            $item_code.val(item_code)
                        }

                        stocks.sort((a, b) => {
                            return a.stock < b.stock ? 1 : (a.stock > b.stock ? -1 : 0)
                        })

                        $sellerSkuSelector
                            .after(tplRender(tplStockDatalist, {stocks, item_code}))
                            .attr('list', `list-${item_code}-stocks`)
                            .attr('placeholder', 'please select ...')


                        let skusInfo = rows2object(stocks, ['seller_name', 'seller_sku', ' | '])

                        $sellerSkuSelector.data('skusInfo', skusInfo)

                        let selected = null

                        if (1 === stocks.length && stocks[0].stock > 0) {
                            selected = stocks[0]
                        } else {
                        }

                        if (selected) $sellerSkuSelector.val(`${selected.seller_name} | ${selected.seller_sku}`).change()

                    }
                })
            })
        }


    </script>
    <div style="clear:both;"></div>
@endsection
