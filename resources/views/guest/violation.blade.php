<html>
    <title>Trash Scan | Violation</title>
    <head>
        <link href="{{url('assets/vendors/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet" />
        <style>
            .logo{
                padding: 5px;
            }
            table tr th {
                padding: 5px;
                text-align: right;
            }
            table tr td {
                padding: 5px;
            }
            .thumbnail {
                height: 190px;
                overflow: hidden;
            }
        </style>
    </head>    
    <body>
        <div align="center">
            <div style="max-width: 680px; min-width: 500px; border: 2px solid #e3e3e3; border-radius:5px; margin-top: 20px">
                <div class="logo"> 
                    <a href="" alt="">
                        <img src="{{$logo}}" alt="logo" style="width: 80px;">
                    </a>
                    <br>
                    <span class="logo_descr">{{$companyName}}</span>
                </div>     
               
                @if($enquiry->isNotEmpty())
                @include('violation.detailsmodal')
                @else
                <div  style="background-color: #fbfcfd; border-top: 1px solid #cccccc; text-align: left;">
                    <div style="margin: 30px 30px 0px 30px;">
                     
                            <p class="text-center" style="font-weight: bold;">
                                The violation information does not exist.
                            </p>
                            <center style="padding: 10px;">
                                ©  Trash Scan  2019. All rights reserved.
                            </center>
                       
                    </div>
                </div>
                @endif
            </div>
        </div>
    </body>
</html>
