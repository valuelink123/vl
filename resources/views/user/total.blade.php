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
                            <div class="input-group date date-picker margin-bottom-5" id="date_from"  data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From" value="{{$date_from}}">
                                <span class="input-group-btn">
                                    <button class="btn btn-sm default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" id="date_to"  data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To" value="{{$date_to}}">
                                <span class="input-group-btn">
                                    <button class="btn btn-sm default" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-addon">Data Type</span>
                                <select  style="width:100%;height:35px;" id="ExportType" name="ExportType">
                                    @permission('data-statistics-users')
                                        <option value ="Users">Users Report</option>
                                    @endpermission
                                    @permission('data-statistics-accounts')
                                    <option value ="Accounts">Accounts Report</option>
                                    @endpermission
                                    @permission('data-statistics-performance')
                                    <option value ="Performance">Performance Report</option>
                                    @endpermission
                                    @permission('data-statistics-reply')
                                    <option value ="Reply">Reply Report</option>
                                    @endpermission
                                    @permission('data-statistics-review')
                                    <option value ="Review">Review Report</option>
                                    @endpermission
                                    @permission('data-statistics-fees')
                                    <option value ="Fees">Fees Report</option>
                                    @endpermission
                                    @permission('data-statistics-removal')
                                    <option value ="Removal">Removal Report</option>
                                    @endpermission
                                    @permission('data-statistics-return')
                                    <option value ="Return">Return Report</option>
                                    @endpermission
                                    @permission('data-statistics-reimbursements')
                                    <option value ="Reimbursements">Reimbursements Report</option>
                                    @endpermission
                                    @permission('data-statistics-estimatedSales')
                                    <option value ="EstimatedSales">Estimated Sales Report</option>
                                    @endpermission
                                    @permission('data-statistics-dailySales')
                                    <option value ="DailySales">Daily Sales Report</option>
                                    @endpermission
                                    @permission('data-statistics-inventoryDaily')
                                    <option value ="InventoryDaily">Inventory Daily Report</option>
                                    @endpermission
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                            <button type="submit" class="btn blue">Export</button>
                            </div>
                        </div>
                        <div class="col-md-8">
                        <div class="form-actions">
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
        $('#ExportType').change(function(){
            var value = $('#ExportType').val();
            if(value=='InventoryDaily'){
                $('#date_from').hide();
            }else{
                $('#date_from').show();
            }
        })

</script>


@endsection
