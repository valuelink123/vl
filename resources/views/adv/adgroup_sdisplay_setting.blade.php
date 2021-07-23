@extends('layouts.layout')
@section('label', 'Ad Group')
@section('content')
<h1 class="page-title font-red-intense"> Ad Group - {{array_get($adgroup,'name')}}
</h1>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="tabbable-line">
                <ul class="nav nav-tabs ">
                    <li class="active">
                        <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/setting" data-toggle="tab" aria-expanded="true" > Setting</a>
                    </li>
                    <li >
                        <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/ad" >Ads</a>
                    </li>

                    <li>
                        <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/targetproduct" >Targeting</a>
                    </li>

                    <li >
                        <a href="/adv/adgroup/{{$profile_id}}/{{$ad_type}}/{{array_get($adgroup,'adGroupId')}}/negproduct" >Negative Targeting</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_setting">
                    <form id="update_form"  name="update_form" >
                        {{ csrf_field() }}
                        <div class="form-body col-md-6">
                            <input type="hidden" name="profile_id" value="{{$profile_id}}">
                            <input type="hidden" name="ad_type" value="{{$ad_type}}">
                            <input type="hidden" name="campaign_id" value="{{array_get($adgroup,'campaignId')}}">
                            <input type="hidden" name="adgroup_id" value="{{array_get($adgroup,'adGroupId')}}">


                            <div class="form-group">
                                <label>Ad Group Name:</label>
                                <input type="text" class="form-control input-inline" name="name" id="name" value="{{array_get($adgroup,'name')}}" >
                            </div>

                            <div class="form-group">
                                <label>Ad Group ID:</label>
                                {{array_get($adgroup,'adGroupId')}}
                            </div>

                            <div class="form-group">
                                <label>Serving Status:</label>
                                {{array_get($adgroup,'servingStatus')}}
                            </div>
                            
                            <div class="form-group">
                                <label>Status:</label>
                                <select class="form-control input-inline" name="state" id="state">
                                @foreach (\App\Models\PpcProfile::STATUS as $k=>$v)
                                <option value="{{$k}}" {{($k==array_get($adgroup,'state'))?'selected':''}} >{{$v}}</option>
                                @endforeach 
                                </select>
                            </div>


                            <div class="form-group">
                                <label>Bid Optimization:</label>
                                {{array_get(\App\Models\PpcProfile::BIDOPTIMIZATION,array_get($adgroup,'bidOptimization'))}}
                            </div>


                            <div class="form-group">
                                <label>Default Bid:</label>
                                <input type="text" class="form-control input-inline" name="defaultBid" id="defaultBid" value="{{array_get($adgroup,'defaultBid')}}" >  
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
			url: "{{ url('/adv/updateAdGroup') }}",
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

