@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
<link href="{{url('assets/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css')}}" rel="stylesheet">
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
</style>
@endsection
@section('content')
<div class="right_col" role="main">
   <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="x_panel">
         <div class="x_title">
            <h2>{{isset($employee->id) ? "Update" : "Add New"}} Employee</h2>
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
            <form id="add_employee" 
               name="add_employee" 
               data-parsley-validate="" 
               class="form-horizontal form-label-left" 
               novalidate="" 
               action="{{isset($employee->id) ? url('employee/'.$employee->id) : url('employee')}}"                       
               method="post">
               <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
               {{!isset($employee->id) ? '' : method_field('PUT')}}
               <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title">Title</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <input id="title" 
                        name="title"
                        class="form-control col-md-7 col-xs-12" 
                        type="text"
                        value="{{!isset($employee->id) ? '' : $employee->title}}"
                        >
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">
                  First Name 
                  <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <input id="first_name" 
                        name="first_name" 
                        data-parsley-required-message="Please enter first name" 
                        required="required"
                        value="{{ Request::is('employee/create') ? old('first_name') : '' }}@if(isset($employee->firstname) && !empty($employee->firstname)){{$employee->firstname}} @endif" 
                        class="form-control col-md-7 col-xs-12" 
                        type="text">
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" 
                     for="last_nam e">
                  Last Name 
                  <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <input id="last_name" 
                        name="last_name" 
                        data-parsley-required-message="Please enter last name" 
                        required="required" 
                        class="form-control col-md-7 col-xs-12" 
                        value="{{ Request::is('employee/create')?old('last_name'):''}}@if(isset($employee->lastname) && !empty($employee->lastname)){{$employee->lastname}} @endif" 
                        type="text">
                  </div>
               </div>
               <div class="form-group">
                  <label for="email_field" 
                     class="control-label col-md-3 col-sm-3 col-xs-12">
                  Email 
                  <span class="required req_field">*</span>
                  </label> 
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <input id="email_field" 
                     data-parsley-remote-message="This email has already been taken." 
                     data-parsley-trigger="focusin focusout" 
                     data-parsley-remote="@if(isset($employee->id) && !empty($employee->id)){{url('validate/email?id='.$employee->id)}}  @else{{url('validate/email')}}@endif"
                     data-parsley-type="email"  
                     class="form-control col-md-7 col-xs-12" 
                     name="email" 
                     data-parsley-required-message="Please enter email" 
                     required="required" 
                     value="{{ Request::is('employee/create')?old('email'):''}}@if(isset($employee->email) && !empty($employee->email)){{$employee->email}}@endif" 
                     @if(isset($employee->email) && !empty($employee->email)) readonly @endif 
                     type="text">
                  </div>
               </div>
               <div class="form-group">
                  <label for="mobile_field" 
                     class="control-label col-md-3 col-sm-3 col-xs-12">
                  Mobile 
                  <span class="required req_field">*</span>
                  </label> 
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <input id="mobile_field"
                        data-parsley-trigger="focusin focusout" 
                        data-parsley-length-message="This number is invalid. Please enter 10 digits in the field." 
                        data-parsley-remote-message="This number has already been taken." 
                        data-parsley-remote="@if(isset($employee->id) && !empty($employee->id)){{url('validate/mobile?id='.$employee->id)}} @else{{url('validate/mobile')}}@endif"
                        name="mobile"
                        required="required"
                        value="{{ Request::is('employee/create')?old('mobile'):''}}@if(isset($employee->mobile)&& !empty($employee->mobile)){{$employee->mobile}}@endif" 
                        class="form-control col-md-7 col-xs-12"
                        type="text"
                        minlength="10" 
                        maxlength="10"
                        onpaste="return false;"
                        onkeypress="return isNumber(event)" >
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Time Zone">
                  Employee Type
                  <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <select class="form-control col-md-7 col-xs-12 emp-type" 
                        name="employee_type" 
                        data-parsley-required-message="Please select the employee type." 
                        required>
                        <option value="">Select</option>
                        <option value="1" @if(isset($employee) && $employee->employee_type == 1) selected @endif>
                        Employee
                        </option>
                        <option value="2" @if(isset($employee) && $employee->employee_type == 2) selected @endif>
                        Contractor
                        </option>
                     </select>
                  </div>
               </div>
               <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Time Zone">
                  Time Zone 
                  <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <select class="form-control col-md-7 col-xs-12"
                        name="timezone" 
                        data-parsley-required-message="Please select timezone" 
                        required="">
                        <option value="">Select</option>
                        @foreach(selectTimezone() as $key => $timezone)
                        <option value="{{$key}}" @if(isset($employee) && $employee->timezone == $key) selected @endif >
                        {{$timezone}}
                        </option>
                        @endforeach
                     </select>
                  </div>
               </div>
               @permission('employees')
               <div class="form-group">
                  <label for="role_field" class="control-label col-md-3 col-sm-3 col-xs-12">
                  Select Role <span class="required req_field">*</span></label> 
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <select required="required" 
                        id="role_field" data-parsley-required-message="Please Select role" 
                        class="form-control col-md-7 col-xs-12" 
                        name="role" 
                        required="required" 
                        value="">
                        <option value="">--</option>
                        @if(isset($roles) && $roles->isNotEmpty())
                        @foreach($roles as $role)
                        <option @if(isset($employee->role_id) && ($employee->role_id == $role->id)) selected="selected" @endif value="{{$role->id}}">
                        {{$role->display_name}}
                        </option>
                        @endforeach
                        @endif
                     </select>
                  </div>
               </div>
               @endpermission
               <div class="form-group">
                  <label for="mobile_field" class="control-label col-md-3 col-sm-3 col-xs-12">Properties</label> 
                  <div class="col-md-6 col-sm-6 col-xs-12"> 
                     <select class="form-control col-md-7 col-xs-12" id="property-id-select2" name="property_id[]" multiple>
                     @isset($property)
                     @foreach($property as $properties) 
                     <option value="{{$properties->id}}" @if(isset($propertyCheck) && in_array($properties->id,$propertyCheck)) selected @endif value="{{$properties->id}}" />{{ucwords($properties->name)}}</option>
                     @endforeach
                     @endisset
                     </select>
                  </div>
               </div>
               <div class="form-group reporting">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Reporting Manager</label> 
                  <div class="col-md-6 col-sm-6 col-xs-12">
                     <select class="form-control col-md-7 col-xs-12 reportManager" name="reportingManagerId" required>
                        @isset($adminUser)
                          @foreach($adminUser as $auser) 
                            <option value="{{$auser->id}}"
                             @if(isset($employee) && $employee->reporting_manager_id == $auser->id) selected @endif>{{ucwords($auser->name)}}</option>
                          @endforeach
                        @endisset
                     </select>
                  </div>
               </div>
               <div class="form-group reporting">
                  <label for="mobile_field" class="control-label col-md-3 col-sm-3 col-xs-12">Employee Schedule Time</label>
                  <div class="col-md-3 col-sm-3 col-xs-6">
                     <div class='input-group date' id='myDatepicker3'>
                        <input type='text' class="form-control" 
                        name="serviceInTime" 
                        value="{{!isset($employee->service_in_time) ? '' : $employee->service_in_time}}" autocomplete="off" 
                        placeholder="Service In Time" />
                        <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                     </div>
                  </div>
                  <div class="col-md-3 col-sm-3 col-xs-6">
                     <div class='input-group date' id='myDatepicker4'>
                        <input type='text' class="form-control"
                        value="{{!isset($employee->service_out_time) ? '' : $employee->service_out_time}}"
                        autocomplete="off"
                        name="serviceOutTime" placeholder="Service Out Time"/>
                        <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                     </div>
                  </div>
               </div> 
               <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" 
                     for="last_name">
                  Frequency <span class="required req_field">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12" style="margin-top: 0.5%;">
                     <label>
                      <input type="checkbox" 
                      name="frequency[]" 
                      value="0" class="flat" 
                      @if(isset($employee->clockinout_frequency_day) && in_array(0, $clockinoutFrequencyDay)) checked @endif
                      /> Sunday
                        <label>
                     <label>
                      <input type="checkbox" 
                      name="frequency[]" value="1" class="flat" 
                      @if(isset($employee->clockinout_frequency_day) && in_array(1, $clockinoutFrequencyDay)) checked @endif
                      /> Monday
                     </label>
                     <label>
                      <input type="checkbox" name="frequency[]" 
                      value="2" class="flat"
                      @if(isset($employee->clockinout_frequency_day)  && in_array(2, $clockinoutFrequencyDay)) checked @endif
                       /> Tuesday
                      </label>
                     <label>
                      <input type="checkbox" name="frequency[]" 
                      value="3" class="flat"
                      @if(isset($employee->clockinout_frequency_day) && in_array(3, $clockinoutFrequencyDay)) checked @endif
                      /> Wednesday
                     </label>
                     <label>
                      <input type="checkbox" name="frequency[]"
                      class="flat"  
                      value="4"  
                      @if(isset($employee->clockinout_frequency_day) && in_array(4, $clockinoutFrequencyDay)) checked @endif
                      /> Thursday
                     </label>
                     <label>
                      <input type="checkbox" name="frequency[]" 
                      class="flat"
                      @if(isset($employee->clockinout_frequency_day) && in_array(5, $clockinoutFrequencyDay)) checked @endif
                      value="5" /> Friday
                     </label>
                     <label>
                      <input type="checkbox" name="frequency[]" value="6"
                      class="flat"
                      @if(isset($employee->clockinout_frequency_day) && in_array(6, $clockinoutFrequencyDay)) checked @endif
                       /> Saturday
                     </label>
                  </div>
               </div>
               <div class="ln_solid"></div>
               <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                     <button type="submit" class="btn btn-primary dffsdfdsf">
                     {{isset($employee->id) ? "Update" : "Add"}}
                     </button>
                     <button class="btn btn-success" 
                        type="button" 
                        onclick="location = '{{url('employee')}}'; return false;"> Cancel
                     </button>                      
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
@endsection 
@section('js')
<script src="{{url('assets/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/add.edit.employee.js')}}"></script>
<script>
   $('#add_employee').parsley({
       excluded: '.two input'
   });
   $(document).ready(function () {
       $id = $('#limit').attr('id');
       if ($id) {
           $("#add_employee").css("display", "none");
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