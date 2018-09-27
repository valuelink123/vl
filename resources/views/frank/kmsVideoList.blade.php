@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Video List
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="btn-group " style="float:right;">

                            <a id="excel-import" class="btn sbold green" href="/kms/videolist/import"> Import
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
                        <th>Type</th>
                        <th>Video Description</th>
                        <th>Video Link</th>
                        <th>Note</th>
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
                    data: 'type',
                    name: 'type',
                    searchable: false,
                },
                {
                    data: 'descr',
                    name: 'descr',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'link',
                    name: 'link',
                    orderable: false,
                    searchable: false,
                    render(data) {
                        return `<span class="video-link">${data}</span>`
                    }
                },
                {
                    data: 'note',
                    name: 'note',
                    orderable: false,
                    searchable: false,
                }
            ],
            ajax: {
                type: 'POST',
                url: '/kms/videolist/get',
                // data(args) {
                //     // 过滤 Request 表单
                //     let columns = args.columns
                //     for (let i = 0; i < columns.length; i++) {
                //         let column = columns[i]
                //         if (!column.searchable && !column.orderable) delete columns[i]
                //     }
                // }
                // // 过滤 Response 数据
                // dataSrc(json) { return json.data }
            }
        })

        $theTable.on('click', '.video-link', (e) => {
            selectText(e.target)
        })

    </script>

@endsection