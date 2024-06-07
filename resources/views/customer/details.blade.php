@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<style>
    .parsley-required{
        display: block;
    }
    .content{
        display:inline;
    } 
</style>    
@endsection
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">

        <div class="col-md-5 col-sm-5 col-xs-12 profile_details">
            <div class="well profile_view">
                <div class="col-sm-12">
                    <div class="left col-xs-12">
                        <h2>{{ucwords($customer->name)}}</h2>

                        <ul class="list-unstyled">
                            <li><i class="fa fa-envelope"></i> Email: {{ $customer->email}}</li>
                            <li><i class="fa fa-phone"></i> Phone #: {{$customer->phone}}</li>
                            <li><i class="fa fa-building"></i> Address #: {{$customer->address}} {{$customer->city}} @if(!empty($customer->state) && ($customer->stateinfo)){{$customer->stateinfo->name}}@endif</li>
                            <li>&nbsp;</li>
                        </ul>
                    </div>
                </div>
                @if ($customer->user_id == $user->id)
                <div class="col-xs-12 bottom text-center">
                    <a href="{{url('customer/'.$customer->id.'/edit')}}/" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i> Edit Profile</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="x_panel">
        <div class="x_title">
            <h2>Properties<small></small></h2>
            <div class="clearfix"></div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action">
                <thead>
                    <tr class="headings">  
                        <th class="column-title">S.No </th>
                        <th class="column-title">Property Name</th>
                        <th class="column-title">Property Type</th>
                        <th class="column-title">Units</th>
                        <th class="column-title">Address</th>

                    </tr>
                </thead>
                <tbody>

                    @if(isset($properties) && !empty($properties))
                    @foreach($properties as $propertiesdetails)

                    <tr class="even pointer">  
                        <td class=" ">{{$loop->iteration}}</td>
                        <td class=" "><a href="{{url('property/'.$propertiesdetails->id.'/edit')}}/">{{ucwords($propertiesdetails['name'])}}</a></td>
                        <td class=" ">

                            @if($propertiesdetails['type'] == 1)
                            Single Family Home  
                            @elseif($propertiesdetails['type'] == 2)
                            Garden Style Apartment 
                            @elseif($propertiesdetails['type'] == 3)
                            High Rise 
                            @elseif($propertiesdetails['type'] == 4)
                            Town Home 
                            @endif
                        </td>
                        <td class=" ">{{$propertiesdetails['units']}}</td>
                        <td class=" ">{{$propertiesdetails['address']}}, {{$propertiesdetails['city']}}, {{$propertiesdetails->getState->name}}, {{$propertiesdetails['zip']}}</td>
                    </tr>
                    @endforeach
                    @endif 


                </tbody>
            </table>
        </div>



    </div>
</div>
@endsection 