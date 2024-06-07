@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .parsley-required{
        display: none;
    }
</style>
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>@if(isset($customer->id) && !empty($customer->id))Edit @else Add New @endif Customer Property <small></small></h2>

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
                <form id="add_customer_property" name="add_customer" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="" method="post">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                    @if(isset($customer->id) && !empty($customer->id))
                    {{method_field('PUT')}}
                    @endif

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field">Customer Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="title_field" name="customer_name" readonly required="required" value="@if(isset($customer->name) && !empty($customer->name)){{$customer->name}}@endif" class="form-control col-md-7 col-xs-12" type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field">Property Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="title_field" name="customer_name" required="required" value="" class="form-control col-md-7 col-xs-12" type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email_field">Property Type <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select id="email_field" name="email"  class="form-control col-md-7 col-xs-12" type="text">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone"># of units <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="phone" name="phone"  required="required" class="form-control col-md-7 col-xs-12" value="" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address_field" class="control-label col-md-3 col-sm-3 col-xs-12">Street Address <span class="required">*</span></label> 
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="address_field"    class="form-control col-md-7 col-xs-12" name="address" required="required" value="" type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="city_field" class="control-label col-md-3 col-sm-3 col-xs-12">City <span class="required">*</span></label> 
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="city_field"   class="form-control col-md-7 col-xs-12" name="city" required="required" value="" type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="state_field" class="control-label col-md-3 col-sm-3 col-xs-12">State <span class="required">*</span></label> 
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select id="state_field"  class="form-control col-md-7 col-xs-12" name="state" required="required">
                                @if(isset($states) && ($states->isNotEmpty()))
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                <option @if(isset($customer->state) && ($customer->state == $state->id)) selected="selected" @endif value="{{$state->id}}">{{$state->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="zip_field" class="control-label col-md-3 col-sm-3 col-xs-12">Zip <span class="required">*</span></label> 
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="zip_field"   class="form-control col-md-7 col-xs-12" name="zip" required="required" value="" type="text">
                        </div>
                    </div>


                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button class="btn btn-primary" type="button" onclick="location = '{{url('customer')}}';return false;">Cancel</button>						 
                            <button type="submit" class="btn btn-success">@if(isset($customer->id) && !empty($customer->id)) Update @else Add @endif</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#add_customer_property').submit(function (event) {
            event.preventDefault();
        })
    })
</script>
@endsection 

