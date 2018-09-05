@extends('layouts.layout')
@section('label', 'Edit Review')
@section('content')
<h1 class="page-title font-red-intense"> Edit Review
        <small>Configure your Review.</small>
    </h1>


    <div class="row"><div class="col-md-8">
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
                    <div class="form-body">
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
                                    <script id="valuelink_review_content" name="review_content" type="text/plain">
									<?php echo old('review_content'); ?>
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue = UE.getEditor('valuelink_review_content');
                                        ue.ready(function() {
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                        });
                               		 </script>
	
                            </div>
                        </div>
						
						
						<div class="form-group">
                            <label>Buyer Email</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Buyer Email" name="buyer_email" id="buyer_email" value="{{old('buyer_email')}}" />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Amazon OrderId</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Amazon OrderId" name="amazon_order_id" id="amazon_order_id" value="{{old('amazon_order_id')}}" />
                            </div>
                        </div>
						
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
						
						
						<div class="form-group">
                            <label>Follow Content</label>
                            <div class="input-group ">
                                <script id="valuelink_follow_content" name="content" type="text/plain">
									<?php echo  old('content'); ?>
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue = UE.getEditor('valuelink_follow_content');
                                        ue.ready(function() {
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
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
                                        var ue = UE.getEditor('valuelink_edescription_content');
                                        ue.ready(function() {
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                        });
                               		 </script>
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

$(function() {
    $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
});


</script>
@endsection
