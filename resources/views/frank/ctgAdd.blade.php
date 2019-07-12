@extends('layouts.layout')
@section('label', 'Crm Add')
@section('content')
    <form  action="{{ url('ctg/store') }}" id="exception_form" novalidate method="POST">
        {{ csrf_field() }}
        <div class="col-lg-9">
            <div class="col-md-12">
                <div class="portlet light portlet-fit bordered ">
                    {{--@if($errors->any())--}}
                        {{--<div class="alert alert-danger">--}}
                            {{--@foreach($errors->all() as $error)--}}
                                {{--<div>{{ $error }}</div>--}}
                            {{--@endforeach--}}
                        {{--</div>--}}
                    {{--@endif--}}
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-microphone font-green"></i>
                            <span class="caption-subject bold font-green">CTG Add</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="tabbable-line">
                            <div class="">

                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
                                                <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" required >
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Order ID</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" name="order_id" id="order_id" value="{{old('order_id')}}" required pattern="\d{3}-\d{7}-\d{7}">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Note</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" name="note" id="note" value="{{old('note')}}" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Channel</label>
                                            <div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <select class="form-control" name="channel" id="channel">
                                                    @foreach ($channel as $key=>$value)
                                                        <option value="{{$key}}" @if(old('channel')==$key) selected @endif>{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div style="clear:both;"></div>
                                    </div>
                                    <div style="clear:both;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-4 col-md-8">
                            <button type="submit" class="btn blue">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @include('frank.common')
    <script>
        $(exception_form).submit(function (e) {

            let type = $('#type').val()

            for (let input of $(this).find('[name]')) {

                let tabID = $(input).closest('.tab-pane').attr('id')

                if (tabID) {
                    if (!(type & tabID.substr(-1))) continue
                }
                if (!input.reportValidity()) {
                    toastr.error('The form is not complete yet.')
                    return false
                }
            }
        });
    </script>
    <div style="clear:both;"></div>
@endsection