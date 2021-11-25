@extends('layouts.layout')
@section('label', 'Create A New Seller Accounts Status Record')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/sellerAccountsStatus/add" id="form" novalidate method="POST" onsubmit="return validate_form()">
		{{ csrf_field() }}
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
							<span class="caption-subject bold font-green">Create A Seller Accounts Status Record</span>
						</div>
					</div>
					<div class="portlet-body">
						<div class="tabbable-line">
							<div class="">
								<div class="col-lg-8">
									<div class="form-group">
										<label>Site</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select  style="width:100%;height:35px;"  id="site" onchange="getAccountBySite()" name="mws_marketplaceid">
												@foreach($site as $value)
													<option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group" id="account-div">
										<label>Account</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select style="width:100%;height:35px;" class="btn btn-default" id="account" name="mws_seller_id">
											</select>
										</div>
									</div>
									<input type="hidden" name="label" id="seller_name" value="">

									<div class="form-group">
										<label>Account Status</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select  style="width:100%;height:35px;"  id="account_status"  name="account_status">
												@foreach($account_status as $value)
													<option value="{{ $value }}">{{ $value }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group">
										<label>Record Status</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select  style="width:100%;height:35px;"  id="record_status" name="record_status">
												@foreach($record_status as $value)
													<option value="{{ $value }}">{{ $value }}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="form-group">
										<label>Remark</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="remark" id="remark" value="">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-4 col-md-8">
								<button type="submit" class="btn blue">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	@include('frank.common')
	<script>
        function validate_form(){
			$('#seller_name').val($('#account').find("option:selected").text());
        }
		function getAccountBySite(){
			var marketplaceid = $('#site option:selected').val();
			$.ajax({
				type: 'post',
				url: '/showAccountBySite',
				data: {marketplaceid:marketplaceid,field:'mws_seller_id'},
				dataType:'json',
				success: function(res) {
					if(res.status==1){
						var html = '';
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
		$(function(){
			getAccountBySite()//触发当前选的站点得到该站点所有的账号
		})
	</script>
@endsection