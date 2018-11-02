@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>[['CTG', '/ctg/list'], 'Process']])
@endsection
@section('content')

    <link rel="stylesheet" href="/js/SmartWizard/css/smart_wizard.min.css" />
    <link rel="stylesheet" href="/js/SmartWizard/css/smart_wizard_theme_arrows.min.css" />
    <script src="/js/SmartWizard/js/jquery.smartWizard.min.js"></script>

    @include('frank.common')

    <h1 class="page-title font-red-intense"> CTG Process
        <small></small>
    </h1>

    <div class="portlet light bordered">

        <div class="portlet-title">
            <div class="caption">
                <i class="icon-ghost font-dark"></i>
                <span class="caption-subject font-dark uppercase">CTG Order ID: 111-1111111-1111111</span>
            </div>
        </div>

        <div class="portlet-body">
            <div>
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">CTG Info</a></li>
                    <li role="presentation" class="active"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Process Step</a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane" id="home">...</div>
                    <div role="tabpanel" class="tab-pane active" id="profile">
                        <div id="smartwizard">
                            <ul>
                                <li><a href="#step-1">Step Title<br /><small>Step description</small></a></li>
                                <li><a href="#step-2">Step Title<br /><small>Step description</small></a></li>
                                <li><a href="#step-3">Step Title<br /><small>Step description</small></a></li>
                                <li><a href="#step-4">Step Title<br /><small>Step description</small></a></li>
                            </ul>

                            <div>
                                <div id="step-1" class="">
                                    Step Content 1
                                </div>
                                <div id="step-2" class="">
                                    Step Content 2
                                </div>
                                <div id="step-3" class="">
                                    Step Content 3
                                </div>
                                <div id="step-4" class="">
                                    Step Content 4
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){

            $('#smartwizard').smartWizard({
                theme: 'arrows'
            });

        });
    </script>

@endsection