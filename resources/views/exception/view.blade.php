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
				//setTimeout(function(){location.reload();},3000);
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
				for( var child_i in items )
			　　{
			　　		$("div[data-repeater-list='group-products']").append('<div data-repeater-item class="mt-repeater-item"><div class="row mt-repeater-row"><div class="col-md-3"><label class="control-label">Seller SKU</label><input type="text"class="form-control"name="sku"placeholder="SKU"value="'+ items[child_i].SellerSKU +'"></div><div class="col-md-5"><label class="control-label">Product Name</label><input type="text"class="form-control"name="title"placeholder="title"value="'+ items[child_i].Title +'"></div><div class="col-md-2"><label class="control-label">Quantity</label><input type="text"class="form-control"name="qty"placeholder="Quantity"value="'+ items[child_i].QuantityOrdered +'"></div><div class="col-md-1"><a href="javascript:;"data-repeater-delete class="btn btn-danger mt-repeater-delete"><i class="fa fa-close"></i></a></div></div></div>');　

			　　}

			}else{
				toastr.error(redata.message);
			}	
		}

	  });
	});
  });
  </script>
<form  action="{{ url('exception') }}" id="exception_form" method="POST">
    <div class="col-md-8">
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
		<div class="col-xs-10">
        <div class="form-group">
			<label>Customer Name</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<input type="text" class="form-control" name="name" id="name" value="{{array_get($email,'name')}}" required >
			</div>
		</div>
		

		
		<div class="form-group">
			<label>Seller Account and Order ID</label>
		<div class="row" >
	
						<div class="col-md-2">
						
													<select id="rebindordersellerid" class="form-control" name="rebindordersellerid" required>
													@foreach ($sellerids as $id=>$name)
														<option value="{{$id}}">{{$name}}</option>
													@endforeach
													</select> 		
													
						</div>

                        <div class="col-md-4">
						<div class="input-group">
                                                 
													
															
                                                                <input id="rebindorderid" class="form-control" type="text" name="rebindorderid" placeholder="Amazon Order ID"> 
                                                            <span class="input-group-btn">
                                                                <button id="rebindorder" class="btn btn-success" type="button">
                                                                    Get Order Info</button>
                                                            </span>
                                                        </div>
                            
                        </div>
                        
                        
                    </div>	
					</div>
					
					<div class="form-group">
			<label>Remark</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<input type="text" class="form-control" name="request_content" id="request_content" value="{{array_get($email,'name')}}" required >
			</div>
		</div>
		
		
		<div class="form-group">
			<label>Type</label>
			<div class="input-group ">
			<span class="input-group-addon">
				<i class="fa fa-bookmark"></i>
			</span>
				<select name="type" id="type" class="form-control" >
				<option value="3">Refund & Replacement
				<option value="2">Replacement
				<option value="1">Refund 
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
				
				
					<div class="col-xs-8">
                        <div class="form-group">
							<label>Refund Amount</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="refund" id="refund" value="{{array_get($email,'name')}}" >
							</div>
						</div>
                        <div style="clear:both;"></div>
                    </div>
 
                     <div style="clear:both;"></div>
                </div>


                <div class="tab-pane" id="tab_2">
					<div class="col-xs-8">
						<div class="form-group">
							<label>Name</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="name" id="shipname" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine1</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="address1" id="address1" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine2</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="address2" id="address2" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>AddressLine3</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="address3" id="address3" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						



						<div class="form-group">
							<label>City</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="city" id="city" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>County</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="county" id="county" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>StateOrRegion</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="state" id="state" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>District</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="district" id="district" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>PostalCode</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="postalcode" id="postalcode" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>CountryCode</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="countrycode" id="countrycode" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						<div class="form-group">
							<label>Phone</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-bookmark"></i>
							</span>
								<input type="text" class="form-control" name="phone" id="phone" value="{{array_get($email,'name')}}" >
							</div>
						</div>
						
						
						
						
						
						




                       <div class="form-group mt-repeater">
							<div data-repeater-list="group-products">
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">Seller SKU</label>
											 <input type="text" class="form-control"  name="sku" placeholder="SKU" >
								
										</div>
										<div class="col-md-5">
											<label class="control-label">Product Name</label>
											 <input type="text" class="form-control"  name="title" placeholder="title" >
								
										</div>
										<div class="col-md-2">
											<label class="control-label">Quantity</label>
											 <input type="text" class="form-control"  name="qty" placeholder="Quantity" >
								
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
                        <div style="clear:both;"></div>
                    </div>
                        
                     <div style="clear:both;"></div>
                </div>



                

            </div>
        </div>


    </div>
</div>
        </div>
		 <div style="clear:both;"></div></div>
</form>
@endsection