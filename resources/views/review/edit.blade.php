@extends('layouts.layout')
@section('label', 'Edit Review')
@section('content')
<style>
.form-horizontal .form-group {
    margin-left: 0px !important;
    margin-right: 0px !important;
}
</style>
<h1 class="page-title font-red-intense"> Edit Review
        <small>Configure your Review.</small>
    </h1>


    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Review Form</span>
                </div>
            </div>
            <div class="portlet-body form">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                
                    <div class="tabbable-line">
			<form class="form-horizontal"  action="{{ url('review/'.$review['id']) }}" id="submit_form" method="POST">
            <ul class="nav nav-tabs ">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="true"> Review Info </a>
                </li>
   
				
				<li class="">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="false"> Follow Up Step </a>
                </li>
				
				<li class="">
                    <a href="#tab_3" data-toggle="tab" aria-expanded="false"> Amazon Order Info </a>
                </li>
				
				
				<li class="">
                    <a href="#tab_4" data-toggle="tab" aria-expanded="false"> Email History </a>
                </li>
				
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
					<div class="col-md-7">
					<h2 style="margin:20px 0;"><a href="https://{{ $review['site']}}/dp/{{ $review['asin']}}" target="_blank">{{ $review['asin']}}</a></h2>
					<div style="clear:both;"></div>
					 <ul class="chats">
						<li class="in"><img class="avatar" alt="" src="/assets/layouts/layout/img/avatar.png"><div class="message"><span class="arrow"> </span> {{ $review['reviewer_name']}}  &lt; 
						<?php for($i=1;$i<=$review['updated_rating'];$i++){
							echo '<i class="fa fa-star"></i>';

						 }?>
						 &gt;  <span class="datetime"> at {{ $review['date']}} </span> <br/><br/>
						 {!!$review['title']!!} {!!($review['vp'])?'<span class="btn btn-danger btn-xs">VP</span>':''!!} {!!($review['is_delete'])?'<span class="btn btn-danger btn-xs">Deleted</span>':''!!}<p>
						 <span class="body" style="font-size:14px;"><a href="https://{{$review['site']}}/gp/customer-reviews/{{$review['review']}}" target="_blank"> {!!$review['review_content']!!} </a></span></div></li>
					</ul>
					
					<?php if(Auth::user()->admin){ ?>
					<div class="form-group">
						<label>Assign to</label>
						<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-user"></i>
							</span>
							<select class="form-control" name="user_id" id="user_id">

								@foreach ($users as $user_id=>$user_name)
									<option value="{{$user_id}}" <?php if($user_id==$review['user_id']) echo 'selected';?>>{{$user_name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<?php } ?>
					
					<div class="form-group">
                                <label>Amazon Order ID</label>
                                
                                <div class="row">
	
						<div class="col-md-5">
						
													<select id="rebindordersellerid" class="form-control" name="rebindordersellerid">
													<option value="">Auto Match SellerID</option>
													@foreach ($sellerids as $id=>$name)
														<option value="{{$id}}" <?php if($review['seller_id']==$id) echo 'selected';?>>{{$name}}</option>
													@endforeach
													</select> 		
													
						</div>

                        <div class="col-md-7">
						<div class="input-group">
                                                            
													
															
                                                                <input id="rebindorderid" class="form-control" type="text" name="rebindorderid" placeholder="Amazon Order ID" value="{{$review['amazon_order_id']}}"> 
                                                            <span class="input-group-btn">
                                                                <button id="rebindorder" class="btn btn-success" type="button">
                                                                    Get Order</button>
                                                            </span>
                                                        </div>
                            
                        </div>
                        
                        
                  
                                </div>
                            </div>
						
					
						
					<div class="form-group">
						<label>Buyer Email</label>
						<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-envelope"></i>
							</span><input id="buyer_email" class="form-control" type="text" name="buyer_email" placeholder="Buyer Email" value="{{$review['buyer_email']?$review['buyer_email']:array_get($customer,'email')}}">
						 </div>
						
					</div>
					
					
					<div class="form-group">
						<label>Buyer Phone</label>
						<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-envelope"></i>
							</span><input id="buyer_phone" class="form-control" type="text" name="buyer_phone" placeholder="Buyer Phone" value="{{$review['buyer_phone']?$review['buyer_phone']:array_get($customer,'phone')}}">
						 </div>
						
					</div>
					
					<div class="form-group">
						<label>Question Type</label>
						<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-envelope"></i>
							</span>
						   <select class="form-control" name="etype" id="etype">
							<option value="">None</option>
							@foreach (getEType() as $etype)
								<option value="{{$etype}}" <?php if($etype== $review['etype']) echo 'selected';?>>{{$etype}}</option>
							@endforeach
						</select>
						</div>
					</div>
                    <div class="form-group">
                        <label>Remark</label>
                        <div class="input-group ">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span><input id="remark" class="form-control" type="text" name="remark" placeholder="Add Remark" value="{{$review['remark']}}">

                        </div>
                    </div>

					
					<div class="form-group">
						<label>Next follow up Date</label>
						<div class="input-group date date-picker col-md-2" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm " name="nextdate" placeholder="Next follow up Date" value="{{$review['nextdate']}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
							
							
						
					</div>
					
					
					<div class="form-group">
						<label>Customer FeedBack</label>
						<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-envelope"></i>
							</span>
						   <select class="form-control" name="customer_feedback" id="customer_feedback">
							@foreach (getCustomerFb() as $k=>$customer_feedback)
								<option value="{{$k}}" <?php if($k== $review['customer_feedback']) echo 'selected';?>>{{$customer_feedback}}</option>
							@endforeach
						</select>
						</div>
					</div>
					
					
					
					<div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Save</button>
                            </div>
                        </div>
                    </div>
					
					<div style="clear:both;"></div>
					</div>
					
					<div class="col-md-5">
						<?php
						if($customer){
						?>
						<h2 style="margin:20px 0;">CustomerID information</h2>
						
						<p style="margin:20px 0;">
						Email :  {{array_get($customer,'email')}}</p>
						<p style="margin:20px 0;">
						Phone :  {{array_get($customer,'phone')}}</p>
						<p style="margin:20px 0;">
						Other :  {!!array_get($customer,'other')!!}</p>
						<?php } ?>
						
						
						<?php
						if($review_info){
						?>
						<h2 style="margin:20px 0;">ReviewID information</h2>
						
						<p style="margin:20px 0;">
						Email :  {{array_get($review_info,'email')}}</p>
						<p style="margin:20px 0;">
						Phone :  {{array_get($review_info,'phone')}}</p>
						<p style="margin:20px 0;">
						Other :  {!!array_get($review_info,'other')!!}</p>
						<?php } ?>
						
						<?php if(!$customer && !$review_info){ ?>
						<p style="margin:20px 0;">The database does not have contact information for the review. If you need to buy, please save it in the Step ( GetCustomerInfo ).</p>
						<?php } ?>
						
						
					</div>
					 <div style="clear:both;"></div>
                </div>
				
				
				<div class="tab-pane" id="tab_2">
                   	<div class="portlet light" id="form_wizard_1">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class=" icon-layers font-red"></i>
                                            <span class="caption-subject font-red bold uppercase"> Follow Up Steps -
                                                <span class="step-title"> Step 1 of {{ count($steps)+1}} </span>
                                            </span>
                                        </div>
                     
                                    </div>
                                    <div class="portlet-body form">
                                        
                                            <div class="form-wizard">
                                                <div class="">
                                                    <ul class="nav nav-pills nav-justified steps">
														<?php 
														$i=1;
														$step_logs = unserialize($review['follow_content']);
														if(!$step_logs) $step_logs=[];
														foreach($steps as $step){
														
														?>
                                                        <li data-stepid="{{$step->id}}" class="<?php if($i==count($step_logs)) echo 'active'; ?>">
                                                            <a href="#tab{{$step->id}}" data-toggle="tab" class="step" style="padding: 5px 0px;" >
                                                                <span class="number"> {{$i}} </span>
                                                                <span class="desc" style="font-size: 14px; font-weight:bold;">
                                                                    <i class="fa fa-check"></i> {{$step->title}} </span>
                                                            </a>
                                                        </li>
														<?php 
														$i++;
														} ?>
														 <li data-stepid="X" class="<?php if($i==count($step_logs)) echo 'active'; ?>" style="padding: 5px 0px;" >
                                                            <a href="#tabX" data-toggle="tab" class="step">
                                                                <span class="number"> {{$i}} </span>
                                                                <span class="desc" style="font-size: 14px; font-weight:bold;">
                                                                    <i class="fa fa-check"></i> Result </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <div id="bar" class="progress progress-striped" role="progressbar">
                                                        <div class="progress-bar progress-bar-success"> </div>
                                                    </div>
                                                    <div class="tab-content">
													@include('UEditor::head')
														<?php
														
														$i=1;
														foreach($steps as $step){
														
														?>
														                                         
                                                        <div class="tab-pane follow_step <?php if($i==count($step_logs)) echo 'active'; ?>" id="tab{{$step->id}}"  >
															<input type="hidden" name="do_date_{{$step->id}}" id="do_date_{{$step->id}}" value="{{array_get($step_logs,$step->id.'.do_date')}}" />
                                                            <p class="block">{!!$step->content!!}</p>
															
															<div class="form-group">
																<span>Have you commented on this review?</span><br/>
																<label style="margin-right:5em;">
																	<input type="radio" name="commented" id="commented1" value="1"/>
																	Yes
																</label>
																
																<label>
																	<input type="radio" name="commented" id="commented0" value="0" />
																	No
																</label>
															</div>
															
                                                            <div class="form-group">
																<label>Follow Content</label>
																<div class="input-group ">
																	<script id="valuelink_follow_content_{{$step->id}}" name="valuelink_follow_content_{{$step->id}}" type="text/plain">
																		<?php echo array_get($step_logs,$step->id.'.do_content'); ?>
																		</script>
																		<!-- 实例化编辑器 -->
																		<script type="text/javascript">
																			var ue{{$step->id}} = UE.getEditor('valuelink_follow_content_{{$step->id}}');
																			ue{{$step->id}}.ready(function() {
																				ue{{$step->id}}.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
																				ue{{$step->id}}.setHeight(300);
																			});
																		 </script>
																		 
																</div>
															</div>
															
                                                        </div>
														<?php 
														$i++;
														} ?>
														
														<div class="tab-pane follow_step <?php if($i==count($step_logs)) echo 'active'; ?>" id="tabX">
                                                            <h3 class="block">Process Result</h3>
                                                            <div class="form-group">
																<label>Follow Status</label>
																<div class="input-group col-md-6">
																	<span class="input-group-addon">
																		<i class="fa fa-user"></i>
																	</span>
																	<input type="hidden" name="do_date_X" id="do_date_X" value="{{array_get($step_logs,'X.do_date')}}" />
																	<select name="status" class="form-control form-filter input-sm">
																			@foreach (getReviewStatus() as $key=>$val)
																				<option value="{{$key}}" <?php if($key== $review['status']) echo 'selected';?>>{{$val}}</option>
																			@endforeach
																	</select>
																</div>
															</div>
															
															<div class="form-group" id="closedreson" style="display: none;">
																<label>Closed Reson</label>
																<div class="input-group col-md-6">
																	<span class="input-group-addon">
																		<i class="fa fa-user"></i>
																	</span>
																	<select class="form-control" name="creson" id="creson">
																	<option value="">Please Select Closed Reson</option>
																	@foreach (getClosedReson() as $creson)
																		<option value="{{$creson}}" <?php if($creson==$review['creson']) echo 'selected';?>>{{$creson}}</option>
																	@endforeach
																</select>
																</div>
															</div>
	
															
															
															
																							</div>
																						</div>
																					</div>
																					
	
																					<div class="form-actions">
																						<div class="row">
																							<div class="col-md-offset-3 col-md-9">
																								<a href="javascript:;" class="btn default button-previous">
																									<i class="fa fa-angle-left"></i> Back </a>
																								<a href="javascript:;" class="btn btn-outline green button-next"> Continue
																									<i class="fa fa-angle-right"></i>
																								</a>
																								<button type="submit" class="btn blue">Save</button>
                                                                                                <a href="/send/create?from_address=support@claimthegift.com&to_address={{$review['buyer_email']?$review['buyer_email']:array_get($customer,'email')}}&subject=Claim the gift" target="_blank"><button class="btn green" style="width:9em" type="button">Compose</button></a>
																							</div>
																						</div>
																					</div>
																				</div>
																			
																		</div>
																	</div>
														<div style="clear:both;"></div>
               										 </div>
													 
				<div class="tab-pane" id="tab_3">
					<?php
                    if($review['amazon_order_id']){?>
					<div class="row">
                            <div class="col-xs-12">
								
								 <a class="btn btn-lg red-haze hidden-print uppercase print-btn" href="{{ url('exception/create?request_orderid='.$review['amazon_order_id'])}}" target="_blank">Create Refund and Replacement</a>

                            </div>
                        </div>
                    <?php
					}
                    if(isset($order->AmazonOrderId)){?>
                    <div class="invoice-content-2 bordered">
                        <div class="row invoice-head">
                            <div class="col-md-7 col-xs-6">
                                <div class="invoice-logo">
                                    <h1 class="uppercase">{{$order->AmazonOrderId}}  ( {{array_get($sellerids,$order->SellerId)}} )</h1>
                                    Buyer Email : {{$order->BuyerEmail}}<BR>
                                    Buyer Name : {{$order->BuyerName}}<BR>
                                    PurchaseDate : {{$order->PurchaseDate}}
                                </div>
                            </div>
                            <div class="col-md-5 col-xs-6">
                                <div class="company-address">
                                    <span class="bold ">{{$order->Name}}</span>
                                    <br> {{$order->AddressLine1}}
                                    <br> {{$order->AddressLine2}}
                                    <br> {{$order->AddressLine3}}
                                    <br> {{$order->City}} {{$order->StateOrRegion}} {{$order->CountryCode}}
                                    <br> {{$order->PostalCode}}
                                </div>
                            </div>
                        </div>
                        <BR><BR>
                        <div class="row invoice-cust-add">
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Seller ID</h4>
                                <p class="invoice-desc">{{$order->SellerId}}   </p>
                            </div>
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Site</h4>
                                <p class="invoice-desc">{{$order->SalesChannel}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Fulfillment Channel</h4>
                                <p class="invoice-desc">{{$order->FulfillmentChannel}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Ship Service Level</h4>
                                <p class="invoice-desc">{{$order->ShipServiceLevel}}</p>
                            </div>

                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Status</h4>
                                <p class="invoice-desc">{{$order->OrderStatus}}</p>
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
                                    <?php
                                    foreach($order->item as $item){ ?>
                                    <tr>
                                        <td>
                                            <h4>{{$item->ASIN}} ( {{$item->SellerSKU}} )</h4>
                                            <p> {{$item->Title}} </p>
                                        </td>
                                        <td class="text-center sbold">{{$item->QuantityOrdered}}</td>
                                        <td class="text-center sbold">{{round($item->ItemPriceAmount/$item->QuantityOrdered,2)}}</td>
                                        <td class="text-center sbold">{{round($item->ShippingPriceAmount,2)}} {{($item->ShippingDiscountAmount)?'( -'.round($item->ShippingDiscountAmount,2).' )':''}}</td>
                                        <td class="text-center sbold">{{($item->PromotionDiscountAmount)?'( -'.round($item->PromotionDiscountAmount,2).' )':''}}</td>
										<td class="text-center sbold">{{round($item->ItemTaxAmount,2)}}</td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row invoice-subtotal">
                            <div class="col-xs-6">
                                <h4 class="invoice-title uppercase">Total</h4>
                                <p class="invoice-desc grand-total">{{round($order->Amount,2)}} {{$order->CurrencyCode}}</p>
                            </div>
                        </div>
						
                    </div>
                       <?php }else{
                            echo "Can not match or find order";
                        } ?>
                </div>										 
					
					
				<div class="tab-pane" id="tab_4">
					<div class="table-container">
						<table class="table table-striped table-bordered table-hover order-column" id="email_table"  >
                        <thead>
                        <tr>
                            <th>From Address </th>
							<th>To Address </th>
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
				$(function() {
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
								"previous":"Prev",
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
			
			<?php 
			if($step_logs){
			foreach($step_logs as $done_id=>$done_step){ ?>
				<input type="hidden" name="do_id[]" value="{{$done_id}}">
			<?php 
			$last_do_id = $done_id;
			}
			}?>
			</form>
        </div>

               
            </div>
        </div>
    </div>
    <div class="col-md-4">
        

    </div>

    </div>
	
	

<script>

function closedreson(){
	if($("select[name='status']").val()==6){
		$('#closedreson').show();
	}else{
		$('#closedreson').hide();
	}
}



$(function() {
	closedreson();
	$("#commented{{$review['commented']}}").attr("checked","checked");
    $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
	$("select[name='status']").change(function(){
		closedreson();
	});
	var form = $('#submit_form');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            var handleTitle = function(tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#form_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#form_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }
				

                if (current == 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                    //$('#form_wizard_1').find('.button-submit').show();
                    //displayConfirm();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
            }

            $('#form_wizard_1').bootstrapWizard({
                'nextSelector': '.button-next',
                'previousSelector': '.button-previous',
				onInit: function(tab, navigation, index) {
					handleTitle(tab, navigation, {{count($step_logs)?count($step_logs)-1:0}});
				},
                onTabClick: function (tab, navigation, index, clickedIndex) {
                    success.hide();
                    error.hide();
					if(index+1==clickedIndex){
						handleTitle(tab, navigation, index+1);
					}else{
						var checkifisback = false;
						$("input[name='do_id[]']").each(function(){
							if($(this).val()==navigation.find('li').eq(clickedIndex).data('stepid')) checkifisback = true;
						});
						if(checkifisback){
							handleTitle(tab, navigation, clickedIndex);
						}else{
							return false;
						}
					}
                },
                onNext: function (tab, navigation, index) {

                    success.hide();
                    error.hide();

                    handleTitle(tab, navigation, index);
                },
                onPrevious: function (tab, navigation, index) {

                    success.hide();
                    error.hide();

                    handleTitle(tab, navigation, index);
                },
                onTabShow: function (tab, navigation, index) {
					
                    var total = navigation.find('li').length;
                    var current = index + 1;
                    var $percent = (current / total) * 100;
					var checkifisexists = false;
					var stepid = navigation.find('li').eq(index).data('stepid');
 					$("input[name='do_id[]']").each(function(){
						if($(this).val()==stepid) checkifisexists = true;
						if(stepid!='X'){
							if($(this).val()=='X'){
								$(this).remove();
							}else if($(this).val()>stepid){
								$(this).remove();
							} 
						}
				    });
					if(!checkifisexists){
						$('#form_wizard_1').append('<input type="hidden" name="do_id[]" value="'+stepid+'">');
					}

					if($("#do_date_"+stepid).val()==''){

						var dd = new Date();
						var y = dd.getFullYear();
						var m = (dd.getMonth()+1)<10 ? ('0'+(dd.getMonth()+1)) : (dd.getMonth()+1);
						var d = dd.getDate() <10 ? ('0'+ dd.getDate()) :dd.getDate();
						var h = dd.getHours() <10 ? ('0'+ dd.getHours()) :dd.getHours();
						var i = dd.getMinutes() <10 ? ('0'+ dd.getMinutes()) :dd.getMinutes();
						var s = dd.getSeconds() <10 ? ('0'+ dd.getSeconds()) :dd.getSeconds();
						$("#do_date_"+stepid).val(y+"-"+m+"-"+d+" "+h+":"+i+":"+s);
				    }
					//alert($("#do_date_"+stepid).val());
                    $('#form_wizard_1').find('.progress-bar').css({
                        width: $percent + '%'
                    });

                }
            });
				
            //$('#form_wizard_1').find('.button-previous').hide();
	$("#rebindorder").click(function(){
	  $.post("/saporder/get",
	  {
	  	"_token":"{{csrf_token()}}",
		"inboxid":0,
		"sellerid":$("#rebindordersellerid").val(),
		"orderid":$("#rebindorderid").val()
	  },
	  function(data,status){
	  	if(status=='success'){
	  		var redata = JSON.parse(data);
			if(redata.result==1){
				toastr.success(redata.message);
				if($("input[name='buyer_email']").val()!=''){
					var r=confirm("Replace existing buyer email?");
					if (r==true){
						if(redata.buyeremail) $("input[name='buyer_email']").val(redata.buyeremail);
					}
				}else{
					if(redata.buyeremail) $("input[name='buyer_email']").val(redata.buyeremail);
				}
				if(redata.sellerid) $("select[name='rebindordersellerid']").val(redata.sellerid);
				if(redata.orderhtml) $("#tab_3").html(redata.orderhtml);
			}else{
				toastr.error(redata.message);
			}	
		}

	  });
	});
			
});


</script>
@endsection
