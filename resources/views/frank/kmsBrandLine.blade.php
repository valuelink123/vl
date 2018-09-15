@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')
    <h1 class="page-title font-red-intense"> Product Guide
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">

                    <div class="col-md-8">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
                            <span> </span>

                            <input id="giveBrandLine" placeholder="Set Brand Line" class="table-group-action-input form-control input-inline input-small input-sm">
                            <button class="btn btn-sm green table-group-action-submit">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>


                    </div>
                    <div class="col-md-4">
                        <div class="btn-group " style="float:right;">
                            <button id="vl_list_export" class="btn sbold blue"> Export
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
                        <th>Item Group</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>User Manual</th>
                        <th>Video List</th>
                        <th>Q&A</th>
                        <th>Parts list</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        var arr = []
        for (var i = 0; i < 50; i++) {
            var line = []
            for (var j = 0; j < 7; j++) {
                line.push(Math.ceil(99999 * Math.random()))
            }
            arr.push(line)
        }
        $(thetable).dataTable({
            aaData: arr
        })
    </script>

@endsection