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

        <div class="col-md-5 col-sm-5 col-xs-12 profile_details">
            <div class="well profile_view">
                <div class="col-sm-12">
                    <div class="left col-xs-7">
                        <h2>
                            @isset($employee_details->title)
                                {{ucwords($employee_details->title)}}
                            @endisset
                            
                            @isset($employee_details->firstname)
                                {{ucwords($employee_details->firstname)}}
                            @endisset
                            
                            @isset($employee_details->lastname)
                                {{ucwords($employee_details->lastname)}}
                            @endisset
                        </h2>
                        <ul class="list-unstyled">
                            <li><i class="fa fa-envelope"></i> 
                                Email: @isset($employee_details->email){{$employee_details->email}}@endisset
                            </li>
                            <li><i class="fa fa-phone"></i> 
                                Phone #: @isset($employee_details->mobile){{$employee_details->mobile}}@endisset
                            </li>
                        </ul>
                    </div>                          
                </div>
                <div class="col-xs-12 bottom text-center">
                    <div class="col-xs-12 col-sm-6 emphasis"></div>
                    <div class="col-xs-12 col-sm-6 emphasis">
                        <a href="{{url('employee/'.$employee_details->id.'/edit/')}}/" 
                           class="btn btn-success btn-xs"><i class="fa fa-pencil"></i> 
                           Edit Profile
                        </a>         
                        <a href="{{url('activity/logs?user='.$employee_details->id)}}" title='Activity' class="btn btn-success btn-xs">
                            <li class="fa fa-history"></li> View Logs
                        </a> 
                    </div>
                </div>
            </div>
        </div>
        <div class="x_panel">
            <div class="x_title">
                <h2>Assigned Properties<small></small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped jambo_table bulk_action">
                    <thead>
                        <tr class="headings">  
                            <th class="column-title">S.No </th>
                            <th class="column-title">Property Name </th>
                            <th class="column-title">Property Type</th>
                            <th class="column-title">Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($propertydetails))
                            @foreach($propertydetails as $propdetails)
                            <tr class="even pointer">  
                                <td>{{$loop->iteration}}</td>
                                <td>{{$propdetails['property_name']}}</td>
                                <td>  
                                    @if($propdetails['type'] == 1)
                                        Single Family Home  
                                    @elseif($propdetails['type'] == 2)
                                        Garden Style Apartment 
                                    @elseif($propdetails['type'] == 3)
                                        High Rise 
                                    @elseif($propdetails['type'] == 4)
                                        Town Home 
                                    @endif
                                </td>
                                <td>{{$propdetails['address']}}, {{$propdetails['city']}}, {{$propdetails['state']}}, {{$propdetails['zip']}}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@yield('css')
<style>
    .content{
       display:inline;
    }
</style>
@endsection 