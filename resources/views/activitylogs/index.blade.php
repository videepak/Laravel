@extends('layouts.user_app')
@include('layouts.user_menu')
@section('content')
@yield('menu')     
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6"><h2>Activity Logs<small></small></h2></div> 
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">S.No</th>
                                <th class="column-title">User Name</th>
                                <th class="column-title">User Email</th>
                                <th class="column-title">User Activity</th>
                                <th class="column-title">User IP Address</th>
                                <th class="column-title">User Bar Code</th>
                                <th class="column-title">Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activity as $activitylogs)
                            <tr class="even pointer">  
                                <td class=" ">{{ $loop->iteration }}</td>
                                <td class=" "> {{$user_details->firstname . ' ' .$user_details->lastname}}</td>
                                <td class=" "> {{$user_details->email}}</td>
                                <td class=" "> {{$activitylogs->text}}</td>
                                <td class=" ">{{$activitylogs->ip_address}}</td>
                                <td class=" ">{{$activitylogs->barcode_id}}</td>
                                <td class=" ">{{\Carbon\Carbon::parse($activitylogs->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{$activity->links()}}
        </div>
    </div>
</div>
@endsection 

