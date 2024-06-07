<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="<?php echo e(csrf_token()); ?>" />
        <title>Trash Scan </title>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <link rel="icon" href="<?php echo e(url('assets/images/TrashScan.ico')); ?>" type="image/x-icon" />
        <link href="<?php echo e(url('assets/vendors/bootstrap/dist/css/bootstrap.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/font-awesome/css/font-awesome.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/iCheck/skins/flat/green.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/google-code-prettify/bin/prettify.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/select2/dist/css/select2.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/switchery/dist/switchery.min.css')); ?>" rel="stylesheet">  
        <link href="<?php echo e(url('assets/vendors/starrr/dist/starrr.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/bootstrap-daterangepicker/daterangepicker.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/animate.css/animate.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/build/css/custom.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/build/css/trashscan.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/bootstrap-daterangepicker/daterangepicker.css')); ?>" rel="stylesheet">    
        <link href="<?php echo e(url('assets/vendors/pnotify/dist/pnotify.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/pnotify/dist/pnotify.buttons.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/pnotify/dist/pnotify.nonblock.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/trashscancss/trashscan.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(url('assets/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css')); ?>" rel="stylesheet">
        <?php echo $__env->yieldContent('css'); ?>
    </head>
    <body class="login nav-md">        
        <div class="container body">
            <div class="main_container">
                <div class="loading">Loading&#8230;</div>
                <?php echo $__env->yieldContent('menu'); ?>
                <?php echo $__env->yieldContent('content'); ?>
                <!--Logout form:Start-->
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                    <?php echo e(csrf_field()); ?>

                </form>
                <!--Logout form:End-->
                <footer>
                    <div class="text-center">
                        <a href="<?php echo e(url('home')); ?>" target="_blank">
                            <img src="<?php echo e(url('/assets/production/images')); ?>/Trash-Scan.png" 
                                 class="img-rounded" title="Trashcan"/>
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </footer>
            </div>
        </div>
    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="<?php echo e(url('assets/vendors/jquery/dist/jquery.min.js')); ?>"></script>
	<script src="<?php echo e(url('ckeditor/ckeditor.js')); ?>"></script>



	<script>
$(document).ready(function() {
	$('.stripe-button-el').attr('disabled', 'disabled');
	var num=0;
	$('#tnc2').on('change', function(){
	  if ($(this).is(':checked')){
		 //$('.stripe-button-el').removeAttr('disabled');
		 num++;
		 if(num=='2'){
			 $('.stripe-button-el').removeAttr('disabled');
		 } else {
			 $('.stripe-button-el').attr('disabled', 'disabled');
		 }
	  } else {
		 //$('.stripe-button-el').attr('disabled', 'disabled');
		 num--;
		 $('.stripe-button-el').attr('disabled', 'disabled');
	  }
	});
	
	$('#tnc').on('change', function(){
	  if ($(this).is(':checked')){
		 //$('.stripe-button-el').removeAttr('disabled');
		 num++;
		 if(num=='2'){
			 $('.stripe-button-el').removeAttr('disabled');
		 } else {
			 $('.stripe-button-el').attr('disabled', 'disabled');
		 }
	  } else {
		 $('.stripe-button-el').attr('disabled', 'disabled');
		 num--;
		 
	  }
	});
	
});
</script>
    <script src="<?php echo e(url('assets/vendors/bootstrap/dist/js/bootstrap.min.js')); ?>"></script>   
    <script src="<?php echo e(url('assets/vendors/fastclick/lib/fastclick.js')); ?>"></script>    
    <!-- <script src="<?php echo e(url('assets/vendors/Chart.js/dist/Chart.min.js')); ?>"></script> -->   
    <script src="<?php echo e(url('assets/vendors/jquery-sparkline/dist/jquery.sparkline.min.js')); ?>"></script>  
    <script src="<?php echo e(url('assets/vendors/raphael/raphael.min.js')); ?>"></script> 
    <!-- <script src="<?php echo e(url('assets/vendors/gauge.js/dist/gauge.min.js')); ?>"></script> -->    
    <script src="<?php echo e(url('assets/vendors/skycons/skycons.js')); ?>"></script>  
   <!--  <script src="<?php echo e(url('assets/vendors/Flot/jquery.flot.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/Flot/jquery.flot.pie.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/Flot/jquery.flot.time.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/Flot/jquery.flot.stack.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/Flot/jquery.flot.resize.js')); ?>"></script>    
    <script src="<?php echo e(url('assets/vendors/flot.orderbars/js/jquery.flot.orderBars.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/flot-spline/js/jquery.flot.spline.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/flot.curvedlines/curvedLines.js')); ?>"></script>    
    <script src="<?php echo e(url('assets/vendors/DateJS/build/date.js')); ?>"></script>-->
    <script src="<?php echo e(url('assets/build/js/custom.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/iCheck/icheck.min.js')); ?>"></script>  
    <script src="<?php echo e(url('assets/vendors/moment/min/moment.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/bootstrap-daterangepicker/daterangepicker.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/jquery.hotkeys/jquery.hotkeys.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/google-code-prettify/src/prettify.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/jquery.tagsinput/src/jquery.tagsinput.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/switchery/dist/switchery.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/select2/dist/js/select2.full.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/parsleyjs/dist/parsley.min.js')); ?>"></script>  
    <script src="<?php echo e(url('assets/vendors/autosize/dist/autosize.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/devbridge-autocomplete/dist/jquery.autocomplete.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/starrr/dist/starrr.js')); ?>"></script>
    <!-- PNotify -->
    <script src="<?php echo e(url('assets/vendors/pnotify/dist/pnotify.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/pnotify/dist/pnotify.buttons.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/pnotify/dist/pnotify.nonblock.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/validator/validator.js')); ?>"></script>
    <script src="<?php echo e(url('assets/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')); ?>"></script>
    <script src="<?php echo e(url('assets/trashscanjs/trashscan.js')); ?>" type="text/javascript"></script>
    <script>var BaseUrl = "<?php echo e(url('')); ?>";</script>
    <?php echo $__env->yieldContent('js'); ?>
    <?php if(session('status') && is_array(session('status'))): ?>
    <script>
        $(function() {
            new PNotify({
            title: '<?php echo e(session('status')['title']); ?>',
                    text: '<?php echo e(session('status')['text']); ?>',
                    type: '<?php echo e(session('status')['class']); ?>',
                    styling: 'bootstrap3'
            });
        });
    </script>
    <?php endif; ?>
</html>
