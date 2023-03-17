@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['CRM--CS Team']])
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
        /*.amazon_order_id{*/
        /*    width:200px !important;*/
        /*}*/
    </style>

    @include('frank.common')

    <div class="portlet light bordered">
        <div class="col-md-12" style="padding: 0px;margin-bottom: 30px;">

{{--            @permission('cs-crm-add')--}}
{{--            <a  data-toggle="modal" href="{{ url('cscrm/create')}}" target="_blank"><button id="sample_editable_1_2_new" class="btn sbold red"> Add New--}}
{{--                    <i class="fa fa-plus"></i>--}}
{{--                </button>--}}
{{--            </a>--}}
{{--            @endpermission--}}


{{--            @permission('compose')--}}
{{--            <div class="btn-group">--}}
{{--                <button id="batch-send" class="btn sbold blue"> Batch Send--}}
{{--                </button>--}}
{{--            </div>--}}
{{--            @endpermission--}}

{{--            <div class="btn-group " style="float:right;">--}}
{{--                <form action="{{url('/cscrm/import')}}" method="post" enctype="multipart/form-data">--}}
{{--                    <div class="col-md-12">--}}
{{--                        @permission('cs-crm-import')--}}
{{--                        <div class="col-md-4"  >--}}
{{--                            <a href="{{ url('/cscrm/download')}}" >Import Template--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-6">--}}
{{--                            {{ csrf_field() }}--}}
{{--                            <input type="file" name="importFile"  style="width: 90%;"/>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-2">--}}
{{--                            <button type="submit" class="btn blue" id="data_search">Import</button>--}}

{{--                        </div>--}}
{{--                        @endpermission--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}

        </div>
        <div class="portlet-body">
            <div class="table-toolbar" id="thetabletoolbar">
                <div class="row">
                    <div class="col-md-2">
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
                            <span class="input-group-addon">Question Type</span>
                            <select multiple style="width:100%;" id="linkage1" data-init-by-query="ins.linkage1">
                                @foreach($categoryData as $id=>$name)
                                    <option value="{!! $id !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
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
                        <br/>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Country</span>
                            <select multiple style="width:100%;" id="country" data-init-by-query="ins.country">
                                @foreach($countrys as $name)
                                    <option value="{!! $name !!}">{!! $name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Amazon Order Id</span>
                            <input type="text" id="amazon_order_id" style="width:100%;height: 28px" name="amazon_order_id" value="">
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

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Email</span>
                            <input type="text" id="email" style="width:100%;height: 28px" name="email" value="">
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">Item Group</span>
                            <input type="text" id="item_group" style="width:100%;height: 28px" name="item_group" value="">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Asin</span>
                            <input type="text" id="asin"  name="asin" value="">
                        </div>
                        <br/>
                        <div class="input-group">
                            <span class="input-group-addon">ItemNo</span>
                            <input type="text" id="item_no" name="item_no" value="">
                        </div>
                    </div>


                </div>
            </div>
            <div style="clear:both;height:50px; text-align: right;">

            </div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable" style="white-space: nowrap;">
                    <thead>
                    <tr>
                        <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                            <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>
                        </th>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>From</th>
                        <th>Gender</th>
                        <th>Facebook_name</th>

                        <th>Amazon order id</th>
                        <th>Brand</th>
                        <th>Review</th>
{{--                        <th>Status</th>--}}
                        <th>Question Type</th>
                        <th>Sku</th>
                        <th>Asin</th>
                        <th>Item no</th>
                        <th>Item group</th>
                        <th>BG</th>
                        <th>BU</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <input id="search_str" type="hidden" name="search_str" value="">
            </div>
        </div>
    </div>

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
                    asin: asin.value,
                    item_group: item_group.value,
                    item_no: item_no.value
                },
                ins: {
                    bg: $('#bg').val(),
                    bu: $('#bu').val(),
                    from: $('#from').val(),
                    country: $('#country').val(),
                    brand: $('#brand').val(),
                    linkage1:$('#linkage1').val(),
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
            select: {
                style: 'os',
                info: true, // info N rows selected
                // blurable: true, // unselect on blur
                selector: 'td:first-child', // 指定第一列可以点击选中
            },
            "ordering": false,
            columns: [
                {
                    width: "1px",
                    orderable: false,
                    defaultContent: '',
                    className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                },
                {data: 'id', name: 'id'},
                {data: 'date', name: 'date'},
                {data: 'encrypted_email', name: 'encrypted_email'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'country', name: 'country'},
                {data: 'from', name: 'from'},
                {data: 'gender', name: 'gender'},
                {data: 'facebook_name', name: 'facebook_name'},
                {data: 'amazon_order_id',width: "100px", name: 'amazon_order_id'},
                {data: 'brand', name: 'brand'},
                {data: 'review', name: 'review'},
                // {data: 'status', name: 'status'},
                {data: 'question_type', name: 'question_type'},
                {data: 'sku', name: 'sku'},
                {data: 'asin', name: 'asin'},
                {data:'item_no',name:'item_no'},
                {data:'item_group',name:'item_group'},
                {data: 'bg', name: 'bg'},
                {data: 'bu', name: 'bu'},
            ],
            ajax: {
                type: 'POST',
                url: "/cscrm/get"
            }
        })

        let dtApi = $theTable.api();
        $("#export").click(function(){
            location.href='/crm/export?date_from='+$("#date_from").val()+'&date_to='+$("#date_to").val();

        });
        $(thetabletoolbar).change(e => {
            dtApi.ajax.reload()
        })

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