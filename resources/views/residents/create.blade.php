@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">
<link href="{{url('assets/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css')}}" rel="stylesheet">
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.6.3/css/buttons.dataTables.min.css" rel="stylesheet">
<style>
   .parsley-required{
      display: block;
   }
   .select2-container--default
   .select2-selection--multiple
   .select2-selection__choice {
   background-color: #3E5566;
   color: white;
   }
   #add_resident .row {
      margin-top: 12px;
   }
   .move_out_date {
      background: #26B99A;
      border: 1px solid #169F85;
      padding: 6px 12px;
      font-size: 14px;
      text-decoration: none;
      color: #fff;
      margin-bottom: 5px;
      margin-right: 5px;
      border-radius: 3px;
   }
   #resident_logs_paginate .paginate_button.active a {
      color: white !important;
   }
</style>
@endsection
@section('content')
<div class="right_col" role="main">
   <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="x_panel">
         <div class="x_title">
            <h2>{{isset($resident->id) ? "Update" : "Add New"}} Resident</h2>
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
            <br>
            @if(Session::has('message'))
            <p class="alert alert-danger">{{ Session::get('message') }}</p>
            @endif
            <form id="add_resident" 
               name="add_resident" 
               data-parsley-validate="" 
               class="form-horizontal form-label-left" 
               novalidate="" 
               action="{{isset($resident->id) ? url('property-manager/resident/'.$resident->id) : url('property-manager/resident')}}"                       
               method="post">
               <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
               {{!isset($resident->id) ? '' : method_field('PUT')}}
               <div class="row">
                  <label class="control-label col-md-1 col-sm-1 col-xs-6" for="first_name">
                  First Name 
                  <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-5 col-sm-5 col-xs-6">
                     <input id="first_name" 
                        name="first_name" 
                        data-parsley-required-message="Please enter first name" 
                        required="required"
                        value="{{ Request::is('property-manager/resident/create') ? old('first_name') : '' }}@if(isset($resident->firstname) && !empty($resident->firstname)){{$resident->firstname}} @endif" 
                        class="form-control col-md-4 col-xs-12" 
                        type="text">
                  </div>
                  <label class="control-label col-md-1 col-sm-1 col-xs-6" 
                     for="last_name">
                  Last Name 
                  <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-5 col-sm-5 col-xs-6">
                     <input id="last_name" 
                        name="last_name" 
                        data-parsley-required-message="Please enter last name" 
                        required="required" 
                        class="form-control col-md-4 col-xs-12" 
                        value="{{ Request::is('property-manager/resident/create')?old('last_name'):''}}@if(isset($resident->lastname) && !empty($resident->lastname)){{$resident->lastname}} @endif" 
                        type="text">
                  </div>
                </div>
                <div class="row">
                     <label for="mobile_field" 
                        class="control-label col-md-1 col-sm-1 col-xs-6">
                     Mobile 
                     <span class="required req_field">*</span>
                     </label> 
                     <div class="col-md-5 col-sm-5 col-xs-6">
                        <input id="mobile_field"
                           data-parsley-trigger="focusin focusout" 
                           data-parsley-length-message="This number is invalid. Please enter 10 digits in the field." 
                           data-parsley-remote-message="This number has already been taken." 
                           data-parsley-remote="@if(isset($resident->id) && !empty($resident->id)){{url('validate/mobile?id='.$resident->id)}} @else{{url('validate/mobile')}}@endif"
                           name="mobile"
                           required="required"
                           value="{{ Request::is('property-manager/resident/create')?old('mobile'):''}}@if(isset($resident->mobile)&& !empty($resident->mobile)){{$resident->mobile}}@endif" 
                           class="form-control col-md-4 col-xs-12"
                           type="text"
                           minlength="10" 
                           maxlength="10"
                           onpaste="return false;"
                           onkeypress="return isNumber(event)" >
                     </div>
                  <label for="email_field" 
                     class="control-label col-md-1 col-sm-1 col-xs-6">
                  Email 
                  <span class="required req_field">*</span>
                  </label> 
                  <div class="col-md-5 col-sm-5 col-xs-6">
                     <input id="email_field" 
                     data-parsley-remote-message="This email has already been taken." 
                     data-parsley-trigger="focusin focusout" 
                     data-parsley-remote="@if(isset($resident->id) && !empty($resident->id)){{url('validate/email?id='.$resident->id)}}  @else{{url('validate/email')}}@endif"
                     data-parsley-type="email"  
                     class="form-control col-md-4 col-xs-12" 
                     name="email" 
                     data-parsley-required-message="Please enter email" 
                     required="required" 
                     value="{{ Request::is('property-manager/resident/create')?old('email'):''}}@if(isset($resident->email) && !empty($resident->email)){{$resident->email}}@endif" 
                     @if(isset($resident->email) && !empty($resident->email)) readonly @endif 
                     type="text">
                  </div>
                </div> 
                <div class="row">
                  @if(isset($resident->id))
                  <label for="unit" class="control-label col-md-1 col-sm-1 col-xs-6">Unit</label> 
                  <div class="col-md-5 col-sm-5 col-xs-6"> 
                     {{-- <select class="form-control col-md-4 col-xs-12" id="unit-id-select2" name="unit_id">
                        <option value="">Select</option>   
                           @if(isset($unitDetails))
                              @foreach($unitDetails as $property)
                                 {{$unitName = $property->name}};
                                 @foreach($property->getUnit as $value)
                                    <option value="{{$value->id}}" 
                                    @if(isset($resident) && $resident->unit_id == $value->id) selected @endif>{{$unitName}} - {{$value->unit_number}}</option>
                                 @endforeach
                              @endforeach
                           @endif
                     </select> --}}
                     <a href='#' class='unit-change editable editable-click' data-type='select' data-pk={{ $resident->id }} data-url={{ url('/property-manager/update-unit') }} data-title='Select Unit'>@if(isset($resident->getUnit) && !empty($resident->getUnit)){{ $resident->getUnit->getPropertyDetail->name }} - {{ $resident->getUnit->unit_number }} @endif</a>
                     @endif
                  </div>
               
                {{-- <div class="form-group">
                  <label for="move_in_date" class="control-label col-md-3 col-sm-3 col-xs-12">Move in Date</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <div class='input-group date' id='residentDate'>
                        <input type='text' class="form-control datepicker" 
                        name="move_in_date" 
                        value="{{ Request::is('property-manager/resident/create')?old('move_in_date'):''}}@if(isset($resident->move_in_date) && !empty($resident->move_in_date)){{\Carbon\Carbon::parse($resident->move_in_date)->format('m/d/Y')}}@endif"  
                        autocomplete="off"
                        data-parsley-date=""
                        />
                        <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                     </div>
                  </div>
               </div> 
               @if(isset($resident->id))
               <div class="form-group">
                  <label for="move_in_date" class="control-label col-md-3 col-sm-3 col-xs-12">Move out Date</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <div class='input-group date' id='residentMoveOut'>
                        <input type='text' class="form-control datepicker" 
                        name="move_out_date" 
                        value="@if(isset($resident->move_out_date) && !empty($resident->move_out_date)){{\Carbon\Carbon::parse($resident->move_out_date)->format('m/d/Y')}}@endif"  
                        autocomplete="off"
                        data-parsley-date=""
                         />
                        <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                     </div>
                  </div>
               </div> 
               @endif --}}
               <div class="col-md-6 col-sm-6 col-xs-12">
                  <button type="submit" class="btn btn-primary dffsdfdsf">
                  {{isset($resident->id) ? "Update" : "Add"}}
                  </button>
                  <button class="btn btn-success" 
                     type="button" 
                     onclick="location = '{{url('property-manager/resident')}}'; return false;"> Cancel
                  </button>                      
               </div>
               {{-- <div class="ln_solid col-md-offset-3"></div> --}}
            </form>
         </div>
      </div>
   </div>
   @if(isset($resident->id))
   <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="x_panel">
         <div class="x_title">
              <h2>Resident Logs</h2>
              <ul class="nav navbar-right panel_toolbox">
               <li id="show-enties"></li>
               <li id="newSearchPlace"></li>
              </ul>
              <div class="clearfix"></div>
         </div>
              <div class="x_content">
               <div class="table-responsive">
                   <table id="resident_logs" class="table table-striped jambo_table bulk_action" data-id="{{ $resident->id }}">
                       <thead>
                           <tr class="headings">
                               <th class="column-title">S.No</th>
                               <th class="column-title">Property Name</th>
                               <th class="column-title">Unit Name</th>
                               <th class="column-title">Move in Date</th>
                               <th class="column-title">Move out Date</th>
                               <th class="column-title">Action</th>
                           </tr>
                       </thead>
                   </table>
               </div>
           </div>

          </div>
      </div>
   </div>
   @endif
</div>

@endsection 
@section('js')
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>
<script src="{{url('assets/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/add.edit.resident.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script type="text/javascript">
   var uniDetails =  {!! json_encode($unitDetails); !!};
</script>
<script type="text/javascript">
   $('#add_resident').parsley({
       excluded: '.two input'
   });
   $(document).ready(function () {
       $id = $('#limit').attr('id');
       if ($id) {
           $("#add_resident").css("display", "none");
       }
   });
   function isNumber(evt) {
       evt = (evt) ? evt : window.event;
       var charCode = (evt.which) ? evt.which : evt.keyCode;
       if (charCode > 31 && (charCode < 48 || charCode > 57)) {
           return false;
       }
       return true;
   }
</script>
@endsection