<table class="table table-striped table-bordered table-hover order-column" id="rsg_request_table">
    <thead>
    <tr>
        <th>Submit Date</th>
        <th>Customer Email</th>
        <th>Request Product</th>
        <th>Current Step</th>
        <th>Site</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    $(function () {
        $('#rsg_request_table').dataTable({
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No matching records found",
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
            ajax: {
                type: 'POST',
                url: "/crm/getRsgRequestList",
                data:{ 'record_id': '{{$record_id}}' }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'customer_email', name: 'customer_email'},
                {data: 'asin_link', name: 'asin_link'},
                {data: 'step', name: 'step'},
                {data:'site',name:'site'},
            ],
            "bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
            "autoWidth": false,
            "lengthMenu": [
                [10, 50, 100, -1],
                [10, 50, 100, "All"] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,
            "order": [
                //[1, "asc"]
            ]
        });
    });

</script>
