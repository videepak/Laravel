@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">
<style>  
.parsley-required {
  display:block; 
  padding-left: 15px; 
} 
#example_processing {
        height: 50px !important;
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
    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
        background-color: #3E5566;
        color: white;
    }
</style>
@endsection
@section('content')
<?php
// echo "<pre>";
// print_r($property->getBuilding);die;
?>
<div class="right_col" role="main">
	<div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Route Check Point: {{ucwords($property->name)}}</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <!-- <li>
                            <a class="btn btn-primary pull-right" href="javascript:void(0);" 
                            data-toggle="modal" data-target="#routeCheckPoint">
                            + Add Route Check Point
                            </a>
                        </li> -->
                        <li>
                            <a href="{{url('routecheck-point/qrcode?id=')}}{{$property->id}}"
                                class="btn btn-primary pull-right" >
                                <i class="fa fa-qrcode" aria-hidden="true"></i> 
                                    Barcodes
                            </a>
                        </li>
                    </ul>			 
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table id="route-point" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr> 
                                <th class="column-title">S.No</th>
                                <th class="column-title">Barcode</th>
                                <th class="column-title">
                                    <select
                                        class="form-control input-sm filter"
                                        style="width: 72%;" id="buildList">
                                        <option value="">Buildings</option>
                                        @foreach($property->getBuilding as $build)
                                            <option value="{{ $build->id }}">{{ ucwords($build->building_name) }}</option>
                                        @endforeach
                                        </option>
                                    </select>
                                </th>
                                {{-- <th class="column-title">
                                    <select class="form-control input-sm filter"
                                    style="width: 72%;" id="buildList">
                                        <option value="">Buildings</option>
                                        @foreach($property->getBuilding as $build)
                                        <option value="{{ $build->id }}">{{ ucwords($build->building_name) }}</option>
                                        @endforeach
                                        </option>
                                  </select>
                                </th> --}}
                                <!-- <th class="column-title">Description </th>
                                <th class="column-title">Mandatory</th> -->
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>       
<div id="outprint" style="display:none;"></div>
<!-- Modals Start -->
<div id="routeCheckPoint" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Route Check Point </h4>
            </div>
                <form class="form-horizontal" action="/action_page.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label col-sm-2">Name
                                <span class="required req_field">*</span></label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="name" placeholder="Enter Name">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2" >Address1
                                <span class="required req_field">*</span></label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="addressOne" placeholder="Enter Address1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="email">Address2</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="addressTwo" placeholder="Enter Address2">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-sm-2">Description</label>
                                    <div class="col-sm-10">
                                        <textarea name="" id="description" class="form-control" placeholder="Enter Description"></textarea>
                                    </div>
                                </div>
                                <div class="form-group"
                                style="display: {{$property->type == 1 || $property->type == 4 ? 'none' : 'block'}}" >
                                <label class="control-label col-sm-2">Building</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" id="buildingId">
                                            @foreach($property->getBuilding as $build)
                                            <option value="{{$build->id}}">{{$build->building_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                <label class="control-label col-sm-2" >Mandatory</label>
                                <div class="col-sm-10">
                                    <div class="checkbox">
                                        <input type="checkbox" class="flat" id="isRequired">
                                    </div>
                                </div>
                                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary submit-btn create-route">Submit</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>       
            </div>
        </div>
    </div>
</div>
@endsection 
@section('js')
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/routecheck.point.list.js')}}"></script>
<script>var propertyId = {{$_GET['property']}}</script>
@endsection





