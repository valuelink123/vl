@extends('layouts.layout')
@section('label', 'Campaigns')
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
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting" data-toggle="tab" aria-expanded="true" >Setting</a>
                    </li>
                    <li >
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/adgroup">Ad Groups</a>
                    </li>
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
                                <label>Serving Status:</label>
                                {{array_get($campaign,'servingStatus')}}
                            </div>

                            <div class="form-group">
                                <label>Type:</label>
                                Sponsored Display
                            </div>
                            
                            <div class="form-group">
                                <label>Status:</label>
                                <select class="form-control input-inline" name="state" id="state">
                                @foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
                                <option value="{{$k}}" {{($k==array_get($campaign,'state'))?'selected':''}} >{{$v}}</option>
                                @endforeach 
                                </select>
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

                            <div class="form-group">
                                <label>Daily Budget:</label>
                                <input type="text" class="form-control input-inline" name="budget" id="budget" value="{{array_get($campaign,'budget')}}" >  
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
				if(data.code=='207'){
                    toastr.success('Update Success');
				}else{
					toastr.error('Update Failed');
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

