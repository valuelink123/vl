<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="@yield('description')" name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/css/google.fonts.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
	<link href="/assets/global/plugins/bootstrap-colorpicker/css/colorpicker.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jquery-minicolors/jquery.minicolors.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/pages/css/pricing.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap-select.min.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="/assets/layouts/layout/css/layout.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/layouts/layout/css/themes/darkblue.min.css" rel="stylesheet" type="text/css" id="style_color" />
    <link href="/assets/layouts/layout/css/custom.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME LAYOUT STYLES -->
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
	<link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
	<link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="/assets/global/plugins/jquery-ui/jquery-ui.min.css">
    <!--[if lt IE 9]>
    <script src="/assets/global/plugins/respond.min.js"></script>
    <script src="/assets/global/plugins/excanvas.min.js"></script>
    <script src="/assets/global/plugins/ie8.fix.min.js"></script>
    <![endif]-->
    <!-- BEGIN CORE PLUGINS -->
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/jquery-repeater/jquery.repeater.js" type="text/javascript"></script>
    <!-- END CORE PLUGINS -->


    <script src="/assets/global/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
	<script src="/assets/pages/scripts/form-repeater.js?v=laji" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap-select.min.js" type="text/javascript"></script>

    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="/assets/global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
	<script src="/assets/pages/scripts/ui-modals.min.js" type="text/javascript"></script>

    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.js" type="text/javascript"></script>

    <!-- BEGIN THEME GLOBAL SCRIPTS -->
    <script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-minicolors/jquery.minicolors.min.js" type="text/javascript"></script>
    <!-- END THEME GLOBAL SCRIPTS -->
    <!-- BEGIN THEME LAYOUT SCRIPTS -->
    <script src="/assets/layouts/layout/scripts/layout.js" type="text/javascript"></script>
    <script src="/assets/layouts/layout/scripts/demo.min.js" type="text/javascript"></script>
    <script src="/assets/layouts/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>
    <script src="/assets/layouts/global/scripts/quick-nav.min.js" type="text/javascript"></script>
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="/assets/global/plugins/fancybox/source/jquery.fancybox.pack.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/vendor/load-image.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js" type="text/javascript"></script>

    <script src="/assets/pages/scripts/form-fileupload.js" type="text/javascript"></script>
	<script src="/assets/layouts/global/scripts/echarts.min.js" type="text/javascript"></script>

    <!-- END PAGE LEVEL SCRIPTS -->
    <link rel="shortcut icon" href="/favicon.ico" />
    <link href="/css/common.css?v=9.1" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="/js/artDialog4.1.7/artDialog.js?skin=blue"></script>
    <script type="text/javascript" src="/js/artDialog4.1.7/plugins/iframeTools.js"></script>

	<script type="text/javascript" src="/js/banner.js"></script>
	<link href="/assets/pages/css/select2.min.css" rel="stylesheet" type="text/css"/>
	<script src="/assets/pages/scripts/select2.js" type="text/javascript"></script>
	<!-- 日期插件bootstrap-daterangepicker -->
	<link href="/assets/pages/css/daterangepicker.css" rel="stylesheet" type="text/css"/>
	<script src="/assets/pages/scripts/moment.min.js" type="text/javascript"></script>
	<script src="/assets/pages/scripts/daterangepicker.js" type="text/javascript"></script>

</head>
<!-- END HEAD -->

