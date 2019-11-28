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
                <form role="form" action="{{ url('rsgrequests/'.$rule['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="form-body">
						
				<div class="mt-comments">                      
					<div class="mt-comment">
						<div class="mt-comment-img">
							<img src="{{array_get($product,'product_img')}}" width="100px" height="100px"> </div>
						<div class="mt-comment-body" style="padding-left: 100px;">
							<div class="mt-comment-info">
								<span class="mt-comment-author">{{array_get($rule,'customer_email')}}</span>
								<span class="mt-comment-date">
								{{array_get($rule,'created_at')}}
								</span>
							</div>
							<div class="mt-comment-text"><a href="https://{{array_get($product,'site')}}/dp/{{array_get($product,'asin')}}?m={{array_get($product,'seller_id')}}" target="_blank">{{array_get($product,'product_name')}}</a></div> 
							<div class="mt-comment-details">
								<span class="mt-comment-status mt-comment-status-rejected">Price : {{round(array_get($product,'price'),2)}} {{array_get($product,'currency')}}<BR />
								Keyword : {{array_get($product,'keyword')}} ; Page : {{array_get($product,'page')}} ; Position : {{array_get($product,'position')}}
								<BR />
								<span class="badge badge-success">{{array_get(getStepStatus(),$rule['step'])}}</span> {{array_get($rule,'updated_at')}}</span>
							</div>
							
						</div>
					</div>
				</div>
				
				<div class="clearfix margin-bottom-20"></div>

						<div class="form-group col-md-9">
							<label>Site</label>
							<select class="form-control" name="site" id="sitesel" required disabled>
								@foreach(getAsinSites() as $key=>$site)
									<option value="{!! $site !!}" @if($product['site']==$site) selected @endif>{!! $site !!}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group col-md-9">
							<label>Products</label>
							<select class="form-control" name="product_id" id="product_id" required>
								<option value="{!! $product['id'] !!}">{!! $product['product_name'] !!}</option>
								@if(isset($products[$product['site']]))
									@foreach($products[$product['site']] as $k=>$v)
										<option value="{!! $v['id'] !!}">{!! $v['product_name'] !!}</option>
									@endforeach
								@endif
							</select>
						</div>

						<div class="form-group col-md-3">
							<label></label>
							<input id="search_product" type="text" class="form-control" placeholder="search product">
						</div>
						
                        <div class="form-group col-md-6">
                            <label>Customer Email</label>
                            
                             
                                <input type="text" class="form-control" name="customer_email" id="customer_email" value="{{array_get($rule,'customer_email')}}" required disabled readonly>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Current Step</label>
								<select class="form-control " name="step" id="step" required>
								<?php 
								foreach(getStepStatus() as $k=>$v){ 	
									$selected = ($k==$rule['step'])?'selected':'';
									echo '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
								}?>
								
								</select>                           
                        </div>
						<div class="form-group col-md-6">
                            <label>Customer Paypal</label>
                            
                             
                                <input type="text" class="form-control" name="customer_paypal_email" id="customer_paypal_email" value="{{array_get($rule,'customer_paypal_email')}}">
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Fund Money</label>
                            	<div class="clearfix"></div>
                             	<div class="form-inline">
                                <input type="text" class="form-control col-md-8" name="transfer_amount" id="transfer_amount" value="{{round(array_get($rule,'transfer_amount'),2)}}">
								
								<select class="form-control col-md-4" name="transfer_currency" id="transfer_currency">
								<?php 
									foreach(getCurrency() as $v){ 
										$selected = ($v==$rule['transfer_currency'])?'selected':'';
										echo '<option value="'.$v.'" '.$selected.'>'.$v.' </option>';
									}?>
								</select>
								</div>
                            
                        </div>
						
						

						
						
						
						<div class="form-group col-md-6">
                            <label>Amazon Order Id</label>
                                <input type="text" class="form-control" name="amazon_order_id" id="amazon_order_id" value="{{array_get($rule,'amazon_order_id')}}">
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Review Url</label>
                                <input type="text" class="form-control" name="review_url" id="review_url" value="{{array_get($rule,'review_url')}}">
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Remark</label>
                                <input type="text" class="form-control" name="transaction_id" id="transaction_id" value="{{array_get($rule,'transaction_id')}}">
                           
                        </div>

						<div class="form-group col-md-6">
							<label>Star rating</label>
							<input type="text" class="form-control" name="star_rating" id="star_rating" value="{{array_get($rule,'star_rating')}}">

						</div>

						{{--<div class="form-group col-md-6">--}}
							{{--<label>Follow</label>--}}
							{{--<input type="text" class="form-control" name="follow" id="follow" value="{{array_get($rule,'follow')}}">--}}

						{{--</div>--}}

						{{--<div class="form-group col-md-6">--}}
							{{--<label>Next follow date</label>--}}
							{{--<input type="text" class="form-control" name="next_follow_date" id="next_follow_date" value="{{array_get($rule,'next_follow_date')}}">--}}

						{{--</div>--}}

						<div class="form-group col-md-6">
							<label>Channel</label>
							<select class="form-control " name="channel" id="channel" required>
								<?php
								foreach(getRsgRequestChannel() as $k=>$v){
									$selected = ($k==$rule['channel'])?'selected':'';
									echo '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
								}?>

							</select>
						</div>

						<div class="form-group col-md-6">
							<label>Customer's FB Name</label>
							<input type="text" class="form-control" name="facebook_name" id="facebook_name" value="{{array_get($rule,'facebook_name')}}">

						</div>

						<div class="form-group col-md-6">
							<label>FB Group</label>
							<input id="facebook_group" class="form-control" name="facebook_group" list="list-facebook_group" placeholder="Facebook Group" value="{{$rule['facebook_group']}}"/>
							<datalist id="list-facebook_group">
								@foreach(getFacebookGroup() as $id=>$name)
									<option value="{!! $id !!} | {!! $name !!}"></option>
								@endforeach
							</datalist>
						</div>

						<div class="form-group col-md-6">
							<label>Auto Send</label>
							<select class="form-control " name="auto_send_status" id="auto_send_status" disabled readonly>
								<option value="0" @if($rule['auto_send_status']==0) selected @endif>Yes</option>
								<option value="1" @if($rule['auto_send_status']==1) selected @endif>No</option>
							</select>
						</div>
						
						@if (array_get($rule,'trans'))
						<table class="table table-hover col-md-12">
							<thead>
								<tr>
									<th> Sender </th>
									<th> Date </th>
									<th> Type </th>
									<th> Receiver </th>
									<th> TransactionID </th>
									<th> Amount </th>
									<th> Status </th>
								</tr>
							</thead>
							<tbody>
								@foreach ($rule['trans'] as $tran)
								<tr>
									<td> {{array_get($tran,'paypal_account')}} </td>
									<td> {{array_get($tran,'timestamp')}} </td>
									<td> {{array_get($tran,'type')}}  </td>
									<td> {{array_get($tran,'payer')}}  </td>
									<td> {{array_get($tran,'transaction_id')}}  </td>
									<td> {{array_get($tran,'gross_amount').' '.array_get($tran,'gross_amount_currency')}}  </td>
									<td> {{array_get($tran,'status')}}  </td>
								</tr>
								 @endforeach
							</tbody>
						</table>
						@endif
						
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
                var site = $(this).val();
                $('#product_id').val('');
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

            // $("#sitesel").trigger("change");
            $("#search_product").trigger("keyup");

        });
	</script>
