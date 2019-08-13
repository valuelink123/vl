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
  $(function() {
  	changeType();
	$("#type").change(function(){
		changeType();
	});
	$("#rebindorder").click(function(){
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
                $('#state-div').attr('statevalue',data.StateOrRegion);
				// $("#countrycode", $("#exception_form")).val(data.CountryCode);
                $("select[name='countrycode']>option").each(function(){
                    if($(this).text() == data.CountryCode){
                        $(this).prop('selected', true)
                    }else{
                        $(this).prop('selected',false);
                    }
                });
                $("#countrycode").trigger("change");//触发countrycode改变事件

				$("#phone", $("#exception_form")).val(data.Phone);
				
				$("div[data-repeater-list='group-products']").html('');
				var items = data.orderItemData;
				var order_sku='';
				for( var child_i in items )
			　　{
			　　		$("div[data-repeater-list='group-products']").append('<div data-repeater-item class="mt-repeater-item"><div class="row mt-repeater-row"><div class="col-md-3"><label class="control-label">Replaced SKU</label><input type="text"class="form-control"name="group-products['+child_i+'][sku]"placeholder="SKU"value="'+ items[child_i].SellerSKU +'"></div><div class="col-md-5"><label class="control-label">Replaced Product/Accessories Name</label><input type="text"class="form-control"name="group-products['+child_i+'][title]"placeholder="title"value="'+ items[child_i].Title +'"></div><div class="col-md-2"><label class="control-label">Quantity</label><input type="text"class="form-control"name="group-products['+child_i+'][qty]"placeholder="Quantity"value="'+ items[child_i].QuantityOrdered +'"></div><div class="col-md-1"><a href="javascript:;"data-repeater-delete class="btn btn-danger mt-repeater-delete"><i class="fa fa-close"></i></a></div></div></div>');　
					order_sku+=items[child_i].SellerSKU+'*'+items[child_i].QuantityOrdered+'; ';
			　　}
				$("#order_sku", $("#exception_form")).val(order_sku);

			}else{
				toastr.error(redata.message);
			}	
		}

	  });
	});
  });
  </script>