<body class="page-header-fixed page-sidebar-closed-hide-logo page-container-bg-solid page-content-white">
<div class="page-wrapper">
    <!-- BEGIN HEADER -->
    <div class="page-header navbar navbar-fixed-top">


        <!-- BEGIN HEADER INNER -->
        <div class="page-header-inner">
            <!-- BEGIN LOGO -->
            <div class="page-logo">

                    <img src="/assets/layouts/layout/img/logo.png" alt="logo" class="logo-default" />

            </div>
            <!-- END LOGO -->

            <!-- BEGIN TOP NAVIGATION MENU -->
			<div class="hor-menu  ">
                                    <ul class="nav navbar-nav">

                                        <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="/{{(Auth::user()->seller_rules || Auth::user()->sap_seller_id)?'home':'service'}}"> Home
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												<li class="dropdown-submenu">
                                                    <a href="/{{(Auth::user()->seller_rules || Auth::user()->sap_seller_id)?'home':'service'}}" class="nav-link nav-toggle ">
                                                        Dashboard
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class=" ">
                                                            <a href="{{url('home')}}" class="nav-link "> Sales Dashboard </a>
                                                        </li>
                                                        <li class=" ">
                                                            <a href="{{url('service')}}" class="nav-link "> CS Dashboard </a>
                                                        </li>
                                                        <li class="">
                                                            <a href="/finance" class="nav-link nav-toggle ">
                                                                Finance Dashboard
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
												@permission('sales-report-show')
                                                <li class="">
                                                    <a href="/skus" class="nav-link nav-toggle ">
                                                        Daily Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('sales-management')
												<li class="">
                                                    <a href="/seller" class="nav-link nav-toggle ">
                                                        Sales Management
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('budgets-show')
												<li class="">
                                                    <a href="/budgets" class="nav-link nav-toggle ">
                                                        Sales Budget
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
						@endpermission
						@permission('budgets-sku-edit')
                                                <li class="">
                                                    <a href="/budgetSku" class="nav-link nav-toggle ">
                                                        Budget Skus
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
                                            </ul>
                                        </li>

                                        <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Sales
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
                                                <li class="dropdown-submenu">
                                                    <a href="javascript:;" class="nav-link nav-toggle ">
                                                        Amazon
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @permission('order-list-show')
                                                        <li class="">
                                                            <a href="/orderList" class="nav-link nav-toggle ">
                                                                Order List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                        @permission('refund-show')
                                                        <li class="">
                                                            <a href="/refund" class="nav-link nav-toggle ">
                                                                Refund List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                        @permission('return-show')
                                                        <li class="">
                                                            <a href="/return" class="nav-link nav-toggle ">
                                                                Return List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                        @permission('mcf-list-show')
                                                        <li class="">
                                                            <a href="/McfOrderList" class="nav-link nav-toggle ">
                                                                McfOrder List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                        @permission('return-analysis')
                                                        <li class="">
                                                            <a href="/returnAnalysis/returnAnalysis"
                                                               class="nav-link nav-toggle ">
                                                                Return analysis
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                        @permission('asin-analysis')
                                                        <li class="">s
                                                            <a href="/returnAnalysis/asinAnalysis"
                                                               class="nav-link nav-toggle ">
                                                                Asin Return analysis
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                        @permission('sku-analysis')
                                                        <li class="">
                                                            <a href="/returnAnalysis/skuAnalysis"
                                                               class="nav-link nav-toggle ">
                                                                SKU Return analysis
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        @endpermission
                                                    </ul>
                                                </li>
                                                <li class="dropdown-submenu">
                                                    <a href="javascript:;" class="nav-link nav-toggle ">
                                                        eBay
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="">
                                                            <a href="/ebayOrderList" class="nav-link nav-toggle ">
                                                                Order List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        <li class="">
                                                            <a href="/ebayOrderList/skuMatchList" class="nav-link nav-toggle ">
                                                                SKU Match List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="dropdown-submenu">
                                                    <a href="javascript:;" class="nav-link nav-toggle ">
                                                        Joybuy
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="">
                                                            <a href="/joybuyOrderList" class="nav-link nav-toggle ">
                                                                Order List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        <li class="">
                                                            <a href="/joybuyOrderList/skuMatchList" class="nav-link nav-toggle ">
                                                                SKU Match List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="dropdown-submenu">
                                                    <a href="javascript:;" class="nav-link nav-toggle ">
                                                        Newegg
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="">
                                                            <a href="/neweggOrderList" class="nav-link nav-toggle ">
                                                                Order List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        <li class="">
                                                            <a href="/neweggOrderList/skuMatchList" class="nav-link nav-toggle ">
                                                                SKU Match List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="dropdown-submenu">
                                                    <a href="javascript:;" class="nav-link nav-toggle ">
                                                        Rakuten
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="">
                                                            <a href="/letianOrderList" class="nav-link nav-toggle ">
                                                                Order List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                        <li class="">
                                                            <a href="/letianOrderList/skuMatchList" class="nav-link nav-toggle ">
                                                                SKU Match List
                                                                <span class="arrow"></span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>

										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Listing
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
{{--												<li class="">--}}
{{--                                                    <a href="/home/asins" class="nav-link nav-toggle ">--}}
{{--                                                        My Listing--}}
{{--                                                        <span class="arrow"></span>--}}
{{--                                                    </a>--}}
{{--                                                </li>--}}
												@permission('asin-rating-show')
												<li class="">
												    <a href="/star" class="nav-link nav-toggle ">
												        Rating Table
												        <span class="arrow"></span>
												    </a>
												</li>
												@endpermission
                                                @permission('reselling-show')
												<li class="">
												    <a href="/hijack/index" class="nav-link nav-toggle ">
                                                        Asin Reselling
														<span class="arrow"></span>
												    </a>
												</li>
                                                @endpermission
												@permission('asin-table-show')
												<li class="">
												    <a href="/asin" class="nav-link nav-toggle ">
												        Asin Table
												        <span class="arrow"></span>
												    </a>
												</li>
												 @endpermission

                                                 @permission('skuforuser-show')
												<li class="">
												    <a href="/skuforuser" class="nav-link nav-toggle ">
												        Skus For Users
												        <span class="arrow"></span>
												    </a>
												</li>
												 @endpermission
                                                @permission('asin-match-relation-show')
                                                <li class="">
                                                    <a href="/asinMatchRelation" class="nav-link nav-toggle ">
                                                        Asin Match Relation
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                            </ul>
                                        </li>
                                        <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Financy
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
                                                <li class="">
                                                    <a href="/settlement" class="nav-link nav-toggle ">
                                                        Amazon Settlement
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Review
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">

												@permission('rsgproducts-rsgtask')
												<li class="">
													<a href="/rsgtask" class="nav-link nav-toggle ">
														RSG Task
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
												@permission('rsgrequests-show')
												<li class="">
													<a href="/rsgrequests" class="nav-link nav-toggle ">
														RSG
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
												@permission('rsgproducts-show')
												<li class="">
													<a href="/rsgproducts" class="nav-link nav-toggle ">
														RSG Product
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
                                                @permission('review-show')
												<li class="">
                                                    <a href="/review" class="nav-link nav-toggle ">
                                                        Review Table
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('ctg-show')
												<li class="">
                                                    <a href="/ctg/list" class="nav-link nav-toggle ">
                                                        CTG
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('non-ctg-show')
												<li class="">
                                                    <a href="/nonctg" class="nav-link nav-toggle ">
                                                        Non-CTG
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('crm-show')
												<li class="">
                                                    <a href="/crm" class="nav-link nav-toggle ">
                                                        CRM
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission

												@permission('rsgUser-show')
												<li class="">
													<a href="/rsgUser/list" class="nav-link nav-toggle ">
														RSG User
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
												@permission('reqrev-show')
												<li class="">
                                                    <a href="/reqrev" class="nav-link nav-toggle ">
                                                        Auto Request Reviews
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												 @endpermission

                                                <li class="">
                                                    <a href="/accountStocklist" class="nav-link nav-toggle ">
                                                        Account Stocklist
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Marketing
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('fee-split-show')
                                                <li class="">
                                                    <a href="/fees" class="nav-link nav-toggle ">
                                                        Fees List
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('fee-split-show')
												<li class="">
												    <a href="/marketingPlan/index" class="nav-link nav-toggle ">
												        Marketing Plan
												        <span class="arrow"></span>
												    </a>
												</li>
												@endpermission
                                                @permission('cuckoo-show')
                                                <li class="">
                                                    <a href="/cuckoo/show" class="nav-link nav-toggle" target="_blank">
                                                        Cuckoo
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission

                                            </ul>
                                        </li>

										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Inventory
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('sales-forecast-show')
												<li class="">
                                                    <a href="/mrp/list" class="nav-link nav-toggle ">
                                                        Sales Forecast-22W
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('plans-forecast-show')
												<li class="">
													<a href="/plansforecast/list" class="nav-link nav-toggle ">
														Plans Forecast-22W
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
												<li class="">
                                                    <a href="/mrp" class="nav-link nav-toggle ">
                                                        Inventory Monitor
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@permission('productTransfer-show')
                                                <li class="">
                                                    <a href="{{ url('productTransfer') }}" class="nav-link nav-toggle ">
                                                       Product Transfer
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission

												@permission('productTransfer-reply')
												<li class="">
													<a href="{{ url('productTransfer/replyList') }}" class="nav-link nav-toggle ">
														Transfer ReplyList
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission

												@permission('distribution-analysis-show')
												<li class="">
                                                    <a href="/tran" class="nav-link nav-toggle ">
                                                        Distribution analysis
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('partslist-show')
												<li class="">
                                                    <a href="/kms/partslist" class="nav-link nav-toggle ">
                                                        Inventory Inquiry
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												{{--<li class="">--}}
												    {{--<a href="/collaborativeReplenishment/index" class="nav-link nav-toggle ">--}}
												        {{--Collaborative Replenishment--}}
												        {{--<span class="arrow"></span>--}}
												    {{--</a>--}}
												{{--</li>--}}
												<li class="dropdown-submenu ">
												    <a href="" class="nav-link nav-toggle ">
														Manage Distribute Time
												       <span class="arrow"></span>
												    </a>
												    <ul class="dropdown-menu">
												        <li class=" ">
												            <a href="{{url('manageDistributeTime/safetyStockDays')}}" class="nav-link "> Safety Stock days </a>
												        </li>
												        <li class=" ">
												            <a href="{{url('manageDistributeTime/fba')}}" class="nav-link "> FBA-FC Transfer time </a>
												        </li>
														<li class=" ">
														    <a href="{{url('manageDistributeTime/fbm')}}" class="nav-link "> FBM-FBA Transfer time </a>
														</li>
														<li class=" ">
														    <a href="{{url('manageDistributeTime/internationalTransportTime')}}" class="nav-link "> International transport Time </a>
														</li>
												    </ul>
												</li>

												<li class="">
												    <a href="/cpfr/index" class="nav-link nav-toggle ">
												        CPFR协同补货
												        <span class="arrow"></span>
												    </a>
												</li>
                                                @permission('inventory-cycle-count-show')
                                                <li class="">
                                                    <a href="/inventoryCycleCount" class="nav-link nav-toggle ">
                                                        库存盘点
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                            </ul>
                                        </li>



										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Price
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
                                                @permission('price-model')
												<li class="">
                                                    <a href="/price" class="nav-link nav-toggle ">
                                                        Price Model
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('auto-price-show')
												<li class="">
                                                    <a href="/autoprice" class="nav-link nav-toggle ">
                                                        Automatic price adjustment
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
                                                @permission('roi-show')
                                                <li class="">
                                                    <a href="/roi" class="nav-link nav-toggle ">
                                                        ROI Analysis
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission

                                                @permission('roi-performance-show')
                                                <li class="">
                                                    <a href="/roiPerformance" class="nav-link nav-toggle ">
                                                        Roi Performance Analysis
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                                
                                            </ul>
                                        </li>






										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Email
                                                <span class="badge badge-danger" >{{intval(array_get($unreply,'Site',0)+array_get($unreply,'Amazon',0))}}</span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('inbox-show')
												<li class="dropdown-submenu ">
                                                    <a href="{{url('inbox')}}"" class="nav-link nav-toggle ">
                                                        Inbox
														<span class="badge badge-danger" style="margin-right:20px;">{{intval(array_get($unreply,'Site',0)+array_get($unreply,'Amazon',0))}}</span>
                                                        <span class="arrow"></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class=" ">
                                                            <a href="{{url('inbox/filter/Site')}}" class="nav-link "> Site  <span class="badge badge-danger" >{{intval(array_get($unreply,'Site',0))}}</span></a>
                                                        </li>
                                                        <li class=" ">
                                                            <a href="{{url('inbox/filter/Amazon')}}" class="nav-link "> Amazon  <span class="badge badge-danger" >{{intval(array_get($unreply,'Amazon',0))}}</span></a>
                                                        </li>
                                                    </ul>
                                                </li>
												@endpermission
												@permission('sendbox-show')
                                                <li class="">
                                                    <a href="/send" class="nav-link nav-toggle ">
                                                        Send Box
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('templates-show')
												<li class="">
                                                    <a href="/template" class="nav-link nav-toggle ">
                                                        Template
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('callmessage-show')
												<li class="">
                                                    <a href="/phone" class="nav-link nav-toggle ">
                                                        Call Message
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('compose')
												<li class="">
                                                    <a href="/send/create" class="nav-link nav-toggle ">
                                                        Compose
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission

												@permission('auto-reply-show')
												<li class="">
                                                    <a href="/auto" class="nav-link nav-toggle ">
                                                        Auto Reply
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>

												@endpermission

                                            </ul>
                                        </li>

										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Tools
                                                <span class="arrow"></span>
                                            </a>
											<ul class="dropdown-menu pull-left">
                                            @permission('exception-show')
											<li class="">
												<a href="/exception" class="nav-link nav-toggle ">
													Refund & Replacement
													<span class="arrow"></span>
												</a>
											</li>
											@endpermission
                                            @permission('gift-card-show')
											<li class="">
												<a href="/giftcard" class="nav-link nav-toggle ">
													Gift Card
													<span class="arrow"></span>
												</a>
											</li>
											@endpermission

											@permission('mcforders')
											<li class="">
												<a href="/mcforder" class="nav-link nav-toggle ">
													Mcf Order
													<span class="arrow"></span>
												</a>
											</li>
											@endpermission


											@permission('sales-prediction-show')
											<li class="">
												<a href="/salesp" class="nav-link nav-toggle ">
													Sales Prediction
													<span class="arrow"></span>
												</a>
											</li>

											@endpermission


											@permission('proline-show')
											<li class="">
												<a href="/proline" class="nav-link nav-toggle ">
													Proline Report
													<span class="arrow"></span>
												</a>
											</li>

											@endpermission

                                            @permission('barcode-show')
                                            <li class="">
                                                <a href="/barcode" class="nav-link nav-toggle">
                                                    Barcode
                                                    <span class="arrow"></span>
                                                </a>
                                            </li>
                                            @endpermission

                                            @permission('barcode-qc')
                                            <li class="">
                                                <a href="/barcode/qc?p=ec93a64741" class="nav-link nav-toggle">
                                                    Barcode-QC
                                                    <span class="arrow"></span>
                                                </a>
                                            </li>
                                            @endpermission

                                            @permission('adv-show')
                                            <li class="">
                                                <a href="/adv" class="nav-link nav-toggle">
                                                    Adv manage
                                                    <span class="arrow"></span>
                                                </a>
                                            </li>
                                            @endpermission

											</ul>
                                        </li>

									   <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> KMS
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('product-guide-show')
                                                <li class="">
                                                    <a href="/kms/productguide" class="nav-link nav-toggle ">
                                                        Product Guide
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('user-manual-show')
												<li class="">
                                                    <a href="/kms/usermanual" class="nav-link nav-toggle ">
                                                        User Manual
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('video-show')
												<li class="">
                                                    <a href="/kms/videolist" class="nav-link nav-toggle ">
                                                        Video List
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('qa-show')
												<li class="">
                                                    <a href="/qa" class="nav-link nav-toggle ">
                                                        Knowledge Base
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('qa-show')
												<li class="">
                                                    <a href="/question" class="nav-link nav-toggle ">
                                                        Knowledge Center
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('qa-category-show')
												<li class="">
                                                    <a href="/category" class="nav-link nav-toggle ">
                                                        Knowledge Category
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission

												@permission('learn-center')
												<li class="">
                                                    <a href="/kms/learn" class="nav-link nav-toggle ">
                                                        Learning Center
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>

												@endpermission

												@permission('notice-center')
												<li class="">
                                                    <a href="/kms/notice" class="nav-link nav-toggle ">
                                                        Notice Center
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>

												@endpermission
                                            </ul>
                                        </li>


									   <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Report
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('data-statistics-show')
                                                <li class="">
                                                    <a href="/total" class="nav-link nav-toggle ">
                                                        Data Statistics
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('product-problem-show')
												<li class="">
                                                    <a href="/etotal" class="nav-link nav-toggle ">
                                                        Product Problem Statistics
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('requestreport-show')
												<li class="">
                                                    <a href="/rr" class="nav-link nav-toggle ">
                                                        Request Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
                                                @permission('reports-show')
												<li class="">
                                                    <a href="/reports" class="nav-link nav-toggle ">
                                                        Fba Amazon Fulfilled Inventory Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/reports?type=fba_daily_inventory_history_report" class="nav-link nav-toggle ">
                                                        Fba Daily Inventory History Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/reports?type=fba_inventory_adjustments_report" class="nav-link nav-toggle ">
                                                        Fba Inventory Adjustments Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/reports?type=fba_monthly_inventory_history_report" class="nav-link nav-toggle ">
                                                        Fba Monthly Inventory History Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/reports?type=fba_manage_inventory" class="nav-link nav-toggle ">
                                                        Fba Manage Inventory
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/reports?type=amazon_settlements" class="nav-link nav-toggle ">
                                                        Settlements Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/reports?type=amazon_settlement_details" class="nav-link nav-toggle ">
                                                        Settlements Details Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission

                                                @permission('amazon-fulfilled-shipments-report')
                                                <li class="">
                                                    <a href="/amazonFulfiledShipments" class="nav-link nav-toggle ">
                                                        Amazon fulfilled Shipments Report
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                            </ul>
                                        </li>


										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Setting
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												<li class="">
												    <a href="/management" class="nav-link nav-toggle ">
												        management
												        <span class="arrow"></span>
												    </a>
												</li>
												@permission('users-show')
                                                <li class="">
                                                    <a href="/user" class="nav-link nav-toggle ">
                                                        User Manage
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('role-show')
												<li class="">
                                                    <a href="/role" class="nav-link nav-toggle ">
                                                        Role Manage
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('group-show')
												<li class="">
                                                    <a href="/group" class="nav-link nav-toggle ">
                                                        Group Manage
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('rule-show')
												<li class="">
                                                    <a href="/rule" class="nav-link nav-toggle ">
                                                        Match Rules
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('accounts-show')
												<li class="">
                                                    <a href="/account" class="nav-link nav-toggle ">
                                                        Seller Account Manage
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('seller-tab-show')
												<li class="">
                                                    <a href="/sellertab" class="nav-link nav-toggle ">
                                                        Seller Tab Config
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
												@permission('review-tab-show')
												<li class="">
                                                    <a href="/rs" class="nav-link nav-toggle ">
                                                        Review Step Config
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
												@endpermission
                                                @permission('config-option-show')
                                                <li class="">
                                                    <a href="/config_option" class="nav-link nav-toggle ">
                                                        Config Options
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                                @permission('seller-accounts-status-show')
                                                <li class="">
                                                    <a href="/sellerAccountsStatus" class="nav-link nav-toggle ">
                                                        Seller Accounts Status
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission

                                            </ul>
                                        </li>

										<li class="menu-dropdown classic-menu-dropdown ">
											<a href="javascript:;"> CCP
												<span class="arrow"></span>
											</a>
											<ul class="dropdown-menu pull-left">

												@permission('ccp-show')
												<li class="">
													<a href="/ccp" class="nav-link nav-toggle ">
														CCP
														<span class="arrow"></span>
													</a>
												</li>
												<li class="">
													<a href="/ccp/salesboard" class="nav-link nav-toggle ">
														CCP环比数据分析
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
                                                @permission('ccp-ad-campaign-show')
                                                <li class="">
                                                    <a href="/ccp/adCampaign" class="nav-link nav-toggle ">
                                                        AD Campaign
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                                @permission('ccp-ad-group-show')
                                                <li class="">
                                                    <a href="/ccp/adGroup" class="nav-link nav-toggle ">
                                                        AD Group
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                                @permission('ccp-ad-keyword-show')
                                                <li class="">
                                                    <a href="/ccp/adKeyword" class="nav-link nav-toggle ">
                                                        AD Keyword
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                                @permission('ccp-ad-product-show')
                                                <li class="">
                                                    <a href="/ccp/adProduct" class="nav-link nav-toggle ">
                                                        AD Product
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
                                                @permission('ccp-ad-target-show')
                                                <li class="">
                                                    <a href="/ccp/adTarget" class="nav-link nav-toggle ">
                                                        AD Target
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission
											</ul>
										</li>

										<li class="menu-dropdown classic-menu-dropdown ">
											<a href="javascript:;"> EDM
												<span class="arrow"></span>
											</a>
											<ul class="dropdown-menu pull-left">

												@permission('edm-tag-show')
												<li class="">
													<a href="/edm/tag" class="nav-link nav-toggle ">
														Tag
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission

												@permission('edm-customers-show')
												<li class="">
													<a href="/edm/customers" class="nav-link nav-toggle ">
														Customers Info
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
												@permission('edm-template-show')
												<li class="">
													<a href="/edm/template" class="nav-link nav-toggle ">
														Template
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
												@permission('edm-campaign-show')
												<li class="">
													<a href="/edm/campaign" class="nav-link nav-toggle ">
														Campaign
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
											</ul>
										</li>

										<li class="menu-dropdown classic-menu-dropdown ">
											<a href="javascript:;"> CPFR协同补货
												<span class="arrow"></span>
											</a>
											<ul class="dropdown-menu pull-left">

												@permission('transfer-request-show')
												<li class="">
													<a href="/transfer/request/list" class="nav-link nav-toggle ">
														Transfer Request
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
                                                @permission('transfer-plan-show')
												<li class="">
													<a href="/transferPlan" class="nav-link nav-toggle ">
														Transfer Plan
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
                                                @permission('transfer-task-show')
												<li class="">
													<a href="/transferTask" class="nav-link nav-toggle ">
														Transfer Task
														<span class="arrow"></span>
													</a>
												</li>
												@endpermission
											</ul>
										</li>
                                        <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> RSG
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
                                                <li class="">
                                                    <a href="/shopsaver" class="nav-link nav-toggle">
                                                        ShopSaver Products
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/shopsaver/users" class="nav-link nav-toggle">
                                                        ShopSaver Clients
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="/shopsaver/orderList" class="nav-link nav-toggle">
                                                        ShopSaver Order List
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> System
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
                                                <li class="">
                                                    <a href="/plugin/download" class="nav-link nav-toggle">
                                                        Plugin Download
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>

                                                @permission('It-requirement-show')
                                                <li class="">
                                                    <a href="/system/itRequirement" class="nav-link nav-toggle" target="_blank">
                                                        IT Requirement
                                                        <span class="arrow"></span>
                                                    </a>
                                                </li>
                                                @endpermission

                                            </ul>
                                        </li>

                                    </ul>
                                </div>
            <div class="top-menu">

                <ul class="nav navbar-nav pull-right">

                    <li class="dropdown dropdown-user">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <img alt="" class="img-circle" src="/assets/layouts/layout/img/avatar.png" />

                            <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-default">

                            <li>
                                <a href="{{ url('profile') }}">
                                    <i class="icon-user"></i> My Profile </a>
                            </li>
                            <!--<li>
                                <a href="{{ url('account') }}">
                                    <i class="icon-lock"></i> Account Setting </a>
                            </li>-->
                            <li>
                                <a href="{{ route('logout') }}" onClick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <i class="icon-key"></i> Log Out </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- END TOP NAVIGATION MENU -->
        </div>
        <!-- END HEADER INNER -->
    </div>
    <!-- END HEADER -->
    <!-- BEGIN HEADER & CONTENT DIVIDER -->
    <div class="clearfix"> </div>
    <!-- END HEADER & CONTENT DIVIDER -->
    <!-- BEGIN CONTAINER -->

    <div class="page-container">
        <!-- BEGIN SIDEBAR -->

        <!-- END SIDEBAR -->
        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <!-- BEGIN CONTENT BODY -->
            <div class="page-content">

                <div class="page-bar">

					{{--轮播图--}}
					{{--<div class="slider" id="slider">--}}
						{{--<div class="slider-inner">--}}
							{{--<div class="item">--}}
								{{--<img class="img" style="background: url('/image/slide_1.jpg');">--}}
							{{--</div>--}}
							{{--<div class="item">--}}
								{{--<img class="img" style="background: url('/image/slide_2.jpg');">--}}
							{{--</div>--}}
						{{--</div>--}}
					{{--</div>--}}

					{{--倒计时开始--}}
					{{--<div class="mod-holiday—countdown">--}}
						{{--<div class="holiday—countdown text-center">--}}
							{{--<div style=" width:5%;border: 1px solid #D9EDF7;"></div>--}}
							{{--@foreach(Session::get('countDown') as $key=>$val)--}}
								{{--@if($key < 3)--}}
								{{--<div class="col-md-4">距离<span class="holiday">{!! $val['name'] !!}</span>还有<span class="day">{!! $val['day'] !!}</span>天</div>--}}
								{{--@endif--}}
							{{--@endforeach--}}
							{{--<div style=" width:5%;border: 1px solid #D9EDF7;"></div>--}}
							{{--<div style="clear:both;"></div>--}}
						{{--</div>--}}
					{{--</div>--}}
					{{--倒计时结束--}}
					<div>
                    <ul class="page-breadcrumb" style="margin-left:20px;">
                        <li>
                            <a href="{{url((Auth::user()->seller_rules || Auth::user()->sap_seller_id)?'home':'service')}}">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>@yield('label')</span>
                        </li>
                        @yield('crumb')
                    </ul>
					</div>
                </div>

                @yield('content')
            </div>
            <!-- END CONTENT BODY -->
        </div>
        <!-- END CONTENT -->
    </div>
    <!-- END CONTAINER -->
    <!-- BEGIN FOOTER -->
	@permission('task-create')
	<a data-target="#global_task_ajax" data-toggle="modal" href="{{ url('task/create')}}" class="btn btn-circle btn-lg green" style="position: fixed;
    right: 70px;
    bottom: 5px;"> Task
		<i class="fa fa-plus"></i>
	</a>

	@endpermission
    <div class="page-footer">
        <div class="page-footer-inner"> 2018 © Valuelink Ltd.
        </div>

        <div class="scroll-to-top">
            <i class="icon-arrow-up"></i>
        </div>
    </div>
    <!-- END FOOTER -->
</div>

<!-- END QUICK NAV -->

<script>
    $(function() {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-bottom-right",
            "onclick": null,
            "showDuration": "1000",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        @if(Session::has('success_message'))
            toastr.success("{{Session::get('success_message')}}");
            {{Session::forget('success_message')}}
        @endif

        @if(Session::has('error_message'))
            toastr.error("{{Session::get('error_message')}}");
            {{Session::forget('error_message')}}
        @endif
    });

    jQuery(document).ready(function($) {
        $('#slider').Slider();
    });
</script>

<div class="modal fade bs-modal-lg" id="global_task_ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" >
				<img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
				<span>Loading... </span>
			</div>
		</div>
	</div>
</div>

<!-- END THEME LAYOUT SCRIPTS -->
</body>

</html>
