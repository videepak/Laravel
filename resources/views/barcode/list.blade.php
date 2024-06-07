@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<style>
    #example_processing {
        height: 100%;
        width: 100%;
        position: absolute;
        top: 0%;
        left: 8%;
        z-index: 99999;
        filter: alpha(opacity=75);
        -moz-opacity: 0.75;
        opacity: 0.75;
        color: black;
        padding: 50% 0% 0% 0%;
        font-weight: 600;
        font-size: medium;
    }
    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        color: white !important;
    }
    .set-width {
        width: 205%;
        margin-left: -89%;
    }
    .dataTables_wrapper .dt-buttons {
        display: none;
    }
</style>
@endsection
@section('content')     
@yield('menu')     
<div class="right_col" role="main">
    <div class="row tile_count" style="text-align:center;margin-top: 7%;">
        <div class="col-md-offset-3 col-md-2 col-sm-4 col-xs-6 tile_stats_count">
            <span class="count_top"><i class="fa fa-barcode"></i></i> Total QR Code</span>
            <div class="count">{{ $qrcodeActive->count() + $qrcodeInactive->count() }}</div>
            <span class="count_bottom"><i class="green"></i> ---------</span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
            <span class="count_top"><i class="fa fa-barcode"></i></i> Active QR Code</span>
            <div class="count">{{ $qrcodeActive->count() }}</div>
            <span class="count_bottom"><i class="green"></i> ---------</span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
            <span class="count_top"><i class="fa fa-barcode"></i></i> InActive QR Code</span>
            <div class="count">{{ $qrcodeInactive->count() }}</div>
            <span class="count_bottom"><i class="green"></i> ---------</span>
        </div>
    </div>

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel"> 
            <div class="x_title">
                <h2>Bin Tags List</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li class="bulk-actions" style="display: none">
                        <a class="btn btn-primary pull-right bulk-activation" href="javascript:void(0);">
                            <i class="fa fa-key" aria-hidden="true"></i> Bulk Activation
                        </a>
                    </li>
                    <li class="bulk-actions route-display" style="display: none">
                        <a class="btn btn-primary pull-right make-route" data-type="1" href="javascript:void(0);">
                            <i class="fa fa-key" aria-hidden="true"></i> Make As A Route Checkpoints
                        </a>
                    </li>
                    <li class="bulk-actions unit-display" style="display: none">
                        <a class="btn btn-primary pull-right make-route" data-type="0" href="javascript:void(0);">
                            <i class="fa fa-key" aria-hidden="true"></i> Make As A Unit
                        </a>
                    </li>
                    <li>
                        <a class="btn btn-primary pull-right excel-option" >
                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel </a>
                    </li>
                </ul>		
                <div class="clearfix"></div> 
            </div>

            <div class="x_content">

                <div class="table-responsive">
                    <table id="example" class="table table-striped jambo_table bulk_action" id="datatable">
                        <thead>
                            <tr class="headings">
                                <th>
                                    <input type="checkbox" id="check-all" class="datatable-checkbox" style='cursor: pointer'>
                                </th>    
                                <th class="column-title">S.No </th>
                                <th class="column-title">QR Code </th>
                                <th class="column-title">Unit Number </th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter type" 
                                    data-col="type">
                                        <option value="">Type</option>
                                        <option value="1">Route Checkpoint</option>
                                        <option value="0">Unit</option>
                                    </select>
                                </th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter" id="properties">
                                        <option value="">Property</option>
                                        @foreach($propertyList as $property)
                                        <option value="{{$property->id}}">
                                            {{ucwords($property->name)}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="column-title">
                                <select class="form-control input-sm filter status" 
                                data-col="status">
                                    <option value="">Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                </th>
                                <th class="column-title">Action </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <form id="search-filter" action="" method="GET"></form> -->
<form id="deleteBarcode" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section("js")
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/bin.tag.list.js')}}"></script>

<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
<script>
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

    // $(".filter-search").change(function () {
    //     var base_url = '{{url('')}}';
    //     var barcodeStatus = $('#barcodeStatus').val();
    //     //     alert(barcodeStatus);
    //     var propertiesId = $('#propertiesId').val();
    //     //     alert(propertiesId);

    //     $("#search-filter").attr('action', base_url + "/barcode/filter/" + propertiesId + '/' + barcodeStatus);
    //     $("#search-filter").submit();
    // });

</script>

@endsection 