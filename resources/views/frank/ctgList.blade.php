@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['CTG']])
@endsection
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> CTG List
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
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Customer Email</th>
                        <th>Phone Number</th>
                        <th>Rating</th>
                        <th>Commented</th>
                        <th>Status</th>
                        <th>Processor</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>

        let $theTable = $(thetable)

        $theTable.dataTable({
            search: {search: queryStringToObject().search},
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'rating', name: 'rating'},
                {data: 'rating', name: 'rating'},
                {data: 'rating', name: 'rating'},
                {data: 'rating', name: 'rating'},
                {data: 'rating', name: 'rating'}
            ],
            ajax: {
                type: 'POST',
                url: location.href
            }
        })

    </script>

@endsection