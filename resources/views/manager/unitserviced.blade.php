@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<style>
    .pagination>.active>a, .pagination>.active>a:focus, .pagination>.active>a:hover, .pagination>.active>span, .pagination>.active>span:focus, .pagination>.active>span:hover {
        background-color: #3E5566 !important;
        border-color: #3E5566 !important;
    }
    .pagination>li>a {
        color: #3E5566 !important;
    }
</style>
@endsection
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-10 col-sm-8 col-xs-4">
                    <h2>Unit Serviced</h2>
                </div> 
                <div class="col-md-2 col-sm-8 col-xs-4">
                    <input type="text" class="form-control has-feedback-left" id="single_cal" readonly />
                    <span class="fa fa-calendar-o form-control-feedback left" aria-hidden="true"></span>
                    <span id="inputSuccess2Status4" class="sr-only">(success)</span>
                </div>
            <div class="clearfix"></div>
        </div>

        <div class="x_content">
            <div class="table-responsive">
               
            <table class="table table-striped jambo_table bulk_action" id="datatable">
                <thead>
                    <tr class="headings">    
                        <th class="column-title">S.no </th>
                        <th class="column-title">QR code </th>
                        <th class="column-title">#of Unit </th>
                        <th class="column-title">Property Detail </th>
                        <th class="column-title">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($qrcodeDetail as $qr_property => $qrCollection)
                        <tr>
                            <th colspan="5" style="background: #3d5566;color: white;">
                                <center style="font-size: initial;">{{ucwords($qr_property)}}</center>
                            </th>
                        </tr>  
                    @foreach($qrCollection as $qrcodeDetails)
                    @isset($qrcodeDetails->unit_number)
                    <tr class="even pointer">    
                        <td>
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {!! QrCode::size(100)->generate($qrcodeDetails->barcode_id); !!}
                        </td>
                        <td>
                            @if(!empty($qrcodeDetails->unit_number))
                            {{$qrcodeDetails->unit_number}}
                            @else
                            {{ $qrcodeDetails->barcode_id }}
                            @endif
                        </td>

                        <td> 
                            <b>Property Name:</b> 
                            {{ ucfirst($qrcodeDetails->getPropertyDetail->name) }}.
                            <br/>
                            <b>Property Address:</b> 
                            {{ ucwords($qrcodeDetails->getPropertyDetail->address.", ".$qrcodeDetails->getPropertyDetail->city.", ".$qrcodeDetails->getPropertyDetail->getState->name.", ".$qrcodeDetails->getPropertyDetail->zip) }}.<br/>

                            @if($qrcodeDetails->getPropertyDetail->type == 1 || $qrcodeDetails->getPropertyDetail->type == 4) 

                            <b>Unit Address:</b> 
                            {{ ucfirst($qrcodeDetails->address1) }}.<br/>

                            @elseif($qrcodeDetails->getPropertyDetail->type == 3 || $qrcodeDetails->getPropertyDetail->type == 2)

                            <b>Building Name:</b> 
                            @isset($qrcodeDetails->getBuildingDetail->building_name)
                            {{ ucfirst($qrcodeDetails->getBuildingDetail->building_name) }}.
                            @endisset
                            <br/>
                            <b>Building Address:</b>
                            @isset($qrcodeDetails->getBuildingDetail->address)
                            {{ ucfirst($qrcodeDetails->getBuildingDetail->address)	 }}.<br/>
                            @endisset
                            @endif
                            <b>Unit:</b> 
                            @empty($qrcodeDetails->unit_number)
                            Not Mention.
                            @else
                            {{ ucfirst($qrcodeDetails->unit_number) }}
                            @endempty
                            <br/>
                            <b>Property Type:</b> 
                            @if($qrcodeDetails->getPropertyDetail->type == 1)
                            Single Family Home
                            @elseif($qrcodeDetails->getPropertyDetail->type == 2)
                            Garden Style Apartment
                            @elseif($qrcodeDetails->getPropertyDetail->type == 3)
                            High Rise Apartment
                            @elseif($qrcodeDetails->getPropertyDetail->type == 4)
                            Townhome        
                            @endif
                            <br/>
                        </td>
                        <td> 
                           <b>Username:</b> {{$qrcodeDetails->username}}<br/>
                           <b>Pickup Date:</b> {{\Carbon\Carbon::parse($qrcodeDetails->created)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}
                        </td>  
                    </tr>
                    @endisset
                    @endforeach
                    @endforeach    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
@endsection 
@section('js')
<script>
var dates = "{{request()->get('date')}}";

$(window).ready(function() {

    $("#single_cal").daterangepicker({
        singleDatePicker: true,
        singleClasses: "picker_4",
        startDate: new Date(dates),
        maxDate: new Date()
    });
  
    $('#single_cal').change(
        function() { 
            if(dates != $('#single_cal').val()) {
                window.location.href = BaseUrl + '/property-manager/units-serviced?date='+$('#single_cal').val();
            }
        }
    );
});
</script>
@endsection 