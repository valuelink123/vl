@extends('layouts.layout')
@section('label', 'Sales Alert Create')
@section('content')
    <h1 class="page-title font-red-intense"> Sales Alert
        <small></small>
    </h1>
    <div class="row">
        <div class="col-md-8">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject font-dark sbold uppercase">Create Sales Alert</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    <form role="form" action="{{ url('salesAlert/'.$data['id']) }}" method="POST">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-body">
                            <div class="form-group">
                                <label>部门</label>
                                <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                    <input type="text" name="department" class="form-control" value="{{$data['department']}}" required />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>年</label>
                                        <div class="input-group ">
                                        <span class="input-group-addon">
                                            <i class="fa fa-tag"></i>
                                        </span>
                                            <input  class="form-control"  value="{{old('year')}}" id="year" name="year" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label>月</label>
                                        <div class="input-group ">
                                        <span class="input-group-addon">
                                            <i class="fa fa-tag"></i>
                                        </span>
                                            <input  class="form-control" value="{{old('month')}}" id="month" name="month" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label>开始时间</label>
                                        <div class="input-group ">
                                        <span class="input-group-addon">
                                            <i class="fa fa-tag"></i>
                                        </span>
                                            <input  class="form-control"  value="{{old('start_time')}}" id="start_time" name="start_time" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label>结束时间</label>
                                        <div class="input-group ">
                                        <span class="input-group-addon">
                                            <i class="fa fa-tag"></i>
                                        </span>
                                            <input  class="form-control"  value="{{old('end_time')}}" id="end_time" name="end_time" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>销售额</label>
                                        <div class="input-group ">
                                        <span class="input-group-addon">
                                            <i class="fa fa-tag"></i>
                                        </span>
                                            <input  class="form-control"  value="{{old('sales')}}" id="sales" name="sales" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label>营销费用</label>
                                        <div class="input-group ">
                                        <span class="input-group-addon">
                                            <i class="fa fa-tag"></i>
                                        </span>
                                            <input  class="form-control" value="{{old('marketing_expenses')}}" id="marketing_expenses" name="marketing_expenses" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="button"  class="btn grey-salsa btn-outline"  data-dismiss="modal" aria-hidden="true">Close</button>
                                    <button type="submit" class="btn blue">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>
    <script>
        $('#year').datepicker({
            format: 'yyyy',
            //            language: "zh-CN",
            autoclose:true,
            startView: 2,
            minViewMode: 2,
            maxViewMode: 2
        });

        $('#month').datepicker({
            format: 'mm',
//            language: "zh-CN",
            autoclose:true,
            startView: 1,
            minViewMode: 1,
            maxViewMode: 1
        });
        $('#start_time').datepicker({
            format: 'yyyy-mm-dd',
//            language: "zh-CN",
            autoclose:true,
            startView: 0,
            minViewMode: 0,
            maxViewMode: 0
        });
        $('#end_time').datepicker({
            format: 'yyyy-mm-dd',
//            language: "zh-CN",
            autoclose:true,
            startView: 0,
            minViewMode: 0,
            maxViewMode: 0
        });
    </script>
@endsection