@extends('layouts.layout')
@section('label', 'Edit Review')
@section('content')
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
                <form role="form" action="{{ url('review') }}" method="POST">
                    {{ csrf_field() }}
					
					<div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="true"> Review Info</a>
                </li>
                <li class="">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="false"> Amazon Order Info </a>
                </li>
   
                <li class="">
                    <a href="#tab_3" data-toggle="tab" aria-expanded="false"> Follow Up Info </a>
                </li>
				
				<li class="">
                    <a href="#tab_4" data-toggle="tab" aria-expanded="false"> Step </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
					<div class="form-body col-md-8">
                        <div class="form-group">
                            <label>Review ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Review" name="review" id="review" value="{{old('review')}}" required />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Site</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="site">
									@foreach (getAsinSites() as $site)
										<option value="{{$site}}" <?php if($site==old('site')) echo 'selected';?>>{{$site}}</option>
									@endforeach
								</select>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Review Date</label>
                            <div class="input-group ">
 
								
								<div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control" placeholder="Review Date" name="date" id="date" value="{{old('date')}}" required />
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Account</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Account" name="amazon_account" id="amazon_account" value="{{old('amazon_account')}}" required />
                            </div>
                        </div>
						
						
						<div class="form-group">
                            <label>Reviewer Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Reviewer Name" name="reviewer_name" id="reviewer_name" value="{{old('reviewer_name')}}" required />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Asin</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Asin" name="asin" id="asin" value="{{old('asin')}}" required />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>SellerSku</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="SellerSku" name="sellersku" id="sellersku" value="{{old('sellersku')}}" required />
                            </div>
                        </div>
						
					
						
						<div class="form-group">
                            <label>Rating</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="rating">
								<?php for($i=1;$i<=5;$i++){
									$selected='';
									if($i==old('rating')) $selected='selected';
									echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';

								 }?>
                                 </select>
                            </div>
                        </div>
						
						
						<div class="form-group">
                            <label>Review Content</label>
                            <div class="input-group ">
                  
								
								@include('UEditor::head')

                                    <!-- 加载编辑器的容器 -->
                                    <script id="valuelink_review_content" name="review_content" type="text/plain" >
									<?php echo old('review_content'); ?>
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue = UE.getEditor('valuelink_review_content');
                                        ue.ready(function() {
											ue.setHeight(100);
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');
											
                                        });
                               		 </script>
	
                            </div>
                        </div>
						</div>
						 <div style="clear:both;"></div>
                </div>


                <div class="tab-pane" id="tab_2">

                       <div class="form-body col-md-8">
	
						<div class="form-group">
                            <label>Seller ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
								<input id="seller_id" class="form-control" name="seller_id"  type="text" placeholder="Amazon Seller ID" value="{{old('seller_id')}}" />
														
													
						</div>
                            
                        </div>

						<div class="form-group">
                            <label>Amazon Order ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                                            <input id="amazon_order_id" class="form-control" type="text" name="amazon_order_id" value="{{old('amazon_order_id')}}" placeholder="Amazon Order ID" />
						
						</div>
                            
                        </div>
						
                        <div class="form-group">
                            <label>Buyer Email</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span><input id="buyer_email" class="form-control" type="text" name="buyer_email" placeholder="Buyer Email" value="{{old('buyer_email')}}">
                             </div>
                            
                        </div>
                        <div style="clear:both;"></div>
                        
                    </div>
 					<div style="clear:both;"></div>
                    <?php
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
						 <div style="clear:both;"></div>
                </div>



                <div class="tab-pane" id="tab_3">
                    <div class="form-body col-md-8">
						<?php if(Auth::user()->admin){ ?>
						<div class="form-group">
                            <label>Assign to</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control" name="user_id" id="user_id">

                                    @foreach ($users as $user_id=>$user_name)
                                        <option value="{{$user_id}}">{{$user_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
						<?php } ?>
						
                        <div class="form-group">
                            <label>Follow Status</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select name="status" class="form-control form-filter input-sm">
										@foreach (getReviewStatus() as $key=>$val)
											<option value="{{$key}}" <?php if($key==old('status')) echo 'selected';?>>{{$val}}</option>
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
                                    <option value="{{$creson}}" <?php if($creson==old('creson')) echo 'selected';?>>{{$creson}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Follow Content</label>
                            <div class="input-group ">
                                <script id="valuelink_follow_content" name="content" type="text/plain">
									<?php echo  old('content'); ?>
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue1 = UE.getEditor('valuelink_follow_content');
                                        ue1.ready(function() {
                                            ue1.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
											ue1.setHeight(100);
                                        });
                               		 </script>
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
                                    <option value="{{$etype}}" <?php if($etype==old('etype')) echo 'selected';?>>{{$etype}}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Problem Point</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Question Point" name="epoint" id="epoint" value="{{old('epoint')}}"  />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Add Remark</label>
                            <div class="input-group ">
                                <script id="valuelink_edescription_content" name="edescription" type="text/plain">
									<?php echo old('edescription'); ?>
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue2 = UE.getEditor('valuelink_edescription_content');
                                        ue2.ready(function() {
                                        
										    ue2.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
											ue2.setHeight(100);
                                        });
                               		 </script>
                            </div>
                        </div>
						</div>
                    <div style="clear:both;"></div>
                </div>
				
				
				
				
				<div class="tab-pane" id="tab_4">
                   
                    <div style="clear:both;"></div>
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
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        

    </div>

    </div>
	
	<div class="portlet light bordered" id="form_wizard_1">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class=" icon-layers font-red"></i>
                                            <span class="caption-subject font-red bold uppercase"> Form Wizard -
                                                <span class="step-title"> Step 1 of 4 </span>
                                            </span>
                                        </div>
                                        <div class="actions">
                                            <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                                                <i class="icon-cloud-upload"></i>
                                            </a>
                                            <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                                                <i class="icon-wrench"></i>
                                            </a>
                                            <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                                                <i class="icon-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form class="form-horizontal" action="#" id="submit_form" method="POST">
                                            <div class="form-wizard">
                                                <div class="form-body">
                                                    <ul class="nav nav-pills nav-justified steps">
                                                        <li>
                                                            <a href="#tab1" data-toggle="tab" class="step">
                                                                <span class="number"> 1 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Account Setup </span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#tab2" data-toggle="tab" class="step">
                                                                <span class="number"> 2 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Profile Setup </span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#tab3" data-toggle="tab" class="step active">
                                                                <span class="number"> 3 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Billing Setup </span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#tab4" data-toggle="tab" class="step">
                                                                <span class="number"> 4 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Confirm </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <div id="bar" class="progress progress-striped" role="progressbar">
                                                        <div class="progress-bar progress-bar-success"> </div>
                                                    </div>
                                                    <div class="tab-content">
                                                        <div class="alert alert-danger display-none">
                                                            <button class="close" data-dismiss="alert"></button> You have some form errors. Please check below. </div>
                                                        <div class="alert alert-success display-none">
                                                            <button class="close" data-dismiss="alert"></button> Your form validation is successful! </div>
                                                        <div class="tab-pane active" id="tab1">
                                                            <h3 class="block">Provide your account details</h3>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Username
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="username" />
                                                                    <span class="help-block"> Provide your username </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Password
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="password" class="form-control" name="password" id="submit_form_password" />
                                                                    <span class="help-block"> Provide your password. </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Confirm Password
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="password" class="form-control" name="rpassword" />
                                                                    <span class="help-block"> Confirm your password </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Email
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="email" />
                                                                    <span class="help-block"> Provide your email address </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="tab2">
                                                            <h3 class="block">Provide your profile details</h3>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Fullname
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="fullname" />
                                                                    <span class="help-block"> Provide your fullname </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Phone Number
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="phone" />
                                                                    <span class="help-block"> Provide your phone number </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Gender
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <div class="radio-list">
                                                                        <label>
                                                                            <input type="radio" name="gender" value="M" data-title="Male" /> Male </label>
                                                                        <label>
                                                                            <input type="radio" name="gender" value="F" data-title="Female" /> Female </label>
                                                                    </div>
                                                                    <div id="form_gender_error"> </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Address
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="address" />
                                                                    <span class="help-block"> Provide your street address </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">City/Town
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="city" />
                                                                    <span class="help-block"> Provide your city or town </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Country</label>
                                                                <div class="col-md-4">
                                                                    <select name="country" id="country_list" class="form-control">
                                                                        <option value=""></option>
                                                                        <option value="AF">Afghanistan</option>
                                                                        <option value="AL">Albania</option>
                                                                        <option value="DZ">Algeria</option>
                                                                        <option value="AS">American Samoa</option>
                                                                        <option value="AD">Andorra</option>
                                                                        <option value="AO">Angola</option>
                                                                        <option value="AI">Anguilla</option>
                                                                        <option value="AR">Argentina</option>
                                                                        <option value="AM">Armenia</option>
                                                                        <option value="AW">Aruba</option>
                                                                        <option value="AU">Australia</option>
                                                                        <option value="AT">Austria</option>
                                                                        <option value="AZ">Azerbaijan</option>
                                                                        <option value="BS">Bahamas</option>
                                                                        <option value="BH">Bahrain</option>
                                                                        <option value="BD">Bangladesh</option>
                                                                        <option value="BB">Barbados</option>
                                                                        <option value="BY">Belarus</option>
                                                                        <option value="BE">Belgium</option>
                                                                        <option value="BZ">Belize</option>
                                                                        <option value="BJ">Benin</option>
                                                                        <option value="BM">Bermuda</option>
                                                                        <option value="BT">Bhutan</option>
                                                                        <option value="BO">Bolivia</option>
                                                                        <option value="BA">Bosnia and Herzegowina</option>
                                                                        <option value="BW">Botswana</option>
                                                                        <option value="BV">Bouvet Island</option>
                                                                        <option value="BR">Brazil</option>
                                                                        <option value="IO">British Indian Ocean Territory</option>
                                                                        <option value="BN">Brunei Darussalam</option>
                                                                        <option value="BG">Bulgaria</option>
                                                                        <option value="BF">Burkina Faso</option>
                                                                        <option value="BI">Burundi</option>
                                                                        <option value="KH">Cambodia</option>
                                                                        <option value="CM">Cameroon</option>
                                                                        <option value="CA">Canada</option>
                                                                        <option value="CV">Cape Verde</option>
                                                                        <option value="KY">Cayman Islands</option>
                                                                        <option value="CF">Central African Republic</option>
                                                                        <option value="TD">Chad</option>
                                                                        <option value="CL">Chile</option>
                                                                        <option value="CN">China</option>
                                                                        <option value="CX">Christmas Island</option>
                                                                        <option value="CC">Cocos (Keeling) Islands</option>
                                                                        <option value="CO">Colombia</option>
                                                                        <option value="KM">Comoros</option>
                                                                        <option value="CG">Congo</option>
                                                                        <option value="CD">Congo, the Democratic Republic of the</option>
                                                                        <option value="CK">Cook Islands</option>
                                                                        <option value="CR">Costa Rica</option>
                                                                        <option value="CI">Cote d'Ivoire</option>
                                                                        <option value="HR">Croatia (Hrvatska)</option>
                                                                        <option value="CU">Cuba</option>
                                                                        <option value="CY">Cyprus</option>
                                                                        <option value="CZ">Czech Republic</option>
                                                                        <option value="DK">Denmark</option>
                                                                        <option value="DJ">Djibouti</option>
                                                                        <option value="DM">Dominica</option>
                                                                        <option value="DO">Dominican Republic</option>
                                                                        <option value="EC">Ecuador</option>
                                                                        <option value="EG">Egypt</option>
                                                                        <option value="SV">El Salvador</option>
                                                                        <option value="GQ">Equatorial Guinea</option>
                                                                        <option value="ER">Eritrea</option>
                                                                        <option value="EE">Estonia</option>
                                                                        <option value="ET">Ethiopia</option>
                                                                        <option value="FK">Falkland Islands (Malvinas)</option>
                                                                        <option value="FO">Faroe Islands</option>
                                                                        <option value="FJ">Fiji</option>
                                                                        <option value="FI">Finland</option>
                                                                        <option value="FR">France</option>
                                                                        <option value="GF">French Guiana</option>
                                                                        <option value="PF">French Polynesia</option>
                                                                        <option value="TF">French Southern Territories</option>
                                                                        <option value="GA">Gabon</option>
                                                                        <option value="GM">Gambia</option>
                                                                        <option value="GE">Georgia</option>
                                                                        <option value="DE">Germany</option>
                                                                        <option value="GH">Ghana</option>
                                                                        <option value="GI">Gibraltar</option>
                                                                        <option value="GR">Greece</option>
                                                                        <option value="GL">Greenland</option>
                                                                        <option value="GD">Grenada</option>
                                                                        <option value="GP">Guadeloupe</option>
                                                                        <option value="GU">Guam</option>
                                                                        <option value="GT">Guatemala</option>
                                                                        <option value="GN">Guinea</option>
                                                                        <option value="GW">Guinea-Bissau</option>
                                                                        <option value="GY">Guyana</option>
                                                                        <option value="HT">Haiti</option>
                                                                        <option value="HM">Heard and Mc Donald Islands</option>
                                                                        <option value="VA">Holy See (Vatican City State)</option>
                                                                        <option value="HN">Honduras</option>
                                                                        <option value="HK">Hong Kong</option>
                                                                        <option value="HU">Hungary</option>
                                                                        <option value="IS">Iceland</option>
                                                                        <option value="IN">India</option>
                                                                        <option value="ID">Indonesia</option>
                                                                        <option value="IR">Iran (Islamic Republic of)</option>
                                                                        <option value="IQ">Iraq</option>
                                                                        <option value="IE">Ireland</option>
                                                                        <option value="IL">Israel</option>
                                                                        <option value="IT">Italy</option>
                                                                        <option value="JM">Jamaica</option>
                                                                        <option value="JP">Japan</option>
                                                                        <option value="JO">Jordan</option>
                                                                        <option value="KZ">Kazakhstan</option>
                                                                        <option value="KE">Kenya</option>
                                                                        <option value="KI">Kiribati</option>
                                                                        <option value="KP">Korea, Democratic People's Republic of</option>
                                                                        <option value="KR">Korea, Republic of</option>
                                                                        <option value="KW">Kuwait</option>
                                                                        <option value="KG">Kyrgyzstan</option>
                                                                        <option value="LA">Lao People's Democratic Republic</option>
                                                                        <option value="LV">Latvia</option>
                                                                        <option value="LB">Lebanon</option>
                                                                        <option value="LS">Lesotho</option>
                                                                        <option value="LR">Liberia</option>
                                                                        <option value="LY">Libyan Arab Jamahiriya</option>
                                                                        <option value="LI">Liechtenstein</option>
                                                                        <option value="LT">Lithuania</option>
                                                                        <option value="LU">Luxembourg</option>
                                                                        <option value="MO">Macau</option>
                                                                        <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                                                                        <option value="MG">Madagascar</option>
                                                                        <option value="MW">Malawi</option>
                                                                        <option value="MY">Malaysia</option>
                                                                        <option value="MV">Maldives</option>
                                                                        <option value="ML">Mali</option>
                                                                        <option value="MT">Malta</option>
                                                                        <option value="MH">Marshall Islands</option>
                                                                        <option value="MQ">Martinique</option>
                                                                        <option value="MR">Mauritania</option>
                                                                        <option value="MU">Mauritius</option>
                                                                        <option value="YT">Mayotte</option>
                                                                        <option value="MX">Mexico</option>
                                                                        <option value="FM">Micronesia, Federated States of</option>
                                                                        <option value="MD">Moldova, Republic of</option>
                                                                        <option value="MC">Monaco</option>
                                                                        <option value="MN">Mongolia</option>
                                                                        <option value="MS">Montserrat</option>
                                                                        <option value="MA">Morocco</option>
                                                                        <option value="MZ">Mozambique</option>
                                                                        <option value="MM">Myanmar</option>
                                                                        <option value="NA">Namibia</option>
                                                                        <option value="NR">Nauru</option>
                                                                        <option value="NP">Nepal</option>
                                                                        <option value="NL">Netherlands</option>
                                                                        <option value="AN">Netherlands Antilles</option>
                                                                        <option value="NC">New Caledonia</option>
                                                                        <option value="NZ">New Zealand</option>
                                                                        <option value="NI">Nicaragua</option>
                                                                        <option value="NE">Niger</option>
                                                                        <option value="NG">Nigeria</option>
                                                                        <option value="NU">Niue</option>
                                                                        <option value="NF">Norfolk Island</option>
                                                                        <option value="MP">Northern Mariana Islands</option>
                                                                        <option value="NO">Norway</option>
                                                                        <option value="OM">Oman</option>
                                                                        <option value="PK">Pakistan</option>
                                                                        <option value="PW">Palau</option>
                                                                        <option value="PA">Panama</option>
                                                                        <option value="PG">Papua New Guinea</option>
                                                                        <option value="PY">Paraguay</option>
                                                                        <option value="PE">Peru</option>
                                                                        <option value="PH">Philippines</option>
                                                                        <option value="PN">Pitcairn</option>
                                                                        <option value="PL">Poland</option>
                                                                        <option value="PT">Portugal</option>
                                                                        <option value="PR">Puerto Rico</option>
                                                                        <option value="QA">Qatar</option>
                                                                        <option value="RE">Reunion</option>
                                                                        <option value="RO">Romania</option>
                                                                        <option value="RU">Russian Federation</option>
                                                                        <option value="RW">Rwanda</option>
                                                                        <option value="KN">Saint Kitts and Nevis</option>
                                                                        <option value="LC">Saint LUCIA</option>
                                                                        <option value="VC">Saint Vincent and the Grenadines</option>
                                                                        <option value="WS">Samoa</option>
                                                                        <option value="SM">San Marino</option>
                                                                        <option value="ST">Sao Tome and Principe</option>
                                                                        <option value="SA">Saudi Arabia</option>
                                                                        <option value="SN">Senegal</option>
                                                                        <option value="SC">Seychelles</option>
                                                                        <option value="SL">Sierra Leone</option>
                                                                        <option value="SG">Singapore</option>
                                                                        <option value="SK">Slovakia (Slovak Republic)</option>
                                                                        <option value="SI">Slovenia</option>
                                                                        <option value="SB">Solomon Islands</option>
                                                                        <option value="SO">Somalia</option>
                                                                        <option value="ZA">South Africa</option>
                                                                        <option value="GS">South Georgia and the South Sandwich Islands</option>
                                                                        <option value="ES">Spain</option>
                                                                        <option value="LK">Sri Lanka</option>
                                                                        <option value="SH">St. Helena</option>
                                                                        <option value="PM">St. Pierre and Miquelon</option>
                                                                        <option value="SD">Sudan</option>
                                                                        <option value="SR">Suriname</option>
                                                                        <option value="SJ">Svalbard and Jan Mayen Islands</option>
                                                                        <option value="SZ">Swaziland</option>
                                                                        <option value="SE">Sweden</option>
                                                                        <option value="CH">Switzerland</option>
                                                                        <option value="SY">Syrian Arab Republic</option>
                                                                        <option value="TW">Taiwan, Province of China</option>
                                                                        <option value="TJ">Tajikistan</option>
                                                                        <option value="TZ">Tanzania, United Republic of</option>
                                                                        <option value="TH">Thailand</option>
                                                                        <option value="TG">Togo</option>
                                                                        <option value="TK">Tokelau</option>
                                                                        <option value="TO">Tonga</option>
                                                                        <option value="TT">Trinidad and Tobago</option>
                                                                        <option value="TN">Tunisia</option>
                                                                        <option value="TR">Turkey</option>
                                                                        <option value="TM">Turkmenistan</option>
                                                                        <option value="TC">Turks and Caicos Islands</option>
                                                                        <option value="TV">Tuvalu</option>
                                                                        <option value="UG">Uganda</option>
                                                                        <option value="UA">Ukraine</option>
                                                                        <option value="AE">United Arab Emirates</option>
                                                                        <option value="GB">United Kingdom</option>
                                                                        <option value="US">United States</option>
                                                                        <option value="UM">United States Minor Outlying Islands</option>
                                                                        <option value="UY">Uruguay</option>
                                                                        <option value="UZ">Uzbekistan</option>
                                                                        <option value="VU">Vanuatu</option>
                                                                        <option value="VE">Venezuela</option>
                                                                        <option value="VN">Viet Nam</option>
                                                                        <option value="VG">Virgin Islands (British)</option>
                                                                        <option value="VI">Virgin Islands (U.S.)</option>
                                                                        <option value="WF">Wallis and Futuna Islands</option>
                                                                        <option value="EH">Western Sahara</option>
                                                                        <option value="YE">Yemen</option>
                                                                        <option value="ZM">Zambia</option>
                                                                        <option value="ZW">Zimbabwe</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Remarks</label>
                                                                <div class="col-md-4">
                                                                    <textarea class="form-control" rows="3" name="remarks"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="tab3">
                                                            <h3 class="block">Provide your billing and credit card details</h3>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Card Holder Name
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="card_name" />
                                                                    <span class="help-block"> </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Card Number
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="card_number" />
                                                                    <span class="help-block"> </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">CVC
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <input type="text" placeholder="" class="form-control" name="card_cvc" />
                                                                    <span class="help-block"> </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Expiration(MM/YYYY)
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">

                                                                    <input type="text" placeholder="MM/YYYY" maxlength="7" class="form-control" name="card_expiry_date" />
                                                                    <span class="help-block"> e.g 11/2020 </span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Payment Options
                                                                    <span class="required"> * </span>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <div class="checkbox-list">
                                                                        <label>
                                                                            <input type="checkbox" name="payment[]" value="1" data-title="Auto-Pay with this Credit Card." /> Auto-Pay with this Credit Card </label>
                                                                        <label>
                                                                            <input type="checkbox" name="payment[]" value="2" data-title="Email me monthly billing." /> Email me monthly billing </label>
                                                                    </div>
                                                                    <div id="form_payment_error"> </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="tab4">
                                                            <h3 class="block">Confirm your account</h3>
                                                            <h4 class="form-section">Account</h4>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Username:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="username"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Email:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="email"> </p>
                                                                </div>
                                                            </div>
                                                            <h4 class="form-section">Profile</h4>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Fullname:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="fullname"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Gender:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="gender"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Phone:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="phone"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Address:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="address"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">City/Town:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="city"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Country:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="country"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Remarks:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="remarks"> </p>
                                                                </div>
                                                            </div>
                                                            <h4 class="form-section">Billing</h4>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Card Holder Name:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="card_name"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Card Number:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="card_number"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">CVC:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="card_cvc"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Expiration:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="card_expiry_date"> </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">Payment Options:</label>
                                                                <div class="col-md-4">
                                                                    <p class="form-control-static" data-display="payment[]"> </p>
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
                                                            <a href="javascript:;" class="btn green button-submit"> Submit
                                                                <i class="fa fa-check"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
<script src="/assets/pages/scripts/form-wizard.min.js" type="text/javascript"></script>
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
    $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
	$("select[name='status']").change(function(){
		closedreson();
	});
	
});


</script>
@endsection
