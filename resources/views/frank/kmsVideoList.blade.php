@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')
    <h1 class="page-title font-red-intense"> Video List
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">

                    <div class="col-md-8">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
                            <span> </span>

                            <input id="giveBrandLine" placeholder="Set Brand Line" class="table-group-action-input form-control input-inline input-small input-sm">
                            <button class="btn btn-sm green table-group-action-submit">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>


                    </div>
                    <div class="col-md-4">
                        <div class="btn-group " style="float:right;">
                            <button id="vl_list_export" class="btn sbold blue"> Export
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
        let $theTable = $(thetable).dataTable({
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'item_group', name: 'item_group'},
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
                // dataSrc(json) { return json.data }
            }
        })

        $theTable.on('click', '.video-link', (e) => {
            selectText(e.target)
        })

        /**
         * 选中文本
         */
        function selectText(ele) {
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(ele);
                range.select();
            } else if (window.getSelection) {
                window.getSelection().empty();
                var range = document.createRange();
                range.selectNodeContents(ele);
                window.getSelection().addRange(range);
            }
        }
    </script>

@endsection