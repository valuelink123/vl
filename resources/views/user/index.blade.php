@extends('layouts.layout')
@section('label', 'User Accounts Manage')
@section('content')
    <h1 class="page-title font-red-intense"> User List
        <small>Manager users and user's permissions</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">User List</span>
                    </div>
                </div>
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a href="{{ url('total')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Data Statistics
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="btn-group ">
                                <form role="form" action="{{url('user')}}" method="GET">
                        {{ csrf_field() }}
						<div class="col-md-3">
						<select class="form-control form-filter input-sm" name="user_id_from">
                                        <option value="">Select...</option>
                                        @foreach ($groups as $group_id=>$group)
											<optgroup label="{{array_get($group,'group_name')}}">
												@foreach (array_get($group,'user_ids') as $user_id)
												<option value="{{$group_id.'_'.$user_id}}">{{array_get($users_array,$user_id)}}</option>
												@endforeach
											</optgroup>
										@endforeach
                                    </select>
						</div>
						
						<div class="col-md-3">
						<select class="form-control form-filter input-sm" name="user_id_to">
                                        <option value="">Select...</option>
                                        @foreach ($groups as $group_id=>$group)
											<optgroup label="{{array_get($group,'group_name')}}">
												@foreach (array_get($group,'user_ids') as $user_id)
												<option value="{{$group_id.'_'.$user_id}}">{{array_get($users_array,$user_id)}}</option>
												@endforeach
											</optgroup>
										@endforeach
                                    </select>
						</div>	

								
                        <div class="col-md-3">
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="submit" class="btn blue">Change Mail User</button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    </form>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover table-checkable order-column" id="manage_account">
                        <thead>
                        <tr>
                            <th> ID </th>
                            <th> Email</th>
                            <th> Name </th>
                            <th> Admin</th>
                            <th> Created At </th>
                            <th> Updated At </th>
                            <th> Action </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr class="odd gradeX">
                                <td>
                                    {{$user['id']}}
                                </td>
                                <td>
                                    {{$user['email']}}
                                </td>
                                <td>
                                    {{$user['name']}}
                                </td>
                                <td>
                                    {{$user['admin']?'Yes':'No'}}
                                </td>
                                <td>
                                    {{$user['created_at']}}
                                </td>

                                <td>
                                    {{$user['updated_at']}}
                                </td>

                                <td>

                                    <a href="{{ url('user/'.$user['id'].'/edit') }}">
                                        <button type="submit" class="btn btn-success btn-xs">Edit</button>
                                    </a>
                                    <form action="{{ url('user/'.$user['id']) }}" method="POST" style="display: inline;">
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

                var table = $('#manage_account');

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
                    "order": [
                        [1, "asc"]
                    ] // set first column as a default sort by asc
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
