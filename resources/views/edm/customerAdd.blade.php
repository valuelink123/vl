@extends('layouts.layout')
@section('label', 'Create A New Edm Customer')
@section('content')
	<form  action="/edm/customers/add" id="form" novalidate method="POST" onsubmit="return validate_form()">
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
							<span class="caption-subject bold font-green">Create A New Edm Customer</span>
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
											<input type="text" class="form-control" name="email" id="email" value="" required >
										</div>
									</div>
									<div class="form-group">
										<label>First Name</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="first_name" id="first_name" value=""  >
										</div>
									</div>
									<div class="form-group">
										<label>Last Name</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="last_name" id="last_name" value="" >
										</div>
									</div>
									<div class="form-group">
										<label>Address</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="address" id="address" value="">
										</div>
									</div>
									<div class="form-group">
										<label>Phone</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="phone" id="phone" value="">
										</div>
									</div>

									<div class="form-group">
										<label>Tag</label>
										<div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
											<select class="mt-multiselect btn btn-default" id="tag_id" multiple="multiple" data-width="100%" data-action-onchange="true" name="tag_id[]" id="tag_id[]">
												@foreach ($tag as $key => $value)
													<option value="{{$key}}" >{{$value}}</option>
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
        function validate_form(){
            var email = $('#email').val().trim();
            if(email == ''){
                alert("email cannot be empty.");
				return false;
            }else{
                if (/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(email) == false) {
                    alert("The email format is not correct, please fill in again.");
                    return false;
                }
			}
        }
	</script>
@endsection