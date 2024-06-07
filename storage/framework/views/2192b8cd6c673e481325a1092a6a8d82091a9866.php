<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Trash Scan</title>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="<?php echo e(url('assets/images/TrashScan.ico')); ?>" type="image/x-icon" />

        <!-- CSS STYLES -->
        <link href="<?php echo e(url('assets/css/bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo e(url('assets/css/flexslider.css')); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo e(url('assets/css/animate.css')); ?>" rel="stylesheet" type="text/css" media="all" />
        <link href="<?php echo e(url('assets/css/style.css')); ?>" rel="stylesheet" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=Nunito:400,300,700' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Quintessential' rel='stylesheet' type='text/css'>
        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" 
              rel="stylesheet" 
              integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" 
              crossorigin="anonymous">
        <style>
            .contact_btn {
                width: auto;
                padding: 3px 14px 4px;
                background-color: #12b27a;
                border: 1px solid #12b27a !important;
                color: #fff;
                float: right;
            }
            .container.vh-height {
                min-height: calc(100vh - 342px);
            }
            .logo{ margin-top: 20px;}
            
            .login_form{ max-width: 400px; margin: 40px auto; margin-bottom: 0px;}
            .login-btn{ margin-top: 10px;margin-bottom: 10px;}
            .login_form input{
                border-radius: 0px !important;
                height: 40px !important;
                margin-bottom: 10px !important;
            }
        </style>
        <script src="<?php echo e(url('assets/js/jquery.min.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/bootstrap.min.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/jquery-ui.min.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/superfish.min.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/jquery.flexslider-min.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/animate.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/myscript.js')); ?>" type="text/javascript"></script>
        <script src="<?php echo e(url('assets/js/jquery.easing.js')); ?>" type="text/javascript"></script>	   
    </head>
    <body>
        <div id="page"> 
            <div class=""> 
                <div class="wrapper">
                    <header id="ajax-form">
                        <div class="top_info clearfix">
                            <div class="container">
                                <div class="row align-item-center">
                                    <div class="col-md-6 col-sm-6 text-center-sm"> </div>
                                    <div class="col-md-12 col-sm-12 text-center-sm">
                                        <div class=" top-bar">
                                            <div class="product-links"> 
                                                <a href="<?php echo e(url('request-demo')); ?>"  >
                                                    Request a Demo
                                                </a> 
												<a href="https://itunes.apple.com/us/app/trash-scan/id1384850027?ls=1&amp;mt=8" target="_blank">
                                                    <img src="<?php echo e(url('assets/images/apple-store.png')); ?>">
                                                </a> 
                                                <a href="https://play.google.com/store/apps/details?id=com.gwl.trashscan" target="_blank">
                                                    <img src="<?php echo e(url('assets/images/play-store.png')); ?>">
                                                </a> 
                                            </div>
                                            <div class="call-us">
                                                <span>Call :</span>
                                                <span><a href="tel:1(800) 770-6963">1(800) 770-6963</a></span> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="menu_block clearfix">
                            <div class="container primary-header"> 
                                <!-- LOGO -->
                                <div class="wrap-logo-menu-btn">
                                    <div class="logo" style="text-align:center;float:none;">
                                        <a href="<?php echo e(url('/')); ?>" alt=""> 
                                            <img src="<?php echo e(url('assets/images/logo.png')); ?>" alt="logo">
                                        </a>
                                        <br>
                                        <span class="logo_descr">Waste Management Cloud Solution</span></div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <?php echo $__env->yieldContent('content'); ?>
                    <footer class="full_width footer_block" style="padding:0px !important;"> 
                        <div class="container">
                            <div class="copyright clearfix">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-5 col-sm-5 col-xs-12">
                                            <p>
                                                &copy;  Trash Scan
                                                <?php echo e(date('Y')); ?>.
                                                All rights reserved. 
                                            </p>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-xs-12 text-right">
                                            <div class="product-links"> 
                                                <a href="https://itunes.apple.com/us/app/trash-scan/id1384850027?ls=1&amp;mt=8" target="_blank">
                                                    <img src="<?php echo e(url('assets/images/apple-store.png')); ?>">
                                                </a> 
                                                <a href="https://play.google.com/store/apps/details?id=com.gwl.trashscan" target="_blank">
                                                    <img src="<?php echo e(url('assets/images/play-store.png')); ?>">
                                                </a> 
                                            </div>
                                            <div class="pull-right developed-by"> Developed by &nbsp;
                                                <a href="https://www.galaxyweblinks.com/">
                                                    <img src="<?php echo e(url('assets/images/galaxy-logo-1.png')); ?>" class="gwl-icon">
                                                </a> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>  
        <?php echo $__env->yieldContent('js'); ?>
    </body>
</html>