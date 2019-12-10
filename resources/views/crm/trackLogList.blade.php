<table class="table table-striped table-bordered table-hover order-column" id="track_log_table">
    <thead>
    <tr>
        <th>ID</th>
        {{--<th>Type</th>--}}
        <th>Channel</th>
        <th>Email</th>
        <th>Subject Type</th>
        <th>Note</th>
        {{--<th>Processor</th>--}}
        <th>Created At</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>
<script>
    $(function () {
        $('#track_log_table').dataTable({
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
            ajax: {
                type: 'POST',
                url: "/crm/getTrackLog",
                data:{ 'record_id': '{{$record_id}}' }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'channel', name: 'channel'},
                {data: 'email', name: 'email'},
                {data: 'subject_type', name: 'subject_type'},
                {data: 'note', name: 'note'},
                {data: 'created_at', name: 'created_at'}
            ],
            columnDefs: [
                //给第5列指定宽度
                { "width": "500px", "targets": 4 }
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
                //[3, "desc"]
            ] // set first column as a default sort by asc
        });


    });

    function see_more(id){
        $see_more_element = $('a[number=' + id + ']');
        var note_complete = $see_more_element.next().html();
        $previous = $see_more_element.prev();
        $next = $see_more_element.next();
        if($see_more_element.text() == 'See More'){
            $previous.hide();
            $next.show();
            $see_more_element.text('See Less');
        }
        else{
            $previous.show();
            $next.hide();
            $see_more_element.text('See More');
        }
   }
</script>
<style type="text/css">
    .text{
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>