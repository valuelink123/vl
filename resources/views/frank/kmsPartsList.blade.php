@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Parts List
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="btn-group " style="float:right;">

                            <button id="excel-export" class="btn sbold blue"> Export
                                <i class="fa fa-download"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both;height:50px;"></div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        <th>Item No</th>
                        <th>Item Name</th>
                        <th>Stock</th>
                        <th>Average Per Day</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>

        let $theTable = $(thetable)

        $theTable.on('preXhr.dt', (e, settings, data) => {
            if (!data.search.value) {
                let obj = getQuerys()
                if (obj.item_group) {
                    let ands = data.search.ands = {}
                    ands.item_group = obj.item_group
                    ands.item_model = obj.item_model
                    return
                }
            }

            history.replaceState(null, null, '?search=' + encodeURIComponent($.trim(data.search.value)))
        })

        $theTable.dataTable({
            search: {search: getQuerys().search},
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'item_no', name: 'item_no'},
                {data: 'item_name', name: 'item_name'},
                {data: 'stock', name: 'stock'},
                {
                    data: 'apd',
                    name: 'apd',
                },
            ],
            ajax: {
                type: 'POST',
                url: '/kms/partslist/get',
            }
        })

    </script>

@endsection