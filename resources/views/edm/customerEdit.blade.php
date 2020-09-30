@extends('layouts.layout')
@section('label', 'Update Edm Customer')
@section('content')
	<form  action="/edm/customers/update" id="form" novalidate method="POST">
		{{ csrf_field() }}
		<input type="hidden" class="form-control" name="id" value="{{$customerData['id']}}">
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
							<span class="caption-subject bold font-green">Update Edm Customer</span>
						</div>
					</div>
					<div class="portlet-body">
						<div class="tabbable-line">
							<div class="">
								<div class="col-lg-8">
									<div class="form-group">
										<label>Email</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="email" id="email" value="{{$customerData['email']}}" readonly="readonly" >
										</div>
									</div>
									<div class="form-group">
										<label>First Name</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="first_name" id="first_name" value="{{$customerData['first_name']}}"  readonly="readonly">
										</div>
									</div>
									<div class="form-group">
										<label>Last Name</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="last_name" id="last_name" value="{{$customerData['last_name']}}" readonly="readonly">
										</div>
									</div>
									<div class="form-group">
										<label>Address</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="address" id="address" value="{{$customerData['address']}}" readonly="readonly">
										</div>
									</div>
									<div class="form-group">
										<label>Phone</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="phone" id="phone" value="{{$customerData['phone']}}" readonly="readonly">
										</div>
									</div>

									<div class="form-group">
										<label>Group</label>
										<div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
											<select class="mt-multiselect btn btn-default" id="tag_id" multiple="multiple" data-width="100%" data-action-onchange="true" name="tag_id[]" id="tag_id[]">
													@foreach ($tag as $key=> $val)
														<option value="{{$key}}" {{ in_array($key,$customerData['tag_ids'])?'selected':''}}>{{$val}}</option>
													@endforeach

											</select>
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
	</form>
	@include('frank.common')
	<script>
	</script>
@endsection