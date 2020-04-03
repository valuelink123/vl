<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>

@include('frank.common')
    <div class="portlet light bordered">
        <div style="margin-bottom: 15px"></div>
        <div class="portlet-body">
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        <th>Item Group</th>
                        <th>Item Group Description</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Manual</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>

        let $theTable = $(thetable)

        $theTable.dataTable({
            //search: {search: queryStringToObject().search},
            search: {search: '{{ $searchTerm }}'},
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'item_group', name: 'item_group', width: '70px'},
                {data: 'brand_line', name: 'brand_line', width: '181px'},
                {data: 'brand', name: 'brand', width: '70px'},
                {data: 'item_model', name: 'item_model', width: '70px'},
                {
                    data: 'link',
                    name: 'link',
                    orderable: false,
                    width: '70px',
                    render(data) {
                        return `<a href="${data}" target="_blank" class='btn btn-success btn-xs'>View</a>`
                    }
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    width: '70px'
                },
                {data: 'action', orderable: false,name: 'action', width: '70px'}
            ],
            ajax: {
                type: 'POST',
                url: '/kms/usermanual/get',
            }
        })
    </script>
