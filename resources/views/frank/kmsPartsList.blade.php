@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <style>

        body {
            overflow-y: scroll; /* 避免因“展开/收缩”产生的晃动 */
        }

        #thetable .sub-item-row {
            padding: 0;
            background: #fff;
        }

        #thetable tr:nth-of-type(even) + .sub-item-row tr {
            background: #eef1f5;
        }

        #thetable .sub-item-row .table {
            margin-bottom: 0;
        }

        .sub-item-row th, .sub-item-row td {
            text-align: center;
        }

    </style>

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
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="text/template" id="sub-table-tpl">
        `
        <table class="table">
            <thead>
            <tr>
                <th>Item No</th>
                <th>Asin</th>
                <th>Item Name</th>
                <th>Fbm Stock</th>
                <th>Fba Stock</th>
                <th>Fba Transfer</th>
            </tr>
            </thead>
            <tbody>
            ${trs}
            </tbody>
        </table>
        `
    </script>

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
                {data: 'fba_transfer', name: 'fba_transfer'},
                {
                    "className": 'details-control disabled',
                    "orderable": false,
                    "data": 'item_code',
                    render(item_code) {
                        return `<a class="ctrl-${item_code}"></a>`
                    }
                }
            ],
            ajax: {
                type: 'POST',
                url: '/kms/partslist/get',
                dataSrc(json) {
                    let rows = json.data
                    for (let row of rows) {
                        let item_code = row.item_code
                        $.post('/kms/partslist/subitems', {item_code}).success(rows => {
                            if (rows.length > 0) {
                                $(`.ctrl-${item_code}`).parent().removeClass('disabled')
                            }
                        })
                    }
                    return rows
                }
            }
        })

        async function buildSubItemTable(item_code) {

            let rows = await new Promise((resolve, reject) => {
                $.post('/kms/partslist/subitems', {item_code})
                    .success(rows => resolve(rows))
                    .error((xhr, status, errmsg) => reject(errmsg))
            })

            if (!rows.length) return ''

            let trs = []

            for (let row of rows) {
                trs.push(`<tr><td>${row.item_code}</td><td>${row.asin}</td><td>${row.item_name}</td><td>${row.fbm_stock}</td><td>${row.fba_stock}</td><td>${row.fba_transfer}</td></tr>`)
            }

            let tpl = $('#sub-table-tpl').html()

            trs = trs.join('')

            return eval(tpl)
        }

        // Add event listener for opening and closing details
        $theTable.on('click', 'td.details-control', function () {

            let $td = $(this)

            let row = $theTable.api().row($td.closest('tr'));

            if (row.child.isShown()) {
                row.child.remove();
                $td.removeClass('closed');
            } else {
                let {item_code} = row.data()
                let id = `sub-item-loading-${item_code}`

                row.child(`<div id="${id}" style="padding:3em;">Data is Loading...</div>`, 'sub-item-row').show()

                buildSubItemTable(item_code).then(html => {
                    if (html) {
                        $td.removeClass('disabled')
                        $(`#${id}`).parent().html(html)
                    } else {
                        $(`#${id}`).html('Nothing to Show.')
                    }
                })

                $td.addClass('closed');
            }
        });

    </script>

@endsection