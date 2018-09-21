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

                            <a id="excel-import" class="btn sbold green" href="/kms/videolist/new"> Import
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

        $theTable.one('preXhr.dt', (e, settings, data) => {
            let obj = getQuerys()
            if (obj.search) {
                data.search.value = obj.search
            } else if (obj.item_group) {
                data.search.item_group = obj.item_group
                data.search.item_model = obj.item_model
            }
            $theTable.on('preXhr.dt', (e, settings, data) => {
                history.replaceState(null, null, '?search=' + encodeURIComponent($.trim(data.search.value)))
            })
        })

        function getQuerys() {
            let obj = {}
            if (location.search) {
                let strs = location.search.substr(1).split('&')
                for (let str of strs) {
                    let par = str.split('=')
                    obj[par[0]] = par[1] ? decodeURIComponent(par[1]) : ''
                }
            }
            return obj
        }

        $theTable.dataTable({
            // search: {search: location.search},
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