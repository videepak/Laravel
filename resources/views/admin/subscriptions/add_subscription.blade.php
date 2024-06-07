@extends('layouts.user_app')
@extends('layouts.menu')
@section('content')
<style>
    .parsley-required{
        display: block;
    }
    .req_field{
        color: red;
    }
</style>
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>@if($subscription)Update @else Add New @endif Subscription <small></small></h2>

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
                <form id="add_subscription" name="add_subscription" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="@if($subscription){{url('admin/updatesubscription')}}/{{$subscription->id}}@else{{url('admin/addsubscription')}}@endif" method="post">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="package_name">Package Name <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="package_name" name="package_name" data-parsley-required-message="Please enter package name" required="required" value="@if($subscription){{$subscription->package_offering}}@else{{old('package_name')}}@endif" class="form-control col-md-7 col-xs-12" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="admin">Premium User Licenses <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="admin" name="admin" data-parsley-required-message="Please enter number of admin accounts"  data-parsley-type="digits" required="required" class="form-control col-md-7 col-xs-12" value="@if($subscription){{$subscription->package_admin}}@else{{old('admin')}}@endif" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">User Licenses	 <span class="required req_field">*</span></label> 
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="field_controller" data-parsley-type="digits"  class="form-control col-md-7 col-xs-12" name="field_controller" data-parsley-required-message="Please enter number of field employees"  required="required" value="@if($subscription){{$subscription->package_field_collector}}@else{{old('field_controller')}}@endif" type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Number Of QR Code Packages <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="code_package" data-parsley-type="digits" name="code_package" data-parsley-required-message="Please enter number of QR code packages"  class="form-control col-md-7 col-xs-12" required="required" value="@if($subscription){{$subscription->package_qr_code}}@else{{old('code_package')}}@endif" type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Subscription Type <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <p></p>   
                            Monthly:
                            <input type="radio" class="flat" name="type" id="type1" value="1" @if($subscription)@if($subscription->subscription_type==1)checked="" @endif @else checked="" @endif  required /> Yearly:
                                   <input type="radio" class="flat" name="type" id="type2" value="12" @if($subscription)@if($subscription->subscription_type==12)checked="" @endif @endif />

                        </div>
                    </div>
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Number of Property Can manage<span class="required req_field"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="number" id="number_of_property" name="number_of_property" data-parsley-required-message="Please enter number of property" class="form-control col-md-7 col-xs-12" value="@if($subscription){{$subscription->number_of_property}}@else{{old('number_of_property')}}@endif" required="required" /> 
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Price($) <span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="price" name="price" data-parsley-required-message="Please enter price"  data-parsley-type="digits" class="form-control col-md-7 col-xs-12" required="required" value="@if($subscription){{$subscription->price}}@else{{old('price')}}@endif" type="text">
                        </div>
                    </div>
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Features<span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <textarea id="features" name="features" data-parsley-required-message="Please enter features" class="form-control col-md-7 col-xs-12" required="required" >@if($subscription){{$subscription->features}}@else{{old('features')}}@endif</textarea>
                        </div>
                    </div>
					
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Star Features(if marked *, mention detail )<span class="required req_field"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="star_features" name="star_features" data-parsley-required-message="Please enter features" class="form-control col-md-7 col-xs-12" value="@if($subscription){{$subscription->star_features}}@else{{old('star_features')}}@endif" /> 
                        </div>
                    </div>
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Plan Content<span class="required req_field">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <textarea id="plan_content" name="plan_content" data-parsley-required-message="Please enter plan content" class="form-control col-md-7 col-xs-12 ckeditor" required="required" >@if($subscription){{$subscription->plan_content}}@else{{old('plan_content')}}@endif</textarea>
                        </div>
                    </div>
					
					
					

                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">@if($subscription) Update @else Add @endif</button>
                            <button class="btn btn-primary" type="button" onclick="history.go(-1);return false;">Cancel</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection 

