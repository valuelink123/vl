@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Product Guide
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">

                    <div class="col-md-8">
                        {{--<div class="table-actions-wrapper" id="table-actions-wrapper">--}}
                        {{--<span> </span>--}}
                        {{--<input id="giveBrandLine" placeholder="Set Brand Line" class="table-group-action-input form-control input-inline input-small input-sm">--}}
                        {{--<button class="btn btn-sm green table-group-action-submit">--}}
                        {{--<i class="fa fa-search"></i> Search--}}
                        {{--</button>--}}
                        {{--</div>--}}
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
                        <th>User Manual</th>
                        <th>Video List</th>
                        <th>Q&A</th>
                        <th>Parts list</th>
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
                    orderable: false,
                    searchable: false,
                    // defaultContent: '<button type="button" class="btn btn-success btn-xs">View</button>',
                    render(data, type, row, meta) {
                        return `<button type='button' class='btn btn-success btn-xs'>View</button>`
                    }
                },
                {
                    orderable: false,
                    searchable: false,
                    render(data, type, row, meta) {
                        let args = {'item_group': row.item_group, 'item_model': row.item_model}
                        return `<a href="/kms/videolist?${jQuery.param(args)}" target="_blank" class='btn btn-success btn-xs'>View</a>`
                    }
                },
                {
                    orderable: false,
                    searchable: false,
                    render() {
                        return `<button class='btn btn-success btn-xs'>View</button>`
                    }
                },
                {
                    orderable: false,
                    searchable: false,
                    render() {
                        return `<button class='btn btn-success btn-xs'>View</button>`
                    }
                }
            ],
            ajax: {
                type: 'POST',
                url: '/kms/brandline/get',
                // dataSrc(json) { return json.data }
            }
        })

        // `<button type='button' class='btn btn-success btn-xs' data-search='${JSON.stringify(search)}'>View</button>`
        // $theTable.on('click', '.btn', (e) => {
        //     let search = $(e.target).data('search')
        //     if (!search) return;
        //     window.open(`/kms/${search.type}?${jQuery.param(search.args)}`)
        // })
    </script>

@endsection