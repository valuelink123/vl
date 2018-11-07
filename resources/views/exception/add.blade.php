@extends('layouts.layout')
@section('label', 'Email Details')
@section('content')

<script>
  function changeType(){

      $(thetabs).find('[href^="#tab_"]').hide()

      let type = $("#type").val()

      // 位运算，3 等于 1 OR 2
      // 即用 3 来表示 Refund & Replacement 组合
      for(let bit of [2, 1, 4]){
          if(bit & type) $(thetabs).find(`[href="#tab_${bit}"]`).show().tab('show')
      }
  }

  function loadorder(){

	if(!rebindorderid.reportValidity()){
	    return
	}

  	$.post("/exception/getorder",
	  {
	  	"_token":"{{csrf_token()}}",
		"sellerid":$("#rebindordersellerid").val(),
		"orderid":$("#rebindorderid").val()
	  },
	  function(data,status){
	  	if(status=='success'){
	  		var redata = JSON.parse(data);
			if(redata.result!=''){
				toastr.success(redata.message);
				var data = redata.result;
				$("#name", $("#exception_form")).val(data.BuyerName);
				$("#refund", $("#exception_form")).val((Math.floor(data.Amount * 1000000) / 1000000).toFixed(2));
				$("#shipname", $("#exception_form")).val(data.Name);
				$("#address1", $("#exception_form")).val(data.AddressLine1);
				$("#address2", $("#exception_form")).val(data.AddressLine2);
				$("#address3", $("#exception_form")).val(data.AddressLine3);
				$("#city", $("#exception_form")).val(data.City);
				$("#county", $("#exception_form")).val(data.County);
				$("#district", $("#exception_form")).val(data.District);
				$("#state", $("#exception_form")).val(data.StateOrRegion);
				$("#postalcode", $("#exception_form")).val(data.PostalCode);
				$("#countrycode", $("#exception_form")).val(data.CountryCode);
				$("#phone", $("#exception_form")).val(data.Phone);
				var items = data.orderItemData;
				var order_sku='';
				for( var child_i in items )
			　　{
					order_sku+=items[child_i].SellerSKU+'*'+items[child_i].QuantityOrdered+'; ';
			　　}
				$("#order_sku", $("#exception_form")).val(order_sku);

                rebindordersellerid.value = data.SellerId
                let site = `www.${data.SalesChannel}`.toLowerCase()
                setReplacementItemList(items, site)

			}else{
				toastr.error(redata.message);
			}
		}

	  });
  }
  $(function() {
  	var request_orderid = "{{array_get($_REQUEST,'request_orderid')}}";
	var request_sellerid = "{{array_get($_REQUEST,'request_sellerid')}}";
	var request_groupid = "{{array_get($_REQUEST,'request_groupid')}}";
	if(request_orderid!='' && request_sellerid!=''){
		$("#rebindordersellerid").val(request_sellerid);
		$("#rebindorderid").val(request_orderid);
		$("#group_id").val(request_groupid);
		loadorder();
	}
  	changeType();
	$("#type").change(function(){
		changeType();
	});
	$("#rebindorder").click(function(){
	  loadorder();
	});
  });
  </script>
