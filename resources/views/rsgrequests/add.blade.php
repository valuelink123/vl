<style>
    .inactive{
        color:red;
    }
    .active{
        color:black;
    }
    #product_id optgroup{color:black;}

</style>
    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">

            <div class="portlet-body form">
                <form role="form" action="{{ url('rsgrequests') }}" method="POST">
                    {{ csrf_field() }}

                    <div class="form-body">
						
				
				
				<div class="clearfix margin-bottom-20"></div>

						<div class="form-group col-md-9">
							<label>Site</label>
						<select class="form-control" name="site" id="sitesel" required>
							<option class="active" value="">Please select</option>
							@foreach(getAsinSites() as $key=>$site)
								<option value="{!! $site !!}" @if($data['site']==$site) selected @endif>{!! $site !!}</option>
							@endforeach
						</select>
						</div>

						<div class="form-group col-md-9">
                            <label>Products</label>
                                <select class="form-control" name="product_id" id="product_id" required>
                                    <option class="active" value="">Please select</option>
									@foreach($products as $key=>$val)
										<optgroup label="{!! $key !!}">
											@foreach($val as $k=>$v)
												<option value="{!! $v['id'] !!}" @if($data['productid']==$v['id']) selected @endif>{!! $v['product_name'] !!}</option>
											@endforeach
										</optgroup>
									@endforeach
								</select>
                        </div>
                        <div class="form-group col-md-3">
                            <label></label>
                            <input id="search_product" type="text" class="form-control" value="" placeholder="search product">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Customer Email</label>
                                <input type="text" class="form-control" name="customer_email" id="customer_email"  required>
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Current Step</label>
								<select class="form-control " name="step" id="step" required>
								<?php 
								foreach(getStepStatus() as $k=>$v){ 	
									echo '<option value="'.$k.'" >'.$v.'</option>';
								}?>
								
								</select>                           
                        </div>
						<div class="form-group col-md-6">
                            <label>Customer Paypal</label>
                            
                             
                                <input type="text" class="form-control" name="customer_paypal_email" id="customer_paypal_email" >
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Fund Money</label>
                            	<div class="clearfix"></div>
                             	<div class="form-inline">
                                <input type="text" class="form-control col-md-8" name="transfer_amount" id="transfer_amount" >
								
								<select class="form-control col-md-4" name="transfer_currency" id="transfer_currency">
								<?php 
									foreach(getCurrency() as $v){ 
										echo '<option value="'.$v.'" >'.$v.' </option>';
									}?>
								</select>
								</div>
                            
                        </div>
						
						

						
						
						
						<div class="form-group col-md-6">
                            <label>Amazon Order Id</label>
                                <input type="text" class="form-control" name="amazon_order_id" id="amazon_order_id" >
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Review Url</label>
                                <input type="text" class="form-control" name="review_url" id="review_url" >
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Remark</label>
                                <input type="text" class="form-control" name="transaction_id" id="transaction_id" >
                           
                        </div>

						<div class="form-group col-md-6">
							<label>Star rating</label>
							<input type="text" class="form-control" name="star_rating" id="star_rating" >

						</div>

						{{--<div class="form-group col-md-6">--}}
							{{--<label>Follow</label>--}}
							{{--<input type="text" class="form-control" name="follow" id="follow" >--}}

						{{--</div>--}}

						{{--<div class="form-group col-md-6">--}}
							{{--<label>Next follow date</label>--}}
							{{--<input type="text" class="form-control" name="next_follow_date" id="next_follow_date">--}}

						{{--</div>--}}

                        <div class="form-group col-md-6">
                            <label>Channel</label>
                            <select class="form-control " name="channel" id="channel" required>
								<?php
								foreach(getRsgRequestChannel() as $k=>$v){
									echo '<option value="'.$k.'" >'.$v.'</option>';
								}?>

                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Customer's FB Name</label>
                            <input type="text" class="form-control" name="facebook_name" id="facebook_name">

                        </div>

                        <div class="form-group col-md-6">
                            <label>FB Group</label>
                            <input id="facebook_group" class="form-control" name="facebook_group" list="list-facebook_group" placeholder="Facebook Group"/>
                            <datalist id="list-facebook_group">
                                @foreach(getFacebookGroup() as $id=>$name)
                                    <option value="{!! $id !!} | {!! $name !!}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Auto Send</label>
                            <select class="form-control " name="auto_send_status" id="auto_send_status" required>
                                <option value="0">Yes</option>
                                <option value="1">No</option>
                            </select>
                        </div>
						
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
								<button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                <button type="submit" class="btn blue pull-right">Submit</button>
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

<script>
    $(function() {
        //下拉选择站点后，选择显示或者隐藏产品的选项
        $("#sitesel").change(function(){
            $("#search_product").trigger("keyup");
            var site = $(this).val();
            $('#product_id optgroup').each(function (index,element){
                // var content = $(element).text().toUpperCase();
                var content = $(element).attr('label');
                if(content.indexOf(site) >= 0 ) {
                    //包含搜索的内容
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
        });

        $("#search_product").keyup(function(){
            var search_value = $(this).val().toUpperCase();
            $('#product_id option').each(function (index,element){
                var content = $(element).text().toUpperCase();
                if(content.indexOf(search_value) >= 0 ) {
                    //包含搜索的内容
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
        });

        $("#sitesel").trigger("change");
        $("#search_product").trigger("keyup");

    });
</script>
