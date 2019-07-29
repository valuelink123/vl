<style>
    .inactive{
        color:red;
    }
    .active{
        color:black;
    }
</style>
    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">

            <div class="portlet-body form">
                <form role="form" action="{{ url('rsgrequests') }}" method="POST">
                    {{ csrf_field() }}

                    <div class="form-body">
						
				
				
				<div class="clearfix margin-bottom-20"></div>
				
						<div class="form-group col-md-12">
                            <label>Products</label>
                            
                             
                                <select class="form-control" name="product_id" id="product_id" required>
								<?php 
									$c_s='';$i=0;
									$p_c=count($products);
									foreach($products as $pd){
										$i++;
										if($pd['site']<>$c_s && $c_s) echo '</optgroup>';
										if($pd['site']<>$c_s) echo '<optgroup label="'.$pd['site'].'">';
										$c_s = $pd['site'];
										echo '<option value="'.$pd['id'].'" class="'.$pd['class'].'">'.$pd['product_name'].' </option>';
										if($i==$p_c) echo '</optgroup>';
									}?>
								</select>
                            
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

						<div class="form-group col-md-6">
							<label>Follow</label>
							<input type="text" class="form-control" name="follow" id="follow" >

						</div>

						<div class="form-group col-md-6">
							<label>Next follow date</label>
							<input type="text" class="form-control" name="next_follow_date" id="next_follow_date">

						</div>

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
    $("#product_id").change(function(){
        var optionclass = $(this).find("option:selected").attr('class');
        $('#product_id').removeClass('inactive');
        $('#product_id').removeClass('active');
        $('#product_id').addClass(optionclass);

    });

    $(function() {
        $("#product_id").trigger("change");
    });
</script>
