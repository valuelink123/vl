<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-body">
                    <form id="update_form"  name="update_form" >
                        {{ csrf_field() }}
                        <div class="form-body">
                            <input type="hidden" name="profile_id" value="{{$profile_id}}">
                            <input type="hidden" name="ad_type" value="{{$ad_type}}">
                            <input type="hidden" name="campaign_id" value="{{array_get($campaign,'campaignId')}}">


                            

                            <div class="form-group">
                                <label>Seller Account:</label>
                                <select class="form-control" name="to_profile_id" id="to_profile_id" >
                                    @foreach ($profiles as $k=>$v)
                                        <option value="{{$v->profile_id}}" {{($v->profile_id==$profile_id)?'selected':''}} >{{$v->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Campaign Name:</label>
                                <input type="text" class="form-control" name="to_name" id="to_name" value="{{array_get($campaign,'name')}} copy" >
                            </div>

                            <div class="form-group date date-picker" data-date-format="yyyy-mm-dd" >
                                <label>Date Range:</label>

                                <div class="input-group date date-picker col-md-4" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control " readonly name="startDate" id="startDate" value="{{date('Y-m-d',strtotime(array_get($campaign,'startDate')))}}">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                                <div class="input-group date date-picker col-md-4" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" readonly name="endDate" id="endDate" value="{{array_get($campaign,'endDate')?date('Y-m-d',strtotime(array_get($campaign,'endDate'))):''}}">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>

                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-actions col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                <button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                    <input type="submit" name="update" value="Copy" class="btn blue pull-right" >
                                    
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

    $('#update_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/adv/copyCampaign') }}",
			data: $('#update_form').serialize(),
			success: function (data) {
				if(data.code=='SUCCESS'){
                    toastr.success(data.description);
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

