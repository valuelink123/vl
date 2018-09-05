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
                <form role="form" action="{{ url('group/'.$group['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
					<input type="hidden" name="id" value="{{$group['id']}}" />
                    <div class="form-body">
                        <div class="form-group">
                            <label>Group Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="group_name" id="group_name" value="{{$group['group_name']}}" required />
                            </div>
                        </div>
                       <div class="form-group mt-repeater">
							<div data-repeater-list="group-users">
							<?php foreach($group_details as $detail) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">User</label>
											<select class="form-control" name="user_id" required>

                                    @foreach ($users as $user_id=>$user_name)
                                        <option value="{{$user_id}}" <?php if($user_id == array_get($detail,'user_id')) echo "selected"?>>{{$user_name}}</option>
                                    @endforeach
                                </select>
								
								 </div>
										<div class="col-md-3">
											<label class="control-label">Time From</label>
											 <div class="input-group">
                                        <input type="text" class="form-control timepicker timepicker-24"  name="time_from" placeholder="From" value="{{array_get($detail,'time_from')}}">
                                        <span class="input-group-btn">
                                                                <button class="btn default" type="button">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </button>
                                                            </span>
                                    </div> </div>
											
										<div class="col-md-3">
											<label class="control-label">Time To</label>
											<div class="input-group">
                                        <input type="text" class="form-control timepicker timepicker-24"  name="time_to" placeholder="To" value="{{array_get($detail,'time_to')}}">
                                        <span class="input-group-btn">
                                                                <button class="btn default" type="button">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </button>
                                                            </span>
                                    </div></div>
									
									<div class="col-md-2">
											<label class="control-label">Role
</label>
											<div class="input-group">
                                        <select class="form-control" name="leader" required>
										<option value="0">Staff
										<option value="1" <?php if(array_get($detail,'leader')) echo 'selected' ?>>Leader
										
										</select>
        
                                    </div></div>
									
										<div class="col-md-1">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
								<?php } ?>
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
                Please set a unique name for your rules to distinguish them.
                <p></p>
                The System will match the mail in order of priority.
                <p></p>
                You can set multiple keywords for Subject, Mail From , Asin ; Please use semicolons separated them
                <p></p>You can set Timed out Warning like  3day or 36hour or 90min; Leave blank or 0 means no limit
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
