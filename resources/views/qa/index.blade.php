<!DOCTYPE html>
<!-- 
Template Name: Metronic - Responsive Admin Dashboard Template build with Twitter Bootstrap 3.3.7
Version: 4.7
Author: KeenThemes
Website: http://www.keenthemes.com/
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
Purchase: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
Renew Support: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->

    <head>
        <meta charset="utf-8" />
        <title>Questions And Solutions</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="#1 selling multi-purpose bootstrap admin theme sold in themeforest marketplace packed with angularjs, material design, rtl support with over thausands of templates and ui elements and plugins to power any type of web applications including saas and admin dashboards. Preview page of Theme #1 for blog listing page"
            name="description" />
        <meta content="" name="author" />
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
        <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
        <!-- END GLOBAL MANDATORY STYLES -->
        <!-- BEGIN THEME GLOBAL STYLES -->
        <link href="/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
        <link href="/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
        <!-- END THEME GLOBAL STYLES -->
        <!-- BEGIN PAGE LEVEL STYLES -->
        <link href="/assets/pages/css/blog.min.css" rel="stylesheet" type="text/css" />
		<link href="/assets/pages/css/search.min.css" rel="stylesheet" type="text/css">
        <!-- END PAGE LEVEL STYLES -->
        <!-- BEGIN THEME LAYOUT STYLES -->
        <link href="/assets/layouts/layout/css/layout.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/layouts/layout/css/themes/darkblue.min.css" rel="stylesheet" type="text/css" id="style_color" />
        <link href="/assets/layouts/layout/css/custom.min.css" rel="stylesheet" type="text/css" />
        <!-- END THEME LAYOUT STYLES -->
        <link rel="shortcut icon" href="favicon.ico" /> </head>
    <!-- END HEAD -->

    <body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white" style="background:#eef1f5;">

	<div class="blog-page">

	<div class="search-page">
	<div class="search-bar" style="  position: fixed; z-index:999;">
                                <div class="row">
								<form method="get">
                                    <div class="col-lg-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="keywords" placeholder="Search for..." value='{{$keywords}}'>
                                            <span class="input-group-btn">
                                                <button class="btn blue uppercase bold" type="submit">Search</button>
                                            </span>
                                        </div>
                                    </div>
									</form>
                                    <div class="col-lg-5">
                                        <p class="search-desc clearfix" style="color: #666;
  font-size: 35px;
  text-align: center;">Questions And Solutions</p>
                                    </div>
                                </div>
                            </div>
	</div>

	 <div class="row" style="  padding-top: 120px;z-index:998;">
	 
	  <?php if(!count($qas)) echo '<div class="row" style="text-align:center;">No matched records</div>';?>
	  @foreach ($qas as $qa)
        
        <div class="col-lg-6" style="float:left;padding:30px 70px;">
                                    <div class="blog-post-lg bordered blog-container" style="padding:30px; background: #fff;">
                                        <div class="blog-img-thumb" style="width:100%; height:400px; overflow:hidden;">
                                            <a href="/question/{{$qa->id}}"  target="_blank">
                                                <img src="<?php echo textimage($qa->description);?>" style="width:100%;" />
                                            </a>
                                        </div>
                                        <div class="blog-post-content">
                                            <h2 class="blog-title blog-post-title" style="height:60px; line-height:20px; overflow:hidden;font-size:18px;">
                                                <a href="/question/{{$qa->id}}"  target="_blank">{{ $qa->title }}</a>
                                            </h2>
                                            <p class="blog-post-desc" style="height:100px; line-height:20px; overflow:hidden;" >{{ html2text($qa->description) }}</p>
                                            <div class="blog-post-foot">
                                              
                                                <div class="blog-post-meta" style="float:left;">
                                                    <i class="icon-calendar font-blue"></i>
                                                    <a href="javascript:;">{{ $qa->created_at }} By {{ array_get($users,$qa->user_id) }}</a>
                                                </div>
                                                <div class="blog-post-meta" style="float:right;">
													<?php if($qa->confirm){ ?>
													<span class="label label-sm label-primary">Confirmed</span>
													<?php }else{ ?>
                                                   <span class="label label-sm label-danger">Un Confirm</span>
												   <?php }?>
                                                </div>
												<div style="clear:both;"></div>
                                            </div>
                                        </div>
                       
					                </div>
		</div>
		
		
    @endforeach
		
								</div>
								
		<div class="row" style="text-align:center;">{!! $qas->appends(['keywords' => $keywords])->links() !!}</div>
		</div>

        <!-- END QUICK NAV -->
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
        <!-- END CORE PLUGINS -->
        <!-- BEGIN THEME GLOBAL SCRIPTS -->
        <script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
        <!-- END THEME GLOBAL SCRIPTS -->
        <!-- BEGIN THEME LAYOUT SCRIPTS -->
        <script src="/assets/layouts/layout/scripts/layout.min.js" type="text/javascript"></script>
        <script src="/assets/layouts/layout/scripts/demo.min.js" type="text/javascript"></script>
        <script src="/assets/layouts/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>
        <script src="/assets/layouts/global/scripts/quick-nav.min.js" type="text/javascript"></script>
        <!-- END THEME LAYOUT SCRIPTS -->
    </body>

</html>