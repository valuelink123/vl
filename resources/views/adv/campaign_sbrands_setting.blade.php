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
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/setting"> Setting</a>
                    </li>
                    <!--
                    <li >
                        <a href="/adv/campaign/{{$profile_id}}/{{$ad_type}}/{{array_get($campaign,'campaignId')}}/creative" >Creative</a>
                    </li>
                    -->
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
                                Sponsored Brands
                            </div>

                            <div class="form-group">
                                <label>Ad Format:</label>
                                {{array_get($campaign,'adFormat')}}
                            </div>

                            <div class="form-group">
                                <label>Asins:</label>
                                {{implode(', ',array_get($campaign,'creative.asins'))}}
                            </div>

                            <div class="form-group">
                                <label>Landing Page:</label>
                                {{array_get($campaign,'landingPage.pageType'). ' - '.array_get($campaign,'landingPage.url')}}
                            </div>
                            @if(array_get($campaign,'adFormat')=='video')
                            <div class="form-group">
                                <label>Video Media Ids:</label>
                                {{implode(', ',array_get($campaign,'creative.videoMediaIds'))}}
                            </div>
                            @else
                            <div class="form-group">
                                <label>Brand Name:</label>
                                {{array_get($campaign,'creative.brandName')}}
                            </div>
                            
                            <div class="form-group">
                                <label>Brand Logo Url:</label>
                                {{array_get($campaign,'creative.brandLogoUrl')}}
                            </div>

                            <div class="form-group">
                                <label>Head Line:</label>
                                {{array_get($campaign,'creative.headline')}}
                            </div>
                            @endif
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
                                <label>Budget:</label>
                                <input type="text" class="form-control input-inline" name="budget" id="budget" value="{{array_get($campaign,'budget')}}" >  {{array_get($campaign,'budgetType')}}
                            </div>
                            @if(array_get($campaign,'adFormat')=='productCollection')
                            <div class="form-group">
                                <label>Bid Optimization:</label>
                                <select class="form-control input-inline" name="bidOptimization" id="bidOptimization">
                                @foreach (['1'=>'Enabled','0'=>'Disabled'] as $k=>$v)
                                <option value="{{$k}}" {{($k==array_get($campaign,'bidOptimization'))?'selected':''}} >{{$v}}</option>
                                @endforeach 
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Bid Multiplier:</label>
                                <input class="form-control input-inline" {{array_get($campaign,'bidOptimization')?'disabled':''}}  name="bidMultiplier" id="bidMultiplier" value="{{array_get($campaign,'bidMultiplier')}}" >% (-99 to 99)
                            </div>
                            @endif
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

