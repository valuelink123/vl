@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['rsgTask']])
@endsection
@section('content')

    <style>
        th,td{text-align:center;}
        table .special-content{
            background-color: #FF66FF;
            font-size: 20px !important;
        }
    </style>

    <link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
    <script src="/js/chosen/chosen.jquery.min.js"></script>

    @include('frank.common')

    <div class="portlet light bordered">
        <div class="portlet-body">
            </div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Score</th>
                        <th>Weight Status</th>
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
                        <th>BG</th>
                        <th>BU</th>
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
                            <th>{!! $val['rank'] !!}</th>
                            <th>{!! $val['score'] !!}</th>
                            <th>{!! $val['order_status'] !!}</th>
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
                            <th>{!! $val['bg'] !!}</th>
                            <th>{!! $val['bu'] !!}</th>
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
        });

    </script>

@endsection