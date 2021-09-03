@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['NON-CTG']])
@endsection
@section('content')

    <link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
    <script src="/js/chosen/chosen.jquery.min.js"></script>

    <style>
        .form-control {
            height: 29px;
        }
        .dataTables_extended_wrapper .table.dataTable {
            margin: 0px !important;
        }


        th,td,td>span {
            font-size:12px !important;
            font-family:Arial, Helvetica, sans-serif;}
    </style>

    @include('frank.common')

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar" id="thetabletoolbar">
                <div class="row">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! date('Y-m-d', strtotime('-90 day')) !!}" data-init-by-query="daterange.from" id="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">To</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! date('Y-m-d', strtotime('+1 day')) !!}" data-init-by-query="daterange.to" id="date_to" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select multiple style="width:100%;" id="bg" data-init-by-query="ins.bg">
                                @foreach($bgs as $bg)
                                    <option value="{!! $bg !!}">{!! $bg !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">BU</span>
                            <select multiple style="width:100%;" id="bu" data-init-by-query="ins.bu">
                                @foreach($bus as $bu)
                                    <option value="{!! $bu !!}">{!! $bu !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon">Processor</span>
                            <select multiple style="width:100%;" id="processor" data-init-by-query="ins.processor">
                                @foreach($users as $id=>$name)
                                    <option value="{!! $id !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">Status</span>
                            <select multiple style="width:100%;" id="status" data-init-by-query="ins.status">
                                @foreach($status as $sk=>$sv)
                                    <option value="{!! $sk !!}">{!! $sv !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select style="width:100%;height:29px;" id="site" data-init-by-query="ins.site">
                                <option value="" >Select</option>
                                @foreach(getSiteUrl() as $k=>$v)
                                    <option value="{!! $v !!}" >{!! $v !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">Type</span>
                            <select multiple style="width:100%;" id="crmType" data-init-by-query="ins.crmType">
                                @foreach(getCrmClientType() as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        @permission('non-ctg-export')
                        <div class="btn-group">
                            <button id="export" class="btn sbold blue"> Export
                                <i class="fa fa-download"></i>
                            </button>
                        </div>
                        @endpermission

                        @permission('compose')
                        <div class="btn-group">
                            <button id="batch-send" class="btn sbold blue"> Batch Send
                            </button>
                        </div>
                        @endpermission
                    </div>
                </div>
            </div>
            <div style="clear:both;height:50px; text-align: right;">

            </div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                            <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>
                        </th>
                        <th> Date </th>
                        <th> Email </th>
                        <th> Name </th>
                        <th>Order ID </th>
                        <th>Asin</th>
                        <th>Saleschannel</th>
                        <th>Sellersku</th>
                        <th>Item Group</th>
                        <th>Item no</th>
                        <th>From</th>
                        <th>Status</th>
                        <th>BG</th>
                        <th>BU</th>
                        <th>Sales</th>
                        <th>Processor</th>
                        <th>Track Note</th>
                        <th>Join RSg</th>
                        <th> Action </th>
                        <th>Email-Hidden</th>
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
                                    @foreach($users as $id=>$name)
                                        <option value="{!! $id !!} | {!! $name !!}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>
                </script>
            </div>
        </div>
    </div>

    <script>

        XFormHelper.initByQuery('[data-init-by-query]')

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
                daterange: {
                    from: date_from.value,
                    to: date_to.value
                },
                ands: {
                    site:$('#site').val(),
                    // item_group: item_group.value,
                    // brand: brand.value,
                    // item_model: item_model.value
                },
                ins: {
                    processor: $('#processor').val(),
                    status: $('#status').val(),
                    bg: $('#bg').val(),
                    bu: $('#bu').val(),
                    crmType: $('#crmType').val(),
                    from: $('#from').val(),
                }
            })

            history.replaceState(null, null, '?' + objectToQueryString(data.search))
        })

        $theTable.dataTable({
            // searching: false,
            search: {search: queryStringToObject().value},
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
            "aoColumnDefs": [
                { "bVisible": false, "aTargets": [19] }
            ],
            columns: [
                {
                    width: "1px",
                    orderable: false,
                    defaultContent: '',
                    className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                },
                {
                    width: "55px",
                    data: 'date',
                    name: 'date'
                },
                {data: 'email', name: 'email'},
                {data: 'name', name: 'name'},
                {data: 'order_id', name: 'order_id'},
                {
                    data: 'asin',
                    name: 'asin',
                    render(data, type, row) {
                        if (!data) return ''
                        let asin = data.split(',')
                        return asin.map(asin => {
                            return `<a href="https://${row.site}/dp/${asin}" target="_blank" rel="noreferrer">${asin}</a>`
                        }).join('<br/>')
                    }
                },
                {data: 'saleschannel', name: 'saleschannel'},
                {data: 'sellersku', name: 'sellersku'},
                {data: 'item_group', name: 'item_group'},
                {data: 'item_no', name: 'item_no'},
                {data: 'from', name: 'from'},
                {
                    width: "100px",
                    data: 'status',
                    name: 'status'
                },
                {data: 'bg', name: 'bg'},
                {data: 'bu', name: 'bu'},
                {data: 'seller', name: 'seller'},
                {data: 'processor', name: 'processor', width: "120px"},
                {data: 'track_note', name: 'track_note'},
                {data: 'join_rsg', name: 'join_rsg',orderable: false,},
                {
                    width: "20px",
                    data: 'order_id',
                    name: 'order_id',
                    orderable: false,
                    render(data, type, row) {
                        return `<a class="btn btn-danger btn-xs" href="/nonctg/process?order_id=${data}&id=${row.id}" target="_blank">Process</a>`
                    }
                },
                {data: 'email_hidden', name: 'email_hidden'}
            ],
            ajax: {
                type: 'POST',
                url: "/nonctg/get"
            },
            lengthMenu: [
                [10, 50, 100],
                [10, 50, 100]
            ],
        })

        let users = @json($users) ;
        $theTable.closest('.table-scrollable').after(tplRender(bottomtoolbar, {users}))
        $(assignto).change(e => {

            $this = $(e.currentTarget)

            let processor = parseInt($this.val())
            if (isNaN(processor)) return

            let selectedRows = dtApi.rows({selected: true})

            let ctgRows = selectedRows.data().toArray().map(obj => [obj.id,obj.email])

            if (!ctgRows.length) {
                $this.val('')
                toastr.error('Please select some rows first !')
                return
            }

            postByJson('/nonctg/batchAssignTask', {processor, ctgRows}).then(arr => {
                for (let rowIndex of selectedRows[0]) {
                    // console.log(dtApi.cell(rowIndex, 9).data())
                    dtApi.cell(rowIndex, 9).data(arr[1]).draw()
                    // draw 之后，dt 自作主张，向服务器请求数据然后又更新一遍
                }

                toastr.success('Saved !')
                $this.val('')
                selectAll.checked = false

            }).catch(err => {
                toastr.error(err.message)
            })
        })

        let dtApi = $theTable.api();

        $("#export").click(function(){
            location.href='/nonctg/export?date_from='+$("#date_from").val()+'&date_to='+$("#date_to").val();
        });

        //批量发邮件
        $('#batch-send').click(function(){
            let selectedRows = dtApi.rows({selected: true})

            let ctgRows = selectedRows.data().toArray().map(obj => [obj.email_hidden])

            if (!ctgRows.length) {
                toastr.error('Please select some rows first !')
                return
            }
            var email = ctgRows.join(';');
            window.open('/send/create?to_address='+email,'_blank');
        })

    </script>

@endsection