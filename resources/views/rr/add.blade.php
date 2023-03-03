@extends('layouts.layout')
@section('label', 'Setting Report')
@section('content')
    <h1 class="page-title font-red-intense"> Report
        <small>Configure filtering rules to distribute information to different users.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Report Form</span>
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
                <form role="form" action="{{ url('rr') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Account</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
								
                               <select  name="seller_account_ids[]" id="seller_account_ids[]" class="mt-multiselect btn btn-default" multiple="multiple" data-clickable-groups="true" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" required>
									<optgroup label="Select All US" >
									@foreach($accounts as $k=>$v)
										@if ($v['area']=='US')
										<option value="{{$k}}">{{$v['name']}} -- {{$v['area']}}</option>
										@endif
									@endforeach
									</optgroup>
									<optgroup label="Select All EU" >
									@foreach($accounts as $k=>$v)
										@if ($v['area']=='EU')
										<option value="{{$k}}">{{$v['name']}} -- {{$v['area']}}</option>
										@endif
									@endforeach
									</optgroup>
									<optgroup label="Select All JP" >
									@foreach($accounts as $k=>$v)
										@if ($v['area']=='JP')
										<option value="{{$k}}">{{$v['name']}} -- {{$v['area']}}</option>
										@endif
									@endforeach
									</optgroup>
								</select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Report Type</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-heart"></i>
                                </span>
                                <select class="form-control" name="report_type" id="report_type" required>
								<option value="">Please Select...</option>
								<option value="GET_FBA_FULFILLMENT_CUSTOMER_RETURNS_DATA">GET_FBA_FULFILLMENT_CUSTOMER_RETURNS_DATA</option>
<option value="GET_FBA_FULFILLMENT_CUSTOMER_SHIPMENT_REPLACEMENT_DATA">GET_FBA_FULFILLMENT_CUSTOMER_SHIPMENT_REPLACEMENT_DATA</option>
<option value="GET_FBA_FULFILLMENT_REMOVAL_ORDER_DETAIL_DATA">GET_FBA_FULFILLMENT_REMOVAL_ORDER_DETAIL_DATA</option>
<option value="GET_FBA_FULFILLMENT_REMOVAL_SHIPMENT_DETAIL_DATA">GET_FBA_FULFILLMENT_REMOVAL_SHIPMENT_DETAIL_DATA</option>
<option value="GET_FBA_MYI_ALL_INVENTORY_DATA">GET_FBA_MYI_ALL_INVENTORY_DATA</option>
<option value="GET_AFN_INVENTORY_DATA">GET_AFN_INVENTORY_DATA</option>
<option value="GET_AFN_INVENTORY_DATA_BY_COUNTRY">GET_AFN_INVENTORY_DATA_BY_COUNTRY</option>
<option value="GET_LEDGER_SUMMARY_VIEW_DATA">GET_LEDGER_SUMMARY_VIEW_DATA</option>
<option value="GET_FBA_REIMBURSEMENTS_DATA">GET_FBA_REIMBURSEMENTS_DATA</option>
<option value="GET_LEDGER_DETAIL_VIEW_DATA">GET_LEDGER_DETAIL_VIEW_DATA</option>
<option value="GET_RESERVED_INVENTORY_DATA">GET_RESERVED_INVENTORY_DATA</option>
								</select>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Report Date</label>
                            <div class="input-group ">
                                
                                <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control input-sm" readonly name="after_date" placeholder="From" value="{{date('Y-m-d',strtotime('-1day'))}}">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="before_date" placeholder="To" value="{{date('Y-m-d')}}">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                            </div>
                        </div>
						<div class="form-group mt-repeater">
							<label>Report Option</label>
							<div data-repeater-list="report_option">
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-4">
											<label class="control-label">Option</label>
											<div class="input-group">
                                       	 	<input type="text" class="form-control"  name="option">
            
                                    		</div>
										</div>		
										<div class="col-md-4">
											<label class="control-label">Value</label>
											<div class="input-group">
                                       		<input type="text" class="form-control"  name="value">
            
                                    		</div>
										</div>
				
										<div class="col-md-4">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add Option</a>
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
                <p></p>
                You can set Timed out Warning like  3day or 36hour or 90min; Leave blank or 0 means no limit
            </div>
        </div>

    </div>

    </div>

<script>
$('.date-picker').datepicker({
	rtl: App.isRTL(),
	autoclose: true
});
</script>
@endsection
