@extends('layouts.layout')
@section('label', 'Add Activity')
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-microphone font-green"></i>
                        <span class="caption-subject bold font-green">Add Activity</span>
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
                    <form role="form" action="{{ url('/crm/trackLogStore') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="text" name="record_id" id="record_id" value="{{$record_id}}" hidden>
                        <input type="text" name="email" id="email" value="{{$email}}" hidden>
                        <div class="row form-group">
                            <div class="col-lg-6 col-md-6">
                                <label>Channel</label>
                                <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                    <select class="form-control" name="channel" id="channel">
                                        @foreach (getTrackLogChannel() as $key => $value)
                                            <option value="{{$key}}" >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <label>Subject Type</label>
                                <div class="input-group ">
                                <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                </span>
                                    <select class="form-control" name="subject_type" id="subject_type">
                                        @foreach ($subject_type as $key => $value)
                                            <option value="{{$key}}" >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </div>

                        {{--<div class="form-group">--}}
                            {{--<label>Email</label>--}}
                            {{--<div class="input-group ">--}}
                                {{--<span class="input-group-addon">--}}
                                    {{--<i class="fa fa-bookmark"></i>--}}
                                {{--</span>--}}
                                {{--<input type="email" class="form-control" name="email" id="email" value="" required>--}}
                            {{--</div>--}}
                        {{--</div>--}}

                        <div class="form-group">
                            <label>Note</label>
                            @include('UEditor::head')
                            <!-- 加载编辑器的容器 -->
                            <script id="container" name="note" type="text/plain" required></script>
                            <!-- 实例化编辑器 -->
                            <script type="text/javascript">
                                var ue = UE.getEditor('container');
                                ue.ready(function() {
                                    ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                });
                            </script>
                            <div style="clear:both;"></div>
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
    </div>

@endsection
