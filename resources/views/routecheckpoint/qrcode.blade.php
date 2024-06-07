@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .qrImage {
        width: 100%;
        padding: 0;
    }
    .barcode-img {
        max-width: 100%;
        margin-bottom: 10px;
    }
    .barcode-section {
        background: white;
        margin: 0px 0px 40px 0px;
    }
    .barcode-row-div {
        padding: 0;
        max-width: 260px;
        margin: 10px auto;
    }
    .barcode-detail {
        padding: 0% 0% 0% 22%;
        margin: -18% 0% 0% 0%;
    }
    .barcode-name {
        font-weight: normal;
        margin: -40px 0 0 0;
        text-align: center;
        font-size: 14px;
        color: #222;

    }
    .img-section{
        margin: inherit;
        padding: -1px 0px 0px 0px;
    }
    .logoimage {
        margin: 0;
    }
    p.barcode-name img {
        margin-left: -5px;
        vertical-align: middle;
        margin-top: -5px;
    }
    .nav-md .container.body .col-md-3.left_col {
        position: fixed;
        left: 0;
        top: 0;
        min-height: 100vh;
    }
    .barcode-name {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
    }
    .bar-code p {
        font-weight: 600;
        margin-top: 11px;

    }
    .print {
        padding: 10px 10px 10px 10px; 
        font-weight: bold;
    }

    @media screen and (max-width:991px) {

        .barcode-name {
            font-size: 12px;
        }
        .x_title h2 {
            width: auto;
            margin: 10px 0;
        }
        .stripe-button-el {
            margin-top: 0;
        }

    }

    @media screen and (max-width:767px){   

        .barcode-row-div {
            max-width: 238px;
        }
        .barcode-section {
            margin: 0px 0px 10px 0px;
        }
        .full-width-mob {
            width: 50%;
        }
    } 
    @media screen and (max-width:575px){ 
        .full-width-mob {width: 100%;}

    } 
</style>   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if(session('message'))

        @endif
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6">
                    <h2>Route Checkpoint Barcode </h2>
                </div>

                <div class="col-md-6 pull-right">
                  
                    <a href="javascipt:void(0);"
                        class="btn btn-primary pull-right"
                        onclick="printDiv('printableArea')">
                            <i class="fa fa-print" aria-hidden="true"></i> 
                                Print
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" id="printableArea">

                @if(!empty($data))                
                @foreach($data as $code) 

                <div class="col-sm-4 full-width-mob barcode-section">
                    <div class="row barcode-row-div">
                        <div class="col-sm-12 qrImage">    

                            <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(300)->generate($code['barcode_id'])) !!} " class="barcode-img">
                            <div class="barcode-name"> 
                                <div class="bar-code">
                                    <p class="bar-code-p"> 
                                       {{$code["unit_number"]}} 
                                    </p>
                                </div> 
                                <div class="bin-img">
                                    <img class="logoimage" src= "{{url('/assets/production/images')}}/trashscanlogo.png" >
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>
                @endforeach 
                @else
                    <div class="col-sm-4 full-width-mob barcode-section">
                        No Record Found
                    </div>   
                @endif
            </div>
            <span style="float: right;">{{ $data->appends(request()->query())->links() }}</span>
        </div>
    </div>
</div>
@endsection 
@section('js')
<script>
  function printDiv(divName) {
        var contents = $("#printableArea").html();
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html><head><title>DIV Contents</title>');
        frameDoc.document.write('</head><body>');
        //Append the external CSS file.
        frameDoc.document.write('<link href="{{url("assets/build/css/print.css")}}" rel="stylesheet" type="text/css" />');

        //Append the DIV contents.
        frameDoc.document.write(contents);
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            //frame1.remove();
        }, 500);
    }
</script>
@endsection


