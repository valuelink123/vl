@extends('layouts.layout')
@section('label', 'Edit RSG Products')
@section('content')
<script>
    window.UEDITOR_HOME_URL = "/laravel-u-editor/";//配置编辑器的文件路径
</script>
@include('UEditor::head')

    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">

            <div class="portlet-body form">
                <form role="form" action="{{ url('rsgproducts/'.$rule['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="form-body">
						<div class="form-group col-md-6">
                            <label>Start Date</label>
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
								<input type="text" class="form-control" readonly name="start_date" placeholder="From" value="{{array_get($rule,'start_date')}}" required>
								<span class="input-group-btn">
									<button class="btn default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
							</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
								<input type="text" class="form-control" readonly name="end_date" placeholder="To" value="{{array_get($rule,'end_date')}}" required>
								<span class="input-group-btn">
									<button class="btn   default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
							</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Account</label>
                            
                                
								<select name="seller_id" class="form-control" required disabled readonly>
								   <?php 
									foreach($accounts as $k=>$v){ 
										$selected = ($k==$rule['seller_id'])?'selected':'';
										echo '<option value="'.$k.'" '.$selected.'>'.$v.' ( '.$k.' ) </option>';
									}?>
								</select>
                            
                        </div>
                        <div class="form-group col-md-6">
                            <label>Site</label>
                           
                                
                                <select name="site" class="form-control " required disabled readonly>
								   <?php 
									foreach(getAsinSites() as $v){ 	
										$selected = ($v==$rule['site'])?'selected':'';
										echo '<option value="'.$v.'" '.$selected.'>'.$v.' </option>';
									}?>
								</select>
                            
                        </div>
                        <div class="form-group col-md-6">
                            <label>Asin</label>
                            
                             
                                <input type="text" class="form-control" name="asin" id="asin" value="{{array_get($rule,'asin')}}" required disabled readonly>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Daily Gift Quantity</label>
                            
                      
                                <input type="text" class="form-control" name="daily_stock" id="daily_stock" value="{{array_get($rule,'daily_stock')}}" required>
                           
                        </div>
						<div class="form-group col-md-6">
                            <label>Product Name</label>
                            
                             
                                <input type="text" class="form-control" name="product_name" id="product_name" value="{{array_get($rule,'product_name')}}" required>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Product Image</label>
                            
                      
                                <input type="text" class="form-control" name="product_img" id="product_img" value="{{array_get($rule,'product_img')}}" required>
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Price</label>
                            	<div class="clearfix"></div>
                             	<div class="form-inline">
                                <input type="text" class="form-control col-md-8" name="price" id="price" value="{{round(array_get($rule,'price'),2)}}" required>
								
								<select class="form-control col-md-4" name="currency" id="currency" required>
								<?php 
									foreach(getCurrency() as $v){ 
										$selected = ($v==$rule['currency'])?'selected':'';
										echo '<option value="'.$v.'" '.$selected.'>'.$v.' </option>';
									}?>
								</select>
								</div>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Purchase Search Keywords</label>
                            
                      
                                <input type="text" class="form-control" name="keyword" id="keyword" value="{{array_get($rule,'keyword')}}">
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Purchase Search Page No.</label>
                            
                      
                                <input type="text" class="form-control" name="page" id="page" value="{{intval(array_get($rule,'page'))}}" >
                           
                        </div>
						
						
						<div class="form-group col-md-6">
                            <label>Purchase Search Position</label>
                            
                      
                                <input type="text" class="form-control" name="position" id="position" value="{{intval(array_get($rule,'position'))}}" >
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Reviews Needed</label>
                            
                      
                                <input type="text" class="form-control" name="positive_target" id="positive_target" value="{{intval(array_get($rule,'positive_target'))}}">
                           
                        </div>
						
						
						<div class="form-group col-md-6">
                            <label>Reviews needed weekly</label>
                            
                      
                                <input type="text" class="form-control" name="positive_daily_limit" id="positive_daily_limit" value="{{intval(array_get($rule,'positive_daily_limit'))}}">
                           
                        </div>

                        <div class="form-group col-md-6">
                            <label>Review Rating</label>


                            <input type="text" class="form-control" name="review_rating" id="review_rating" value="{{intval(array_get($rule,'review_rating'))}}">

                        </div>

                        <div class="form-group col-md-6">
                            <label>Number of reviews</label>


                            <input type="text" class="form-control" name="number_of_reviews" id="number_of_reviews" value="{{intval(array_get($rule,'number_of_reviews'))}}">

                        </div>

                        <div class="form-group col-md-6">
                            <label>Sales' target reviews</label>
                            <input type="text" class="form-control" name="sales_target_reviews" id="sales_target_reviews" value="{{intval(array_get($rule,'sales_target_reviews'))}}" required pattern="[0-9]*">

                        </div>

                        <div class="form-group col-md-8">
                            <label>Product Summary</label>
                            <textarea class="form-control"  style="height:300px;" name="product_summary">{{array_get($rule,'product_summary')}}</textarea>

                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Product Content</label>
                                <script id="bdeditor" name="product_content" type="text/plain"><?php echo $rule['product_content']; ?></script>
                            </div>
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
$('.date-picker').datepicker({
	rtl: App.isRTL(),
	autoclose: true
});

    var ue = UE.getEditor('bdeditor');
    ue.ready(function() {
        ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
    });

});
</script>
@endsection
