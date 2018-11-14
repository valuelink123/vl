@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['CTG']])
@endsection
@section('content')

    <link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
    <script src="/js/chosen/chosen.jquery.min.js"></script>

    <style>
        .form-control {
            height: 29px;
        }
    </style>

    @include('frank.common')

    <h1 class="page-title font-red-intense"> CTG List
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar" id="thetabletoolbar">
                <div class="row">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd 00:00:00'" value="{!! date('Y-m-d 00:00:00', strtotime('-90 day')) !!}" data-init-by-query="ands.date_from" id="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">To</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd 23:59:59'" value="{!! date('Y-m-d 23:59:59') !!}" data-init-by-query="ands.date_to" id="date_to" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Expect Rating</span>
                            <select multiple style="width:100%;" name="rating">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon">Processor</span>
                            <select multiple style="width:100%;" name="processor">
                                @foreach($users as $id=>$name)
                                    <option value="{!! $id !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon">Status</span>
                            <select multiple style="width:100%;" name="status">
                                <option value="0">Confirm Review</option>
                                <option value="1">Arrange Shipment</option>
                                <option value="2">Delivery Confirmation</option>
                                <option value="3">Lead To Leave Review</option>
                                <option value="4">Re-SG</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
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
                        <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                            <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()"/>
                        </th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Customer Email</th>
                        <th>Phone Number</th>
                        <th>Expect Rating</th>
                        <th>Reviewed</th>
                        <th>Tracking Note</th>
                        <th>Status</th>
                        <th>Processor</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <script type="text/template" id="bottomtoolbar">
                    <div class="row">
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon">Task Assign to</span>
                                <input class="xform-autotrim form-control" list="list-assignto" id="assignto"/>
                                <datalist id="list-assignto">
                                    <% for(let user_id in users) { %>
                                    <option value="${user_id} | ${users[user_id]}">
                                        <% } %>
                                </datalist>
                            </div>
                        </div>
                    </div>
                </script>
            </div>
        </div>
    </div>

    <script>

        $("#thetabletoolbar [id^='date_']").each(function () {

            let defaults = {
                autoclose: true
            }

            let options = eval(`({${$(this).data('options')}})`)

            $(this).datepicker(Object.assign(defaults, options))
        })

        $("#thetabletoolbar select[multiple]").chosen()

        $(thetabletoolbar).change(e => {
            dtApi.ajax.reload()
        })

        let $theTable = $(thetable)

        $theTable.on('preXhr.dt', (e, settings, data) => {

            Object.assign(data.search, {
                // value: fuzzysearch.value,
                ands: {
                    date_from: date_from.value,
                    date_to: date_to.value,
                    // item_group: item_group.value,
                    // brand: brand.value,
                    // item_model: item_model.value
                }
            })

            history.replaceState(null, null, '?' + objectToQueryString(data.search))
        })

        $theTable.dataTable({
            // searching: false,
            search: {search: queryStringToObject().search},
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            order: [[1, 'desc']],
            select: {
                style: 'os',
                info: true, // info N rows selected
                // blurable: true, // unselect on blur
                selector: 'td:first-child', // 指定第一列可以点击选中
            },
            columns: [
                {
                    width: "1px",
                    orderable: false,
                    defaultContent: '',
                    className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                },
                {
                    width: "55px",
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    width: "20px",
                    data: 'name',
                    name: 'name'
                },
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {
                    width: "20px",
                    data: 'rating',
                    name: 'rating'
                },
                {
                    width: "20px",
                    data: 'commented',
                    name: 'commented',
                    render(data) {
                        return parseInt(data) > 0 ? 'Yes' : 'No'
                    }
                },
                {
                    width: "200px",
                    data: 'steps',
                    name: 'steps',
                    render(data, type, row) {
                        if (!data) return ''
                        let steps = JSON.parse(data)
                        return steps.track_notes[row.status] || ''
                    }
                },
                {
                    width: "190px",
                    data: 'status',
                    name: 'status'
                },
                {
                    width: "120px",
                    data: 'processor',
                    name: 'processor'
                },
                {
                    width: "20px",
                    data: 'order_id',
                    name: 'order_id',
                    orderable: false,
                    render(data) {
                        return `<a class="btn btn-danger btn-xs" href="/ctg/list/process?order_id=${data}" target="_blank">Process</a>`
                    }
                }
            ],
            ajax: {
                type: 'POST',
                url: location.href
            }
        })

        let users = @json($users) ;
        $theTable.closest('.table-scrollable').after(tplRender(bottomtoolbar, {users}))
        $(assignto).change(e => {

            $this = $(e.currentTarget)

            let processor = parseInt($this.val())
            if (isNaN(processor)) return

            let selectedRows = dtApi.rows({selected: true})

            let order_ids = selectedRows.data().toArray().map(obj => obj.order_id)

            if (!order_ids.length) {
                $this.val('')
                toastr.error('Please select some rows first !')
                return
            }

            postByJson('/ctg/batchassigntask', {processor, order_ids}).then(arr => {
                for (let rowIndex of selectedRows[0]) {
                    // console.log(dtApi.cell(rowIndex, 9).data())
                    dtApi.cell(rowIndex, 9).data(arr[1]).draw()
                }
                toastr.success('Saved !')
            }).catch(err => {
                toastr.error(err.message)
            })
        })

        let dtApi = $theTable.api()

    </script>

@endsection