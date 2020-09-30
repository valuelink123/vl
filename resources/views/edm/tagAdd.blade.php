@extends('layouts.layout')
@section('label', 'Create A New Edm Tag')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/edm/tag/add" id="form" novalidate method="POST" onsubmit="return validate_form()">
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
							<span class="caption-subject bold font-green">Create A New Edm tag</span>
						</div>
					</div>
					<div class="portlet-body">
						<div class="tabbable-line">
							<div class="">
								<div class="col-lg-8">
									<div class="form-group">
										<label>Name</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="name" id="name" value="" required >
										</div>
									</div>
									<div class="form-group">
										<label>Describe</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="describe" id="describe" value=""  >
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
            var name = $('#name').val().trim();
            var describe = $('#describe').val().trim();
            if(name == ''){
                alert("name cannot be empty.");
                return false;
            }
            if(describe == ''){
                alert("describe cannot be empty.");
                return false;
            }
        }
	</script>
@endsection