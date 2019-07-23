@extends('layouts.layout')
@section('label', 'Report List')
@section('content')
    <h1 class="page-title font-red-intense"> Report List
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Report List</span>
                    </div>
                </div>
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
							@permission('requestreport-create')
                            <div class="btn-group">
                                <a href="{{ url('rr/create')}}" target="_blank"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add Report
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
							@endpermission
                        </div>
                        <!--
                        <div class="col-md-6">
                            <div class="btn-group pull-right">
                                <button class="btn blue  btn-outline dropdown-toggle" data-toggle="dropdown">Tools
                                    <i class="fa fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="javascript:;">
                                            <i class="fa fa-file-excel-o"></i> Export to Excel </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        -->
                    </div>
                </div>
                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover order-column" id="manage_report">
                        <thead>
                        <tr>
                            <th> Account </th>
                            <th> Report Type </th>
							<th> Report Date </th>
                            <th> Request Date </th>
							<th> Complete Date </th>
							<th> Status </th>
                            <th> Actions </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($datas as $data)
                            <tr class="odd gradeX">
                                <td>
                                    {{array_get($accounts,$data->SellerId.'.name').' -- '.array_get($accounts,$data->SellerId.'.area')}}
                                </td>
                                <td>
                                    {{$data->Type}}
                                </td>
								<td>
                                    {{$data->StartDate}} -- {{$data->EndDate}}
                                </td>
                                <td>
                                    {{$data->RequestDate}}
                                </td>
                                <td>
                                    {{$data->CompleteDate}}
                                </td>
								<td>
                                    {{$data->Message}}
                                </td>
                                <td>
									@if($data->Path)
                                    <a href="http://116.6.105.153:18003/reports/{{$data->Path}}" target="_blank">
                                        <button type="submit" class="btn btn-success btn-xs">Download</button>
                                    </a>
									@endif
                                    <form action="{{ url('rr/'.$data->id) }}" method="POST" style="display: inline;">
                                        {{ method_field('DELETE') }}
                                        {{ csrf_field() }}
                                        <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                    </form>
                                </td>
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
        $(function() {
        var TableDatatablesManaged = function () {

            var initTable = function () {

                var table = $('#manage_report');

                // begin first table
                table.dataTable({

                    // Internationalisation. For more info refer to http://datatables.net/manual/i18n
                    "language": {
                        "aria": {
                            "sortAscending": ": activate to sort column ascending",
                            "sortDescending": ": activate to sort column descending"
                        },
                        "emptyTable": "No data available in table",
                        "info": "Showing _START_ to _END_ of _TOTAL_ records",
                        "infoEmpty": "No records found",
                        "infoFiltered": "(filtered1 from _MAX_ total records)",
                        "lengthMenu": "Show _MENU_",
                        "search": "Search:",
                        "zeroRecords": "No matching records found",
                        "paginate": {
                            "previous":"Prev",
                            "next": "Next",
                            "last": "Last",
                            "first": "First"
                        }
                    },

                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.

                    "lengthMenu": [
                        [5, 15, 20, -1],
                        [5, 15, 20, "All"] // change per page values here
                    ],
                    // set the initial value
                    "pageLength": 20,
                    "pagingType": "bootstrap_full_number",
                    "columnDefs": [

                        {
                            "className": "dt-right",
                            //"targets": [2]
                        }
                    ],
					
                });

            }


            return {

                //main function to initiate the module
                init: function () {
                    if (!jQuery().dataTable) {
                        return;
                    }

                    initTable();
                }

            };

        }();



            TableDatatablesManaged.init();
        });


</script>


@endsection
