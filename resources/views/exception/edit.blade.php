@extends('layouts.layout')
@section('label', 'Email Details')
@section('content')

<script>
  function changeType(){
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
				$("#countrycode", $("#exception_form")).val(data.CountryCode);
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
if($exception['user_id'] == Auth::user()->id  && $exception['process_status'] =='cancel'){
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
                                                 
													
															
                                                                <input id="rebindorderid" class="form-control" type="text" name="rebindorderid" placeholder="Amazon Order ID" value="{{$exception['amazon_order_id']}}" required {{$disable}} > 
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
				<input type="text" class="form-control" name="request_content" id="request_content" value="{{$exception['request_content']}}" {{$disable}}>
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
						<div class="form-group">
							<label>County</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="county" id="county" value="{{array_get($replace,'county')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>StateOrRegion</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" {{$disable}} class="form-control" name="state" id="state" value="{{array_get($replace,'state')}}" >
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
								<input type="text" {{$disable}} class="form-control" name="countrycode" id="countrycode" value="{{array_get($replace,'countrycode')}}" >
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
						
						
						
						
						
						




                       <div class="form-group mt-repeater">
							<div data-repeater-list="group-products">
								<?php 
								$products_details = array_get($replace,'products',array());
								if(is_array($products_details)){
								foreach($products_details as $detail) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">Replaced SKU</label>
											 <input type="text" {{$disable}} class="form-control"  name="sku" placeholder="SKU"  value="{{array_get($detail,'sku')}}">
								
										</div>
										<div class="col-md-5">
											<label class="control-label">Replaced Product/Accessories Name</label>
											 <input type="text" {{$disable}} class="form-control"  name="title" placeholder="title" value="{{array_get($detail,'title')}}" >
								
										</div>
										<div class="col-md-2">
											<label class="control-label">Quantity</label>
											 <input type="text" {{$disable}} class="form-control"  name="qty" placeholder="Quantity" value="{{array_get($detail,'qty')}}">
								
										</div>
										<div class="col-md-1">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete"  {{$disable}}>
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
								<?php }} ?>
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add"  {{$disable}}>
								<i class="fa fa-plus"></i> Add Product</a>
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
if($exception['user_id'] == Auth::user()->id  && $exception['process_status'] =='cancel'){ ?>
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
if((Auth::user()->admin || in_array($exception['group_id'],array_get($mygroups,'manage_groups',array()))) && $exception['process_status']=='submit'){
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
    <div class="portlet-body">
		<div class="col-xs-12">
		
		 
		<div class="form-group">
			<label>Process Status</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select name="process_status"  id="process_status" class="form-control form-filter input-sm" {{$disable}}>
					<option value="">Select...</option>
					<option value="submit" <?php if($exception['process_status']=='submit') echo 'selected';?> distabled>Processing</option>
					<option value="cancel" <?php if($exception['process_status']=='cancel') echo 'selected';?>>Cancelled</option>
					<option value="done" <?php if($exception['process_status']=='done') echo 'selected';?>>Done</option>
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
		
		<div class="form-group">
			<label>Process Attach</label>
			<div class="input-group ">

				<input type="file" class="form-control" name="importFile" {{$disable}} />  
				<?php if(array_get($exception,'process_attach')){ ?>
				<a href="{{array_get($exception,'process_attach')}}" target="_blank">{{basename(array_get($exception,'process_attach'))}}</a>
				<?php } ?>
			</div>
		</div>
		<?php if($last_inboxid){ ?>
		<div class="form-group">
		<div class="btn-group">
			<a href="{{ url('/inbox/'.$last_inboxid)}}" target="_blank" > See Email History
				
			</a>
		</div>
		</div>
		<?php 
		}
if((Auth::user()->admin || in_array($exception['group_id'],array_get($mygroups,'manage_groups',array()))) && $exception['process_status']=='submit'){ ?>
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
		</div><div style="clear:both;"></div>
		</div></div></div>					
</form>
<div style="clear:both;"></div>
@endsection