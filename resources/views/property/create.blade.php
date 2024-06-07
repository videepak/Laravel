@extends('layouts.user_app')
@include('layouts.user_menu')
@section('css')
    <link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
    <style>
    </style>
@endsection
@section('content')
<style>
    .parsley-required {
        display: block;
    }
    .lab {
        margin-left: 73px;
    }
    .has-feedback .form-control {
        padding-right: 0px !important; 
    }
    p.parsley-success {
        color: #808EAC !important; 
        background-color: #FFFFFF !important; 
        border: 1px solid #FFFFFF !important; 
    }
    p.parsley-error {
        color: #808EAC !important; 
        background-color: #FFFFFF !important; 
        border: 1px solid #FFFFFF !important;   
    }
    .modal-footer-model {
    padding: 15px ;
    text-align: right;  

}
.select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
        background-color: #3E5566;
        color: white;
    }
.modal-lg {
    width: 90%;
}    
</style>
<?php
//property limit
    $auth = Auth::user();
    $property_limit = '';
    $user_property = DB::table('properties')->where('user_id', $auth->id)->count();
    if ($auth->trial == 'yes') {
        if (date('Y-m-d') > $auth->trial_end) { //free expired
            $current = DB::table('subscribers')->where('user_id', $auth->id)->first();

            $p = DB::table('subscriptions')->where('id', $current->subscription_id)->first();
            $property_limit = $p->number_of_property;
        } else {
            $p = DB::table('subscriptions')->where('id', '28')->first();
            $property_limit = $p->number_of_property;
        }
    }
    $url = url('property');
    if ($auth->is_admin != 0) {
        if ($user_property == $property_limit) {
            header("Location: $url");
        }
    }
    ?>
    <div class="right_col" role="main">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <div class="col-md-6">
                        <h2>
                            {{empty($property->id) ? 'Add New' : 'Update' }} Property
                            <small>
                                @isset($remaing_unit) 
                                    Remaining Unit {{$remaing_unit}}
                                @endisset 
                            </small>
                        </h2>
                    </div>      
                    <div class="col-md-6" style="text-align: right;">
                        @if(!isset($property->id))  
                            <a class="btn btn-primary addCustomer" href="#"> + Add Customer</a>
                        @endif

                        @if(!empty($property->units))         
                            <a class="btn btn-primary" href="{{url('routecheck-point?property=')}}{{$property->id}}" target="_blank"> + Route Check Point</a>
                        @endif
                        
                        <!-- @if(isset($property->id) && $property->type == 3)
                            <a class="btn btn-primary" href="javascript:void(0);" 
                                data-toggle="modal" data-target="#add_more_unit"> + Add More Units
                            </a>
                        @endif -->
                   </div> 
                   <div class="clearfix"></div>
                </div>
                @if(session('status'))
                    <div class="alert alert-danger">
                        <ul>
                            {{ session('status') }}
                        </ul>
                    </div>      
                @endif     
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    <div class="col-md-12 center-margin">
                        <form id="add_property" 
                            name="add_property" 
                            data-parsley-validate="" 
                            class="form-horizontal form-label-left" 
                            novalidate="" 
                            action="@if(isset($property->id) && !empty($property->id)){{url('property/'.$property->id)}} @else{{url('property')}}@endif" 
                            method="post" >
                            <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                            @if(isset($property->id) && !empty($property->id))
                                {{method_field('PUT')}}
                            @endif
                            <div class="x_content">
                                <br>
                                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                    <label>Customer *</label> 
                                    <select id="state_field"
                                        class="form-control col-md-7 col-xs-12" name="customer" 
                                        data-parsley-required-message="Please Select customer" 
                                        required="required req_field">
                                            @if(isset($customers) && ($customers->isNotEmpty()))
                                                <option value="">Select Customer</option>
                                                    @foreach($customers as $customer)
                                                        <option @if(isset($current_customer->id) && ($current_customer->id == $customer->id)) selected="selected" @endif @if(isset($property->customer_id) && ($property->customer_id == $customer->id)) selected="selected" @endif value="{{$customer->id}}">{{$customer->name}}
                                                        </option>
                                                    @endforeach
                                            @endif
                                    </select>
                                </div>                                    
                                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                    <label>Property Manager</label> 
                                    <select class="form-control col-md-7 col-xs-12"
                                        id="property-manager-select" 
                                        name="propertyManager[]" multiple>
                                        @if(isset($propertyManager) && ($propertyManager->isNotEmpty()))
                                            @foreach($propertyManager as $customer)
                                            <option value="{{$customer->id}}" @if(isset($propertyCheck) && in_array($customer->id,$propertyCheck)) selected @endif>{{ucwords($customer->firstname)}} {{ucwords($customer->lastname)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                    <label>Redundant Route Service</label> 
                                    <select class="form-control col-md-7 col-xs-12"
                                        id="redundant-select" 
                                        name="redundant[]" multiple>
                                        @if(isset($users) && ($users->isNotEmpty()))
                                            @foreach($users as $users)
                                            <option value="{{$users->id}}" @if(isset($redundant) && in_array($users->id, $redundant)) selected @endif>{{ucwords($users->firstname)}} {{ucwords($users->lastname)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @isset($property->customer_id)
                                  <input type="hidden" name="previousCustomerId" value="{{$property->customer_id}}">
                                @endisset
                                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                    <label>Address Type *</label>
                                    <p>
                                        <input type='checkbox'
                                            class="type-check" {{ Request::is('property/create') ? 'checked' : '' }}  name="main_address[]" value="1" id="inline_content"  @isset($property) @if($property->main_address == 1) checked @else @endif @endisset ><a href="#" data-toggle="tooltip" data-placement="bottom" title="It will select main address.">
                                            Main Office 
                                        </a>                                   
                                            <input type="checkbox"
                                                class="type-check" 
                                                name="main_address[]" class="same"
                                                id="same_address"
                                                value="same_address" 
                                                @isset($property)) @if($property->same_address == 1) checked @endif @endisset >
                                            
                                        <a href="#" data-toggle="tooltip" 
                                            data-placement="bottom" 
                                            title="It will set your present address."> Same Address </a>                                        
                                        <span class="type-msg" style="color:red"></span>
                                    </p>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                                    <label>Property Name *</label>
                                    <input id="property_name" 
                                        name="property_name" 
                                        data-parsley-required-message="Please enter property name" 
                                        placeholder="Property Name" required="required" 
                                        value="@if(isset($property->name) && !empty($property->name)){{$property->name}} @endif" 
                                        class="form-control col-md-7 col-xs-12" type="text">
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                                    <label>Property Type *</label>
                                    <select id="property_type" name="property_type" data-parsley-required-message="Please select property type"  required="required" class="form-control col-md-7 col-xs-12" @if(isset($property->name)) disabled="disabled" @endif>
                                        <option @if(isset($property)) @if($property->type==2) selected="selected" @endif @endif value="2">Garden Style Apartment</option>
                                        <option @if(isset($property)) @if($property->type==1) selected="selected" @endif @endif value="1">Curbside Community</option>
                                        <option @if(isset($property)) @if($property->type==3) selected="selected" @endif @endif value="3">High Rise Apartment</option>
                                        <option @if(isset($property)) @if($property->type==4) selected="selected" @endif @endif value="4">Townhome</option>
                                    </select>
                                </div>                                
                                @if(isset($property->name)) <input name="property_type" value="{{$property->type}}" type="hidden"> @endif
                                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                    <label>Address *</label>
                                    <input id="address_field"
                                        placeholder="Address" 
                                        class="form-control col-md-7 col-xs-12" 
                                        name="address" data-parsley-required-message="Please enter street address"
                                        required="required"
                                        value="@if(isset($property->address) && !empty($property->address)){{$property->address}}@endif" 
                                        type="text">
                                </div>
                                <div class="form-group">
                                    <div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
                                        <label>City *</label>
                                        <input id="city_field" 
                                            placeholder="City" class="form-control col-md-7 col-xs-12" 
                                            name="city" data-parsley-required-message="Please enter city" 
                                            required="required" 
                                            value="@if(isset($property->city) && !empty($property->city)){{$property->city}}@endif" 
                                            type="text">
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
                                        <label>State *</label>
                                        <select id="state_field"  class="form-control col-md-7 col-xs-12" name="state" data-parsley-required-message="Please select state" required="required">
                                            @if(isset($states) && ($states->isNotEmpty()))
                                                    <option value="">Select State</option>
                                                @foreach($states as $state)
                                                    <option @if(isset($property->state) && ($property->state == $state->id)) selected="selected" @endif value="{{$state->id}}" {{ old('state') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-12 form-group has-feedback">
                                        <label>Zip Code *</label>
                                        <input id="zip_field" data-parsley-type="digits" 
                                            placeholder="Zip Code"  class="form-control col-md-7 col-xs-12" 
                                            name="zip" data-parsley-required-message="Please enter zip code" 
                                            required="required" 
                                            value="@if(isset($property->zip) && !empty($property->zip)){{$property->zip}}@endif" 
                                            type="text" onkeypress="return isNumber(event)"> 
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                        <label>Radius (Miles) *</label>
                                        <input placeholder="Radius" 
                                            class="form-control col-md-7 col-xs-12" 
                                            name="propertyRadius" 
                                            data-parsley-required-message="Please enter property radius" 
                                            required="required"  
                                            value="@if(isset($property->radius)){{$property->radius}}@else{{1}}@endif" 
                                            type="text" onkeypress="return isNumber(event)"> 
                                    </div>
                                </div>  
                               
                                <div id="hide-and-show">
                                  <div class="x_title" id="hide-and-show-heading">
                                      <div class="col-md-6">
                                          <h2 class="building-label-heading">
                                          @if(isset($property->id) && $property->type==1)
                                            Streets Info
                                          @elseif(isset($property->id) && $property->type==4)
                                            Buildings Info
                                          @elseif(isset($property->id) && $property->type==3)
                                            Floors Info
                                          @else
                                          Buildings Info
                                          @endif
                                          </h2>
                                          &nbsp;<small>
                                              <a href="javascript:void(0);" class="add_button" title="Add field">
                                                  <i class="fa fa-plus-square fa-2x" style="color: #00ae7a;"></i>
                                              </a></small>
                                      </div>
                                    <div class="clearfix"></div>
                                </div>                                    
                                <br>                                
                                <div class="field_wrapper">
                                    @if(isset($units) && $units->isNotEmpty())
                                    @foreach($units as $unit)
                                    <div>
                                        <div class="row">       
                                            <div class="col-md-12">       
                                                <div class="col-md-6 builiding-name">
                                                    <label>
                                                        @if(isset($property->id) && $property->type==1)
                                                            Name Of Streets *
                                                        @elseif(isset($property->id) && $property->type==2)
                                                            Name Of Buildings *
                                                        @elseif(isset($property->id) && $property->type==3)
                                                            Name Of Floors *
                                                        @else
                                                            Name Of Streets *
                                                        @endif
                                                    </label>
                                                    <input class="form-control" 
                                                        name="editBuilding[{{$loop->index}}][name]"
                                                        type="text" 
                                                        value="{{$unit->building_name}}" 
                                                        required /> 
                                                </div>        
                                                <div class="col-md-3 unit-number">
                                                     <label># of Units *</label>
                                                     <input placeholder="Number Of Unit" 
                                                        class="form-control col-md-7 col-xs-12" type="text" 
                                                        value="{{$unit->get_unit_count}}"
                                                        data-parsley-type="digits" 
                                                        onkeypress="return isNumber(event)" readonly/> 
                                                </div>     
                                                <div class="col-md-3 unit-number">
                                                     <label>Add More Units *</label>
                                                     <input placeholder="Add More Unit" 
                                                        class="form-control col-md-7 col-xs-12 addmoreunit" type="text" 
                                                        name="editBuilding[{{$loop->index}}][more]"
                                                        data-parsley-type="digits" 
                                                        onkeypress="return isNumber(event)" /> 
                                                        <ul class="parsley-errors-list filled" id="parsley-id-420">
                                                            <li class="parsley-required"></li>
                                                        </ul>
                                                </div>                                            
                                                <div class="col-md-12" style="padding-top: 10px;">
                                                    <label class="building-label">
                                                        @if($property->type==1)
                                                            Streets Address *
                                                        @elseif($property->type==2)
                                                            Buildings Address *
                                                        @elseif($property->type==3)
                                                            Floors Address *
                                                        @else
                                                            Streets Address *
                                                        @endif
                                                    </label>
                                                    <input name="editBuilding[{{$loop->index}}][address]"
                                                        class="form-control col-md-7 col-xs-12" 
                                                        type="text" 
                                                        value="{{$unit->address}}" 
                                                        required> 
                                                </div>    
                                                <input name="editBuilding[{{$loop->index}}][bid]" type="hidden" value="{{$unit->id}}" >
                                            </div>
                                            <a href="javascript:void(0);" class="remove-building pull-right" style="color:red" data-bulidingId="{{$unit->id}}">Remove</a>
                                        </div>
                                     </div>
                                    <br/>
                                    @endforeach                                   
                                    @endif                                    
                                    @if(!isset($units) || $units->isEmpty())
                                    <div>
                                        <div class="row">       
                                            <div class="col-md-12">       
                                                <div class="col-md-6 builiding-name">
                                                     <label class="name-label">
                                                     Name Of Building *
                                                     </label>
                                                     <input name="addBuilding[1][name]"  
                                                        class="form-control col-md-7 col-xs-12" type="text" 
                                                        id="buildingNameType" 
                                                        data-parsley-required-message="Please enter the name" 
                                                        required="required"> 
                                                </div>        
                                                <div class="col-md-6 unit-number">
                                                     <label># of Units *</label>
                                                     <input name="addBuilding[1][unit]" 
                                                        class="form-control col-md-7 col-xs-12 unit-val addmoreunit" 
                                                        id="buildingUnitType" value="1" 
                                                        type="text" data-parsley-type="digits" 
                                                        data-parsley-required-message="Please enter the number of unit." 
                                                        onkeypress="return isNumber(event)"
                                                        required="required"> 
                                                        <ul class="parsley-errors-list filled" id="parsley-id-420">
                                                            <li class="parsley-required"></li>
                                                        </ul>
                                                </div>  
                                                <div class="col-md-12" style="padding-top: 10px;">
                                                    <label class="building-label">Building Address *</label>
                                                    <input name="addBuilding[1][address]"
                                                        class="form-control col-md-7 col-xs-12"
                                                        type="text" id="buildingAddress"
                                                        data-parsley-required-message="Please enter the address"
                                                        required="required"> 
                                                </div>  
                                            </div>
                                        </div>  
                                    </div>
                                    @endif
                                </div>
                                </div>
                                <br>                                
                                <div class="x_title">
                                    <div class="col-md-6"><h2>Service Agreement <small></small></h2></div>
                                    <div class="clearfix"></div>
                                </div>
                                <br>
                                <div class="x_content">
                                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                         <label>Pick Up Date *</label>
                                         <input type="text"
                                            name="datefilter" 
                                            class="form-control col-md-7 col-xs-12 datepicker" 
                                            value="@if(isset($service->pickup_start) && !empty($service->pickup_start) && isset($service->pickup_finish) && !empty($service->pickup_finish)){{\Carbon\Carbon::parse($service->pickup_start)->format('m/d/Y')}} - {{\Carbon\Carbon::parse($service->pickup_finish)->subDays(1)->format('m/d/Y') }}@endif"
                                            data-parsley-date="" 
                                            data-date-format="MM/DD/YYYY" required="required" readonly/>
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                        <label>Pick up Frequency *<small> [Current Day 6 AM to Next day 6 AM]</small></label>
                                        <p>
                                            <?php $count = 0; ?>
                                            @foreach ($days as $x => $day)
                                                <input type="checkbox" {{ Request::is('property/create') ? 'checked' : '' }} @if(isset($frequencies) && in_array($count, $frequencies))checked="checked"@endif   name="pick_frequency[]" value="{{$x}}" class="ifcheck" > {{$day}} 
                                                <?php ++$count; ?>
                                            @endforeach
                                        </p>   
                                    </div>
                                    <input type="hidden" name="qr_code_tracking" value="1">
                                    <div class="form-group">
                                        <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                                            <label for="zip_field">Pick up type *</label> 
                                            <p>
                                                <input type="radio" 
                                                   class="flat" name="pick_type" 
                                                   id="inline_content" value="1" checked=""  @if(isset($service)) @if($service->pickup_type==1) checked="" @endif @endif /> Waste
                                                <input type="radio" 
                                                    class="flat" name="pick_type" 
                                                    id="inline_content" value="2" @if(isset($service)) @if($service->pickup_type==2) checked="" @endif @endif /> Recycle
                                                <input type="radio"
                                                    class="flat" name="pick_type" 
                                                    id="inline_content" value="3" @if(isset($service)) @if($service->pickup_type==3) checked="" @endif @endif /> Both
                                            </p>       
                                        </div>
                                    </div>
                                    <div class="form-group">  
                                        <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                                            <label>Bin Size - Waste (gal) *</label>
                                            <input id="waste_weight_field" 
                                               placeholder="Waste weight"
                                               class="form-control col-md-7 col-xs-12"
                                               name="waste_weight"
                                               data-parsley-required-message="Please enter waste weight"
                                               value="@if(isset($service->waste_weight) && !empty($service->waste_weight)) {{$service->waste_weight}}@endif"
                                               onkeypress="return isNumber(event)"/>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                                            <label>Bin Size - Recycle (gal)*</label>
                                            <input id="recycle_weight_field"
                                                placeholder="Recycle weight"
                                                class="form-control col-md-7 col-xs-12"
                                                name="recycle_weight"
                                                data-parsley-required-message="Please enter recycle weight"
                                                value="@if(isset($service->recycle_weight) && !empty($service->recycle_weight)) {{$service->recycle_weight}}@endif" onkeypress="return isNumber(event)"/>
                                        </div>
                                    </div>
                                    <div class="form-group"> 
                                        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                            <label>Waste Reduction Target (%)*</label> 
                                            <input id="waste_reduction" required 
                                                onkeypress="return isNumber(event)" 
                                                data-parsley-type="digits" 
                                                placeholder="Waste reduction target" 
                                                name="waste_reduction" 
                                                class="form-control col-md-7 col-xs-12" 
                                                data-parsley-required-message="Please enter waste reduction target"
                                                value="@if(isset($service->waste_reduction_target) && !empty($service->waste_reduction_target)){{$service->waste_reduction_target}}@endif"/>
                                        </div>
                                    </div>
                               <br>
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                                        <button type="button" 
                                            class="btn btn-success add_units">
                                                @if(isset($property->id) && !empty($property->id)) Update @else Add @endif
                                        </button>        
                                        <button class="btn btn-primary" 
                                                type="button" onclick="location = '{{url('property')}}'; return false;">
                                                Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<input type="text" value="@if(isset($remaing_unit)) {{$remaing_unit}} @endif" class="remaing_unit">
@endsection
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>

<script>    
$(document).ready(function() {
    
    
    if (new URLSearchParams(window.location.search).get('id') != null) {
        let urlString = new URLSearchParams(window.location.search).get('id');
        $("#state_field").children('[value="' + urlString + '"]').prop("selected", true);
    }

    $( ".addCustomer" ).click(function() {
        localStorage.setItem('customerStatus', true);
        window.location.href = BaseUrl + '/customer/create';
    }); 

    $('.remove-building').click(function() {
        if (confirm("Are you sure you want to delete the building?")) {
            let id = $(this).data('bulidingid');
            var is = $(this).parent();
            $.ajax({
                url: `${BaseUrl}/property/bulding-remove`,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: id
                },
                success: function (data) {
                    if(data.status) {
                        is.remove();
                    }

                    new PNotify({
                        title: "Property",
                        text: data.text,
                        type: data.type,
                        styling: 'bootstrap3'
                    });
                }
            });
        }
    }); 

    $(".add_units").click(function() {

        var edit = {{!empty($property->id) ? 'true' : 'false' }}
        
        if(edit && confirm("Are you sure you want to update the property details ?")) {
            $('#add_property').submit();
        } 
        
        if (!edit) {
            $('#add_property').submit();
        }
        
        return;
    });

    function sumall() {
        
        var total = 0;
        
        $('.addmoreunit').each(function(){
            quantity = parseInt($(this).val()); 
                if (!isNaN(quantity)) {
                    total += quantity;
                }
        });
        
        return total;
    }
    
    $(document).on("keyup", ".addmoreunit", function() {
        
    var remaing_unit = $(".remaing_unit").val();
    
        if (sumall() > remaing_unit) {
            
            $("#parsley-id-420 li").html("Remaining Unit: 0");
            $('.add_units').prop('disabled', true);
            $('#add_unit').prop('disabled', true);
        } else {

            $("#parsley-id-420 li").html("");
            $('.add_units').prop('disabled', false);
            $('#add_unit').prop('disabled', false);
        }
    });
 

    if (jQuery('.type-check:checked').length == 0) {
        
        $('.add_units').attr('disabled', 'disabled');
        $('.type-msg').text('Please select at least one checkbox.');
    }
 
    $('.type-check').click(function() { 
        
        if (jQuery('.type-check:checked').length === 0) { 
            
            $('.add_units').attr('disabled', 'disabled');
            $('.type-msg').text('Please select at least one checkbox.');
        }
        else
        {
            
            $('.add_units').removeAttr('disabled');
            $('.type-msg').text('');
        }
    });
    
    if(jQuery('.ifcheck:checked').length == 0) {
        
        $('.add_units').attr('disabled', 'disabled');
        $('#checkboxMsg').text('Please select at least one checkbox.');
    }

    $('.ifcheck').click(function() {
        
        if(jQuery('.ifcheck:checked').length == 0) {
            $('.add_units').attr('disabled', 'disabled');
            $('#checkboxMsg').text('Please select at least one checkbox.');
        }
        else
        {
            $('.add_units').removeAttr('disabled');
            $('#checkboxMsg').text('');
        }
    });
    
    @if(isset($service) && $service->pickup_type==1)
    
        $("input[name=waste_weight]").prop('required', true);
        $("input[name=recycle_weight]").prop('required', false);
        $("input[name=waste_reduction]").prop('required', true);
       
    @elseif(isset($service) && $service->pickup_type==2)
      
        $("#recycle_weight_field").attr('required', true);
        $("#waste_weight_field").attr('required', false);
        $("#waste_reduction").attr('required', false);
    
    @elseif(isset($service) && $service->pickup_type==3)
    
        $("input[name=recycle_weight]").prop('required', true);
        $("input[name=waste_weight]").prop('required', true);
        $("input[name=waste_reduction]").prop('required', true);    
        
    @endif
});
    
    $("input[name=waste_weight]").prop('required', true);
    $("input[name=recycle_weight]").prop('required', false);
    $('input[name="pick_type"]').on('ifChanged', function(event) {

        if ($(this).val() == '1') {

            $("input[name=waste_weight]").prop('required', true);
            $("input[name=recycle_weight]").prop('required', false);
            $("input[name=waste_reduction]").prop('required', true);
        } else if ($(this).val() == '2') {

            $("input[name=recycle_weight]").prop('required', true);
            $("input[name=waste_weight]").prop('required', false);
            $("input[name=waste_reduction]").prop('required', false);
        } else {
            $("input[name=recycle_weight]").prop('required', true);
            $("input[name=waste_weight]").prop('required', true);
            $("input[name=waste_reduction]").prop('required', true);
        }
    });
    
    /*date range picker*/
    $(function() {
        $('input[name="datefilter"]').daterangepicker({
                minDate:new Date(),
                autoUpdateInput: false,
                locale: {
                cancelLabel: 'Clear'            
            }
        });

        $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
        
    $(document).ready(function () {
       
        var $radios = $(document).on('change','#same_address',function() {
       
            if ($(this).is(":checked")) {
                if ($(this).val() == "same_address") {

                    var customer_id = $('#state_field').val();
                    var token = $('#_token').val();
                    var base_url = '{{url('')}}';

                    $.ajax({
                        url: base_url + "/property/customer/details/autopuplate",
                        type: "POST",
                        dataType:'json',
                        data: {_token: token, customer_id: customer_id},
                        success: function (data) {
                            if (data.status == 1) {
                                $('#address_field').val(data.address);
                                $('#city_field').val(data.city);
                                $('#zip_field').val(data.zip);
                                $('select[name^="state"] option[value="' + data.state + '"]').prop("selected", true);
                            }
                        }
                    });
                }
            } else {

                $('#address_field').val('');
                $('#city_field').val('');
                $('#zip_field').val('');
                event.preventDefault();
                $('select[name^="state"] option:selected').removeAttr('selected');
            }
        });
    });
  
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
        return true;
    }
    
    $(document).ready(function() {
        var maxField = 1000000000; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper        
        var x = 1; //Initial field counter is 1
        
        //Once add button is clicked
        $(addButton).click(function(){
            //Check maximum number of input fields
        
            if($('#property_type').val() == 1 || $('#property_type').val() == 4) {
                var address = 'Streets Address';
                var name = 'Name Of Streets';
            }

            if($('#property_type').val() == 3) {
                var address = 'Floors Address';
                var name = 'Name Of Floors';
            }
            
            if($('#property_type').val() == 2) {
                var address = 'Building Address';
                var name = 'Name Of Building';
            }
                
            if(x < maxField){ 
                x++; //Increment field counter
                var fieldHTML = '<div style="margin-top: 3%;" class="remove-div-onchange"><div class="row"><div class="col-md-12"><div class="col-md-6 builiding-name"><label>'+name+' *</label><input name="addBuilding['+x+'][name]"  class="form-control col-md-7 col-xs-12" type="text" data-parsley-required-message="Please enter the name" required="required"></div><div class="col-md-6 unit-number"><label># of Units *</label><input name="addBuilding['+x+'][unit]" class="form-control col-md-7 col-xs-12 unit-val addmoreunit" type="text" data-parsley-type="digits" data-parsley-required-message="Please enter the number of unit." onkeypress="return isNumber(event)"required="required"><ul class="parsley-errors-list filled" id="parsley-id-420"><li class="parsley-required"></li></ul></div><div class="col-md-12" style="padding-top: 10px;"><label class="building-label">'+address+' *</label><input name="addBuilding['+x+'][address]"  class="form-control col-md-7 col-xs-12" type="text" id="buildingAddress" data-parsley-required-message="Please enter the address" required="required"></div></div></div><a href="javascript:void(0);" class="remove_button pull-right" style="color:red">Remove</a></div>'; //New input field html 
                $(wrapper).append(fieldHTML); //Add field html
            }
            
        // if($('#property_type').val() == 4) { 
        //     $('.builiding-name').hide();
        //     $('.unit-val').val(1); 
        //     $('.building-label').text('Unit Address');
        //     $('.unit-val').attr('readonly','readonly');
        //     $('.unit-number').removeClass('col-md-6').addClass('col-md-12');
        // } else {
        //     $('.builiding-name').show();
        //     $('.building-label').text('Building Address');
        //     $('.unit-val').removeAttr('readonly');
        //     $('.unit-number').removeClass('col-md-12').addClass('col-md-6');
        // }     
    });        
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        $(this).parent('div').remove(); //Remove field html
        x--; //Decrement field counter
    });
});

$('#redundant-select').select2();
$('#property-manager-select').select2();

$('#property_type').on("change",function() {
    $('.remove-div-onchange').remove();
    $("#parsley-id-420 li").html("");
    $(".addmoreunit").val("");

    // $('.builiding-name').show();
    // $('.unit-number').removeClass('col-md-12').addClass('col-md-6');
    // $('.unit-val').removeAttr('readonly');



    if($(this).val() == 1 || $(this).val() == 4) {
        $('.building-label').text('Streets Address');
        $('.building-label-heading').text('Streets Info');
        $('.name-label').text('Name Of Streets');
    }

    if($(this).val() == 3){
        $('#buildingAddress').removeAttr('required');
        $('.building-label').text('Floors Address');
        $('.building-label-heading').text('Floors Info');
        $('.name-label').text('Name Of Floors');
        $('.unit-val').removeAttr('readonly');
    }
    
    if($(this).val() == 2){
        $('#buildingAddress').removeAttr('required');
        $('.building-label').text('Building Address');
        $('.building-label-heading').text('Building Info');
        $('.name-label').text('Name Of Building');
        $('.unit-val').removeAttr('readonly');
    }
        $('#buildingAddress').attr('required','required');
        $('#buildingNameType').val('');
        $('#hide-and-show').show(); 
});
</script>
@endsection
