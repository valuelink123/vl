@extends('layouts.layout')
@section('label', 'Config Options')
@section('content')
<h1 class="page-title font-red-intense"> Config Option
	<small>Config Option</small>
</h1>
<div class="row"><div class="col-md-8">
		<div class="portlet light bordered">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-settings font-dark"></i>
					<span class="caption-subject font-dark sbold uppercase">Config Option</span>
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
				<form role="form" action="{{ url('config_option/'.$config_option['id'].'/update') }}" method="POST">
					{{ csrf_field() }}
					<div class="form-body">
						{{--<div class="form-group">--}}
						{{--<input type="hidden" name="co_id" id="co_id" value="{{$config_option['id']}}" />--}}
						{{--</div>--}}
						<div class="form-group">
							<label>Parent Name</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-tag"></i>
							</span>
								<input type="hidden" name="co_pid" id="co_pid" value="{{$config_option['co_pid']}}" />
								<input type="text" class="form-control" value="{{$id_name_pairs[$config_option['co_pid']]}}" disabled />
							</div>
						</div>
						<div class="form-group">
							<label>Name</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-tag"></i>
							</span>
								<input type="text" class="form-control" name="co_name" id="co_name" value="{{$config_option['co_name']}}" required />
							</div>
						</div>
						<div class="form-group">
							<label>Order</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-tag"></i>
							</span>
								<input type="number" class="form-control" name="co_order" id="co_order" value="{{$config_option['co_order']}}" required />
							</div>
						</div>
						<div class="form-group">
							<label>Status</label>
							<div class="input-group ">
								<span class="input-group-addon">
									<i class="fa fa-bookmark"></i>
								</span>
								<select class="form-control" name="co_status" id="co_status">
									@foreach (getConfigOptionStatus() as $key => $value)
										<option value="{{$key}}" @if($key==$config_option['co_status']) selected @endif>
											{{$value}}
										</option>
									@endforeach
								</select>
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
</div>

@endsection
