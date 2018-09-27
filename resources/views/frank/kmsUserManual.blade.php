@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> User Manual
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="btn-group " style="float:right;">

                            <a id="excel-import" class="btn sbold green" href="/kms/usermanual/import"> Import
                                <i class="fa fa-plus-circle"></i>
                            </a>

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
                        <th>Item Group</th>
                        <th>Item Group Description</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Manual</th>
                        <th>Date</th>
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
                let obj = queryStringToObject()
                if (obj.item_group) {
                    let ands = data.search.ands = {}
                    ands.item_group = obj.item_group
                    ands.brand = obj.brand
                    ands.item_model = obj.item_model
                    return
                }
            }

            history.replaceState(null, null, '?search=' + encodeURIComponent($.trim(data.search.value)))
        })

        $theTable.dataTable({
            search: {search: queryStringToObject().search},
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'item_group', name: 'item_group'},
                {data: 'item_group_descr', name: 'item_group_descr'},
                {data: 'brand', name: 'brand'},
                {data: 'item_model', name: 'item_model'},
                {
                    data: 'link',
                    name: 'link',
                    orderable: false,
                    render(data) {
                        return `<a href="${data}" target="_blank" class='btn btn-success btn-xs'>View</a>`
                    }
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                }
            ],
            ajax: {
                type: 'POST',
                url: '/kms/usermanual/get',
            }
        })
    </script>

@endsection