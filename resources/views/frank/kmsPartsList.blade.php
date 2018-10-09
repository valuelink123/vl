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
                        <th>Seller Name</th>
                        <th>Asin</th>
                        <th>Seller SKU</th>
                        <th>Item Name</th>
                        <th>Fbm Stock</th>
                        <th>Fba Stock</th>
                        <th>Fba Transfer</th>
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
                {data: 'item_code', name: 'item_code'},
                {data: 'seller_name', name: 'seller_name'},
                {data: 'asin', name: 'asin'},
                {data: 'seller_sku', name: 'seller_sku'},
                {data: 'item_name', name: 'item_name'},
                {data: 'fbm_stock', name: 'fbm_stock'},
                {data: 'fba_stock', name: 'fba_stock'},
                {data: 'fba_transfer', name: 'fba_transfer'}
            ],
            ajax: {
                type: 'POST',
                url: '/kms/partslist/get',
            }
        })

    </script>

@endsection