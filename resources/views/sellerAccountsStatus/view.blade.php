@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Seller Accounts Status Record']])
@endsection
@section('content')
    @include('frank.common')
    <style>
        table th,table td{
            text-align:center;
        }
    </style>
    <div class="row">
        <div class="top portlet light">
            <div>
                <table class="table table-striped table-bordered" id="datatable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Site</th>
                        <th>Seller Id</th>
                        <th>Account Name</th>
                        <th>Account Status</th>
                        <th>Record Status</th>
                        <th>Remark</th>
                        <th>User</th>
                        <th>Create Date</th>
                        <th>Update Date</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $key=>$val)
                            <tr>
                                <td>{{$val['id']}}</td>
                                <td>{{$val['site']}}</td>
                                <td>{{$val['mws_seller_id']}}</td>
                                <td>{{$val['label']}}</td>
                                <td>{{$val['account_status']}}</td>
                                <td>{{$val['record_status']}}</td>
                                <td>{{$val['remark']}}</td>
                                <td>{{$val['user_name']}}</td>
                                <td>{{$val['created_at']}}</td>
                                <td>{{$val['updated_at']}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection