@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<style>
    .parsley-required{
        display: none;
    }
    .req_field{
        color: red;
    }
    .form_cls form {
    padding: 35px 0 0;
}

.form_cls form .form-group {
    padding-bottom: 3px;
}

.print_cls a {
    color: #fff;
    background: #26B99A;
    border: 1px solid #169F85;
    min-width: 71px;
    min-height: 34px;
    line-height: 1;
}
.print_cls {
    text-align: center;
}
</style> 
@endsection
@section('content')   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Bin Tags<small></small></h2>
                <div class="clearfix"></div>
            </div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="x_content">
                <div class="col-md-12">
                    <div class="col-md-6 form_cls">
                        <form id="add_employee" name="add_employee" data-parsley-validate=""
                          class="form-horizontal form-label-left" novalidate=""
                          action="{{url('barcode/'.$detail->id)}}" method="post">

                        {{method_field('PUT')}}

                        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field"> Address1 
                                <span class="required req_field">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="title_field" name="address1" required="required" value="{{$detail->address1}}" 
                                       class="form-control col-md-7 col-xs-12" type="text">
                            </div>
                        </div>
                        <input type="hidden" name="property_id" value="{{$detail->property_id}}">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last_name"> Unit 
                                <span class="required req_field">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="last_name" name="unit"  required="required" class="form-control col-md-7 col-xs-12" value="{{$detail->unit_number}}" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email_field" class="control-label col-md-3 col-sm-3 col-xs-12">Floor </label> 
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="mobile_field" onpaste="return false;"  data-parsley-type="digits" data-parsley-maxlength="10" class="form-control col-md-7 col-xs-12" name="floor" value="{{$detail->floor}}" type="text">
                            </div>
                        </div>
                        @if(isset($detail->getPropertyDetail->getBuilding) && ($detail->getPropertyDetail->type == 2 || $detail->getPropertyDetail->type == 3))
                        <div class="form-group">
                            <label for="mobile_field" class="control-label col-md-3 col-sm-3 col-xs-12">Building <span class="required req_field">*</span></label> 
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <select name="building" class="form-control">Building
                                    <option value="0">Select Building</option>
                                    @foreach($detail->getPropertyDetail->getBuilding as $building)
                                    <option value="{{$building->id}}" @if($detail->building_id == $building->id) selected @endif>{{ucwords($building->building_name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class=" col-md-7 col-sm-7 col-xs-12 col-md-offset-3">
                                <button class="btn btn-primary" type="button" onclick="location = '{{url('barcode')}}';return false;">Cancel</button>						 
                                <button type="submit" class="btn btn-success">Update</button>
                            </div>
                        </div>
                    </form>
                    </div>
                    <div class="col-md-6 print_cls">
                        <div id="printableArea">
                            {!! QrCode::size(250)->generate($detail->barcode_id); !!}
                        </div>
                        <a href="javascript:void(0);" onclick="printDiv('printableArea')" class="btn btn-success"> 
                            <span class="glyphicon glyphicon-print"></span> Print </a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection 
@section('js')
<script>
    function printDiv(divName) {

        var divToPrint = document.getElementById(divName);
        newWin = window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();
    }
</script>
@endsection
