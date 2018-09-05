@extends('layouts.layout')
@section('label', 'Setting Rules')
@section('content')
    <h1 class="page-title font-red-intense"> Rules
        <small>Configure filtering rules to distribute information to different users.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Rules Form</span>
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
                <form role="form" action="{{ url('rule') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Rule Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="rule_name" id="rule_name" value="{{old('rule_name')}}" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <div class="input-group col-md-3">
                                <span class="input-group-addon">
                                    <i class="fa fa-sort-amount-asc"></i>
                                </span>
                                <input type="text" class="form-control" name="priority" id="priority" value="{{old('priority')}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-heart"></i>
                                </span>
                                <input type="text" class="form-control" name="subject" id="subject" value="{{old('subject')}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Send To</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <select name="to_email[]" id="to_email[]" class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
                                    @foreach ($accounts as $account_id=>$account_email)
                                        <option value="{{$account_email}}" <?php if(in_array($account_email,old('to_email')?old('to_email'):array())) echo 'selected';?>>{{$account_email}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email From</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" name="from_email" id="from_email" value="{{old('from_email')}}" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>ASIN</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" name="asin" id="asin" value="{{old('asin')}}" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Product Group</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" name="sku" id="sku" value="{{old('sku')}}" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Timed out Warning</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-clock-o"></i>
                                </span>
                                <input type="text" class="form-control" name="timeout" id="timeout" value="{{old('timeout')}}" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Set Email Status To</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control" name="reply_status" id="reply_status">
                                    <option value="0" <?php if(0==old('reply_status')) echo 'selected';?>>Need reply</option>
                                    <option value="1" <?php if(1==old('reply_status')) echo 'selected';?>>Do not need to reply</option>
                                    <option value="99" <?php if(99==old('reply_status')) echo 'selected';?>>Trash</option>
                                </select>
                            </div>
                        </div>
						<div class="form-group">
                            <label>Group</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-group"></i>
                                </span>
                                <select class="form-control" name="group_id" id="group_id" required>

                                    @foreach ($groups as $group_id=>$group_name)
                                        <option value="{{$group_id}}" <?php if($group_id==old('group_id')) echo 'selected';?>>{{$group_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                





                            <!--
                            <label>Amazon AWS Access KeyId</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)">
                            </div>

                            <label>Amazon Secret Key</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)">
                            </div>
                            -->

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
