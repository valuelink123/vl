@extends('layouts.layout')
@section('label', 'Email Details')
@section('content')

<script>
  function changeType(){

    $("a[href='#tab_3']").parent().removeClass('active').hide();
    $("#tab_3").removeClass('active');

  	if($("#type").val()==1){
		$("a[href='#tab_1']").parent().addClass('active').show();
		$("#tab_1").addClass('active');
		$("a[href='#tab_2']").parent().removeClass('active').hide();
		$("#tab_2").removeClass('active');
	}
	if($("#type").val()==2){
		$("a[href='#tab_1']").parent().removeClass('active').hide();
		$("#tab_1").removeClass('active');
		$("a[href='#tab_2']").parent().addClass('active').show();
		$("#tab_2").addClass('active');
	}
	if($("#type").val()==3){
		$("a[href='#tab_1']").parent().show();
		$("a[href='#tab_2']").parent().show();
		if($("a[href='#tab_1']").parent().hasClass('active')){
			$("#tab_1").addClass('active');
			$("#tab_2").removeClass('active');
		}else{
			$("#tab_1").removeClass('active');
			$("#tab_2").addClass('active');
		}

	}
    if($("#type").val()==4){
		$("a[href='#tab_1'],a[href='#tab_2']").parent().removeClass('active').hide();
        $("#tab_1,#tab_2").removeClass('active');
        $("a[href='#tab_3']").parent().addClass('active').show();
        $("#tab_3").addClass('active');
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
                setReplacementItemList(items)

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
<form  action="{{ url('exception') }}" id="exception_form" method="POST">
 {{ csrf_field() }}
    <div class="col-md-7">
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
		<div class="col-xs-12">


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
            <ul class="nav nav-tabs ">
                <li class="">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="false"> Refund </a>
                </li>
                <li class="active">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="true"> Replacement </a>
                </li>
				<li class="">
					<a href="#tab_3" data-toggle="tab" aria-expanded="false"> Gift Card </a>
				</li>
            </ul>
            <div class="tab-content">

                <div class="tab-pane " id="tab_1">


					<div class="col-xs-12">
                        <div class="form-group">
							<label>Refund Amount</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="refund" id="refund" value="{{old('refund')}}" autocomplete="off" />
							</div>
						</div>
                        <div style="clear:both;"></div>
                    </div>

                     <div style="clear:both;"></div>
                </div>


                <div class="tab-pane active" id="tab_2">
					<div class="col-xs-12">
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










                       <div class="form-group mt-repeater frank">
							<div data-repeater-list="group-products" id="replacement-product-list">
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-2">
											<label class="control-label">Item Code</label>
											 <input type="text" class="form-control item_code" name="item_code" placeholder="item code" autocomplete="off" />
                                            <input type="hidden" class="seller_id" name="seller_id" />
                                            <input type="hidden" class="seller_sku" name="seller_sku" />
										</div>
										<div class="col-md-7">
											<label class="control-label">Seller Account and SKU</label>
											<input type="hidden" class="item_name" name="title" />
											<input type="text" class="form-control seller-sku-selector" name="note" placeholder="Seller Account and SKU" autocomplete="off" />
										</div>
										<div class="col-md-2">
											<label class="control-label">Quantity</label>
											 <input type="text" class="form-control"  name="qty" value="1" placeholder="Quantity" />

										</div>
										<div class="col-md-1">
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
                        <div style="clear:both;"></div>
                    </div>

                     <div style="clear:both;"></div>
                </div>


				<div class="tab-pane" id="tab_3">


					<div class="col-xs-12">
						<div class="form-group">
							<label>Gift Amount</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="gift_card_amount" id="gift_card_amount" value="{{old('gift_card_amount')}}" autocomplete="off" />
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

    function setReplacementItemList(items){
        replacementItemRepeater.setList(items.map(i => {
            return {
                seller_id: i.SellerId,
                seller_sku: i.SellerSKU,
                title: i.Title,
                qty: i.QuantityOrdered
            }
        }));

        $replacementProductList.find('.item_code').each((i, ele) => {

            handleItemCodeSearch.call(ele, {
                currentTarget: ele,
                seller_id: $(ele).siblings('.seller_id').val(),
                seller_sku: $(ele).siblings('.seller_sku').val()
            })
        })
    }

    function handleItemCodeSearch(e) {

        let item_code = e.currentTarget.value.trim()

        let $sellerSku = $(e.currentTarget).closest('.mt-repeater-row').find('.seller-sku-selector')

        if ($sellerSku.attr('list') === `list-${item_code}-stocks`) return

        $sellerSku.val('').change().removeAttr('list').data('skusInfo', null).next('datalist').remove()

        if (!item_code) {

            var {seller_id, seller_sku} = e

            if (seller_id && seller_sku) {
                var postData = {seller_id, seller_sku}
            } else {
                return $sellerSku.attr('placeholder', 'Seller Account and SKU')
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
                        e.currentTarget.value = 'no match'
                        $sellerSku.val(`${seller_id} | ${seller_sku}`)
                    } else {
                        $sellerSku.attr('placeholder', 'no match')
                    }
                    return
                }

                if (false === stocks[0]) {
                    let errmsg = stocks[1]
                    $sellerSku.attr('placeholder', errmsg)
                    return
                }

                // console.log(stocks)
                if(!item_code){
                    item_code = stocks[0].item_code
                    e.currentTarget.value = item_code
                }

                stocks.sort((a, b) => {
                    return a.stock < b.stock ? 1 : (a.stock > b.stock ? -1 : 0)
                })

                $sellerSku
                    .after(tplRender(tplStockDatalist, {stocks, item_code}))
                    .attr('list', `list-${item_code}-stocks`)
                    .attr('placeholder', 'please select ...')


                let skusInfo = rows2object(stocks, ['seller_name', 'seller_sku', ' | '])

                $sellerSku.data('skusInfo', skusInfo)

                var stock = null

                if (1 === stocks.length && stocks[0].stock > 0) {
                    stock = stocks[0]
                } else {
                    let theSellerId = rebindordersellerid.value.trim()
                    for(let s of stocks){
                        if(s.seller_id === theSellerId && s.stock) {
                            stock = s
                            break
                        }
                    }
                }

                if(stock) $sellerSku.val(`${stock.seller_name} | ${stock.seller_sku}`).change()

            }
        })
    }

    jQuery($ => {

        XFormHelper.autoTrim('#replacement-product-list', 'input')

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
                $repeatRow.find('.seller_id').val('')
                $repeatRow.find('.seller_sku').val('')
			}

            let item_name = info ? info.item_name : ''

            $repeatRow.find('.item_name').val(item_name).prev().html(item_name||'Seller Account and SKU')

        })

        bindDelayEvents('#replacement-product-list', 'change keyup paste', '.item_code', handleItemCodeSearch);
    })
</script>
<div style="clear:both;"></div>
@endsection