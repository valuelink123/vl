@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>[['KMS', '/kms/productguide'], 'Inventory Inquiry']])
@endsection
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
        .invalid-account,.invalid-account a{color:red !important;}

    </style>

    <h1 class="page-title font-red-intense"> Inventory Inquiry
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
            @permission('partslist-update')
            <div class="col-md-4" style="float:right;margin-right:-70px;">
                <div class="col-md-6">
                    <select class="mt-multiselect btn btn-default select-user-id" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="seller_name[]" id="seller_name[]" value="">
                        @foreach ($sellerName as $key=>$value)
                            <option value="{{$key}}" class="{{$value['class']}}">{{$value['seller_name']}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="table-actions-wrapper">
                    <select id="status" class="table-group-action-input form-control input-inline input-small input-sm" name="status" value="">
                        <option value="-1">Select Status</option>
                        <option value="0">Valid</option>
                        <option value="1">Invalid</option>
                    </select>
                    <button class="btn btn-sm green table-group-action-submit update-status">
                        <i class="fa fa-check"></i> Update</button>
                </div>
            </div>
            @endpermission
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
                        <th>Fbm Valid Stock</th>
                        <th>Fba Stock</th>
                        <th>Fba Transfer</th>
                        <th>Unsellable</th>
                        <th>Fbm Update</th>
                        <th>Fba Update</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="text/template" id="sub-table-tpl">
        <table class="table">
            <thead>
            <tr>
                <th>Item No</th>
                <th>Seller Name</th>
                <th>Asin</th>
                <th>Seller SKU</th>
                <th>Item Name</th>
                <th>Fbm Stock</th>
                <th>Fbm Valid Stock</th>
                <th>Fba Stock</th>
                <th>Fba Transfer</th>
                <th>Unsellable</th>
                <th>Fbm Update</th>
                <th>Fba Update</th>
            </tr>
            </thead>
            <tbody>
            <% for(let row of rows){ %>
            <tr>
                <td>${row.item_code}</td>
                <td>${row.seller_name}</td>
                <td>${row.asin}</td>
                <td>${row.seller_sku}</td>
                <td>${row.item_name}</td>
                <td>${row.fbm_stock}</td>
                <td>${row.fbm_valid_stock}</td>
                <td>${row.fba_stock}</td>
                <td>${row.fba_transfer}</td>
                <td>${row.unsellable}</td>
                <td>${row.fbm_update}</td>
                <td>${row.fba_update}</td>
            </tr>
            <% } %>
            </tbody>
        </table>
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
            aoColumnDefs: [ { "bSortable": false, "aTargets": [ 6,9 ] }],
            columns: [
                {data: 'item_code', name: 'item_code'},
                {data: 'seller_name', name: 'seller_name'},
                {data: 'asin', name: 'asin'},
                {data: 'seller_sku', name: 'seller_sku'},
                {data: 'item_name', name: 'item_name'},
                {data: 'fbm_stock', name: 'fbm_stock'},
                {data: 'fbm_valid_stock', name: 'fbm_valid_stock'},
                {data: 'fba_stock', name: 'fba_stock'},
                {data: 'fba_transfer', name: 'fba_transfer'},
                {data: 'unsellable', name: 'unsellable'},
                {data: 'fbm_update', name: 'fbm_update'},
                {data: 'fba_update', name: 'fba_update'},
                {
                    width: "2px",
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
                        // 根据每一行 item_code 进行预查询，如果有配件数据，则将加号按钮变绿
                        $.post('/kms/partslist/subitems', {item_code}).success(rows => {
                            if (rows.length > 0) {
                                if (false === rows[0]) return
                                $(`#thetable .ctrl-${item_code}`).parent().removeClass('disabled')
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
                    .error((xhr, status, errmsg) => reject(new Error(errmsg)))
            })

            if (!rows.length) return ''

            if (false === rows[0]) return Promise.reject(new Error(rows[1]))

            return tplRender('#sub-table-tpl', {rows})
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
                }).catch(err => {
                    $(`#${id}`).html(`<span style="color:red">Server Error: ${err.message}</span>`)
                })

                $td.addClass('closed');
            }
        });

        let dtApi = $theTable.api();
        //点击update的时候，批量设置对应的账号机是否有效
        $('.update-status').click(function(){
            var seller_name = $("select[name='seller_name[]']").val();
            var status = $("select[name='status']").val();
            //账号机跟状态为必选的下拉框
            if(!seller_name){
                alert('Please select account machine!');
                return false;
            }
            if(status=='-1'){
                alert('Please select state');
                return false;
            }
            //ajax设置账号机的状态
            $.post("/kms/partslist/updateStatus",
                {
                    "_token":"{{csrf_token()}}",
                    "seller_name":seller_name,
                    "status":status
                },
                function(res){
                    if(res){
                        toastr.success('Saved !');
                        //更新下拉选择框内的颜色
                        var obj = $('.multiselect-container .active');
                        $.each(obj,function(i,item){
                            if(status==1){//无效的时候，添加类名，标红显示
                                $(this).addClass('invalid-account');
                            }else{
                                $(this).removeClass('invalid-account');
                            }
                        })
                        //更新表格内的数据
                        dtApi.ajax.reload();
                    }
                }
            );

        })

    </script>

@endsection