<form  action="{{ url('exception/'.$exception['id']) }}" id="exception_form" method="POST" enctype="multipart/form-data">
<?php 
$mcf_order_str='';
if($exception['user_id'] == Auth::user()->id && ($exception['process_status'] =='cancel' || $exception['process_status'] =='submit')){
	$disable='';
}else{
	$disable='disabled';
}
?>
 {{ csrf_field() }}
                    {{ method_field('PUT') }}
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
				<select class="form-control" name="group_id" id="group_id" required {{$disable}}>

				@foreach (array_get($mygroups,'groups',array()) as $group_id=>$group)
				
					<option value="{{$group_id}}" <?php if($group_id ==$exception['group_id']) echo 'selected' ?>>{{array_get($groups,$group_id.'.group_name')}}</option>
					
				@endforeach
		</select>
			</div>
		</div>							
       
		

		
		<div class="form-group">
			<label>Seller Account and Order ID</label>
		<div class="row" >
	
						<div class="col-md-5">
						
													<select id="rebindordersellerid" class="form-control" name="rebindordersellerid" required {{$disable}}>
													<?php foreach ($sellerids as $id=>$name){?>
														<option value="{{$id}}" <?php if($id ==$exception['sellerid']) echo 'selected' ?>>{{$name}}</option>
													<?php }?>
													</select> 		
													
						</div>

                        <div class="col-md-7">
						<div class="input-group">
                                                 
													
															
                                                                <input id="rebindorderid" class="form-control" type="text" name="rebindorderid" placeholder="Amazon Order ID" value="{{$exception['amazon_order_id']}}" required disabled >
                                                            <span class="input-group-btn">
                                                                <button id="rebindorder" class="btn btn-success" type="button"  {{$disable}}>
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
				<input type="text" class="form-control" name="name" id="name" value="{{$exception['name']}}" required {{$disable}}>
				<input type="hidden" class="form-control" name="order_sku" id="order_sku" value="{{$exception['order_sku']}}" >
			</div>
		</div>
					<div class="form-group">
			<label>Reason</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select class="form-control" name="request_content" id="request_content" required {{$disable}}>
					<option value="">Select</option>
					@foreach($requestContentHistoryValues as $rcValue)

						<option value="{{$rcValue}}" @if($exception['request_content']==$rcValue) selected @endif>{{$rcValue}}</option>

					@endforeach
				</select>
				{{--<input type="text" class="form-control" name="request_content" id="request_content" value="{{$exception['request_content']}}" {{$disable}}>--}}
			</div>
		</div>

			<div class="form-group">
				<label>Description</label>
				<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
					<input type="text" class="form-control" name="descrip" id="descrip" value="{{$exception['descrip']}}" required {{$disable}}>
				</div>
			</div>
		
		
		<div class="form-group">
			<label>Type</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select name="type" id="type" class="form-control" required {{$disable}}>
				<option value="3" <?php if(3 ==$exception['type']) echo 'selected' ?>>Refund & Replacement
				<option value="2" <?php if(2 ==$exception['type']) echo 'selected' ?>>Replacement
				<option value="1" <?php if(1 ==$exception['type']) echo 'selected' ?>>Refund
				<option value="4" <?php if(4 ==$exception['type']) echo 'selected' ?>>Gift Card
				</select>
			</div>
		</div>				
		</div>
		<div style="clear:both"></div>
        <div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="true"> Refund </a>
                </li>
                <li class="">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="false"> Replacement </a>
                </li>
				<li class="">
					<a href="#tab_3" data-toggle="tab" aria-expanded="false"> Gift Card </a>
				</li>
            </ul>
            <div class="tab-content">
			
                <div class="tab-pane active" id="tab_1">
				
				
					<div class="col-xs-12">
                        <div class="form-group">
							<label>Refund Amount</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="refund" id="refund" value="{{array_get($exception,'refund',0)}}" {{$disable}} >
							</div>
						</div>
                        <div style="clear:both;"></div>
                    </div>
 
                     <div style="clear:both;"></div>
                </div>

				<?php
				$replace = unserialize(array_get($exception,'replacement',''));
				?>
                <div class="tab-pane" id="tab_2">
					<div class="col-xs-12">
						<div class="form-group">
							<label>Name</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="shipname" id="shipname" value="{{array_get($replace,'shipname')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine1</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="address1" id="address1" value="{{array_get($replace,'address1')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine2</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="address2" id="address2" value="{{array_get($replace,'address2')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine3</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="address3" id="address3" value="{{array_get($replace,'address3')}}" >
							</div>
						</div>
						



						<div class="form-group">
							<label>City</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="city" id="city" value="{{array_get($replace,'city')}}" >
							</div>
						</div>
						{{--<div class="form-group">--}}
							{{--<label>County</label>--}}
							{{--<div class="input-group ">--}}
							{{--<span class="input-group-addon">--}}
								{{--<i class="fa fa-bookmark"></i>--}}
							{{--</span>--}}
								{{--<input type="text" {{$disable}} class="form-control" name="county" id="county" value="{{array_get($replace,'county')}}" >--}}
							{{--</div>--}}
						{{--</div>--}}
						<div class="form-group">
							<label>StateOrRegion</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<div id="state-div" statevalue="{{array_get($replace,'state')}}" {{$disable}} ableattr="{{$disable}}">
									<input type="text" class="form-control" name="state" id="state" value="{{array_get($replace,'state')}}" {{$disable}}>
								</div>
								<div id="state-select" style="display:none;" {{$disable}}>
									<option value="">Select</option>
									@foreach(getStateOrRegionByUS() as $key=>$value)

										<option value="{{$key}}" @if(array_get($replace,'state')==$key) selected @endif>{{$value}}</option>

									@endforeach
								</div>
								{{--<input type="text" {{$disable}} class="form-control" name="state" id="state" value="{{array_get($replace,'state')}}" >--}}
							</div>
						</div>
						<div class="form-group">
							<label>District</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="district" id="district" value="{{array_get($replace,'district')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>PostalCode</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="postalcode" id="postalcode" value="{{array_get($replace,'postalcode')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>CountryCode</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<select class="form-control" name="countrycode" id="countrycode" {{$disable}}>
									<option value="">Select</option>
									@foreach(getCountryCode() as $key=>$value)

										<option value="{{$key}}" @if(array_get($replace,'countrycode')==$key) selected @endif>{{$key}}</option>

									@endforeach
								</select>
								{{--<input type="text" {{$disable}} class="form-control" name="countrycode" id="countrycode" value="{{array_get($replace,'countrycode')}}" >--}}
							</div>
						</div>
						<div class="form-group">
							<label>Phone</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="phone" id="phone" value="{{array_get($replace,'phone')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>Shipping Speed</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<select  class="form-control" name="shippingspeed" id="shippingspeed"{{$disable}} >
								<option value="Standard" <?php if("Standard" ==array_get($replace,'shippingspeed')) echo 'selected' ?>>Standard</option>
								<option value="Expedited" <?php if("Expedited" ==array_get($replace,'shippingspeed')) echo 'selected' ?>>Expedited</option>
								<option value="Priority" <?php if("Priority" ==array_get($replace,'shippingspeed')) echo 'selected' ?>>Priority</option>
								</select>
							</div>
						</div>
						
						
						
						
						




                       <div class="form-group mt-repeater">
							<div data-repeater-list="group-products" id="replacement-product-list">
								<?php 
								$products_details = array_get($replace,'products',array());
								$replacement_order_ids=[];
								if(is_array($products_details)){
								
								foreach($products_details as $detail) { 
									$replacement_order_ids[]=array_get($detail,'replacement_order_id');
									$addattr=array_get($detail,'addattr',[]);
								?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">Item No.</label>
											 <input type="text" class="form-control"  name="sku"  value="{{array_get($detail,'item_code')}}" disabled>
								
										</div>
										<div class="col-md-5">
											<label class="control-label">{{array_get($detail,'title')}}</label>
											 <input type="text" class="form-control"  name="title" value="{{array_get($detail,'note')??array_get($detail,'seller_sku')??array_get($detail,'sku')}}" disabled>
								
										</div>
										<div class="col-md-2">
											<label class="control-label">Quantity</label>
											 <input type="text" class="form-control quantity-input"  name="qty" value="{{array_get($detail,'qty')}}" disabled>
								
										</div>
										<div class="col-md-2">
											<label class="control-label"><input type="checkbox" name="addattr" disabled value="Returned" <?php if(in_array('Returned',$addattr)) echo "checked";?> >Returned</label>
											<label class="control-label"><input type="checkbox" name="addattr" disabled value="Urgent" <?php if(in_array('Urgent',$addattr)) echo "checked";?>>Urgent</label>
										</div>
										<div class="col-md-1">
											{{--<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete"  {{$disable}}>--}}
												{{--<i class="fa fa-close"></i>--}}
											{{--</a>--}}
										</div>
									</div>
								</div>
								<?php }} ?>
							</div>
							{{--<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add"  {{$disable}}>--}}
								{{--<i class="fa fa-plus"></i> Add Product</a>--}}
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
								<input type="text" class="form-control" name="gift_card_amount" id="gift_card_amount" value="{{array_get($exception,'gift_card_amount',0)}}" {{$disable}} />
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
<?php 
if(($exception['user_id'] == Auth::user()->id || Auth::user()->can(['exception-check']) || in_array($exception['group_id'],array_get($mygroups,'manage_groups',array()))) && $exception['process_status'] =='cancel'){ ?>
<div class="form-actions">
	<div class="row">
		<div class="col-md-offset-4 col-md-8">
			<button type="submit" class="btn blue"  {{$disable}}>Submit</button>
			<button type="reset" class="btn grey-salsa btn-outline"  {{$disable}}>Cancel</button>
		</div>
	</div>
</div>
<?php } ?>
        </div>
		 </div>
		 
		 
 <div class="col-md-5">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered ">

<?php 
if((Auth::user()->can(['exception-check']) || in_array($exception['group_id'],array_get($mygroups,'manage_groups',array()))) && ($exception['process_status']!='cancel')){
	$disable='';
}else{
	$disable='disabled';
}
?>
		
		
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-microphone font-green"></i>
            <span class="caption-subject bold font-green">Operate</span>
            <span class="caption-helper">Operate.</span>
        </div>

    </div>
    <div class="portlet-body" >
		<div class="col-xs-12">
		
		 
		<div class="form-group">
			<label>Process Status</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select name="process_status"  id="process_status" class="form-control form-filter input-sm" {{$disable}}>
					<option value="">Select...</option>
					<option value="submit" <?php if($exception['process_status']=='submit') echo 'selected';?> distabled>Pending</option>
					<option value="cancel" <?php if($exception['process_status']=='cancel') echo 'selected';?>>Cancelled</option>
					<option value="done" <?php if($exception['process_status']=='done') echo 'selected';?>>Done</option>
					<option value="confirmed" <?php if($exception['process_status']=='confirmed') echo 'selected';?>>Confirmed</option>
					<option value="auto done" <?php if($exception['process_status']=='auto done') echo 'selected';?>>Auto Done</option>
				</select>
			</div>
		</div>	
		<div class="form-group">
			<label>Process Remark</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<input type="text" class="form-control" name="process_content" id="process_content" value="{{$exception['process_content']}}"  {{$disable}}>
			</div>
		</div>

		<div class="form-group mt-repeater">
			<div class="row mt-repeater-row">
				<div class="col-md-6">
					<label class="control-label">Score</label>
					<div class="input-group ">
				<span class="input-group-addon">
					<i class="fa fa-bookmark"></i>
				</span>
						<input type="text" class="form-control" name="score" onkeyup="this.value=this.value.replace(/[^\d.]/g,'')"  id="score" value="{{$exception['score']}}"  {{$disable}}>
					</div>
				</div>

				<div class="col-md-6">
					<label class="control-label">Comment</label>
					<div class="input-group ">
				<span class="input-group-addon">
					<i class="fa fa-bookmark"></i>
				</span>
						<input type="text" class="form-control" name="comment"  id="comment" value="{{$exception['comment']}}"  {{$disable}}>
					</div>
				</div>
			</div>
		</div>
		
		<div class="form-group mt-repeater">
			<div class="row mt-repeater-row">
				<div class="col-md-6">
					<label class="control-label">Process Attach</label>
					<div class="input-group ">

						<input type="file" class="form-control" name="importFile" {{$disable}} />
						<?php if(array_get($exception,'process_attach')){ ?>
						<a href="{{array_get($exception,'process_attach')}}" target="_blank">{{basename(array_get($exception,'process_attach'))}}</a>
						<?php } ?>
					</div>
				</div>

				<div class="col-md-6">
					<label>Replacement Order Id:</label>
					@foreach ($replacement_order_ids as $replacement_order_id)
						<div class="input-group "><input type="text" class="form-control form-filter" name="replacement_order_id[]"  value="{{$replacement_order_id}}"  {{$disable}} {{(($replacement_order_id==$exception['amazon_order_id'])?'readonly':'')}}></div>
					@endforeach
					<BR />
				</div>
				
				
				<?php 
				$mcf_result=array('0'=>'Waiting','1'=>'Success','-1'=>'Failed');	
				if(array_get($exception,'auto_create_mcf')){ ?>
				<div class="col-md-12">
					<span class="label label-sm label-danger">{{array_get($mcf_result,array_get($exception,'auto_create_mcf_result'))}}</span>	
					<BR />
					{{array_get($exception,'last_auto_create_mcf_date')}}
					<BR />
					{{array_get($exception,'last_auto_create_mcf_log')}}
					
				</div>
				<?php } ?>
				
				<?php 
				if($auto_create_mcf_logs){ ?>
				<div class="col-md-12">
				 	@foreach ($auto_create_mcf_logs as $auto_create_mcf_log)
					{{array_get($users,$auto_create_mcf_log->user_id )}} {{($auto_create_mcf_log->status)?'submited':'canceled'}} {{($auto_create_mcf_log->type)?$auto_create_mcf_log->type:'MCF'}} at  {{$auto_create_mcf_log->date}}
					<BR />
					@endforeach
				</div>
				<?php } ?>

			</div>
		</div>

		<?php if($last_inboxid){ ?>
		<div class="form-group">
		<div class="btn-group">
			<a href="{{ url('/inbox/'.$last_inboxid)}}" target="_blank" > See Email History</a>
		</div>
		</div>
		<?php 
		}
		?>
		
		<div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
								<?php
if((Auth::user()->can(['exception-check']) || in_array($exception['group_id'],array_get($mygroups,'manage_groups',array()))) && ($exception['process_status']!='cancel')){ ?>
                                <button type="submit" class="btn blue"  {{$disable}}>Submit</button>
                                <button type="reset" class="btn grey-salsa btn-outline"  {{$disable}}>Cancel</button>
								<?php } ?>
								<?php 
								if($exception['process_status']=='auto done'){
									if(array_get($exception,'auto_create_mcf')){
								?>
								<button type="submit" class="btn blue" name="acf" value="0">Cancel Auto Create MCF</button>
								<?php }else{ ?>
								<button type="submit" class="btn blue" name="acf" value="1">Auto Create MCF</button>
								<?php }} ?>
								
								
								<?php 
								if($exception['process_status']=='auto done' || $exception['process_status']=='done'){
									if(array_get($exception,'auto_create_sap')){
								?>
								<button type="submit" class="btn blue" name="acp" value="0">Cancel Auto Create SAP</button>
								<?php }else{ ?>
								<button type="submit" class="btn blue" name="acp" value="1">Auto Create SAP</button>
								<?php }} ?>
                            </div>
                        </div>
                    </div>
		
		</div>
	</div><div style="clear:both;"></div>

	<div style="margin-top:20px;margin-left:35px;margin-right: 30px;">
		@foreach ($mcf_orders as $mcf_order)
			{{$mcf_order->SellerFulfillmentOrderId}} :<br/> {{$mcf_order->PackageNumber}}, {{$mcf_order->CarrierCode}}, {{$mcf_order->TrackingNumber}}, {{$mcf_order->EstimatedArrivalDateTime}}<BR /><br/>
		@endforeach
	</div>

		{{--@if($exception['process_status']!='done' and $exception['process_status']!='auto done' and $exception['update_status_log'])--}}
		@if($exception['update_status_log'])
		<div style="margin-top:20px;margin-left:35px;margin-right: 30px;">
			<pre>{!! $exception['update_status_log'] !!}</pre>
		</div>
		@endif

		</div></div></div>					
</form>
<script>
    $(function() {
        //当countrycode为US的时候，StateOrRegion为固定下拉选项
        $("#countrycode").change(function(){
            var country = $('#countrycode').val();
            var  able = $('#state-div').attr('ableattr');
            //当countrycode选择US的时候，StateOrRegion为固定的下拉选项
            var html = '';
            var statevalue = $('#state-div').attr('statevalue');
            if(country=='US'){
                html += '<select class="form-control" name="state" id="state" required '+able+'>';
                html += $('#state-select').html();
                html += '</select>';
                $('#state-div').html(html);
                $("select[name='state']>option").each(function(){
                    if($(this).val() == statevalue){
                        $(this).prop('selected', true)
                    }else{
                        $(this).prop('selected',false);
                    }
                });
            }else{
                html = '<input type="text" class="form-control" name="state" id="state" value="'+statevalue+'" '+able+'>';
                $('#state-div').html(html);
            }
        });

        $("#countrycode").trigger("change");

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

            var  able = $('#state-div').attr('ableattr');
            //可以修改左边编辑框的内容的时候才检验数据
            if(able=='') {
                //当quantity的数量大于1的时候，弹出提示框， You will send out MORE THAN ONE replacement. Please confirm before you submit!
                // var obj = $('.quantity-input');
                // var sub = 0;
                // $.each(obj, function (i, item) {
                //     var value = $(this).val();
                //     if (value > 1) {
                //         var flag = confirm('You will send out MORE THAN ONE replacement. Please confirm before you submit!');
                //         if (flag != true && sub == 0) {
                //             sub = 1;
                //         }
                //     }
                // })
				//
                // if (sub == 1) {
                //     return false;
                // }
                //当countrycode为US和CA的时候，StateOrRegion填的值必须强制为两个大写字母,且当countrycode为US的时候，StateOrRegion为固定下拉选项
                var countryCode = $('#countrycode').val();

                if ($('#type').val() == 2) {
                    if (countryCode == 'US' || countryCode == 'CA') {
                        var state = $('#state').val();
                        if (!(state.length == 2 && /^[A-Z]+$/.test(state))) {
                            alert('StateOrRegion has to be an abbreviation');
                            return false;
                        }
                    }
                    //提交请求为Replacement类型时，Name，AddressLine1，City，StateOrRegion，PostalCode和CountryCode，Item No. 和Search by Item No and select，以及Quantity均为必填项
                    var a = {'shipname': 'Name', 'address1': 'AddressLine1', 'city': 'City', 'state': 'StateOrRegion', 'postalcode': 'PostalCode', 'countrycode': 'CountryCode'};
                    for (var e in a) {
                        if (!$('#' + e + '').val()) {
                            alert(a[e] + ' are required');
                            return false;
                        }
                    }
                }
            }

        })

    });
</script>
<div style="clear:both;"></div>
@endsection