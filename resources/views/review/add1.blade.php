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
                    
                </div>



                <div class="tab-pane" id="tab_3">
                    <div class="form-body col-md-8">
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
                                <input type="text" name='creson' id='creson' Value='Please Fill Closed Reson' class="form-control" />
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
