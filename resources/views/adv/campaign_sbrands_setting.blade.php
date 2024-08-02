@extends('layouts.layout')
@section('label')
<a href="/adv">Advertising</a>  - Campaigns <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting">{{array_get($campaign,'name')}}</a>
@endsection
@section('content')
<h1 class="page-title font-red-intense"> Campaign - {{array_get($campaign,'name')}}
</h1>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="tabbable-line">
                <ul class="nav nav-tabs ">
                    <li class="active">
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting"> Setting</a>
                    </li>
                    
                    <li >
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/adgroup" >Ad Groups</a>
                    </li>
                    <!--
                    <li >
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/targetkeyword" >Targeting keywords</a>
                    </li>
                    <li >
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/negkeyword" >Negative keywords</a>
                    </li>

                    <li>
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/targetproduct" >Targeting products</a>
                    </li>

                    <li>
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/negproduct" >Negative products</a>
                    </li>
                    -->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_setting">
                    <form id="update_form"  name="update_form" >
                        {{ csrf_field() }}
                        <div class="form-body col-md-6">
                            <input type="hidden" name="profile_id" value="{{$profile_id}}">
                            <input type="hidden" name="ad_type" value="{{$ad_type}}">
                            <input type="hidden" name="campaign_id" value="{{array_get($campaign,'campaignId')}}">


                            <div class="form-group">
                                <label>Campaign Name:</label>
                                <input type="text" class="form-control input-inline" name="name" id="name" value="{{array_get($campaign,'name')}}" >
                            </div>

                            <div class="form-group">
                                <label>Campaign ID:</label>
                                {{array_get($campaign,'campaignId')}}
                            </div>

                            <div class="form-group">
                                <label>Type:</label>
                                Sponsored Brands
                            </div>

                            <div class="form-group">
                                <label>Status:</label>
                                <select class="form-control input-inline" name="state" id="state">
                                @foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
                                <option value="{{strtoupper($k)}}" {{($k==strtolower(array_get($campaign,'state')))?'selected':''}} >{{$v}}</option>
                                @endforeach 
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Budget:</label>
                                <input type="text" class="form-control input-inline" name="budget" id="budget" value="{{array_get($campaign,'budget')}}" >  {{array_get($campaign,'budgetType')}}
                            </div>

                            
                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-actions col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="submit" name="update" value="Update" class="btn blue pull-right" >
                                    
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
    </div>
</div>

<script>

$(function() {
    $('.date-picker').datepicker({
        rtl: App.isRTL(),
        autoclose: true
    });

    $('#bidOptimization').on('change',function(){
        if($(this).val()==1){
            $('#bidMultiplier').attr('disabled',true);
            $('#bidMultiplier').val('');
        }else{
            $('#bidMultiplier').attr('disabled',false);
        }
    });

    $('#update_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/adv/updateCampaign') }}",
			data: $('#update_form').serialize(),
			success: function (data) {
				if(data.code=='SUCCESS'){
                    toastr.success(data.code);
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
@endsection

