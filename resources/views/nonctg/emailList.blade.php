<table class="table table-striped table-bordered table-hover order-column" id="email_table">
                                <thead>
                                <tr>
                                    <th>From Address</th>
                                    <th>To Address</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th width="110px">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($emails as $data)
                                    <tr class="odd gradeX">
                                        <td>
                                            {{array_get($data,'from_address')}}
                                        </td>
                                        <td>
                                            {{array_get($data,'to_address')}}
                                        </td>
                                        <td>
                                            <a href="/send/{{array_get($data,'id')}}" target="_blank"> {{array_get($data,'subject')}}</a>
                                        </td>
                                        <td>
                                            {{array_get($data,'date')}}
                                        </td>
                                        <td>
                                            {{array_get($users,array_get($data,'user_id'))}}
                                        </td>
                                        <td>
                                            {!!array_get($data,'send_date')?'<span class="label label-sm label-success">'.array_get($data,'send_date').'</span> ':'<span class="label label-sm label-danger">'.array_get($data,'status').'</span>'!!}
                                        </td>

                                    </tr>
                                @endforeach


                                </tbody>
                            </table>
                            <script>
                                $(function () {
                                    $('#email_table').dataTable({
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
                                                "previous": "Prev",
                                                "next": "Next",
                                                "last": "Last",
                                                "first": "First"
                                            }
                                        },

                                        "bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
                                        "autoWidth": false,
                                        "lengthMenu": [
                                            [10, 50, 100, -1],
                                            [10, 50, 100, "All"] // change per page values here
                                        ],
                                        // set the initial value
                                        "pageLength": 10,
                                        "order": [
                                            [3, "desc"]
                                        ] // set first column as a default sort by asc
                                    });
                                });
                            </script>