<form  action="{{ url('exception') }}" id="exception_form" novalidate method="POST">
 {{ csrf_field() }}
    <div class="col-lg-9">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered ">
	@if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif


    <div class="portlet-title">
        <div class="caption">
            <i class="icon-microphone font-green"></i>
            <span class="caption-subject bold font-green">Refund && Replacement</span>
            <span class="caption-helper">Refund && Replacement.</span>
        </div>

    </div>
    <div class="portlet-body">
		<div class="col-lg-8">


		<div class="form-group">
			<label>Group</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select class="form-control" name="group_id" id="group_id">

				@foreach (array_get($mygroups,'groups',array()) as $group_id=>$group)

					<option value="{{$group_id}}">{{array_get($groups,$group_id.'.group_name')}}</option>

				@endforeach
		</select>
			</div>
		</div>




		<div class="form-group">
			<label>Seller Account and Order ID</label>
		<div class="row" >

						<div class="col-md-5">

													<input id="rebindordersellerid" class="form-control xform-autotrim" name="rebindordersellerid" list="list-rebindordersellerid" placeholder="Seller Account" autocomplete="off" />
													<datalist id="list-rebindordersellerid">
														@foreach ($sellerids as $id=>$name)
															<option value="{{$id}}" label="{{$name}}" >
														@endforeach
													</datalist>

						</div>

                        <div class="col-md-7">
						<div class="input-group">



                                                                <input id="rebindorderid" class="form-control xform-autotrim" type="text" name="rebindorderid" placeholder="Amazon Order ID" autocomplete="off" required pattern="\d{3}-\d{7}-\d{7}" />
                                                            <span class="input-group-btn">
                                                                <button id="rebindorder" class="btn btn-success" type="button">
                                                                    Get Order Info</button>
                                                            </span>
                                                        </div>

                        </div>


                    </div>
					</div>
					 <div class="form-group">
			<label>Customer Name</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required >
				<input type="hidden" class="form-control" name="order_sku" id="order_sku" value="{{old('order_sku')}}" >
			</div>
		</div>
					<div class="form-group">
			<label>Reason</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<input type="text" class="form-control xform-autotrim" name="request_content" id="request_content" value="{{old('request_content')}}" list="list-request_content" autocomplete="off" required />
				<datalist id="list-request_content">
					@foreach($requestContentHistoryValues as $rcValue)
						<option value="{{$rcValue}}" >
					@endforeach
				</datalist>
			</div>
		</div>


		<div class="form-group">
			<label>Type</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select name="type" id="type" class="form-control" >
				<option value="2">Replacement
				<option value="1">Refund
				<option value="3">Refund & Replacement
				<option value="4">Gift Card
				</select>
			</div>
		</div>
		</div>
		<div style="clear:both"></div>
        <div class="tabbable-line">
            <ul class="nav nav-tabs" id="thetabs">
                <li class="">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="false"> Refund </a>
                </li>
                <li class="active">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="true"> Replacement </a>
                </li>
				<li class="">
					<a href="#tab_4" data-toggle="tab" aria-expanded="false"> Gift Card </a>
				</li>
            </ul>
            <div class="tab-content">

                <div class="tab-pane" id="tab_1">


					<div class="col-lg-8">
                        <div class="form-group">
							<label>Refund Amount</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="refund" id="refund" value="{{old('refund')}}" autocomplete="off" required />
							</div>
						</div>
                        <div style="clear:both;"></div>
                    </div>

                     <div style="clear:both;"></div>
                </div>


                <div class="tab-pane active" id="tab_2">
					<div class="col-lg-8">
						<div class="form-group">
							<label>Name</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="shipname" id="shipname" value="{{old('shipname')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine1</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="address1" id="address1" value="{{old('address1')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine2</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="address2" id="address2" value="{{old('address2')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine3</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="address3" id="address3" value="{{old('address3')}}" >
							</div>
						</div>




						<div class="form-group">
							<label>City</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="city" id="city" value="{{old('city')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>County</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="county" id="county" value="{{old('county')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>StateOrRegion</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="state" id="state" value="{{old('state')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>District</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="district" id="district" value="{{old('district')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>PostalCode</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="postalcode" id="postalcode" value="{{old('postalcode')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>CountryCode</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="countrycode" id="countrycode" value="{{old('countrycode')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>Phone</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="phone" id="phone" value="{{old('phone')}}" >
							</div>
						</div>
                        <div style="clear:both;"></div>

                    </div>

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
                                        <div class="col-lg-6 col-md-5">
                                            <label class="control-label">Search by Item No and select</label>
                                            <input type="hidden" class="item_name" name="title" />
                                            <input type="text" class="form-control seller-sku-selector" name="note" placeholder="Seller Account and SKU" autocomplete="off" required />
                                        </div>
                                        <div class="col-lg-2 col-md-2">
                                            <label class="control-label">Quantity</label>
                                            <input type="text" class="form-control"  name="qty" value="1" placeholder="Quantity" required />
                                        </div>
                                        <div class="col-lg-1 col-md-2">
                                            <label class="control-label"><input type="checkbox" name="addattr" value="Returned">Returned</label>
                                            <label class="control-label"><input type="checkbox" name="addattr" value="Urgent">Urgent</label>
                                        </div>
                                        <div class="col-lg-1 col-md-1">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
                                <i class="fa fa-plus"></i> Add Product</a>
                        </div>
                        <script id="tplStockDatalist" type="text/template">
                            <datalist id="list-${item_code}-stocks">
                                <% for(let {seller_name,seller_id,seller_sku,stock} of stocks){ %>
                                <option value="${seller_name} | ${seller_sku}" label="Stock: ${stock}">
                                <% } %>
                            </datalist>
                        </script>
                    </div>
                    <div style="clear:both;"></div>

                </div>


				<div class="tab-pane" id="tab_4">


					<div class="col-lg-8">
						<div class="form-group">
							<label>Gift Amount</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="gift_card_amount" id="gift_card_amount" value="{{old('gift_card_amount')}}" autocomplete="off" required />
							</div>
						</div>
						<div style="clear:both;"></div>
					</div>

					<div style="clear:both;"></div>
				</div>


            </div>

        </div>


    </div>

</div>
<div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                                <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                            </div>
                        </div>
                    </div>
        </div>
		 </div>
</form>
@include('frank.common')
<script>

    let $replacementProductList = $('#replacement-product-list')

    let replacementItemRepeater = $replacementProductList.parent().repeater({defaultValues:{qty:1}})

    // 将原订单SKU填充到重发列表，并带出库存信息
    function setReplacementItemList(items, site){
        replacementItemRepeater.setList(items.map(i => {
            return {
                find_item_by: JSON.stringify({
                    site,
                    asin: i.ASIN,
                    seller_id: i.SellerId,
                    seller_sku: i.SellerSKU,
                }),
                qty: i.QuantityOrdered
            }
        }));

        $replacementProductList.find('.item_code').each((i, ele) => {

            let $findBy = $(ele).siblings('.find_item_by')
            let eventData = JSON.parse($findBy.val())
            $findBy.remove()
            eventData.currentTarget = ele

            handleItemCodeSearch.call(ele, eventData)
        })
    }

    /**
     * 通过 item_code (手动输入)
     * 或者 seller_id + seller_sku (FBA发货)
     * 或者 site + seller_sku + asin (FBM发货)
     * 把物料的库存列表带出来(包括fba、fbm)以供选择重发
     */
    function handleItemCodeSearch(e) {

        let $item_code = $(e.currentTarget)

        let item_code = $item_code.val().trim()

        let $sellerSkuSelector = $item_code.closest('.mt-repeater-row').find('.seller-sku-selector')

        if ($sellerSkuSelector.attr('list') === `list-${item_code}-stocks`) return

        $sellerSkuSelector.val('').change().removeAttr('list').data('skusInfo', null).next('datalist').remove()

        if (!item_code) {

            var {seller_id, seller_sku, site, asin} = e

            if (asin) {
                var postData = {seller_id, seller_sku, site, asin}
            } else {
                return $sellerSkuSelector.attr('placeholder', 'Seller Account and SKU')
            }

        } else {
            var postData = {item_code}
        }

        $.ajax({
            method: 'POST',
            url: '/kms/stocklist',
            data: postData,
            dataType: 'json',
            success(stocks) {

                if (!stocks.length) {
                    if(seller_id && seller_sku){
                        $item_code.val('no match')
                        $sellerSkuSelector.val(`${seller_id} | ${seller_sku}`)
                    } else {
                        $sellerSkuSelector.attr('placeholder', 'no match')
                    }
                    return
                }

                if (false === stocks[0]) {
                    let errmsg = stocks[1]
                    $sellerSkuSelector.attr('placeholder', errmsg)
                    return
                }

                // console.log(stocks)
                if(!item_code){
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
                    let theSellerId = rebindordersellerid.value.trim()
                    for(let stock of stocks){
                        if(stock.seller_id === theSellerId && stock.stock) {
                            selected = stock
                            break
                        }
                    }
                }

                if(selected) $sellerSkuSelector.val(`${selected.seller_name} | ${selected.seller_sku}`).change()

            }
        })
    }

    jQuery($ => {

        XFormHelper.autoTrim('#replacement-product-list', 'input')

        // 更新隐藏表单
        $replacementProductList.on('change', '.seller-sku-selector', function (e) {

            let skusInfo = $(this).data('skusInfo') || {}
            let info = skusInfo[this.value]

            let $repeatRow = $(this).closest('.mt-repeater-row')

            if(info){

                $repeatRow.find('.seller_id').val(info.seller_id)
                $repeatRow.find('.seller_sku').val(info.seller_sku)

                if(info.stock <= 0){
                    alert('The stock of this item is Zero.');
                }
            } else {
                this.value = ''
                $repeatRow.find('.seller_id').val('')
                $repeatRow.find('.seller_sku').val('')
			}

            let item_name = info ? info.item_name : ''

            $repeatRow.find('.item_name').val(item_name).prev().html(item_name||'Search by Item No and select')

        })

        $(exception_form).submit(function (e) {

            let type = $('#type').val()

            for(let input of $(this).find('[name]')){

                let tabID = $(input).closest('.tab-pane').attr('id')

                if(tabID){
                    if(!(type & tabID.substr(-1))) continue
                }

                // if($.contains($replacementProductList[0], input)){ }

                if(!input.reportValidity()) {
                    toastr.error('The form is not complete yet.')
                    return false
                }
            }

        })

        bindDelayEvents('#replacement-product-list', 'change keyup paste', '.item_code', handleItemCodeSearch);
    })
</script>
<div style="clear:both;"></div>
@endsection