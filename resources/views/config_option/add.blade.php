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
				<form role="form" action="{{ url('config_option/store') }}" method="POST">
					{{ csrf_field() }}
					<div class="form-group">
						<label>Parent Name</label>
						<div class="input-group ">
							<span class="input-group-addon">
									<i class="fa fa-bookmark"></i>
								</span>
							<select class="form-control" name="co_pid" id="co_pid">
								@foreach ($id_name_pairs as $k => $v)
									@if($id_pid_pairs[$k] == '0')
									<option value="{{$k}}">{{$v}}</option>
									@endif
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-body">
						<div class="form-group">
							<label>Name</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-tag"></i>
							</span>
								<input type="text" class="form-control" name="co_name" id="co_name" value="" required />
							</div>
						</div>
						<div class="form-group">
							<label>Order</label>
							<div class="input-group ">
							<span class="input-group-addon">
								<i class="fa fa-tag"></i>
							</span>
								<input type="number" class="form-control" name="co_order" id="co_order" value="" required />
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
										<option value="{{$key}}" >
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
