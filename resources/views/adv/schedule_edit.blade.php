<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>Add a scheduled task</h1></div>
            <div class="portlet-body">
                    <form id="schedule_form"  name="schedule_form" >
                        {{ csrf_field() }}
                        <div class="form-body">
                            <input type="hidden" name="profile_id" value="{{array_get($form,'profile_id')}}">
                            <input type="hidden" name="ad_type" value="{{array_get($form,'ad_type')}}">
                            <input type="hidden" name="record_type" value="{{array_get($form,'record_type')}}">
                            <input type="hidden" name="record_type_id" value="{{array_get($form,'record_type_id')}}">
                            <input type="hidden" name="campaign_id" value="{{array_get($form,'campaign_id')}}">
                            <input type="hidden" name="adgroup_id" value="{{array_get($form,'adgroup_id')}}">
                            <input type="hidden" name="id" value="{{array_get($form,'id')}}">
                            @if(!empty($exists))
                            <table class="table table-bordered">
                                <tr>
                                    <td colspan=6>Already existed</td>
                                </tr>
                                <tr>
                                    <td>Start</td>
                                    <td>End</td>
                                    <td>Bid</td>
                                    <td>State</td>
                                    <td>Status</td>
                                    <td>User</td>
                                </tr>
                                @foreach($exists  as $exist)
                                <tr>
                                    <td>{{array_get($exist,'date_from')}}</td>
                                    <td>{{array_get($exist,'date_to')}}</td>
                                    <td>{{array_get($exist,'bid')}}</td>
                                    <td>{{array_get(\App\Models\PpcProfile::STATUS,array_get($exist,'state'))}}</td>
                                    <td>{{array_get(\App\Models\PpcSchedule::STATUS,array_get($exist,'status'))}}</td>
                                    <td>{{array_get($users,array_get($exist,'user_id'))}}</td>
                                </tr>
                                @endforeach
                            </table>
                            @endif
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" readonly class="form-control" name="record_name" id="record_name" value="{{array_get($form,'record_name')}}" >
                            </div>
                            

                            <div class="form-group">
                                <label>Status:</label>
                                <select class="form-control" name="status" id="status" >
                                @foreach (\App\Models\PpcSchedule::STATUS as $k=>$v)
                                <option value="{{$k}}" {{($k==array_get($form,'status'))?'selected':''}} >{{$v}}</option>
                                @endforeach 
                                </select>
                            </div>

                            <div class="form-group date date-picker" data-date-format="yyyy-mm-dd" >
                                <label>Date Range:</label>

                                <div class="input-group date date-picker col-md-4" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control " readonly name="date_from" id="date_from" value="{{array_get($form,'date_from')}}">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                                <div class="input-group date date-picker col-md-4" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" readonly name="date_to" id="date_to" value="{{array_get($form,'date_to')}}">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            @if(!empty(array_get($form,'id')))
                            <div class="form-group">
                                <label>Time:</label>
                                <div class="input-group col-md-4">
                                    <input type="text" class="form-control timepicker timepicker-24"  name="time" placeholder="time" value="{{array_get($form,'time')}}">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-clock-o"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>State:</label>
                                <select class="form-control " name="state" id="state">
                                @foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
                                <option value="{{$k}}" {{($k==array_get($form,'state'))?'selected':''}} >{{$v}}</option>
                                @endforeach 
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Bid:</label>
                                <input type="text" class="form-control" name="bid" id="bid" value="{{array_get($form,'bid')}}" >
                            </div>
							@else
							<div class="form-group mt-repeater">
								<div data-repeater-list="schedules">
									<div data-repeater-item class="mt-repeater-item">
										<div class="row mt-repeater-row">
											<div class="col-md-3">
												<label>Time:</label>
												<div class="input-group">
													<input type="text" class="form-control timepicker timepicker-24"  name="time" placeholder="time" value="00:00" required>
													<span class="input-group-btn">
														<button class="btn default" type="button">
															<i class="fa fa-clock-o"></i>
														</button>
													</span>
												</div>
											</div>
											<div class="col-md-3">
												<label>State:</label>
												<select class="form-control " name="state" id="state" required>
												@foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
												<option value="{{$k}}" {{($k==array_get($form,'state'))?'selected':''}} >{{$v}}</option>
												@endforeach 
												</select>
											</div>
											
											<div class="col-md-3">
												<label>Bid:</label>
                                				<input type="text" class="form-control" name="bid" id="bid" value="0" required>
											</div>
												
					
											<div class="col-md-3">
												<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
													<i class="fa fa-close"></i>
												</a>
											</div>
										</div>
									</div>
									
								</div>
								<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add Schedule</a>
							</div>
							@endif

                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-actions col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                <button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                    <input type="submit" name="update" value="Save" class="btn blue pull-right" >
                                    
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
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

    $('.timepicker-24').timepicker({
        autoclose: true,
        minuteStep: 1,
        showSeconds: false,
        showMeridian: false
    });
	
	FormRepeater.init();
	
    $('#schedule_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/adv/saveSchedule') }}",
			data: $('#schedule_form').serialize(),
			success: function (data) {
				if(data.code=='SUCCESS'){
                    $('#ajax').modal('hide');
					$('.modal-backdrop').remove();
                    toastr.success(data.description);
                    var dttable = $('#datatable_ajax').dataTable();
					dttable.api().ajax.reload(null, false);
				}else{
					toastr.error(data.description);
				}
			},
			error: function(data) {
                toastr.error(data.responseText);
			}
		});
		return false;
	});
});
</script>

