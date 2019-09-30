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
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
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
    <link href="/css/common.css?v=9" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="/js/artDialog4.1.7/artDialog.js?skin=blue"></script>
    <script type="text/javascript" src="/js/artDialog4.1.7/plugins/iframeTools.js"></script>
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
                                            <a href="/home"> Home
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
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
                                            </ul>
                                        </li>
										
										@permission('task-show')
										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="/task"> Task
												<span class="badge badge-danger" >{{intval($untasks)}}</span>
                                                <span class="arrow"></span>
                                            </a>
                                            
                                        </li>
										@endpermission
										
										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Listing
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('asin-rating-show')
                                                <li class="">
                                                    <a href="/star" class="nav-link nav-toggle ">
                                                        My Listing
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
                                            </ul>
                                        </li>
										
										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Review
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
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
												
                                            </ul>
                                        </li>
										
										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Inventory
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('productTransfer-show')
                                                <li class="">
                                                    <a href="{{ url('productTransfer') }}" class="nav-link nav-toggle ">
                                                       Product Transfer
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
                                            </ul>
                                        </li>
										



										
										
										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Email
                                                <span class="badge badge-danger" >{{intval(array_get($unreply,'Site',0)+array_get($unreply,'Amazon',0))}}</span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
												@permission('inbox-show')
												<li class="dropdown-submenu ">
                                                    <a href="javascript:;" class="nav-link nav-toggle ">
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
												@permission('data-statistics')
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
                                            </ul>
                                        </li>
										
										
										<li class="menu-dropdown classic-menu-dropdown ">
                                            <a href="javascript:;"> Setting
                                                <span class="arrow"></span>
                                            </a>
                                            <ul class="dropdown-menu pull-left">
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
                    <ul class="page-breadcrumb">
                        <li>
                            <a href="{{url('home')}}">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>@yield('label')</span>
                        </li>
                        @yield('crumb')
                    </ul>
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
    right: 10px;
    bottom: 50px;"> Task
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