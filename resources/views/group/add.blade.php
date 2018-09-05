@extends('layouts.layout')
@section('label', 'Setting Groups')
@section('content')
    <h1 class="page-title font-red-intense"> Groups
        <small>Configure Service Teams.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Groups Form</span>
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
                <form role="form" action="{{ url('group') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Group Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="group_name" id="group_name" value="{{old('group_name')}}" required />
                            </div>
                        </div>
                        <div class="form-group mt-repeater">
							<div data-repeater-list="group-users">
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">User</label>
											<select class="form-control" name="user_id" required>

                                    @foreach ($users as $user_id=>$user_name)
                                        <option value="{{$user_id}}">{{$user_name}}</option>
                                    @endforeach
                                </select>
								
								 </div>
										<div class="col-md-3">
											<label class="control-label">Time From</label>
											 <div class="input-group">
                                        <input type="text" class="form-control timepicker timepicker-24"  value="0:00"  name="time_from" placeholder="From" >
                                        <span class="input-group-btn">
                                                                <button class="btn default" type="button">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </button>
                                                            </span>
                                    </div> </div>
											
										<div class="col-md-3">
											<label class="control-label">Time To</label>
											<div class="input-group">
                                        <input type="text" value="23:59" class="form-control timepicker timepicker-24"  name="time_to" placeholder="To">
                                        <span class="input-group-btn">
                                                                <button class="btn default" type="button">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </button>
                                                            </span>
                                    </div></div>
									<div class="col-md-2">
											<label class="control-label">Role</label>
											<div class="input-group">
                                        <select class="form-control" name="leader" required>
										<option value="0">Staff
										<option value="1">Leader
										
										</select>
        
                                    </div></div>
										<div class="col-md-1">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add User</a>
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
        <div class="portlet light bordered" id="blockui_sample_1_portlet_body">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-bubble font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp sbold">How to use it?</span>
                </div>
            </div>
            <div class="portlet-body">
                Randomly assigned to team members<p></p>
You can set a member multiple non-consecutive time period<p></p>
No time period is set to 0:00 to 23:59
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
            $('.timepicker-24').timepicker({
                autoclose: true,
                minuteStep: 1,
                showSeconds: false,
                showMeridian: false
            });

        });
    </script>

@endsection
