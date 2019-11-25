@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>[['CRM', '/crm'], 'show']])
@endsection
@section('content')
    <style>
        .font-dark {
            color: #5888b9 !important;
        }
    </style>

    <link rel="stylesheet" href="/js/SmartWizard/css/smart_wizard.min.css"/>
    <link rel="stylesheet" href="/js/SmartWizard/css/smart_wizard_theme_arrows.min.css"/>
    <script src="/js/SmartWizard/js/jquery.smartWizard.min.js"></script>

    @include('frank.common')

    <h1 class="page-title font-red-intense"> CRM Show
        <small></small>
    </h1>

    <div class="portlet light bordered">

        <div class="portlet-body">
            <div>
                <ul class="nav nav-tabs" role="tablist" id="tabs">
                    <li role="presentation" class="active"><a href="#ctg-info" aria-controls="ctg-info" role="tab" data-toggle="tab">Contact Info</a></li>
                    <li role="presentation"><a href="#order-info" aria-controls="order-info" role="tab" data-toggle="tab">Amazon Order Info</a></li>
                    <li role="presentation"><a href="#email-history" aria-controls="email-history" role="tab" data-toggle="tab">Email History</a></li>
                </ul>

                <div class="tab-content">

                    <div role="tabpanel" class="tab-pane active" id="ctg-info">
                        <form class="row">
                            <div class="col-md-8">
                                @if($contactInfo)
                                @foreach ($contactInfo as $data)
                                    @if($data['name'])Name: {!! $data['name'] !!}<br/>@endif
                                    @if($data['email'])Email: {!! $data['email'] !!}<br/>@endif
                                    @if($data['phone'])Phone: {!! $data['phone'] !!}<br/>@endif
                                    @if($data['country'])Country: {!! $data['country'] !!}<br/>@endif
                                    @if($data['remark'])Remark: {!! $data['remark'] !!}<br/>@endif
                                    <hr>
                                @endforeach
                                @else
                                    <b>not find Contact</b>
                                @endif
                                <br/>
                            </div>
                        </form>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="order-info">
                        @include('nonctg.orderInfo')
                    </div>

                    <div role="tabpanel" class="tab-pane" id="email-history">
                        <div class="table-container">
                            @include('nonctg.emailList')
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript">

    </script>

@endsection
