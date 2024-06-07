@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
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
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>@if(isset($employee[0]->id) && !empty($employee[0]->id))Update @else Add @endif Property Manager <small></small></h2>

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

            <form id="add_employee" name="add_employee" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="@if(isset($employee[0]->id) && !empty($employee[0]->id)){{url('property-manager/update/'.$employee[0]->id)}} @else{{url('property-manager/add')}}@endif" method="post">

                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>

                @if(isset($employee[0]->id) && !empty($employee[0]->id))
                {{method_field('PUT')}}
                @endif

                <input type="hidden" value="1" name="isPropertyManger">

                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title">Title</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input id="title" 
                               name="title"
                               class="form-control col-md-7 col-xs-12" 
                               type="text" value="@if(isset($employee[0]->title) && !empty($employee[0]->title)){{$employee[0]->title}} @endif">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">First Name <span class="required req_field">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input id="first_name" name="first_name" data-parsley-required-message="Please enter first name" required="required" value="@if(isset($employee[0]->firstname) && !empty($employee[0]->firstname)){{$employee[0]->firstname}} @endif" class="form-control col-md-7 col-xs-12" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last_nam e">Last Name <span class="required req_field">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input id="last_name" name="last_name" data-parsley-required-message="Please enter last name" required="required" class="form-control col-md-7 col-xs-12" value="@if(isset($employee[0]->lastname) && !empty($employee[0]->lastname)){{$employee[0]->lastname}} @endif" type="text">
                    </div>
                </div> 
                <div class="form-group">
                    <label for="email_field" class="control-label col-md-3 col-sm-3 col-xs-12">Email <span class="required req_field">*</span></label> 
                    <div class="col-md-6 col-sm-6 col-xs-12">

                        <input id="email_field" data-parsley-remote-message="This email has already been taken." data-parsley-trigger="focusin focusout"  data-parsley-remote="@if(isset($employee[0]->id) && !empty($employee[0]->id)){{url('validate/email?id='.$employee[0]->id)}} @else{{url('validate/email')}}@endif" data-parsley-type="email"  class="form-control col-md-7 col-xs-12" name="email" data-parsley-required-message="Please enter email" required="required" value="@if(isset($employee[0]->email) && !empty($employee[0]->email)){{$employee[0]->email}}@endif" @if(isset($employee[0]->email) && !empty($employee[0]->email)) readonly @endif type="text">

                    </div>
                </div>

                <div class="form-group">
                    <label for="mobile_field" class="control-label col-md-3 col-sm-3 col-xs-12">Mobile <span class="required req_field">*</span></label> 
                    <div class="col-md-6 col-sm-6 col-xs-12">

                        <input id="mobile_field" data-parsley-trigger="focusin focusout" data-parsley-length-message="This number is invalid. Please enter 10 digits in the field." data-parsley-remote-message="This number has already been taken." data-parsley-remote="@if(isset($employee[0]->id) && !empty($employee[0]->id)){{url('validate/mobile?id='.$employee[0]->id)}} @else{{url('validate/mobile')}}@endif"
                               name="mobile"
                               required="required"
                               value="@if(isset($employee[0]->mobile)&& !empty($employee[0]->mobile)){{$employee[0]->mobile}}@endif" 
                               class="form-control col-md-7 col-xs-12"
                               type="text"
                               minlength="10" 
                               maxlength="10"
                               onpaste="return false;"
                               onkeypress="return isNumber(event)" >
                    </div>
                </div> 
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Time Zone">Time Zone  <span class="required req_field">*</span>
                    </label>

                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <select class="form-control col-md-7 col-xs-12" name="timezone" data-parsley-required-message="Please select timezone" required="">

                            <option value="">Select</option>
                            @foreach(selectTimezone() as $key => $timezone)
                            <option value="{{$key}}" @if(isset($employee) && $employee[0]->timezone == $key) selected @endif >{{$timezone}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
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

                <!--                        <div class="form-group">
                                            <label class="col-md-3 col-sm-3 col-xs-12 control-label">Permission <span class="required req_field">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-xs-12">
                                                Call property manager permission helper: Start
                                                @foreach(managerPremission() as $key => $permission)
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="permission[]" value="{{$key}}" data-parsley-multiple="permission"  @if(isset($permission_id) && in_array($key,$permission_id)) checked @endif> {{$permission}}
                                                    </label>
                                                </div>
                                                @endforeach
                                                Call property manager permission helper: End
                                            </div>
                                        </div>-->

                <div class="form-group">
                    <label for="mobile_field" class="control-label col-md-3 col-sm-3 col-xs-12"></label> 
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input name="role" type="hidden" value="10">
                    </div>
                </div>

                <div class="clearfix"></div>


                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button type="submit" class="btn btn-success role-submit">@if(isset($employee[0]->id) && !empty($employee[0]->id)) Update @else Add @endif</button>
                        <button class="btn btn-primary" type="button" onclick="location = '{{url('property-manager')}}'; return false;" >Cancel</button>						 

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 
@section('js')
<script>

    $('#add_employee').parsley({
        excluded: '.two input'
    });
    $(document).ready(function () {
        $id = $('#limit').attr('id');
        if ($id) {
            $("#add_employee").css("display", "none");
        }

        $('#property-id-select2').select2();

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

