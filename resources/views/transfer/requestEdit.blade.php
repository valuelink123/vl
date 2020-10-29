@extends('layouts.layout')
@section('label', 'Edit A Transfer Request')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
		<div class="col-lg-9">
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
							<span class="caption-subject bold font-green">Edit A Transfer Request</span>
						</div>
					</div>
					<div class="portlet-body" style="min-height:700px !important;">
						<div class="tabbable-line">
							<form  action="/transfer/request/edit" id="form" novalidate method="POST" onsubmit="return validate_form()">
								{{ csrf_field() }}
							<div class="">
								<input  type="hidden" value="{!! $data['id'] !!}" name="id">
								<div class="col-lg-8">
									<div class="form-group">
										<label>Site</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select class="form-control"  id="site" name="site" onchange="getAccountBySite()" {!! $showtype !!}>
												<option value="">Select</option>
												@foreach(getSiteCode() as $key=>$val)
													<option value="{!! $val !!}" @if($val==$data['marketplace_id']) selected @endif readonly="readonly">{!! $key !!}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="form-group">
										<label>Account</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select class="form-control"  id="account" name="account" account-value="{!! $data['seller_id'] !!}" {!! $showtype !!} >
												<option value="">Select</option>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label>Delivery Date</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<div class="input-group">
												<input  class="form-control" value="{!! $data['delivery_date'] !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="date" name="date" {!! $showtype !!}/>
											</div>
										</div>
									</div>

									<div class="form-group">
										<label>Rms Sku</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="rms_sku" id="rms_sku" value="{!! $data['rms_sku'] !!}"  {!! $showtype !!}>
										</div>
									</div>

									<div class="form-group">
										<label>Reason</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="request_reason" id="request_reason" value="{!! $data['request_reason'] !!}"  {!! $showtype !!}>
										</div>
									</div>

									<div class="col-md-6" style="margin-left:-15px;">
										<div class="form-group">
											<label>Transfer Request Key</label>
											<div class="input-group ">
												<span class="input-group-addon">
													<i class="fa fa-bookmark"></i>
												</span>
												<input type="text" class="form-control" value="{!! $data['transfer_request_key'] !!}"  disabled="disabled">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>Sku</label>
											<div class="input-group ">
												<span class="input-group-addon">
													<i class="fa fa-bookmark"></i>
												</span>
												<input type="text" class="form-control" value="{!! $data['sku'] !!}" disabled="disabled" >
											</div>
										</div>
									</div>
									<div class="col-md-6" style="margin-left:-15px;">
										<div class="form-group">
											<label>bg</label>
											<div class="input-group ">
												<span class="input-group-addon">
													<i class="fa fa-bookmark"></i>
												</span>
												<input type="text" class="form-control" value="{!! $data['bg'] !!}" disabled="disabled" >
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>bu</label>
											<div class="input-group ">
												<span class="input-group-addon">
													<i class="fa fa-bookmark"></i>
												</span>
												<input type="text" class="form-control" value="{!! $data['bu'] !!}" disabled="disabled" >
											</div>
										</div>
									</div>


								</div>

								<div class="col-sm-12">
									<div class="form-group mt-repeater frank">
										<div data-repeater-list="group-data" id="replacement-product-list">
											<div data-repeater-item class="mt-repeater-item">
												<div class="row mt-repeater-row">
													<div class="col-lg-2 col-md-2">
														<label class="control-label">Asin</label>
														<input type="text" class="form-control asin" name="asin" placeholder="Asin" value="{!! $data['asin'] !!}" onchange="checkAsin($(this))" data-asin="{!! $data['asin'] !!}" {!! $showtype !!}/>
													</div>
													<div class="col-lg-2 col-md-2">
														<label class="control-label">Quantity</label>
														<input type="text" class="form-control quantity" name="quantity" placeholder="quantity" value="{!! $data['quantity'] !!}" {!! $showtype !!}/>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-4 col-md-8">
											@if($type==2)
												<button type="submit" class="btn blue">Submit</button>
											@endif
										</div>
									</div>
								</div>
							</form>

							<div class="col-sm-12">
								@if($data['attach_data'])
									<form id="upload-form" action="/transfer/request/uploadAttach" method="post" enctype="multipart/form-data"   style="width:500px;" >
										<input type="hidden" id="upload-id" name="id" value="{!! $data['id'] !!}">
										<div class="pull-left">
											{{ csrf_field() }}
											<input type="file" name="uploadFile[]" id="uploadFile" multiple="multiple" />
										</div>
										<div class=" pull-left">
											<button type="submit" class="upload-btn btn blue btn-sm" id="data_search">更新大货资料</button>
										</div>
									</form>
									<div class=" pull-left" style="margin-left:50px;">
										@foreach($data['files'] as $file)
											<a href="/transfer/request/downloadAttach?src={!! $file['src'] !!}" >{!! $file['showname'] !!}</a><br><br>
										@endforeach
									</div>
								@endif
							</div>
						</div>
					</div>


				</div>
			</div>
		</div>


	@include('frank.common')
	<script>
        //日期控件初始化
        $('#date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        function validate_form(){
            var site = $('#site').val();
            var account = $('#account').val();
            var request_reason = $('#request_reason').val();
            var rms_sku = $('#rms_sku').val();
            if(site == ''){
                alert("site cannot be empty.");
                return false;
            }
            if(account == ''){
                alert("account cannot be empty.");
                return false;
            }
            if(rms_sku == ''){
                alert("Rms Sku cannot be empty.");
                return false;
            }

            if(request_reason == ''){
                alert("Request Reason cannot be empty.");
                return false;
            }

            var flag = 0;
            $(".asin").each(function(ii,vv){ //ii 指第几个元素的序列号,vv 指遍历得到的元素
                var asin = $(this).val();
                if(asin == ''){
                    flag = 1;
                }
            });
            if(flag==1){
                alert("asin cannot be empty.");
                return false;
            }
            var flag_q = 0;
            $(".quantity").each(function(ii,vv){ //ii 指第几个元素的序列号,vv 指遍历得到的元素
                var quantity = $(this).val();
                if(quantity == ''){
                    flag_q = 1;
                }
            });
            if(flag_q==1){
                alert("quantity cannot be empty.");
                return false;
            }
        }
        let $replacementProductList = $('#replacement-product-list')
        let replacementItemRepeater = $replacementProductList.parent().repeater({repeaters: [{selector: '.inner-repeater'}],defaultValues:{qty:1}})
        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            $.ajax({
                type: 'post',
                url: '/showAccountBySite',
                data: {marketplaceid:marketplaceid},
                dataType:'json',
                success: function(res) {
                    if(res.status==1){
                        var html = '<option value="">Select</option>';
                        $.each(res.data,function(i,item) {
                            html += '<option value="'+item.id+'">'+item.label+'</option>';
                        })
                        $('#account').html(html);
                    }else{
                        alert('请先选择站点');
                    }
                }
            });
        }
        //初始化账号选项
        getAccountBySite();
        var account = $('#account').attr('account-value');
        setTimeout(function(){
			$('#account option').each(function(){
				if($(this).val()==account){
                    $(this).attr("selected","selected");
				}
			})
        },1000)

        //检测asin是否为有效asin，并且为自己所属的asin
        function checkAsin(obj){
            var asin = obj.val();
            var defaultasin = obj.attr('data-asin');
            var site = $('#site').val();
            $.ajax({
                type: 'post',
                url: '/checkAsin',
                dataType:'json',
                data: {asin:asin,site:site},
                success: function(res) {
                    if(res.status==0){
                        alert(res.msg);
                        obj.val(defaultasin);
                    }
                }
            });
        }
	</script>
@endsection