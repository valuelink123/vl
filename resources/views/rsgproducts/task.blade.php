@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['rsgTask']])
@endsection
@section('content')

    <style>
        th,td{text-align:center;}
        table .special-content{
            background-color: #ED6B75;
            font-size: 20px !important;
        }
    </style>

    <link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
    <script src="/js/chosen/chosen.jquery.min.js"></script>

    @include('frank.common')

    <div id="switch-content">
        <div class="switch-one right-float">
            <div class="switch-type active" data-value="US">
                <div>United States</div>
            </div>
            <div class="triangle"></div>
        </div>

        <div class="switch-one right-float">
            <div class="switch-type" data-value="EU">
                <div>European</div>
            </div>
            <div class="triangle" style="display:none;"></div>
        </div>
        <div class="switch-one right-float">
            <div class="switch-type" data-value="JP">
                <div>Japanese</div>
            </div>
            <div class="triangle" style="display:none;"></div>
        </div>

    </div>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        {{--<th>Rank</th>--}}
                        {{--<th>Score</th>--}}
                        {{--<th>Weight Status</th>--}}
                        <th>Product</th>
                        <th>Site</th>
                        <th>Asin</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Item No</th>
                        <th>Level</th>
                        <th>SKU Status</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        {{--<th>BG</th>--}}
                        {{--<th>BU</th>--}}
                        <th>Seller</th>
                        <th title="The number of applications which have PayPal but haven't completed in the last 15 days">Unfinished</th>
                        <th>Target</th>
                        <th>Achieved</th>
                        <th class="special-content">Task</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $key=>$val)
                        <tr>
                            {{--<th>{!! $val['rank'] !!}</th>--}}
                            {{--<th>{!! $val['score'] !!}</th>--}}
                            {{--<th>{!! $val['order_status'] !!}</th>--}}
                            <th>{!! $val['product'] !!}</th>
                            <th>{!! $val['site'] !!}</th>
                            <th>{!! $val['asin'] !!}</th>
                            <th>{!! $val['type'] !!}</th>
                            <th>{!! $val['status'] !!}</th>
                            <th>{!! $val['item_no'] !!}</th>
                            <th>{!! $val['sku_level'] !!}</th>
                            <th>{!! $val['sku_status'] !!}</th>
                            <th>{!! $val['rating'] !!}</th>
                            <th>{!! $val['review'] !!}</th>
                            {{--<th>{!! $val['bg'] !!}</th>--}}
                            {{--<th>{!! $val['bu'] !!}</th>--}}
                            <th>{!! $val['seller'] !!}</th>
                            <th>{!! $val['unfinished'] !!}</th>
                            <th>{!! $val['target_review'] !!}</th>
                            <th>{!! $val['requested_review'] !!}</th>
                            <th class="special-content">{!! $val['task'] !!}</th>
                            <th>{!! $val['action'] !!}</th>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

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
        $(function() {
            $("#ajax").on("hidden.bs.modal",function(){
                $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
            });

            //点击表格头部切换栏切换站点数据
            $('#switch-content .switch-type').click(function(){
                $('#switch-content .switch-type').removeClass('active');
                $(this).addClass('active');
                $('.switch-one .triangle').hide();
                $(this).parent().find('.triangle').show();
                var value = $(this).attr('data-value');
                $.ajax({
                    type: 'post',
                    url: '/rsgtask',
                    data: {site:value},
                    dataType: 'json',
                    success: function(res) {
                        var html = '';
                        if(res.status==1){
                            var data = res.data;
                            $.each(data,function(key,val){
                                html += '<tr>';
                                html += '<th>' + val.product + '</th>';
                                html += '<th>' + val.site + '</th>';
                                html += '<th>' + val.asin + '</th>';
                                html += '<th>' + val.type + '</th>';
                                html += '<th>' + val.status + '</th>';
                                html += '<th>' + val.item_no + '</th>';
                                html += '<th>' + val.sku_level + '</th>';
                                html += '<th>' + val.sku_status + '</th>';
                                html += '<th>' + val.rating + '</th>';
                                html += '<th>' + val.review + '</th>';
                                html += '<th>' + val.seller + '</th>';
                                html += '<th>' + val.unfinished + '</th>';
                                html += '<th>' + val.target_review + '</th>';
                                html += '<th>' + val.requested_review + '</th>';
                                html += '<th class="special-content">' + val.task + '</th>';
                                html += '<th>' + val.action + '</th>';
                                html += '</tr>';
                            });
                        }else{
                            html = '<tr><th colspan="16">No Data</th></tr>';
                        }
                        $('#thetable tbody').html(html);
                    }
                });
            })
        });

    </script>

@endsection