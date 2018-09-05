@extends('layouts.layout')
@section('label', 'Setting Templates')
@section('content')
    <h1 class="page-title font-red-intense"> Templates
        <small></small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Templates Form</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form role="form" action="{{ url('template/'.$template['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="form-body">
                         <div class="form-group">
                            <label>Tags</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Tags (Important: Multiple keywords separated by semicolons.)" name="tag" id="tag" value="{{$template['tag']}}" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="title" id="title" value="{{$template['title']}}" required>
                            </div>
                        </div>
                        <div class="form-group" >
                                    @include('UEditor::head')

                                    <!-- 加载编辑器的容器 -->
                                    <script id="valuelink_amzmessage_container" name="content" type="text/plain">					<?php echo $template['content']; ?></script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue = UE.getEditor('valuelink_amzmessage_container');
                                        ue.ready(function() {
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                        });
                                </script>
                                        <div style="clear:both;"></div>
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
                Available labels
                <p><p>
                    {EMAIL_TITLE}
                <p>
                    {CUSTOMER_NAME}
                <p>
                    {CUSTOMER_EMAIL}
            </div>
        </div>

    </div>

    </div>


@endsection
