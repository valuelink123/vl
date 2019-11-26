@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['CRM']])
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
        <div class="col-md-12" style="padding: 0px;margin-bottom: 30px;">

            @permission('crm-add')
            <a  data-toggle="modal" href="{{ url('crm/create')}}" target="_blank"><button id="sample_editable_1_2_new" class="btn sbold red"> Add New
                    <i class="fa fa-plus"></i>
                </button>
            </a>
            @endpermission
            @permission('crm-export')
            <button id="export" class="btn sbold blue"> Export
                <i class="fa fa-download"></i>
            </button>
            @endpermission

            @permission('compose')
            <div class="btn-group">
                <button id="batch-send" class="btn sbold blue"> Batch Send
                </button>
            </div>
            @endpermission

            <div class="btn-group " style="float:right;">
                <form action="{{url('/crm/import')}}" method="post" enctype="multipart/form-data">
                    <div class="col-md-12">
                        @permission('crm-import')
                        <div class="col-md-4"  >
                            <a href="{{ url('/crm/download')}}" >Import Template
                            </a>
                        </div>
                        <div class="col-md-6">
                            {{ csrf_field() }}
                            <input type="file" name="importFile"  style="width: 90%;"/>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn blue" id="data_search">Import</button>

                        </div>
                        @endpermission
                    </div>
                </form>
            </div>

        </div>
        <div class="portlet-body">
            <div class="table-toolbar" id="thetabletoolbar">
                <div class="row">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon">From</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date_from !!}" data-init-by-query="daterange.from" id="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">To</span>
                            <input class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date_to !!}" data-init-by-query="daterange.to" id="date_to" autocomplete="off"/>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">facebook_group</span>
                            <input id="facebook_group" class="form-control xform-autotrim" name="facebook_group" list="list-facebook_group" placeholder="Facebook Group" autocomplete="off" />
                            <datalist id="list-facebook_group">
                                @foreach(getFacebookGroup() as $id=>$name)
                                    <option value="{!! $id !!} | {!! $name !!}"></option>
                                @endforeach
                            </datalist>
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
                    <div class="col-md-2">
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
                            <span class="input-group-addon">Country</span>
                            <select multiple style="width:100%;" id="country" data-init-by-query="ins.country">
                                @foreach($countrys as $name)
                                    <option value="{!! $name !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">From</span>
                            <select multiple style="width:100%;" id="from" data-init-by-query="ins.from">
                                @foreach($froms as $name)
                                    <option value="{!! $name !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">Brand</span>
                            <select multiple style="width:100%" id="brand" data-init-by-query="ins.brand">
                                @foreach($brands as $name)
                                    <option value="{!! $name !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon">Email</span>
                            <input type="text" id="email" style="width:100%;height: 28px" name="email" value="">
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">Amazon Order Id</span>
                            <input type="text" id="amazon_order_id" style="width:100%;height: 28px" name="amazon_order_id" value="">
                        </div>
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
                        <th title="The customer ID is unique. If there is a connection between customer mailbox and order number, the default is the same customer">ID</th>
                        <th >Date</th>
                        <th title="Customer contact email, customer can have more than one email/phone, here the default display of the first">Email</th>
                        <th >Name</th>
                        <th title="Customer contact number, customer can have more than one email/phone, here the default display of the first">Phone</th>
                        <th >Country</th>
                        <th title="From is a source for customers to contact for the first time. Customers may contact through a variety of ways and means">From</th>
                        <th title="Brand is the source of customers' first purchase of products or consultation questions. They may purchase products of several brands. Please check the order information for details">Brand</th>
                        <th title="The number of times the client participates in CTG does not represent the success of the review">CTG</th>
                        <th title="The number of times the customer participates in RSG and the status is Complete">RSG</th>
                        <th title="The number of times to leave comments on the bad comment list as determined by the customer's email">Negative</th>
                        <th title="Data default to CTG retention status showing the sum of Yes and RSG retention, without excluding CTG retention">Positive</th>
                        <th >Order</th>
                        <th >BG</th>
                        <th >BU</th>
                        <th>Facebook Name</th>
                        <th>Group</th>
                        <th>Remark</th>
                        <th>Type</th>
                        <th >Processor</th>
                        <th >Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                @permission('review-batch-update')
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
                @endpermission
                <input id="search_str" type="hidden" name="search_str" value="">
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
                    email: email.value,
                    amazon_order_id: amazon_order_id.value,
                    facebook_group: facebook_group.value
                },
                ins: {
                    processor: $('#processor').val(),
                    bg: $('#bg').val(),
                    bu: $('#bu').val(),
                    from: $('#from').val(),
                    country: $('#country').val(),
                    brand: $('#brand').val(),
                }
            })

            history.replaceState(null, null, '?' + objectToQueryString(data.search))

            $('#search_str').val(objectToQueryString(data.search));
        })

        $theTable.dataTable({
            searching: false,
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
            "aoColumnDefs": [ { "bSortable": false, "aTargets": [21] }],
            columns: [
                {
                    width: "1px",
                    orderable: false,
                    defaultContent: '',
                    className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                },
                {data: 'id', name: 'id'},
                {data: 'date', name: 'date'},
                {data: 'email', name: 'email'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'country', name: 'country'},
                {data: 'from', name: 'from'},
                {data: 'brand', name: 'brand'},
                {data: 'times_ctg', name: 'times_ctg'},
                {data: 'times_rsg', name: 'times_rsg'},
                {data: 'times_negative_review', name: 'times_negative_review'},
                {data: 'times_positive_review', name: 'times_positive_review'},
                {data: 'order_num', name: 'order_num'},
                {data: 'bg', name: 'bg'},
                {data: 'bu', name: 'bu'},
                {data:'facebook_name',name:'facebook_name'},
                {data:'facebook_group',name:'facebook_group'},
                {data:'remark',name:'remark'},
                {data:'type', name:'type'},
                {data: 'processor', name: 'processor'},
                {data: 'action', name: 'action'},
            ],
            ajax: {
                type: 'POST',
                url: "/crm/get"
            }
        })

        let users = @json($users) ;
        $theTable.closest('.table-scrollable').after(tplRender(bottomtoolbar, {users}))
        $(assignto).change(e => {

            $this = $(e.currentTarget)

            let processor = parseInt($this.val())
            if (isNaN(processor)) return

            let selectedRows = dtApi.rows({selected: true})

            let ctgRows = selectedRows.data().toArray().map(obj => [obj.id])

            if (!ctgRows.length) {
                $this.val('')
                toastr.error('Please select some rows first !')
                return
            }

            postByJson('/crm/batchAssignTask', {processor, ctgRows}).then(arr => {
                // 向服务器请求数据然后刷新数据
                dtApi.cell(0, 18).data(arr[1]).draw()

                toastr.success('Saved !')
                $this.val('')
                selectAll.checked = false

            }).catch(err => {
                toastr.error(err.message)
            })
        })

        let dtApi = $theTable.api();
        $("#export").click(function(){
            location.href='/crm/export?date_from='+$("#date_from").val()+'&date_to='+$("#date_to").val();

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