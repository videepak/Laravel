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
                <h2>Update Property</h2>
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
            <form id="property" name="property" class="form-horizontal form-label-left" 
                action="{{url('property-manager/update-property/' . $property->id)}}"  
                enctype="multipart/form-data" method="post">

                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="propertyId" value="@if(!empty($property->id)){{$property->id}}@endif"/>
                    <div class="col-md-10 col-sm-12 col-xs-12 form-group has-feedback">
                        <label>Property Logo</label>
                            <input id="image_name" name="image_type"  class="form-control col-md-7 col-xs-12" style="cursor: pointer;" type="file"> 
                    </div>
                
                @if(!empty($property->image))
                    <div class="col-md-2 col-sm-12 col-xs-12 form-group has-feedback">
                        <img src="{{url('/uploads/property')}}/{{$property->image}}" height="100px" width="100px" class="img-circle">
                    </div>
                @endif
                
                <div class="form-group"> 
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                        <label>Violation Reminders</label>
                            <textarea name='reminder' class="form-control col-md-7 col-xs-12 ckeditor" placeholder="Write Here...">@if(!empty($property->reminder)){{$property->reminder}}@endif</textarea>
                    </div>
                </div>   
                <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                            <button type="submit" class="btn btn-success">
                                Update
                            </button>        
                            <button class="btn btn-primary" type="button" onclick="location = '{{url('property')}}'; return false;">
                                Cancel
                            </button>
                        </div>
                    </div>   
            </form>
        </div>
    </div>
</div>
@endsection 