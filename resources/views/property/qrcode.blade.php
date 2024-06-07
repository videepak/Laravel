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
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-4">
                    <h2>
                        Bin Tags : {{ $propertyName }}
                    </h2>
                    
                </div>
                <div class="col-md-4 pull-right">
                    <form  method="post" class="import-form" action="{{url('/uploadunits')}}" enctype="multipart/form-data" style="text-align: right">
                        {{csrf_field()}}
                        <input type="file" name="uploadexcel" id="file-upload" style="display: none"/>
                        <input type="hidden" value="{{$property_id}}" name="property_id"/>
                        <input type="hidden" value="{{$propertyType}}" name="property_type"/>
                        <a href="{{url('property/download-excel/'.$property_id.'')}}" class="btn btn-success download-excel" >Download Units Information</a>
                        <input type="button" class="btn btn-primary  import-class" value="Import" />
                        <input type="button" id="print" onclick="printDiv('printableArea')" class="btn btn-default" value="Print"  />
                    </form>
                </div>
                <div class="col-md-4 pull-right">
                    <form method="get" action="{{url('property/qrcode-generate/')}}/{{$property_id}}">
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <input name="unitsSearch" class="form-control input-sm" 
                            type="text" placeholder="Search by unit." value="{{!empty($unitsSearch) ? $unitsSearch : ''}}" />
                        </div>
                        @if($propertyType == 2 || $propertyType == 3)
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <select class="form-control input-sm" style="min-width: 82%;" name="buildingSearch">
                                <option value="">All Building</option>
                                @foreach($pBuilding as $emp)
                                    <option value="{{$emp->id}}" @if($buildingSearch == $emp->id) selected @endif>
                                        {{ucwords($emp->building_name)}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif    
                        <input type="submit" id="print" class="btn btn-primary" value="Submit"  />
                        </form>
                </div>
                <div class="clearfix"></div>                
            </div>
            <div class="col-md-12">
                <span style="float: right;">{{$property->links()}}</span>
            </div>
            <div class="row" id="printableArea">
                @if($property->isNotEmpty())
                    @foreach($property as $code)
                       <div class="col-sm-4 full-width-mob barcode-section">
                            <div class="row barcode-row-div">
                                <div class="col-sm-12 qrImage">
                                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(300)->generate($code->barcode_id)) !!} " class="barcode-img">
                                    <div class="barcode-name"> 
                                        <div class="bar-code">
                                            <p class="bar-code-p"> 
                                                 @if(!empty($code->unit_number))
                                                    Unit#: {{$code->unit_number}}
                                                @else
                                                    Unit#: {{$code->barcode_id}} 
                                                @endif
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
                <div class="well" style="overflow: auto">
                    <h5>No Recode Found.</h5>
                </div>    
                @endif
            </div>
            <span style="float: right;">{{$property->links()}}</span>
            <div class="col-md-12" style="text-align: right;">
                <a href="{{url('property/download-excel/'.$property_id.'')}}" class="btn btn-success download-excel" >Download Units Information</a>
                <input type="button" class="btn btn-primary  import-class" value="Import" />
                <input type="button" id="print" onclick="printDiv('printableArea')" class="btn btn-default" value="Print"  />
            </div> 
        </div>
</div>
@endsection 
@section('js')
<script>
    $(document).ready(function () {
        $('.import-class').click(
            function () {
                $('#file-upload').trigger('click');
            }
        );

        $('#file-upload').change(function () {

            if (confirm("Are sure you want to update unit number by the excel?")) {
                $('.import-form').submit();
            } else {
                $(this).val('');
                return false;
            }
        });
    });

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

    $(".download-excel").click(
        function () {
            // setTimeout(function () {
            //     location.reload();
            // }, 1000);
        }
    );
</script>
@endsection



