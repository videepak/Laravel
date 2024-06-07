@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .parsley-required{
        display: block;
    }
</style>   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    @if(isset($customer->id) && !empty($customer->id))
                    Update 
                    @else Add New @endif Customer</h2>
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
                <form id="add_customer"
                    name="add_customer" data-parsley-validate="" 
                    class="form-horizontal form-label-left" 
                    action="@if(isset($customer->id) && !empty($customer->id)){{url('customer/'.$customer->id)}} @else{{url('customer')}}@endif" 
                    method="post">
                    <input type="hidden" 
                        name="_token" id="_token" 
                        value="{{ csrf_token() }}"
                    />
                            
                        @if(isset($customer->id) && !empty($customer->id))
                            {{method_field('PUT')}}
                        @endif

                    @if(isset($customer))
                        @includeIf(
                            'customer.createform', 
                            [
                                'customer' => $customer,
                                'allCustomers'=> null,
                                'states'=>$states
                            ]
                        )
                    @else
                        @includeIf(
                            'customer.createform', 
                            [
                                'customer'=>null,
                                'allCustomers'=> $allCustomers,
                                'states'=>$states
                            ]
                        )
                    @endif

                    <div class="ln_solid"></div>
                    
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">
                                @if(isset($customer->id) && !empty($customer->id)) Update @else Add @endif
                            </button>

                            <button class="btn btn-primary" type="button" onclick="location = '{{url('customer')}}';return false;">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 

