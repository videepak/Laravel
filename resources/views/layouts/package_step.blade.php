<?php
Route::get('/clear-cache', function() {
   $exitCode = Artisan::call('cache:clear');
   // return what you want
});

?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta charset="utf-8">

	<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
			<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> 
			<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" >
			
			<link rel="icon" href="{{url('assets/images/TrashScan.ico')}}" type="image/x-icon" />
			<link href="{{url('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
			<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

			<script src="{{url('assets/js/bootstrap.min.js')}}" ></script>
			<link rel="stylesheet" type="text/css" href="{{url('assets/css/step.css')}}">
			 

			<!-- ---------------for animation-------------------- -->
<style>
.top-bar {
    font-size: 14px;
    text-align: right;
    display: inline-block;
    width: 100%;
}
.pull-right.top-bar{margin-top: 15px;}
.pull-right.top-bar {
    margin-top: 7px;
}

.top_info {
     padding:8px 0;
     border-bottom:2px solid #ececec;
}
.text-center-sm{text-align: center; display: grid;}
.copyright .product-links {
    margin-right: 10px;
    width: 100%;
    text-align: center;
}
.product-links img {
    max-width: 100px;
    margin-right: 8px;
}
.copyright  .product-links {
    display: inline-block;
	margin-right: 10px;
}
a {
     color:#505050;
     transition: all 0.3s ease-in-out;
     -webkit-transition: all 0.3s ease-in-out;
}
 a:hover, a:focus {
     text-decoration:none;
     color:#12b27a;
}

 .full_width {
     position:relative;
     margin-left:-30px;
     margin-right:-30px;
}



</style>		
</head> 
<body>
<header class="Header">
	<div class="top_info clearfix">
                            <div class="container">
                                <div class="row align-item-center">
                                    <div class="col-md-6 col-sm-6 text-center-sm"><a href="{{url('/')}}"  ><img src="{{url('assets/images/logo.png')}} " width="35px"></a></div>
                                    <div class="col-md-6 col-sm-6 text-center-sm">
                                        <div class=" top-bar">
                                            <div class="product-links"> 
                                                <a href="{{url('request-demo')}}"  >
                                                    Request a Demo
                                                </a> 
												<a href="https://itunes.apple.com/us/app/trash-scan/id1384850027?ls=1&amp;mt=8" target="_blank">
                                                    <img src="{{url('assets/images/apple-store.png')}}">
                                                </a> 
                                                <a href="https://play.google.com/store/apps/details?id=com.gwl.trashscan" target="_blank">
                                                    <img src="{{url('assets/images/play-store.png')}}">
                                                </a> 
												
												<span>Call :</span>
                                                <span><a href="tel:1(800) 770-6963">1(800) 770-6963</a></span> 
                                            </div>
                                             
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
</header>
<!-- ------------------------------------------------------ -->
                     
                    
					
					@yield('content')
					
<footer class="footer">
<img src="{{url('assets/images/footer.jpg')}}" width="100%">
</footer>

</body>
</html>