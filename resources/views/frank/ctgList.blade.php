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
		.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}


th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
    </style>

    @include('frank.common')

    {{--<h1 class="page-title font-red-intense"><a href="{{url('ctg/list')}}"><button class="btn blue" style="width:9em;" type="button">CTG</button></a><a href="{{url('cb/list')}}"><button class="btn default" style="width:9em;" type="button">CashBack</button></a><a href="{{url('bg/list')}}"><button class="btn default" style="width:9em;" type="button">BuyOneGetOne</button></a>--}}
    {{--</h1>--}}

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar" id="thetabletoolbar">
                <div class="row">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! date('Y-m-d', strtotime('-7 day')) !!}" data-init-by-query="daterange.from" id="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">To</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! date('Y-m-d') !!}" data-init-by-query="daterange.to" id="date_to" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Expect Rating</span>
                            <select multiple style="width:100%;" id="rating" data-init-by-query="ins.rating">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select multiple style="width:100%;" id="bg" data-init-by-query="ins.bg">
                                @foreach($bgs as $bg)
                                    <option value="{!! $bg !!}">{!! $bg !!}</option>
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
                            <span class="input-group-addon">BU</span>
                            <select multiple style="width:100%;" id="bu" data-init-by-query="ins.bu">
                                @foreach($bus as $bu)
                                    <option value="{!! $bu !!}">{!! $bu !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Status</span>
                            <select multiple style="width:100%;" id="status" data-init-by-query="ins.status">
                                <option value="Confirm Review">Confirm Review</option>
                                <option value="Arrange Shipment">Arrange Shipment</option>
                                <option value="Delivery Confirmation">Delivery Confirmation</option>
                                <option value="Lead To Leave Review">Lead To Leave Review</option>
                                <option value="Re-SG">Re-SG</option>
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">Brand</span>
                            <select multiple style="width:100%;" id="brand" data-init-by-query="ins.brand">
                                @foreach($brands as $brand)
                                    <option value="{!! $brand !!}">{!! $brand !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Review ID</span>
                            <input  style="width:100%;height:29px;" id="review_id" data-init-by-query="ins.review_id" value="">
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Channel</span>
                            <select style="width:100%;height:29px;" id="channel" data-init-by-query="ins.channel">
                                @foreach($channel as $k=>$v)
                                    <option value="{!! $k !!}" @if($selchannel==$k) selected @endif>{!! $v !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
					@permission('ctg-export')
                    <div class="col-md-1">
                        <div class="btn-group " style="float:right;">

                            <button id="ctg-export" class="btn sbold blue"> Export
                                <i class="fa fa-download"></i>
                            </button>

                        </div>
                    </div>
					@endpermission
                    @permission('ctg-add')
                    <div class="btn-group" style="float:right;margin-top:10px;">
                        <a data-target="#ajax" data-toggle="modal" href="{{ url('ctg/create')}}">
                            <button id="ctg-add" class="btn sbold blue"> Add New
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>
                    </div>
                    @endpermission

                    @permission('compose')
                    <div class="btn-group" style="margin-top:10px;margin-left:15px;">
                        <button id="batch-send" class="btn sbold blue"> Batch Send
                        </button>
                    </div>
                    @endpermission

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
                        <th>Date</th>
                        <th>Email</th>
                        <th>Customer</th>
						<th>Order No</th>
                        <th>Item No</th>
                        <th>Item Name</th>
                        <th>Asin</th>
                        <th>Seller SKU</th>
                        <th>Brand</th>
                        <th>Item Group</th>
                        <th>Phone</th>
                        <th>Expect Rating</th>
                        <th>Reviewed</th>
                        <th>Tracking Note</th>
                        <th>Status</th>
                        <th>BG</th>
                        <th>BU</th>
                        <th>Channel</th>
                        <th>Facebook Name</th>
                        <th>Group</th>
                        <th>Processor</th>
                        <th>Join RSg</th>
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
    <input type="hidden" id="email" value="{!! $email !!}">
    <div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" >
                <div class="modal-body" >
                    <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
                    <span>Loading... </span>
                </div>
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
                // value: fuzzysearch.value,
                daterange: {
                    from: date_from.value,
                    to: date_to.value
                },
                ands: {
                    email: $('#email').val(),
                    // brand: brand.value,
                    // item_model: item_model.value
                },
                ins: {
                    rating: $('#rating').val(),
                    processor: $('#processor').val(),
                    status: $('#status').val(),
                    bg: $('#bg').val(),
                    bu: $('#bu').val(),
                    brand: $('#brand').val(),
                    review_id:$('#review_id').val(),
                    channel:$('#channel').val(),
                }
            })

            history.replaceState(null, null, '?' + objectToQueryString(data.search))
        })

        $theTable.dataTable({
            // searching: false,
            search: {search: queryStringToObject().value},
            serverSide: true,
            scrollX: 2000,
            fixedColumns:   {
						leftColumns:3,
						rightColumns: 3
					},
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
                    name: 'created_at',
                    render(data) {
                        return data.substr(0, 10)
                    }
                },
                {data: 'email', name: 'email'},
                {data: 'name', name: 'name'},
				{data: 'order_id', name: 'order_id'},
                {data: 'itemCodes', name: 'itemCodes'},
                {data: 'itemNames', name: 'itemNames', width: "200px"},
                {
                    data: 'asins',
                    name: 'asins',
                    render(data, type, row) {
                        if (!data) return ''
                        let asins = data.split(',')
                        return asins.map(asin => {
                            return `<a href="https://www.${row.SalesChannel}/dp/${asin}" target="_blank" rel="noreferrer">${asin}</a>`
                        }).join('<br/>')
                    }
                },
                {data: 'sellerskus', name: 'sellerskus'},
                {data: 'brands', name: 'brands'},
                {data: 'itemGroups', name: 'itemGroups', width: "80px"},
                {data: 'phone', name: 'phone'},
                {
                    width: "100px",
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
                        let steps = eval('(' + data.replace(/<[^>]+>/g,"") + ')');//JSON.parse(data.replace(/<[^>]+>/g,""))
						
                        let html = steps.track_notes[row.status]
						//alert(html);
                        if (!html) return ''
                        return html.trim().substr(0, 67)
                    }
                },
                {
                    width: "100px",
                    data: 'status',
                    name: 'status'
                },
                {data: 'bgs', name: 'bgs'},
                {data: 'bus', name: 'bus'},
                {data:'channel',name:'channel'},
                {data:'facebook_name',name:'facebook_name'},
                {data:'facebook_group',name:'facebook_group'},
                {data: 'processor', name: 'processor', width: "120px"},
                {data: 'join_rsg', name: 'join_rsg',orderable: false,},
                {
                    width: "20px",
                    data: 'order_id',
                    name: 'order_id',
                    orderable: false,
                    render(data, type, row) {
                        return `<a class="btn btn-danger btn-xs" href="/ctg/list/process?order_id=${data}&created_at=${encodeURIComponent(row.created_at)}&channel=${$('#channel').val()}" target="_blank">Process</a>`
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

            let ctgRows = selectedRows.data().toArray().map(obj => [obj.created_at, obj.order_id])

            if (!ctgRows.length) {
                $this.val('')
                toastr.error('Please select some rows first !')
                return
            }

            var channel = $('#channel').val();
            postByJson('/ctg/batchassigntask?channel='+channel, {processor, ctgRows}).then(arr => {
                for (let rowIndex of selectedRows[0]) {
                    // console.log(dtApi.cell(rowIndex, 9).data())
                    dtApi.cell(rowIndex, 19).data(arr[1]).draw()
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

//        $("#ctg-export").click(function(){
//            location.href='/ctg/export?asin_status='+(($("select[name='asin_status[]']").val())?$("select[name='asin_status[]']").val():'')+'&keywords='+$("input[name='keywords']").val()+'&date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&nextdate='+$("input[name='nextdate']").val()+'&follow_status='+(($("select[name='follow_status[]']").val())?$("select[name='follow_status[]']").val():'')+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&site='+(($("select[name='site[]']").val())?$("select[name='site[]']").val():'')+'&rating='+$("select[name='rating']").val()+'&bgbu='+$("select[name='bgbu']").val()+'&vp='+$('select[name="vp"]').val()+'&rc='+$('select[name="rc"]').val()+'&del='+$('select[name="del"]').val();
//        });

        $("#ctg-export").click(function(){
            var channel = $('#channel').val();
            location.href='/ctg/export?channel='+channel+'&date_from='+$("#date_from").val()+'&date_to='+$("#date_to").val();
        });

        //批量发邮件
        $('#batch-send').click(function(){
            let selectedRows = dtApi.rows({selected: true})

            let ctgRows = selectedRows.data().toArray().map(obj => [obj.email])

            if (!ctgRows.length) {
                toastr.error('Please select some rows first !')
                return
            }
            var email = ctgRows.join(';');
            window.open('/send/create?to_address='+email,'_blank');
        })
        
    </script>

@endsection