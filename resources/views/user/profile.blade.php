@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .parsley-required {
        display: block;
    }
    .parsley-type{
        display: none;
    }
</style>
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Update Profile <small></small></h2>

                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br>
                <form id="add_subscriber" 
                      name="add_subscriber"
                      data-parsley-validate=""
                      class="form-horizontal form-label-left" 
                      novalidate="" action="@if(isset($subscriber_details)){{url('subsprofileupdate')}}/{{$subscriber_details->id}} @else {{url('userprofileupdate')}}/{{$user->id}} @endif" 
                      method="post" 
                      enctype="multipart/form-data">
                    
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                    <input type="hidden" name="userid"  value="@if(isset($user)){{$user->id}} @endif"/>
                    <input type="hidden" name="old_image_name"  value="@if(isset($user)){{$user->image_name}} @endif"/>
                    @role('admin')
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="package_name">
                            Company Name  <span class="required">*</span>
                        </label>
    
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="company_name" 
                                   name="company_name"
                                   data-parsley-required-message="Please enter company name" 
                                   placeholder="Company Name" 
                                   value="@if($subscriber_details){{$subscriber_details->company_name}}@endif" 
                                   required="required" 
                                   class="form-control col-md-7 col-xs-12"
                                   type="text">
                        </div>
                    </div>
                    @endrole
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="package_name">Title</label>
                        <div class="col-md-2 col-xs-12">
                            <input id="title" 
                                   name="title"
                                   placeholder="Title" 
                                   value="@if($subscriber_details){{$user->title}}@endif"
                                   class="form-control col-md-7 col-xs-12"
                                   type="text">     
                        </div>

                        <div class="col-md-2 col-sm-3 col-xs-12">                                    
                            <input id="first_name" name="first_name" data-parsley-required-message="Please enter first name" placeholder="First Name" required="required"   class="form-control has-feedback-left" value="@if(isset($user)){{$user->firstname}}@endif"  type="text"/>
                            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                        </div>


                        <div class="col-md-2 col-sm-3 col-xs-12">                                   
                            <input id="last_name" name="last_name" data-parsley-required-message="Please enter last name"  required="required" placeholder="Last Name" class="form-control has-feedback-left" value="@if(isset($user)){{$user->lastname}}@endif" type="text"/>
                            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email <span class="required">*</span>
                        </label>
                        <div class="col-md-3 col-sm-3 col-xs-12">                                   
                            <input id="email" 
                                   value="@if(isset($user)){{$user->email}}@endif"   
                                   class="form-control col-md-7 col-xs-12" 
                                   type="email" readonly>

                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12">                                   

                            <input id="mobile" 
                                   data-parsley-trigger="focusin focusout" 
                                   data-parsley-length-message="This value length is invalid. It should be have only 10 digits" 
                                   name="mobile" 
                                   required="required"
                                   value="@if(isset($user)){{$user->mobile}}@endif" 
                                   placeholder="Mobile" 
                                   class="form-control col-md-7 col-xs-12"
                                   type="text" 
                                   minlength="10" 
                                   maxlength="10" 
                                   onpaste="return false;"
                                   onkeypress="return isNumber(event)" />
                        </div>
                    </div>

                    @if(isset($subscriber_details) && !empty($subscriber_details))
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">City <span class="required">*</span>
                        </label>
                        <div class="col-md-3 col-sm-3 col-xs-12">                                   
                            <input id="city_name" name="city_name" data-parsley-required-message="Please enter city" placeholder="City"  required="required" value="@if($subscriber_details){{$subscriber_details->city}}@endif"  class="form-control col-md-7 col-xs-12" type="text">
                        </div>


                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <select id="state_field"  class="form-control col-md-7 col-xs-12" 
                                    name="state" 
                                    data-parsley-required-message="Please select state"
                                    required="required">
                                @if(isset($states) && ($states->isNotEmpty()))
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                <option @if(isset($subscriber_details->state) && ($subscriber_details->state == $state->id)) selected="selected" @endif value="{{$state->id}}">{{$state->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>


                        <div class="col-md-1 col-sm-1 col-xs-12">                                   
                            <input id="zip"
                                   name="zip" 
                                   data-parsley-required-message="Please enter zip code" 
                                   placeholder="Zipcode" 
                                   required="required" 
                                   value="@if($subscriber_details){{$subscriber_details->zip}}@endif"  
                                   class="form-control col-md-7 col-xs-12" type="text">
                        </div>

                    </div>

                    @role('admin')
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="address">
                            Address <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">                                   
                            <input id="address"
                                   name="address" 
                                   data-parsley-required-message="Please enter address" 
                                   required="required" 
                                   value="@if($user){{$subscriber_details->address}}@endif" 
                                   placeholder="Address"
                                   class="form-control col-md-7 col-xs-12" 
                                   type="text">

                        </div>
                    </div>
                    @endrole

                    @endif

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Time Zone">Time Zone  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select class="form-control col-md-7 col-xs-12" name="timezone" data-parsley-required-message="Please select timezone" required="">
                                <option value="">Select</option>
                                @foreach(selectTimezone() as $key => $timezone)
                                <option value="{{$key}}" @if(isset($user) && $user->timezone == $key) selected @endif >{{$timezone}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">
                            User Profile 
                        </label>
                        
                        @if (isset($user->image_name) && (!is_null($user->image_name) || Storage::disk('s3')->exists('uploads/user/' . $user->image_name)))
                            @php
                                $filename = url('uploads/user/' . $user->image_name);
                            @endphp
                        @else
                            @php
                                $filename = url('/uploads/user/no-image-available.png'); 
                            @endphp
                        @endif

                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                        <img 
                            src="{{$filename}}"
                            height="50px"
                            width="50px"
                        >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="image_name" name="image_type"  value="@if(isset($user)){{$user->image_name}} @endif" class="form-control col-md-7 col-xs-12" style="cursor: pointer;" type="file">
                        </div>
                    </div>


                    @role('admin')
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Company Logo  
                        </label>

                        @if (isset($subscriber_details) && ($subscriber_details->company_logo == null || Storage::disk('s3')->exists('uploads/user/' . $subscriber_details->company_logo)))

                            @php
                                $filename = url('uploads/user/' . $subscriber_details->company_logo);
                            @endphp    
                        @else
                            @php
                                $filename = url('/uploads/user/no-image-available.png'); 
                            @endphp
                        @endif
 
                       <div class="col-md-6 col-sm-6 col-xs-12"> 
                            <img 
                                src="{{$filename}}"
                                height="50px"
                                width="50px"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="companyLogo" name="companyLogo" 
                                   value="@if(isset($subscriber_details)){{$subscriber_details->company_logo}} @endif"
                                   class="form-control col-md-7 col-xs-12"
                                   style="cursor: pointer;" type="file">
                        </div>
                    </div>
                    @endrole
                    <!--FOR SUBSCRIBER ONLY-->
                    @if($user->is_admin)     
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">
                         <!-- Task: 1007 comment: #4, #13, #15--> 
                         Manual Pickup
                      </label>
                        <div class="checkbox">
                            <label>
                              <input type="checkbox" class="flat" name="manual_pickup"
                                @if(is_null($appPermission)) checked
                                @elseif(!is_null($appPermission) && !empty($appPermission->manual_pickup)) checked @endif
                              > 
                            </label>
                          </div>
                    </div>
                    @endif
                  <!--FOR SUBSCRIBER ONLY-->

                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">Update</button>
                            <button class="btn btn-primary" 
                                    type="button" 
                                    onclick="history.go(-1); return false;">Cancel</button>                         
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection 
@section('js')
<script>
  
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    $(document).ready(function () {
        $('#mobile').bind('copy paste cut', function (e) {
            e.preventDefault(); //disable cut,copy,paste

        });
    });
    
</script>

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <script>
            $(function() {
                new PNotify({
                    title: 'Error',
                    text: '{{$error}}',
                    type: 'error',
                    styling: 'bootstrap3'
                });
            });
        </script>
    @endforeach
@endif

@endsection

