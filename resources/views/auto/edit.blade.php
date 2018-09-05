@extends('layouts.layout')
@section('label', 'Setting Auto Reply')
@section('content')
    <h1 class="page-title font-red-intense"> Auto Reply
        <small>Set the rules for the system to automatically reply to the specified mail.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Auto Reply Form</span>
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
                <form role="form" action="{{ url('auto/'.$rule['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Rule Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="rule_name" id="rule_name" value="{{$rule['rule_name']}}" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <div class="input-group col-md-3">
                                <span class="input-group-addon">
                                    <i class="fa fa-sort-amount-asc"></i>
                                </span>
                                <input type="text" class="form-control" name="priority" id="priority" value="{{$rule['priority']}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-heart"></i>
                                </span>
                                <input type="text" class="form-control" name="subject" id="subject" value="{{$rule['subject']}}">
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
                                        <option value="{{$account_email}}" <?php if(in_array($account_email,explode(';',$rule['to_email']))) echo 'selected';?>>{{$account_email}}</option>
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
                                <input type="text" class="form-control" name="from_email" id="from_email" value="{{$rule['from_email']}}" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Users</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <select name="user[]" id="user[]" class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
                                    @foreach ($users as $user_id=>$user_name)
                                        <option value="{{$user_id}}" <?php if(in_array($user_id,explode(';',$rule['users']))) echo 'selected';?>>{{$user_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="form-group">
                            <label>Date</label>
                            <div class="input-group ">
                                <div class="col-md-3">
                                    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm"  name="date_from" placeholder="From" value="{{$rule['date_from']}}">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm"  name="date_to" placeholder="To" value="{{$rule['date_to']}}">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Time</label>
                            <div class="input-group ">

                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control timepicker timepicker-24"  name="time_from" placeholder="From" value="{{$rule['time_from']}}">
                                        <span class="input-group-btn">
                                                                <button class="btn default" type="button">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </button>
                                                            </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control timepicker timepicker-24"  name="time_to" placeholder="To" value="{{$rule['time_to']}}">
                                        <span class="input-group-btn">
                                                                <button class="btn default" type="button">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </button>
                                                            </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin:50px 0;">
                            <label>Weeks</label>
                            <div class="input-group ">
                                <?php
                                $weeks = array('0'=>'Sunday','1'=>'Monday','2'=>'Tuesday','3'=>'Wednesday','4'=>'Thursday','5'=>'Friday','6'=>'Saturday');
                                foreach($weeks as $k=>$v){
                                    $checked='';
                                    if($rule['weeks'] && in_array($k,explode(';',$rule['weeks']))) $checked = 'checked';
                                    echo '<div class="col-md-3"><input type="checkbox" name="weeks[]" value="'.$k.'" '.$checked.' >'.$v.' </div>';
                                }
                                ?>

                            </div>
                        </div>




                        <div class="form-group" >
                            <label>Template</label>

                        @include('UEditor::head')

                        <!-- 加载编辑器的容器 -->
                            <script id="container" name="content" type="text/plain"></script>
                            <!-- 实例化编辑器 -->
                            <script type="text/javascript">
                                var ue = UE.getEditor('container');
                                ue.ready(function() {
                                    ue.execCommand('inserthtml', '<?php echo $rule['content'];?>');
                                    ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                });
                            </script>
                            <div style="clear:both;"></div>
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
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false
            });

        });
    </script>

@endsection
