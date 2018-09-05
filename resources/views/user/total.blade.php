@extends('layouts.layout')
@section('label', 'Data Statistics')
@section('content')
    <h1 class="page-title font-red-intense"> Data Statistics
        <small>Data Statistics</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('total')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From" value="{{$date_from}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To" value="{{$date_to}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-8">
                        <div class="form-actions">
                            <div class="row">
                                
                                    <button type="submit" class="btn blue" name ="ExportType" value ="Users">Export Users Report</button>
									<button type="submit" class="btn blue" name ="ExportType" value ="Accounts">Export Accounts Report</button>
									<button type="submit" class="btn blue" name ="ExportType" value ="Performance">Export Performance Report</button>
									<button type="submit" class="btn blue" name ="ExportType" value ="Reply">Export Reply Report</button>
									<button type="submit" class="btn blue" name ="ExportType" value ="Review">Export Review Report</button>
                                
                            </div>
                        </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




    




    <script>
        

        jQuery(document).ready(function() {
            $('.date-picker').datepicker({
                    rtl: App.isRTL(),
                    autoclose: true
                });
        });


</script>


@endsection
