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
    <center> 
        <div class="row tile_count" style="text-align:center;margin-top: 7%;">
            <div class="col-md-offset-3 col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top"><i class="fa fa-barcode"></i></i> Total QR code</span>
                <div class="count">{{ $qrcodeActive->count() + $qrcodeInactive->count() }}</div>
                <span class="count_bottom"><i class="green"></i> ---------</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top"><i class="fa fa-barcode"></i></i> Active QR code</span>
                <div class="count">{{ $qrcodeActive->count() }}</div>
                <span class="count_bottom"><i class="green"></i> ---------</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top"><i class="fa fa-barcode"></i></i> InActive QR code</span>
                <div class="count">{{ $qrcodeInactive->count() }}</div>
                <span class="count_bottom"><i class="green"></i> ---------</span>
            </div>
        </div>
    </center> 

    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6 col-sm-8 col-xs-4">
                    <h2>Bin Tags List <small></small></h2>
                </div> 
                <div class="col-md-3 col-sm-4 col-xs-8 pull-right">
                    <select name='property' class="form-control filter-search" id="propertiesId">Properties
                        <option value="0">Select Property</option>
                        @foreach($properties as $property) 
                        <option value='{{$property['id']}}' @if($id == $property['id']) selected="selected" @endif  >{{ucwords($property['name'])}}
                                @if($property->type == 1)
                                (Single Family Home)
                                @elseif($property->type == 2)
                                (Garden Style Apartment)
                                @elseif($property->type == 3)
                                (High Rise Apartment)
                                @elseif($property->type == 4)
                                (Townhome)        
                                @endif
                    </option>
                    @endforeach
                </select>
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
                            <th class="column-title">Bin Tag Status </th>
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
                        <td id="printableArea{{$qrcodeDetails->barcode_id}}">
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
                        <td class=" "> 
                            <span class="label label-warning">Not Pickup</span>
                        </td>  
                    </tr>
                    @endisset
                    @endforeach
                    @endforeach    
                    </tbody>
                </table>
            </div>
            <span style="float: right;">{{ $property_units->links() }}</span>
        </div>
    </div>
</div>
</div>
<form id="search-filter" action="" method="GET"></form>
<form id="deleteBarcode" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section("js")
<script>


    function printDiv(divName) {

        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }


    function deleteBarcode(element, event)
    {
        event.preventDefault();
        if (confirm('Are you sure you want to continue?'))
        {
            var url = $(element).attr('href');
            $('#deleteBarcode').attr('action', url);
            $('#deleteBarcode').submit();
        }
    }

    $(".filter-search").change(function () {
        var base_url = '{{url('')}}';
        var propertiesId = $('#propertiesId').val();
        $("#search-filter").attr('action', base_url + "/notPickupList/filter/" + propertiesId);
        $("#search-filter").submit();
    });

</script>

@endsection 