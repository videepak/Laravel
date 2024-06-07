<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>500 Server Error Occurred</title>

        <!-- Bootstrap -->
        <link href="{{url('assets/vendors/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="{{url('assets/vendors/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
        <!-- Custom Theme Style -->
        <link href="{{url('assets/build/css/custom.min.css')}}" rel="stylesheet">
        <style>
            .error-number {
                font-size: 61px;
                line-height: 90px;
                margin: 20px 0;
                color: white;
            }
        </style>
    </head>

    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                <!-- page content -->
                <div class="col-md-12">
                    <div class="col-middle">
                        <div class="text-center">
                            <h1 class="error-number">Oops!</h1>
                            <h3 style="color:white;">500 Server Error Occurred</h3>
                            <p  style="color:white;"> Sorry, an unexpected error has occurred, Please try again later!</p>
                            <div class="error-actions">
                                <a href="{{url('home')}}" class="btn btn-primary btn-lg">
                                    <span class="glyphicon glyphicon-home"></span>
                                    Take Me Home 
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>

        <!-- jQuery -->
        <script src="{{url('assets/vendors/jquery/dist/jquery.min.js')}}"></script>
        <!-- Bootstrap -->
        <script src="{{url('assets/vendors/bootstrap/dist/js/bootstrap.min.js')}}"></script>
        <!-- FastClick -->
        <script src="{{url('assets/vendors/fastclick/lib/fastclick.js')}}"></script>
        <!-- Custom Theme Scripts -->
        <script src="{{url('assets/build/js/custom.min.js')}}"></script>
    </body>
</html>
