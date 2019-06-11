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
								
                               <select  name="sellerid[]" id="sellerid[]" class="mt-multiselect btn btn-default" multiple="multiple" data-clickable-groups="true" data-collapse-groups="true" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" required>

									@foreach($accounts as $k=>$v)
				
										<option value="{{$k}}">{{$v}}</option>
				
									@endforeach
								</select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Report Type</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-heart"></i>
                                </span>
                                <select class="form-control" name="type" id="type" required>
								<option value="">Please Select...</option>
								<option value="_GET_AFN_INVENTORY_DATA_">_GET_AFN_INVENTORY_DATA_</option>
				
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


@endsection
