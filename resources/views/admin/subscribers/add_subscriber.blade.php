@extends('layouts.user_app')
@extends('layouts.menu')
@section('content')
<style>
    .parsley-required {
        display: block;
    }
    .parsley-type {
        display: none;
    }
    .req_field {
        color: red;
    }
</style>
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>@if($subscriber)Update @else Add New @endif Subscriber <small></small></h2>
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
                <form id="add_subscriber" name="add_subscriber" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="@if($subscriber){{url('admin/updatesubscriber')}}/{{$subscriber->id}} @else {{url('admin/addsubscriber')}} @endif" method="post">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="package_name">Company Name  <span class="required req_field">*</span>
                        </label>

                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <input id="company_name" name="company_name" data-parsley-required-message="Please enter company name"  placeholder="Company Name" value="@if($subscriber){{$subscriber->company_name}}@endif" required="required"  class="form-control col-md-7 col-xs-12" type="text">
                        </div>

                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <select class="form-control col-md-7 col-xs-12" name="subscription_type" data-parsley-required-message="Please select subscription type"  id="subscription_type" required="">
                                <option value="">Select Subscription</option>
                                @foreach($subscription_type as $subs)
                                <option @if($subscriber) @if($subscriber->subscription_id==$subs->id) selected @endif @endif value="{{$subs->id}}">{{$subs->package_offering}} ($ {{$subs->price}} / @if($subs->subscription_type==1) Monthly @elseif($subs->subscription_type==12) Monthly @endif )</option>
                                @endforeach

                            </select>                                   

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="package_name">Title  <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-2 col-xs-12" required="required">
                            <select class="form-control col-md-7 col-xs-12" name="title" data-parsley-required-message="Please select title"  id="title" required="">

                                <option value="">Select</option>
                                <option value="Mr."  @if(isset($subscriber) && isset($user) && $user->title=='Mr.') selected @endif> Mr.</option>
                                <option value="Mrs." @if(isset($subscriber) && isset($user) && $user->title=='Mrs.') selected @endif> Mrs.</option>
                            </select>                                   
                        </div>

                        <div class="col-md-2 col-sm-3 col-xs-12">                                    
                            <input id="first_name" name="first_name" data-parsley-required-message="Please enter first name" placeholder="First Name" required="required"   class="form-control has-feedback-left" value="@if($subscriber){{$user->firstname}}@endif"  type="text"/>
                            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                        </div>


                        <div class="col-md-2 col-sm-3 col-xs-12">                                   
                            <input id="last_name" name="last_name" data-parsley-required-message="Please enter last name"  required="required" placeholder="Last Name" class="form-control has-feedback-left" value="@if($subscriber){{$user->lastname}}@endif" type="text"/>
                            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                        </div>

                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">                                   
                            <input id="email" name="email" data-parsley-trigger="focusin focusout" data-parsley-remote-message="This email has already been taken." data-parsley-remote="@if(isset($user->id) && !empty($user->id)){{url('validate/subscriber/email?id='.$user->id)}} @else{{url('validate/subscriber/email')}}@endif" data-parsley-required-message="Please enter email" required="required" value="@if($subscriber){{$user->email}} @endif" placeholder="Email" class="form-control col-md-7 col-xs-12" @if($subscriber) readonly @endif type="email">

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone">Contact Number <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">                                   
                            <input id="phone" name="phone" data-parsley-trigger="focusin focusout" data-parsley-length-message="This number is invalid. Please enter 10 digits in the field." data-parsley-remote-message="This number has already been taken." data-parsley-remote-message="This number has already been taken." data-parsley-remote="@if(isset($user->id) && !empty($user->id)){{url('validate/subscriber/mobile?id='.$user->id)}} @else{{url('validate/subscriber/mobile')}}@endif" data-parsley-required-message="Please enter contact number" required="required" value="@if($subscriber){{$user->mobile}}@endif" placeholder="Mobile" class="form-control col-md-7 col-xs-12" type="text" minlength="10" maxlength="10" onkeypress="return isNumber(event)">

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="address">Address <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">                                   
                            <input id="address" name="address" data-parsley-required-message="Please enter address" required="required" value="@if($subscriber){{$subscriber->address}}@endif" placeholder="Address" class="form-control col-md-7 col-xs-12" type="text">

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">City <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-3 col-sm-3 col-xs-12">                                   
                            <input id="city_name" name="city_name" data-parsley-required-message="Please enter city" placeholder="City"  required="required" value="@if($subscriber){{$subscriber->city}}@endif"  class="form-control col-md-7 col-xs-12" type="text">
                        </div>


                        <div class="col-md-2 col-sm-2 col-xs-12">
                            <select id="state_field"  class="form-control col-md-7 col-xs-12" name="state" data-parsley-required-message="Please select state" required="required">
                                @if(isset($states) && ($states->isNotEmpty()))
                                <option value="">Select State</option>
                                @foreach($states as $state)

                                <option @if(isset($subscriber->state) && ($subscriber->state == $state->id)) selected="selected" @endif value="{{$state->id}}">{{$state->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>


                        <div class="col-md-1 col-sm-1 col-xs-12">                                   
                            <input id="zip" name="zip" type="text"
                                   data-parsley-required-message="Please enter zip code"
                                   placeholder="Zipcode"
                                   required="required"
                                   value="@if($subscriber){{$subscriber->zip}}@endif"
                                   class="form-control col-md-7 col-xs-12">
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Time Zone">
                            Time Zone <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select class="form-control col-md-7 col-xs-12" 
                                    name="timezone" 
                                    data-parsley-required-message="Please select timezone" 
                                    required="">
                                <option value="">Select</option>
                                @foreach(selectTimezone() as $key => $timezone)
                                <option value="{{$key}}" @if(isset($user) && $user->timezone == $key) selected @endif >{{$timezone}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">
                            Auto Renew <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <p></p>   
                            Yes:
                            <input type="radio"  class="flat" name="renew" id="renew1" value="1" checked @if($subscriber) @if($subscriber->auto_renew==1) checked="" @endif @endif  /> No:
                                   <input type="radio"  class="flat" name="renew" id="renew2"  value="0" @if($subscriber) @if($subscriber->auto_renew==0) checked="" @endif @endif />

                        </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success"> @if($subscriber) Update @else Add @endif </button>
                            <button class="btn btn-primary" type="button" onclick="history.go(-1);return false;">Cancel</button>						 
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
</script>
@endsection

