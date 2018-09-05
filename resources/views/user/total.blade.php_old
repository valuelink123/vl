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
                        <div class="col-md-2">
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="submit" class="btn blue">Search</button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    </form>
                </div>

                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">User List</span>
                    </div>
                </div>

                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover order-column" id="manage_user">
                        <thead>
                        <tr>

                            <th> Name </th>
                            <?php for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) { ?>
                            <th> {{date('md',$i)}}</th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($user_key as $user_id=>$user_value)
                            <tr class="odd gradeX">
                                <td>
                                    {{array_get($users,$user_id)}} Received
                                </td>
                                <?php for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) { ?>
                                <td> {{array_get($user_received_total,$user_id.'.'.date('Y-m-d',$i),0)}}</td>
                                <?php } ?>

                            </tr>
							
							<tr class="odd gradeX">
                                <td>
                                    {{array_get($users,$user_id)}} Send
                                </td>
                                <?php for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) { ?>
                                <td> {{array_get($user_send_total,$user_id.'.'.date('Y-m-d',$i),0)}}</td>
                                <?php } ?>

                            </tr>
                        @endforeach



                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Account List</span>
                    </div>
                </div>
                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover order-column" id="manage_account">
                        <thead>
                        <tr>
                            <th> Account Email </th>
							<th> Account Name </th>
                            <?php for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) { ?>
                            <th> {{date('md',$i)}}</th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($account_key as $account_mail=>$account_value)
                            <tr class="odd gradeX">
                                <td>
                                    {{$account_mail}} Received
                                </td>
								<th> {{array_get($accounts,strtolower($account_mail))}} </th>
                                <?php for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) { ?>
                                <td> {{array_get(array_get($account_received_total,$account_mail)?$account_received_total[$account_mail]:array(),date('Y-m-d',$i),0)}}</td>
                                <?php } ?>
                            </tr>
							
							
							 <tr class="odd gradeX">
                                <td>
                                    {{$account_mail}} Send
                                </td>
								<th> {{array_get($accounts,strtolower($account_mail))}} </th>
                                <?php for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) { ?>
                                <td>{{array_get(array_get($account_send_total,$account_mail)?$account_send_total[$account_mail]:array(),date('Y-m-d',$i),0)}}</td>
                                <?php } ?>
                            </tr>
                        @endforeach



                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




    <script>
        var TableDatatablesScroller = function () {
            var initPickers = function () {
                //init date pickers
                $('.date-picker').datepicker({
                    rtl: App.isRTL(),
                    autoclose: true
                });
            }
            var initTable1 = function () {
                var table = $('#manage_user');

                var oTable = table.dataTable({

                    // Internationalisation. For more info refer to http://datatables.net/manual/i18n
                    "language": {
                        "aria": {
                            "sortAscending": ": activate to sort column ascending",
                            "sortDescending": ": activate to sort column descending"
                        },
                        "emptyTable": "No data available in table",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "infoEmpty": "No entries found",
                        "infoFiltered": "(filtered1 from _MAX_ total entries)",
                        "lengthMenu": "_MENU_ entries",
                        "search": "Search:",
                        "zeroRecords": "No matching records found"
                    },

                    // Or you can use remote translation file
                    //"language": {
                    //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
                    //},

                    // setup buttons extension: http://datatables.net/extensions/buttons/
                    buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'users' }
                    ],

                    // scroller extension: http://datatables.net/extensions/scroller/
                    scrollY:        300,
                    deferRender:    true,
                    scroller:       true,
                    deferRender:    true,
                    scrollX:        true,
                    scrollCollapse: true,

                    stateSave:      true,

                    "order": [
                        [0, 'asc']
                    ],

                    "lengthMenu": [
                        [10, 15, 20, -1],
                        [10, 15, 20, "All"] // change per page values here
                    ],
                    // set the initial value
                    "pageLength": 10,

                    "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

                    // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                    // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
                    // So when dropdowns used the scrollable div should be removed.
                    //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                });
            }

            var initTable2= function () {
                var table = $('#manage_account');

                var oTable = table.dataTable({

                    // Internationalisation. For more info refer to http://datatables.net/manual/i18n
                    "language": {
                        "aria": {
                            "sortAscending": ": activate to sort column ascending",
                            "sortDescending": ": activate to sort column descending"
                        },
                        "emptyTable": "No data available in table",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "infoEmpty": "No entries found",
                        "infoFiltered": "(filtered1 from _MAX_ total entries)",
                        "lengthMenu": "_MENU_ entries",
                        "search": "Search:",
                        "zeroRecords": "No matching records found"
                    },

                    // Or you can use remote translation file
                    //"language": {
                    //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
                    //},

                    // setup buttons extension: http://datatables.net/extensions/buttons/
                    buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'accounts'}
                    ],

                    // scroller extension: http://datatables.net/extensions/scroller/
                    scrollY:        300,
                    deferRender:    true,
                    scroller:       true,
                    deferRender:    true,
                    scrollX:        true,
                    scrollCollapse: true,

                    stateSave:      true,

                    "order": [
                        [0, 'asc']
                    ],

                    "lengthMenu": [
                        [10, 15, 20, -1],
                        [10, 15, 20, "All"] // change per page values here
                    ],
                    // set the initial value
                    "pageLength": 10,

                    "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

                    // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                    // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
                    // So when dropdowns used the scrollable div should be removed.
                    //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                });
            }


            return {

                //main function to initiate the module
                init: function () {

                    if (!jQuery().dataTable) {
                        return;
                    }
                    initPickers();
                    initTable1();
                    initTable2();
                }

            };

        }();

        jQuery(document).ready(function() {
            TableDatatablesScroller.init();
        });


</script>


@endsection
