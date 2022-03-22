@extends('layouts.layout')
@section('label', 'inventory cycle count')
@section('content')
    <link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
    <style>
        .dataTables_extended_wrapper .table.dataTable {
            margin: 0px !important;
        }

        table.dataTable thead th, table.dataTable thead td {
            padding: 10px 2px !important;
        }
        table.dataTable tbody th, table.dataTable tbody td {
            padding: 10px 2px;
        }
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th{
            padding:8px 5px;
            vertical-align:middle;
        }
        th,td,td>span {
            font-size:10px !important;
            text-align:center;
            font-family:Arial, Helvetica, sans-serif;}
        .progress-bar.green-sharp,.progress-bar.red-haze,.progress-bar.blue-sharp{
            color:#000 !important;
        }
        table{
            table-layout:fixed;
        }
        table .head{
            text-align:center;
            vertical-align:middle;
            background:#fff2cc;
            font-weight:bold;
        }
        td.strategy_s,td.keyword_s{
            text-overflow:ellipsis;
            -moz-text-overflow: ellipsis;
            overflow:hidden;
            white-space: nowrap;
        }
        .table-bordered, .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
            border: 1px solid #ccc;
        }
        .portlet.light {
            padding:0;
        }

        .table-head{padding-right:17px;background-color:#999;color:#000;}
        .table-body{width:100%; max-height:500px;overflow-y:scroll;}
        .table-head table,.table-body table{width:100%;}
        .table-body table tr:nth-child(2n+1){background-color:#f2f2f2;}
        .editable-input textarea.form-control {width:500px;font-size:12px;}

        #reason-form input,#reason-form select{
            height:34px;
        }
        #reason-form .one{
            margin-top: 18px;
        }
        #reason-form span{
            width:150px;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover tbl1">
                            <tr class="head" >
                                <th>盘点日期</th>
                                <th>SKU</th>
                                <th>工厂</th>
                                <th>库位</th>
                                <th>SAP库存数量</th>
                                <th>账面数量</th>
                                <th>未过账数量</th>
                                <th>实物数量</th>
                                <th>最初差异数量</th>
                                <th>最初差异率</th>
                                <th>处理后SAP数量</th>
                                <th>处理后账面数量</th>
                                <th>处理后未过账数量</th>
                                <th>处理后差异数量</th>
                                <th>处理后差异率</th>
                                <th>状态</th>
                            </tr>
                            <tbody>
                            <tr>
                                <td>{{$data['date']}}</td>
                                <td>{{$data['sku']}}</td>
                                <td>{{$data['factory']}}</td>
                                <td>{{$data['location']}}</td>
                                <td>{{$data['dispose_before_number']}}</td>
                                <td>{{$data['account_number']}}</td>
                                <td>{{$data['notaccount_number']}}</td>
                                <td>{{$data['actual_number']}}</td>
                                <td>{{$data['difference_before_number']}}</td>
                                <td>{{$data['difference_before_rate']}}</td>
                                <td>{{$data['dispose_after_number']}}</td>
                                <td>{{$data['dispose_after_account_number']}}</td>
                                <td>{{$data['dispose_after_notaccount_number']}}</td>
                                <td>{{$data['difference_after_number']}}</td>
                                <td>{{$data['difference_after_rate']}}</td>
                                <td>{{$data['status_name']}}</td>
                            </tr>
                            </tbody>
                        </table>

                        <table class="table table-striped table-bordered table-hover" style="margin-top: 50px;">
                            <div class="" style="margin-right: 18px;margin-bottom: 10px;float: right;">
                                <div class="input-group">
                                    <div class="btn-group pull-right" >
                                        @if($data['status']<4)
                                            <button id="add_reason" class="btn sbold blue">+</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <thead style="background-color: #A09898;font-weight:bold;">
                            <tr>
                                <th>序号</th>
                                <th>差异数量</th>
                                <th>原因类别</th>
                                <th>备注具体原因</th>
                                <th>处理方案</th>
                                <th>处理时间</th>
                                <th>处理人</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data['reason'] as $key=>$val)
                                <tr>
                                    <th>{{$key+1}}</th>
                                    <th>{{$val['number']}}</th>
                                    <th>{{$val['reason']}}</th>
                                    <th>{{$val['reason_remark']}}</th>
                                    <th>{{$val['solution']}}</th>
                                    <th>{{$val['dispose_time']}}</th>
                                    <th>{{$val['dispose_userid']}}</th>
                                    <th>{!! $val['action'] !!}</th>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
        <div id="add-reason-form" style="display:none;">
            <form id="reason-form" method="post">
                <input type="hidden"  name="id" value="{{$data['id']}}" style="width:150px;">
                <div class="col-md-12 one">
                    <div class="input-group">
                        <span class="input-group-addon">数量</span>
                        <input type="text" id="number"  name="number" value="" style="width:150px;">
                    </div>
                </div>
                <div class="col-md-12 one">
                    <div class="input-group">
                        <span class="input-group-addon">原因</span>
                        <select id="reason" name="reason" style="width:150px;">
                            @foreach($reason as $key=>$value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 one">
                    <div class="input-group">
                        <span class="input-group-addon">备注具体原因</span>
                        <input type="text" id="reason_remark"  name="reason_remark" value="" style="width:350px;">
                    </div>
                </div>
                <div class="col-md-12 one">
                    <div class="input-group">
                        <span class="input-group-addon">处理方案</span>
                        <input type="text" id="solution"  name="solution" value="" style="width:350px;">
                    </div>
                </div>
                <br/>
            </form>
        </div>
    </div>
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
    <script>
        $('table').on('click','.delete-action',function(){
            var reasonID = $(this).parent().attr('data-reasonID');
            var id = $(this).parent().attr('data-id');
            var r = confirm("真的确定删除吗？")
            if (r == true){
                $.ajax({
                    type: 'post',
                    url: '/inventoryCycleCount/deleteReason',
                    data: {reasonID:reasonID},
                    dataType:'json',
                    success: function(res) {
                        if(res.status>0){
                            window.location.href="/inventoryCycleCount/show?id="+id;
                        }else{
                            alert('失败');
                        }
                    }
                });
            }
        })

        // //点击添加差异原因
        $('#add_reason').click(function(){
            art.dialog({
                id: 'art_add_reason',
                title: '添加差异原因',
                width:'580px',
                content: document.getElementById('add-reason-form'),
                okVal: 'Submit',
                ok: function () {
                    var number = $('#number').val();
                    if(number>0){
                        this.title('In the submission…');
                        $.ajax({
                            type: 'post',
                            url: '/inventoryCycleCount/addReason',
                            data: $("#reason-form").serialize(),
                            dataType:'json',
                            success: function(res) {
                                if(res.status>0){
                                    window.location.reload();
                                }else{
                                    alert('添加失败');
                                }
                            }
                        });
                    }else{
                        alert('请填写数量');
                        return false;
                    }

                },
                cancel: true,
                cancelVal:'Cancel'
            });
        })

        //把状态改为已处理
        $('table').on('click','.edit-reason-action',function(){
            var reasonID = $(this).parent().attr('data-reasonID');
            var status = $(this).attr('data-after-status');
            $.ajax({
                type: 'post',
                url: '/inventoryCycleCount/editReason',
                data: {status:status,reasonID:reasonID},
                dataType:'json',
                success: function(res) {
                    if(res.status>0){
                        window.location.reload();
                        alert('成功');
                    }else{
                        alert('失败');
                    }

                }
            });
        })
    </script>
@endsection
